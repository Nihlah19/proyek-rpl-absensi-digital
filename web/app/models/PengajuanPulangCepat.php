<?php
class PengajuanPulangCepat extends Model {

    public function getActiveBySiswaAndDate($siswa_id, $tanggal) {
        $this->db->query("SELECT * FROM pengajuan_pulang_cepat 
                          WHERE siswa_id = :siswa_id AND tanggal = :tanggal 
                          AND status IN ('DRAFT', 'PENDING', 'APPROVED')");
        $this->db->bind(':siswa_id', $siswa_id);
        $this->db->bind(':tanggal', $tanggal);
        return $this->db->single();
    }

    public function createDraft($data) {
        // Cek kembali untuk anti-duplicate
        $existing = $this->getActiveBySiswaAndDate($data['siswa_id'], $data['tanggal']);
        if ($existing) return false;

        $query = "INSERT INTO pengajuan_pulang_cepat 
                  (siswa_id, wali_kelas_id, tanggal, jam_pengajuan, jam_masuk, jam_pulang_jadwal, status) 
                  VALUES 
                  (:siswa_id, :wali_kelas_id, :tanggal, :jam_pengajuan, :jam_masuk, :jam_pulang_jadwal, 'DRAFT')";
        
        $this->db->query($query);
        $this->db->bind(':siswa_id', $data['siswa_id']);
        $this->db->bind(':wali_kelas_id', $data['wali_kelas_id']);
        $this->db->bind(':tanggal', $data['tanggal']);
        $this->db->bind(':jam_pengajuan', $data['jam_pengajuan']);
        $this->db->bind(':jam_masuk', $data['jam_masuk']);
        $this->db->bind(':jam_pulang_jadwal', $data['jam_pulang_jadwal']);
        
        return $this->db->execute();
    }

    public function updateToPending($id, $alasan) {
        $this->db->query("UPDATE pengajuan_pulang_cepat SET status = 'PENDING', alasan = :alasan WHERE id = :id AND status = 'DRAFT'");
        $this->db->bind(':id', $id);
        $this->db->bind(':alasan', $alasan);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getDaftarPengajuan($waliKelasId = null) {
        $query = "SELECT p.*, u.name as nama_siswa, s.nis, k.nama_kelas
                  FROM pengajuan_pulang_cepat p
                  JOIN siswa s ON p.siswa_id = s.id
                  JOIN users u ON s.user_id = u.id
                  JOIN kelas k ON s.kelas_id = k.id";
        
        if ($waliKelasId !== null) {
            $query .= " WHERE p.wali_kelas_id = :wali_id";
        }
        $query .= " ORDER BY p.created_at DESC";

        $this->db->query($query);
        if ($waliKelasId !== null) {
            $this->db->bind(':wali_id', $waliKelasId);
        }
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT p.*, u.name as nama_siswa, s.nis, k.nama_kelas, w.name as nama_wali, w.telegram_chat_id, k.telegram_bot_token, u.telegram_chat_id as siswa_chat_id
                          FROM pengajuan_pulang_cepat p
                          JOIN siswa s ON p.siswa_id = s.id
                          JOIN users u ON s.user_id = u.id
                          JOIN kelas k ON s.kelas_id = k.id
                          LEFT JOIN users w ON p.wali_kelas_id = w.id
                          WHERE p.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function approve($id, $approved_by) {
        // Atomic approve using direct DB query
        $this->db->query("UPDATE pengajuan_pulang_cepat 
                          SET status = 'APPROVED', approved_by = :approved_by, approved_at = NOW() 
                          WHERE id = :id AND status = 'PENDING'");
        $this->db->bind(':id', $id);
        $this->db->bind(':approved_by', $approved_by);
        $this->db->execute();
        return $this->db->rowCount(); // > 0 if successful
    }

    public function reject($id, $rejected_by, $reason) {
        // Atomic reject
        $this->db->query("UPDATE pengajuan_pulang_cepat 
                          SET status = 'REJECTED', rejected_by = :rejected_by, rejected_at = NOW(), rejected_reason = :reason 
                          WHERE id = :id AND status = 'PENDING'");
        $this->db->bind(':id', $id);
        $this->db->bind(':rejected_by', $rejected_by);
        $this->db->bind(':reason', $reason);
        $this->db->execute();
        return $this->db->rowCount(); // > 0 if successful
    }

    public function getWaliKelasId($siswa_id) {
        $this->db->query("SELECT k.wali_kelas_id FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.id = :siswa_id");
        $this->db->bind(':siswa_id', $siswa_id);
        $res = $this->db->single();
        return $res ? $res->wali_kelas_id : null;
    }
}
