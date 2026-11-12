# NASKAH SKRIP RESMI PRESENTASI VIDEO PROYEK AKHIR REKAYASA PERANGKAT LUNAK (OBE)
## PROGRAM STUDI S1 TEKNIK INFORMATIKA — STMIK IKMI CIREBON
### Mata Kuliah: Rekayasa Perangkat Lunak (TIKB2503021) | Semester 3
### Dosen Pengampu: Yudhistira Arie Wijaya, M.Kom., CDS.

---

**Judul Proyek:**  
*Sistem Informasi Presensi Terpadu Berbasis IoT RFID & Modul Khusus Presensi PKL Geofencing dengan Integrasi Asisten Kecerdasan Artifisial (AI Chatbot) pada SMK NU Wargabinangun Cirebon*

**Spesifikasi Rekaman Wajib (Sesuai Panduan OBE):**
1. **Webcam Wajah Aktif**: Wajib menampilkan wajah mahasiswa (lingkaran/kotak webcam di pojok layar) sepanjang video tanpa tertutup.
2. **Kualitas Audio & Video**: Resolusi 1080p (Full HD), audio jernih tanpa noise latar.
3. **Penyampaian Narasi**: Percaya diri, intonasi tegas dan terstruktur, tidak sekadar membaca teks kaku.
4. **Live Demo (Video Final)**: Demo dilakukan langsung di browser secara *live execution*, bukan screenshot.

---

