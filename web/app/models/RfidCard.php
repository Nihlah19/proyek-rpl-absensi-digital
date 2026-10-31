<?php
class RfidCard extends Model {

    public function getAllCards() {
        $this->db->query("
            SELECT r.id, r.uid, r.student_id, r.status, r.created_at, r.updated_at, 
                   u.name as student_name, s.nis, k.class_code, k.nama_kelas
            FROM rfid_cards r
            JOIN siswa s ON s.id = r.student_id
            JOIN users u ON u.id = s.user_id
            LEFT JOIN kelas k ON k.id = s.kelas_id
            ORDER BY r.created_at DESC
        ");
        return $this->db->resultSet();
    }

    public function getCardById($id) {
        $this->db->query("
            SELECT r.id, r.uid, r.student_id, r.status, r.created_at, r.updated_at, 
                   u.name as student_name, s.nis, k.class_code, k.nama_kelas
            FROM rfid_cards r
            JOIN siswa s ON s.id = r.student_id
            JOIN users u ON u.id = s.user_id
            LEFT JOIN kelas k ON k.id = s.kelas_id
            WHERE r.id = :id
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getCardByUid($uid) {
        $this->db->query("SELECT * FROM rfid_cards WHERE uid = :uid");
        $this->db->bind(':uid', $uid);
        return $this->db->single();
    }

    public function getCardByStudentId($student_id) {
        $this->db->query("SELECT * FROM rfid_cards WHERE student_id = :student_id");
        $this->db->bind(':student_id', $student_id);
        return $this->db->single();
    }

    public function assignCard($uid, $student_id) {
        $this->db->query("INSERT INTO rfid_cards (uid, student_id, status) VALUES (:uid, :student_id, 'active')");
        $this->db->bind(':uid', $uid);
        $this->db->bind(':student_id', $student_id);
        return $this->db->execute();
    }

    public function updateStatus($id, $status) {
        $this->db->query("UPDATE rfid_cards SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function deleteCard($id) {
        $this->db->query("DELETE FROM rfid_cards WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
