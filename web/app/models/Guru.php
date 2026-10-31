<?php
class Guru extends Model {
    /**
     * Cari guru berdasarkan user_id (session login).
     */
    public function getByUserId($user_id) {
        $this->db->query("SELECT * FROM guru WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    /**
     * Cari guru berdasarkan barcode_id atau nip.
     */
    public function getByBarcode($barcode) {
        $clean = trim((string)$barcode);
        $this->db->query("SELECT * FROM guru 
                          WHERE barcode_id = :barcode 
                             OR (nip IS NOT NULL AND nip != '' AND nip = :barcode_nip)
                          LIMIT 1");
        $this->db->bind(':barcode', $clean);
        $this->db->bind(':barcode_nip', $clean);
        return $this->db->single();
    }
}
