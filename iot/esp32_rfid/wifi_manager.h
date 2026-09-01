/**
 * @file wifi_manager.h
 * @brief Manajemen koneksi WiFi dengan auto-reconnect untuk ESP32
 *
 * Fitur:
 *  - Koneksi awal blocking dengan timeout yang dapat dikonfigurasi
 *  - Reconnect otomatis non-blocking dari loop() saat koneksi hilang
 *  - Batas maksimum percobaan sebelum hard-restart (ESP.restart())
 *  - Query status dan info koneksi (IP, RSSI)
 *
 * Semua konstanta dikonfigurasi melalui config.h.
 */

#pragma once
#include <Arduino.h>
#include <WiFi.h>
#include "config.h"

class WiFiManager {
public:
    WiFiManager()
        : _reconnectAttempts(0),
          _lastReconnectTime(0)
    {}

    /**
     * Hubungkan ke WiFi saat boot. Bersifat blocking hingga terhubung
     * atau mencapai WIFI_CONNECT_TIMEOUT_MS.
     *
     * @return true jika berhasil terhubung dalam batas waktu.
     */
    bool begin() {
        Serial.printf("[WiFi] Menghubungkan ke \"%s\"", WIFI_SSID);
        WiFi.mode(WIFI_STA);
        WiFi.setAutoReconnect(false);  // kita kelola sendiri
        WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

        unsigned long start = millis();
        while (WiFi.status() != WL_CONNECTED) {
            if (millis() - start >= WIFI_CONNECT_TIMEOUT_MS) {
                Serial.println("\n[WiFi] Timeout — gagal terhubung.");
                return false;
            }
            delay(500);
            Serial.print(".");
        }

        Serial.println();
        _printInfo("Terhubung");
        _reconnectAttempts = 0;
        return true;
    }

    /**
     * Panggil dari loop() setiap iterasi.
     * Mengelola deteksi putus koneksi dan proses reconnect secara non-blocking.
     *
     * @return true jika WiFi sedang terhubung (sebelum atau sesudah reconnect).
     */
    bool loop() {
        if (WiFi.status() == WL_CONNECTED) {
            _reconnectAttempts = 0;
            return true;
        }

        unsigned long now = millis();
        if (now - _lastReconnectTime < WIFI_RECONNECT_INTERVAL_MS) {
            return false;  // belum saatnya coba lagi
        }

        _lastReconnectTime = now;
        _reconnectAttempts++;

        Serial.printf("[WiFi] Koneksi hilang. Reconnect %d/%d...\n",
                      _reconnectAttempts, WIFI_MAX_RECONNECT_ATTEMPTS);

        WiFi.disconnect();
        delay(100);
        WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

        // Tunggu singkat (non-blocking gaya polling cepat)
        unsigned long t = millis();
        while (millis() - t < 1000) {
            if (WiFi.status() == WL_CONNECTED) {
                _printInfo("Reconnect berhasil");
                _reconnectAttempts = 0;
                return true;
            }
            delay(100);
        }

        if (_reconnectAttempts >= WIFI_MAX_RECONNECT_ATTEMPTS) {
            Serial.println("[WiFi] Melebihi batas percobaan. Restart...");
            delay(500);
            ESP.restart();
        }

        return false;
    }

    /** @return true jika WiFi saat ini terhubung */
    bool isConnected() const {
        return WiFi.status() == WL_CONNECTED;
    }

    /** @return Alamat IP lokal sebagai String */
    String localIP() const {
        return WiFi.localIP().toString();
    }

    /** @return Kekuatan sinyal WiFi (dBm) */
    int rssi() const {
        return WiFi.RSSI();
    }

private:
    int           _reconnectAttempts;
    unsigned long _lastReconnectTime;

    void _printInfo(const char *label) const {
        Serial.printf("[WiFi] %s! IP: %s | RSSI: %d dBm\n",
                      label,
                      WiFi.localIP().toString().c_str(),
                      WiFi.RSSI());
    }
};
