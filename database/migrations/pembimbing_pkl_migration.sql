-- Migration: Penugasan Guru Pembimbing PKL & Integrasi Lokasi
-- SMK NU Wargabinangun

-- 1. Tambah kolom is_pembimbing_pkl di tabel guru jika belum ada
SET @col_exists := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'guru' 
      AND COLUMN_NAME = 'is_pembimbing_pkl'
);

SET @sql := IF(@col_exists = 0, 
    'ALTER TABLE guru ADD COLUMN is_pembimbing_pkl TINYINT(1) NOT NULL DEFAULT 0 AFTER subject', 
    'SELECT "Column is_pembimbing_pkl already exists in guru"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Buat tabel pkl_pembimbing_kelas jika belum ada
CREATE TABLE IF NOT EXISTS pkl_pembimbing_kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    user_id INT NOT NULL,
    kelas_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pembimbing_kelas (guru_id, kelas_id),
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tambah kolom pembimbing_guru_id di pkl_kelompok jika belum ada
SET @col_kel_exists := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'pkl_kelompok' 
      AND COLUMN_NAME = 'pembimbing_guru_id'
);

SET @sql_kel := IF(@col_kel_exists = 0, 
    'ALTER TABLE pkl_kelompok ADD COLUMN pembimbing_guru_id INT NULL AFTER radius_meter', 
    'SELECT "Column pembimbing_guru_id already exists in pkl_kelompok"'
);
PREPARE stmt FROM @sql_kel;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Tambah kolom hari_kerja di pkl_kelompok jika belum ada (PRD v4)
SET @col_hari_exists := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'pkl_kelompok' 
      AND COLUMN_NAME = 'hari_kerja'
);

SET @sql_hari := IF(@col_hari_exists = 0, 
    'ALTER TABLE pkl_kelompok ADD COLUMN hari_kerja VARCHAR(100) NOT NULL DEFAULT \'["Senin","Selasa","Rabu","Kamis","Jumat"]\' AFTER pembimbing_kontak', 
    'SELECT "Column hari_kerja already exists in pkl_kelompok"'
);
PREPARE stmt FROM @sql_hari;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Tambah kolom is_jam_bebas di pkl_kelompok jika belum ada (PRD v4)
SET @col_jam_exists := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'pkl_kelompok' 
      AND COLUMN_NAME = 'is_jam_bebas'
);

SET @sql_jam := IF(@col_jam_exists = 0, 
    'ALTER TABLE pkl_kelompok ADD COLUMN is_jam_bebas TINYINT(1) NOT NULL DEFAULT 1 AFTER hari_kerja', 
    'SELECT "Column is_jam_bebas already exists in pkl_kelompok"'
);
PREPARE stmt FROM @sql_jam;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
