# Arsitektur & Analisis Sistem Absensi SMK

## 1. Analisis Kebutuhan
Sistem Absensi ini ditujukan untuk mengakomodasi dua metode absensi yang berbeda (Siswa dengan RFID satu arah, Guru dengan Barcode/QR dua arah) yang terintegrasi dengan perangkat IoT (ESP32) dan memiliki sistem chatbot AI untuk bantuan penggunaan. Sistem ini dibangun tanpa framework backend (PHP Native + MVC) untuk kesederhanaan dan performa, serta memisahkan akses pengguna menjadi 5 level role.

**Kendala & Solusi Desain:**
- **Siswa tidak scan pulang:** Sistem akan menggunakan rutinitas pengecekan (_trigger_ saat load laporan atau _Cron Job_) untuk menandai kepulangan siswa secara otomatis berdasarkan jadwal kelas dan hari.
- **PHP Native MVC:** Akan dibangun arsitektur *routing* sederhana yang memisahkan Model (PDO), View (HTML/Bootstrap), dan Controller (Logic) untuk kemudahan *maintenance*.
- **ESP32 & REST API:** Dibutuhkan _layer_ API (contoh: `/api/v1/attendance.php`) dengan otentikasi sederhana untuk menerima data dari ESP32 (POST JSON) dengan aman.
- **AI Chatbot:** Akan dibangun endpoint internal yang menjembatani frontend (via AJAX) ke layanan AI eksternal (contoh: OpenAI/Gemini), dilengkapi dengan _System Prompt_ spesifik agar Chatbot memahami konteks website dan pemecahan masalah IoT.

---

## 2. Arsitektur Sistem

Berikut adalah rancangan *High-Level Architecture* sistem secara keseluruhan:

```mermaid
graph TD
    %% Entities
    Client[Browser: Admin, Guru, Kepsek, Wali Kelas, Siswa]
    IoT_RFID[ESP32 + RFID Reader]
    IoT_QR[ESP32 + QR/Barcode Scanner]
    AI_Service[External AI Provider API]
    
    %% Core System
    subgraph Web Server
        App[PHP Native MVC App]
        REST_API[REST API Layer]
        Chatbot_Controller[AI Chatbot Controller]
    end
    
    subgraph Database Server
        DB[(MySQL/MariaDB)]
    end

    %% Connections
    Client -- HTTP/HTTPS --> App
    App -- PDO --> DB
    
    IoT_RFID -- HTTP POST JSON --> REST_API
    IoT_QR -- HTTP POST JSON --> REST_API
    REST_API -- PDO --> DB
    
    Client -- AJAX/Fetch --> Chatbot_Controller
    Chatbot_Controller -- HTTP Request --> AI_Service
```

---

## 3. Struktur Folder

Sistem akan dipisahkan menjadi 4 domain utama: `/web`, `/iot`, `/database`, dan `/docs`.

```text
/
├── web/                    # Root folder aplikasi web (PHP MVC)
│   ├── app/                # Core aplikasi
│   │   ├── controllers/    # Controller untuk logic modul
│   │   ├── models/         # Interaksi Database (PDO)
│   │   ├── views/          # Tampilan antarmuka HTML & Bootstrap
│   │   └── core/           # Router, DB Wrapper, Base Class
│   ├── public/             # Folder yang diekspos ke publik (Document Root)
│   │   ├── index.php       # Entry point / Front Controller Frontend
│   │   ├── assets/         # CSS, JS, Image, Icon
│   ├── api/                # Folder khusus endpoint REST API
│   │   └── v1/
│   │       ├── attendance.php
│   │       └── device.php
│   └── config/             # Config koneksi DB, Routing, dan API Keys
├── iot/                    # Source code perangkat keras mikrokontroler
│   ├── esp32_rfid/         # Sketsa Arduino untuk RFID Reader (Siswa)
│   └── esp32_qr/           # Sketsa Arduino untuk QR/Barcode (Guru)
├── database/               # Struktur dan skema relasi
│   ├── schema.sql          # DDL (Definisi Tabel dan Relasi)
│   └── seed.sql            # Dummy data (Admin default, dll)
└── docs/                   # Dokumentasi teknis
    ├── architecture.md
    └── api_reference.md
```

---

## 4. Alur Sistem (Flowcharts)

### A. Alur Absensi Siswa (1 Arah)

```mermaid
sequenceDiagram
    participant Siswa
    participant ESP32 as ESP32 (RFID)
    participant API as REST API
    participant DB as Database

    Siswa->>ESP32: Tap Kartu RFID (Pagi Hari)
    ESP32->>API: POST /api/v1/attendance (rfid_uid, device_id)
    API->>DB: Cek kecocokan rfid_uid & jadwal absen
    alt Jadwal Cocok & Belum Absen
        API->>DB: Insert Absensi Masuk (Status Hadir/Terlambat)
        DB-->>API: Success
        API-->>ESP32: 200 OK (Status Berhasil)
        ESP32-->>Siswa: Indikator Berhasil (LED/Buzzer)
    else Sudah Absen / Kartu Tidak Terdaftar
        API-->>ESP32: 400 Bad Request
        ESP32-->>Siswa: Indikator Error
    end
    
    Note over API,DB: Jam Pulang diset otomatis tanpa proses scan.<br>Status otomatis menjadi 'Selesai/Pulang' setelah jam sekolah.
```

