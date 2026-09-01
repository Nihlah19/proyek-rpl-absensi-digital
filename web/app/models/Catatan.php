<?php
class Catatan extends Model
{
    // ──────────────────────────────────────────────────────────────
    // CREATE
    // Wali kelas menulis ke siswa  → tabel catatan_siswa
    // Kepsek   menulis ke guru    → tabel catatan_guru
    // ──────────────────────────────────────────────────────────────

    public function create($data)
    {
        $isSiswa = ($data['target_role'] === 'siswa');

        if ($isSiswa) {
            // ── Catatan Wali Kelas → Siswa ──
            $query = "INSERT INTO catatan_siswa
                           (siswa_id, wali_kelas_id, catatan, created_at)
                      VALUES (:target_id, :author_id, :content, CURRENT_TIMESTAMP)";
            $this->db->query($query);
            $this->db->bind(':target_id', $data['target_id']);   // siswa_id
            $this->db->bind(':author_id', $data['author_id']);    // wali_kelas_id (user id wali)
            $this->db->bind(':content', $data['title'] . "\n\n" . $data['content']);
            $this->db->execute();
            return $this->db->rowCount();
        } else {
            // ── Catatan Kepsek → Guru ──
            $query = "INSERT INTO catatan_guru
                           (guru_id, kepsek_id, catatan, created_at)
                      VALUES (:target_id, :author_id, :content, CURRENT_TIMESTAMP)";
            $this->db->query($query);
            $this->db->bind(':target_id', $data['target_id']);   // guru_id
            $this->db->bind(':author_id', $data['author_id']);    // kepsek_id (user id kepsek)
            $this->db->bind(':content', $data['title'] . "\n\n" . $data['content']);
            $this->db->execute();
            return $this->db->rowCount();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // READ — ambil catatan yang diterima user (siswa / guru)
    // ──────────────────────────────────────────────────────────────

    public function getByUser($userId, $role)
    {
        if ($role === 'siswa') {
            // Ambil siswa_id dari user_id
            $this->db->query("SELECT id FROM siswa WHERE user_id = :uid");
            $this->db->bind(':uid', $userId);
            $siswa = $this->db->single();
            if (!$siswa) return [];

            $this->db->query(
                "SELECT cs.*, u.name AS author_name
                 FROM catatan_siswa cs
                 JOIN users u ON u.id = cs.wali_kelas_id
                 WHERE cs.siswa_id = :sid
                 ORDER BY cs.created_at DESC"
            );
            $this->db->bind(':sid', $siswa->id);
            return $this->db->resultSet();
        }

        if ($role === 'guru') {
            // Ambil guru_id dari user_id
            $this->db->query("SELECT id FROM guru WHERE user_id = :uid");
            $this->db->bind(':uid', $userId);
            $guru = $this->db->single();
            if (!$guru) return [];

            $this->db->query(
                "SELECT cg.*, u.name AS author_name
                 FROM catatan_guru cg
                 JOIN users u ON u.id = cg.kepsek_id
                 WHERE cg.guru_id = :gid
                 ORDER BY cg.created_at DESC"
            );
            $this->db->bind(':gid', $guru->id);
            return $this->db->resultSet();
        }

        return [];
    }

    // ──────────────────────────────────────────────────────────────
    // GET BY ID (untuk detail jika diperlukan di masa depan)
    // ──────────────────────────────────────────────────────────────

    public function getById($id)
    {
        // Coba cari di catatan_siswa dulu
        $this->db->query(
            "SELECT cs.*, u.name AS author_name, 'siswa' AS note_type
             FROM catatan_siswa cs
             JOIN users u ON u.id = cs.wali_kelas_id
             WHERE cs.id = :id"
        );
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        if ($row) return $row;

        // Kalau tidak ada, cari di catatan_guru
        $this->db->query(
            "SELECT cg.*, u.name AS author_name, 'guru' AS note_type
             FROM catatan_guru cg
             JOIN users u ON u.id = cg.kepsek_id
             WHERE cg.id = :id"
        );
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
