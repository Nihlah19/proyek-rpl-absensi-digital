/**
 * @file esp32_barcode.ino
 * @brief Firmware ESP32 — Absensi Guru via Barcode/QR Scanner (UART)
 *
 * Alur utama:
 *   Scanner baca barcode/QR
 *   → Debounce (abaikan scan duplikat)
 *   → POST ke API server
 *   → Parse response
 *   → Tampilkan hasil (LED + Buzzer + Serial + OLED opsional)
 *
 * Dependencies (install via Arduino Library Manager):
 *   - ArduinoJson  by Benoit Blanchon  (wajib, versi 6.x atau 7.x)
 *   - Adafruit SSD1306 + Adafruit GFX (opsional, jika USE_OLED aktif)
 *
 * Board: "ESP32 Dev Module" (Arduino IDE: Tools > Board > ESP32 Arduino)
 * Flash Frequency: 80MHz | Flash Mode: QIO | Partition Scheme: Default
 *
 * @author   Sistem Absensi SMK
 * @version  1.0.0
 */

#include <Arduino.h>
#include "config.h"
#include "wifi_manager.h"
#include "scanner.h"
#include "api_client.h"

// ─── Opsional: OLED ──────────────────────────────────────────────────────────
#ifdef USE_OLED
    #include <Wire.h>
    #include <Adafruit_GFX.h>
    #include <Adafruit_SSD1306.h>
    Adafruit_SSD1306 oled(OLED_SCREEN_WIDTH, OLED_SCREEN_HEIGHT, &Wire, -1);
    bool oledReady = false;
#endif

// =============================================================================
// Objek global
// =============================================================================

/** Driver scanner UART — ganti tipe di sini untuk scanner berbeda */
UartScannerDriver scannerDriver(Serial2,
                                SCANNER_UART_RX_PIN,
                                SCANNER_UART_TX_PIN,
                                SCANNER_BAUD_RATE);

/** Pointer ke interface abstrak (memungkinkan swap driver tanpa ubah logika) */
ScannerBase *scanner = &scannerDriver;

WiFiManager wifiManager;
ApiClient   apiClient;

// =============================================================================
// State internal
// =============================================================================
static String        lastScannedId      = "";
static unsigned long lastScanTime       = 0;
static unsigned long lastHeartbeatTime  = 0;
static bool          processingRequest  = false;

// =============================================================================
// Forward declarations
// =============================================================================
void showResult(const AttendanceResult &result);
void showMessage(const String &line1, const String &line2 = "",
                 bool isError = false);
void feedbackOk();
void feedbackError();
void feedbackWaiting();
void initOled();
void oledPrint(const String &line1, const String &line2 = "",
               const String &line3 = "");

// =============================================================================
// setup()
// =============================================================================
void setup() {
    Serial.begin(115200);
    delay(500);
    Serial.println("\n========================================");
    Serial.println("  ESP32 Absensi Guru — Barcode/QR");
    Serial.printf ("  Device ID: %s\n", DEVICE_ID);
    Serial.println("========================================\n");

    // --- Inisialisasi pin ---
    pinMode(PIN_BUZZER,   OUTPUT);
    pinMode(PIN_LED_OK,   OUTPUT);
    pinMode(PIN_LED_ERR,  OUTPUT);
    digitalWrite(PIN_BUZZER,  LOW);
    digitalWrite(PIN_LED_OK,  LOW);
    digitalWrite(PIN_LED_ERR, LOW);

    // --- Inisialisasi OLED (opsional) ---
    initOled();

    // --- Inisialisasi scanner UART ---
    if (!scanner->begin()) {
        Serial.println("[Scanner] GAGAL init scanner!");
        // Firmware tetap berjalan; scanner akan dicoba terus dari loop()
    } else {
        Serial.printf("[Scanner] UART siap — RX:GPIO%d TX:GPIO%d @%lu baud\n",
                      SCANNER_UART_RX_PIN, SCANNER_UART_TX_PIN,
                      (unsigned long)SCANNER_BAUD_RATE);
    }

    // --- Sambungkan WiFi ---
    oledPrint("Menghubungkan", "ke WiFi...", WIFI_SSID);
    if (!wifiManager.begin()) {
        Serial.println("[Setup] WiFi gagal. Akan coba reconnect dari loop().");
        oledPrint("WiFi GAGAL", "Coba ulang...");
        feedbackError();
    } else {
        oledPrint("WiFi Terhubung", wifiManager.getIP());
        feedbackOk();
    }

    // --- Heartbeat pertama ---
    if (wifiManager.isConnected()) {
        apiClient.sendHeartbeat();
        lastHeartbeatTime = millis();
    }

    Serial.println("[Setup] Selesai. Sistem siap menerima scan.\n");
    oledPrint("SIAP", "Scan kartu guru...");
}

