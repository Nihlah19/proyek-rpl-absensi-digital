<?php
class Absensi extends Model {

    /**
     * Helper: Konversi nama hari Inggris ke Indonesia.
     */
    private function getHariIndo() {
        $hariMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $hariMap[date('l')];
    }

    /**
     * Jadwal siswa hari ini (global, dari tabel jadwal_siswa).
     */
    public function getJadwalHariIni() {
        $this->db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1");
        $this->db->bind(':hari', $this->getHariIndo());
        return $this->db->single();
    }

    /**
     * Jadwal guru hari ini (spesifik per guru, dari tabel jadwal_guru).
     * Mengembalikan null jika guru tidak punya jadwal aktif hari ini
     * → artinya guru TIDAK wajib absen, TIDAK dianggap alfa.
     */
    public function getJadwalGuruHariIni($guru_id) {
        $this->db->query("SELECT * FROM jadwal_guru WHERE guru_id = :guru_id AND hari = :hari AND is_active = 1");
        $this->db->bind(':guru_id', $guru_id);
        $this->db->bind(':hari', $this->getHariIndo());
        return $this->db->single();
    }

    // STUDENT
    public function getSiswaByRFID($rfid) {
        $this->db->query("
            SELECT s.*, u.name, k.nama_kelas
            FROM siswa s
            JOIN users u ON u.id = s.user_id
            LEFT JOIN kelas k ON k.id = s.kelas_id
            WHERE s.rfid_uid = :rfid
        ");
        $this->db->bind(':rfid', $rfid);
        return $this->db->single();
    }

    public function cekAbsensiSiswa($siswa_id, $tanggal) {
        $this->db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
        $this->db->bind(':siswa_id', $siswa_id);
        $this->db->bind(':tanggal', $tanggal);
        return $this->db->single();
    }

    public function insertAbsensiSiswa($data) {
        $this->db->query("INSERT INTO absensi_siswa (siswa_id, tanggal, jam_datang, jam_pulang, status, source_device_id) VALUES (:siswa_id, :tanggal, :jam_datang, :jam_pulang, :status, :source_device_id)");
        $this->db->bind(':siswa_id', $data['siswa_id']);
        $this->db->bind(':tanggal', $data['tanggal']);
        $this->db->bind(':jam_datang', $data['jam_datang']);
        $this->db->bind(':jam_pulang', $data['jam_pulang']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':source_device_id', $data['source_device_id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // TEACHER
    public function getGuruByBarcode($barcode) {
        $this->db->query("SELECT * FROM guru WHERE barcode_id = :barcode");
        $this->db->bind(':barcode', $barcode);
        return $this->db->single();
    }

    public function cekAbsensiGuru($guru_id, $tanggal) {
        $this->db->query("SELECT * FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = :tanggal");
        $this->db->bind(':guru_id', $guru_id);
        $this->db->bind(':tanggal', $tanggal);
        return $this->db->single();
    }

    public function insertAbsensiGuru($data) {
        $this->db->query("INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, status, source_device_id) VALUES (:guru_id, :tanggal, :jam_datang, :status, :source_device_id)");
        $this->db->bind(':guru_id', $data['guru_id']);
        $this->db->bind(':tanggal', $data['tanggal']);
        $this->db->bind(':jam_datang', $data['jam_datang']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':source_device_id', $data['source_device_id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateAbsensiPulangGuru($id, $jam_pulang) {
        $this->db->query("UPDATE absensi_guru SET jam_pulang = :jam_pulang WHERE id = :id");
        $this->db->bind(':jam_pulang', $jam_pulang);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
