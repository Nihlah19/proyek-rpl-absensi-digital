<?php
/**
 * AbsensiMonitor.php
 * Model untuk mengambil statistik dan riwayat absensi per role.
 * Tidak mengubah business logic yang ada di Absensi.php.
 */
class AbsensiMonitor extends Model {

    // =========================================================================
    // ADMIN — statistik global hari ini
    // =========================================================================

    public function getStatsSiswaHariIni() {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT
                COUNT(DISTINCT s.id)                                        AS total_siswa,
                SUM(CASE WHEN ab.status = 'hadir'    THEN 1 ELSE 0 END)    AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END)   AS terlambat,
                SUM(CASE WHEN ab.status = 'izin'     THEN 1 ELSE 0 END)    AS izin,
                SUM(CASE WHEN ab.status = 'sakit'    THEN 1 ELSE 0 END)    AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' OR ab.id IS NULL THEN 1 ELSE 0 END) AS alfa
            FROM siswa s
            LEFT JOIN absensi_siswa ab ON ab.siswa_id = s.id AND ab.tanggal = :today
        ");
        $this->db->bind(':today', $today);
        return $this->db->single();
    }

    public function getStatsGuruHariIni() {
        $today = date('Y-m-d');
        $hari_ini = date('l');
        $hari_indo_map = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $hari_indo = $hari_indo_map[$hari_ini];

        $this->db->query("
            SELECT
                COUNT(DISTINCT g.id)                                        AS total_guru,
                SUM(CASE WHEN ab.status = 'hadir'    THEN 1 ELSE 0 END)    AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END)   AS terlambat,
                SUM(CASE WHEN ab.status = 'izin'     THEN 1 ELSE 0 END)    AS izin,
                SUM(CASE WHEN ab.status = 'sakit'    THEN 1 ELSE 0 END)    AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' OR (ab.id IS NULL AND jg.is_active = 1) THEN 1 ELSE 0 END) AS alfa
            FROM guru g
            LEFT JOIN absensi_guru ab ON ab.guru_id = g.id AND ab.tanggal = :today
            LEFT JOIN jadwal_guru jg ON jg.guru_id = g.id AND jg.hari = :hari_indo
        ");
        $this->db->bind(':today', $today);
        $this->db->bind(':hari_indo', $hari_indo);
        return $this->db->single();
    }

    public function getStatusIotDevices() {
        $this->db->query("
            SELECT
                device_code, device_name, device_type, location, status, last_seen,
                CASE
                    WHEN last_seen IS NULL THEN 'offline'
                    WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN 'online'
                    ELSE 'offline'
                END AS iot_status
            FROM iot_devices
            ORDER BY device_type, device_name
        ");
        return $this->db->resultSet();
    }

    // =========================================================================
    // WALI KELAS — statistik kelasnya sendiri
    // =========================================================================

    /** Ambil kelas_id yang diasuh oleh wali_kelas_id tertentu */
    public function getKelasWali($wali_kelas_id) {
        $this->db->query("SELECT id, nama_kelas FROM kelas WHERE wali_kelas_id = :wid");
        $this->db->bind(':wid', $wali_kelas_id);
        return $this->db->single();
    }

    public function getStatsSiswaKelas($kelas_id) {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT
                COUNT(DISTINCT s.id)                                        AS total_siswa,
                SUM(CASE WHEN ab.status = 'hadir'    THEN 1 ELSE 0 END)    AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END)   AS terlambat,
                SUM(CASE WHEN ab.status = 'izin'     THEN 1 ELSE 0 END)    AS izin,
                SUM(CASE WHEN ab.status = 'sakit'    THEN 1 ELSE 0 END)    AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' OR ab.id IS NULL THEN 1 ELSE 0 END) AS alfa
            FROM siswa s
            LEFT JOIN absensi_siswa ab ON ab.siswa_id = s.id AND ab.tanggal = :today
            WHERE s.kelas_id = :kelas_id
        ");
        $this->db->bind(':today', $today);
        $this->db->bind(':kelas_id', $kelas_id);
        return $this->db->single();
    }

    /** 5 siswa yang paling sering terlambat bulan ini */
    public function getSiswaSaringTerlambat($kelas_id, $limit = 5) {
        $bulanIni = date('Y-m');
        $this->db->query("
            SELECT u.name, COUNT(ab.id) AS jumlah_terlambat
            FROM absensi_siswa ab
            JOIN siswa s  ON s.id = ab.siswa_id
            JOIN users u  ON u.id = s.user_id
            WHERE ab.status = 'terlambat'
              AND s.kelas_id = :kelas_id
              AND DATE_FORMAT(ab.tanggal, '%Y-%m') = :bulan
            GROUP BY s.id, u.name
            ORDER BY jumlah_terlambat DESC
            LIMIT :lim
        ");
        $this->db->bind(':kelas_id', $kelas_id);
        $this->db->bind(':bulan', $bulanIni);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    // =========================================================================
    // KEPALA SEKOLAH — statistik guru
    // =========================================================================

    /** 5 guru yang paling sering terlambat bulan ini */
    public function getGuruSeringTerlambat($limit = 5) {
        $bulanIni = date('Y-m');
        $this->db->query("
            SELECT u.name, COUNT(ab.id) AS jumlah_terlambat
            FROM absensi_guru ab
            JOIN guru g   ON g.id = ab.guru_id
            JOIN users u  ON u.id = g.user_id
            WHERE ab.status = 'terlambat'
              AND DATE_FORMAT(ab.tanggal, '%Y-%m') = :bulan
            GROUP BY g.id, u.name
            ORDER BY jumlah_terlambat DESC
            LIMIT :lim
        ");
        $this->db->bind(':bulan', $bulanIni);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    // =========================================================================
    // GURU — absensi diri sendiri
    // =========================================================================

    public function getAbsensiGuruHariIni($guru_id) {
        $today = date('Y-m-d');
        $this->db->query("SELECT * FROM absensi_guru WHERE guru_id = :gid AND tanggal = :today");
        $this->db->bind(':gid', $guru_id);
        $this->db->bind(':today', $today);
        return $this->db->single();
    }

    /**
     * Riwayat absensi guru dengan filter opsional.
     * @param int    $guru_id
     * @param string $dari    tanggal mulai (Y-m-d) atau ''
     * @param string $sampai  tanggal akhir (Y-m-d) atau ''
     * @param string $status  status filter atau ''
     * @param string $search  pencarian teks atau ''
     * @param int    $offset  offset pagination
     * @param int    $limit   baris per halaman
     */
    public function getRiwayatGuru($guru_id, $dari = '', $sampai = '', $status = '', $search = '', $offset = 0, $limit = 15) {
        $where = "WHERE ag.guru_id = :gid";
        if ($dari)   $where .= " AND ag.tanggal >= :dari";
        if ($sampai) $where .= " AND ag.tanggal <= :sampai";
        if ($status) $where .= " AND ag.status = :status";
        if ($search) $where .= " AND u.name LIKE :search";

        $this->db->query("
            SELECT ag.tanggal, ag.jam_datang, ag.jam_pulang, ag.status, ag.keterangan,
                   u.name
            FROM absensi_guru ag
            JOIN guru g  ON g.id = ag.guru_id
            JOIN users u ON u.id = g.user_id
            $where
            ORDER BY ag.tanggal DESC
            LIMIT :lim OFFSET :off
        ");
        $this->db->bind(':gid', $guru_id, PDO::PARAM_INT);
        if ($dari)   $this->db->bind(':dari', $dari);
        if ($sampai) $this->db->bind(':sampai', $sampai);
        if ($status) $this->db->bind(':status', $status);
        if ($search) $this->db->bind(':search', '%' . $search . '%');
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        $this->db->bind(':off', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countRiwayatGuru($guru_id, $dari = '', $sampai = '', $status = '', $search = '') {
        $where = "WHERE ag.guru_id = :gid";
        if ($dari)   $where .= " AND ag.tanggal >= :dari";
        if ($sampai) $where .= " AND ag.tanggal <= :sampai";
        if ($status) $where .= " AND ag.status = :status";
        if ($search) $where .= " AND u.name LIKE :search";

        $this->db->query("
            SELECT COUNT(*) AS total
            FROM absensi_guru ag
            JOIN guru g  ON g.id = ag.guru_id
            JOIN users u ON u.id = g.user_id
            $where
        ");
        $this->db->bind(':gid', $guru_id, PDO::PARAM_INT);
        if ($dari)   $this->db->bind(':dari', $dari);
        if ($sampai) $this->db->bind(':sampai', $sampai);
        if ($status) $this->db->bind(':status', $status);
        if ($search) $this->db->bind(':search', '%' . $search . '%');
        $r = $this->db->single();
        return (int)($r->total ?? 0);
    }

    // =========================================================================
    // SISWA — absensi diri sendiri
    // =========================================================================

    public function getAbsensiSiswaHariIni($siswa_id) {
        $today = date('Y-m-d');
        $this->db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :today");
        $this->db->bind(':sid', $siswa_id);
        $this->db->bind(':today', $today);
        return $this->db->single();
    }

    public function getRiwayatSiswa($siswa_id, $dari = '', $sampai = '', $status = '', $search = '', $offset = 0, $limit = 15) {
        $where = "WHERE ab.siswa_id = :sid";
        if ($dari)   $where .= " AND ab.tanggal >= :dari";
        if ($sampai) $where .= " AND ab.tanggal <= :sampai";
        if ($status) $where .= " AND ab.status = :status";

        $this->db->query("
            SELECT ab.tanggal, ab.jam_datang, ab.jam_pulang, ab.status, ab.keterangan
            FROM absensi_siswa ab
            $where
            ORDER BY ab.tanggal DESC
            LIMIT :lim OFFSET :off
        ");
        $this->db->bind(':sid', $siswa_id, PDO::PARAM_INT);
        if ($dari)   $this->db->bind(':dari', $dari);
        if ($sampai) $this->db->bind(':sampai', $sampai);
        if ($status) $this->db->bind(':status', $status);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        $this->db->bind(':off', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countRiwayatSiswa($siswa_id, $dari = '', $sampai = '', $status = '') {
        $where = "WHERE ab.siswa_id = :sid";
        if ($dari)   $where .= " AND ab.tanggal >= :dari";
        if ($sampai) $where .= " AND ab.tanggal <= :sampai";
        if ($status) $where .= " AND ab.status = :status";

        $this->db->query("SELECT COUNT(*) AS total FROM absensi_siswa ab $where");
        $this->db->bind(':sid', $siswa_id, PDO::PARAM_INT);
        if ($dari)   $this->db->bind(':dari', $dari);
        if ($sampai) $this->db->bind(':sampai', $sampai);
        if ($status) $this->db->bind(':status', $status);
        $r = $this->db->single();
        return (int)($r->total ?? 0);
    }

    // =========================================================================
    // Helper — ambil guru_id / siswa_id dari user_id
    // =========================================================================

    public function getGuruIdByUserId($user_id) {
        $this->db->query("SELECT id FROM guru WHERE user_id = :uid");
        $this->db->bind(':uid', $user_id);
        $r = $this->db->single();
        return $r ? (int)$r->id : null;
    }

    public function getSiswaIdByUserId($user_id) {
        $this->db->query("SELECT id FROM siswa WHERE user_id = :uid");
        $this->db->bind(':uid', $user_id);
        $r = $this->db->single();
        return $r ? (int)$r->id : null;
    }
}