// =============================================================================
// loop()
// =============================================================================
void loop() {
    // 1. Kelola WiFi (reconnect otomatis jika putus)
    wifiManager.loop();

    // 2. Heartbeat berkala
    if (millis() - lastHeartbeatTime >= HEARTBEAT_INTERVAL_MS) {
        lastHeartbeatTime = millis();
        if (wifiManager.isConnected()) {
            Serial.println("[Heartbeat] Mengirim...");
            apiClient.sendHeartbeat();
        }
    }

    // 3. Baca scanner — non-blocking
    if (processingRequest) return;  // tunggu selesai

    String scannedId;
    if (!scanner->readScan(scannedId)) return;  // belum ada data

    // 4. Validasi panjang identifier
    if (scannedId.length() < SCAN_MIN_LENGTH) {
        Serial.printf("[Scanner] Identifier terlalu pendek (%d char): '%s'\n",
                      scannedId.length(), scannedId.c_str());
        return;
    }

    // 5. Debounce — abaikan scan yang sama dalam SCAN_DEBOUNCE_MS
    if (scannedId == lastScannedId &&
        (millis() - lastScanTime) < SCAN_DEBOUNCE_MS) {
        Serial.printf("[Debounce] '%s' diabaikan (cooldown %lu ms tersisa)\n",
                      scannedId.c_str(),
                      SCAN_DEBOUNCE_MS - (millis() - lastScanTime));
        return;
    }

    lastScannedId = scannedId;
    lastScanTime  = millis();

    // 6. Tampilkan status "memproses"
    processingRequest = true;
    Serial.printf("\n[Scan] Identifier: '%s'\n", scannedId.c_str());
    oledPrint("Memproses...", scannedId.substring(0, 16));
    feedbackWaiting();
    scanner->flush();  // buang sisa buffer

    // 7. Cek koneksi WiFi sebelum kirim
    if (!wifiManager.isConnected()) {
        Serial.println("[API] WiFi tidak terhubung — batalkan request.");
        AttendanceResult offline;
        offline.success  = false;
        offline.httpCode = 0;
        offline.message  = "SERVER TIDAK TERHUBUNG";
        offline.action   = "offline";
        showResult(offline);
        processingRequest = false;
        return;
    }

    // 8. Kirim ke API server
    AttendanceResult result = apiClient.postAttendance(scannedId);

    // 9. Tampilkan hasil
    showResult(result);
    processingRequest = false;
}

// =============================================================================
// Fungsi bantu: tampilkan hasil absensi
// =============================================================================
void showResult(const AttendanceResult &result) {
    String line1, line2;

    if (!wifiManager.isConnected() || result.action == "offline") {
        // ── SERVER TIDAK TERHUBUNG ──────────────────────────────────────────
        line1 = "SERVER";
        line2 = "TIDAK TERHUBUNG";
        Serial.println("[Hasil] SERVER TIDAK TERHUBUNG");
        oledPrint(line1, line2);
        feedbackError();
        return;
    }

    if (result.success) {
        if (result.action == "check_in") {
            // ── ABSENSI DATANG BERHASIL ─────────────────────────────────────
            line1 = "ABSENSI DATANG";
            line2 = "BERHASIL";
            if (result.teacherName.length() > 0) {
                Serial.printf("[Hasil] ABSENSI DATANG BERHASIL — %s\n",
                              result.teacherName.c_str());
                oledPrint(line1, line2, result.teacherName);
            } else {
                Serial.println("[Hasil] ABSENSI DATANG BERHASIL");
                oledPrint(line1, line2);
            }
            feedbackOk();

        } else if (result.action == "check_out") {
            // ── ABSENSI PULANG BERHASIL ─────────────────────────────────────
            line1 = "ABSENSI PULANG";
            line2 = "BERHASIL";
            if (result.teacherName.length() > 0) {
                Serial.printf("[Hasil] ABSENSI PULANG BERHASIL — %s\n",
                              result.teacherName.c_str());
                oledPrint(line1, line2, result.teacherName);
            } else {
                Serial.println("[Hasil] ABSENSI PULANG BERHASIL");
                oledPrint(line1, line2);
            }
            feedbackOk();

        } else if (result.action == "already_done") {
            // ── ABSENSI SUDAH SELESAI ───────────────────────────────────────
            line1 = "ABSENSI";
            line2 = "SUDAH TERCATAT";
            Serial.println("[Hasil] ABSENSI SUDAH TERCATAT");
            oledPrint(line1, line2);
            feedbackError();

        } else {
            // Aksi tidak dikenal tapi server bilang success
            Serial.printf("[Hasil] Sukses tak dikenal: %s\n",
                          result.action.c_str());
            oledPrint("BERHASIL", result.message.substring(0, 16));
            feedbackOk();
        }

    } else {
        // ── ERROR — tentukan pesan berdasarkan action / httpCode ────────────
        if (result.action == "not_found" || result.httpCode == 400) {
            // ── BARCODE TIDAK TERDAFTAR ─────────────────────────────────────
            line1 = "BARCODE TIDAK";
            line2 = "TERDAFTAR";
            Serial.println("[Hasil] BARCODE TIDAK TERDAFTAR");
        } else if (result.httpCode == 401 || result.httpCode == 403) {
            line1 = "AKSES DITOLAK";
            line2 = "Cek konfigurasi";
            Serial.printf("[Hasil] Autentikasi gagal (HTTP %d)\n",
                          result.httpCode);
        } else if (result.httpCode <= 0) {
            line1 = "SERVER";
            line2 = "TIDAK TERHUBUNG";
            Serial.println("[Hasil] SERVER TIDAK TERHUBUNG");
        } else {
            line1 = "ERROR SERVER";
            line2 = "HTTP " + String(result.httpCode);
            Serial.printf("[Hasil] Server error HTTP %d\n", result.httpCode);
        }
        oledPrint(line1, line2);
        feedbackError();
    }
}

