/**
 * @file wifi_manager.h
 * @brief Manajemen koneksi WiFi untuk ESP32 dengan reconnect otomatis
 *
 * Fitur:
 *  - Koneksi awal dengan timeout
 *  - Reconnect otomatis saat koneksi terputus
 *  - Batas percobaan reconnect + hard-restart jika melebihi batas
 *  - Non-blocking check (tidak memblokir loop utama)
 */

#pragma once
#include <Arduino.h>
#include <WiFi.h>
#include "config.h"

class WiFiManager {
public:
    WiFiManager() : _reconnectAttempts(0), _lastReconnectTime(0) {}

    /**
     * Inisialisasi dan sambungkan ke WiFi.
     * Blocking hingga terhubung atau timeout.
     *
     * @return true jika berhasil terhubung.
     */
    bool begin() {
        Serial.printf("[WiFi] Menghubungkan ke SSID: %s\n", WIFI_SSID);
        WiFi.mode(WIFI_STA);
        WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

        unsigned long startTime = millis();
        while (WiFi.status() != WL_CONNECTED) {
            if (millis() - startTime > WIFI_CONNECT_TIMEOUT_MS) {
                Serial.println("[WiFi] Timeout — gagal terhubung.");
                return false;
            }
            delay(500);
            Serial.print(".");
        }

        Serial.println();
        _printConnected();
        _reconnectAttempts = 0;
        return true;
    }

    /**
     * Harus dipanggil dari loop() secara rutin.
     * Mengelola reconnect non-blocking jika koneksi terputus.
     *
     * @return true jika WiFi sedang terhubung.
     */
    bool loop() {
        if (WiFi.status() == WL_CONNECTED) {
            _reconnectAttempts = 0;
            return true;
        }

        unsigned long now = millis();
        if (now - _lastReconnectTime < WIFI_RECONNECT_INTERVAL_MS) {
            return false;  // belum waktunya coba ulang
        }
        _lastReconnectTime = now;
        _reconnectAttempts++;

        Serial.printf("[WiFi] Koneksi terputus. Percobaan reconnect %d/%d...\n",
                      _reconnectAttempts, WIFI_MAX_RECONNECT_ATTEMPTS);

        WiFi.disconnect();
        WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

        // Tunggu sebentar secara blocking (800ms maks) untuk cek hasil
        unsigned long t = millis();
        while (millis() - t < 800) {
            if (WiFi.status() == WL_CONNECTED) {
                Serial.println("[WiFi] Reconnect berhasil.");
                _printConnected();
                _reconnectAttempts = 0;
                return true;
            }
            delay(100);
        }

        if (_reconnectAttempts >= WIFI_MAX_RECONNECT_ATTEMPTS) {
            Serial.println("[WiFi] Melebihi batas percobaan. Restart ESP32...");
            delay(1000);
            ESP.restart();
        }

        return false;
    }

    /** @return true jika WiFi terhubung */
    bool isConnected() const {
        return WiFi.status() == WL_CONNECTED;
    }

    /** @return IP address sebagai String */
    String getIP() const {
        return WiFi.localIP().toString();
    }

    /** @return RSSI sinyal WiFi (dBm) */
    int getRSSI() const {
        return WiFi.RSSI();
    }

private:
    int           _reconnectAttempts;
    unsigned long _lastReconnectTime;

    void _printConnected() const {
        Serial.printf("[WiFi] Terhubung! IP: %s | RSSI: %d dBm\n",
                      WiFi.localIP().toString().c_str(),
                      WiFi.RSSI());
    }
};
