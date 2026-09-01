/**
 * @file esp32_rfid.ino
 * @brief Firmware ESP32 — Absensi Siswa via RFID MFRC522
 *
 * Alur:
 *   Boot → init hardware → konek WiFi → heartbeat
 *   → loop: cek WiFi | heartbeat berkala | baca RFID
 *   → tap kartu → debounce → POST API → parse response
 *   → tampilkan hasil (Serial + LED + Buzzer + OLED opsional)
 *
 * ⚠ KEAMANAN: Absensi TIDAK dianggap berhasil tanpa HTTP 200 dari server.
 *
 * Kompilasi:
 *   Board     : "ESP32 Dev Module" (Tools > Board > ESP32 Arduino)
 *   Library   : ArduinoJson (Library Manager), MFRC522 (Library Manager)
 *   Optional  : Adafruit SSD1306, Adafruit GFX (jika USE_OLED aktif)
 *
 * @version 1.0.0
 */

#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

#include "config.h"
#include "wifi_manager.h"
#include "rfid_reader.h"
#include "api_client.h"

// ─── OLED (opsional) ─────────────────────────────────────────────────────────
#ifdef USE_OLED
    #include <Wire.h>
    #include <Adafruit_GFX.h>
    #include <Adafruit_SSD1306.h>
    static Adafruit_SSD1306 oled(OLED_WIDTH, OLED_HEIGHT, &Wire, -1);
    static bool             oledOk = false;
#endif

// =============================================================================
// Objek global
// =============================================================================

static WiFiManager  wifiMgr;
static RfidReader   rfid(PIN_RFID_SS, PIN_RFID_RST);
static ApiClient    api;

// =============================================================================
// State
// =============================================================================

static unsigned long lastHeartbeat   = 0;
static bool          processing      = false;   // flag: sedang POST ke server

// =============================================================================
// Forward declarations
// =============================================================================

void onResult(const AttendanceResult &r);
void displayReady();
void feedbackOk();
void feedbackError();
void feedbackBusy();
void initOled();
void oledShow(const String &l1,
              const String &l2 = "",
              const String &l3 = "",
              const String &l4 = "");

// =============================================================================
// setup()
// =============================================================================

void setup() {
    Serial.begin(115200);
    delay(300);

    Serial.println();
    Serial.println("╔══════════════════════════════════════╗");
    Serial.println("║  ESP32 Absensi Siswa — RFID MFRC522 ║");
    Serial.printf ("║  Device: %-28s║\n", DEVICE_CODE);
    Serial.println("╚══════════════════════════════════════╝");
    Serial.println();

    // ── Pin setup ───────────────────────────────────────────────────────────
    pinMode(PIN_BUZZER,  OUTPUT);
    pinMode(PIN_LED_OK,  OUTPUT);
    pinMode(PIN_LED_ERR, OUTPUT);
    digitalWrite(PIN_BUZZER,  LOW);
    digitalWrite(PIN_LED_OK,  LOW);
    digitalWrite(PIN_LED_ERR, LOW);

    // ── OLED ────────────────────────────────────────────────────────────────
    initOled();
    oledShow("ESP32 RFID", "Absensi Siswa", "Inisialisasi...");

    // ── RFID ────────────────────────────────────────────────────────────────
    if (!rfid.begin()) {
        Serial.println("[Setup] ⚠ MFRC522 tidak ditemukan — cek wiring SPI!");
        oledShow("ERROR", "MFRC522", "Tidak Ditemukan", "Cek Wiring!");
        feedbackError();
        // Lanjut boot; loop() tetap coba baca reader
    }

    // ── WiFi ────────────────────────────────────────────────────────────────
    oledShow("Menghubungkan", "ke WiFi...", WIFI_SSID);
    if (!wifiMgr.begin()) {
        Serial.println("[Setup] ⚠ WiFi gagal — akan retry dari loop().");
        oledShow("WiFi GAGAL", "Coba reconnect...");
        feedbackError();
    } else {
        oledShow("WiFi OK", wifiMgr.localIP(), "Kirim heartbeat...");
        feedbackOk();
        delay(300);

        // Heartbeat pertama
        api.sendHeartbeat();
        lastHeartbeat = millis();
    }

    displayReady();
    Serial.println("[Setup] Selesai. Tempelkan kartu RFID.\n");
}

// =============================================================================
// loop()
// =============================================================================

