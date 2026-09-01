/**
 * @file api_client.h
 * @brief HTTP API Client — Absensi Siswa via RFID
 *
 * Mengelola:
 *  - POST absensi ke server dengan payload JSON
 *  - Retry otomatis dengan batas maksimum dan jeda
 *  - Timeout per request (mencegah hang)
 *  - Device authentication via header HTTP kustom
 *  - Parse response JSON menjadi struct AttendanceResult
 *  - Kirim heartbeat berkala
 *
 * ⚠ PRINSIP KEAMANAN:
 *  - Jika server tidak tersedia / timeout / HTTP error → result.success = false
 *  - TIDAK PERNAH mengasumsikan absensi berhasil tanpa konfirmasi HTTP 200
 *    dari server.
 *
 * Library:
 *  - WiFi.h, HTTPClient.h (built-in ESP32 Arduino Core)
 *  - ArduinoJson by Benoit Blanchon (install via Library Manager, versi 6.x / 7.x)
 *
 * Format request JSON yang dikirim ke server:
 * {
 *   "device_code" : "ESP_RFID_01",
 *   "device_type" : "rfid",
 *   "uid"         : "A1B2C3D4"
 * }
 *
 * Format response JSON yang diharapkan dari server:
 * {
 *   "status"   : "success" | "error",
 *   "code"     : "check_in" | "already_today" | "not_found" | ...,
 *   "message"  : "...",
 *   "name"     : "Nama Siswa",      // ada jika status = success
 *   "class"    : "XII RPL 1",       // ada jika status = success
 *   "time"     : "07:30",           // ada jika status = success
 *   "att_status": "Hadir" | "Terlambat",  // ada jika status = success
 *   "reason"   : "..."              // ada jika status = error
 * }
 */

#pragma once
#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include "config.h"

// =============================================================================
// Struct hasil response server
// =============================================================================

/** Kode semantik dari server — mempermudah branching di main loop */
enum class AttendanceCode : uint8_t {
    SUCCESS_CHECK_IN,   ///< Absensi masuk berhasil dicatat
    ALREADY_TODAY,      ///< Sudah absen hari ini
    NOT_FOUND,          ///< UID tidak terdaftar di database
    SERVER_ERROR,       ///< Error sisi server (5xx)
    AUTH_FAILED,        ///< Otentikasi perangkat ditolak (401/403)
    NETWORK_ERROR,      ///< Tidak bisa mencapai server sama sekali
    UNKNOWN             ///< Kode tidak dikenali
};

struct AttendanceResult {
    bool           success;      ///< true hanya jika server konfirmasi sukses
    int            httpCode;     ///< Kode HTTP (200, 400, 500, atau <=0 jika network error)
    AttendanceCode code;         ///< Kode semantik hasil
    String         message;      ///< Pesan dari server (untuk display)
    String         studentName;  ///< Nama siswa (jika success)
    String         studentClass; ///< Kelas siswa (jika success)
    String         time;         ///< Jam absen (jika success), format "HH:MM"
    String         attStatus;    ///< Status kehadiran: "Hadir" / "Terlambat"
    String         reason;       ///< Keterangan error (jika gagal)
};

// =============================================================================
// API Client
// =============================================================================

class ApiClient {
public:
    ApiClient() {}

    /**
     * Kirim data absensi ke server.
     *
     * Melakukan hingga API_MAX_RETRY percobaan. Setiap percobaan memiliki
     * timeout API_HTTP_TIMEOUT_MS. Jika semua gagal, result.success = false
     * dan result.code = NETWORK_ERROR.
     *
     * @param uid  UID kartu RFID hasil normalisasi (hex uppercase, misal "A1B2C3D4")
     * @return     AttendanceResult berisi seluruh data response server.
     */
    AttendanceResult postAttendance(const String &uid) {
        AttendanceResult result;
        result.success  = false;
        result.httpCode = 0;
        result.code     = AttendanceCode::NETWORK_ERROR;

        // Bangun payload JSON
        // Kapasitas: {"device_code":"xxx","device_type":"rfid","uid":"xxxxxxxxxxxxxxxx"}
        StaticJsonDocument<200> reqDoc;
        reqDoc["device_code"] = DEVICE_CODE;
        reqDoc["device_type"] = DEVICE_TYPE;
        reqDoc["rfid_uid"]    = uid;
        String payload;
        serializeJson(reqDoc, payload);

        String url = String(API_BASE_URL) + API_ATTENDANCE_EP;

        for (int attempt = 1; attempt <= API_MAX_RETRY; attempt++) {
            Serial.printf("[API] POST absensi UID=%s (percobaan %d/%d)\n",
                          uid.c_str(), attempt, API_MAX_RETRY);

            int httpCode = _doPost(url, payload);
            result.httpCode = httpCode;

            if (httpCode == HTTP_CODE_OK) {
                _parseAttendance(_lastBody, result);
                Serial.printf("[API] Berhasil. code=%d | name=%s\n",
                              (int)result.code, result.studentName.c_str());
                return result;
            }

            // Client error — tidak perlu retry
            if (httpCode == 400 || httpCode == 401 || httpCode == 403) {
                Serial.printf("[API] HTTP %d — tidak di-retry.\n", httpCode);
                _parseAttendance(_lastBody, result);
                if (httpCode == 401 || httpCode == 403) {
                    result.code    = AttendanceCode::AUTH_FAILED;
                    result.message = "Otentikasi perangkat gagal";
                }
                return result;
            }

            // Server error atau network error — retry
            if (attempt < API_MAX_RETRY) {
                Serial.printf("[API] HTTP %d. Retry dalam %lu ms...\n",
                              httpCode, API_RETRY_DELAY_MS);
                delay(API_RETRY_DELAY_MS);
            }
        }

        // Semua percobaan gagal
        Serial.println("[API] Semua percobaan POST gagal.");
        result.code    = AttendanceCode::NETWORK_ERROR;
        result.message = "SERVER TIDAK TERHUBUNG";
        return result;
    }

