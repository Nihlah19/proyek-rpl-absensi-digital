<?php
class Laporan {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function buildGuruFilters($filters) {
        $where = "1=1";
        $binds = [];
        
        if (!empty($filters['dari'])) {
            $where .= " AND ag.tanggal >= :dari";
            $binds[':dari'] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where .= " AND ag.tanggal <= :sampai";
            $binds[':sampai'] = $filters['sampai'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND ag.status = :status";
            $binds[':status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (u.name LIKE :search OR g.nip LIKE :search)";
            $binds[':search'] = '%' . $filters['search'] . '%';
        }
        
        return ['where' => $where, 'binds' => $binds];
    }

    private function buildSiswaFilters($filters) {
        $where = "1=1";
        $binds = [];
        
        if (!empty($filters['dari'])) {
            $where .= " AND ab.tanggal >= :dari";
            $binds[':dari'] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where .= " AND ab.tanggal <= :sampai";
            $binds[':sampai'] = $filters['sampai'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND ab.status = :status";
            $binds[':status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (u.name LIKE :search OR s.nis LIKE :search)";
            $binds[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['jurusan'])) {
            $where .= " AND k.jurusan = :jurusan";
            $binds[':jurusan'] = $filters['jurusan'];
        }
        if (!empty($filters['tingkat'])) {
            $where .= " AND k.tingkat = :tingkat";
            $binds[':tingkat'] = $filters['tingkat'];
        }
        if (!empty($filters['kelas_id'])) {
            $where .= " AND k.id = :kelas_id";
            $binds[':kelas_id'] = $filters['kelas_id'];
        }
        
        return ['where' => $where, 'binds' => $binds];
    }

    // ================== GURU ==================
    public function getRekapGuru($filters = []) {
        $filterData = $this->buildGuruFilters($filters);
        $where = $filterData['where'];
        $binds = $filterData['binds'];

        $query = "
            SELECT ag.tanggal, ag.jam_datang, ag.jam_pulang, ag.status, ag.keterangan,
                   u.name, g.nip, g.id AS guru_id,
                   (SELECT GROUP_CONCAT(cg.catatan SEPARATOR ' | ')
                    FROM catatan_guru cg
                    WHERE cg.guru_id = g.id
                    AND DATE(cg.created_at) = ag.tanggal) AS data_catatan
            FROM absensi_guru ag
            JOIN guru g ON g.id = ag.guru_id
            JOIN users u ON u.id = g.user_id
            WHERE $where AND u.role IN ('guru', 'wali_kelas', 'walikelas')
            ORDER BY ag.tanggal DESC, u.name ASC
        ";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $query .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)$filters['offset'];
        }

        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    public function countRekapGuru($filters = []) {
        $filterData = $this->buildGuruFilters($filters);
        $where = $filterData['where'];
        $binds = $filterData['binds'];

        $this->db->query("
            SELECT COUNT(ag.id) AS total
            FROM absensi_guru ag
            JOIN guru g ON g.id = ag.guru_id
            JOIN users u ON u.id = g.user_id
            WHERE $where AND u.role IN ('guru', 'wali_kelas', 'walikelas')
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }
    
    public function getStatsRekapGuru($filters = []) {
        // We only use 'dari' and 'sampai' for stats usually, but let's reuse the filter partially
        $where = "1=1";
        $binds = [];
        
        if (!empty($filters['dari'])) {
            $where .= " AND ag.tanggal >= :dari";
            $binds[':dari'] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where .= " AND ag.tanggal <= :sampai";
            $binds[':sampai'] = $filters['sampai'];
        }

        $this->db->query("SELECT COUNT(id) as total FROM users WHERE role IN ('guru', 'wali_kelas', 'walikelas')");
        $totalGuru = $this->db->single()->total;

        $this->db->query("
            SELECT 
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa
            FROM absensi_guru ag
            JOIN guru g ON g.id = ag.guru_id
            JOIN users u ON u.id = g.user_id
            WHERE $where AND u.role IN ('guru', 'wali_kelas', 'walikelas')
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $stats = $this->db->single();
        $totalHadir = ($stats->hadir ?? 0) + ($stats->terlambat ?? 0);
        $persentase = $totalGuru > 0 ? round(($totalHadir / $totalGuru) * 100, 1) : 0;

        return [
            'total_guru' => $totalGuru,
            'hadir' => $stats->hadir ?? 0,
            'terlambat' => $stats->terlambat ?? 0,
            'izin' => $stats->izin ?? 0,
            'sakit' => $stats->sakit ?? 0,
            'alfa' => $stats->alfa ?? 0,
            'persentase' => $persentase
        ];
    }

    // ================== SISWA ==================
    public function getRekapSiswa($filters = []) {
        $filterData = $this->buildSiswaFilters($filters);
        $where = $filterData['where'];
        $binds = $filterData['binds'];

        $query = "
            SELECT ab.tanggal, ab.jam_datang, ab.jam_pulang, ab.status, ab.keterangan,
                   u.name, s.nis, k.nama_kelas, k.jurusan, k.tingkat, s.id AS siswa_id,
                   (SELECT GROUP_CONCAT(cs.catatan SEPARATOR ' | ')
                    FROM catatan_siswa cs
                    WHERE cs.siswa_id = s.id
                    AND DATE(cs.created_at) = ab.tanggal) AS data_catatan
            FROM absensi_siswa ab
            JOIN siswa s ON s.id = ab.siswa_id
            JOIN users u ON u.id = s.user_id
            JOIN kelas k ON k.id = s.kelas_id
            WHERE $where
            ORDER BY ab.tanggal DESC, u.name ASC
        ";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $query .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)$filters['offset'];
        }

        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    public function countRekapSiswa($filters = []) {
        $filterData = $this->buildSiswaFilters($filters);
        $where = $filterData['where'];
        $binds = $filterData['binds'];

        $this->db->query("
            SELECT COUNT(ab.id) AS total
            FROM absensi_siswa ab
            JOIN siswa s ON s.id = ab.siswa_id
            JOIN users u ON u.id = s.user_id
            JOIN kelas k ON k.id = s.kelas_id
            WHERE $where
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }
    
    public function getStatsRekapSiswa($filters = []) {
        $where = "1=1";
        $binds = [];
        
        if (!empty($filters['dari'])) {
            $where .= " AND ab.tanggal >= :dari";
            $binds[':dari'] = $filters['dari'];
        }
        if (!empty($filters['sampai'])) {
            $where .= " AND ab.tanggal <= :sampai";
            $binds[':sampai'] = $filters['sampai'];
        }
        if (!empty($filters['kelas_id'])) {
            $where .= " AND k.id = :kelas_id";
            $binds[':kelas_id'] = $filters['kelas_id'];
        }

        $this->db->query("
            SELECT COUNT(s.id) as total 
            FROM siswa s 
            JOIN kelas k ON s.kelas_id = k.id 
            " . (!empty($filters['kelas_id']) ? "WHERE k.id = :kelas_id" : "")
        );
        if (!empty($filters['kelas_id'])) {
            $this->db->bind(':kelas_id', $filters['kelas_id']);
        }
        $totalSiswa = $this->db->single()->total;

        $this->db->query("
            SELECT 
                SUM(CASE WHEN ab.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN ab.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN ab.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' THEN 1 ELSE 0 END) AS alfa,
                SUM(CASE WHEN ab.status = 'pkl' THEN 1 ELSE 0 END) AS pkl
            FROM absensi_siswa ab
            JOIN siswa s ON ab.siswa_id = s.id
            JOIN kelas k ON s.kelas_id = k.id
            WHERE $where
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $stats = $this->db->single();
        $totalHadir = ($stats->hadir ?? 0) + ($stats->terlambat ?? 0) + ($stats->pkl ?? 0);
        $persentase = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100, 1) : 0;

        return [
            'total_siswa' => $totalSiswa,
            'hadir' => $stats->hadir ?? 0,
            'terlambat' => $stats->terlambat ?? 0,
            'izin' => $stats->izin ?? 0,
            'sakit' => $stats->sakit ?? 0,
            'alfa' => $stats->alfa ?? 0,
            'pkl' => $stats->pkl ?? 0,
            'persentase' => $persentase
        ];
    }

    public function getChartGuru($dari, $sampai) {
        $this->db->query("
            SELECT tanggal,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa
            FROM absensi_guru
            WHERE tanggal >= :dari AND tanggal <= :sampai
            GROUP BY tanggal
            ORDER BY tanggal ASC
        ");
        $this->db->bind(':dari', $dari);
        $this->db->bind(':sampai', $sampai);
        return $this->db->resultSet();
    }

    // ================== EXPORT (tanpa pagination) ==================

    public function getRekapGuruAll($dari = '', $sampai = '', $extraFilters = []) {
        $where = "1=1";
        $binds = [];
        if (!empty($dari)) {
            $where .= " AND ag.tanggal >= :dari";
            $binds[':dari'] = $dari;
        }
        if (!empty($sampai)) {
            $where .= " AND ag.tanggal <= :sampai";
            $binds[':sampai'] = $sampai;
        }
        if (!empty($extraFilters['status'])) {
            $where .= " AND ag.status = :status";
            $binds[':status'] = $extraFilters['status'];
        }
        if (!empty($extraFilters['search'])) {
            $where .= " AND (u.name LIKE :search OR g.nip LIKE :search)";
            $binds[':search'] = '%' . $extraFilters['search'] . '%';
        }
        $this->db->query("
            SELECT ag.tanggal, ag.jam_datang, ag.jam_pulang, ag.status, ag.keterangan,
                   u.name, g.nip,
                   (SELECT GROUP_CONCAT(cg.catatan SEPARATOR ' | ')
                    FROM catatan_guru cg
                    WHERE cg.guru_id = g.id
                    AND DATE(cg.created_at) = ag.tanggal) AS data_catatan
            FROM absensi_guru ag
            JOIN guru g ON g.id = ag.guru_id
            JOIN users u ON u.id = g.user_id
            WHERE $where AND u.role IN ('guru', 'wali_kelas', 'walikelas')
            ORDER BY ag.tanggal DESC, u.name ASC
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    public function getRekapSiswaAll($dari = '', $sampai = '', $extraFilters = []) {
        $where = "1=1";
        $binds = [];
        if (!empty($dari)) {
            $where .= " AND ab.tanggal >= :dari";
            $binds[':dari'] = $dari;
        }
        if (!empty($sampai)) {
            $where .= " AND ab.tanggal <= :sampai";
            $binds[':sampai'] = $sampai;
        }
        if (!empty($extraFilters['kelas_id'])) {
            $where .= " AND k.id = :kelas_id";
            $binds[':kelas_id'] = $extraFilters['kelas_id'];
        }
        if (!empty($extraFilters['status'])) {
            $where .= " AND ab.status = :status";
            $binds[':status'] = $extraFilters['status'];
        }
        if (!empty($extraFilters['search'])) {
            $where .= " AND (u.name LIKE :search OR s.nis LIKE :search)";
            $binds[':search'] = '%' . $extraFilters['search'] . '%';
        }
        $this->db->query("
            SELECT ab.tanggal, ab.jam_datang, ab.jam_pulang, ab.status, ab.keterangan,
                   u.name, s.nis, k.nama_kelas,
                   (SELECT GROUP_CONCAT(cs.catatan SEPARATOR ' | ')
                    FROM catatan_siswa cs
                    WHERE cs.siswa_id = s.id
                    AND DATE(cs.created_at) = ab.tanggal) AS data_catatan
            FROM absensi_siswa ab
            JOIN siswa s ON s.id = ab.siswa_id
            JOIN users u ON u.id = s.user_id
            JOIN kelas k ON k.id = s.kelas_id
            WHERE $where
            ORDER BY ab.tanggal DESC, k.nama_kelas ASC, u.name ASC
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    public function getRekapSiswaByKelas($kelas_id, $dari = '', $sampai = '') {
        $where = "s.kelas_id = :kelas_id";
        $binds = [':kelas_id' => $kelas_id];
        if (!empty($dari)) {
            $where .= " AND ab.tanggal >= :dari";
            $binds[':dari'] = $dari;
        }
        if (!empty($sampai)) {
            $where .= " AND ab.tanggal <= :sampai";
            $binds[':sampai'] = $sampai;
        }
        $this->db->query("
            SELECT ab.tanggal, ab.jam_datang, ab.jam_pulang, ab.status, ab.keterangan,
                   u.name, s.nis, k.nama_kelas,
                   (SELECT GROUP_CONCAT(cs.catatan SEPARATOR ' | ')
                    FROM catatan_siswa cs
                    WHERE cs.siswa_id = s.id
                    AND DATE(cs.created_at) = ab.tanggal) AS data_catatan
            FROM absensi_siswa ab
            JOIN siswa s ON s.id = ab.siswa_id
            JOIN users u ON u.id = s.user_id
            JOIN kelas k ON k.id = s.kelas_id
            WHERE $where
            ORDER BY ab.tanggal DESC, u.name ASC
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    // ================== AKUMULASI PER SISWA & GURU (FORMAT RAPORT / NILAI) ==================

    public function getAkumulasiSiswa($filters = []) {
        $dari   = !empty($filters['dari']) ? $filters['dari'] : date('Y-m-01');
        $sampai = !empty($filters['sampai']) ? $filters['sampai'] : date('Y-m-d');
        
        $where = "1=1";
        $binds = [':dari' => $dari, ':sampai' => $sampai];

        if (!empty($filters['kelas_id'])) {
            $where .= " AND k.id = :kelas_id";
            $binds[':kelas_id'] = $filters['kelas_id'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (u.name LIKE :search OR s.nis LIKE :search)";
            $binds[':search'] = '%' . $filters['search'] . '%';
        }

        $query = "
            SELECT 
                s.id AS siswa_id,
                s.nis,
                u.name,
                k.nama_kelas,
                k.tingkat,
                k.jurusan,
                COUNT(ab.id) AS total_absen,
                SUM(CASE WHEN ab.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN ab.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN ab.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' THEN 1 ELSE 0 END) AS alfa,
                SUM(CASE WHEN ab.status = 'pkl' THEN 1 ELSE 0 END) AS pkl
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN absensi_siswa ab ON ab.siswa_id = s.id 
                AND ab.tanggal >= :dari AND ab.tanggal <= :sampai
            WHERE $where
            GROUP BY s.id, s.nis, u.name, k.nama_kelas, k.tingkat, k.jurusan
            ORDER BY k.nama_kelas ASC, u.name ASC
        ";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $query .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)$filters['offset'];
        }

        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    public function countAkumulasiSiswa($filters = []) {
        $where = "1=1";
        $binds = [];
        if (!empty($filters['kelas_id'])) {
            $where .= " AND k.id = :kelas_id";
            $binds[':kelas_id'] = $filters['kelas_id'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (u.name LIKE :search OR s.nis LIKE :search)";
            $binds[':search'] = '%' . $filters['search'] . '%';
        }

        $this->db->query("
            SELECT COUNT(s.id) AS total
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            JOIN kelas k ON s.kelas_id = k.id
            WHERE $where
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    public function getAkumulasiSiswaAll($dari = '', $sampai = '', $extraFilters = []) {
        $dari   = !empty($dari) ? $dari : date('Y-m-01');
        $sampai = !empty($sampai) ? $sampai : date('Y-m-d');
        
        $where = "1=1";
        $binds = [':dari' => $dari, ':sampai' => $sampai];

        if (!empty($extraFilters['kelas_id'])) {
            $where .= " AND k.id = :kelas_id";
            $binds[':kelas_id'] = $extraFilters['kelas_id'];
        }
        if (!empty($extraFilters['search'])) {
            $where .= " AND (u.name LIKE :search OR s.nis LIKE :search)";
            $binds[':search'] = '%' . $extraFilters['search'] . '%';
        }

        $this->db->query("
            SELECT 
                s.id AS siswa_id,
                s.nis,
                u.name,
                k.nama_kelas,
                k.tingkat,
                k.jurusan,
                COUNT(ab.id) AS total_absen,
                SUM(CASE WHEN ab.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN ab.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN ab.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' THEN 1 ELSE 0 END) AS alfa,
                SUM(CASE WHEN ab.status = 'pkl' THEN 1 ELSE 0 END) AS pkl
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN absensi_siswa ab ON ab.siswa_id = s.id 
                AND ab.tanggal >= :dari AND ab.tanggal <= :sampai
            WHERE $where
            GROUP BY s.id, s.nis, u.name, k.nama_kelas, k.tingkat, k.jurusan
            ORDER BY k.nama_kelas ASC, u.name ASC
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    public function getAkumulasiGuru($filters = []) {
        $dari   = !empty($filters['dari']) ? $filters['dari'] : date('Y-m-01');
        $sampai = !empty($filters['sampai']) ? $filters['sampai'] : date('Y-m-d');
        
        $where = "u.role IN ('guru', 'wali_kelas', 'walikelas')";
        $binds = [':dari' => $dari, ':sampai' => $sampai];

        if (!empty($filters['search'])) {
            $where .= " AND (u.name LIKE :search OR g.nip LIKE :search)";
            $binds[':search'] = '%' . $filters['search'] . '%';
        }

        $query = "
            SELECT 
                g.id AS guru_id,
                g.nip,
                u.name,
                COUNT(ab.id) AS total_absen,
                SUM(CASE WHEN ab.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN ab.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN ab.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' THEN 1 ELSE 0 END) AS alfa
            FROM guru g
            JOIN users u ON g.user_id = u.id
            LEFT JOIN absensi_guru ab ON ab.guru_id = g.id 
                AND ab.tanggal >= :dari AND ab.tanggal <= :sampai
            WHERE $where
            GROUP BY g.id, g.nip, u.name
            ORDER BY u.name ASC
        ";

        if (isset($filters['limit']) && isset($filters['offset'])) {
            $query .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)$filters['offset'];
        }

        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    public function countAkumulasiGuru($filters = []) {
        $where = "u.role IN ('guru', 'wali_kelas', 'walikelas')";
        $binds = [];
        if (!empty($filters['search'])) {
            $where .= " AND (u.name LIKE :search OR g.nip LIKE :search)";
            $binds[':search'] = '%' . $filters['search'] . '%';
        }

        $this->db->query("
            SELECT COUNT(g.id) AS total
            FROM guru g
            JOIN users u ON g.user_id = u.id
            WHERE $where
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    public function getAkumulasiGuruAll($dari = '', $sampai = '', $extraFilters = []) {
        $dari   = !empty($dari) ? $dari : date('Y-m-01');
        $sampai = !empty($sampai) ? $sampai : date('Y-m-d');
        
        $where = "u.role IN ('guru', 'wali_kelas', 'walikelas')";
        $binds = [':dari' => $dari, ':sampai' => $sampai];

        if (!empty($extraFilters['search'])) {
            $where .= " AND (u.name LIKE :search OR g.nip LIKE :search)";
            $binds[':search'] = '%' . $extraFilters['search'] . '%';
        }

        $this->db->query("
            SELECT 
                g.id AS guru_id,
                g.nip,
                u.name,
                COUNT(ab.id) AS total_absen,
                SUM(CASE WHEN ab.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN ab.status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN ab.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN ab.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN ab.status = 'alfa' THEN 1 ELSE 0 END) AS alfa
            FROM guru g
            JOIN users u ON g.user_id = u.id
            LEFT JOIN absensi_guru ab ON ab.guru_id = g.id 
                AND ab.tanggal >= :dari AND ab.tanggal <= :sampai
            WHERE $where
            GROUP BY g.id, g.nip, u.name
            ORDER BY u.name ASC
        ");
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    public function getAkumulasiMapSiswa($dari, $sampai, $siswaIds = []) {
        if (empty($siswaIds)) return [];
        $idsStr = implode(',', array_map('intval', $siswaIds));
        $this->db->query("
            SELECT 
                siswa_id,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa,
                SUM(CASE WHEN status = 'pkl' THEN 1 ELSE 0 END) AS pkl
            FROM absensi_siswa
            WHERE tanggal >= :dari AND tanggal <= :sampai
              AND siswa_id IN ($idsStr)
            GROUP BY siswa_id
        ");
        $this->db->bind(':dari', $dari);
        $this->db->bind(':sampai', $sampai);
        $rows = $this->db->resultSet();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->siswa_id] = [
                'hadir'     => (int)$r->hadir,
                'terlambat' => (int)$r->terlambat,
                'izin'      => (int)$r->izin,
                'sakit'     => (int)$r->sakit,
                'alfa'      => (int)$r->alfa,
                'pkl'       => (int)$r->pkl,
            ];
        }
        return $map;
    }

    public function getAkumulasiMapGuru($dari, $sampai, $guruIds = []) {
        if (empty($guruIds)) return [];
        $idsStr = implode(',', array_map('intval', $guruIds));
        $this->db->query("
            SELECT 
                guru_id,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa
            FROM absensi_guru
            WHERE tanggal >= :dari AND tanggal <= :sampai
              AND guru_id IN ($idsStr)
            GROUP BY guru_id
        ");
        $this->db->bind(':dari', $dari);
        $this->db->bind(':sampai', $sampai);
        $rows = $this->db->resultSet();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->guru_id] = [
                'hadir'     => (int)$r->hadir,
                'terlambat' => (int)$r->terlambat,
                'izin'      => (int)$r->izin,
                'sakit'     => (int)$r->sakit,
                'alfa'      => (int)$r->alfa,
            ];
        }
        return $map;
    }
}

