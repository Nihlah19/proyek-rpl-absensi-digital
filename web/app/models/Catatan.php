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
        if ($role === 'siswa') {
            $this->db->query("SELECT id FROM siswa WHERE user_id = :uid");
            $this->db->bind(':uid', $userId);
            $siswa = $this->db->single();
            if (!$siswa) return [];

            $this->db->query(
                "SELECT cs.id, cs.siswa_id, cs.wali_kelas_id, cs.catatan, cs.created_at,
                        u.name AS author_name, u.username AS author_username
                 FROM catatan_siswa cs
                 JOIN users u ON u.id = cs.wali_kelas_id
                 WHERE cs.siswa_id = :sid
                 ORDER BY cs.created_at DESC"
            );
            $this->db->bind(':sid', $siswa->id);
            return $this->parseCatatanList($this->db->resultSet());
        }

        if ($role === 'guru' || $role === 'wali_kelas' || $role === 'walikelas') {
            $this->db->query("SELECT id FROM guru WHERE user_id = :uid");
            $this->db->bind(':uid', $userId);
            $guru = $this->db->single();
            if (!$guru) return [];

            $this->db->query(
                "SELECT cg.id, cg.guru_id, cg.kepsek_id, cg.catatan, cg.created_at,
                        u.name AS author_name, u.username AS author_username
                 FROM catatan_guru cg
                 JOIN users u ON u.id = cg.kepsek_id
                 WHERE cg.guru_id = :gid
                 ORDER BY cg.created_at DESC"
            );
            $this->db->bind(':gid', $guru->id);
            return $this->parseCatatanList($this->db->resultSet());
        }

        if ($role === 'kepsek') {
            $this->db->query(
                "SELECT cg.id, cg.guru_id, cg.kepsek_id, cg.catatan, cg.created_at,
                        u.name AS target_name, u.username AS target_username,
                        'Kepala Sekolah' AS author_name
                 FROM catatan_guru cg
                 JOIN guru g ON g.id = cg.guru_id
                 JOIN users u ON u.id = g.user_id
                 WHERE cg.kepsek_id = :uid
                 ORDER BY cg.created_at DESC"
            );
            $this->db->bind(':uid', $userId);
            return $this->parseCatatanList($this->db->resultSet());
        }

        return [];
    }

    /**
     * Ambil daftar catatan yang dikirim oleh Wali Kelas ke siswa kelasnya.
     */
    public function getSentByWaliKelas($wali_kelas_id)
    {
        $this->db->query(
            "SELECT cs.id, cs.siswa_id, cs.wali_kelas_id, cs.catatan, cs.created_at,
                    u.name AS target_name, u.username AS target_username, s.nis,
                    'Wali Kelas' AS author_name
             FROM catatan_siswa cs
             JOIN siswa s ON s.id = cs.siswa_id
             JOIN users u ON u.id = s.user_id
             WHERE cs.wali_kelas_id = :uid
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
        $this->db->query(
            "SELECT cs.*, u.name AS author_name, u.username AS author_username, 'siswa' AS source_table
             FROM catatan_siswa cs
             JOIN users u ON u.id = cs.wali_kelas_id
             WHERE cs.id = :id"
        );
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        if ($row) return $row;

        $this->db->query(
            "SELECT cg.*, u.name AS author_name, u.username AS author_username, 'guru' AS source_table
             FROM catatan_guru cg
             JOIN users u ON u.id = cg.kepsek_id
             WHERE cg.id = :id"
        );
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
