#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <EEPROM.h>
#include <ESP8266WebServer.h>
#include <DNSServer.h>

// ================= KONFIGURASI HARDWARE =================
#define RST_PIN  D3
#define SS_PIN   D4
#define I2C_SDA_PIN D2
#define I2C_SCL_PIN D1

// ================= DISPLAY (DUAL ADDRESS 0x27 / 0x3F AUTO-DETECT) =================
LiquidCrystal_I2C lcd27(0x27, 16, 2);
LiquidCrystal_I2C lcd3F(0x3F, 16, 2);
LiquidCrystal_I2C* activeLcd = &lcd27;
#define lcd (*activeLcd)

MFRC522 mfrc522(SS_PIN, RST_PIN);

// ================= KONFIGURASI DEFAULT =================
const char* DEFAULT_SSID = "Absensi";
const char* DEFAULT_PASS = "123456789";
const char* DEFAULT_HOST = "http://10.21.92.173";
const char* API_KEY      = "password";
const char* DEVICE_CODE  = "ESP32_RFID_001";

// ================= STRUKTUR EEPROM =================
struct DeviceConfig {
  char magic[5];       // Penanda "ABS2"
  char wifi_ssid[33];  // Nama WiFi
  char wifi_pass[65];  // Password WiFi
  char api_host[65];   // IP / Host Server (contoh: http://10.21.92.173)
};

DeviceConfig config;

// ================= WEB SERVER & CAPTIVE PORTAL =================
const byte DNS_PORT = 53;
DNSServer dnsServer;
ESP8266WebServer server(80);
bool isConfigMode = false;
bool webPingConnected = false;
unsigned long apStartTime = 0;
const unsigned long AP_TIMEOUT_MS = 300000; // 5 menit (300 detik)
const char* AP_SSID = "ESP-ABSENSI-RFID";
const char* AP_PASS = "12345678";

// ================= NETWORK INTERVAL =================
unsigned long lastHeartbeat = 0;
const unsigned long heartbeatInterval = 60000; // 60 detik

// ================= FORWARD DECLARATIONS =================
void startConfigPortal();
void startLocalWebServer();
void showStandby();
void displayError(String line1, String line2);
void displayMessage(String line1, String line2);
void handlePing();
void handleSwitchMode();
void handleSerialCommands();

// ================= UTILITAS STRING & URL =================
String sanitizeHost(String h) {
  h.trim();
  if (h.length() == 0) return "http://192.168.100.163";
  if (!h.startsWith("http://") && !h.startsWith("https://")) {
    h = "http://" + h;
  }
  while (h.endsWith("/")) {
    h = h.substring(0, h.length() - 1);
  }
  return h;
}

String getApiEndpoint() {
  return String(config.api_host) + "/AbsensiRFID/web/public/api/iot/check";
}

String getHeartbeatEndpoint() {
  return String(config.api_host) + "/AbsensiRFID/web/public/api/iot/heartbeat";
}

// ================= MANAJEMEN EEPROM =================
void saveConfig() {
  EEPROM.begin(512);
  EEPROM.put(0, config);
  EEPROM.commit();
}

void loadConfig() {
  EEPROM.begin(512);
  EEPROM.get(0, config);
  
  if (String(config.magic) != "ABS2") {
    Serial.println("Memperbarui EEPROM dengan konfigurasi default terbaru...");
    strncpy(config.magic, "ABS2", sizeof(config.magic));
    strncpy(config.wifi_ssid, DEFAULT_SSID, sizeof(config.wifi_ssid));
    strncpy(config.wifi_pass, DEFAULT_PASS, sizeof(config.wifi_pass));
    strncpy(config.api_host, DEFAULT_HOST, sizeof(config.api_host));
    config.magic[sizeof(config.magic) - 1] = '\0';
    config.wifi_ssid[sizeof(config.wifi_ssid) - 1] = '\0';
    config.wifi_pass[sizeof(config.wifi_pass) - 1] = '\0';
    config.api_host[sizeof(config.api_host) - 1] = '\0';
    saveConfig();
  } else {
    Serial.println("Konfigurasi dimuat dari EEPROM:");
    Serial.println("SSID : " + String(config.wifi_ssid));
    Serial.println("HOST : " + String(config.api_host));
  }
}