    /**
     * Kirim sinyal heartbeat ke server.
     * Digunakan untuk membuktikan perangkat aktif dan melaporkan status.
     *
     * @return true jika server membalas HTTP 200.
     */
    bool sendHeartbeat() {
        StaticJsonDocument<150> doc;
        doc["device_code"] = DEVICE_CODE;
        doc["device_type"] = DEVICE_TYPE;
        doc["event"]       = "heartbeat";
        doc["uptime_s"]    = millis() / 1000UL;
        doc["rssi"]        = WiFi.RSSI();

        String payload;
        serializeJson(doc, payload);

        String url  = String(API_BASE_URL) + API_HEARTBEAT_EP;
        int    code = _doPost(url, payload);
        Serial.printf("[API] Heartbeat → HTTP %d\n", code);
        return (code == HTTP_CODE_OK);
    }

private:
    String _lastBody;  ///< Body response dari request terakhir

    /**
     * Lakukan HTTP POST ke url dengan payload JSON.
     * Menyisipkan header autentikasi perangkat.
     *
     * @return HTTP status code, atau nilai negatif jika koneksi gagal.
     */
    int _doPost(const String &url, const String &payload) {
        HTTPClient http;

        if (!http.begin(url)) {
            Serial.println("[API] http.begin() gagal.");
            _lastBody = "";
            return -1;
        }

        http.setTimeout(API_HTTP_TIMEOUT_MS);
        http.addHeader("Content-Type",    "application/json");
        http.addHeader("X-Device-Code",   DEVICE_CODE);
        http.addHeader("X-Device-Secret", DEVICE_SECRET_KEY);

        int code = http.POST(payload);
        _lastBody = (code > 0) ? http.getString() : "";
        http.end();
        return code;
    }

    /**
     * Parse body JSON response ke AttendanceResult.
     * Toleran terhadap field yang hilang atau body kosong/invalid JSON.
     */
    void _parseAttendance(const String &body, AttendanceResult &out) {
        if (body.isEmpty()) {
            out.message = "Respons server kosong";
            out.code    = AttendanceCode::SERVER_ERROR;
            return;
        }

        // Kapasitas JSON: status + code + message + name + class + time + att_status + reason
        StaticJsonDocument<512> doc;
        DeserializationError err = deserializeJson(doc, body);
        if (err) {
            Serial.printf("[API] JSON error: %s | body: %s\n",
                          err.c_str(), body.substring(0, 80).c_str());
            out.message = "Format respons tidak valid";
            out.code    = AttendanceCode::SERVER_ERROR;
            return;
        }

        const char *status    = doc["status"]     | "";
        const char *codeStr   = doc["code"]       | "";
        out.success           = (strcmp(status, "success") == 0);
        out.message           = doc["message"]    | "";
        out.studentName       = doc["name"]       | "";
        out.studentClass      = doc["class"]      | "";
        out.time              = doc["time"]        | "";
        out.attStatus         = doc["att_status"] | "";
        out.reason            = doc["reason"]     | "";

        // Mapping code string → AttendanceCode enum
        if (strcmp(codeStr, "check_in") == 0) {
            out.code = AttendanceCode::SUCCESS_CHECK_IN;
        } else if (strcmp(codeStr, "already_today") == 0) {
            out.code = AttendanceCode::ALREADY_TODAY;
        } else if (strcmp(codeStr, "not_found") == 0) {
            out.code = AttendanceCode::NOT_FOUND;
        } else if (out.success) {
            out.code = AttendanceCode::SUCCESS_CHECK_IN;  // fallback jika code baru
        } else {
            out.code = AttendanceCode::UNKNOWN;
        }

        // Isi message default jika server tidak mengirim message
        if (out.message.isEmpty()) {
            switch (out.code) {
                case AttendanceCode::SUCCESS_CHECK_IN:
                    out.message = "ABSENSI BERHASIL"; break;
                case AttendanceCode::ALREADY_TODAY:
                    out.message = "SUDAH ABSEN HARI INI"; break;
                case AttendanceCode::NOT_FOUND:
                    out.message = "RFID TIDAK TERDAFTAR"; break;
                default:
                    out.message = out.success ? "BERHASIL" : "GAGAL"; break;
            }
        }
    }
};
