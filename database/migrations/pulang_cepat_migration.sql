CREATE TABLE IF NOT EXISTS pengajuan_pulang_cepat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    wali_kelas_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_pengajuan TIME NOT NULL,
    jam_masuk TIME NOT NULL,
    jam_pulang_jadwal TIME NOT NULL,
    alasan TEXT NULL,
    status ENUM('DRAFT', 'PENDING', 'APPROVED', 'REJECTED') DEFAULT 'DRAFT',
    
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    
    rejected_by INT NULL,
    rejected_at TIMESTAMP NULL,
    rejected_reason TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (wali_kelas_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_siswa_tanggal_status (siswa_id, tanggal, status)
);

ALTER TABLE absensi_siswa MODIFY COLUMN status ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alfa', 'pulang_cepat') DEFAULT 'alfa';
