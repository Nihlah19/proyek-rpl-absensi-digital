-- ============================================================
-- Migration: Notifikasi WhatsApp Orang Tua (Fonnte API)
-- Tanggal: 2026-10-23
-- ============================================================

-- 1. Tambah kolom no_hp_ortu di tabel siswa
ALTER TABLE siswa ADD COLUMN IF NOT EXISTS no_hp_ortu VARCHAR(20) NULL AFTER rfid_uid;

-- 2. Tabel konfigurasi device Fonnte
CREATE TABLE IF NOT EXISTS wa_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL DEFAULT 'Device Utama',
    token VARCHAR(255) NOT NULL,
    device_number VARCHAR(20) NOT NULL,
    max_per_minute INT NOT NULL DEFAULT 5,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_used_at TIMESTAMP NULL,
    total_sent_today INT NOT NULL DEFAULT 0,
    total_sent_today_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel antrian pesan WhatsApp
CREATE TABLE IF NOT EXISTS wa_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    config_id INT NULL,
    siswa_id INT NULL,
    target_phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    notif_type ENUM('masuk','pulang','pulang_cepat','alfa','terlambat','info') NOT NULL DEFAULT 'info',
    status ENUM('pending','sending','sent','failed') NOT NULL DEFAULT 'pending',
    attempt INT NOT NULL DEFAULT 0,
    max_attempt INT NOT NULL DEFAULT 3,
    response TEXT NULL,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (config_id) REFERENCES wa_config(id) ON DELETE SET NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE SET NULL,
    KEY idx_wa_queue_status (status),
    KEY idx_wa_queue_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
