# PANDUAN IMPORT DESAIN UI/UX KE FIGMA
## Sistem Presensi Terpadu IoT RFID & Mobile PKL — SMK NU Wargabinangun

Folder ini berisi seluruh rancangan desain antarmuka (**UI/UX**) aplikasi presensi yang telah dirancang dengan standar **Figma Artboard (Desktop 1440×900 & Mobile 390×844)**, siap diimpor ke aplikasi **Figma**.

---

## 1. Daftar File Desain di Folder `presentasi/figma/`

| No | Nama Berkas | Ukuran Kanvas | Deskripsi Halaman / Fitur |
| :---: | :--- | :---: | :--- |
| **00** | **`master_all_dashboards_figma_board.svg`** | **4600 × 3000** | **Mega Board:** Berisi seluruh halaman dan Design System dalam 1 kanvas besar. |
| 01 | `01_landing_page.svg` | 1440 × 900 | Landing Page modern, Hero banner, fitur grid, dan live preview. |
| 02 | `02_dashboard_admin.svg` | 1440 × 900 | Dashboard Superadmin, status gate RFID IoT, dan tabel realtime. |
| 03 | `03_fitur_presensi_pkl.svg` | 390 × 844 | Mobile Screen presensi PKL, WebRTC live camera, & Haversine radar. |
| 04 | `04_fitur_chatbot_ai.svg` | 1440 × 900 | Modal Chatbot Asisten AI cerdas (Groq LLM) dengan grounding database. |
| 05 | `05_dashboard_guru.svg` | 1440 × 900 | Portal Guru, scan barcode masuk, jadwal mengajar, supervisi kepsek. |
| 06 | `06_dashboard_siswa.svg` | 1440 × 900 | Portal Siswa, kartu pelajar RFID digital UID, timeline presensi. |
| 07 | `07_fitur_rekap_absensi.svg` | 1440 × 900 | Rekapitulasi presensi, filter tanggal/kelas/status, ekspor Excel. |
| 08 | `08_dashboard_wali_kelas.svg` | 1440 × 900 | Dashboard Wali Kelas, verifikasi izin sakit, broadcast Telegram kelas. |
| 09 | `09_dashboard_kepsek.svg` | 1440 × 900 | Executive Dashboard Kepala Sekolah, statistik kehadiran & komparasi jurusan. |

---

## 2. Cara Impor Langsung ke Figma (Hanya Butuh 3 Langkah)

Figma secara bawaan (*native*) memiliki parser vector engine terbaik yang dapat mengonversi file format **SVG Artboard** langsung menjadi **Figma Native Layers, Frame, Vector, dan Editable Text**:

### Langkah 1: Buka Figma
- Buka aplikasi **Figma Desktop** atau akses [https://figma.com](https://figma.com) di peramban (Chrome/Edge/Firefox).
- Buat file desain baru (**New design file**).

### Langkah 2: Drag & Drop File
- Buka folder `presentasi/figma/` di File Explorer laptop Anda.
- **Tarik (Drag) file:**
  - Pilih **`master_all_dashboards_figma_board.svg`** untuk memasukkan seluruh halaman sekaligus dalam 1 kali tarik!
  - ATAU tarik berkas individual (misal `01_landing_page.svg`, `03_fitur_presensi_pkl.svg`) sesuai kebutuhan.
- Lepaskan (*Drop*) kursor ke atas lembar kerja (canvas) kosong di Figma.

### Langkah 3: Semua Elemen Siap Diedit
- Figma akan seketika menghasilkan **Frame**, **Groups**, **Text box**, dan **Vectors** yang sepenuhnya dapat Anda ubah (ganti warna, ganti font, ubah layout, atau jadikan komponen Auto-Layout).

---

## 3. Cara Menyimpan sebagai File `.fig` Resmi dari Figma

Format file dengan ekstensi **`.fig`** adalah format biner tertutup (*proprietary Kiwi binary protocol*) milik Figma. Format ini dikompilasi secara internal oleh engine Figma agar tidak rusak (*corrupt*).

Setelah Anda men-drag file SVG ke Figma di atas, untuk menyimpannya sebagai file **`.fig`**:
1. Di Figma, klik menu **Figma Icon** (kiri atas) $\rightarrow$ **File** $\rightarrow$ **Save local copy...**
2. Pilih folder penyimpanan Anda dan beri nama `Absensi_RFID_SMK_NU.fig`.
3. File `.fig` resmi yang 100% valid dan tidak akan error kini tersimpan di laptop Anda!

---

## 4. Standar Desain (Design System Tokens)

- **Primary Brand:** Indigo (`#4f46e5` / `#6366f1`)
- **Dark Mode Background:** Slate 900 (`#0f172a`), Slate 800 (`#1e293b`)
- **Light Mode Surface:** Slate 50 (`#f8fafc`), White (`#ffffff`)
- **Status Badges:**
  - **Hadir (RFID/QR):** Emerald (`#10b981` / `#dcfce7`)
  - **PKL Hadir:** Cyan / Sky (`#0284c7` / `#e0f2fe`)
  - **Terlambat:** Amber (`#f59e0b` / `#fef3c7`)
  - **Izin / Sakit:** Purple (`#7e22ce` / `#f3e8ff`)
  - **Alfa:** Rose / Red (`#ef4444` / `#fee2e2`)
- **Tipografi:** Inter / Plus Jakarta Sans / Arial (Ukuran Heading 32px, 24px, 18px; Body 14px; Caption 11-12px).
