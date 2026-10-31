<?php
/**
 * UserManagement.php
 * Model untuk CRUD user (guru & siswa) oleh admin.
 */
class UserManagement extends Model {

    // -- LIST --

    public function getAllGuru() {
        $this->db->query("SELECT u.id, u.username, u.name, u.role, u.created_at,
                          g.id as guru_id, g.nip, g.barcode_id, g.subject
                          FROM users u
                          JOIN guru g ON g.user_id = u.id
                          WHERE u.role IN ('guru', 'wali_kelas', 'walikelas', 'kepsek')
                          ORDER BY u.name ASC");
        return $this->db->resultSet();
    }

    public function getAllSiswa() {
        $this->db->query("SELECT u.id, u.username, u.name, u.role, u.created_at,
                          s.id as siswa_id, s.nis, s.no_hp_ortu, r.uid as rfid_uid, s.kelas_id, k.nama_kelas
                          FROM users u
                          JOIN siswa s ON s.user_id = u.id
                          LEFT JOIN kelas k ON k.id = s.kelas_id
                          LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active'
                          WHERE u.role = 'siswa'
                          ORDER BY k.nama_kelas, u.name ASC");
        return $this->db->resultSet();
    }

    public function getAllKelas() {
        $this->db->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
        return $this->db->resultSet();
    }

    // -- GURU DETAIL --

    public function getGuruById($guru_id) {
        $this->db->query("SELECT u.id, u.username, u.name, g.id as guru_id, g.nip, g.barcode_id, g.subject, k.id as wali_kelas_id
                          FROM users u JOIN guru g ON g.user_id = u.id
                          LEFT JOIN kelas k ON k.wali_kelas_id = u.id
                          WHERE g.id = :guru_id");
        $this->db->bind(':guru_id', $guru_id);
        return $this->db->single();
    }

    // -- SISWA DETAIL --

    public function getSiswaById($siswa_id) {
        $this->db->query("SELECT u.id, u.username, u.name, s.id as siswa_id, s.nis, s.no_hp_ortu, r.uid as rfid_uid, s.kelas_id, k.nama_kelas
                          FROM users u JOIN siswa s ON s.user_id = u.id
                          LEFT JOIN kelas k ON k.id = s.kelas_id
                          LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active'
                          WHERE s.id = :siswa_id");
        $this->db->bind(':siswa_id', $siswa_id);
        return $this->db->single();
    }

    // -- CREATE GURU --

    public function createGuru($data) {
        $this->db->query("INSERT INTO users (username, password, name, role) VALUES (:username, :password, :name, 'guru')");
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->bind(':name', $data['name']);
        $this->db->execute();
        $user_id = $this->db->lastInsertId();

        $barcode = !empty($data['barcode_id']) ? trim($data['barcode_id']) : null;
        if (empty($barcode)) {
            $this->db->query("SELECT MAX(id) as max_id FROM guru");
            $row = $this->db->single();
            $next_id = ($row->max_id ?? 0) + 1;
            $barcode = sprintf('QR-GURU-%03d', $next_id);
            while ($this->isBarcodeExist($barcode)) {
                $next_id++;
                $barcode = sprintf('QR-GURU-%03d', $next_id);
            }
        }

        $this->db->query("INSERT INTO guru (user_id, nip, barcode_id, subject) VALUES (:user_id, :nip, :barcode_id, :subject)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':nip', $data['nip']);
        $this->db->bind(':barcode_id', $barcode);
        $this->db->bind(':subject', isset($data['subject']) ? $data['subject'] : null);
        $this->db->execute();
        return $user_id;
    }

    // -- UPDATE GURU --

    public function updateGuru($guru_id, $data) {
        if (!empty($data['password'])) {
            $this->db->query("UPDATE users u JOIN guru g ON g.user_id = u.id SET u.name = :name, u.username = :username, u.password = :password WHERE g.id = :guru_id");
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
        } else {
            $this->db->query("UPDATE users u JOIN guru g ON g.user_id = u.id SET u.name = :name, u.username = :username WHERE g.id = :guru_id");
        }
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':guru_id', $guru_id);
        $this->db->execute();

        $barcode = !empty($data['barcode_id']) ? trim($data['barcode_id']) : null;
        if (empty($barcode)) {
            $this->db->query("SELECT barcode_id FROM guru WHERE id = :guru_id");
            $this->db->bind(':guru_id', $guru_id);
            $row = $this->db->single();
            $barcode = $row ? $row->barcode_id : null;
            
            if (empty($barcode)) {
                $barcode = sprintf('QR-GURU-%03d', $guru_id);
                while ($this->isBarcodeExist($barcode, $guru_id)) {
                    $barcode = 'QR-GURU-' . $guru_id . '-' . rand(100, 999);
                }
            }
        }

        $this->db->query("UPDATE guru SET nip = :nip, barcode_id = :barcode_id, subject = :subject WHERE id = :guru_id");
        $this->db->bind(':nip', $data['nip']);
        $this->db->bind(':barcode_id', $barcode);
        $this->db->bind(':subject', isset($data['subject']) ? $data['subject'] : null);
        $this->db->bind(':guru_id', $guru_id);
        $this->db->execute();

        // Update wali kelas assignment
        if (array_key_exists('wali_kelas_id', $data)) {
            $this->db->query("SELECT user_id FROM guru WHERE id = :guru_id");
            $this->db->bind(':guru_id', $guru_id);
            $userRow = $this->db->single();
            if ($userRow) {
                $user_id = $userRow->user_id;
                
                // Set role
                $role = !empty($data['wali_kelas_id']) ? 'wali_kelas' : 'guru';
                $this->db->query("UPDATE users SET role = :role WHERE id = :user_id");
                $this->db->bind(':role', $role);
                $this->db->bind(':user_id', $user_id);
                $this->db->execute();

                // Clear previous class assignment for this user
                $this->db->query("UPDATE kelas SET wali_kelas_id = NULL WHERE wali_kelas_id = :user_id");
                $this->db->bind(':user_id', $user_id);
                $this->db->execute();

                if (!empty($data['wali_kelas_id'])) {
                    // Check if target class already had a wali kelas, if so demote them to guru
                    $this->db->query("SELECT wali_kelas_id FROM kelas WHERE id = :kelas_id");
                    $this->db->bind(':kelas_id', $data['wali_kelas_id']);
                    $oldClass = $this->db->single();
                    if ($oldClass && $oldClass->wali_kelas_id && $oldClass->wali_kelas_id != $user_id) {
                        $this->db->query("UPDATE users SET role = 'guru' WHERE id = :old_user_id");
                        $this->db->bind(':old_user_id', $oldClass->wali_kelas_id);
                        $this->db->execute();
                    }

                    // Set as new wali kelas
                    $this->db->query("UPDATE kelas SET wali_kelas_id = :user_id WHERE id = :kelas_id");
                    $this->db->bind(':user_id', $user_id);
                    $this->db->bind(':kelas_id', $data['wali_kelas_id']);
                    $this->db->execute();
                }
            }
        }
    }

    // -- DELETE GURU --

    public function deleteGuru($guru_id) {
        $this->db->query("DELETE u FROM users u JOIN guru g ON g.user_id = u.id WHERE g.id = :guru_id");
        $this->db->bind(':guru_id', $guru_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // -- CREATE SISWA --

    public function createSiswa($data) {
        $this->db->query("INSERT INTO users (username, password, name, role) VALUES (:username, :password, :name, 'siswa')");
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->bind(':name', $data['name']);
        $this->db->execute();
        $user_id = $this->db->lastInsertId();

        $this->db->query("INSERT INTO siswa (user_id, kelas_id, nis, no_hp_ortu) VALUES (:user_id, :kelas_id, :nis, :no_hp_ortu)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':kelas_id', !empty($data['kelas_id']) ? $data['kelas_id'] : null);
        $this->db->bind(':nis', $data['nis']);
        $this->db->bind(':no_hp_ortu', !empty($data['no_hp_ortu']) ? $data['no_hp_ortu'] : null);
        $this->db->execute();
        $siswa_id = $this->db->lastInsertId();

        if (!empty($data['rfid_uid'])) {
            $this->db->query("INSERT INTO rfid_cards (uid, student_id, status) VALUES (:uid, :student_id, 'active')");
            $this->db->bind(':uid', $data['rfid_uid']);
            $this->db->bind(':student_id', $siswa_id);
            $this->db->execute();
        }
        return $user_id;
    }

    // -- UPDATE SISWA --

    public function updateSiswa($siswa_id, $data) {
        if (!empty($data['password'])) {
            $this->db->query("UPDATE users u JOIN siswa s ON s.user_id = u.id SET u.name = :name, u.username = :username, u.password = :password WHERE s.id = :siswa_id");
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
        } else {
            $this->db->query("UPDATE users u JOIN siswa s ON s.user_id = u.id SET u.name = :name, u.username = :username WHERE s.id = :siswa_id");
        }
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':siswa_id', $siswa_id);
        $this->db->execute();

        $this->db->query("UPDATE siswa SET nis = :nis, kelas_id = :kelas_id, no_hp_ortu = :no_hp_ortu WHERE id = :siswa_id");
        $this->db->bind(':nis', $data['nis']);
        $this->db->bind(':kelas_id', !empty($data['kelas_id']) ? $data['kelas_id'] : null);
        $this->db->bind(':no_hp_ortu', !empty($data['no_hp_ortu']) ? $data['no_hp_ortu'] : null);
        $this->db->bind(':siswa_id', $siswa_id);
        $this->db->execute();

        if (array_key_exists('rfid_uid', $data)) {
            if (empty($data['rfid_uid'])) {
                $this->db->query("DELETE FROM rfid_cards WHERE student_id = :student_id");
                $this->db->bind(':student_id', $siswa_id);
                $this->db->execute();
            } else {
                $this->db->query("SELECT id FROM rfid_cards WHERE student_id = :student_id");
                $this->db->bind(':student_id', $siswa_id);
                $existing = $this->db->single();
                if ($existing) {
                    $this->db->query("UPDATE rfid_cards SET uid = :uid, status = 'active' WHERE student_id = :student_id");
                    $this->db->bind(':uid', $data['rfid_uid']);
                    $this->db->bind(':student_id', $siswa_id);
                    $this->db->execute();
                } else {
                    $this->db->query("INSERT INTO rfid_cards (uid, student_id, status) VALUES (:uid, :student_id, 'active')");
                    $this->db->bind(':uid', $data['rfid_uid']);
                    $this->db->bind(':student_id', $siswa_id);
                    $this->db->execute();
                }
            }
        }
    }

    // -- DELETE SISWA --

    public function deleteSiswa($siswa_id) {
        $this->db->query("DELETE u FROM users u JOIN siswa s ON s.user_id = u.id WHERE s.id = :siswa_id");
        $this->db->bind(':siswa_id', $siswa_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // -- BULK ACTIONS SISWA --

    public function moveSiswaByKelas($kelas_asal_id, $kelas_tujuan_id) {
        $this->db->query("UPDATE siswa SET kelas_id = :kelas_tujuan_id WHERE kelas_id = :kelas_asal_id");
        $this->db->bind(':kelas_tujuan_id', $kelas_tujuan_id);
        $this->db->bind(':kelas_asal_id', $kelas_asal_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteSiswaByKelas($kelas_id) {
        $this->db->query("DELETE u FROM users u JOIN siswa s ON s.user_id = u.id WHERE s.kelas_id = :kelas_id");
        $this->db->bind(':kelas_id', $kelas_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // -- VALIDASI DUPLIKAT --

    public function isUsernameExist($username, $excludeId = null) {
        if ($excludeId) {
            $this->db->query("SELECT id FROM users WHERE username = :username AND id != :id");
            $this->db->bind(':id', $excludeId);
        } else {
            $this->db->query("SELECT id FROM users WHERE username = :username");
        }
        $this->db->bind(':username', $username);
        return $this->db->single() !== false;
    }

    public function isNipExist($nip, $excludeId = null) {
        if ($excludeId) {
            $this->db->query("SELECT id FROM guru WHERE nip = :nip AND id != :id");
            $this->db->bind(':id', $excludeId);
        } else {
            $this->db->query("SELECT id FROM guru WHERE nip = :nip");
        }
        $this->db->bind(':nip', $nip);
        return $this->db->single() !== false;
    }

    public function isNisExist($nis, $excludeId = null) {
        if ($excludeId) {
            $this->db->query("SELECT id FROM siswa WHERE nis = :nis AND id != :id");
            $this->db->bind(':id', $excludeId);
        } else {
            $this->db->query("SELECT id FROM siswa WHERE nis = :nis");
        }
        $this->db->bind(':nis', $nis);
        return $this->db->single() !== false;
    }

    public function isRfidExist($uid, $excludeStudentId = null) {
        if ($excludeStudentId) {
            $this->db->query("SELECT id FROM rfid_cards WHERE uid = :uid AND student_id != :student_id");
            $this->db->bind(':student_id', $excludeStudentId);
        } else {
            $this->db->query("SELECT id FROM rfid_cards WHERE uid = :uid");
        }
        $this->db->bind(':uid', $uid);
        return $this->db->single() !== false;
    }

    public function isBarcodeExist($barcode, $excludeGuruId = null) {
        $clean = trim((string)$barcode);
        if ($excludeGuruId) {
            $this->db->query("SELECT id FROM guru WHERE barcode_id = :barcode AND id != :id");
            $this->db->bind(':id', $excludeGuruId);
        } else {
            $this->db->query("SELECT id FROM guru WHERE barcode_id = :barcode");
        }
        $this->db->bind(':barcode', $clean);
        return $this->db->single() !== false;
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
}
