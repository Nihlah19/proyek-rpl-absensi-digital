# PANDUAN LOGIKA REVISI FITUR PRESENSI PKL
## Sistem Presensi Terpadu IoT RFID & Mobile PKL — SMK NU Wargabinangun
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

## 5. Ringkasan File di Folder `presentasi/`
| Nama Berkas | Deskripsi |
| :--- | :--- |
| `use_case_diagram_sistem_absensi_pkl.png` | Diagram Use Case lengkap dengan integrasi fitur Absen PKL (serasi dengan flow RFID & QR). |
| `activity_diagram_sistem_absensi_pkl.png` | Activity Diagram Master 8 Swimlane yang menyatukan alur RFID, QR, dan PKL. |
| `activity_diagram_alur_kerja_presensi_pkl.png` | Activity Diagram Khusus PKL (High Definition 1660x2050) yang merinci validasi sensor & kalkulasi. |
| `penjelasan_logika_revisi_pkl.md` | Panduan naskah penjelasan logika untuk presentasi, ujian, atau sidang skripsi. |