// =============================================================================
// Feedback Audio (Buzzer) & Visual (LED)
// =============================================================================

/** Satu beep pendek + LED hijau = SUKSES */
void feedbackOk() {
    digitalWrite(PIN_LED_ERR, LOW);
    digitalWrite(PIN_LED_OK,  HIGH);
    digitalWrite(PIN_BUZZER,  HIGH);
    delay(BEEP_SUCCESS_DURATION_MS);
    digitalWrite(PIN_BUZZER,  LOW);
    delay(LED_FEEDBACK_DURATION_MS - BEEP_SUCCESS_DURATION_MS);
    digitalWrite(PIN_LED_OK,  LOW);
}

/** Satu beep panjang + LED merah = ERROR */
void feedbackError() {
    digitalWrite(PIN_LED_OK,  LOW);
    digitalWrite(PIN_LED_ERR, HIGH);
    digitalWrite(PIN_BUZZER,  HIGH);
    delay(BEEP_ERROR_DURATION_MS);
    digitalWrite(PIN_BUZZER,  LOW);
    delay(LED_FEEDBACK_DURATION_MS - BEEP_ERROR_DURATION_MS);
    digitalWrite(PIN_LED_ERR, LOW);
}

/** LED berkedip satu kali saat menunggu respons server */
void feedbackWaiting() {
    digitalWrite(PIN_LED_OK,  HIGH);
    digitalWrite(PIN_LED_ERR, HIGH);
    delay(100);
    digitalWrite(PIN_LED_OK,  LOW);
    digitalWrite(PIN_LED_ERR, LOW);
}

// =============================================================================
// OLED Helpers (dikompilasi hanya jika USE_OLED aktif)
// =============================================================================

void initOled() {
#ifdef USE_OLED
    Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
    if (!oled.begin(SSD1306_SWITCHCAPVCC, OLED_I2C_ADDR)) {
        Serial.println("[OLED] OLED tidak ditemukan. Lewati.");
        oledReady = false;
        return;
    }
    oledReady = true;
    oled.clearDisplay();
    oled.setTextSize(1);
    oled.setTextColor(SSD1306_WHITE);
    oled.setCursor(0, 0);
    oled.println("Absensi Guru");
    oled.println("Barcode/QR Scanner");
    oled.display();
    delay(1500);
    Serial.println("[OLED] Siap.");
#endif
}

/**
 * Tampilkan maksimum 3 baris teks di OLED.
 * Jika OLED tidak aktif, hanya print ke Serial.
 */
void oledPrint(const String &line1, const String &line2, const String &line3) {
    Serial.printf("[OLED] %s | %s | %s\n",
                  line1.c_str(), line2.c_str(), line3.c_str());
#ifdef USE_OLED
    if (!oledReady) return;
    oled.clearDisplay();
    oled.setTextSize(1);
    oled.setTextColor(SSD1306_WHITE);

    // Baris 1 — sedikit lebih besar jika hanya 1-2 baris
    if (line2.isEmpty()) {
        oled.setTextSize(2);
        oled.setCursor(0, 20);
        oled.println(line1);
    } else {
        oled.setCursor(0, 0);
        oled.setTextSize(2);
        oled.println(line1);
        oled.setTextSize(1);
        oled.println(line2);
        if (!line3.isEmpty()) {
            oled.println(line3);
        }
    }
    oled.display();
#endif
}
