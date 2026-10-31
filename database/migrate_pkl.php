<?php
require_once 'd:/xampp/htdocs/AbsensiRFID/web/app/init.php';
$db = new Database();

echo "Running PKL Migration...\n";

// 1. Tambah 'pkl' ke enum status di absensi_siswa jika belum ada
try {
    $db->query("ALTER TABLE absensi_siswa MODIFY COLUMN status ENUM('hadir','terlambat','izin','sakit','alfa','pulang_cepat','pkl') DEFAULT 'alfa'");
    $db->execute();
    echo "[OK] absensi_siswa.status updated to include 'pkl'\n";
} catch (Exception $e) {
    echo "[INFO] Status enum check: " . $e->getMessage() . "\n";
}

// 2. Tambah kolom foto & koordinat di absensi_siswa jika belum ada
$colsToAdd = [
    'foto_masuk'       => "VARCHAR(255) NULL AFTER keterangan",
    'foto_pulang'      => "VARCHAR(255) NULL AFTER foto_masuk",
    'latitude_masuk'   => "DECIMAL(11, 8) NULL AFTER foto_pulang",
    'longitude_masuk'  => "DECIMAL(11, 8) NULL AFTER latitude_masuk",
    'latitude_pulang'  => "DECIMAL(11, 8) NULL AFTER longitude_masuk",
    'longitude_pulang' => "DECIMAL(11, 8) NULL AFTER latitude_pulang",
    'jarak_meter'      => "INT NULL AFTER longitude_pulang",
];

foreach ($colsToAdd as $col => $def) {
    try {
        $db->query("SHOW COLUMNS FROM absensi_siswa LIKE '{$col}'");
        if (!$db->single()) {
            $db->query("ALTER TABLE absensi_siswa ADD COLUMN {$col} {$def}");
            $db->execute();
            echo "[OK] Added column {$col} to absensi_siswa\n";
        } else {
            echo "[SKIP] Column {$col} already exists\n";
        }
    } catch (Exception $e) {
        echo "[ERROR] Column {$col}: " . $e->getMessage() . "\n";
    }
}

// 3. Buat tabel siswa_pkl_lokasi jika belum ada
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS siswa_pkl_lokasi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL UNIQUE,
            nama_tempat VARCHAR(150) NOT NULL,
            alamat TEXT NULL,
            link_lokasi TEXT NULL,
            latitude DECIMAL(11, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            radius_meter INT DEFAULT 10,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $db->execute();
    echo "[OK] Table siswa_pkl_lokasi ready\n";
} catch (Exception $e) {
    echo "[ERROR] Table siswa_pkl_lokasi: " . $e->getMessage() . "\n";
}

// 4. Pastikan folder uploads/absensi_pkl ada
$pklUploadDir = 'd:/xampp/htdocs/AbsensiRFID/web/public/uploads/absensi_pkl';
if (!is_dir($pklUploadDir)) {
    mkdir($pklUploadDir, 0777, true);
    echo "[OK] Created directory uploads/absensi_pkl\n";
} else {
    echo "[SKIP] Directory uploads/absensi_pkl already exists\n";
}

echo "PKL Migration finished successfully.\n";
