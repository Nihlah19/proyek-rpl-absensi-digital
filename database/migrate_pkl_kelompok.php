<?php
require_once __DIR__ . '/../web/app/init.php';
$db = new Database();

echo "Running PKL Kelompok Migration...\n";

// 1. Buat tabel pkl_kelompok
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS pkl_kelompok (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kelas_id INT NOT NULL,
            nama_kelompok VARCHAR(100) NOT NULL,
            nama_tempat VARCHAR(150) NOT NULL,
            alamat TEXT NULL,
            link_lokasi TEXT NULL,
            latitude DECIMAL(11, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            radius_meter INT DEFAULT 50,
            pembimbing_nama VARCHAR(100) NULL,
            pembimbing_kontak VARCHAR(50) NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $db->execute();
    echo "[OK] Table pkl_kelompok ready\n";
} catch (Exception $e) {
    echo "[ERROR] Table pkl_kelompok: " . $e->getMessage() . "\n";
}

// 2. Buat tabel pkl_kelompok_siswa
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS pkl_kelompok_siswa (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kelompok_id INT NOT NULL,
            siswa_id INT NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (kelompok_id) REFERENCES pkl_kelompok(id) ON DELETE CASCADE,
            FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $db->execute();
    echo "[OK] Table pkl_kelompok_siswa ready\n";
} catch (Exception $e) {
    echo "[ERROR] Table pkl_kelompok_siswa: " . $e->getMessage() . "\n";
}

// 3. Migrasi data lama dari siswa_pkl_lokasi jika belum ada kelompok
try {
    $db->query("SELECT count(*) as total FROM pkl_kelompok");
    $cnt = $db->single()->total ?? 0;
    if ($cnt == 0) {
        $db->query("
            SELECT spl.*, s.kelas_id 
            FROM siswa_pkl_lokasi spl
            JOIN siswa s ON spl.siswa_id = s.id
        ");
        $oldRows = $db->resultSet();
        foreach ($oldRows as $old) {
            $db->query("
                INSERT INTO pkl_kelompok (kelas_id, nama_kelompok, nama_tempat, alamat, link_lokasi, latitude, longitude, radius_meter)
                VALUES (:kid, :nkel, :ntemp, :alm, :link, :lat, :lng, :rad)
            ");
            $db->bind(':kid', $old->kelas_id);
            $db->bind(':nkel', 'Kelompok 1 - ' . $old->nama_tempat);
            $db->bind(':ntemp', $old->nama_tempat);
            $db->bind(':alm', $old->alamat);
            $db->bind(':link', $old->link_lokasi);
            $db->bind(':lat', $old->latitude);
            $db->bind(':lng', $old->longitude);
            $db->bind(':rad', max(50, (int)$old->radius_meter));
            $db->execute();
            $newKelompokId = $db->lastInsertId();

            $db->query("
                INSERT INTO pkl_kelompok_siswa (kelompok_id, siswa_id)
                VALUES (:kel_id, :sid)
                ON DUPLICATE KEY UPDATE kelompok_id = VALUES(kelompok_id)
            ");
            $db->bind(':kel_id', $newKelompokId);
            $db->bind(':sid', $old->siswa_id);
            $db->execute();
            echo "[OK] Migrated old PKL location for siswa_id {$old->siswa_id} to kelompok_id {$newKelompokId}\n";
        }
    }
} catch (Exception $e) {
    echo "[INFO] Migration old rows: " . $e->getMessage() . "\n";
}

echo "Migration PKL Kelompok completed.\n";
