/**
 * @file config.h
 * @brief Konfigurasi terpusat untuk ESP32 Barcode/QR Absensi Guru
 *
 * Sesuaikan bagian yang ditandai [SESUAIKAN] sebelum di-flash ke perangkat.
 *
 * =============================================================================
 * WIRING DIAGRAM
 * =============================================================================
 *
 *  ESP32              Barcode/QR Scanner (UART TTL)
 *  ─────              ────────────────────────────────────────────────────────
 *  GPIO16 (RX2)  ←──  TX  (data barcode dari scanner ke ESP32)
 *  GPIO17 (TX2)  ──→  RX  (perintah dari ESP32 ke scanner, opsional)
 *  3.3V / 5V     ──→  VCC [SESUAIKAN — cek tegangan operasi modul Anda!]
 *  GND           ──→  GND
 *
 *  ⚠ PERINGATAN LEVEL TEGANGAN:
 *  - ESP32 GPIO TIDAK toleran tegangan 5V.
 *  - Jika scanner beroperasi 5V dan TX-nya mengeluarkan 5V, WAJIB pasang
 *    voltage-divider atau level-shifter (5V → 3.3V) pada jalur TX Scanner
 *    → RX2 ESP32.
 *  - Jika scanner beroperasi 3.3V, koneksi langsung aman.
 *  - Konfirmasikan dengan datasheet modul scanner Anda. [SESUAIKAN]
 *
 *  ESP32              Buzzer Aktif
 *  ─────              ──────────────────────────────────────────────────────
 *  GPIO25        ──→  + (positif, buzzer aktif/active buzzer)
 *  GND           ──→  - (negatif)
 *
 *  ESP32              LED Hijau (Indikator Sukses)
 *  ─────              ──────────────────────────────
 *  GPIO26        ──→  Anode  →  [Resistor 220Ω]  →  Cathode  →  GND
 *
 *  ESP32              LED Merah (Indikator Error)
 *  ─────              ─────────────────────────────
 *  GPIO27        ──→  Anode  →  [Resistor 220Ω]  →  Cathode  →  GND
 *
 *  ESP32              OLED SSD1306 128×64 (I2C) — OPSIONAL
 *  ─────              ──────────────────────────────────────
 *  GPIO21 (SDA)  ──→  SDA
 *  GPIO22 (SCL)  ──→  SCL
 *  3.3V          ──→  VCC
 *  GND           ──→  GND
 *
 * =============================================================================
 */

#pragma once

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 1: IDENTITAS PERANGKAT
// [SESUAIKAN] Setiap unit fisik harus memiliki DEVICE_ID unik
// ─────────────────────────────────────────────────────────────────────────────
#define DEVICE_ID           "ESP_BARCODE_01"
#define DEVICE_TYPE         "barcode"
/** Kunci otentikasi perangkat. Harus cocok dengan konfigurasi server. */
#define DEVICE_SECRET_KEY   "BARCODE_SECRET_CHANGE_THIS_KEY"  // [SESUAIKAN]

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 2: KONFIGURASI WiFi
// [SESUAIKAN]
// ─────────────────────────────────────────────────────────────────────────────
#define WIFI_SSID           "NAMA_WIFI_ANDA"    // [SESUAIKAN]
#define WIFI_PASSWORD       "PASSWORD_WIFI"     // [SESUAIKAN]

/** Timeout koneksi WiFi awal (ms) */
#define WIFI_CONNECT_TIMEOUT_MS      15000UL
/** Interval coba sambung ulang setelah putus (ms) */
#define WIFI_RECONNECT_INTERVAL_MS   5000UL
/** Maks percobaan reconnect sebelum hard-restart ESP32 */
#define WIFI_MAX_RECONNECT_ATTEMPTS  10

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 3: KONFIGURASI API SERVER
// [SESUAIKAN] Ganti base URL sesuai IP/domain server
// ─────────────────────────────────────────────────────────────────────────────
#define API_BASE_URL        "http://192.168.1.100/AbsensiRFID/web/api/v1"  // [SESUAIKAN]
#define API_ATTENDANCE_EP   "/attendance.php"
#define API_HEARTBEAT_EP    "/device.php"

/** Timeout tiap request HTTP (ms) */
#define API_HTTP_TIMEOUT_MS    8000
/** Maks percobaan POST sebelum menyerah */
#define API_MAX_RETRY          3
/** Jeda antar percobaan retry (ms) */
#define API_RETRY_DELAY_MS     2000UL

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 4: PIN HARDWARE
// [SESUAIKAN] Sesuaikan dengan skema breadboard/PCB Anda
// ─────────────────────────────────────────────────────────────────────────────

// --- UART Scanner ---
// ESP32 DevKit-V1: Serial2 default = GPIO16 (RX) / GPIO17 (TX)
#define SCANNER_UART_RX_PIN    16   // [SESUAIKAN jika menggunakan GPIO lain]
#define SCANNER_UART_TX_PIN    17   // [SESUAIKAN]
/**
 * Baud rate UART. WAJIB sesuai datasheet modul scanner Anda.
 * Contoh umum: GM65 = 9600, Datalogic = 115200 [SESUAIKAN]
 */
#define SCANNER_BAUD_RATE      9600

// --- Buzzer ---
#define PIN_BUZZER             25

// --- LED ---
#define PIN_LED_OK             26   // LED Hijau - Sukses
#define PIN_LED_ERR            27   // LED Merah - Error/Gagal

// --- OLED (Opsional) ---
// Uncomment baris berikut untuk mengaktifkan OLED:
// #define USE_OLED
#define OLED_I2C_ADDR          0x3C
#define OLED_SCREEN_WIDTH      128
#define OLED_SCREEN_HEIGHT     64

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 5: LOGIKA SCANNER & DEBOUNCE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Debounce: abaikan scan identifier yang SAMA dalam rentang waktu ini.
 * Mencegah double-entry jika barcode terbaca dua kali berturut-turut.
 * Nilai ms: 3000 = 3 detik cooldown per barcode yang sama.
 */
#define SCAN_DEBOUNCE_MS          3000UL

/**
 * Timeout per karakter UART (ms).
 * Setelah karakter terakhir diterima, tunggu durasi ini sebelum frame
 * dianggap lengkap (end-of-frame detection).
 */
#define SCANNER_READ_TIMEOUT_MS   100

/** Panjang minimum identifier yang dianggap valid (karakter) */
#define SCAN_MIN_LENGTH           4
/** Panjang maksimum buffer identifier */
#define SCAN_MAX_LENGTH           64

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 6: HEARTBEAT
// ─────────────────────────────────────────────────────────────────────────────
/** Interval kirim heartbeat ke server (ms). Default: 5 menit = 300000 */
#define HEARTBEAT_INTERVAL_MS     300000UL

// ─────────────────────────────────────────────────────────────────────────────
// BAGIAN 7: DURASI FEEDBACK AUDIO/VISUAL (ms)
// ─────────────────────────────────────────────────────────────────────────────
#define BEEP_SUCCESS_DURATION_MS   200
#define BEEP_ERROR_DURATION_MS     600
#define LED_FEEDBACK_DURATION_MS   1500
