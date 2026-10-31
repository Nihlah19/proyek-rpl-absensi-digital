#include <Arduino.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <EEPROM.h>
#include <ESP8266WebServer.h>
#include <DNSServer.h>

// ================= KONFIGURASI HARDWARE =================
#define RST_PIN    D3
#define SS_PIN     D4
#define BUZZER_PIN D0
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

// ================= DUAL MODE: ABSENSI vs REGISTRASI (TAMBAH KARTU) =================
String currentAppMode = "absen"; // "absen" (Default) atau "regis"
unsigned long regisModeStartTime = 0;
const unsigned long REGIS_TIMEOUT_MS = 300000; // 5 menit auto-revert ke Mode Absen jika tidak ada aktivitas

// ================= NETWORK INTERVAL =================
unsigned long lastHeartbeat = 0;
const unsigned long heartbeatInterval = 15000; // 15 detik agar responsif dan tidak membebani wifi

// ================= FORWARD DECLARATIONS =================
void startConfigPortal();
void startLocalWebServer();
void showStandby();
String fitLcd16(String str);
String formatNamaLcd(String rawName);
void displayMessage(String line1, String line2);
void displayError(String line1, String line2);
void handlePing();
void handleSwitchMode();
void handleSetMode();
void handleGetMode();
void setAppMode(String newMode);
void processAttendance(String uidString);
void processRegistration(String uidString);
void playSukses();
void playGagalScan();
void playDoubleAbsen();
void playBelumDaftar();
String extractJsonValue(String json, String key);
void handleSerialCommands();

// ================= UTILITAS STRING & URL =================
String sanitizeHost(String h) {
  h.trim();
  h.replace("\\/", "/");
  if (h.length() == 0) return "http://10.21.92.173";
  if (!h.startsWith("http://") && !h.startsWith("https://")) {
    h = "http://" + h;
  }
  while (h.endsWith("/")) {
    h = h.substring(0, h.length() - 1);
  }
  return h;
}

String getApiEndpoint() {
  return String(config.api_host) + "/AbsensiRFID/web/public/api/iot/attendance/student";
}

