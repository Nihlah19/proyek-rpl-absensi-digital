# Sistem Informasi Presensi Digital Siswa SMK Berbasis IoT RFID, Geofencing PKL & AI Chatbot

![Version](https://img.shields.io/badge/Version-2.5-emerald.svg)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success.svg)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4.svg?logo=php)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-Offline%20Compiled-38B2AC.svg?logo=tailwind-css)
![IoT](https://img.shields.io/badge/IoT-ESP8266%20%2F%20ESP32-00979D.svg?logo=arduino)
![CI](https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF.svg?logo=github-actions)

---

## Identitas Mahasiswa & Proyek RPL

| Atribut | Keterangan |
| :--- | :--- |
| **Nama** | **Nihlah Auliya** |
| **NIM** | **41257146** |
| **Kelompok** | **Sunyaragi Nexus** |
| **Tema Topik Kelompok** | **Absensi Digital Siswa SMK** |
| **Institusi Mitra / Studi Kasus** | SMK NU Wargabinangun Cirebon |
| **Repositori Publik Pribadi** | `Nihlah19/proyek-rpl-absensi-digital` |
| **Desain UI/UX (Figma)** | [Buka Desain UI/UX Absensi Digital di Figma](https://www.figma.com/design/8u9jDgeLnZYtYpfKmOWqhU/ui-ux-absensi-digital?node-id=0-1&t=9AOYUDDeMCdWBhJl-1) |
| **Berkas Dokumen Lengkap (PDF)** | [`docs/Dokumen_Lengkap_RPL_Nihlah_Auliya_41257146.pdf`](docs/Dokumen_Lengkap_RPL_Nihlah_Auliya_41257146.pdf) |

---

## Deskripsi Aplikasi & Fitur AI Chatbot

**Sistem Informasi Presensi Digital Siswa SMK NU Wargabinangun** adalah aplikasi web terintegrasi berbasis **PHP 8 MVC** yang memadukan perangkat keras **IoT RFID & QR Scanner**, modul khusus **Presensi Mandiri PKL berbasis Geofencing & WebRTC Live Selfie**, serta **Asisten AI Chatbot** untuk kebutuhan sekolah kejuruan.

### Fitur Utama Aplikasi
1. **Presensi Gerbang IoT (RFID Siswa & QR Code Guru)**
   - Pemindaian kartu RFID MFRC522 pada node ESP8266/ESP32 melalui endpoint REST JSON (`POST /api/iot/attendance/student`, `POST /api/iot/attendance/teacher`, dan `POST /api/iot/heartbeat`) menggunakan autentikasi header `X-Api-Key`.
   - Status keterangan absensi terstandar hanya **Hadir, Izin, Sakit, Alfa** (keterlambatan dicatat terpisah pada metadata ketepatan waktu, bukan membuang status Hadir), serta aturan **Auto-Alfa** otomatis bagi siswa/guru reguler yang tidak memiliki presensi/izin pada hari aktif.
2. **Modul Khusus PKL (Geofencing Haversine & Jadwal Industri Fleksibel)**
   - **Siswa PKL** melakukan check-in & check-out via smartphone menggunakan **kamera depan live WebRTC** (anti-unggah foto galeri) dan **validasi GPS Haversine Formula** sesuai titik koordinat & radius DUDI.
   - **Wali Kelas / Pembimbing PKL** dapat mengatur jadwal hari kerja kelompok PKL (5 hari, 6 hari, shift 7 hari), membebaskan jam masuk/pulang sesuai shift industri, memantau log selfie harian, dan mengelola anggota kelompok secara responsif di desktop maupun Android.
3. **Manajemen Izin/Sakit, Catatan Bimbingan & Daftar Catatan**
   - Pengajuan Izin/Sakit mendukung unggah lampiran bukti (gambar & dokumen PDF/DOCX) dengan approval berjenjang.
   - **Fitur Catatan Fleksibel (`catatan_siswa` & `catatan_guru`):** Wali Kelas dan Kepala Sekolah dapat menulis, **mengedit**, dan **menghapus** catatan bimbingan dengan proteksi kepemilikan penulis & CSRF, serta melihat seluruh arsip di halaman khusus **Daftar Catatan** (`/catatan/daftar`).
4. **Kontrol Akses Berbasis Peran (RBAC) & Keamanan**
   - Pembatasan tegas 6 peran: `admin`, `kepsek` (monitoring eksekutif guru/sekolah tanpa akses rekap & monitoring siswa), `wali_kelas`, `guru`, `pembimbing_pkl`, dan `siswa`.
   - Proteksi CSRF token, Prepared Statements PDO (kompatibel MySQL lokal & TiDB Serverless SSL), session cookie hardening, serta Tailwind CSS terkompilasi lokal (*offline-ready*) untuk pemuatan cepat di jaringan lambat.
5. **Fitur Asisten AI Chatbot (Multi-Role Grounded Assistant & Telegram Bot)**
   - Terintegrasi dengan LLM API (**Google Gemini / Groq**) dan didukung pengambilan konteks basis data real-time (**RAG ringan**).
   - **Smart Prompt & Privacy Boundary Guard:** Jawaban chatbot difokuskan langsung hanya pada pertanyaan pengguna (misalnya ketika siswa Kelas 10 TKJ menanyakan siapa wali kelasnya, AI hanya menjawab tepat wali kelas 10 TKJ terkait tanpa membocorkan daftar kelas lain atau komentar tidak relevan).
   - **Keamanan Kredensial UI:** Input API Key dikonfigurasi aman tanpa memicu pop-up simpan password browser agar kunci Gemini/API tidak terekspos.
   - **Integrasi Notifikasi Telegram & Web Push:** Laporan kehadiran dan catatan sekolah dapat diteruskan melalui bot Telegram dan notifikasi sistem.

---

## Tautan Video Presentasi & Dokumentasi

| Tahap Video | Fokus Bahasan | Tautan Video | Status |
| :--- | :--- | :--- | :--- |
| **Video Progres 1** | Penjelasan Dokumen PRD / SRS & Konsep Aplikasi | `[Lengkapi Link Video Progres 1 di Sini]` | Menunggu Tautan Upload |
| **Video Progres 2** | Pemodelan UML (Use Case, ERD, Activity, Sequence) & UI/UX Figma | `[Lengkapi Link Video Progres 2 di Sini]` | Menunggu Tautan Upload |
| **Video Final Demo** | Live Demo Sistem Utuh, IoT, PKL Geofencing, AI Chatbot & CI/CD | `[Lengkapi Link Video Final Demo di Sini]` | Menunggu Tautan Upload |

> **Berkas Referensi Terkait di Repositori:**
> - Naskah Skrip Video Presentasi Lengkap: [`docs/NASKAH_VIDEO_PRESENTASI_LENGKAP.md`](docs/NASKAH_VIDEO_PRESENTASI_LENGKAP.md)
> - Dokumen Lengkap Terjilid (PRD/SRS + SDD UML & Figma + Laporan Pengujian/CI-CD): [`docs/Dokumen_Lengkap_RPL_Nihlah_Auliya_41257146.pdf`](docs/Dokumen_Lengkap_RPL_Nihlah_Auliya_41257146.pdf)
> - Dokumentasi Endpoint IoT API: [`docs/api.md`](docs/api.md)

---

## Petunjuk Instalasi Aplikasi (Setup Instruction)

### 1. Prasyarat Sistem
- **Web Server & PHP:** PHP `>= 8.1` (ekstensi `pdo`, `pdo_mysql`, `mbstring`, `curl`, `gd`, `fileinfo` aktif) — misalnya XAMPP / Laragon / Docker.
- **Basis Data:** MySQL `8.0+` / MariaDB `10.4+` / TiDB Cloud Serverless.
- **Node.js (Opsional untuk rebuild Tailwind CSS):** Node.js `>= 18.x` & npm.
- **Perangkat IoT (Opsional):** ESP8266 / ESP32 + RFID MFRC522 / Scanner QR Code.

### 2. Langkah Instalasi Lokal (XAMPP / Apache)
1. **Clone repositori:**
   ```bash
   git clone https://github.com/Nihlah19/proyek-rpl-absensi-digital.git AbsensiRFID
   cd AbsensiRFID
   ```
2. **Konfigurasi Environment (`.env`):**
   Salin file contoh `.env.example` menjadi `.env` di root proyek dan sesuaikan kredensial database serta API Key:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=absensi_smk
   DB_USER=root
   DB_PASS=
   GEMINI_API_KEY=isi_api_key_gemini_anda
   TELEGRAM_BOT_TOKEN=opsional_token_bot_telegram
   ```
3. **Import Basis Data:**
   Buat database `absensi_smk` pada MySQL/phpMyAdmin atau TiDB, lalu impor berkas skema SQL yang tersedia di folder `database/`.
4. **Build Aset CSS Offline (Opsional — CSS hasil kompilasi sudah tersedia di repo):**
   ```bash
   npm install
   npx tailwindcss -i ./src/input.css -o ./web/public/assets/css/tailwind.css --minify
   ```
5. **Jalankan Aplikasi:**
   Pastikan modul Apache & MySQL berjalan, lalu akses melalui browser:
   - Lokal: `http://localhost/AbsensiRFID/web/public/`
   - Atau via PHP Built-in Server dari direktori `web/public/`:
     ```bash
     php -S localhost:8080 -t web/public
     ```

### 3. Menjalankan Pengujian Otomatis & Linting (CI Check Mandiri)
Jalankan seluruh unit test dan validasi database dengan perintah:
```bash
php tests/run_tests.php
```
Untuk memeriksa sintaks PHP pada seluruh controller dan model:
```bash
php -l web/app/controllers/CatatanController.php
php -l web/app/models/Catatan.php
```

---

## Struktur Direktori Utama
```text
.
├── .github/workflows/ci.yml       # Pipeline CI/CD GitHub Actions (PHP Lint & Unit Tests)
├── database/                      # Skema SQL & migrasi tabel MySQL/TiDB
├── docs/                          # PRD, API Docs, Diagram Activity, & PDF Lengkap Terjilid
├── iot/                           # Firmware ESP8266/ESP32 untuk mesin tap RFID & QR
├── presentasi/                    # Diagram UML & berkas presentasi proyek
├── src/input.css                  # Sumber CSS Tailwind (dikompilasi ke web/public/assets/css/tailwind.css)
├── tests/run_tests.php            # Suite pengujian otomatis end-to-end
└── web/
    ├── api/                       # Entry point Vercel Serverless PHP
    ├── app/                       # Inti MVC (Controllers, Models, Views, Helpers)
    └── public/                    # Document root, PWA manifest, assets JS/CSS offline
```
