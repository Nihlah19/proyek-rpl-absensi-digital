<?php
class JadwalGuru extends Model {
    
    public function getSemuaGuru() {
        $this->db->query("SELECT g.id, u.name as nama_guru, g.nip 
                          FROM guru g 
                          JOIN users u ON g.user_id = u.id
                          ORDER BY u.name ASC");
        return $this->db->resultSet();
    }

    public function getGuruById($guru_id) {
        $this->db->query("SELECT g.id, u.name as nama_guru, g.nip 
                          FROM guru g 
                          JOIN users u ON g.user_id = u.id
                          WHERE g.id = :id");
        $this->db->bind(':id', $guru_id);
        return $this->db->single();
    }

    public function getJadwalByGuru($guru_id) {
        // Pastikan setiap hari (Senin s/d Sabtu) selalu tersedia
        $this->ensureJadwalLengkap($guru_id);

        $this->db->query("SELECT * FROM jadwal_guru WHERE guru_id = :guru_id ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
        $this->db->bind(':guru_id', $guru_id);
        return $this->db->resultSet();
    }
    
    public function getJadwalById($id) {
        $this->db->query("SELECT * FROM jadwal_guru WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function ensureJadwalLengkap($guru_id) {
        $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        foreach ($hari_list as $hari) {
            $this->db->query("SELECT id FROM jadwal_guru WHERE guru_id = :guru_id AND hari = :hari");
            $this->db->bind(':guru_id', $guru_id);
            $this->db->bind(':hari', $hari);
            $row = $this->db->single();
            if (!$row) {
                $jam_pulang = ($hari === 'Sabtu') ? '13:00:00' : '15:00:00';
                $this->db->query("INSERT INTO jadwal_guru (guru_id, hari, jam_mulai_absensi, jam_datang, batas_terlambat, jam_pulang, is_active) 
                                  VALUES (:guru_id, :hari, '06:30:00', '07:00:00', '07:15:00', :jam_pulang, 0)");
                $this->db->bind(':guru_id', $guru_id);
                $this->db->bind(':hari', $hari);
                $this->db->bind(':jam_pulang', $jam_pulang);
                $this->db->execute();
            }
        }
    }

    public function generateDefaultJadwal($guru_id) {
        $this->ensureJadwalLengkap($guru_id);
    }

    public function update($data) {
        $jam_datang = $data['jam_datang'] ?? '07:00:00';
        $jam_mulai = $data['jam_mulai_absensi'] ?? null;
        if (empty($jam_mulai)) {
            $ts = strtotime($jam_datang);
            $jam_mulai = date('H:i:s', $ts ? $ts - 1800 : strtotime('06:30:00'));
        }

        $this->db->query("UPDATE jadwal_guru SET 
            jam_mulai_absensi = :jam_mulai_absensi,
            jam_datang = :jam_datang,
            batas_terlambat = :batas_terlambat,
            jam_pulang = :jam_pulang,
            is_active = :is_active
            WHERE id = :id
        ");
        
        $this->db->bind(':jam_mulai_absensi', $jam_mulai);
        $this->db->bind(':jam_datang', $data['jam_datang']);
        $this->db->bind(':batas_terlambat', $data['batas_terlambat']);
        $this->db->bind(':jam_pulang', $data['jam_pulang']);
        $this->db->bind(':is_active', isset($data['is_active']) ? 1 : 0);
        $this->db->bind(':id', $data['id']);

        $this->db->execute();
        return $this->db->rowCount();
    }
}
