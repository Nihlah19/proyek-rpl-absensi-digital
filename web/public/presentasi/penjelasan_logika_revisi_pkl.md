# PANDUAN LOGIKA REVISI FITUR PRESENSI PKL & ARSITEKTUR SISTEM
## Sistem Presensi Terpadu IoT RFID, QR Code, Mobile PKL & Asisten AI — SMK NU Wargabinangun
Dokumentasi Resmi & Panduan Materi Presentasi Sidang / Pengujian Sistem

---

## 1. Latar Belakang & Alasan Revisi (Problem Statement)
Pada implementasi awal sistem presensi:
- Seluruh siswa diwajibkan melakukan presensi fisik dengan menempelkan (**tap**) kartu RFID pada mesin pintu gerbang sekolah antara pukul 06:30 – 07:15 WIB.
- **Masalah Lapangan:** Siswa kelas 12 yang sedang melaksanakan **Praktik Kerja Lapangan (PKL) / Magang Industri** tidak berada di lingkungan fisik sekolah, melainkan tersebar di berbagai Dunia Usaha / Dunia Industri (DUDI).
- **Karakteristik DUDI:**
  1. Siswa tidak mungkin datang ke sekolah hanya untuk tap kartu RFID.
  2. Jam kerja DUDI sangat beragam (contoh: bengkel buka 08:30 WIB, kantor instansi buka 07:45 WIB, atau sistem shift). Batasan jam masuk kaku sekolah (07:15 WIB) tidak relevan.
  3. Hari kerja DUDI bervariasi (ada yang 5 hari kerja, 6 hari kerja, atau jadwal custom).
  4. Pihak sekolah, wali kelas, guru pembimbing, dan orang tua membutuhkan bukti kehadiran valid (anti-kecurangan) serta rekapitulasi akumulasi (Hadir, Sakit, Izin, Alfa).

Oleh karena itu, dilakukan **Revisi Fitur Presensi Khusus Siswa PKL (PRD v4.0)**.

---

## 2. Logika Revisi pada Use Case Diagram
File diagram: `presentasi/use_case_diagram_sistem_absensi_pkl.png`

Di dalam Use Case Diagram, logika absensi dibuat **100% harmonis dan serasi** dengan 2 metode presensi yang telah ada sebelumnya:

1. **Jalur Presensi Guru:**
   - Aktor `Guru` ──> `(Scan barcode QR)` ──`<<Include>>`──> `(Validasi identitas)` ──`<<Include>>`──> `(Simpan data absensi)`.
2. **Jalur Presensi Siswa Reguler (Di Sekolah):**
   - Aktor `Siswa` ──> `(Scan absen RFID)` ──`<<Include>>`──> `(Validasi kartu UID)` ──`<<Include>>`──> `(Simpan data absensi)`.
3. **Jalur Presensi Siswa PKL (Di Industri) [REVISI BARU]:**
   - Aktor `Siswa` ──> `(Absen mandiri PKL)` ──`<<Include>>`──> `(Validasi lokasi & swafoto)` ──`<<Include>>`──> `(Simpan data absensi)`.

### Output Terpusat yang Harmonis:
Ketiga metode presensi di atas menyatu ke dalam satu entitas inti yang sama: **`(Simpan data absensi)`**. Dari sini, sistem secara otomatis mengeksekusi:
- `<<Extends>>` ──> `(Tampilkan status absensi)` (Menampilkan konfirmasi sukses hadir pada UI siswa/guru).
- `<<Extends>>` ──> `(Mengirim notif kehadiran siswa)` ──> Terhubung ke aktor `[Bot Father / Telegram]` dan diteruskan ke aktor `[Wali kelas]`.

---

## 3. Logika Revisi pada Activity Diagram Master (8 Swimlanes)
File diagram: `presentasi/activity_diagram_sistem_absensi_pkl.png`

Pada diagram alir aktivitas master 8 swimlane:
1. **Swimlane Siswa:**
   - Siswa memiliki dua jalur absensi:
     - **Siswa Reguler:** Menjalankan `Scan absen RFID` di gerbang.
     - **Siswa PKL:** Menjalankan `Absen mandiri PKL (Swafoto & GPS)` via ponsel pintar di lokasi DUDI.
2. **Swimlane Wali Kelas:**
   - Menjalankan aktivitas `Kelola kelompok & jadwal PKL` untuk mendaftarkan titik GPS tempat magang, radius toleransi meter, dan hari kerja aktif siswa.