### B. Alur Absensi Guru (2 Arah)

```mermaid
sequenceDiagram
    participant Guru
    participant ESP32 as ESP32 (QR/Barcode)
    participant API as REST API
    participant DB as Database

    Guru->>ESP32: Scan QR Code / Barcode ID
    ESP32->>API: POST /api/v1/attendance (qr_uid, device_id)
    API->>DB: Pengecekan riwayat hari ini
    
    alt Belum Absen Masuk (Pagi)
        API->>DB: Insert Absensi Masuk
        API-->>ESP32: 200 OK (Masuk Berhasil)
    else Sudah Masuk, Belum Pulang (Sore)
        API->>DB: Update data absen dengan Jam Pulang
        API-->>ESP32: 200 OK (Pulang Berhasil)
    else Sudah Absen Pulang
        API-->>ESP32: 400 Bad Request (Tugas Selesai)
    end
```

### C. Komunikasi ESP32 -> REST API

```mermaid
graph LR
    A[ESP32 Device] -->|HTTP POST Payload| B(REST API URL)
    
    subgraph Payload JSON Example
        C[{"device_id": "ESP_RFID_01", "uid": "A1B2C3D4", "type": "rfid"}]
    end
    
    A -.-> C
    
    B -->|Response JSON Example| D[{"status": "success", "message": "Hadir: Budi", "code": 200}]
```

### D. Role Access Diagram

```mermaid
graph TD
    Admin((Admin))
    Guru((Guru))
    Kepsek((Kepala Sekolah))
    WaliKelas((Wali Kelas))
    Siswa((Siswa))

    subgraph Features
        F_DashAdmin[Dashboard & Full CRUD]
        F_Absensi[Modul Absensi & Riwayat]
        F_Izin[Pengajuan Izin/Sakit]
        F_ApproveGuru[Approve Izin Guru & Laporan]
        F_ApproveSiswa[Approve Izin Siswa per Kelas]
        F_Notes[Tulis Catatan / Notes]
        F_Chatbot[AI Chatbot Assistant]
    end

    Admin --> F_DashAdmin
    Admin --> F_Chatbot
    
    Guru --> F_Absensi
    Guru --> F_Izin
    Guru --> F_Chatbot

    Kepsek --> F_ApproveGuru
    Kepsek --> F_Notes
    Kepsek --> F_Chatbot

    WaliKelas --> F_ApproveSiswa
    WaliKelas --> F_Notes
    WaliKelas --> F_Chatbot

    Siswa --> F_Absensi
    Siswa --> F_Izin
    Siswa --> F_Chatbot
```

---

## 5. Daftar Modul

1. **Modul Autentikasi**:
   - Login, Session Management, dan Role-based Access Control (RBAC).
2. **Modul Master Data (Admin)**:
   - Manajemen User (Siswa, Guru, Kepsek, Wali Kelas).
   - Manajemen Akademik (Kelas, Jadwal).
   - Manajemen IoT (Registrasi alat ESP32, assign RFID ke Siswa, assign QR ke Guru).
3. **Modul Absensi & API**:
   - REST API handler.
   - Algoritma penentuan status (Hadir/Terlambat).
   - Sistem auto-pulang untuk siswa.
4. **Modul Izin & Catatan**:
   - Pengajuan dokumen Izin/Sakit.
   - Hierarki *Approval* (Kepsek -> Guru; Wali Kelas -> Siswa).
   - Penulisan instruksi/catatan (*Notes*).
5. **Modul AI Chatbot**:
   - *Widget overlay* di setiap halaman dashboard user.
   - Panggilan backend ke API AI untuk panduan *troubleshooting* (misal: "RFID tidak terbaca") dan navigasi UI.

---

## 6. Daftar Dependency

Sesuai permintaan untuk membuat struktur sederhana tanpa framework kompleks:

**Backend (Web & API):**
* PHP 8.x (Native dengan PDO & cURL extension)
* MySQL 5.7+ / MariaDB 10.x

**Frontend:**
* HTML5, CSS3, ES6 JavaScript
* Bootstrap 5 (CSS Framework via CDN atau lokal untuk UI cepat dan responsif)
* FontAwesome (untuk icon, opsional)

**IoT (Perangkat Keras):**
* Mikrokontroler: ESP32 (Modul WiFi terintegrasi)
* Modul RFID: MFRC522 (untuk Siswa)
* Modul Barcode/QR Scanner: Modul TTL/UART Scanner (untuk Guru)
* Library Arduino: `WiFi.h`, `HTTPClient.h`, `ArduinoJson.h` (wajib untuk komunikasi API), dan `MFRC522.h`.

**Eksternal:**
* AI Provider API (contoh: Google Gemini API / OpenAI API).

## User Review Required
Silakan tinjau struktur arsitektur, _flowchart_ logika, dan modul yang diajukan di atas. Berikan arahan apakah _blueprint_ ini sudah sesuai dengan visi PRD (Product Requirements Document) Anda sebelum kita melanjutkan implementasi lebih dalam.
