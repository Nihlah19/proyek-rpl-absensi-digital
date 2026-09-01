<?php
class Jadwal extends Model {
    public function getAll() {
        $this->db->query("SELECT * FROM jadwal_siswa ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT * FROM jadwal_siswa WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function update($data) {
        $this->db->query("UPDATE jadwal_siswa SET 
            jam_mulai_absensi = :jam_mulai_absensi,
            jam_datang = :jam_datang,
            batas_terlambat = :batas_terlambat,
            jam_pulang = :jam_pulang,
            status_aktif = :status_aktif
            WHERE id = :id
        ");
        
        $this->db->bind(':jam_mulai_absensi', $data['jam_mulai_absensi']);
        $this->db->bind(':jam_datang', $data['jam_datang']);
        $this->db->bind(':batas_terlambat', $data['batas_terlambat']);
        $this->db->bind(':jam_pulang', $data['jam_pulang']);
        $this->db->bind(':status_aktif', $data['status_aktif']);
        $this->db->bind(':id', $data['id']);

        $this->db->execute();
        return $this->db->rowCount();
    }
}