3. **Swimlane System:**
   - Menerima payload presensi dari seluruh kanal (RFID, QR, maupun PKL).
   - Memvalidasi keabsahan data, lalu mencatatnya ke dalam `Data tersimpan dalam database`.
4. **Swimlane Bot Father:**
   - Menerima pemicu otomatis pasca data tersimpan untuk menjalankan `Notif Presensi PKL ke Telegram` menuju grup kelas binaan wali kelas dan orang tua.

---

## 4. Logika Rinci Alur Kerja Teknis PKL (Deep Technical Workflow)
File diagram: `presentasi/activity_diagram_alur_kerja_presensi_pkl.png`

Secara teknis operasional, alur kerja mandiri siswa PKL bekerja melalui 4 tahapan validasi anti-kecurangan:
1. **Validasi Hari Aktif (Active Days Check):**
   - Sistem memeriksa kalender hari saat ini terhadap aturan hari berangkat yang ditentukan Wali Kelas.
   - Jika hari libur PKL, tombol presensi otomatis terkunci.
2. **Geofencing & Kalkulasi Jarak Haversine:**
   - Mengambil koordinat GPS presisi tinggi (`navigator.geolocation.getCurrentPosition` dengan `enableHighAccuracy: true`).
   - Menghitung jarak lurus ke titik DUDI dengan rumus matematika Haversine:
     $$d = 2R \cdot \arcsin\left(\sqrt{\sin^2\left(\frac{\Delta \phi}{2}\right) + \cos(\phi_1)\cos(\phi_2)\sin^2\left(\frac{\Delta \lambda}{2}\right)}\right)$$
   - Jika `jarak <= radius_toleransi_meter` (misal $\le 50$ meter), status lokasi **VALID**. Jika di luar radius, tombol kirim dinonaktifkan.
3. **Verifikasi Kamera WebRTC (Live Anti-Fraud Selfie):**
   - Mengakses sensor kamera depan secara langsung (streaming WebRTC).
   - Tidak menyediakan opsi unggah berkas galeri (mencegah manipulasi foto lama / foto titipan).
4. **Sesi Masuk & Pulang Terbuka (Open Time Window):**
   - **Sesi Masuk:** Menangkap swafoto masuk, jam datang, dan titik koordinat pertama kali di hari kerja. Langsung tercatat berstatus `'pkl'` (Hadir).
   - **Sesi Pulang:** Menangkap swafoto pulang saat siswa selesai beraktivitas di hari yang sama.
5. **Akumulasi Otomatis (H / S / I / A):**
   - Setiap akhir hari kerja aktif, cronjob/scheduler mengevaluasi siswa yang tidak presensi dan tidak mengajukan izin sakit menjadi status **Alfa PKL**.
   - Dashboard menyajikan rekapitulasi persentase kehadiran lengkap siap ekspor ke Excel (.xlsx) untuk penilaian sertifikat PKL.

---

## 5. Class Diagram Berorientasi Objek & Penerapan Prinsip SOLID
File diagram: `presentasi/class_diagram_solid.png`

Diagram kelas dirancang dengan arsitektur 4-Tier Enterprise yang mematuhi kelima pilar **SOLID**:

```
[ Tier 1: Controllers (SRP) ]
  ├── AbsensiController
  ├── PklController
  ├── ChatbotController
  └── AuthController
        │
        ▼ (Dependency Inversion & Open/Closed)
[ Tier 2: Interfaces & Strategies (OCP, LSP, ISP) ]
  ├── AttendanceStrategyInterface  <── RfidAttendanceStrategy, PklAttendanceStrategy
  ├── GeofenceInterface            <── HaversineGeofenceService
  ├── NotificationInterface        <── TelegramBotDispatcher
  └── AiProviderInterface          <── GroqProvider, GrokProvider, GeminiProvider
        │
        ▼ (Dependency Injection)
[ Tier 3: Data Access & Services (SRP & DIP) ]
  ├── AbsensiModel, PklModel, UserModel
  ├── ChatbotService (Context Grounding & Guardrails)
  └── DatabaseInterface (PDO Database Abstraction)
        │
        ▼ (Domain Mapping)
[ Tier 4: Entities Layer ]
  └── User, Siswa, Guru, PklKelompok, AbsensiRecord
```

### Penerapan Detail Prinsip SOLID:
1. **S — Single Responsibility Principle (SRP):**
   - Masing-masing Controller hanya bertanggung jawab atas satu domain tugas spesifik. `PklController` hanya menangani operasi PKL (absen mandiri, plotting kelompok, monitoring). `AbsensiController` fokus pada RFID/QR reguler. `ChatbotController` hanya memproses orkestrasi AI.
