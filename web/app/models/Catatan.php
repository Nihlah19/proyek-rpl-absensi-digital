<?php
class Catatan extends Model
{
    // ──────────────────────────────────────────────────────────────
    // CREATE — menulis catatan
    //   Wali kelas → siswa : tabel catatan_siswa
    //   Kepsek    → guru   : tabel catatan_guru
    // ──────────────────────────────────────────────────────────────

    public function create($data)
    {
        $isSiswa = ($data['target_role'] === 'siswa');
        $title   = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $finalContent = (!empty($title)) ? ("[" . $title . "]\n" . $content) : $content;

        if ($isSiswa) {
            $this->db->query(
                "INSERT INTO catatan_siswa
                     (siswa_id, wali_kelas_id, catatan, created_at)
                 VALUES (:target_id, :author_id, :content, CURRENT_TIMESTAMP)"
            );
            $this->db->bind(':target_id', $data['target_id']);      // siswa_id
            $this->db->bind(':author_id', $data['author_id']);      // wali_kelas_id
            $this->db->bind(':content', $finalContent);
            $this->db->execute();
            return $this->db->rowCount();
        }

        $this->db->query(
            "INSERT INTO catatan_guru
                 (guru_id, kepsek_id, catatan, created_at)
             VALUES (:target_id, :author_id, :content, CURRENT_TIMESTAMP)"
        );
        $this->db->bind(':target_id', $data['target_id']);          // guru_id
        $this->db->bind(':author_id', $data['author_id']);          // kepsek_id
        $this->db->bind(':content', $finalContent);
        $this->db->execute();
        return $this->db->rowCount();
    }

    private function parseCatatanList($list)
    {
        if (!is_array($list)) return [];
        foreach ($list as $item) {
            $text = $item->catatan ?? '';
            if (preg_match('/^\[(.*?)\]\r?\n?(.*)$/s', $text, $matches)) {
                $item->title = trim($matches[1]);
                $item->content = trim($matches[2]);
            } else {
                $item->title = 'Catatan';
                $item->content = $text;
            }
        }
        return $list;
    }

    // ──────────────────────────────────────────────────────────────
    // READ — ambil catatan yang diterima user (siswa / guru / kepsek)
    // ──────────────────────────────────────────────────────────────

    public function getByUser($userId, $role)
    {
        $userId = (int)$userId;

        if ($role === 'siswa') {
            $this->db->query("SELECT id FROM siswa WHERE user_id = :uid");
            $this->db->bind(':uid', $userId);
            $siswa = $this->db->single();
            $sid   = $siswa ? (int)$siswa->id : 0;

            $this->db->query(
                "SELECT cs.id, cs.siswa_id, cs.wali_kelas_id, cs.catatan, cs.created_at,
                        COALESCE(uwk.name, ugwk.name, 'Wali Kelas') AS author_name,
                        COALESCE(uwk.username, ugwk.username, '') AS author_username
                 FROM catatan_siswa cs
                 LEFT JOIN users uwk ON uwk.id = cs.wali_kelas_id
                 LEFT JOIN guru gwk ON gwk.id = cs.wali_kelas_id
                 LEFT JOIN users ugwk ON ugwk.id = gwk.user_id
                 WHERE cs.siswa_id = :sid OR cs.siswa_id = :uid
                 ORDER BY cs.created_at DESC"
            );
            $this->db->bind(':sid', $sid);
            $this->db->bind(':uid', $userId);
            return $this->parseCatatanList($this->db->resultSet());
        }

        if ($role === 'guru' || $role === 'wali_kelas' || $role === 'walikelas') {
            $this->db->query("SELECT id FROM guru WHERE user_id = :uid");
            $this->db->bind(':uid', $userId);
            $guru = $this->db->single();
            $gid  = $guru ? (int)$guru->id : 0;

            $this->db->query(
                "SELECT cg.id, cg.guru_id, cg.kepsek_id, cg.catatan, cg.created_at,
                        COALESCE(u.name, 'Kepala Sekolah') AS author_name,
                        COALESCE(u.username, '') AS author_username
                 FROM catatan_guru cg
                 LEFT JOIN users u ON u.id = cg.kepsek_id
                 WHERE cg.guru_id = :gid OR cg.guru_id = :uid
                 ORDER BY cg.created_at DESC"
            );
            $this->db->bind(':gid', $gid);
            $this->db->bind(':uid', $userId);
            return $this->parseCatatanList($this->db->resultSet());
        }

        if ($role === 'kepsek') {
            return $this->getSentByKepsek($userId);
        }

        return [];
    }

