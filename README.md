# 🏫 Sistem Absensi Digital SMK NU Wargabinangun
**Berbasis RFID, QR Scanner & AI Assistant (Terintegrasi Telegram)**

![Version](https://img.shields.io/badge/Version-2.0-blue.svg)
![Status](https://img.shields.io/badge/Status-Active-success.svg)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4.svg?logo=php)
![Tailwind](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC.svg?logo=tailwind-css)
![Arduino](https://img.shields.io/badge/IoT-ESP8266-00979D.svg?logo=arduino)

Sistem Absensi Digital Sekolah yang dirancang khusus untuk memonitor kehadiran Siswa dan Guru secara *real-time*. Mengkombinasikan teknologi Internet of Things (IoT) melalui pemindaian kartu RFID, QR Scanner presensi mobile, manajemen izin elektronik (E-Leave), dan notifikasi cerdas melalui Telegram Bot dan AI Assistant.

---

## 🌟 Fitur Unggulan

- 💳 **RFID Tap (2-Scan System):** Pencatatan otomatis "Jam Datang" dan "Jam Pulang" menggunakan kartu RFID (MFRC522 & ESP8266).
- 📱 **QR Code Scanner:** Alternatif presensi cepat bagi Guru menggunakan kamera perangkat seluler/laptop.
- 💬 **Notifikasi Telegram Real-Time:** Pesan otomatis masuk ke grup kelas atau personal bot kurang dari 2 detik setelah scan.
- 🤖 **AI Assistant (Gemini):** Chatbot cerdas yang siap membantu 24/7 terkait kendala atau informasi sekolah.
- 📊 **Dashboard Berlapis (Multi-Role):** Hak akses spesifik untuk Admin, Kepala Sekolah, Wali Kelas, Guru, dan Siswa.
- 📝 **E-Leave (Pengajuan Izin Elektronik):** Alur persetujuan izin/sakit paperless dengan unggah bukti valid.

---

## 🛠️ Teknologi yang Digunakan

**Web Application (Software):**
- **Backend:** PHP (Native/MVC Pattern)
- **Database:** MySQL
- **Frontend:** HTML5, Tailwind CSS, Vanilla JavaScript
- **API Integration:** Telegram Bot API, Google Gemini API

**Internet of Things (Hardware):**
- **Microcontroller:** NodeMCU V3 (ESP8266)
- **Sensor:** MFRC522 RFID Reader
- **Display:** LCD I2C 16x2
- **Audio/Alert:** Active Buzzer (Buzzer Beep)

---

## 👥 Hak Akses & Role Pengguna

Sistem memiliki 5 level otorisasi:

1. **👑 Admin:** Kendali penuh data master (User, Kelas, Perangkat IoT, Jadwal) dan export laporan keseluruhan.
2. **👨‍🏫 Kepala Sekolah:** Mengawasi kinerja guru, memonitor grafik keterlambatan, memberikan peringatan/catatan, dan menyetujui (Approve/Reject) cuti Guru.
3. **👨‍💼 Wali Kelas:** Memonitor absensi kelas asuhannya, memberi catatan ke siswa, dan memvalidasi izin siswa.
4. **👩‍🏫 Guru:** Melihat rekap absensi pribadi, mengajukan izin, dan menerima catatan dari Kepsek.
5. **🎓 Siswa:** Melihat histori pindaian kartu RFID pribadi, mengajukan izin ke Wali Kelas, dan membaca catatan.

---

## ⚙️ Panduan Instalasi (Web Server)

1. **Clone Repository:**
   ```bash
   git clone https://github.com/yourusername/AbsensiRFID.git
   cd AbsensiRFID
   ```
2. **Konfigurasi Database:**
   - Buat database baru di MySQL (contoh: `db_absensirfid`).
   - Import file SQL yang tersedia di folder `database/` ke dalam database tersebut.
3. **Setup Environment:**
   - Masuk ke direktori web dan ubah `.env.example` menjadi `.env`.
   - Sesuaikan konfigurasi koneksi database, URL utama (BASEURL), Token Telegram, dan API Key AI.
4. **Install Dependencies (Frontend):**
   ```bash
   npm install
   npm run build
   ```
   *(Opsional jika Anda ingin mengembangkan CSS menggunakan Tailwind watcher: `npm run dev`)*
5. **Jalankan Server:**
   Akses melalui Localhost (contoh: `http://localhost/AbsensiRFID/web/public/`) atau konfigurasi Virtual Host.

---

## 🔧 Panduan Instalasi IoT (Perangkat Keras)

Kode program Arduino (`.ino`) terdapat di dalam direktori `iot/`.

1. **Rangkaian Kabel (ESP8266 ke MFRC522 & Buzzer):**
   - `SDA/SS` ➔ `D4`
   - `SCK` ➔ `D5`
   - `MOSI` ➔ `D7`
   - `MISO` ➔ `D6`
   - `RST` ➔ `D3`
   - `Buzzer (Positif)` ➔ `D0`
   - `Buzzer (Negatif)` ➔ `GND`
2. Buka `iot/scan_absensi/scan_absensi.ino` menggunakan **Arduino IDE**.
3. Sesuaikan variabel kredensial:
   - `WIFI_SSID` & `WIFI_PASS`
   - `API_HOST` (IP Server komputer/server Anda)
   - `API_KEY` (Sesuaikan dengan file `.env` di sisi web)
4. Compile & Upload ke board NodeMCU ESP8266.

---

## 📝 Alur Modul Pengajuan Izin (E-Leave)

Sistem *Approval* berjenjang untuk menjaga validitas izin:
- **Siswa** ➔ Mengajukan izin ➔ Divalidasi oleh **Wali Kelas**.
- **Guru / Wali Kelas** ➔ Mengajukan izin ➔ Divalidasi oleh **Kepala Sekolah**.

**Langkah:**
1. Pengguna masuk ke menu **Pengajuan Izin**, isi durasi tanggal dan lampirkan bukti PDF/Gambar.
2. Pihak penyetuju masuk ke **Persetujuan Izin**, klik *Cek File*, lalu pilih **Setujui** atau **Tolak**.
3. Jika *Disetujui*, absensi rentang tanggal tersebut akan otomatis di-bypass menjadi Izin/Sakit tanpa perlu melakukan *tap* RFID.

---

## 🔒 Keamanan & Sinkronisasi
- Semua kata sandi dalam sistem dienkripsi secara ireversibel menggunakan algoritma hashing **Bcrypt**.
- Proteksi komunikasi API IoT menggunakan mekanisme `X-Api-Key` Headers.
- Data status Wali Kelas telah direplikasi dan dienkapsulasi dengan data Guru untuk kemudahan penyusunan laporan Kepala Sekolah.
- *Default password* untuk semua akun uji coba pada tahap awal adalah: `password`.

---
*Dikembangkan sebagai Proyek Sistem Manajemen Sekolah Terpadu SMK NU Wargabinangun v2.0.*
