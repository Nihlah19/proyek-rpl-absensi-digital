USE absensi_rfid;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Seed Users (password defaults to 'password')
INSERT INTO users (id, username, password, name, role) VALUES 
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
(2, 'kepsek', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bapak Kepsek', 'kepsek'),
(3, 'wali1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ibu Wali Kelas', 'walikelas'),
(4, 'guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pak Guru', 'guru'),
(5, 'siswa1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siswa Pertama', 'siswa'),
(6, 'siswa2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siswa Kedua', 'siswa');

-- 2. Seed Kelas
INSERT INTO kelas (id, nama_kelas, wali_kelas_id) VALUES 
(1, 'X RPL 1', 3);

-- 3. Seed Siswa
INSERT INTO siswa (user_id, kelas_id, nis, rfid_uid) VALUES 
(5, 1, '10001', 'A1B2C3D4'),
(6, 1, '10002', 'E5F6G7H8');

-- 4. Seed Guru
INSERT INTO guru (user_id, nip, barcode_id) VALUES 
(4, '198001012000011001', 'QR-GURU-001');

-- 5. Seed IoT Devices
-- 'mysecretapikey' hashed
INSERT INTO iot_devices (id, device_code, device_name, device_type, location, api_key_hash) VALUES 
(1, 'ESP32_RFID_001', 'RFID Gerbang Depan', 'RFID_SISWA', 'Gerbang Utama', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 6. Seed Jadwal Siswa
INSERT INTO jadwal_siswa (hari, jam_mulai_absensi, jam_datang, batas_terlambat, jam_pulang, status_aktif) VALUES 
('Senin', '05:30:00', '06:00:00', '07:15:00', '15:30:00', 1),
('Selasa', '05:30:00', '06:00:00', '07:15:00', '15:30:00', 1),
('Rabu', '05:30:00', '06:00:00', '07:15:00', '15:30:00', 1),
('Kamis', '05:30:00', '06:00:00', '07:15:00', '15:30:00', 1),
('Jumat', '05:30:00', '06:00:00', '07:15:00', '11:30:00', 1),
('Sabtu', '00:00:00', '00:00:00', '00:00:00', '00:00:00', 0),
('Minggu', '00:00:00', '00:00:00', '00:00:00', '00:00:00', 0);

-- 7. Seed Jadwal Guru (untuk Guru ID 1)
INSERT INTO jadwal_guru (guru_id, hari, jam_datang, batas_terlambat, jam_pulang, is_active) VALUES
(1, 'Senin', '06:45:00', '07:00:00', '15:00:00', 1),
(1, 'Selasa', '06:45:00', '07:00:00', '15:00:00', 1),
(1, 'Rabu', '06:45:00', '07:00:00', '15:00:00', 1),
(1, 'Kamis', '06:45:00', '07:00:00', '14:00:00', 1),
(1, 'Jumat', '06:45:00', '07:00:00', '11:30:00', 1);

SET FOREIGN_KEY_CHECKS = 1;
