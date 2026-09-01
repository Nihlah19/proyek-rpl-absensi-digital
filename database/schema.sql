CREATE DATABASE IF NOT EXISTS absensi_rfid;
USE absensi_rfid;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabel users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'kepsek', 'walikelas', 'guru', 'siswa') NOT NULL,
    telegram_chat_id VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel kelas (Wali kelas berelasi dengan tabel users)
DROP TABLE IF EXISTS kelas;
CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(50) NOT NULL,
    wali_kelas_id INT NULL,
    telegram_chat_id VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wali_kelas_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 3. Tabel siswa
DROP TABLE IF EXISTS siswa;
CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kelas_id INT NULL,
    nis VARCHAR(20) NOT NULL UNIQUE,
    rfid_uid VARCHAR(50) NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL
);

-- 4. Tabel guru
DROP TABLE IF EXISTS guru;
CREATE TABLE guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nip VARCHAR(20) NOT NULL UNIQUE,
    barcode_id VARCHAR(50) NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Tabel iot_devices
DROP TABLE IF EXISTS iot_devices;
CREATE TABLE iot_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_code VARCHAR(50) NOT NULL UNIQUE,
    device_name VARCHAR(100) NOT NULL,
    device_type ENUM('RFID_SISWA') NOT NULL,
    location VARCHAR(100),
    api_key_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_seen TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Tabel absensi_siswa
DROP TABLE IF EXISTS absensi_siswa;
CREATE TABLE absensi_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_datang TIME NULL,
    jam_pulang TIME NULL,
    status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alfa') DEFAULT 'alfa',
    keterangan TEXT NULL,
    source_device_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (source_device_id) REFERENCES iot_devices(id) ON DELETE SET NULL,
    UNIQUE KEY (siswa_id, tanggal) -- Satu siswa hanya boleh mempunyai satu absensi per tanggal
);

-- 7. Tabel absensi_guru
DROP TABLE IF EXISTS absensi_guru;
CREATE TABLE absensi_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_datang TIME NULL,
    jam_pulang TIME NULL,
    status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alfa') DEFAULT 'alfa',
    keterangan TEXT NULL,
    source_device_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
    FOREIGN KEY (source_device_id) REFERENCES iot_devices(id) ON DELETE SET NULL,
    UNIQUE KEY (guru_id, tanggal) -- Satu guru hanya boleh mempunyai satu absensi per tanggal
);

-- 8. Tabel jadwal_siswa
DROP TABLE IF EXISTS jadwal_siswa;
CREATE TABLE jadwal_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL UNIQUE,
    jam_mulai_absensi TIME NOT NULL,
    jam_datang TIME NOT NULL,
    batas_terlambat TIME NOT NULL,
    jam_pulang TIME NOT NULL,
    status_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8b. Tabel jadwal_guru
DROP TABLE IF EXISTS jadwal_guru;
CREATE TABLE jadwal_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_datang TIME NOT NULL,
    batas_terlambat TIME NOT NULL,
    jam_pulang TIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
    UNIQUE KEY (guru_id, hari)
);

-- 9. Tabel pengajuan_izin
DROP TABLE IF EXISTS pengajuan_izin;
CREATE TABLE pengajuan_izin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- bisa siswa atau guru
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    jenis ENUM('sakit', 'izin') NOT NULL,
    alasan TEXT NOT NULL,
    keterangan TEXT NULL,
    file_bukti VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL, -- FK ke users (kepsek/walikelas)
    catatan_approval TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 10. Tabel catatan_siswa
DROP TABLE IF EXISTS catatan_siswa;
CREATE TABLE catatan_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    wali_kelas_id INT NOT NULL,
    catatan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (wali_kelas_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 11. Tabel catatan_guru
DROP TABLE IF EXISTS catatan_guru;
CREATE TABLE catatan_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    kepsek_id INT NOT NULL,
    catatan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
    FOREIGN KEY (kepsek_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 12. Tabel iot_logs
DROP TABLE IF EXISTS iot_logs;
CREATE TABLE iot_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    payload TEXT NOT NULL,
    response_code INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (device_id) REFERENCES iot_devices(id) ON DELETE CASCADE
);

-- 13. Tabel chat_history
DROP TABLE IF EXISTS chat_history;
CREATE TABLE chat_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pesan_user TEXT NOT NULL,
    balasan_bot TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;
