/**
 * @file config.h
 * @brief Konfigurasi terpusat firmware ESP32 RFID — Absensi Siswa
 *
 * INI ADALAH SATU-SATUNYA FILE YANG PERLU DIUBAH untuk menyesuaikan
 * firmware dengan lingkungan deployment Anda. Semua kredensial, pin
 * hardware, dan konstanta waktu dikumpulkan di sini.
 *
 * Cari komentar [SESUAIKAN] untuk bagian yang wajib diubah.
 *
 * =============================================================================
 * WIRING DIAGRAM — ESP32 + MFRC522
 * =============================================================================
 *
 *  MFRC522 Pin    ESP32 Pin (DevKit-V1)    Keterangan
 *  ───────────    ─────────────────────    ──────────────────────────────────
 *  SDA (SS)   →  GPIO5  (VSPI_CS0)        Chip Select / Slave Select
 *  SCK        →  GPIO18 (VSPI_CLK)        SPI Clock
 *  MOSI       →  GPIO23 (VSPI_MOSI)       Master Out Slave In
 *  MISO       →  GPIO19 (VSPI_MISO)       Master In Slave Out
 *  IRQ        →  (tidak digunakan)        Interrupt (opsional, tidak dipakai)
 *  GND        →  GND                      Ground
 *  RST        →  GPIO22                   Reset — bisa diganti, lihat PIN_RFID_RST
 *  3.3V       →  3.3V                     ⚠ WAJIB 3.3V, BUKAN 5V!
 *
 *  ⚠ PERINGATAN TEGANGAN:
 *  - MFRC522 beroperasi pada 3.3V. Memberi 5V AKAN merusak modul.
 *  - Semua jalur SPI sudah kompatibel level dengan ESP32 (keduanya 3.3V).
 *  - Tidak perlu level-shifter.
 *
 *  ESP32 Pin    Komponen              Keterangan
 *  ─────────    ──────────────────    ──────────────────────────────────────
 *  GPIO25   →  + Buzzer Aktif        Buzzer aktif (active buzzer, bukan pasif)
 *  GND      →  - Buzzer Aktif
 *
 *  GPIO26   →  Anode LED Hijau   →  [Resistor 220Ω]  →  Cathode  →  GND
 *  GPIO27   →  Anode LED Merah   →  [Resistor 220Ω]  →  Cathode  →  GND
 *
 *  GPIO21 (SDA) →  SDA OLED SSD1306  (opsional, aktifkan #define USE_OLED)
 *  GPIO4  (SCL) →  SCL OLED SSD1306  (opsional)
 *               [GPIO22 dipakai RST RFID, jadi OLED SCL geser ke GPIO4]
 *  3.3V     →  VCC OLED
 *  GND      →  GND OLED
 *
 * =============================================================================
 * CATATAN MFRC522 MULTI-CARD:
 * Untuk menghubungkan lebih dari satu MFRC522 ke satu ESP32:
 *  - Gunakan SDA (SS) berbeda untuk tiap modul.
 *  - SCK, MOSI, MISO dipakai bersama (SPI bus).
 *  - RST bisa dipakai bersama atau dipisah.
 * =============================================================================
 */

#pragma once

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 1: IDENTITAS PERANGKAT
// [SESUAIKAN] Tiap unit fisik harus punya DEVICE_CODE unik
// ─────────────────────────────────────────────────────────────────────────────

/** ID unik perangkat ini. Dikirim ke server pada setiap request. */
#define DEVICE_CODE        "ESP_RFID_01"    // [SESUAIKAN]

/** Tipe perangkat — jangan diubah kecuali ada perubahan arsitektur */
#define DEVICE_TYPE        "rfid"

/**
 * Kunci rahasia otentikasi perangkat.
 * Harus cocok dengan nilai yang dikonfigurasi di server.
 * [SESUAIKAN] Gunakan string acak yang panjang dan unik.
 */
#define DEVICE_SECRET_KEY  "RFID_SECRET_CHANGE_THIS_NOW"  // [SESUAIKAN]

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 2: KONFIGURASI WiFi
// [SESUAIKAN]
// ─────────────────────────────────────────────────────────────────────────────

