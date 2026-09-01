<?php
class JadwalGuru extends Model {
    
    public function getSemuaGuru() {
        $this->db->query("SELECT g.id, u.name as nama_guru, g.nip 
                          FROM guru g 
                          JOIN users u ON g.user_id = u.id");
        return $this->db->resultSet();
    }

    public function getJadwalByGuru($guru_id) {
        $this->db->query("SELECT * FROM jadwal_guru WHERE guru_id = :guru_id ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
        $this->db->bind(':guru_id', $guru_id);
        $result = $this->db->resultSet();
        
        // If empty, generate default
        if (empty($result)) {
            $this->generateDefaultJadwal($guru_id);
            $this->db->query("SELECT * FROM jadwal_guru WHERE guru_id = :guru_id ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
            $this->db->bind(':guru_id', $guru_id);
            $result = $this->db->resultSet();
        }
        
        return $result;
    }
    
    public function getJadwalById($id) {
        $this->db->query("SELECT * FROM jadwal_guru WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function generateDefaultJadwal($guru_id) {
        $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        foreach ($hari_list as $hari) {
            $this->db->query("INSERT INTO jadwal_guru (guru_id, hari, jam_datang, batas_terlambat, jam_pulang, is_active) VALUES (:guru_id, :hari, '07:00:00', '07:15:00', '15:00:00', 0)");
            $this->db->bind(':guru_id', $guru_id);
            $this->db->bind(':hari', $hari);
            $this->db->execute();
        }
    }

    public function update($data) {
        $this->db->query("UPDATE jadwal_guru SET 
            jam_datang = :jam_datang,
            batas_terlambat = :batas_terlambat,
            jam_pulang = :jam_pulang,
            is_active = :is_active
            WHERE id = :id
        ");
        
        $this->db->bind(':jam_datang', $data['jam_datang']);
        $this->db->bind(':batas_terlambat', $data['batas_terlambat']);
        $this->db->bind(':jam_pulang', $data['jam_pulang']);
        $this->db->bind(':is_active', isset($data['is_active']) ? 1 : 0);
        $this->db->bind(':id', $data['id']);

        $this->db->execute();
        return $this->db->rowCount();
    }
}