    /**
     * Ambil daftar catatan yang dikirim oleh Wali Kelas ke siswa kelasnya.
     */
    public function getSentByWaliKelas($wali_kelas_id)
    {
        $wali_kelas_id = (int)$wali_kelas_id;
        $this->db->query(
            "SELECT cs.id, cs.siswa_id, cs.wali_kelas_id, cs.catatan, cs.created_at,
                    COALESCE(u.name, 'Siswa') AS target_name,
                    COALESCE(u.username, '') AS target_username,
                    s.nis,
                    'Wali Kelas' AS author_name
             FROM catatan_siswa cs
             LEFT JOIN siswa s ON s.id = cs.siswa_id
             LEFT JOIN users u ON u.id = s.user_id
             WHERE cs.wali_kelas_id = :uid
                OR cs.wali_kelas_id IN (SELECT id FROM guru WHERE user_id = :uid)
             ORDER BY cs.created_at DESC"
        );
        $this->db->bind(':uid', $wali_kelas_id);
        return $this->parseCatatanList($this->db->resultSet());
    }

    // ──────────────────────────────────────────────────────────────
    // GET BY ID (untuk detail jika diperlukan)
    // ──────────────────────────────────────────────────────────────

    public function getById($id)
    {
        $id = (int)$id;
        $this->db->query(
            "SELECT cs.*, COALESCE(u.name, 'Wali Kelas') AS author_name, u.username AS author_username, 'siswa' AS source_table
             FROM catatan_siswa cs
             LEFT JOIN users u ON u.id = cs.wali_kelas_id
             WHERE cs.id = :id"
        );
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        if ($row) return $row;

        $this->db->query(
            "SELECT cg.*, COALESCE(u.name, 'Kepala Sekolah') AS author_name, u.username AS author_username, 'guru' AS source_table
             FROM catatan_guru cg
             LEFT JOIN users u ON u.id = cg.kepsek_id
             WHERE cg.id = :id"
        );
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Ambil catatan spesifik beserta verifikasi kepemilikan penulis (untuk edit/hapus)
     */
    public function getByIdForAuthor($id, $targetRole, $authorId)
    {
        $id       = (int)$id;
        $authorId = (int)$authorId;
        if ($id <= 0 || $authorId <= 0) {
            return null;
        }

        if ($targetRole === 'siswa') {
            $this->db->query(
                "SELECT cs.id, cs.siswa_id AS target_id, cs.wali_kelas_id AS author_id, cs.catatan, cs.created_at,
                        COALESCE(u.name, 'Siswa') AS target_name, 'siswa' AS target_role
                 FROM catatan_siswa cs
                 LEFT JOIN siswa s ON s.id = cs.siswa_id
                 LEFT JOIN users u ON u.id = s.user_id
                 WHERE cs.id = :id
                   AND (cs.wali_kelas_id = :author_id OR cs.wali_kelas_id IN (SELECT id FROM guru WHERE user_id = :author_id))
                 LIMIT 1"
            );
        } elseif ($targetRole === 'guru') {
            $this->db->query(
                "SELECT cg.id, cg.guru_id AS target_id, cg.kepsek_id AS author_id, cg.catatan, cg.created_at,
                        COALESCE(u.name, 'Guru') AS target_name, 'guru' AS target_role
                 FROM catatan_guru cg
                 LEFT JOIN guru g ON g.id = cg.guru_id
                 LEFT JOIN users u ON u.id = g.user_id
                 WHERE cg.id = :id AND cg.kepsek_id = :author_id
                 LIMIT 1"
            );
        } else {
            return null;
        }

        $this->db->bind(':id', $id);
        $this->db->bind(':author_id', $authorId);
        $row = $this->db->single();
        if (!$row) {
            return null;
        }
        $parsed = $this->parseCatatanList([$row]);
        return $parsed[0] ?? $row;
    }

    /**
     * Ambil seluruh catatan yang dikirim oleh Kepsek ke guru
     */
    public function getSentByKepsek($kepsekId)
    {
        $kepsekId = (int)$kepsekId;
        $this->db->query(
            "SELECT cg.id, cg.guru_id AS target_id, cg.catatan, cg.created_at,
                    COALESCE(u.name, 'Guru') AS target_name,
                    COALESCE(u.username, '') AS target_username,
                    g.nip,
                    'guru' AS target_role
             FROM catatan_guru cg
             LEFT JOIN guru g ON g.id = cg.guru_id
             LEFT JOIN users u ON u.id = g.user_id
             WHERE cg.kepsek_id = :kid
             ORDER BY cg.created_at DESC"
        );
        $this->db->bind(':kid', $kepsekId);
        return $this->parseCatatanList($this->db->resultSet());
    }

    /**
     * Perbarui judul dan isi catatan berdasarkan kepemilikan penulis
     */
    public function updateByAuthor($id, $targetRole, $authorId, $title, $content)
    {
        $existing = $this->getByIdForAuthor($id, $targetRole, $authorId);
        if (!$existing) {
            return false;
        }

        $title = trim((string)$title);
        $content = trim((string)$content);
        if ($content === '') {
            return false;
        }

        $formattedNote = ($title !== '') ? ("[" . $title . "]\n" . $content) : $content;

        if ($targetRole === 'siswa') {
            $this->db->query("UPDATE catatan_siswa SET catatan = :catatan WHERE id = :id AND wali_kelas_id = :author_id");
        } elseif ($targetRole === 'guru') {
            $this->db->query("UPDATE catatan_guru SET catatan = :catatan WHERE id = :id AND kepsek_id = :author_id");
        } else {
            return false;
        }

        $this->db->bind(':catatan', $formattedNote);
        $this->db->bind(':id', (int)$id);
        $this->db->bind(':author_id', (int)$authorId);
        return (bool)$this->db->execute();
    }

    /**
     * Hapus catatan berdasarkan kepemilikan penulis
     */
    public function deleteByAuthor($id, $targetRole, $authorId)
    {
        $existing = $this->getByIdForAuthor($id, $targetRole, $authorId);
        if (!$existing) {
            return false;
        }

        if ($targetRole === 'siswa') {
            $this->db->query("DELETE FROM catatan_siswa WHERE id = :id AND wali_kelas_id = :author_id");
        } elseif ($targetRole === 'guru') {
            $this->db->query("DELETE FROM catatan_guru WHERE id = :id AND kepsek_id = :author_id");
        } else {
            return false;
        }

        $this->db->bind(':id', (int)$id);
        $this->db->bind(':author_id', (int)$authorId);
        return (bool)$this->db->execute();
    }

    /**
     * Ambil seluruh catatan siswa yang dikirim oleh seluruh Wali Kelas (untuk Admin)
     */
    public function getAllCatatanSiswa()
    {
        $this->db->query(
            "SELECT cs.id, cs.siswa_id, cs.wali_kelas_id, cs.catatan, cs.created_at,
                    COALESCE(u.name, 'Siswa') AS target_name,
                    COALESCE(u.username, '') AS target_username,
                    s.nis,
                    k.nama_kelas,
                    COALESCE(uwk.name, ugwk.name, 'Wali Kelas') AS author_name,
                    'siswa' AS target_role
             FROM catatan_siswa cs
             LEFT JOIN siswa s ON s.id = cs.siswa_id
             LEFT JOIN users u ON u.id = s.user_id
             LEFT JOIN kelas k ON k.id = s.kelas_id
             LEFT JOIN users uwk ON uwk.id = cs.wali_kelas_id
             LEFT JOIN guru gwk ON gwk.id = cs.wali_kelas_id
             LEFT JOIN users ugwk ON ugwk.id = gwk.user_id
             ORDER BY cs.created_at DESC"
        );
        return $this->parseCatatanList($this->db->resultSet());
    }

    /**
     * Ambil seluruh catatan guru yang dikirim oleh Kepala Sekolah (untuk Admin)
     */
    public function getAllCatatanGuru()
    {
        $this->db->query(
            "SELECT cg.id, cg.guru_id, cg.kepsek_id, cg.catatan, cg.created_at,
                    COALESCE(u.name, 'Guru') AS target_name,
                    COALESCE(u.username, '') AS target_username,
                    g.nip,
                    COALESCE(uk.name, 'Kepala Sekolah') AS author_name,
                    'guru' AS target_role
             FROM catatan_guru cg
             LEFT JOIN guru g ON g.id = cg.guru_id
             LEFT JOIN users u ON u.id = g.user_id
             LEFT JOIN users uk ON uk.id = cg.kepsek_id
             ORDER BY cg.created_at DESC"
        );
        return $this->parseCatatanList($this->db->resultSet());
    }

    /**
     * Perbarui judul dan isi catatan oleh Administrator (bisa edit catatan siswa maupun guru)
     */
    public function updateByAdmin($id, $targetRole, $title, $content)
    {
        $id = (int)$id;
        $title = trim((string)$title);
        $content = trim((string)$content);
        if ($id <= 0 || $content === '') {
            return false;
        }

        $formattedNote = ($title !== '') ? ("[" . $title . "]\n" . $content) : $content;

        if ($targetRole === 'siswa') {
            $this->db->query("UPDATE catatan_siswa SET catatan = :catatan WHERE id = :id");
        } elseif ($targetRole === 'guru') {
            $this->db->query("UPDATE catatan_guru SET catatan = :catatan WHERE id = :id");
        } else {
            return false;
        }

        $this->db->bind(':catatan', $formattedNote);
        $this->db->bind(':id', $id);
        return (bool)$this->db->execute();
    }

    /**
     * Hapus catatan oleh Administrator (bisa hapus catatan siswa maupun guru)
     */
    public function deleteByAdmin($id, $targetRole)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        if ($targetRole === 'siswa') {
            $this->db->query("DELETE FROM catatan_siswa WHERE id = :id");
        } elseif ($targetRole === 'guru') {
            $this->db->query("DELETE FROM catatan_guru WHERE id = :id");
        } else {
            return false;
        }

        $this->db->bind(':id', $id);
        return (bool)$this->db->execute();
    }
}
