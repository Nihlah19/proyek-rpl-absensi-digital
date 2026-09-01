/**
 * @file api_client.h
 * @brief HTTP API Client untuk komunikasi ESP32 ↔ Server Absensi Guru
 *
 * Fitur:
 *  - POST absensi dengan payload JSON (identifier, device_id, device_secret)
 *  - Retry terbatas dengan jeda antar percobaan
 *  - Timeout per request
 *  - Parse response JSON dari server
 *  - Kirim heartbeat berkala
 *  - Device authentication via header X-Device-Secret
 *
 * Bergantung pada library:
 *  - WiFi.h      (built-in ESP32 Arduino Core)
 *  - HTTPClient.h (built-in ESP32 Arduino Core)
 *  - ArduinoJson.h (install via Library Manager: "ArduinoJson" by bblanchon)
 */

#pragma once
#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include "config.h"

// =============================================================================
// Struktur hasil response dari server
// =============================================================================
struct AttendanceResult {
    bool    success;      ///< true jika HTTP 200 dan status server = "success"
    int     httpCode;     ///< HTTP status code (200, 400, 401, 500, ...)
    String  message;      ///< Pesan dari server (misal: "Absensi Datang Berhasil")
    String  action;       ///< "check_in" | "check_out" | "already_done" | "not_found"
    String  teacherName;  ///< Nama guru jika identifier valid
    String  errorReason;  ///< Keterangan error jika gagal
};

// =============================================================================
// API Client
// =============================================================================
class ApiClient {
public:
    ApiClient() {}

    /**
     * Kirim data absensi ke server dengan retry otomatis.
     *
     * Server diharapkan merespons JSON dengan format:
     * {
     *   "status"  : "success" | "error",
     *   "action"  : "check_in" | "check_out" | "already_done" | "not_found",
     *   "message" : "...",
     *   "teacher" : "Nama Guru",   // opsional
     *   "reason"  : "..."          // opsional, jika error
     * }
     *
     * @param identifier  Barcode/QR identifier yang dibaca scanner.
     * @return AttendanceResult berisi hasil parsing response server.
     */
    AttendanceResult postAttendance(const String &identifier) {
        AttendanceResult result;
        result.success  = false;
        result.httpCode = 0;

        // Buat payload JSON
        StaticJsonDocument<256> doc;
        doc["device_id"]   = DEVICE_ID;
        doc["device_type"] = DEVICE_TYPE;
        doc["identifier"]  = identifier;

        String payload;
        serializeJson(doc, payload);

        String url = String(API_BASE_URL) + API_ATTENDANCE_EP;

        for (int attempt = 1; attempt <= API_MAX_RETRY; attempt++) {
            Serial.printf("[API] POST Absensi (percobaan %d/%d): %s\n",
                          attempt, API_MAX_RETRY, identifier.c_str());

            int httpCode = _sendPost(url, payload);
            result.httpCode = httpCode;

            if (httpCode == HTTP_CODE_OK) {
                _parseAttendanceResponse(_lastResponseBody, result);
                Serial.printf("[API] Sukses. Action: %s | Pesan: %s\n",
                              result.action.c_str(), result.message.c_str());
                return result;
            }

            // Kode khusus yang tidak perlu di-retry (client error)
            if (httpCode == 400 || httpCode == 401 || httpCode == 403) {
                Serial.printf("[API] Error %d — tidak di-retry.\n", httpCode);
                _parseAttendanceResponse(_lastResponseBody, result);
                return result;
            }

            Serial.printf("[API] Error %d. Retry dalam %lu ms...\n",
                          httpCode, API_RETRY_DELAY_MS);
            delay(API_RETRY_DELAY_MS);
        }

        // Semua percobaan gagal
        result.message     = "Server tidak merespons setelah " +
                             String(API_MAX_RETRY) + " percobaan";
        result.errorReason = "timeout_or_server_error";
        Serial.println("[API] Semua percobaan gagal.");
        return result;
    }

    /**
     * Kirim heartbeat ke server untuk menandakan perangkat aktif.
     * @return true jika server merespons HTTP 200.
     */
    bool sendHeartbeat() {
        StaticJsonDocument<128> doc;
        doc["device_id"]   = DEVICE_ID;
        doc["device_type"] = DEVICE_TYPE;
        doc["event"]       = "heartbeat";
        doc["rssi"]        = WiFi.RSSI();
        doc["uptime_s"]    = millis() / 1000;

        String payload;
        serializeJson(doc, payload);

        String url = String(API_BASE_URL) + API_HEARTBEAT_EP;
        int code   = _sendPost(url, payload);

        Serial.printf("[API] Heartbeat → HTTP %d\n", code);
        return (code == HTTP_CODE_OK);
    }

private:
    String _lastResponseBody;

    /**
     * Eksekusi HTTP POST.
     * @return HTTP status code, atau nilai negatif jika koneksi gagal.
     */
    int _sendPost(const String &url, const String &payload) {
        HTTPClient http;
        http.begin(url);
        http.setTimeout(API_HTTP_TIMEOUT_MS);
        http.addHeader("Content-Type", "application/json");

        // Device authentication via custom header
        http.addHeader("X-Device-ID",     DEVICE_ID);
        http.addHeader("X-Device-Secret", DEVICE_SECRET_KEY);

        int code = http.POST(payload);
        _lastResponseBody = (code > 0) ? http.getString() : "";
        http.end();
        return code;
    }

    /**
     * Parse body response JSON ke struct AttendanceResult.
     * Aman jika body kosong atau bukan JSON valid.
     */
    void _parseAttendanceResponse(const String &body, AttendanceResult &out) {
        if (body.isEmpty()) {
            out.message     = "Respons server kosong";
            out.errorReason = "empty_response";
            return;
        }

        StaticJsonDocument<512> doc;
        DeserializationError err = deserializeJson(doc, body);
        if (err) {
            Serial.printf("[API] JSON parse error: %s\n", err.c_str());
            out.message     = "Format respons tidak valid";
            out.errorReason = "json_parse_error";
            return;
        }

        // Ambil field dengan fallback aman
        const char *status = doc["status"] | "";
        out.success      = (strcmp(status, "success") == 0);
        out.action       = doc["action"]  | "";
        out.message      = doc["message"] | "";
        out.teacherName  = doc["teacher"] | "";
        out.errorReason  = doc["reason"]  | "";

        // Tentukan pesan tampilan berdasarkan action jika message kosong
        if (out.message.isEmpty()) {
            if (out.action == "check_in") {
                out.message = "ABSENSI DATANG BERHASIL";
            } else if (out.action == "check_out") {
                out.message = "ABSENSI PULANG BERHASIL";
            } else if (out.action == "already_done") {
                out.message = "ABSENSI SUDAH TERCATAT";
            } else if (out.action == "not_found") {
                out.message = "BARCODE TIDAK TERDAFTAR";
            } else {
                out.message = out.success ? "BERHASIL" : "GAGAL";
            }
        }
    }
};
