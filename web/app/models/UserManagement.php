<?php
/**
 * UserManagement.php
 * Model untuk CRUD user (guru & siswa) oleh admin.
 * Mendukung penugasan Wali Kelas & Guru Pembimbing PKL.
 */
class UserManagement extends Model {

    // -- LIST --

    public function getAllGuru() {
        $this->db->query("SELECT u.id, u.username, u.name, u.role, u.created_at,
                          g.id as guru_id, g.nip, g.barcode_id, g.subject, g.is_pembimbing_pkl,
                          GROUP_CONCAT(DISTINCT kp.nama_kelas ORDER BY kp.nama_kelas ASC SEPARATOR ', ') as pembimbing_kelas_names
                          FROM users u
                          JOIN guru g ON g.user_id = u.id
                          LEFT JOIN pkl_pembimbing_kelas ppk ON ppk.guru_id = g.id
                          LEFT JOIN kelas kp ON ppk.kelas_id = kp.id
                          WHERE u.role IN ('guru', 'wali_kelas', 'walikelas', 'kepsek')
                          GROUP BY g.id
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

    public function getAllKelasPkl() {
        $this->db->query("SELECT * FROM kelas WHERE status = 'pkl' ORDER BY nama_kelas ASC");
        $res = $this->db->resultSet();
        // Fallback: jika belum ada kelas berstatus 'pkl', tampilkan semua kelas
        if (empty($res)) {
            return $this->getAllKelas();
        }
        return $res;
    }

    public function getKelasIdsByJurusan($jurusan) {
        // Siswa PKL berasal dari kelas 12
        $this->db->query("SELECT id FROM kelas WHERE UPPER(jurusan) = UPPER(:jurusan) AND (nama_kelas LIKE '12 %' OR tingkat = '12' OR status = 'pkl') ORDER BY nama_kelas ASC");
        $this->db->bind(':jurusan', $jurusan);
        $res = $this->db->resultSet();
        $ids = [];
        foreach ($res as $r) {
            $ids[] = (int)$r->id;
        }
        if (empty($ids)) {
            $this->db->query("SELECT id FROM kelas WHERE UPPER(jurusan) = UPPER(:jurusan) ORDER BY nama_kelas ASC");
            $this->db->bind(':jurusan', $jurusan);
            $res2 = $this->db->resultSet();
            foreach ($res2 as $r2) {
                $ids[] = (int)$r2->id;
            }
        }
        return $ids;
    }

    // -- GURU DETAIL --

    public function getGuruById($guru_id) {
        $this->db->query("SELECT u.id, u.username, u.name, g.id as guru_id, g.nip, g.barcode_id, g.subject, g.is_pembimbing_pkl, k.id as wali_kelas_id
                          FROM users u JOIN guru g ON g.user_id = u.id
                          LEFT JOIN kelas k ON k.wali_kelas_id = u.id
                          WHERE g.id = :guru_id");
        $this->db->bind(':guru_id', $guru_id);
        return $this->db->single();
    }

    public function getPembimbingKelasIds($guru_id) {
        $this->db->query("SELECT kelas_id FROM pkl_pembimbing_kelas WHERE guru_id = :gid");
        $this->db->bind(':gid', $guru_id);
        $rows = $this->db->resultSet();
        return array_map(function($r) { return (int)$r->kelas_id; }, $rows);
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

        $isPembimbingPkl = !empty($data['is_pembimbing_pkl']) ? 1 : 0;

        $this->db->query("INSERT INTO guru (user_id, nip, barcode_id, subject, is_pembimbing_pkl) VALUES (:user_id, :nip, :barcode_id, :subject, :is_pkl)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':nip', $data['nip']);
        $this->db->bind(':barcode_id', $barcode);
        $this->db->bind(':subject', isset($data['subject']) ? $data['subject'] : null);
        $this->db->bind(':is_pkl', $isPembimbingPkl);
        $this->db->execute();
        $guru_id = (int)$this->db->lastInsertId();

        if ($isPembimbingPkl && !empty($data['pembimbing_kelas_ids']) && is_array($data['pembimbing_kelas_ids'])) {
            foreach ($data['pembimbing_kelas_ids'] as $kid) {
                $kid = (int)$kid;
                if ($kid <= 0) continue;
                $this->db->query("INSERT INTO pkl_pembimbing_kelas (guru_id, user_id, kelas_id) VALUES (:gid, :uid, :kid)");
                $this->db->bind(':gid', $guru_id);
                $this->db->bind(':uid', $user_id);
                $this->db->bind(':kid', $kid);
                $this->db->execute();

                // Hubungkan kelompok yang sudah ada di kelas ini
                $this->db->query("UPDATE pkl_kelompok SET pembimbing_guru_id = :gid WHERE kelas_id = :kid AND (pembimbing_guru_id IS NULL OR pembimbing_guru_id = 0)");
                $this->db->bind(':gid', $guru_id);
                $this->db->bind(':kid', $kid);
                $this->db->execute();
            }
        }

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

        $isPembimbingPkl = !empty($data['is_pembimbing_pkl']) ? 1 : 0;

        $this->db->query("UPDATE guru SET nip = :nip, barcode_id = :barcode_id, subject = :subject, is_pembimbing_pkl = :is_pkl WHERE id = :guru_id");
        $this->db->bind(':nip', $data['nip']);
        $this->db->bind(':barcode_id', $barcode);
        $this->db->bind(':subject', isset($data['subject']) ? $data['subject'] : null);
        $this->db->bind(':is_pkl', $isPembimbingPkl);
        $this->db->bind(':guru_id', $guru_id);
        $this->db->execute();

        // Ambil user_id guru
        $this->db->query("SELECT user_id FROM guru WHERE id = :guru_id");
        $this->db->bind(':guru_id', $guru_id);
        $userRow = $this->db->single();
        $user_id = $userRow ? (int)$userRow->user_id : 0;

        // Update wali kelas assignment
        if (array_key_exists('wali_kelas_id', $data) && $user_id > 0) {
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

        // Update penugasan pembimbing PKL
        if (array_key_exists('pembimbing_kelas_ids', $data) && $user_id > 0) {
            // Hapus mapping kelas lama guru ini
            $this->db->query("DELETE FROM pkl_pembimbing_kelas WHERE guru_id = :gid");
            $this->db->bind(':gid', $guru_id);
            $this->db->execute();

            if ($isPembimbingPkl && !empty($data['pembimbing_kelas_ids']) && is_array($data['pembimbing_kelas_ids'])) {
                foreach ($data['pembimbing_kelas_ids'] as $kid) {
                    $kid = (int)$kid;
                    if ($kid <= 0) continue;
                    $this->db->query("INSERT INTO pkl_pembimbing_kelas (guru_id, user_id, kelas_id) VALUES (:gid, :uid, :kid)");
                    $this->db->bind(':gid', $guru_id);
                    $this->db->bind(':uid', $user_id);
                    $this->db->bind(':kid', $kid);
                    $this->db->execute();

                    // Hubungkan kelompok yang ada pada kelas ini dengan guru pembimbing
                    $this->db->query("UPDATE pkl_kelompok SET pembimbing_guru_id = :gid WHERE kelas_id = :kid AND (pembimbing_guru_id IS NULL OR pembimbing_guru_id = 0)");
                    $this->db->bind(':gid', $guru_id);
                    $this->db->bind(':kid', $kid);
                    $this->db->execute();
                }
            } else {
                // Jika tidak lagi pembimbing, lepas kelompok yang dibimbing
                $this->db->query("UPDATE pkl_kelompok SET pembimbing_guru_id = NULL WHERE pembimbing_guru_id = :gid");
                $this->db->bind(':gid', $guru_id);
                $this->db->execute();
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

        $this->db->query("INSERT INTO siswa (user_id, nis, kelas_id, no_hp_ortu) VALUES (:user_id, :nis, :kelas_id, :no_hp_ortu)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':nis', $data['nis']);
        $this->db->bind(':kelas_id', !empty($data['kelas_id']) ? $data['kelas_id'] : null);
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
            $new_uid = trim($data['rfid_uid']);
            if (empty($new_uid)) {
                $this->db->query("DELETE FROM rfid_cards WHERE student_id = :siswa_id");
                $this->db->bind(':siswa_id', $siswa_id);
                $this->db->execute();
            } else {
                $this->db->query("SELECT id FROM rfid_cards WHERE student_id = :siswa_id");
                $this->db->bind(':siswa_id', $siswa_id);
                $existing = $this->db->single();
                if ($existing) {
                    $this->db->query("UPDATE rfid_cards SET uid = :uid, status = 'active' WHERE student_id = :siswa_id");
                } else {
                    $this->db->query("INSERT INTO rfid_cards (uid, student_id, status) VALUES (:uid, :siswa_id, 'active')");
                }
                $this->db->bind(':uid', $new_uid);
                $this->db->bind(':siswa_id', $siswa_id);
                $this->db->execute();
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

    // -- VALIDATION CHECKS --

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
