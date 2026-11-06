# Panduan Deployment PHP di Vercel (Serverless & Docker FrankenPHP)

Dokumentasi ini mencakup dua metode deployment PHP di Vercel:
1. **Metode Otomatis Git Push (Serverless Runtime `vercel-php`)** — Langsung berjalan otomatis setiap push ke GitHub tanpa perlu install Docker di lokal.
2. **Metode Container Docker FrankenPHP** — Sesuai panduan resmi: 👉 **[Deploy PHP on Vercel with Docker](https://vercel.com/kb/guide/deploy-php-on-vercel-with-docker)**

Website **Absensi Siswa & PKL SMK NU Wargabinangun** telah dikonfigurasi secara lengkap agar langsung merender halaman web di browser tanpa memicu unduhan file (`application/x-httpd-php`).

---

## 1. File Konfigurasi yang Tersedia

1. **`api/index.php`**: Entrypoint Serverless Function Vercel yang otomatis mengeksekusi PHP dan merutekan request ke MVC.
2. **`vercel.json`**: Konfigurasi routing Vercel yang mengarahkan static assets (`assets`, `css`, `js`, `uploads`) dan request web ke PHP runtime.
3. **`Dockerfile`**: Image berbasis `dunglas/frankenphp:1-php8.2-alpine` dengan ekstensi PHP (`pdo_mysql`, `gd`, `intl`, `zip`, `opcache`, `curl`, `mbstring`).
4. **`Caddyfile`**: Web server Caddy yang secara dinamis mendengarkan port Vercel (`:{$PORT:80}`) dan merutekan traffic ke `web/public`.
5. **`web/app/config/config.php`**: Otomatis mendeteksi host Vercel (`BASEURL`), membaca Environment Variables, dan menangani reverse-proxy.
6. **`web/app/core/Database.php`**: Dilengkapi antarmuka panduan visual jika database cloud belum dihubungkan.

---

## 2. Persyaratan Database MySQL Cloud

Karena Vercel adalah platform stateless / serverless, database MySQL tidak boleh disimpan lokal di Vercel. Gunakan penyedia MySQL cloud gratis:
- **TiDB Cloud** (Gratis 5GB, sangat stabil, kompatibel MySQL): [https://tidbcloud.com](https://tidbcloud.com)
- **Aiven for MySQL** (Free tier): [https://aiven.io](https://aiven.io)
- **Railway MySQL**: [https://railway.app](https://railway.app)
- **Clever Cloud / PlanetScale**: [https://www.clever-cloud.com](https://www.clever-cloud.com)

**Langkah Import Database:**
1. Buat database baru di salah satu penyedia cloud di atas (misal nama database: `absensi_rfid`).
2. Import schema database dan data awal dari:
   - `database/absensi_rfid_clean_install.sql`.
   - Pastikan migration PKL terbaru (`database/migrations/pembimbing_pkl_migration.sql`) juga ter-execute.
3. Kredensial koneksi yang dibutuhkan:
   - **DB_HOST** (contoh: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`)
   - **DB_PORT** (contoh: `4000` atau `3306`)
   - **DB_USER** (contoh: `xxxxxx.root`)
   - **DB_PASS** (password database cloud)
   - **DB_NAME** (contoh: `absensi_rfid`)

---

## 3. Cara Deploy ke Vercel

### Metode 1: Otomatis via GitHub (Sangat Direkomendasikan)

1. Lakukan push commit ke repository GitHub:
   ```bash
   git push origin main
   ```
2. Vercel akan otomatis mendeteksi commit baru, membuild runtime PHP via `vercel.json` dan `api/index.php`.
3. Buka URL deployment: `https://absensmknu.vercel.app/`
4. Web langsung tampil di browser!

---

### Metode 2: Menggunakan Vercel CLI & Docker Container

Jika ingin menggunakan container FrankenPHP sesuai panduan resmi:
1. Pastikan Vercel CLI terpasang:
   ```bash
   npm install -g vercel
   ```
2. Login ke akun Vercel:
   ```bash
   vercel login
   ```
3. Set project framework ke `container`:
   ```bash
   vercel project update --framework container --yes
   ```
4. Deploy ke production:
   ```bash
   vercel deploy --prod
   ```

---

## 4. Pengaturan Environment Variables di Vercel Dashboard

Buka dashboard Vercel (**Project Settings ➔ Environment Variables**), lalu tambahkan:

| Key | Contoh Value | Keterangan |
| :--- | :--- | :--- |
| `DB_HOST` | `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` | Host MySQL Cloud |
| `DB_PORT` | `4000` atau `3306` | Port MySQL |
| `DB_USER` | `user_admin` | Username DB |
| `DB_PASS` | `PasswordRahasiaAnda` | Password DB |
| `DB_NAME` | `absensi_rfid` | Nama Database |
| `APP_ENV` | `production` | Mode environment |
| `APP_URL` | `https://absensmknu.vercel.app` | URL domain Vercel |
| `APP_TIMEZONE`| `Asia/Jakarta` | Zona waktu WIB |
| `SCHOOL_LAT` | `-6.5724553` | Titik Latitude gerbang sekolah |
| `SCHOOL_LNG` | `108.3977467` | Titik Longitude gerbang sekolah |
| `SCHOOL_RADIUS_METER` | `100` | Toleransi radius GPS sekolah |