String getCheckEndpoint() {
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
  
  // Cek apakah data di EEPROM valid (magic keyword "ABS2")
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
<title>Pengaturan Absensi RFID</title>
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
  input:focus, select:focus { outline: none; border-color: #0f7a4a; ring: 2px rgba(15,122,74,0.15); }
  .hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }
  .btn { width: 100%; padding: 13px; background: #0f7a4a; color: #fff; font-size: 15px; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; margin-top: 10px; transition: background .2s; }
  .btn:hover { background: #0b5c38; }
  .info-box { background: #f1f5f9; border-radius: 10px; padding: 10px; font-size: 12px; margin-bottom: 16px; color: #334155; }
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <h2>PENGATURAN ALAT RFID</h2>
    <p>Scanner Absensi Siswa</p>
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
      <input type="text" name="host" value=")rawliteral" + currentHost + R"rawliteral(" placeholder="Contoh: 192.168.1.100 atau 50.50.49.168" required>
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
  String newSsid = "";
  String newPass = "";
  String newHost = "";

  // 1. Cek jika request berbentuk JSON di body (Direct Push dari Server)
  if (server.hasArg("plain")) {
    String body = server.arg("plain");
    newSsid = extractJsonValue(body, "\"ssid\":\"");
    if (newSsid.length() == 0) newSsid = extractJsonValue(body, "\"wifi_ssid\":\"");
    newPass = extractJsonValue(body, "\"pass\":\"");
    if (newPass.length() == 0) newPass = extractJsonValue(body, "\"wifi_pass\":\"");
    newHost = extractJsonValue(body, "\"host\":\"");
    if (newHost.length() == 0) newHost = extractJsonValue(body, "\"api_host\":\"");
  }

  // 2. Cek form URL-encoded biasa
  if (server.hasArg("ssid")) newSsid = server.arg("ssid");
  if (server.hasArg("pass")) newPass = server.arg("pass");
  if (server.hasArg("host")) newHost = server.arg("host");

  newSsid.trim();
  newPass.trim();
  newHost.trim();

  // Fallback jika tidak diubah
  if (newSsid.length() == 0) {
    newSsid = String(config.wifi_ssid);
  }
  if (newHost.length() == 0) {
    newHost = String(config.api_host);
  } else {
    newHost = sanitizeHost(newHost);
  }

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

    // Kirim response status ACC (JSON jika request API, HTML jika browser)
    if (server.hasArg("plain") || (server.hasHeader("Accept") && server.header("Accept").indexOf("application/json") >= 0)) {
      server.send(200, "application/json", "{\"success\":true,\"message\":\"Config ACC! Restarting...\",\"ssid\":\"" + newSsid + "\"}");
    } else {
      String successPage = R"rawliteral(
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Berhasil Disimpan</title>
<style>
  body { font-family: -apple-system, sans-serif; background: #f8fafc; text-align: center; padding: 40px 20px; }
  .box { max-width: 380px; margin: auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
  h3 { color: #0f7a4a; margin-top: 0; }
  p { font-size: 14px; color: #475569; line-height: 1.6; }
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
    }

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Simpan Berhasil!");
    lcd.setCursor(0, 1);
    lcd.print("Ke Mode Client..");
    Serial.println("\n[CONFIG SAVED] Berhasil disimpan! Menghentikan Mode AP dan beralih ke Mode Client...");
    Serial.println("SSID: " + newSsid);
    Serial.println("Host: " + newHost);
    
    // Matikan DNS & SoftAP seketika, beralih ke Mode Client tanpa menunggu 5 menit
    dnsServer.stop();
    WiFi.softAPdisconnect(true);
    isConfigMode = false;
    delay(1000);
    ESP.restart();
  } else {
    server.send(400, "application/json", "{\"success\":false,\"message\":\"SSID tidak valid\"}");
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
  json += "\"app_mode\":\"" + currentAppMode + "\",";
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

    // Beep singkat tanda koneksi web berhasil
    digitalWrite(BUZZER_PIN, HIGH);
    delay(150);
    digitalWrite(BUZZER_PIN, LOW);

    delay(2000);

    // Teks otomatis berubah kembali ke standby
    showStandby();
  }
}

void handleGetMode() {
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  server.sendHeader("Access-Control-Allow-Headers", "Content-Type");

  if (server.method() == HTTP_OPTIONS) {
    server.send(204);
    return;
  }

  String json = "{\"success\":true,\"app_mode\":\"" + currentAppMode + "\",\"ip\":\"" + WiFi.localIP().toString() + "\"}";
  server.send(200, "application/json", json);
}

void handleSetMode() {
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.sendHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  server.sendHeader("Access-Control-Allow-Headers", "Content-Type");

  if (server.method() == HTTP_OPTIONS) {
    server.send(204);
    return;
  }

  String targetMode = "absen";
  if (server.hasArg("mode")) {
    targetMode = server.arg("mode");
  }

  setAppMode(targetMode);

  String json = "{\"success\":true,\"app_mode\":\"" + currentAppMode + "\",\"message\":\"Mode berhasil diubah ke " + currentAppMode + "\"}";
  server.send(200, "application/json", json);
}

void setAppMode(String newMode) {
  if (newMode == "regis") {
    currentAppMode = "regis";
    regisModeStartTime = millis();
    Serial.println("\n[MODE REGISTRASI KARTU AKTIF] Menunggu pindaian kartu baru...");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("MODE REGISTRASI");
    lcd.setCursor(0, 1);
    lcd.print("Scan Kartu Baru");
    playSukses();
  } else {
    currentAppMode = "absen";
    Serial.println("\n[MODE SCAN ABSENSI AKTIF] Siap presensi harian...");
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
  WiFi.mode(WIFI_AP);
  delay(100);

  IPAddress apIP(192, 168, 4, 1);
  IPAddress netMsk(255, 255, 255, 0);
  WiFi.softAPConfig(apIP, apIP, netMsk);
  bool apStarted = WiFi.softAP(AP_SSID, AP_PASS, 1, 0, 4);

  dnsServer.start(DNS_PORT, "*", apIP);

  server.on("/", handleRoot);
  server.on("/ping", handlePing);
  server.on("/status", handlePing);
  server.on("/switch_mode", handleSwitchMode);
  server.on("/set_mode", handleSetMode);
  server.on("/get_mode", handleGetMode);
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
  server.on("/set_mode", handleSetMode);
  server.on("/get_mode", handleGetMode);
  server.on("/save", HTTP_POST, handleSave);
  server.begin();
  Serial.println("Web Config Server aktif di: http://" + WiFi.localIP().toString());
}

// ================= FUNGSI DISPLAY =================
void initDisplay() {
  // Wajib untuk ESP8266: Inisialisasi bus I2C (SDA=D2, SCL=D1)
  Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN);
  Wire.setClock(100000);
  delay(100);

  // Auto-detect alamat LCD (0x27 atau 0x3F)
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
    Serial.println("[LCD I2C] CATATAN: Pastikan kabel SDA di pin D2 dan SCL di pin D1!");
    activeLcd = &lcd27;
  }

  activeLcd->init();
  activeLcd->backlight();
  activeLcd->clear();
  activeLcd->setCursor(0, 0);
  activeLcd->print("Absensi RFID");
  activeLcd->setCursor(0, 1);
  activeLcd->print("Memulai...");
  delay(500);
}

void showStandby() {
  lcd.clear();
  if (currentAppMode == "regis") {
    lcd.setCursor(0, 0);
    lcd.print("MODE REGISTRASI");
    lcd.setCursor(0, 1);
    lcd.print("Scan Kartu Baru");
  } else if (webPingConnected) {
    lcd.setCursor(0, 0);
    lcd.print("Sistem Absensi");
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

// ================= UTILITAS KHUSUS DISPLAY LCD 16x2 =================
// Menjamin teks dipotong tepat maksimal 16 karakter tanpa meluber keluar layar fisik
String fitLcd16(String str) {
  str.trim();
  if (str.length() > 16) {
    return str.substring(0, 16);
  }
  return str;
}

// Memformat nama siswa secara cerdas agar muat di LCD 16 kolom tanpa terpotong kaku
String formatNamaLcd(String rawName) {
  rawName.trim();
  if (rawName.length() == 0) return "Siswa";
  if (rawName.length() <= 16) return rawName;

  int firstSpace = rawName.indexOf(' ');
  if (firstSpace > 0) {
    String firstWord = rawName.substring(0, firstSpace);
    String rest = rawName.substring(firstSpace + 1);
    rest.trim();

    // Coba singkat kata pertama: "M. NamaLengkap"
    String candidate = firstWord.substring(0, 1) + ". " + rest;
    if (candidate.length() <= 16) {
      return candidate;
    }

    // Jika masih > 16 karakter, cari spasi pada kata berikutnya
    int secondSpace = rest.indexOf(' ');
    if (secondSpace > 0) {
      String middleWord = rest.substring(0, secondSpace);
      String lastWord = rest.substring(secondSpace + 1);
      lastWord.trim();

      // Coba format: "M. Middle L."
      String cand2 = firstWord.substring(0, 1) + ". " + middleWord + " " + lastWord.substring(0, 1) + ".";
      if (cand2.length() <= 16) {
        return cand2;
      }

      // Coba format: "M. Middle"
      String cand3 = firstWord.substring(0, 1) + ". " + middleWord;
      if (cand3.length() <= 16) {
        return cand3;
      }
    } else {
      // 2 kata tapi kata kedua panjang (contoh: "Muhammad Hidayatullah")
      String cand4 = firstWord.substring(0, 1) + ". " + rest;
      if (cand4.length() <= 16) return cand4;
    }

    return candidate.substring(0, 16);
  }

  return rawName.substring(0, 16);
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

// ================= FUNGSI AUDIO (BUZZER) =================
void initBuzzer() {
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
}

void playDoubleAbsen() { /* diam */ }
void playGagalScan()   { /* diam */ }
void playBelumDaftar() { /* diam */ }

void playSukses() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(200);
  digitalWrite(BUZZER_PIN, LOW);
}

// ================= FUNGSI RFID =================
void initRFID() {
  SPI.begin();
  mfrc522.PCD_Init();
}

String readCardUID() {
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    return "";
  }

  displayMessage("Kartu Terdeteksi", "Membaca UID...");
  delay(300);

  String uidString = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uidString += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
    uidString += String(mfrc522.uid.uidByte[i], HEX);
  }
  uidString.toUpperCase();

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  return uidString;
}

// ================= FUNGSI JARINGAN =================
void connectWiFi() {
  displayMessage("Koneksi WiFi...", String(config.wifi_ssid).substring(0, 16));

  WiFi.mode(WIFI_STA);
  WiFi.setSleepMode(WIFI_NONE_SLEEP); // Nonaktifkan modem sleep agar tidak 'pm open,type:2 0'
  delay(100);

  WiFi.begin(config.wifi_ssid, config.wifi_pass);
  WiFi.setAutoReconnect(true);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 40) { // ~20 detik batas toleransi koneksi
    delay(500);
    attempts++;
    Serial.print(".");
    handleSerialCommands();
  }

  if (WiFi.status() == WL_CONNECTED) {
    webPingConnected = false;
    WiFi.setSleepMode(WIFI_NONE_SLEEP); // Pastikan tetap NONE_SLEEP saat connected
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
    // Non-blocking auto-reconnect setiap 5 detik tanpa freeze loop dan TANPA memaksa beralih ke Mode AP
    if (now - lastReconnectAttempt >= 5000) {
      lastReconnectAttempt = now;
      Serial.println("[WiFi] Koneksi terputus. Menghubungkan ulang ke " + String(config.wifi_ssid) + "...");
      WiFi.reconnect();
    }
  }
}

void handleHeartbeat() {
  if (WiFi.status() == WL_CONNECTED) {
    unsigned long currentMillis = millis();
    if (currentMillis - lastHeartbeat >= heartbeatInterval || lastHeartbeat == 0) {
      lastHeartbeat = currentMillis;

      WiFiClient client;
      client.setTimeout(2500); // Cegah blocking 15 detik yang membuat wifi putus
      HTTPClient http;
      http.setTimeout(2500);
      http.begin(client, getHeartbeatEndpoint());
      http.addHeader("Content-Type", "application/json");
      http.addHeader("X-Api-Key", API_KEY);

      String payload = "{\"device_code\":\"" + String(DEVICE_CODE) + "\",\"ip_address\":\"" + WiFi.localIP().toString() + "\",\"wifi_ssid\":\"" + String(config.wifi_ssid) + "\"}";
      int httpResponseCode = http.POST(payload);

      if (httpResponseCode == 200) {
        String resp = http.getString();

        // Sinkronisasi Mode Operasional (Absensi vs Registrasi) dari server
        if (resp.indexOf("\"app_mode\":\"regis\"") > 0) {
          if (currentAppMode != "regis") {
            setAppMode("regis");
          }
        } else if (resp.indexOf("\"app_mode\":\"absen\"") > 0) {
          if (currentAppMode != "absen") {
            setAppMode("absen");
          }
        }

        // Cek jika server mengirim pembaruan konfigurasi jarak jauh (Remote OTA Config)
        if (resp.indexOf("\"config_update\"") > 0) {
          String newSsid = extractJsonValue(resp, "\"wifi_ssid\":\"");
          String newPass = extractJsonValue(resp, "\"wifi_pass\":\"");
          String newHost = extractJsonValue(resp, "\"api_host\":\"");

          bool changed = false;
          if (newSsid.length() > 0 && newSsid != String(config.wifi_ssid)) {
            strncpy(config.wifi_ssid, newSsid.c_str(), sizeof(config.wifi_ssid));
            config.wifi_ssid[sizeof(config.wifi_ssid) - 1] = '\0';
            changed = true;
          }
          if (newPass.length() > 0) {
            strncpy(config.wifi_pass, newPass.c_str(), sizeof(config.wifi_pass));
            config.wifi_pass[sizeof(config.wifi_pass) - 1] = '\0';
            changed = true;
          }
          if (newHost.length() > 0) {
            newHost = sanitizeHost(newHost);
            if (newHost != String(config.api_host)) {
              strncpy(config.api_host, newHost.c_str(), sizeof(config.api_host));
              config.api_host[sizeof(config.api_host) - 1] = '\0';
              changed = true;
            }
          }

          if (changed) {
            strncpy(config.magic, "ABS2", sizeof(config.magic));
            config.magic[sizeof(config.magic) - 1] = '\0';
            saveConfig();
            displayMessage("Ganti Sandi ACC!", "Restarting...");
            Serial.println("\n[REMOTE OTA CONFIG ACC] Menerima konfigurasi/sandi baru dari dashboard!");
            Serial.println("SSID: " + String(config.wifi_ssid));
            Serial.println("Host: " + String(config.api_host));
            delay(1500);
            ESP.restart();
          }
        }
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

void handleApiResponse(int httpResponseCode, String response) {
  webPingConnected = true;

  if (httpResponseCode == 200) {
    if (response.indexOf("\"code\":\"mode_regis\"") > 0) {
      String uidVal = extractJsonValue(response, "\"uid\":\"");
      displayMessage("Kartu Terdeteksi", ("UID:" + uidVal));
      playSukses();
      delay(2000);
      return;
    }

    if (response.indexOf("\"success\":true") > 0) {
      String nama = extractJsonValue(response, "\"nama\":\"");
      String code = extractJsonValue(response, "\"code\":\"");
      String attStatus = extractJsonValue(response, "\"att_status\":\"");
      String timeVal = extractJsonValue(response, "\"time\":\"");
      if (timeVal.length() > 5) {
        timeVal = timeVal.substring(0, 5); // Format HH:mm agar hemat ruang LCD 16x2
      }
      String shortName = nama != "" ? formatNamaLcd(nama) : "Berhasil!";

      if (code == "early_leave") {
        displayMessage("Sudah Masuk", "Ajukan Plg Cepat");
      } else if (code == "check_out") {
        displayMessage("Sukses Pulang", shortName);
      } else {
        // Tampilkan status kehadiran dan jam secara pas di baris ke-2 LCD
        String line2 = attStatus != "" ? (attStatus + (timeVal != "" ? (" " + timeVal) : "")) : "Tercatat Masuk";
        displayMessage(shortName, line2);
      }
      playSukses();
    } else {
      displayError("Gagal Absen", "Respon Invalid");
      playGagalScan();
    }
  } else if (httpResponseCode == 409) {
    displayError("Sudah Absen", "Hari Ini");
    playDoubleAbsen();
  } else if (httpResponseCode == 404) {
    String uidVal = extractJsonValue(response, "\"uid\":\"");
    displayError("Kartu Blm Ada", uidVal != "" ? ("UID:" + uidVal) : "Belum Terdaftar");
    playBelumDaftar();
  } else if (httpResponseCode == 422) {
    // Siswa tercatat izin atau sakit hari ini
    if (response.indexOf("IZIN") >= 0 || response.indexOf("izin") >= 0) {
      displayError("Tercatat Izin", "Tdk Perlu Tap");
    } else if (response.indexOf("SAKIT") >= 0 || response.indexOf("sakit") >= 0) {
      displayError("Tercatat Sakit", "Tdk Perlu Tap");
    } else {
      displayError("Sedang Izin/Skt", "Tdk Perlu Tap");
    }
    playGagalScan();
  } else if (httpResponseCode == 403) {
    displayError("Akses Ditolak", "Alat Nonaktif");
    playGagalScan();
  } else if (httpResponseCode == 401) {
    displayError("API Key Salah", "Cek Konfigurasi");
    playGagalScan();
  } else if (httpResponseCode == 400) {
    // Penanganan eksplisit masalah jadwal atau jam absensi
    if (response.indexOf("Jadwal") >= 0 || response.indexOf("jadwal") >= 0 || response.indexOf("no_schedule") >= 0) {
      displayError("Jadwal Tdk Ada", "Hari Libur");
    } else if (response.indexOf("Belum") >= 0 || response.indexOf("too_early") >= 0) {
      displayError("Belum Jam Masuk", "Tunggu Jadwal");
    } else {
      String msg = extractJsonValue(response, "\"message\":\"");
      displayError("Jadwal Tdk Ada", msg != "" ? msg : "Tidak Ada Jadwal");
    }
    playGagalScan();
  } else {
    displayError("Server Error", "Kode: " + String(httpResponseCode));
    playGagalScan();
  }
}

void processRegistration(String uidString) {
  displayMessage("Membaca Kartu...", ("UID:" + uidString).substring(0, 16));

  if (WiFi.status() != WL_CONNECTED) {
    displayError("WiFi Terputus", "Koneksi Ulang..");
    playGagalScan();
    return;
  }

  WiFiClient client;
  client.setTimeout(4000);
  HTTPClient http;
  http.setTimeout(4000);

  http.begin(client, getCheckEndpoint());
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Api-Key", API_KEY);

  String payload = "{\"device_code\":\"" + String(DEVICE_CODE) + "\",\"rfid_uid\":\"" + uidString + "\"}";
  int httpResponseCode = http.POST(payload);

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println("[REGIS SCAN] Respon HTTP: " + String(httpResponseCode) + " -> " + response);
    displayMessage("UID Terdeteksi!", ("UID: " + uidString).substring(0, 16));
    playSukses();
    delay(2000);
  } else {
    Serial.printf("[REGIS] Error HTTP: %d\n", httpResponseCode);
    displayError("Gagal Hubung", "Cek Server/IP");
    playGagalScan();
  }

  http.end();
  delay(1000);
}

void processAttendance(String uidString) {
  displayMessage("Memproses...", "");

  if (WiFi.status() != WL_CONNECTED) {
    displayError("WiFi Terputus", "Koneksi Ulang..");
    playGagalScan();
    return;
  }

  WiFiClient client;
  client.setTimeout(7000);
  HTTPClient http;
  http.setTimeout(7000);

  http.begin(client, getApiEndpoint());
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Api-Key", API_KEY);

  String payload = "{\"device_code\":\"" + String(DEVICE_CODE) + "\",\"rfid_uid\":\"" + uidString + "\"}";
  int httpResponseCode = http.POST(payload);

  if (httpResponseCode > 0) {
    String response = http.getString();
    handleApiResponse(httpResponseCode, response);
  } else {
    Serial.printf("[ATTENDANCE] Error HTTP: %d\n", httpResponseCode);
    displayError("Gagal Hubung", "Cek Server/IP");
    playGagalScan();
  }

  http.end();
  delay(3000);
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
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Config via USB!");
        lcd.setCursor(0, 1);
        lcd.print("Restarting...");
        delay(1200);
        ESP.restart();
      } else {
        Serial.println("ERR:INVALID_SSID");
      }
    }
  }
}

// ================= SETUP & LOOP =================
void setup() {
  Serial.begin(115200);
  initDisplay();
  initBuzzer();
  initRFID();

  loadConfig();

  // Tampilkan splash screen
  displayMessage("Absensi RFID", "Memulai...");
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

  // Otomatis kembali ke Mode Absen jika dalam Mode Registrasi selama 5 menit tanpa aktivitas
  if (currentAppMode == "regis" && (millis() - regisModeStartTime >= REGIS_TIMEOUT_MS)) {
    Serial.println("\n[REGIS TIMEOUT 5 MENIT] Waktu Mode Registrasi habis. Kembali otomatis ke Mode Absensi...");
    setAppMode("absen");
  }

  checkWiFiConnection();
  handleHeartbeat();

  String uid = readCardUID();
  if (uid != "") {
    webPingConnected = true; // Langsung aktifkan mode absensi siap saat kartu ditempel
    if (currentAppMode == "regis") {
      processRegistration(uid);
    } else {
      processAttendance(uid);
    }
    showStandby();
  }
}
