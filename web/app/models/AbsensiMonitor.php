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
                SUM(CASE WHEN ab.status = 'alfa' OR (ab.id IS NULL AND k.status = 'aktif') THEN 1 ELSE 0 END) AS alfa,
                SUM(CASE WHEN ab.status = 'pkl' OR (ab.id IS NULL AND k.status = 'pkl') THEN 1 ELSE 0 END) AS pkl
            FROM siswa s
            JOIN kelas k ON s.kelas_id = k.id
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
                COUNT(DISTINCT u.id)                                        AS total_guru,
                SUM(CASE WHEN ab.status = 'hadir'    THEN 1 ELSE 0 END)    AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END)   AS terlambat,
                SUM(CASE WHEN ab.status = 'izin'     THEN 1 ELSE 0 END)    AS izin,
                SUM(CASE WHEN ab.status = 'sakit'    THEN 1 ELSE 0 END)    AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' OR (ab.id IS NULL AND jg.is_active = 1) THEN 1 ELSE 0 END) AS alfa
            FROM users u
            LEFT JOIN guru g ON u.id = g.user_id
            LEFT JOIN absensi_guru ab ON ab.guru_id = g.id AND ab.tanggal = :today
            LEFT JOIN jadwal_guru jg ON jg.guru_id = g.id AND jg.hari = :hari_indo
            WHERE u.role IN ('guru', 'wali_kelas', 'walikelas')
        ");
        $this->db->bind(':today', $today);
        $this->db->bind(':hari_indo', $hari_indo);
        return $this->db->single();
    }

    public function getTrendKehadiran7Hari() {
        $startDate = date('Y-m-d', strtotime("-6 days"));

        $siswaByDate = [];
        $this->db->query("
            SELECT tanggal, COUNT(*) as jml 
            FROM absensi_siswa 
            WHERE tanggal >= :startDate AND status IN ('hadir', 'terlambat', 'pkl') 
            GROUP BY tanggal
        ");
        $this->db->bind(':startDate', $startDate);
        $rowsSiswa = $this->db->resultSet();
        if ($rowsSiswa) {
            foreach ($rowsSiswa as $r) {
                $siswaByDate[$r->tanggal] = (int)$r->jml;
            }
        }

        $guruByDate = [];
        $this->db->query("
            SELECT tanggal, COUNT(*) as jml 
            FROM absensi_guru 
            WHERE tanggal >= :startDate AND status IN ('hadir', 'terlambat') 
            GROUP BY tanggal
        ");
        $this->db->bind(':startDate', $startDate);
        $rowsGuru = $this->db->resultSet();
        if ($rowsGuru) {
            foreach ($rowsGuru as $r) {
                $guruByDate[$r->tanggal] = (int)$r->jml;
            }
        }

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $displayDate = date('d M', strtotime($date));
            $trend[] = [
                'tanggal' => $date,
                'label'   => $displayDate,
                'siswa'   => $siswaByDate[$date] ?? 0,
                'guru'    => $guruByDate[$date] ?? 0
            ];
        }
        return $trend;
    }

    public function getStatsKehadiranPerKelasHariIni() {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT 
                k.id,
                k.nama_kelas,
                COUNT(DISTINCT s.id) as total_siswa,
                SUM(CASE WHEN ab.status IN ('hadir', 'terlambat', 'pkl') THEN 1 ELSE 0 END) as hadir_count,
                SUM(CASE WHEN ab.status IN ('izin', 'sakit') THEN 1 ELSE 0 END) as izin_count,
                SUM(CASE WHEN ab.status = 'alfa' OR (ab.id IS NULL AND k.status = 'aktif') THEN 1 ELSE 0 END) as alfa_count
            FROM kelas k
            LEFT JOIN siswa s ON s.kelas_id = k.id
            LEFT JOIN absensi_siswa ab ON ab.siswa_id = s.id AND ab.tanggal = :today
            GROUP BY k.id, k.nama_kelas
            ORDER BY k.nama_kelas ASC
        ");
        $this->db->bind(':today', $today);
        return $this->db->resultSet();
    }

    public function getStatusIotDevices() {
        $now = date('Y-m-d H:i:s');
        $this->db->query("
            SELECT
                id, device_code, device_name, device_type, location, ip_address, status, last_seen, wifi_ssid,
                pending_host, pending_ssid,
                CASE
                    WHEN last_seen IS NULL THEN 'offline'
                    WHEN last_seen >= DATE_SUB(:now, INTERVAL 5 MINUTE) THEN 'online'
                    ELSE 'offline'
                END AS iot_status
            FROM iot_devices
            ORDER BY device_type, device_name
        ");
        $this->db->bind(':now', $now);
        return $this->db->resultSet();
    }

    // =========================================================================
    // ADMIN — Daftar Izin & Pulang Cepat Hari Ini
    // =========================================================================
    public function getSiswaIzinPulangCepatHariIni() {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT ab.id, ab.jam_datang, ab.jam_pulang, ab.status, ab.keterangan, 
                   s.nis, u.name as nama_siswa, k.nama_kelas
            FROM absensi_siswa ab
            JOIN siswa s ON ab.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE ab.tanggal = :today
              AND ab.status IN ('izin', 'sakit', 'pulang_cepat')
            ORDER BY ab.id DESC
        ");
        $this->db->bind(':today', $today);
        return $this->db->resultSet();
    }

    // =========================================================================
    // WALI KELAS — statistik kelasnya sendiri
    // =========================================================================

    /** Ambil kelas_id yang diasuh oleh wali_kelas_id tertentu */
    public function getKelasWali($wali_kelas_id) {
        $this->db->query("SELECT id, class_code, nama_kelas FROM kelas WHERE wali_kelas_id = :wid");
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
                SUM(CASE WHEN ab.status = 'alfa' OR (ab.id IS NULL AND k.status = 'aktif') THEN 1 ELSE 0 END) AS alfa,
                SUM(CASE WHEN ab.status = 'pkl' OR (ab.id IS NULL AND k.status = 'pkl') THEN 1 ELSE 0 END) AS pkl
            FROM siswa s
            JOIN kelas k ON s.kelas_id = k.id
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
              AND u.role IN ('guru', 'wali_kelas', 'walikelas')
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
        $stFilter = strtolower(trim((string)$status));
        if ($stFilter === 'hadir') {
            $where .= " AND ag.status IN ('hadir', 'terlambat')";
        } elseif ($stFilter !== '') {
            $where .= " AND ag.status = :status";
        }
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
        if ($stFilter !== '' && $stFilter !== 'hadir') $this->db->bind(':status', $stFilter);
        if ($search) $this->db->bind(':search', '%' . $search . '%');
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        $this->db->bind(':off', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countRiwayatGuru($guru_id, $dari = '', $sampai = '', $status = '', $search = '') {
        $where = "WHERE ag.guru_id = :gid";
        if ($dari)   $where .= " AND ag.tanggal >= :dari";
        if ($sampai) $where .= " AND ag.tanggal <= :sampai";
        $stFilter = strtolower(trim((string)$status));
        if ($stFilter === 'hadir') {
            $where .= " AND ag.status IN ('hadir', 'terlambat')";
        } elseif ($stFilter !== '') {
            $where .= " AND ag.status = :status";
        }
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
        if ($stFilter !== '' && $stFilter !== 'hadir') $this->db->bind(':status', $stFilter);
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
        $stFilter = strtolower(trim((string)$status));
        if ($stFilter === 'hadir') {
            $where .= " AND ab.status IN ('hadir', 'terlambat')";
        } elseif ($stFilter !== '') {
            $where .= " AND ab.status = :status";
        }

        $this->db->query("
            SELECT ab.id, ab.tanggal, ab.jam_datang, ab.jam_pulang, ab.status, ab.keterangan, ab.foto_masuk, ab.foto_pulang, ab.jarak_meter
            FROM absensi_siswa ab
            $where
            ORDER BY ab.tanggal DESC
            LIMIT :lim OFFSET :off
        ");
        $this->db->bind(':sid', $siswa_id, PDO::PARAM_INT);
        if ($dari)   $this->db->bind(':dari', $dari);
        if ($sampai) $this->db->bind(':sampai', $sampai);
        if ($stFilter !== '' && $stFilter !== 'hadir') $this->db->bind(':status', $stFilter);
        $this->db->bind(':lim', $limit, PDO::PARAM_INT);
        $this->db->bind(':off', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countRiwayatSiswa($siswa_id, $dari = '', $sampai = '', $status = '') {
        $where = "WHERE ab.siswa_id = :sid";
        if ($dari)   $where .= " AND ab.tanggal >= :dari";
        if ($sampai) $where .= " AND ab.tanggal <= :sampai";
        $stFilter = strtolower(trim((string)$status));
        if ($stFilter === 'hadir') {
            $where .= " AND ab.status IN ('hadir', 'terlambat')";
        } elseif ($stFilter !== '') {
            $where .= " AND ab.status = :status";
        }

        $this->db->query("SELECT COUNT(*) AS total FROM absensi_siswa ab $where");
        $this->db->bind(':sid', $siswa_id, PDO::PARAM_INT);
        if ($dari)   $this->db->bind(':dari', $dari);
        if ($sampai) $this->db->bind(':sampai', $sampai);
        if ($stFilter !== '' && $stFilter !== 'hadir') $this->db->bind(':status', $stFilter);
        $r = $this->db->single();
        return (int)($r->total ?? 0);
    }

    public function cekPulangCepatSiswa($siswa_id) {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT id, jam_pengajuan as jam_pulang, jam_pulang_jadwal as jadwal_pulang
            FROM pengajuan_pulang_cepat
            WHERE siswa_id = :sid 
              AND tanggal = :today
              AND status = 'DRAFT'
        ");
        $this->db->bind(':sid', $siswa_id);
        $this->db->bind(':today', $today);
        return $this->db->single();
    }

    public function buatPermohonanPulangCepat($siswa_id, $absensi_id, $kategori, $detail) {
        // Obsolete. Kept to avoid breaking interface, but we won't use it.
        return false;
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

    /** Statistik kehadiran bulanan guru */
    public function getStatsBulananGuru($guru_id, $bulan = null) {
        $bulan = $bulan ?? date('Y-m');
        $this->db->query("
            SELECT
                COUNT(id) AS total_presensi,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status IN ('izin', 'sakit') THEN 1 ELSE 0 END) AS izin_sakit,
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa
            FROM absensi_guru
            WHERE guru_id = :gid AND DATE_FORMAT(tanggal, '%Y-%m') = :bln
        ");
        $this->db->bind(':gid', $guru_id);
        $this->db->bind(':bln', $bulan);
        return $this->db->single();
    }

    /** Statistik kehadiran bulanan siswa */
    public function getStatsBulananSiswa($siswa_id, $bulan = null) {
        $bulan = $bulan ?? date('Y-m');
        $this->db->query("
            SELECT
                COUNT(id) AS total_presensi,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status IN ('izin', 'sakit') THEN 1 ELSE 0 END) AS izin_sakit,
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa,
                SUM(CASE WHEN status = 'pkl' THEN 1 ELSE 0 END) AS pkl
            FROM absensi_siswa
            WHERE siswa_id = :sid AND DATE_FORMAT(tanggal, '%Y-%m') = :bln
        ");
        $this->db->bind(':sid', $siswa_id);
        $this->db->bind(':bln', $bulan);
        return $this->db->single();
    }

    /** Jadwal kerja operasional guru hari ini */
    public function getJadwalHariIniGuru($guru_id) {
        $hari_ini = date('l');
        $hari_indo_map = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hari_indo = $hari_indo_map[$hari_ini] ?? 'Senin';

        $this->db->query("SELECT * FROM jadwal_guru WHERE guru_id = :gid AND hari = :hari LIMIT 1");
        $this->db->bind(':gid', $guru_id);
        $this->db->bind(':hari', $hari_indo);
        return $this->db->single();
    }

    /** Jadwal operasional sekolah untuk siswa hari ini */
    public function getJadwalHariIniSiswa() {
        $hari_ini = date('l');
        $hari_indo_map = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hari_indo = $hari_indo_map[$hari_ini] ?? 'Senin';

        $this->db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari LIMIT 1");
        $this->db->bind(':hari', $hari_indo);
        return $this->db->single();
    }
}
