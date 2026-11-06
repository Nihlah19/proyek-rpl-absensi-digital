<?php
require_once __DIR__ . '/../web/app/init.php';
$db = new Database();

echo "Running Migration: Guru Pembimbing PKL...\n";

// 1. Tambah is_pembimbing_pkl di tabel guru
try {
    $db->query("SHOW COLUMNS FROM guru LIKE 'is_pembimbing_pkl'");
    if (!$db->single()) {
        $db->query("ALTER TABLE guru ADD COLUMN is_pembimbing_pkl TINYINT(1) NOT NULL DEFAULT 0 AFTER subject");
        $db->execute();
        echo "[OK] Added column is_pembimbing_pkl to guru\n";
    } else {
        echo "[SKIP] Column is_pembimbing_pkl already exists in guru\n";
    }
} catch (Exception $e) {
    echo "[ERROR] Column is_pembimbing_pkl: " . $e->getMessage() . "\n";
}

// 2. Buat tabel pkl_pembimbing_kelas
try {
    $db->query("
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
    ");
    $db->execute();
    echo "[OK] Table pkl_pembimbing_kelas ready\n";
} catch (Exception $e) {
    echo "[ERROR] Table pkl_pembimbing_kelas: " . $e->getMessage() . "\n";
}

// 3. Tambah pembimbing_guru_id di pkl_kelompok
try {
    $db->query("SHOW COLUMNS FROM pkl_kelompok LIKE 'pembimbing_guru_id'");
    if (!$db->single()) {
        $db->query("ALTER TABLE pkl_kelompok ADD COLUMN pembimbing_guru_id INT NULL AFTER radius_meter");
        $db->execute();
        echo "[OK] Added column pembimbing_guru_id to pkl_kelompok\n";
    } else {
        echo "[SKIP] Column pembimbing_guru_id already exists in pkl_kelompok\n";
    }
} catch (Exception $e) {
    echo "[ERROR] Column pembimbing_guru_id: " . $e->getMessage() . "\n";
}

// 4. Tambah hari_kerja di pkl_kelompok (PRD v4)
try {
    $db->query("SHOW COLUMNS FROM pkl_kelompok LIKE 'hari_kerja'");
    if (!$db->single()) {
        $db->query("ALTER TABLE pkl_kelompok ADD COLUMN hari_kerja VARCHAR(100) NOT NULL DEFAULT '[\"Senin\",\"Selasa\",\"Rabu\",\"Kamis\",\"Jumat\"]' AFTER pembimbing_kontak");
        $db->execute();
        echo "[OK] Added column hari_kerja to pkl_kelompok\n";
    } else {
        echo "[SKIP] Column hari_kerja already exists in pkl_kelompok\n";
    }
} catch (Exception $e) {
    echo "[ERROR] Column hari_kerja: " . $e->getMessage() . "\n";
}

// 5. Tambah is_jam_bebas di pkl_kelompok (PRD v4)
try {
    $db->query("SHOW COLUMNS FROM pkl_kelompok LIKE 'is_jam_bebas'");
    if (!$db->single()) {
        $db->query("ALTER TABLE pkl_kelompok ADD COLUMN is_jam_bebas TINYINT(1) NOT NULL DEFAULT 1 AFTER hari_kerja");
        $db->execute();
        echo "[OK] Added column is_jam_bebas to pkl_kelompok\n";
    } else {
        echo "[SKIP] Column is_jam_bebas already exists in pkl_kelompok\n";
    }
} catch (Exception $e) {
    echo "[ERROR] Column is_jam_bebas: " . $e->getMessage() . "\n";
}

// 6. Inisialisasi AppSetting Telegram PKL jika belum ada
AppSetting::ensureTable();
if (AppSetting::get('telegram_pkl_enabled') === null) {
    AppSetting::set('telegram_pkl_enabled', '1', 'Status aktif notifikasi Telegram khusus absensi PKL (1 = aktif, 0 = nonaktif)');
    echo "[OK] AppSetting telegram_pkl_enabled initialized\n";
}
if (AppSetting::get('telegram_pkl_chat_id') === null) {
    AppSetting::set('telegram_pkl_chat_id', '', 'Chat ID / Grup ID Telegram khusus notifikasi absensi siswa PKL');
    echo "[OK] AppSetting telegram_pkl_chat_id initialized\n";
}
if (AppSetting::get('telegram_pkl_bot_token') === null) {
    AppSetting::set('telegram_pkl_bot_token', '', 'Token Bot Telegram khusus PKL (opsional, fallback ke bot kelas/sekolah)');
    echo "[OK] AppSetting telegram_pkl_bot_token initialized\n";
}

echo "Migration finished successfully.\n";
