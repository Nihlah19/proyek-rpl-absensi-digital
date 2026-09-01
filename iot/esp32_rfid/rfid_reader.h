/**
 * @file rfid_reader.h
 * @brief Wrapper MFRC522 untuk ESP32 — baca UID kartu RFID
 *
 * Mengelola:
 *  - Inisialisasi SPI + MFRC522
 *  - Deteksi kartu baru (non-blocking di loop)
 *  - Baca & normalisasi UID ke string hex uppercase (misal: "A1B2C3D4")
 *  - Debounce — cegah satu tap terbaca berkali-kali
 *  - Halt kartu setelah baca agar reader siap untuk kartu berikutnya
 *
 * Library yang dibutuhkan:
 *  - MFRC522 by GithubCommunity (install via Library Manager)
 *  - SPI.h (built-in Arduino/ESP32)
 */

#pragma once
#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>
#include "config.h"

class RfidReader {
public:
    /**
     * @param ssPin   GPIO Slave Select (SDA) MFRC522
     * @param rstPin  GPIO Reset MFRC522
     */
    RfidReader(uint8_t ssPin, uint8_t rstPin)
        : _mfrc522(ssPin, rstPin),
          _lastUid(""),
          _lastReadTime(0)
    {}

    /**
     * Inisialisasi SPI bus dan MFRC522.
     * Harus dipanggil sekali di setup() SETELAH Serial.begin().
     *
     * @return true jika MFRC522 terdeteksi dan siap.
     */
    bool begin() {
        SPI.begin();
        _mfrc522.PCD_Init();
        delay(50);  // beri waktu PCD stabil

        // Verifikasi modul terhubung dengan cek versi firmware
        byte ver = _mfrc522.PCD_ReadRegister(MFRC522::VersionReg);
        if (ver == 0x00 || ver == 0xFF) {
            Serial.println("[RFID] ⚠ MFRC522 tidak terdeteksi! Cek wiring.");
            return false;
        }
        Serial.printf("[RFID] MFRC522 siap. Firmware version: 0x%02X\n", ver);
        return true;
    }

    /**
     * Cek dan baca kartu RFID. Non-blocking — panggil dari loop().
     *
     * Logika debounce:
     *  - Jika UID yang sama terdeteksi dalam RFID_DEBOUNCE_MS, abaikan.
     *  - Setelah UID berbeda, atau setelah timeout debounce habis,
     *    proses UID baru.
     *
     * @param[out] uid  String UID kartu (hex uppercase, tanpa spasi).
     *                  Contoh: "A1B2C3D4" atau "1A2B3C4D5E6F7080"
     * @return true jika ada kartu baru yang valid dan siap diproses.
     */
    bool readCard(String &uid) {
        // Tidak ada kartu baru yang masuk ke field
        if (!_mfrc522.PICC_IsNewCardPresent()) return false;
        // Tidak bisa baca serial kartu
        if (!_mfrc522.PICC_ReadCardSerial()) return false;

        String currentUid = _extractUid();

        // Validasi: UID tidak boleh kosong
        if (currentUid.isEmpty()) {
            _haltCard();
            return false;
        }

        unsigned long now = millis();

        // Debounce: abaikan UID yang sama jika belum lewat debounce timeout
        if (currentUid == _lastUid &&
            (now - _lastReadTime) < RFID_DEBOUNCE_MS) {
            Serial.printf("[RFID] Debounce aktif untuk UID %s (%lu ms sisa)\n",
                          currentUid.c_str(),
                          RFID_DEBOUNCE_MS - (now - _lastReadTime));
            _haltCard();
            return false;
        }

        // UID baru atau debounce sudah habis — proses
        _lastUid      = currentUid;
        _lastReadTime = now;
        uid           = currentUid;

        _haltCard();

        Serial.printf("[RFID] Kartu terdeteksi: %s (PICC type: %s)\n",
                      uid.c_str(),
                      _mfrc522.PICC_GetTypeName(
                          _mfrc522.PICC_GetType(
                              _mfrc522.uid.sak)));
        return true;
    }

    /**
     * Reset debounce secara manual.
     * Berguna setelah server mengembalikan error agar tap ulang tidak diblokir.
     */
    void resetDebounce() {
        _lastUid      = "";
        _lastReadTime = 0;
    }

    /**
     * Self-test: print versi firmware MFRC522 ke Serial.
     * Berguna untuk debugging koneksi SPI.
     */
    void selfTest() {
        Serial.println("[RFID] Self-test...");
        _mfrc522.PCD_DumpVersionToSerial();
    }

private:
    MFRC522       _mfrc522;
    String        _lastUid;
    unsigned long _lastReadTime;

    /**
     * Ekstrak UID dari struct _mfrc522.uid dan normalisasi ke
     * string hex uppercase tanpa spasi.
     *
     * Contoh output: "A1B2C3D4" (kartu 4-byte) atau "1A2B3C4D5E6F7080" (7-byte)
     */
    String _extractUid() {
        String result = "";
        result.reserve(_mfrc522.uid.size * 2);
        for (byte i = 0; i < _mfrc522.uid.size; i++) {
            if (_mfrc522.uid.uidByte[i] < 0x10) result += '0';
            result += String(_mfrc522.uid.uidByte[i], HEX);
        }
        result.toUpperCase();
        return result;
    }

    /**
     * Halt kartu dan stop enkripsi agar reader siap untuk kartu berikutnya.
     * HARUS dipanggil setelah selesai memproses satu kartu.
     */
    void _haltCard() {
        _mfrc522.PICC_HaltA();
        _mfrc522.PCD_StopCrypto1();
    }
};
