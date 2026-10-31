<?php
class LogError extends Model {
    public function insertLog($data) {
        $this->db->query("INSERT INTO log_error_absensi 
            (request_id, sumber, barcode_input, jenis_error, pesan_error, user_agent, ip_address) 
            VALUES (:request_id, :sumber, :barcode_input, :jenis_error, :pesan_error, :user_agent, :ip_address)");
        
        $this->db->bind(':request_id', $data['request_id']);
        $this->db->bind(':sumber', $data['sumber']);
        $this->db->bind(':barcode_input', $data['barcode_input']);
        $this->db->bind(':jenis_error', $data['jenis_error']);
        $this->db->bind(':pesan_error', $data['pesan_error']);
        $this->db->bind(':user_agent', $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null);
        $this->db->bind(':ip_address', $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getLogs($filters = []) {
        $query = "SELECT * FROM log_error_absensi WHERE 1=1";
        
        if (!empty($filters['tanggal'])) {
            $query .= " AND DATE(waktu) = :tanggal";
        }
        if (!empty($filters['jenis_error'])) {
            $query .= " AND jenis_error = :jenis_error";
        }
        
        $query .= " ORDER BY waktu DESC LIMIT 100";
        
        $this->db->query($query);
        
        if (!empty($filters['tanggal'])) {
            $this->db->bind(':tanggal', $filters['tanggal']);
        }
        if (!empty($filters['jenis_error'])) {
            $this->db->bind(':jenis_error', $filters['jenis_error']);
        }
        
        return $this->db->resultSet();
    }
}

