# Panduan Deployment PHP di Vercel dengan Docker (FrankenPHP)

Panduan ini disusun sesuai dengan dokumentasi resmi Vercel:
👉 **[Deploy PHP on Vercel with Docker](https://vercel.com/kb/guide/deploy-php-on-vercel-with-docker)**

Website **Absensi Siswa & PKL SMK NU Wargabinangun** kini telah dikonfigurasi menggunakan container runtime **FrankenPHP** (Caddy + PHP 8.2) sehingga dapat di-deploy dan dijalankan di Vercel secara native tanpa serverless timeout limit biasa.

---

## 1. File Konfigurasi yang Telah Disediakan

1. **`Dockerfile`**: Image berbasis `dunglas/frankenphp:1-php8.2-alpine` dengan ekstensi PHP yang dibutuhkan (`pdo_mysql`, `gd`, `intl`, `zip`, `opcache`, `curl`, `mbstring`).
2. **`Caddyfile`**: Web server Caddy yang secara dinamis mendengarkan port Vercel (`:{$PORT:80}`) dan merutekan traffic langsung ke direktori entrypoint `web/public`.
3. **`vercel.json`**: Konfigurasi container runtime untuk Vercel.
4. **`.dockerignore`**: Pengecualian file lokal agar proses build image cepat dan ringan.
5. **`web/app/config/config.php`**: Telah dioptimalkan untuk membaca `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME`, dan `APP_URL` dari Environment Variables Vercel, serta otomatis mendeteksi URL root/reverse-proxy.

---

## 2. Persyaratan Sebelum Deploy

### A. Database MySQL Cloud
Karena Vercel adalah platform stateless, database MySQL tidak boleh disimpan di dalam container Vercel. Anda memerlukan database MySQL cloud (remote), misalnya salah satu dari:
- **TiDB Cloud** (Gratis 5GB, sangat stabil, mendukung MySQL): [https://tidbcloud.com](https://tidbcloud.com)
- **Aiven for MySQL** (Free tier): [https://aiven.io](https://aiven.io)
- **Railway MySQL**: [https://railway.app](https://railway.app)
- **Clever Cloud / PlanetScale**: [https://www.clever-cloud.com](https://www.clever-cloud.com)

**Langkah Import Database:**
1. Buat database baru di salah satu penyedia cloud di atas (misal nama database: `absensi_rfid`).
2. Import schema database dan data awal dari file:
   - `database/absensi_rfid_clean_install.sql` (atau file export database Anda saat ini).
   - Pastikan migration PKL terbaru (`database/migrations/pembimbing_pkl_migration.sql`) juga ter-execute.
3. Catat kredensial koneksi:
   - **Host** (contoh: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`)
   - **Port** (contoh: `4000` atau `3306`)
   - **User** (contoh: `xxxxxx.root`)
   - **Password**
   - **Database Name** (contoh: `absensi_rfid`)

---

## 3. Cara Deploy ke Vercel

### Metode 1: Menggunakan Vercel CLI (Direkomendasikan)

1. Pastikan Vercel CLI terpasang di komputer Anda:
   ```bash
   npm install -g vercel
   ```

2. Login ke akun Vercel Anda:
   ```bash
   vercel login
   ```

3. Update project ke framework `container`:
   ```bash
   vercel project update --framework container --yes
   ```

4. Jalankan deployment ke production:
   ```bash
   vercel deploy --prod
   ```

---

### Metode 2: Menggunakan Git Repository (GitHub / GitLab)

1. Push commit project ini ke repository GitHub Anda:
   ```bash
   git add .
   git commit -m "feat: revisi fitur pkl, notifikasi telegram, dan konfigurasi vercel docker"
   git push origin revisi_fitur_absensi_pkl
   ```

2. Buka dashboard [Vercel Dashboard](https://vercel.com/dashboard).
3. Klik **"Add New..."** ➔ **"Project"** ➔ Import repository GitHub Anda.
4. Pada bagian **Build and Output Settings**, Vercel akan otomatis mendeteksi konfigurasi `Dockerfile` dan `vercel.json`.
5. Buka bagian **Environment Variables** (lihat bagian 4 di bawah).
6. Klik **Deploy**.

---

## 4. Pengaturan Environment Variables di Vercel

Pada dashboard Vercel (**Project Settings ➔ Environment Variables**), tambahkan variabel berikut:

| Key | Contoh Value | Keterangan |
| :--- | :--- | :--- |
| `DB_HOST` | `gateway01.ap-southeast-1.prod.aws.tidbcloud.com` | Host database cloud |
| `DB_PORT` | `3306` (atau `4000`) | Port database cloud |
| `DB_USER` | `user_admin` | Username database cloud |
| `DB_PASS` | `PasswordRahasiaAnda` | Password database cloud |
| `DB_NAME` | `absensi_rfid` | Nama database |
| `APP_ENV` | `production` | Mode environment aplikasi |
| `APP_URL` | `https://absensi-smknu.vercel.app` | URL domain Vercel Anda |
| `APP_TIMEZONE`| `Asia/Jakarta` | Zona waktu Indonesia Barat |
| `SCHOOL_LAT` | `-6.5724553` | Titik Latitude gerbang sekolah |
| `SCHOOL_LNG` | `108.3977467` | Titik Longitude gerbang sekolah |
| `SCHOOL_RADIUS_METER` | `100` | Toleransi radius GPS sekolah |

> **Catatan:** Setelah menambahkan Environment Variables di Vercel, lakukan **Redeploy** agar variabel baru terbaca oleh container runtime.

---

## 5. Pengujian Lokal dengan Docker (Opsional)

Jika Anda memiliki Docker terpasang di komputer lokal, Anda dapat menguji container sebelum deploy ke Vercel:

```bash
# Build image Docker
docker build -t absensi-rfid-app .

# Jalankan container di port 8080
docker run -p 8080:80 \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=3306 \
  -e DB_USER=root \
  -e DB_PASS= \
  -e DB_NAME=absensi_rfid \
  -e APP_URL=http://localhost:8080 \
  absensi-rfid-app
```
Buka browser di `http://localhost:8080`.