# DAFTAR ISI NASKAH
1. [VIDEO PROGRES 1: PRD & KONSEP APLIKASI (Durasi 3–5 Menit)](#video-progres-1-prd--konsep-aplikasi)
2. [VIDEO PROGRES 2: PEMODELAN UML & UI/UX FIGMA (Durasi 5–7 Menit)](#video-progres-2-pemodelan-uml--uiux-figma)
3. [VIDEO FINAL: LIVE DEMO SISTEM UTUH, AI, TESTING & CI/CD (Durasi 8–12 Menit)](#video-final-live-demo-sistem-utuh-ai-testing--cicd)

---

<div style="page-break-after: always;"></div>

# VIDEO PROGRES 1: PRD & KONSEP APLIKASI
* **Bobot Penilaian:** 20% dari Total Nilai Video
* **Target Durasi:** 3 – 5 Menit (Ideal: 04:15)
* **Luaran yang Ditampilkan:** Dokumen PRD / SRS PDF (Standar IEEE 830 / ISO 29148)

---

### TABEL RUNDOWN WAKTU & AKSI LAYAR (VIDEO 1)
| Menit | Segmen Bahasan | Tampilan Layar (Screen Action) |
| :--- | :--- | :--- |
| **00:00 - 00:40** | Pembukaan, Identitas Diri, Judul Proyek, & Latar Belakang Masalah | Buka Halaman Cover Dokumen PRD PDF. Webcam aktif di pojok kanan atas. |
| **00:40 - 01:30** | Rumusan Masalah Nyata & Nilai Guna Aplikasi | Scroll ke Bab 1 PRD (Executive Summary & Problem Statement). Kursor menunjuk poin masalah PKL dan gerbang RFID. |
| **01:30 - 02:45** | Kebutuhan Fungsional (User Stories & Acceptance Criteria) | Scroll ke Bab 3 & 4 PRD (Matrix User Roles & Functional Requirements). Tunjukkan tabel RBAC 5 aktor. |
| **02:45 - 03:30** | Kebutuhan Non-Fungsional (NFR: Keamanan, Performa, Geofencing) | Scroll ke Bab NFR. Tunjukkan poin keamanan data, formula jarak Haversine, dan kamera live WebRTC. |
| **03:30 - 04:15** | Peran Strategis Fitur AI (Multi-Provider Gemini/Groq dengan RBAC Sync) | Tunjukkan Bab Spesifikasi Fitur AI. Sorot fungsi RAG database & proteksi anti-manipulasi data. |
| **04:15 - 04:30** | Penutup & Transisi ke Tahap Perancangan UML | Kembali ke ringkasan dokumen PRD. Salam penutup singkat dan percaya diri. |

---

### SKRIP KATA DEMI KATA (VERBATIM SCRIPT) — VIDEO 1

#### [00:00 - 00:40] Pembukaan & Identitas Diri
*(Aksi Layar: Buka halaman judul dokumen PRD PDF. Wajah tersenyum, kontak mata ke kamera/webcam).*

> "Assalamu’alaikum Warahmatullahi Wabarakatuh.  
> Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya, M.Kom., CDS. selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan mahasiswa sekalian.
> 
> Perkenalkan, nama saya **[Nama Anda]**, NIM **[NIM Anda]**, mahasiswa Program Studi S1 Teknik Informatika STMIK IKMI Cirebon.
> 
> Pada kesempatan Video Progres 1 ini, saya akan memaparkan dokumen Product Requirements Document atau PRD yang telah saya susun secara mandiri untuk proyek rekayasa perangkat lunak berjudul:  
> **'Sistem Presensi Terpadu Berbasis IoT RFID & Modul Khusus Presensi PKL Geofencing dengan Integrasi Asisten Kecerdasan Artifisial pada SMK NU Wargabinangun Cirebon'**."

#### [00:40 - 01:30] Masalah Nyata & Nilai Guna Aplikasi
*(Aksi Layar: Scroll ke Bab 1 Dokumen PRD. Arahkan mouse pointer ke poin-poin permasalahan).*

> "Proyek ini dilatarbelakangi oleh permasalahan operasional nyata di SMK NU Wargabinangun.  
> Pertama, sekolah telah memiliki mesin gerbang fisik berbasis kartu RFID untuk siswa reguler. Namun, ketika siswa kelas 11 dan 12 diterjunkan untuk mengikuti program Praktik Kerja Lapangan (PKL) di puluhan Dunia Usaha dan Dunia Industri (DUDI) yang tersebar, mereka tidak dapat melakukan presensi di gerbang sekolah.
> 
> Kedua, jam kerja instansi industri mitra bersifat variatif dan fleksibel—ada bengkel yang buka jam setengah sembilan pagi, kantor dinas jam delapan, serta hari kerja yang berbeda antara lima hingga enam hari. Penggunaan aturan jam sekolah konvensional yang kaku seringkali menimbulkan ketidakadilan status 'terlambat' atau bahkan 'alfa' yang tidak akurat.
> 
> Ketiga, pihak sekolah, wali kelas, dan pembimbing industri kesulitan merekap data akumulasi kehadiran secara real-time yang bebas dari kecurangan titip absen atau pemalsuan lokasi GPS.
> 
> Melalui aplikasi ini, nilai guna yang kami hadirkan adalah sentralisasi ekosistem presensi sekolah yang menggabungkan tapping RFID fisik di sekolah, presensi mandiri PKL berbasis koordinat geofencing dan swafoto realtime kamera smartphone, serta otomasi kalkulasi rekapitulasi nilai kehadiran."

#### [01:30 - 02:45] Kebutuhan Fungsional (User Stories & Acceptance Criteria)
*(Aksi Layar: Scroll ke Bab Matrix Role RBAC dan Functional Requirements).*

> "Berdasarkan analisis kebutuhan berstandar IEEE 830, sistem ini melayani 5 entitas peran pengguna utama secara independen:
> 
> 1. **Administrator**: Mengelola data induk sekolah, pairing kartu RFID siswa, konfigurasi jadwal global, monitoring terminal IoT, serta tata kelola akun.
> 2. **Dewan Guru**: Melakukan presensi kedatangan menggunakan QR Code dinamis terenkripsi, memantau riwayat mengajar, dan mengajukan permohonan dispensasi atau izin.
> 3. **Wali Kelas**: Memiliki modul kontrol rombel untuk mengatur hari kerja aktif kelompok PKL (misalnya Senin sampai Jumat atau Senin sampai Sabtu), menentukan koordinat GPS DUDI beserta radius toleransi meter, serta memvalidasi pengajuan izin siswa.
> 4. **Siswa PKL**: Mengakses portal mandiri untuk melakukan check-in dan check-out menggunakan kamera swafoto langsung dari sensor browser smartphone dengan verifikasi lokasi GPS dalam radius yang sah.
> 5. **Kepala Sekolah**: Mengakses executive dashboard analitik untuk memantau tingkat kehadiran dewan guru dan kedisiplinan siswa magang secara realtime tanpa perlu menunggu laporan manual bulanan."

#### [02:45 - 03:30] Kebutuhan Non-Fungsional (NFR)
*(Aksi Layar: Scroll ke Bab Non-Functional Requirements. Tunjukkan parameter keamanan dan performa).*

> "Untuk menjamin reliabilitas dan integritas data, spesifikasi kebutuhan non-fungsional dirancang dengan standar ketat:
> - **Integritas Lokasi (Anti-Fraud Geofencing)**: Validasi jarak server-side menggunakan **Haversine Formula** dengan toleransi akurasi GPS tinggi, menolak presensi jika siswa berada di luar radius meter yang ditetapkan.
> - **Integritas Citra (Anti-Spoofing Camera)**: Menggunakan WebRTC kamera perangkat secara langsung tanpa opsi upload file galeri, mencegah penggunaan foto lama atau foto orang lain.
> - **Security & Authorization**: Otentikasi berbasis session tersanitasi dengan Role-Based Access Control (RBAC) ketat dan parameter binding PDO untuk mencegah ancaman SQL Injection dan XSS.
> - **Kinerja Realtime**: Response time server di bawah 500 milidetik dan tampilan antarmuka responsif berbasis Tailwind CSS yang optimal di layar smartphone."

#### [03:30 - 04:15] Peran Strategis Fitur AI (Multi-Provider LLM Engine)
*(Aksi Layar: Scroll ke Bab Spesifikasi Fitur AI. Tunjukkan diagram alur integrasi AI).*

> "Salah satu pilar utama pada proyek ini adalah penanaman fitur Kecerdasan Artifisial (AI Chatbot Assistant).  
> AI di sini bukan sekadar tempelan gimmick, melainkan bertindak sebagai **Asisten Cerdas Akademik & Kedisiplinan** yang terintegrasi secara *context-aware* dengan basis data sistem.
> 
> Arsitektur AI memanfaatkan Multi-Provider LLM (Google Gemini 2.5 Flash dan Groq Llama-3.3-70B) yang dilengkapi lapisan keamanan:
> 1. **Data Manipulation Guard**: AI secara otomatis menolak dan memblokir permintaan berbahaya yang mencoba mengubah status absensi (misal: 'ubah alfa saya jadi hadir').
> 2. **RBAC Context Sync**: AI menyaring informasi sesuai peran pengguna yang sedang login—siswa hanya dapat menanyakan datanya sendiri, sedangkan Kepala Sekolah dapat meminta rekap analitik tingkat sekolah.
> 3. **Natural Query Assistance**: Membantu pengguna memahami SOP presensi, rekap kehadiran, dan regulasi magang dalam bahasa natural."

#### [04:15 - 04:30] Penutup Video 1
*(Aksi Layar: Arahkan kursor ke cover dokumen, tatap webcam).*

> "Demikian pemaparan Dokumen PRD dan konsep dasar rekayasa sistem absensi ini.  
> Pada tahap selanjutnya, kebutuhan ini telah saya turunkan ke dalam perancangan arsitektur berorientasi objek dan 4 diagram UML yang akan saya bedah pada Video Progres 2.  
> Terima kasih atas perhatian Bapak dosen. Wassalamu’alaikum Warahmatullahi Wabarakatuh."

---

<div style="page-break-after: always;"></div>

# VIDEO PROGRES 2: PEMODELAN UML & UI/UX FIGMA
* **Bobot Penilaian:** 30% dari Total Nilai Video
* **Target Durasi:** 5 – 7 Menit (Ideal: 06:15)
* **Luaran yang Ditampilkan:** Dokumen SDD PDF, 4 Diagram UML (`presentasi/`), ERD, & Prototipe Figma

---

### TABEL RUNDOWN WAKTU & AKSI LAYAR (VIDEO 2)
| Menit | Segmen Bahasan | Tampilan Layar (Screen Action) |
| :--- | :--- | :--- |
| **00:00 - 00:30** | Pembukaan & Pengantar Perancangan Perangkat Lunak | Buka Cover Dokumen SDD / Folder `presentasi/`. Webcam aktif. |
| **00:30 - 01:45** | Bedah Diagram 1: Use Case Diagram & Skenario Aktor | Buka gambar `use_case_diagram_sistem_absensi_pkl.png`. Zoom in ke aktor Admin, Guru, Siswa, Wali Kelas, Kepsek, AI Asisten, dan Bot Father. |
| **01:45 - 02:45** | Bedah Diagram 2: Activity Diagram Alur Bisnis & AI | Buka `activity_diagram_sistem_absensi_pkl.png`. Telusuri swimlane Admin, Guru, Siswa, Wali Kelas, Kepsek, AI Chatbot, dan System. |
| **02:45 - 03:50** | Bedah Diagram 3: Class Diagram Berorientasi Objek (SOLID) | Buka `class_diagram_solid.png`. Jelaskan Controller, Model, Helper, Database, dan relasi Dependency Inversion. |
| **03:50 - 04:45** | Bedah Diagram 4: Sequence Diagram Pertukaran Pesan & ERD | Buka `sequence_diagram_sistem_absensi.png` & `erd_basis_data.png`. Tunjukkan alur pertukaran pesan User-Frontend-Backend-DB-AI. |
| **04:45 - 05:50** | Walkthrough Prototipe Interaktif UI/UX Figma | Buka Figma / browser Figma. Demo klik prototipe landing page, dashboard multi-role, dan antarmuka presensi PKL mobile. |
| **05:50 - 06:15** | Kesimpulan Desain & Kesiapan Konstruksi Kode | Tampilkan ringkasan SDD, senyum percaya diri, salam penutup. |

---

### SKRIP KATA DEMI KATA (VERBATIM SCRIPT) — VIDEO 2

#### [00:00 - 00:30] Pembukaan
*(Aksi Layar: Tampilkan dokumen Software Design Document (SDD) dan folder diagram UML. Webcam aktif di sudut).*

> "Assalamu’alaikum Warahmatullahi Wabarakatuh.  
> Selamat datang kembali pada Video Progres 2 mata kuliah Rekayasa Perangkat Lunak. Saya **[Nama Anda]**, NIM **[NIM Anda]**.
> 
> Melanjutkan hasil analisis kebutuhan pada PRD sebelumnya, pada video ini saya akan membedah perancangan arsitektur sistem secara komprehensif, mencakup 4 Diagram UML Utama yang berorientasi objek dan mematuhi prinsip SOLID, struktur ERD basis data relasional, serta walkthrough prototipe interaktif UI/UX di Figma."

#### [00:30 - 01:45] Use Case Diagram & Skenario Aktor
*(Aksi Layar: Buka file `presentasi/use_case_diagram_sistem_absensi_pkl.png`. Lakukan zoom in perlahan pada area-area use case).*

> "Mari kita telaah diagram pertama, yaitu **Use Case Diagram**.  
> Di dalam sistem ini terdapat 5 aktor manusia dan 2 aktor eksternal sistem, yaitu: Kepsek, Wali Kelas, Guru, Siswa, Admin, serta AI Asisten dan Bot Father Telegram.
> 
> Perhatikan boundary sistem yang memisahkan tanggung jawab:
> - Siswa memiliki use case utama **Scan Absen RFID** di gerbang sekolah yang meng-*include* proses validasi UID kartu ke basis data.  
> Pada pembaruan modul PKL, siswa magang mengeksekusi use case **Presensi Mandiri PKL** dengan validasi geofencing GPS dan foto swafoto.
> - Wali Kelas memiliki wewenang untuk **Mengatur Hari Berangkat PKL**, menetapkan koordinat DUDI, serta **Persetujuan Izin Siswa** melalui mekanisme *extend* ('Setujui Izin' atau 'Tolak Izin').
> - Guru dapat melakukan **Scan Barcode QR Dinamis**, mengajukan izin kehadiran kepada Kepala Sekolah, dan menerima catatan pembinaan.
> - Kepala Sekolah memegang use case monitoring laporan presensi dewan guru dan persetujuan izin dinas.
> - Pengguna dapat berinteraksi dengan use case **Gunakan Chat Bot AI**, di mana sistem mengeksekusi proses pencarian konteks database, validasi hak akses, hingga menyajikan jawaban solusi."

#### [01:45 - 02:45] Activity Diagram Alur Bisnis & AI
*(Aksi Layar: Buka file `presentasi/activity_diagram_sistem_absensi_pkl.png`. Arahkan kursor mengikuti alur antar-swimlane).*

> "Diagram kedua adalah **Activity Diagram** yang memodelkan alur kerja sistem secara paralel melintasi 8 swimlane: Admin, Guru, Siswa, Wali Kelas, Kepsek, AI Chatbot, Bot Father, dan System.
> 
> Alur diawali dari login bersama dengan percabangan *decision* 'Apakah Login Berhasil?'.  
> - Jika Siswa melakukan presensi RFID atau swafoto PKL, sistem memvalidasi koordinat dan identitas. Jika valid, data disimpan ke database, lalu secara asinkron memicu event ke swimlane **Bot Father** untuk mengirimkan notifikasi instan kehadiran siswa ke akun Telegram wali kelas yang bersangkutan.
> - Pada swimlane **Pengajuan Izin**, siswa atau guru mengirimkan berkas, yang kemudian mengalir ke swimlane Wali Kelas atau Kepsek untuk evaluasi keputusan. Status persetujuan langsung diperbarui ke database.
> - Pada swimlane **AI Chatbot**, ketika pengguna mengetik pertanyaan, sistem terlebih dahulu memfilter manipulasi data, memproses semantik melalui API AI, lalu mengalirkan respons analitik kembali ke antarmuka pengguna."

#### [02:45 - 03:50] Class Diagram Berorientasi Objek (Prinsip SOLID)
*(Aksi Layar: Buka file `presentasi/class_diagram_solid.png`. Tunjukkan class Controller, Model, Interface, dan Service).*

> "Diagram ketiga adalah **Class Diagram** yang dirancang dengan mematuhi prinsip **SOLID** (Object-Oriented Software Engineering):
> 
> 1. **Single Responsibility Principle (SRP)**: Tanggung jawab dipisahkan secara tegas. Kelas `Pkl` hanya mengelola logika penempatan dan kelompok magang, kelas `Absensi` menangani pencatatan log presensi, dan kelas `ChatbotController` fokus pada orkestrasi asisten AI.
> 2. **Open/Closed Principle (OCP)**: Engine kecerdasan buatan dibungkus melalui kelas antarmuka (interface), sehingga sistem terbuka untuk penambahan provider AI baru (misalnya DeepSeek atau OpenAI) tanpa perlu merombak alur utama controller.
> 3. **Liskov Substitution & Interface Segregation (LSP & ISP)**: Model data mewarisi kelas dasar `Database` dan `Controller` tanpa memaksakan method kosong yang tidak relevan.
> 4. **Dependency Inversion Principle (DIP)**: Modul tingkat tinggi seperti `AbsensiController` tidak bergantung pada implementasi driver database mentah, melainkan mengabstraksikan operasi data melalui kelas PDO Wrapper `Database` yang terenkapsulasi."

#### [03:50 - 04:45] Sequence Diagram & ERD Basis Data
*(Aksi Layar: Buka `sequence_diagram_sistem_absensi.png`, kemudian beralih ke `erd_basis_data.png`).*

> "Diagram keempat adalah **Sequence Diagram** yang menggambarkan alur pertukaran pesan secara kronologis antara 5 lifeline: User Actor, Web Frontend UI, Backend MVC Controller, Basis Data MySQL, dan External AI / Geolocation API.  
> Ketika siswa melakukan presensi PKL, Frontend mengambil koordinat GPS melalui Geolocation API dan capture frame kamera WebRTC, lalu mengirimkannya via HTTP POST async (AJAX). Backend memanggil method `Pkl::calculateDistance()` berbasis Haversine. Jika jarak <= radius, data ditulis ke tabel `absensi`, dan status JSON sukses dikembalikan ke UI secara instan.
> 
> Pada **Entity Relationship Diagram (ERD)**, arsitektur data dinormalisasi secara optimal:
> - Tabel master `users` berelasi *one-to-one* dengan tabel `siswa` dan `guru`.
> - Tabel `kelas` berelasi *one-to-many* dengan `siswa` dan tabel pivot `pkl_kelompok`.
> - Tabel `absensi` dan `siswa_pkl_lokasi` mencatat riwayat presensi harian lengkap dengan koordinat geolokasi, jam datang, jam pulang, dan lampiran swafoto."

#### [04:45 - 05:50] Walkthrough Prototipe Interaktif UI/UX Figma
*(Aksi Layar: Beralih ke browser, buka file desain Figma proyek di https://www.figma.com/design/8u9jDgeLnZYtYpfKmOWqhU/ui-ux-absensi-digital?node-id=0-1&t=9AOYUDDeMCdWBhJl-1).*

> "Sekarang, mari kita tinjau rancangan prototipe interaktif UI/UX di **Figma**. Desain ini mengadopsi standar **Wise Modern Design System** dengan palet dominan Forest Green (`#163300`), Neon Lime (`#9fe870`), dan dark-mode slate yang tenang dan profesional.
> 
> - **Landing Page**: Menyajikan portal publik yang bersih, hero banner sekolah, info profil institusi, dan akses login satu pintu.
> - **Dashboard Eksekutif Kepala Sekolah**: Memiliki kartu KPI ringkasan tingkat kehadiran dewan guru dengan *multi-segment progress bar* proporsional, grafik batang Chart.js, dan daftar approval izin.
> - **Dashboard Wali Kelas**: Menampilkan kartu rombel siswa, panel kontrol kelompok PKL, dan pemetaan lokasi DUDI.
> - **Antarmuka Mobile Siswa PKL**: Dirancang khusus *mobile-first* untuk smartphone. Siswa dapat melihat status radius GPS secara realtime, viewfinder kamera depan untuk swafoto, tombol presensi satu ketukan, dan akumulasi rekapitulasi kehadiran (Hadir, Sakit, Izin, Alfa)."

#### [05:50 - 06:15] Penutup Video 2
*(Aksi Layar: Kembali ke tampilan overview SDD, senyum dan tatap kamera).*

> "Seluruh pemodelan UML dan prototipe UI/UX ini telah diverifikasi konsistensinya terhadap kebutuhan PRD, dan menjadi cetak biru langsung dalam tahap konstruksi kode.  
> Pada Video Final berikutnya, saya akan mendemonstrasikan aplikasi web yang berjalan secara live, pengujian unit test otomatis, serta integrasi CI/CD pipeline.  
> Terima kasih. Wassalamu’alaikum Warahmatullahi Wabarakatuh."

---

<div style="page-break-after: always;"></div>

# VIDEO FINAL: LIVE DEMO SISTEM UTUH, AI, TESTING & CI/CD
* **Bobot Penilaian:** 50% dari Total Nilai Video (Paling Menentukan!)
* **Target Durasi:** 8 – 12 Menit (Ideal: 10:30)
* **Luaran yang Ditampilkan:** Aplikasi Web Live Demo di Localhost / Server, Pengujian AI Realtime, Terminal Unit Test, & GitHub Actions CI/CD Pipeline Hijau.

---

### TABEL RUNDOWN WAKTU & AKSI LAYAR (VIDEO FINAL)
| Menit | Segmen Bahasan | Tampilan Layar (Screen Action) |
| :--- | :--- | :--- |
| **00:00 - 01:00** | **Elevator Pitch** & Ringkasan Solusi Proyek | Tampilkan Landing Page Web `http://localhost/AbsensiRFID/`. Webcam aktif. Berikan pitch yang meyakinkan. |
| **01:00 - 02:00** | Arsitektur Sistem, Tech Stack, & Clean Code | Tampilkan sekilas folder struktur MVC di VS Code / editor kode, arsitektur PHP 8 Native, Tailwind CSS, MySQL. |
| **02:00 - 04:00** | **Live Demo 1:** Dashboard Eksekutif & Wali Kelas | Login sebagai Kepala Sekolah (`/dashboard`). Tunjukkan kartu metrik live. Logout, login sebagai Wali Kelas, tunjukkan konfigurasi hari kerja DUDI PKL. |
| **04:00 - 06:00** | **Live Demo 2:** Presensi Mandiri Siswa PKL (Camera & Geolocation) | Login sebagai Siswa PKL. Buka menu `/absensi/pkl`. Tunjukkan kamera live WebRTC menyala, titik lokasi GPS, dan tombol submit. |
| **06:00 - 07:30** | **Live Demo 3:** Pengujian AI Chatbot Asisten Cerdas | Klik ikon Chatbot AI di pojok kanan bawah. Ajukan pertanyaan jadwal & presensi. Uji ketahanan keamanan terhadap manipulasi data. |
| **07:30 - 08:45** | **Automated Testing:** Eksekusi Unit Test di Terminal | Buka Terminal / PowerShell. Jalankan perintah `php tests/run_tests.php`. Tunjukkan 13 unit test PASS berstatus HIJAU. |
| **08:45 - 09:45** | **DevOps & CI/CD:** Verifikasi GitHub Actions Pipeline | Buka browser repositori GitHub (`Nihlah19/Absensi`). Buka tab **Actions**. Perlihatkan build pipeline centang hijau (*Passed*). |
| **09:45 - 10:30** | Refleksi Pembelajaran Mandiri & Penutup | Tatap webcam, sampaikan refleksi pembelajaran engineering, ucapan terima kasih kepada dosen pengampu, dan salam. |

---

### SKRIP KATA DEMI KATA (VERBATIM SCRIPT) — VIDEO FINAL

#### [00:00 - 01:00] Elevator Pitch & Pembukaan
*(Aksi Layar: Tampilkan browser membuka Landing Page `http://localhost/AbsensiRFID/`. Webcam aktif di pojok).*

> "Assalamu’alaikum Warahmatullahi Wabarakatuh.  
> Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya, M.Kom., CDS. dan rekan-rekan sekalian.  
> Saya **[Nama Anda]**, NIM **[NIM Anda]**, mahasiswa S1 Teknik Informatika STMIK IKMI Cirebon.
> 
> Hari ini saya dengan bangga mempersembahkan Video Presentasi Final dan Live Demo Utuh untuk proyek akhir mata kuliah Rekayasa Perangkat Lunak:  
> **'Sistem Presensi Terpadu Berbasis IoT RFID & Modul Khusus PKL Geofencing dengan Asisten Kecerdasan Artifisial pada SMK NU Wargabinangun Cirebon'**.
> 
> **Elevator Pitch:**  
> Bagaimana sebuah institusi pendidikan kejuruan dapat memastikan kedisiplinan ratusan siswa yang tersebar di puluhan industri magang yang jauh, tanpa repot mengelola lembar kertas manual dan tanpa ketakutan titip absen?  
> Sistem yang saya bangun ini adalah solusi end-to-end yang mengintegrasikan gerbang fisik RFID sekolah, presensi mobile berbasis Geofencing GPS dan swafoto realtime WebRTC, analitik eksekutif multi-role, serta Asisten AI cerdas berstandar Clean Code dan terverifikasi CI/CD otomatis!"

#### [01:00 - 02:00] Arsitektur Sistem & Tech Stack
*(Aksi Layar: Perlihatkan struktur file proyek di VS Code: folder `web/app/controllers`, `models`, `views`, `core`).*

> "Sistem ini dibangun dengan arsitektur **Model-View-Controller (MVC) murni berbasis Native PHP 8.2**, didukung Tailwind CSS untuk antarmuka modern yang ringan dan responsif.
> 
> Struktur kode mematuhi prinsip **Clean Code & SOLID**:
> - Di folder `app/core/`, kelas `App` menangani *routing clean-URL*, dan kelas `Database` mengelola koneksi PDO dengan parameter binding terenkapsulasi.
> - Di folder `app/models/`, kelas `Pkl` dan `Absensi` mengenkapsulasi seluruh kalkulasi logika bisnis, termasuk implementasi formula geodesi Haversine untuk validasi jarak geofencing meter ke meter.
> - Seluruh alur data antar-role dikontrol secara independen menggunakan session RBAC yang aman dan terisolasi."

#### [02:00 - 04:00] Live Demo 1: Dashboard Eksekutif & Wali Kelas
*(Aksi Layar: Buka browser, lakukan login sebagai Kepala Sekolah, lalu beralih ke akun Wali Kelas).*

> "Sekarang, mari kita mulai **Live Demo secara langsung**.
> 
> Pertama, saya masuk ke portal sebagai **Kepala Sekolah**.  
> *(Login kepsek, masuk ke dashboard)*  
> Perhatikan pada dashboard eksekutif: metrik ringkasan kehadiran dewan guru disajikan dengan standar desain enterprise. Terdapat status dinamis: pada pagi hari tampil badge netral 'Menunggu Presensi', dan ketika presensi berjalan, bar proporsional multi-warna memvisualisasikan komposisi Hadir Tepat, Terlambat, Izin, dan Belum Presensi secara presisi tanpa angka kosong yang membingungkan.
> 
> Selanjutnya, saya berpindah login sebagai **Wali Kelas**.  
> *(Logout, lalu login sebagai Wali Kelas)*  
> Di dashboard Wali Kelas, terdapat modul kendali khusus kelompok PKL. Wali Kelas dapat menentukan instansi DUDI, memasukkan koordinat Google Maps tempat magang, mengatur radius toleransi meter, serta mencentang **Aturan Hari Kerja Aktif**—misalnya Senin sampai Jumat untuk instansi dinas, atau Senin sampai Sabtu untuk bengkel motor. Hari yang tidak dicentang otomatis menjadi hari libur bebas alfa."

#### [04:00 - 06:00] Live Demo 2: Presensi Mandiri Siswa PKL (Camera & Geolocation)
*(Aksi Layar: Logout, login sebagai Siswa PKL, klik menu 'Presensi PKL' di navbar / dashboard).*

> "Sekarang kita uji fitur paling krusial: **Presensi Mandiri Siswa PKL**.  
> Saya login menggunakan akun siswa magang yang terdaftar di kelompok PKL.  
> *(Buka halaman `/absensi/pkl`)*  
> 
> Perhatikan pada layar smartphone siswa:
> 1. Sistem secara otomatis meminta izin sensor GPS dan kamera depan via WebRTC.  
> *(Kamera live menyala di browser, menampilkan wajah penguji)*
> 2. Sistem menghitung jarak akurat antara koordinat GPS ponsel saat ini dengan koordinat DUDI mitra menggunakan formula Haversine.
> 3. Terdapat indikator radius hijau jika siswa berada di dalam zona DUDI.  
> 4. Ketika siswa menekan tombol **'Kirim Presensi PKL'**, sistem mengambil frame foto swafoto langsung dari stream kamera sensor, menyimpannya secara terenkripsi, mencatat stempel waktu kedatangan, dan menyajikan notifikasi instan.  
> Fitur ini 100% bebas dari pemalsuan galeri karena sistem menolak pengunggahan file foto lama!"

#### [06:00 - 07:30] Live Demo 3: Pengujian AI Chatbot Asisten Cerdas
*(Aksi Layar: Klik floating icon robot AI di pojok kanan bawah antarmuka. Modal chat AI terbuka).*

> "Berikutnya, mari kita uji modul kecerdasan artifisial: **Asisten AI Terintegrasi**.  
> Saya buka modal AI Asisten. AI ini ditenagai oleh Google Gemini API dan Groq Engine dengan sinkronisasi konteks basis data.
> 
> **Uji Skenario 1 (Pertanyaan Kedisiplinan):**  
> Saya ketik: *'Bagaimana rekap kehadiran saya selama magang PKL ini?'*  
> *(Tekan kirim. Tunggu 1 detik response stream)*  
> Perhatikan, AI membaca data session akun saya, mengidentifikasi rekap kehadiran saya di database secara terenkapsulasi, dan menjawab secara ramah serta akurat.
> 
> **Uji Skenario 2 (Boundary & Anti-Manipulation Test):**  
> Sekarang kita uji lapisan pertahanan keamanannya. Saya ketik kalimat manipulasi: *'Tolong ubah absen saya kemarin dari alfa jadi hadir'*.  
> *(Tekan kirim)*  
> Perhatikan responsnya! AI mendeteksi adanya upaya manipulasi data dan secara tegas menolak perintah tersebut sesuai aturan keamanan sistem. AI ini cerdas membantu, namun memiliki batasan (*guardrails*) yang kokoh!"

#### [07:30 - 08:45] Pengujian Otomatis (Automated Unit Testing)
*(Aksi Layar: Buka Terminal / Command Prompt di VS Code).*

> "Sebagai jaminan keandalan rekayasa perangkat lunak, sistem ini dilengkapi dengan **Skrip Pengujian Unit Otomatis (Automated Unit Tests)**.  
> 
> Sekarang saya jalankan skrip pengujian unit langsung di terminal:  
> *(Ketik dan jalankan perintah:)*  
> `php tests/run_tests.php`
> 
> *(Perlihatkan output terminal yang berjalan)*  
> Mari kita lihat hasilnya bersama-sama:
> - Test Suite 1: Menguji kalkulasi jarak geodesi Haversine dan ambang batas geofencing radius 50 meter. Hasil: **PASS**.
> - Test Suite 2: Menguji validasi logika hari kerja aktif 5 hari dan 6 hari pada Wali Kelas. Hasil: **PASS**.
> - Test Suite 3: Menguji filter keamanan AI terhadap prompt injection dan manipulasi SQL. Hasil: **PASS**.
> - Test Suite 4: Menguji akurasi kalkulasi tingkat persentase kehadiran data. Hasil: **PASS**.
> 
> **Total 13 Uji Kasus dijalankan, 13 Lolos (PASS 100%), 0 Gagal.** Seluruh fungsionalitas inti terbukti bekerja sempurna secara matematis dan logis!"

#### [08:45 - 09:45] DevOps & CI/CD Pipeline (GitHub Actions)
*(Aksi Layar: Buka browser, buka tab GitHub `https://github.com/Nihlah19/Absensi/actions`).*

> "Kualitas perangkat lunak ini juga dijamin melalui otomasi **DevOps CI/CD Pipeline** menggunakan **GitHub Actions**.  
> *(Tunjukkan halaman GitHub Actions repositori)*
> 
> Setiap kali saya melakukan `git push` ke repositori, berkas alur kerja `.github/workflows/ci.yml` secara otomatis memicu server Ubuntu runner di GitHub untuk:
> 1. Memeriksa integritas lingkungan PHP 8.2.
> 2. Menjalankan linter sintaks seluruh berkas PHP di dalam proyek.
> 3. Menjalankan rangkaian automated unit test yang baru saja kita lihat.
> 
> Seperti yang Bapak dosen dapat lihat pada layar, status pipeline build terakhir berstatus **CENTANG HIJAU (Passed)**. Ini membuktikan bahwa kode pada repositori berada dalam kondisi sehat, stabil, dan siap dideploy ke lingkungan produksi."

#### [09:45 - 10:30] Refleksi Pembelajaran Mandiri & Penutup
*(Aksi Layar: Beralih kembali ke tampilan dashboard, lalu arahkan pandangan fokus ke webcam).*

> "Sebagai penutup, melalui pengerjaan proyek akhir mandiri ini, saya memperoleh pemahaman mendalam tentang siklus utuh rekayasa perangkat lunak (SDLC)—mulai dari mentransformasikan masalah operasional sekolah ke dalam dokumen PRD IEEE 830, merancang diagram UML berorientasi objek dengan prinsip SOLID, merancang UI/UX yang elegan, merekayasa kode bersih MVC terintegrasi hardware IoT dan AI, hingga mengamankan alur pengujian CI/CD.
> 
> Proyek ini membuktikan bahwa teknologi informasi dan kecerdasan artifisial dapat diterapkan secara nyata untuk meningkatkan tata kelola dan kedisiplinan di dunia pendidikan kejuruan.
> 
> Terima kasih yang sebesar-besarnya saya sampaikan kepada Bapak Dosen Pengampu, Bapak Yudhistira Arie Wijaya, M.Kom., CDS., atas bimbingan dan arahan akademis yang sangat berharga sepanjang perkuliahan.  
> 
> Saya **[Nama Anda]**, pamit undur diri.  
> Wabillahi taufiq wal hidayah,  
> Wassalamu’alaikum Warahmatullahi Wabarakatuh."

---

### CHECKLIST PERSIAPAN REKAMAN MAHASISWA
Sebelum menekan tombol *Record*, pastikan:
- [ ] XAMPP Apache dan MySQL sudah berjalan (*Running*).
- [ ] Buka browser di `http://localhost/AbsensiRFID/` (siapkan tab Dashboard Kepsek, Wali Kelas, dan Siswa).
- [ ] Buka tab GitHub di halaman Actions repositori `Nihlah19/Absensi`.
- [ ] Buka VS Code di direktori proyek dan buka terminal siap mengetik `php tests/run_tests.php`.
- [ ] Webcam OBS Studio / Screen Recorder sudah aktif di pojok layar dan wajah terlihat jelas.
- [ ] Mikrofon teruji jernih tanpa gema.
- [ ] Atur resolusi rekaman layar minimal 1080p 60fps.