2. **O — Open/Closed Principle (OCP):**
   - Modul presensi terbuka untuk penambahan metode baru namun tertutup dari modifikasi kode inti melalui `AttendanceStrategyInterface`. Menambah metode presensi baru (misal *Face Recognition*) cukup membuat kelas realisasi baru tanpa mengubah baris kode controller lama.
   - Modul AI terbuka untuk multi-provider melalui `AiProviderInterface` (Groq Llama-3.3, Grok-2, Gemini 1.5).
3. **L — Liskov Substitution Principle (LSP):**
   - `RfidAttendanceStrategy` dan `PklAttendanceStrategy` dapat saling menggantikan objek `AttendanceStrategyInterface` tanpa merusak kontrak pengembalian `ValidationResult` dan `AttendanceRecord`.
4. **I — Interface Segregation Principle (ISP):**
   - Interface dibuat ramping dan spesifik: `GeofenceInterface` hanya mendefinisikan metode perhitungan koordinat radius; `NotificationInterface` hanya mendefinisikan pengiriman pesan bot. Tidak ada class yang dipaksa mengimplementasikan metode yang tidak digunakannya.
5. **D — Dependency Inversion Principle (DIP):**
   - Layer Controller dan Model tidak bergantung pada kelas konkret level rendah, melainkan bergantung pada abstraksi interface (`DatabaseInterface`, `NotificationInterface`, `AiProviderInterface`).

---

## 6. Sequence Diagram Alur Pertukaran Pesan Multi-Komponen
File diagram: `presentasi/sequence_diagram_sistem_absensi.png`

Sequence Diagram menggambarkan alur pertukaran pesan secara mendalam antara 6 lifeline:
- **`Actor`** (Siswa / Guru / User)
- **`Front End`** (Web Client, Sensor Kamera WebRTC, Geolocation API, Widget Chat)
- **`Back End`** (MVC Router, `PklController`, `ChatbotController`, Services)
- **`Database`** (MySQL Relational Database Engine)
- **`Telegram Bot API`** (Bot Father Notifikasi Kelas)
- **`AI API Cloud`** (Groq / Grok / Gemini Cloud LLM)

### Skenario 1: Alur Presensi Mandiri PKL
1. **Akses:** Siswa membuka menu presensi PKL (`GET /pkl/absensi`).
2. **Inisialisasi Sensor:** Front End meminta izin hardware kamera (`getUserMedia`) dan GPS (`navigator.geolocation`). Kamera aktif dan koordinat GPS terkunci.
3. **Kirim:** Siswa mengambil foto selfie dan menekan "Kirim Presensi" (`POST /pkl/submitAbsen`).
4. **Validasi Back End:**
   - Query penugasan kelompok PKL ke Database.
   - Database mengembalikan koordinat DUDI, radius batas meter, dan hari kerja.
   - Back End mengeksekusi rumus Haversine. Jika jarak $\le$ radius, status valid.
5. **Simpan:** Back End melakukan `INSERT` ke tabel `absensi_siswa` dengan `status = 'pkl'` dan `metode = 'pkl'`.
6. **Notifikasi Asinkron:** Back End mengambil `telegram_chat_id` kelas siswa dari tabel `kelas`, lalu mengirim pesan bot ke Telegram Bot API (`POST /sendMessage`).
7. **Respon:** Back End mengembalikan JSON sukses ke Front End, yang kemudian menampilkan modal apresiasi dan memutar bunyi konfirmasi audio.

### Skenario 2: Alur Interaksi Chatbot AI Asisten
1. **Input:** User memasukkan pertanyaan natural language pada widget asisten AI.
2. **Filter Guardrail:** Back End memeriksa apakah pertanyaan melanggar domain sekolah (misal: soal PR umum matematika atau coding di luar topik). Jika ya, bot menolak dengan sopan.
3. **Grounding Database:** Back End melakukan query agregat ke MySQL untuk mengambil data riil (kehadiran harian, status PKL, daftar siswa).
4. **Prompt Engineering:** Back End menggabungkan persona SOP sekolah dan data riil ke dalam prompt.
5. **Panggilan AI API:** Back End memanggil endpoint HTTPS Cloud LLM (Groq Llama-3.3-70b / Gemini) via cURL.
6. **Logging:** Percakapan dicatat ke tabel `chat_history`.
7. **Render:** Jawaban cerdas dikirim ke Front End dan dirender dalam bentuk Markdown rapi.