// ================= WEB CONFIG HANDLER =================
void handleRoot() {
  int n = WiFi.scanNetworks();
  String wifiOptions = "";
  for (int i = 0; i < n; ++i) {
    String ssidName = WiFi.SSID(i);
    int rssi = WiFi.RSSI(i);
    String selected = (ssidName == String(config.wifi_ssid)) ? " selected" : "";
    wifiOptions += "<option value='" + ssidName + "'" + selected + ">" + ssidName + " (" + String(rssi) + " dBm)</option>";
  }

  String currentHost = String(config.api_host);
  String currentSsid = String(config.wifi_ssid);
  String ipStatus = isConfigMode ? "192.168.4.1 (Access Point)" : WiFi.localIP().toString() + " (Terhubung)";

  String html = R"rawliteral(
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Alat Registrasi Kartu</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
  .card { max-width: 420px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.07); border: 1px solid #e2e8f0; }
  .header { text-align: center; margin-bottom: 20px; }
  .header h2 { margin: 0; font-size: 20px; color: #0f7a4a; font-weight: 800; }
  .header p { margin: 4px 0 0; font-size: 13px; color: #64748b; }
  .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700; background: #e0f2fe; color: #0284c7; margin-top: 8px; }
  .form-group { margin-bottom: 14px; text-align: left; }
  label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
  input, select { width: 100%; padding: 11px 12px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 14px; background: #fff; color: #0f172a; transition: border-color .2s; }
  input:focus, select:focus { outline: none; border-color: #0f7a4a; }
  .hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }
  .btn { width: 100%; padding: 13px; background: #0f7a4a; color: #fff; font-size: 15px; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; margin-top: 10px; }
  .btn:hover { background: #0b5c38; }
  .info-box { background: #f1f5f9; border-radius: 10px; padding: 10px; font-size: 12px; margin-bottom: 16px; color: #334155; }
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <h2>PENGATURAN ALAT RFID</h2>
    <p>Mode Registrasi Kartu</p>
    <span class="badge">STATUS: )rawliteral" + ipStatus + R"rawliteral(</span>
  </div>

  <div class="info-box">
    <strong>Alat:</strong> )rawliteral" + String(DEVICE_CODE) + R"rawliteral(<br>
    <strong>IP Server Saat Ini:</strong> )rawliteral" + currentHost + R"rawliteral(
  </div>

  <form action="/save" method="POST">
    <div class="form-group">
      <label>Pilih WiFi Sekitar</label>
      <select id="wifi_select" onchange="if(this.value) document.getElementById('ssid').value=this.value;">
        <option value="">-- Pilih Jaringan WiFi --</option>
        )rawliteral" + wifiOptions + R"rawliteral(
      </select>
    </div>

    <div class="form-group">
      <label>Nama WiFi (SSID)</label>
      <input type="text" id="ssid" name="ssid" value=")rawliteral" + currentSsid + R"rawliteral(" placeholder="Ketik nama WiFi jika tersembunyi" required>
    </div>

    <div class="form-group">
      <label>Password WiFi</label>
      <input type="password" name="pass" placeholder="Biarkan kosong jika tanpa password">
      <div class="hint">Kosongkan jika WiFi tidak menggunakan password</div>
    </div>

    <div class="form-group">
      <label>IP Server Laptop / Host</label>
      <input type="text" name="host" value=")rawliteral" + currentHost + R"rawliteral(" placeholder="Contoh: 192.168.100.163 atau 192.168.1.50" required>
      <div class="hint">Cukup isi IP Laptop/Server Anda (misal 192.168.1.50). 'http://' akan ditambahkan otomatis.</div>
    </div>

    <button type="submit" class="btn">Simpan & Restart Alat</button>
  </form>
</div>
</body>
</html>
)rawliteral";

  server.send(200, "text/html", html);
}

void handleSave() {
  if (server.hasArg("ssid") && server.hasArg("host")) {
    String newSsid = server.arg("ssid");
    String newPass = server.arg("pass");
    String newHost = server.arg("host");

    newSsid.trim();
    newPass.trim();
    newHost = sanitizeHost(newHost);

    strncpy(config.magic, "ABS1", sizeof(config.magic));
    strncpy(config.wifi_ssid, newSsid.c_str(), sizeof(config.wifi_ssid));
    if (newPass.length() > 0) {
      strncpy(config.wifi_pass, newPass.c_str(), sizeof(config.wifi_pass));
    }
    strncpy(config.api_host, newHost.c_str(), sizeof(config.api_host));
    
    saveConfig();

    String successPage = R"rawliteral(
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Berhasil Disimpan</title>
<style>
  body { font-family: sans-serif; background: #f8fafc; text-align: center; padding: 40px 20px; }
  .box { max-width: 380px; margin: auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
  h3 { color: #0f7a4a; margin-top: 0; }
  p { font-size: 14px; color: #475569; }
</style>
</head>
<body>
<div class="box">
  <h3>Konfigurasi Berhasil Disimpan!</h3>
  <p>Alat sedang merestart untuk menghubungkan ke:<br>
  WiFi: <b>)rawliteral" + newSsid + R"rawliteral(</b><br>
  Server: <b>)rawliteral" + newHost + R"rawliteral(</b></p>
  <p style="font-size:12px;color:#94a3b8;margin-top:20px;">Silakan lihat layar LCD pada alat Anda.</p>
</div>
</body>
</html>
)rawliteral";

    server.send(200, "text/html", successPage);

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Simpan Berhasil!");
    lcd.setCursor(0, 1);
    lcd.print("Ke Mode Client..");
    Serial.println("\n[CONFIG SAVED] Berhasil disimpan! Menghentikan Mode AP dan beralih ke Mode Client...");
    
    // Matikan DNS & SoftAP seketika, beralih ke Mode Client tanpa menunggu 5 menit
    dnsServer.stop();
    WiFi.softAPdisconnect(true);
    isConfigMode = false;
    delay(1000);
    ESP.restart();
  } else {
    server.send(400, "text/plain", "Data formulir tidak lengkap.");
  }
}

// ================= PING & NETWORK DIAGNOSTIC HANDLERS =================
void handlePing() {
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  server.sendHeader("Access-Control-Allow-Headers", "Content-Type");

  if (server.method() == HTTP_OPTIONS) {
    server.send(204);
    return;
  }

  webPingConnected = true;

  String currentIp = isConfigMode ? WiFi.softAPIP().toString() : WiFi.localIP().toString();
  String json = "{";
  json += "\"status\":\"connected\",";
  json += "\"success\":true,";
  json += "\"mode\":\"" + String(isConfigMode ? "ap" : "client") + "\",";
  json += "\"device_code\":\"" + String(DEVICE_CODE) + "\",";
  json += "\"ip\":\"" + currentIp + "\",";
  json += "\"wifi_ssid\":\"" + String(config.wifi_ssid) + "\",";
  json += "\"rssi\":" + String(WiFi.RSSI()) + ",";
  json += "\"uptime_sec\":" + String(millis() / 1000) + ",";
  json += "\"server_host\":\"" + String(config.api_host) + "\"";
  json += "}";

  server.send(200, "application/json", json);

  if (!isConfigMode) {
    Serial.println("\n[PING] Ping dari Web diterima! Mengupdate LCD...");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Connected!");
    lcd.setCursor(0, 1);
    lcd.print("Web Terhubung");
    delay(2000);
    showStandby();
  }
}

void handleSwitchMode() {
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  server.sendHeader("Access-Control-Allow-Headers", "Content-Type");

  if (server.method() == HTTP_OPTIONS) {
    server.send(204);
    return;
  }

  String targetMode = "client";
  if (server.hasArg("mode")) {
    targetMode = server.arg("mode");
  }

  if (targetMode == "ap") {
    server.send(200, "application/json", "{\"success\":true,\"mode\":\"ap\",\"message\":\"Beralih ke Mode AP (5 menit)\"}");
    delay(500);
    startConfigPortal();
  } else {
    server.send(200, "application/json", "{\"success\":true,\"mode\":\"client\",\"message\":\"Beralih ke Mode Client\"}");
    delay(500);
    if (isConfigMode) {
      dnsServer.stop();
      WiFi.softAPdisconnect(true);
      isConfigMode = false;
      connectWiFi();
      showStandby();
    }
  }
}

// ================= MODALITAS WEBSERVER =================
void startConfigPortal() {
  isConfigMode = true;
  apStartTime = millis(); // Mulai timer 5 menit

  WiFi.disconnect();
  delay(100);
  WiFi.mode(WIFI_AP_STA);

  WiFi.softAP(AP_SSID, AP_PASS);

  dnsServer.start(DNS_PORT, "*", WiFi.softAPIP());

  server.on("/", handleRoot);
  server.on("/ping", handlePing);
  server.on("/status", handlePing);
  server.on("/switch_mode", handleSwitchMode);
  server.on("/save", HTTP_POST, handleSave);
  server.onNotFound([]() {
    server.sendHeader("Location", "http://192.168.4.1/", true);
    server.send(302, "text/plain", "");
  });

  server.begin();

  // Tampilkan SSID dan Password di Layar LCD untuk dikoneksikan ke laptop
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("AP: ESP-ABSENSI");
  lcd.setCursor(0, 1);
  lcd.print("PW: 12345678");

  Serial.println("\n[MODE ACCESS POINT (AP) AKTIF - AUTO SWITCH 5 MENIT]");
  Serial.println("SSID: " + String(AP_SSID));
  Serial.println("Password: " + String(AP_PASS));
  Serial.print("IP Web Portal: ");
  Serial.println(WiFi.softAPIP());
}

void startLocalWebServer() {
  server.on("/", handleRoot);
  server.on("/ping", handlePing);
  server.on("/status", handlePing);
  server.on("/switch_mode", handleSwitchMode);
  server.on("/save", HTTP_POST, handleSave);
  server.begin();
  Serial.println("Web Config Server aktif di: http://" + WiFi.localIP().toString());
}

// ================= FUNGSI INISIALISASI =================
void initHardware() {
  Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN); // SDA=D2, SCL=D1
  Wire.setClock(100000);
  delay(100);

  byte detectedAddr = 0;
  Wire.beginTransmission(0x27);
  if (Wire.endTransmission() == 0) {
    detectedAddr = 0x27;
    activeLcd = &lcd27;
  } else {
    Wire.beginTransmission(0x3F);
    if (Wire.endTransmission() == 0) {
      detectedAddr = 0x3F;
      activeLcd = &lcd3F;
    }
  }

  if (detectedAddr != 0) {
    Serial.printf("[LCD I2C] Layar terdeteksi di alamat 0x%02X\n", detectedAddr);
  } else {
    Serial.println("[LCD I2C] Tidak ada respon di 0x27 maupun 0x3F. Menggunakan fallback 0x27.");
    activeLcd = &lcd27;
  }

  activeLcd->init();
  activeLcd->backlight();
  activeLcd->clear();
  activeLcd->setCursor(0, 0);
  activeLcd->print("Mode Registrasi");
  activeLcd->setCursor(0, 1);
  activeLcd->print("Memulai...");

  SPI.begin();
  mfrc522.PCD_Init();
}

// ================= UTILITAS KHUSUS DISPLAY LCD 16x2 =================
String fitLcd16(String str) {
  str.trim();
  if (str.length() > 16) {
    return str.substring(0, 16);
  }
  return str;
}

void displayError(String line1, String line2) {
  lcd.clear();
  line1 = fitLcd16(line1);
  line2 = fitLcd16(line2);
  lcd.setCursor(0, 0);
  lcd.print(line1);
  lcd.setCursor(0, 1);
  lcd.print(line2);
}

void displayMessage(String line1, String line2) {
  lcd.clear();
  line1 = fitLcd16(line1);
  line2 = fitLcd16(line2);
  lcd.setCursor(0, 0);
  lcd.print(line1);
  lcd.setCursor(0, 1);
  lcd.print(line2);
}

void showStandby() {
  lcd.clear();
  if (webPingConnected) {
    lcd.setCursor(0, 0);
    lcd.print("Mode Registrasi");
    lcd.setCursor(0, 1);
    lcd.print("Tempelkan Kartu");
  } else {
    String ipStr = WiFi.localIP().toString();
    String line2 = (ipStr.length() <= 12) ? ("IP: " + ipStr) : (ipStr.length() <= 13 ? ("IP:" + ipStr) : ipStr);
    line2 = fitLcd16(line2);
    lcd.setCursor(0, 0);
    lcd.print("IP ESP (Client):");
    lcd.setCursor(0, 1);
    lcd.print(line2);
  }
}

// ================= FUNGSI JARINGAN =================
void connectWiFi() {
  displayMessage("Koneksi WiFi...", String(config.wifi_ssid).substring(0, 16));

  WiFi.mode(WIFI_STA);
  WiFi.setSleepMode(WIFI_NONE_SLEEP); // Nonaktifkan modem sleep
  delay(100);

  WiFi.begin(config.wifi_ssid, config.wifi_pass);
  WiFi.setAutoReconnect(true);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 40) { // ~20 detik batas toleransi
    delay(500);
    attempts++;
    Serial.print(".");
    handleSerialCommands();
  }

  if (WiFi.status() == WL_CONNECTED) {
    webPingConnected = false;
    WiFi.setSleepMode(WIFI_NONE_SLEEP);
    String ipStr = WiFi.localIP().toString();
    String line2 = (ipStr.length() <= 12) ? ("IP: " + ipStr) : (ipStr.length() <= 13 ? ("IP:" + ipStr) : ipStr);

    Serial.println("\n[WiFi] Connected! IP: " + ipStr);
    startLocalWebServer();

    // Segera kirim heartbeat awal ke server web agar IP baru langsung terdeteksi di dashboard web!
    handleHeartbeat();

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Terhubung!");
    lcd.setCursor(0, 1);
    lcd.print(line2);
    delay(2000);

    // Tahan tampilan IP pada LCD sampai admin melakukan uji Ping dari web
    showStandby();
  } else {
    displayError("Gagal WiFi", "Mode AP (5 Menit)");
    Serial.println("\nGagal terhubung. Memulai Mode Access Point (Auto Switch 5 Menit)...");
    delay(1500);
    startConfigPortal();
  }
}

void checkWiFiConnection() {
  if (WiFi.status() != WL_CONNECTED) {
    static unsigned long lastReconnectAttempt = 0;
    unsigned long now = millis();
    // Non-blocking auto-reconnect setiap 5 detik tanpa freeze loop dan tanpa masuk AP
    if (now - lastReconnectAttempt >= 5000) {
      lastReconnectAttempt = now;
      Serial.println("[WiFi] Koneksi terputus. Menghubungkan kembali...");
      WiFi.reconnect();
    }
  }
}

// ================= FUNGSI HEARTBEAT =================
void handleHeartbeat() {
  if (WiFi.status() == WL_CONNECTED) {
    unsigned long currentMillis = millis();
    if (currentMillis - lastHeartbeat >= heartbeatInterval || lastHeartbeat == 0) {
      lastHeartbeat = currentMillis;

      WiFiClient client;
      client.setTimeout(2500); // Batas timeout 2.5s agar tidak freeze
      HTTPClient http;
      http.setTimeout(2500);
      http.begin(client, getHeartbeatEndpoint());
      http.addHeader("Content-Type", "application/json");
      http.addHeader("X-Api-Key", API_KEY);

      String payload = "{\"device_code\":\"" + String(DEVICE_CODE) + "\",\"ip_address\":\"" + WiFi.localIP().toString() + "\",\"wifi_ssid\":\"" + String(config.wifi_ssid) + "\"}";
      int httpResponseCode = http.POST(payload);

      if (httpResponseCode == 200) {
        String resp = http.getString();
        Serial.println("Heartbeat terkirim (Status: 200)");

        // Cek jika server mengirim pembaruan konfigurasi jarak jauh (Remote OTA Config)
        if (resp.indexOf("\"config_update\"") > 0) {
          String newSsid = extractJsonValue(resp, "\"wifi_ssid\":\"");
          String newPass = extractJsonValue(resp, "\"wifi_pass\":\"");
          String newHost = extractJsonValue(resp, "\"api_host\":\"");

          bool changed = false;
          if (newSsid.length() > 0 && newSsid != String(config.wifi_ssid)) {
            strncpy(config.wifi_ssid, newSsid.c_str(), sizeof(config.wifi_ssid));
            changed = true;
          }
          if (newPass.length() > 0) {
            strncpy(config.wifi_pass, newPass.c_str(), sizeof(config.wifi_pass));
            changed = true;
          }
          if (newHost.length() > 0) {
            newHost = sanitizeHost(newHost);
            if (newHost != String(config.api_host)) {
              strncpy(config.api_host, newHost.c_str(), sizeof(config.api_host));
              changed = true;
            }
          }

          if (changed) {
            saveConfig();
            displayMessage("Config Baru!", "Restarting...");
            Serial.println("\n[REMOTE OTA CONFIG] Menerima konfigurasi baru dari dashboard!");
            Serial.println("SSID: " + String(config.wifi_ssid));
            Serial.println("Host: " + String(config.api_host));
            delay(2000);
            ESP.restart();
          }
        }
      } else {
        Serial.println("Heartbeat gagal: " + String(httpResponseCode));
      }
      http.end();
    }
  }
}

String extractJsonValue(String json, String key) {
  int startIndex = json.indexOf(key);
  if (startIndex >= 0) {
    int valueStart = startIndex + key.length();
    int endQuote = json.indexOf("\"", valueStart);
    if (endQuote > valueStart) {
      String val = json.substring(valueStart, endQuote);
      val.replace("\\/", "/");
      return val;
    }
  }
  return "";
}

void handleSerialCommands() {
  if (Serial.available() > 0) {
    String cmd = Serial.readStringUntil('\n');
    cmd.trim();
    if (cmd.length() == 0) return;

    if (cmd == "PING") {
      Serial.println("PONG:ESP_READY");
      return;
    }

    if (cmd == "GET_INFO") {
      Serial.println("INFO:{\"ssid\":\"" + String(config.wifi_ssid) + "\",\"host\":\"" + String(config.api_host) + "\"}");
      return;
    }

    if (cmd.startsWith("CONFIG:") || cmd.startsWith("{")) {
      String json = cmd.startsWith("CONFIG:") ? cmd.substring(7) : cmd;
      String newSsid = extractJsonValue(json, "\"wifi_ssid\":\"");
      if (newSsid.length() == 0) newSsid = extractJsonValue(json, "\"ssid\":\"");
      String newPass = extractJsonValue(json, "\"wifi_pass\":\"");
      if (newPass.length() == 0) newPass = extractJsonValue(json, "\"pass\":\"");
      String newHost = extractJsonValue(json, "\"api_host\":\"");
      if (newHost.length() == 0) newHost = extractJsonValue(json, "\"host\":\"");

      newSsid.trim();
      newPass.trim();
      newHost.trim();

      if (newSsid.length() == 0) newSsid = String(config.wifi_ssid);
      if (newHost.length() == 0) newHost = String(config.api_host);
      else newHost = sanitizeHost(newHost);

      if (newSsid.length() > 0) {
        strncpy(config.magic, "ABS2", sizeof(config.magic));
        config.magic[sizeof(config.magic) - 1] = '\0';
        strncpy(config.wifi_ssid, newSsid.c_str(), sizeof(config.wifi_ssid));
        config.wifi_ssid[sizeof(config.wifi_ssid) - 1] = '\0';

        if (newPass.length() > 0) {
          strncpy(config.wifi_pass, newPass.c_str(), sizeof(config.wifi_pass));
          config.wifi_pass[sizeof(config.wifi_pass) - 1] = '\0';
        }

        strncpy(config.api_host, newHost.c_str(), sizeof(config.api_host));
        config.api_host[sizeof(config.api_host) - 1] = '\0';

        saveConfig();

        Serial.println("OK:CONFIG_APPLIED");
        displayMessage("Config via USB!", "Restarting...");
        delay(1200);
        ESP.restart();
      } else {
        Serial.println("ERR:INVALID_SSID");
      }
    }
  }
}

// ================= FUNGSI RFID =================
String readCardUID() {
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    return "";
  }

  displayMessage("Membaca UID...", "");
  delay(300);

  String uidString = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uidString += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
    uidString += String(mfrc522.uid.uidByte[i], HEX);
  }
  uidString.toUpperCase();

  Serial.println("UID Terbaca: " + uidString);

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  return uidString;
}

// ================= FUNGSI REGISTRASI =================
void registerCard(String uidString) {
  displayMessage("Mengirim Data...", uidString);

  if (WiFi.status() != WL_CONNECTED) {
    displayError("Gagal Hubung", "WiFi Terputus");
    return;
  }

  WiFiClient client;
  HTTPClient http;

  http.begin(client, getApiEndpoint());
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Api-Key", API_KEY);

  String payload = "{\"device_code\":\"" + String(DEVICE_CODE) + "\",\"rfid_uid\":\"" + uidString + "\"}";
  int httpResponseCode = http.POST(payload);

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println("HTTP Response code: " + String(httpResponseCode));
    Serial.println(response);

    if (httpResponseCode == 200 || httpResponseCode == 404) {
      displayMessage("Terkirim!", "Cek Dashboard");
    } else {
      displayError("Server Error", "Code: " + String(httpResponseCode));
    }
  } else {
    Serial.println("Error code: " + String(httpResponseCode));
    displayError("Gagal Hubung", "Cek Jaringan/IP");
  }

  http.end();
  delay(2500);
}

// ================= SETUP & LOOP =================
void setup() {
  Serial.begin(115200);
  initHardware();

  loadConfig();

  // Tampilkan splash screen
  displayMessage("Mode Registrasi", "Memulai...");
  delay(1000);

  connectWiFi();
  if (!isConfigMode) {
    showStandby();
  }
}

void loop() {
  handleSerialCommands();

  if (isConfigMode) {
    dnsServer.processNextRequest();
    server.handleClient();

    // Aturan Otomatis: Setelah 5 menit mode AP, ESP otomatis kembali ke mode client
    if (millis() - apStartTime >= AP_TIMEOUT_MS) {
      Serial.println("\n[AP TIMEOUT 5 MENIT] Waktu Mode AP telah habis. Beralih otomatis ke Mode Client...");
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("AP 5 Min Selesai");
      lcd.setCursor(0, 1);
      lcd.print("Ke Mode Client..");
      delay(2000);

      dnsServer.stop();
      WiFi.softAPdisconnect(true);
      isConfigMode = false;
      connectWiFi();
    }
    return;
  }

  // Melayani web config di background pada http://<IP_ESP>/
  server.handleClient();

  checkWiFiConnection();
  handleHeartbeat();

  String uid = readCardUID();
  if (uid != "") {
    webPingConnected = true; // Langsung aktifkan mode registrasi siap saat kartu ditempel
    registerCard(uid);
    showStandby();
  }
}
