/**
 * @file scanner.h
 * @brief Abstraksi layer scanner barcode/QR untuk ESP32
 *
 * Menyediakan interface (ScannerBase) yang seragam sehingga tipe scanner
 * dapat diganti hanya dengan mengganti implementasi konkret, tanpa mengubah
 * kode di lapisan API client atau main loop.
 *
 * CARA MENAMBAH TIPE SCANNER BARU:
 * 1. Buat class turunan dari ScannerBase.
 * 2. Implementasikan semua pure-virtual method.
 * 3. Ganti instantiasi di esp32_barcode.ino.
 *
 * DRIVER YANG TERSEDIA:
 *  - UartScannerDriver : scanner UART TTL generik (default, digunakan sekarang)
 *
 * DRIVER YANG DAPAT DITAMBAHKAN [SESUAIKAN jika model scanner sudah diketahui]:
 *  - Hid-over-USB scanner (periferal USB HID)
 *  - Scanner dengan protokol proprietary via SPI/I2C
 *  - Scanner yang memerlukan command inisialisasi khusus
 *
 * =============================================================================
 * CATATAN ADAPTASI MODEL SCANNER [SESUAIKAN]
 * =============================================================================
 * UartScannerDriver berfungsi untuk scanner UART TTL generik yang:
 *  - Mengirim data barcode secara otomatis saat berhasil baca
 *  - Mengakhiri transmisi dengan CR (\r), LF (\n), atau keduanya (CRLF)
 *  - Tidak memerlukan command inisialisasi khusus
 *
 * Jika scanner Anda memiliki perilaku berbeda (misalnya: memerlukan command
 * "trigger" untuk memulai scan, atau paket data berbentuk frame biner),
 * override method begin() dan readScan() di subclass baru.
 *
 * Contoh model yang kompatibel dengan UartScannerDriver (BELUM DIVERIFIKASI,
 * hanya contoh referensi — konfirmasikan dengan datasheet masing-masing):
 *  - GM65 (Waveshare)
 *  - ZBar module
 *  - ZEBEX Z-3220
 *  - Honeywell Voyager (mode serial)
 * =============================================================================
 */

#pragma once
#include <Arduino.h>
#include <HardwareSerial.h>
#include "config.h"

// =============================================================================
// Interface abstrak — jangan ubah kecuali mengubah seluruh API
// =============================================================================
class ScannerBase {
public:
    virtual ~ScannerBase() = default;

    /**
     * Inisialisasi hardware scanner.
     * Dipanggil sekali di setup().
     * @return true jika inisialisasi berhasil.
     */
    virtual bool begin() = 0;

    /**
     * Baca satu frame scan dari scanner.
     * Non-blocking; mengembalikan string kosong jika belum ada data.
     *
     * @param[out] out  String tempat menyimpan hasil scan.
     * @return true jika ada data baru yang valid, false jika belum/tidak ada.
     */
    virtual bool readScan(String &out) = 0;

    /**
     * Periksa apakah scanner terhubung dan siap.
     * Implementasi default: selalu true (UART tidak memiliki feedback kehadiran).
     * [SESUAIKAN] Override jika scanner mendukung status query.
     */
    virtual bool isConnected() { return true; }

    /**
     * Reset/flush buffer scanner.
     * Berguna setelah error atau debounce.
     */
    virtual void flush() = 0;
};

// =============================================================================
// Implementasi Konkret: UART TTL Scanner (Generik)
// =============================================================================
class UartScannerDriver : public ScannerBase {
public:
    /**
     * @param serial   Referensi ke HardwareSerial (Serial, Serial1, Serial2)
     * @param rxPin    GPIO RX ESP32
     * @param txPin    GPIO TX ESP32
     * @param baudRate Baud rate UART — sesuaikan dengan modul scanner
     */
    UartScannerDriver(HardwareSerial &serial,
                      int rxPin,
                      int txPin,
                      unsigned long baudRate)
        : _serial(serial),
          _rxPin(rxPin),
          _txPin(txPin),
          _baudRate(baudRate)
    {}

    bool begin() override {
        _serial.begin(_baudRate, SERIAL_8N1, _rxPin, _txPin);
        delay(100);  // beri waktu UART stabil
        _serial.flush();
        _buffer.reserve(SCAN_MAX_LENGTH + 4);
        return true;
    }

    /**
     * Membaca stream UART dengan teknik end-of-frame:
     * - Karakter newline (\n) atau carriage-return (\r) → frame selesai.
     * - Atau jika jeda antar karakter > SCANNER_READ_TIMEOUT_MS.
     *
     * [SESUAIKAN] Jika scanner Anda menggunakan delimiter berbeda (misalnya
     * STX/ETX atau checksum), modifikasi logika pembacaan di sini.
     */
    bool readScan(String &out) override {
        while (_serial.available()) {
            char c = (char)_serial.read();
            _lastCharTime = millis();

            // Karakter delimiter: akhiri frame
            if (c == '\r' || c == '\n') {
                if (_buffer.length() >= SCAN_MIN_LENGTH) {
                    out = _buffer;
                    out.trim();
                    _buffer = "";
                    return true;
                }
                _buffer = "";  // frame terlalu pendek, buang
                continue;
            }

            // Hanya terima karakter ASCII yang dapat dicetak
            if (c >= 32 && c <= 126) {
                if ((int)_buffer.length() < SCAN_MAX_LENGTH) {
                    _buffer += c;
                }
            }
        }

        // Timeout antar karakter — frame dianggap selesai
        if (_buffer.length() > 0 &&
            (millis() - _lastCharTime) > SCANNER_READ_TIMEOUT_MS) {
            if ((int)_buffer.length() >= SCAN_MIN_LENGTH) {
                out = _buffer;
                out.trim();
                _buffer = "";
                return true;
            }
            _buffer = "";  // terlalu pendek, buang
        }

        return false;
    }

    void flush() override {
        _buffer = "";
        while (_serial.available()) _serial.read();
    }

private:
    HardwareSerial   &_serial;
    int               _rxPin;
    int               _txPin;
    unsigned long     _baudRate;
    String            _buffer;
    unsigned long     _lastCharTime = 0;
};