#define WIFI_SSID          "NAMA_WIFI_ANDA"   // [SESUAIKAN]
#define WIFI_PASSWORD      "PASSWORD_WIFI"    // [SESUAIKAN]

/** Timeout koneksi WiFi pertama kali sejak boot (ms) */
#define WIFI_CONNECT_TIMEOUT_MS      15000UL
/** Interval antara percobaan reconnect (ms) */
#define WIFI_RECONNECT_INTERVAL_MS   5000UL
/** Maks percobaan reconnect berturut-turut sebelum ESP32 di-restart */
#define WIFI_MAX_RECONNECT_ATTEMPTS  10

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 3: KONFIGURASI API SERVER
// [SESUAIKAN] Ganti dengan IP / domain server Anda
// ─────────────────────────────────────────────────────────────────────────────

#define API_BASE_URL       "http://192.168.1.100/AbsensiRFID/web/api/v1"  // [SESUAIKAN]
#define API_ATTENDANCE_EP  "/attendance.php"
#define API_HEARTBEAT_EP   "/device.php"

/** Timeout per request HTTP (ms). Jangan terlalu kecil di jaringan lambat. */
#define API_HTTP_TIMEOUT_MS    8000
/** Maksimum percobaan POST per tap kartu sebelum menyerah */
#define API_MAX_RETRY          3
/** Jeda antar percobaan retry (ms) */
#define API_RETRY_DELAY_MS     2000UL

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 4: PIN HARDWARE
// [SESUAIKAN] Sesuaikan dengan skema rangkaian Anda
// ─────────────────────────────────────────────────────────────────────────────

// --- MFRC522 (SPI) ---
// SPI bus: SCK=GPIO18, MOSI=GPIO23, MISO=GPIO19 (default VSPI, tidak perlu diubah)
#define PIN_RFID_SS     5    // Slave Select / SDA pin MFRC522
#define PIN_RFID_RST    22   // Reset pin MFRC522

// --- Buzzer Aktif ---
#define PIN_BUZZER      25

// --- LED Indikator ---
#define PIN_LED_OK      26   // LED Hijau — Absensi berhasil
#define PIN_LED_ERR     27   // LED Merah — Gagal / tidak terdaftar

// --- OLED SSD1306 I2C (Opsional) ---
// Aktifkan dengan uncomment baris berikut:
// #define USE_OLED
#define OLED_SDA_PIN    21
#define OLED_SCL_PIN    4    // GPIO4 (GPIO22 dipakai RST RFID)
#define OLED_I2C_ADDR   0x3C
#define OLED_WIDTH      128
#define OLED_HEIGHT     64

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 5: LOGIKA RFID & DEBOUNCE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Debounce RFID (ms).
 * UID yang sama diabaikan jika terdeteksi dalam rentang waktu ini.
 * Mencegah satu tap fisik terbaca berkali-kali karena kartu masih dekat reader.
 * Nilai: 3000 ms = 3 detik cooldown setelah satu tap berhasil diproses.
 */
#define RFID_DEBOUNCE_MS       3000UL

/**
 * Timeout tunggu kartu tidak ada lagi (ms).
 * Setelah kartu terdeteksi dan diproses, tunggu sampai kartu dijauhkan
 * sebelum reader siap untuk tap berikutnya.
 */
#define RFID_CARD_GONE_MS      500UL

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 6: HEARTBEAT
// ─────────────────────────────────────────────────────────────────────────────

/** Interval kirim heartbeat ke server (ms). Default: 5 menit */
#define HEARTBEAT_INTERVAL_MS  300000UL

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 7: DURASI FEEDBACK AUDIO/VISUAL (ms)
// ─────────────────────────────────────────────────────────────────────────────

#define BEEP_SUCCESS_MS       150   // Beep pendek — berhasil
#define BEEP_ERROR_MS         600   // Beep panjang — error
#define LED_ON_DURATION_MS   2000   // Durasi LED menyala setelah feedback