---

## 7. Entity-Relationship Diagram (ERD) Basis Data MySQL
File diagram: `presentasi/erd_basis_data.png`

ERD merefleksikan arsitektur basis data relasional MySQL proyek secara presisi dengan relasi *Crow's Foot*, Primary Key (PK), dan Foreign Key (FK):

### Struktur Relasi Utama:
1. **Pengguna & Role (`users`):**
   - Relasi `1 : 1` ke tabel `siswa` (`users.id = siswa.user_id`).
   - Relasi `1 : 1` ke tabel `guru` (`users.id = guru.user_id`).
   - Relasi `1 : N` ke tabel `kelas` (`users.id = kelas.wali_kelas_id`).
2. **Modul PKL (Pendidikan Sistem Ganda):**
   - `kelas` ──`1 : N`──> `pkl_kelompok` (`kelas.id = pkl_kelompok.kelas_id`).
   - `guru` ──`1 : N`──> `pkl_kelompok` (`guru.id = pkl_kelompok.pembimbing_guru_id`).
   - `pkl_kelompok` ──`1 : N`──> `pkl_kelompok_siswa` (Junction Table anggota kelompok).
   - `siswa` ──`1 : 1`──> `pkl_kelompok_siswa` (Setiap siswa unik dalam 1 kelompok PKL).
3. **Presensi Multi-Metode (`absensi_siswa`):**
   - Terhubung `1 : N` dari `siswa` (`siswa.id = absensi_siswa.siswa_id`).
   - Menyimpan kolom komprehensif: `status` (hadir, terlambat, izin, sakit, alfa, pkl), `metode` (rfid, qr, manual, pkl), `foto_masuk`, `foto_pulang`, `latitude_masuk`, `longitude_masuk`, `jarak_meter`.
   - Terhubung opsional ke `iot_devices.id` sebagai sumber perangkat RFID gerbang.
4. **Presensi Guru (`absensi_guru`):**
   - Terhubung `1 : N` dari `guru` (`guru.id = absensi_guru.guru_id`).
5. **Perizinan & Pembinaan:**
   - `pengajuan_izin` (`user_id`, `approved_by` FK ke `users`).
   - `catatan_siswa` (`siswa_id` FK ke `siswa`, `wali_kelas_id` FK ke `users`).
   - `catatan_guru` (`guru_id` FK ke `guru`, `kepsek_id` FK ke `users`).
6. **Komunikasi Realtime & AI:**
   - `chat_history` (`user_id` FK ke `users`, mencatat prompt, respon bot, provider, token).
   - `notifikasi` (`user_id` FK ke `users`, pencatatan riwayat lonceng notifikasi).
   - `push_subscriptions` (`user_id` FK ke `users`, token web push PWA perangkat mobile).

---

## 8. Ringkasan File di Folder `presentasi/`
Semua file diagram telah diekspor dalam format gambar resolusi tinggi (High-Definition PNG) dan tersimpan di folder `presentasi/` serta telah disinkronkan ke `D:\xampp\htdocs\AbsensiRFID\presentasi`:

| No | Nama Berkas Diagram | Resolusi | Fokus Pembahasan |
| :---: | :--- | :---: | :--- |
| 1 | `use_case_diagram_sistem_absensi_pkl.png` | 1024 x 1024 | Integrasi alur Use Case fitur Absen PKL serasi dengan RFID & QR |
| 2 | `activity_diagram_sistem_absensi_pkl.png` | 800 x 1030 | Master Activity Diagram 8 Swimlane alur bisnis presensi terpadu |
| 3 | `activity_diagram_alur_kerja_presensi_pkl.png` | 1660 x 2050 | Rincian teknis verifikasi GPS Haversine, Kamera WebRTC & Anti-Fraud |
| 4 | `class_diagram_solid.png` | 2450 x 1850 | Arsitektur OOP 4-Tier mematuhi 5 prinsip SOLID (SRP, OCP, LSP, ISP, DIP) |
| 5 | `sequence_diagram_sistem_absensi.png` | 2400 x 2150 | Alur pesan Front End, Back End, Database, AI API & Telegram Bot |
| 6 | `erd_basis_data.png` | 2800 x 2250 | Skema relasional MySQL lengkap dengan PK, FK, tipe data & Crow's Foot |
| 7 | `penjelasan_logika_revisi_pkl.md` | Dokumen | Panduan komprehensif naskah penjelasan logika presentasi dan sidang |