void loop() {
    // 1. Kelola WiFi (reconnect jika putus)
    wifiMgr.loop();

    // 2. Heartbeat berkala
    if (millis() - lastHeartbeat >= HEARTBEAT_INTERVAL_MS) {
        lastHeartbeat = millis();
        if (wifiMgr.isConnected()) {
            Serial.println("[Heartbeat] Mengirim...");
            api.sendHeartbeat();
        }
    }

    // 3. Tidak proses tap baru jika sedang POST
    if (processing) return;

    // 4. Baca kartu RFID (non-blocking, includes debounce)
    String uid;
    if (!rfid.readCard(uid)) return;

    // 5. Tandai sedang proses — blokir tap baru
    processing = true;

    Serial.printf("\n[Loop] UID terdeteksi: %s\n", uid.c_str());
    oledShow("Memproses...", uid.length() <= 16 ? uid : uid.substring(0, 16));
    feedbackBusy();

    // 6. Cek WiFi sebelum kirim
    if (!wifiMgr.isConnected()) {
        Serial.println("[Loop] WiFi tidak terhubung — absensi dibatalkan.");
        AttendanceResult offline;
        offline.success  = false;
        offline.httpCode = 0;
        offline.code     = AttendanceCode::NETWORK_ERROR;
        offline.message  = "SERVER TIDAK TERHUBUNG";
        onResult(offline);
        processing = false;
        return;
    }

    // 7. POST ke API — blocking tapi ada timeout internal
    AttendanceResult result = api.postAttendance(uid);

    // 8. Tampilkan hasil
    onResult(result);

    // 9. Jika server error (bukan logika, tapi koneksi gagal), reset debounce
    //    agar siswa bisa tap ulang setelah masalah koneksi teratasi
    if (result.code == AttendanceCode::NETWORK_ERROR ||
        result.code == AttendanceCode::SERVER_ERROR  ||
        result.code == AttendanceCode::AUTH_FAILED) {
        rfid.resetDebounce();
    }

    processing = false;
}

// =============================================================================
// Tampilkan hasil ke Serial + OLED + feedback audio/visual
// =============================================================================

void onResult(const AttendanceResult &r) {
    Serial.println("─────────────────────────────────────");

    switch (r.code) {

        // ── ABSENSI BERHASIL ────────────────────────────────────────────────
        case AttendanceCode::SUCCESS_CHECK_IN: {
            Serial.println("[Hasil] ABSENSI BERHASIL");
            Serial.printf ("[Hasil] Nama    : %s\n", r.studentName.c_str());
            Serial.printf ("[Hasil] Kelas   : %s\n", r.studentClass.c_str());
            Serial.printf ("[Hasil] Jam     : %s\n", r.time.c_str());
            Serial.printf ("[Hasil] Status  : %s\n", r.attStatus.c_str());

            oledShow(
                "ABSENSI BERHASIL",
                r.studentName.isEmpty()  ? "-" : r.studentName,
                r.studentClass.isEmpty() ? "-" : r.studentClass,
                (r.time.isEmpty() ? "-" : r.time) +
                    (r.attStatus.isEmpty() ? "" : " | " + r.attStatus)
            );
            feedbackOk();
            break;
        }

        // ── SUDAH ABSEN HARI INI ────────────────────────────────────────────
        case AttendanceCode::ALREADY_TODAY: {
            Serial.println("[Hasil] SUDAH ABSEN HARI INI");
            if (!r.studentName.isEmpty())
                Serial.printf("[Hasil] Nama: %s\n", r.studentName.c_str());

            oledShow(
                "SUDAH ABSEN",
                "HARI INI",
                r.studentName.isEmpty() ? "" : r.studentName
            );
            feedbackError();
            break;
        }

        // ── RFID TIDAK TERDAFTAR ────────────────────────────────────────────
        case AttendanceCode::NOT_FOUND: {
            Serial.println("[Hasil] RFID TIDAK TERDAFTAR");
            oledShow("RFID TIDAK", "TERDAFTAR");
            feedbackError();
            break;
        }

        // ── AUTH GAGAL ──────────────────────────────────────────────────────
        case AttendanceCode::AUTH_FAILED: {
            Serial.printf("[Hasil] Autentikasi perangkat gagal (HTTP %d)\n",
                          r.httpCode);
            oledShow("AKSES DITOLAK", "Cek konfigurasi", "device credential");
            feedbackError();
            break;
        }

        // ── SERVER TIDAK TERHUBUNG ──────────────────────────────────────────
        case AttendanceCode::NETWORK_ERROR: {
            Serial.println("[Hasil] SERVER TIDAK TERHUBUNG");
            oledShow("SERVER", "TIDAK TERHUBUNG");
            feedbackError();
            break;
        }

        // ── SERVER ERROR / UNKNOWN ──────────────────────────────────────────
        case AttendanceCode::SERVER_ERROR:
        case AttendanceCode::UNKNOWN:
        default: {
            Serial.printf("[Hasil] Error tidak dikenal. HTTP %d | %s\n",
                          r.httpCode, r.message.c_str());
            oledShow("ERROR SERVER", "HTTP " + String(r.httpCode));
            feedbackError();
            break;
        }
    }

    Serial.println("─────────────────────────────────────\n");

    // Beri jeda agar tampilan dapat dibaca sebelum kembali ke "SIAP"
    delay(LED_ON_DURATION_MS);
    displayReady();
}

