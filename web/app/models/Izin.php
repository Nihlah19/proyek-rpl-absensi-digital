<?php
class Izin extends Model {

    public function ajukan($data) {
        $query = "INSERT INTO pengajuan_izin 
                  (user_id, tanggal_mulai, tanggal_selesai, jenis, alasan, keterangan, file_bukti, status) 
                  VALUES 
                  (:user_id, :tanggal_mulai, :tanggal_selesai, :jenis, :alasan, :keterangan, :file_bukti, 'pending')";
        $this->db->query($query);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':tanggal_mulai', $data['tanggal_mulai']);
        $this->db->bind(':tanggal_selesai', $data['tanggal_selesai']);
        $this->db->bind(':jenis', $data['jenis']);
        $this->db->bind(':alasan', $data['alasan']);
        $this->db->bind(':keterangan', $data['keterangan']);
        $this->db->bind(':file_bukti', $data['file_bukti']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getRiwayatUser($userId) {
        $this->db->query("SELECT * FROM pengajuan_izin WHERE user_id = :user_id ORDER BY created_at DESC");
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function getById($id) {
        $this->db->query("SELECT pi.*, u.name, u.role, k.nama_kelas
                          FROM pengajuan_izin pi 
                          JOIN users u ON pi.user_id = u.id 
                          LEFT JOIN siswa s ON u.id = s.user_id
                          LEFT JOIN kelas k ON s.kelas_id = k.id
                          WHERE pi.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getDaftarPersetujuanWali($waliId) {
        // Wali kelas hanya melihat pengajuan dari siswa di kelasnya
        $query = "SELECT pi.*, u.name as nama_siswa, k.nama_kelas 
                  FROM pengajuan_izin pi
                  JOIN users u ON pi.user_id = u.id
                  JOIN siswa s ON u.id = s.user_id
                  JOIN kelas k ON s.kelas_id = k.id
                  WHERE k.wali_kelas_id = :wali_id
                  ORDER BY pi.created_at DESC";
        $this->db->query($query);
        $this->db->bind(':wali_id', $waliId);
        return $this->db->resultSet();
    }

    public function getDaftarPersetujuanKepsek() {
        // Kepsek melihat pengajuan dari semua guru
        $query = "SELECT pi.*, u.name as nama_guru
                  FROM pengajuan_izin pi
                  JOIN users u ON pi.user_id = u.id
                  WHERE u.role = 'guru'
                  ORDER BY pi.created_at DESC";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    public function updateStatus($id, $status, $approvedBy, $catatan) {
        $query = "UPDATE pengajuan_izin 
                  SET status = :status, approved_by = :approved_by, catatan_approval = :catatan 
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind(':status', $status);
        $this->db->bind(':approved_by', $approvedBy);
        $this->db->bind(':catatan', $catatan);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function tambahAbsensiDariIzin($pengajuan) {
        // Insert for each date between tanggal_mulai and tanggal_selesai
        $start = new DateTime($pengajuan->tanggal_mulai);
        $end = new DateTime($pengajuan->tanggal_selesai);
        $end->modify('+1 day'); // include end date
        
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($start, $interval, $end);
        
        $isSiswa = ($pengajuan->role == 'siswa');
        
        $table = $isSiswa ? 'absensi_siswa' : 'absensi_guru';
        $entityIdCol = $isSiswa ? 'siswa_id' : 'guru_id';
        
        // Get entity ID
        if ($isSiswa) {
            $this->db->query("SELECT id FROM siswa WHERE user_id = :user_id");
        } else {
            $this->db->query("SELECT id FROM guru WHERE user_id = :user_id");
        }
        $this->db->bind('user_id', $pengajuan->user_id);
        $entity = $this->db->single();
        if (!$entity) return false;
        
        $entityId = $entity->id;

        foreach($daterange as $date){
            $tgl = $date->format("Y-m-d");
            
            // Cek apakah sudah ada absensi
            $this->db->query("SELECT id FROM {$table} WHERE {$entityIdCol} = :entity_id AND tanggal = :tanggal");
            $this->db->bind('entity_id', $entityId);
            $this->db->bind('tanggal', $tgl);
            $exists = $this->db->single();
            
            if ($exists) {
                // Update
                $this->db->query("UPDATE {$table} SET status = :status, keterangan = :keterangan WHERE id = :id");
                $this->db->bind('status', $pengajuan->jenis); // 'sakit' or 'izin'
                $this->db->bind('keterangan', $pengajuan->alasan);
                $this->db->bind('id', $exists->id);
                $this->db->execute();
            } else {
                // Insert
                $this->db->query("INSERT INTO {$table} ({$entityIdCol}, tanggal, status, keterangan) VALUES (:entity_id, :tanggal, :status, :keterangan)");
                $this->db->bind('entity_id', $entityId);
                $this->db->bind('tanggal', $tgl);
                $this->db->bind('status', $pengajuan->jenis);
                $this->db->bind('keterangan', $pengajuan->alasan);
                $this->db->execute();
            }
        }
        return true;
    }
}