// =============================================================================
// Status layar "SIAP" — tampilan default saat menunggu kartu
// =============================================================================

void displayReady() {
    Serial.println("[Status] Menunggu kartu RFID...");
    oledShow("SIAP", "Tempelkan kartu", "RFID Anda");
}

// =============================================================================
// Feedback audio (buzzer) + visual (LED)
// =============================================================================

/** Satu beep pendek + LED hijau */
void feedbackOk() {
    digitalWrite(PIN_LED_ERR, LOW);
    digitalWrite(PIN_LED_OK,  HIGH);
    digitalWrite(PIN_BUZZER,  HIGH);
    delay(BEEP_SUCCESS_MS);
    digitalWrite(PIN_BUZZER,  LOW);
}

/** Satu beep panjang + LED merah */
void feedbackError() {
    digitalWrite(PIN_LED_OK,  LOW);
    digitalWrite(PIN_LED_ERR, HIGH);
    digitalWrite(PIN_BUZZER,  HIGH);
    delay(BEEP_ERROR_MS);
    digitalWrite(PIN_BUZZER,  LOW);
}

/** Kedip singkat kedua LED — tanda sedang memproses */
void feedbackBusy() {
    digitalWrite(PIN_LED_OK,  HIGH);
    digitalWrite(PIN_LED_ERR, HIGH);
    delay(80);
    digitalWrite(PIN_LED_OK,  LOW);
    digitalWrite(PIN_LED_ERR, LOW);
}

// =============================================================================
// OLED Helpers (noop jika USE_OLED tidak didefinisikan)
// =============================================================================

void initOled() {
#ifdef USE_OLED
    Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
    if (!oled.begin(SSD1306_SWITCHCAPVCC, OLED_I2C_ADDR)) {
        Serial.println("[OLED] Tidak ditemukan — lanjut tanpa OLED.");
        oledOk = false;
        return;
    }
    oledOk = true;
    oled.clearDisplay();
    oled.setTextColor(SSD1306_WHITE);
    oled.setTextSize(1);
    oled.setCursor(0, 0);
    oled.println("ESP32 RFID Absensi");
    oled.display();
    Serial.println("[OLED] Siap.");
    delay(1000);
#endif
}

/**
 * Tampilkan hingga 4 baris teks di OLED.
 * Baris 1 ditampilkan dengan ukuran font lebih besar jika baris lain kosong.
 * Jika USE_OLED tidak aktif, hanya log ke Serial.
 */
void oledShow(const String &l1, const String &l2,
              const String &l3, const String &l4) {
    // Log ringkas ke Serial agar mudah debug tanpa OLED
    Serial.printf("[OLED] %s", l1.c_str());
    if (!l2.isEmpty()) Serial.printf(" | %s", l2.c_str());
    if (!l3.isEmpty()) Serial.printf(" | %s", l3.c_str());
    if (!l4.isEmpty()) Serial.printf(" | %s", l4.c_str());
    Serial.println();

#ifdef USE_OLED
    if (!oledOk) return;

    oled.clearDisplay();
    oled.setTextColor(SSD1306_WHITE);

    bool multiLine = !l2.isEmpty();

    if (!multiLine) {
        // Satu baris — font besar
        oled.setTextSize(2);
        oled.setCursor(0, 16);
        oled.println(l1);
    } else {
        // Multi baris — font kecil semua
        oled.setTextSize(1);
        oled.setCursor(0, 0);
        oled.println(l1);
        oled.println(l2);
        if (!l3.isEmpty()) oled.println(l3);
        if (!l4.isEmpty()) oled.println(l4);
    }

    oled.display();
#endif
}
