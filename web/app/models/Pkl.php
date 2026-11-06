<?php
/**
 * Pkl.php
 * Model untuk manajemen lokasi PKL per kelompok, Guru Pembimbing PKL,
 * dan Presensi Mandiri Siswa PKL (Foto + Titik Lokasi GPS)
 */
class Pkl extends Model {

    /**
     * Ambil informasi detail siswa, kelas, status kelas PKL, telegram bot, dan nama wali kelas
     */
    public function getSiswaPklDetail($userId) {
        $this->db->query("
            SELECT s.*, u.name as nama, u.username, k.nama_kelas, k.status as status_kelas,
                   k.telegram_chat_id, k.telegram_chat_id_pkl, k.telegram_bot_token,
                   w.name as wali_nama,
                   gp.telegram_chat_id as pembimbing_telegram_chat_id
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN users w ON k.wali_kelas_id = w.id
            LEFT JOIN pkl_kelompok_siswa ks ON s.id = ks.siswa_id
            LEFT JOIN pkl_kelompok kel ON ks.kelompok_id = kel.id
            LEFT JOIN guru gp ON kel.pembimbing_guru_id = gp.id
            WHERE s.user_id = :uid
            LIMIT 1
        ");
        $this->db->bind(':uid', $userId);
        return $this->db->single();
    }

    /**
     * Ambil lokasi PKL siswa (berdasarkan kelompok PKL yang didaftarkan oleh guru pembimbing)
     * Fallback ke tabel siswa_pkl_lokasi jika ada data legacy
     */
    public function getLokasiBySiswaId($siswaId) {
        // 1. Cek dari kelompok PKL
        $this->db->query("
            SELECT 
                k.id as kelompok_id,
                k.kelas_id,
                k.nama_kelompok,
                k.nama_tempat,
                k.alamat,
                k.link_lokasi,
                k.latitude,
                k.longitude,
                k.radius_meter,
                k.pembimbing_nama,
                k.pembimbing_kontak,
                k.pembimbing_guru_id,
                k.hari_kerja,
                k.is_jam_bebas,
                ug.name as pembimbing_guru_nama
            FROM pkl_kelompok_siswa ks
            JOIN pkl_kelompok k ON ks.kelompok_id = k.id
            LEFT JOIN guru g ON k.pembimbing_guru_id = g.id
            LEFT JOIN users ug ON g.user_id = ug.id
            WHERE ks.siswa_id = :sid
            LIMIT 1
        ");
        $this->db->bind(':sid', $siswaId);
        $res = $this->db->single();

        if ($res) {
            return $res;
        }

        // 2. Fallback ke siswa_pkl_lokasi jika belum tergabung kelompok
        $this->db->query("SELECT *, nama_tempat as nama_kelompok, NULL as pembimbing_guru_nama FROM siswa_pkl_lokasi WHERE siswa_id = :sid LIMIT 1");
        $this->db->bind(':sid', $siswaId);
        return $this->db->single();
    }

    /**
     * Cek apakah user adalah Guru Pembimbing PKL
     */
    public function isUserPembimbingPkl($userId) {
        $userId = (int)$userId;
        if ($userId <= 0) return false;

        $this->db->query("
            SELECT g.id, g.is_pembimbing_pkl,
                   (SELECT COUNT(*) FROM pkl_pembimbing_kelas ppk WHERE ppk.guru_id = g.id) as cnt_kelas,
                   (SELECT COUNT(*) FROM pkl_kelompok pk WHERE pk.pembimbing_guru_id = g.id) as cnt_kelompok
            FROM guru g
            WHERE g.user_id = :uid
            LIMIT 1
        ");
        $this->db->bind(':uid', $userId);
        $row = $this->db->single();
        if (!$row) return false;
        return ((int)$row->is_pembimbing_pkl === 1 || (int)$row->cnt_kelas > 0 || (int)$row->cnt_kelompok > 0);
    }

    /**
     * Ambil data guru dari user_id
     */
    public function getGuruByUserId($userId) {
        $this->db->query("
            SELECT g.*, u.name, u.username 
            FROM guru g 
            JOIN users u ON g.user_id = u.id 
            WHERE g.user_id = :uid 
            LIMIT 1
        ");
        $this->db->bind(':uid', $userId);
        return $this->db->single();
    }

    /**
     * Ambil daftar kelas yang dibimbing oleh Guru Pembimbing PKL
     */
    public function getKelasByPembimbingUserId($userId) {
        $this->db->query("
            SELECT DISTINCT k.*, u.name as wali_nama,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa,
                   (SELECT COUNT(*) FROM pkl_kelompok pk WHERE pk.kelas_id = k.id) as total_kelompok
            FROM kelas k
            LEFT JOIN users u ON k.wali_kelas_id = u.id
            WHERE k.id IN (
                SELECT ppk.kelas_id 
                FROM pkl_pembimbing_kelas ppk
                JOIN guru g ON ppk.guru_id = g.id
                WHERE g.user_id = :uid1
            )
            OR k.id IN (
                SELECT pk2.kelas_id
                FROM pkl_kelompok pk2
                JOIN guru g2 ON pk2.pembimbing_guru_id = g2.id
                WHERE g2.user_id = :uid2
            )
            ORDER BY k.nama_kelas ASC
        ");
        $this->db->bind(':uid1', $userId);
        $this->db->bind(':uid2', $userId);
        $res = $this->db->resultSet();

        // Fallback jika guru tercatat sebagai pembimbing PKL aktif tapi belum diassign kelas spesifik
        if (empty($res)) {
            $guru = $this->getGuruByUserId($userId);
            if ($guru && !empty($guru->is_pembimbing_pkl)) {
                return $this->getAllKelasPkl();
            }
        }
        return $res;
    }

    /**
     * Ambil seluruh guru yang berstatus Pembimbing PKL
     */
    public function getAllPembimbing() {
        $this->db->query("
            SELECT g.id, g.user_id, u.name as guru_nama, g.nip
            FROM guru g
            JOIN users u ON g.user_id = u.id
            WHERE g.is_pembimbing_pkl = 1
            ORDER BY u.name ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Ambil data ringkasan eksekutif PKL untuk Admin Dashboard
     */
    public function getAdminPklDashboardData() {
        $today = date('Y-m-d');

        // 1. Total Kelas PKL
        $this->db->query("SELECT id, nama_kelas, jurusan, status FROM kelas WHERE status = 'pkl' ORDER BY nama_kelas ASC");
        $kelasList = $this->db->resultSet();
        $kelasIds = array_column($kelasList, 'id');
        $inKelas = !empty($kelasIds) ? implode(',', array_map('intval', $kelasIds)) : '0';

        // 2. Total Siswa PKL
        $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE kelas_id IN ($inKelas)");
        $rowSiswa = $this->db->single();
        $totalSiswa = (int)($rowSiswa->total ?? 0);

        // 3. Total Kelompok / DU-DI
        $this->db->query("SELECT COUNT(*) as total FROM pkl_kelompok");
        $rowKelompok = $this->db->single();
        $totalKelompok = (int)($rowKelompok->total ?? 0);

        // 4. Total Guru Pembimbing PKL
        $this->db->query("
            SELECT g.id, g.user_id, u.name as guru_nama, g.nip,
                   GROUP_CONCAT(DISTINCT k.nama_kelas ORDER BY k.nama_kelas SEPARATOR ', ') as bimbingan_kelas,
                   GROUP_CONCAT(DISTINCT k.jurusan ORDER BY k.jurusan SEPARATOR ', ') as bimbingan_jurusan,
                   (SELECT COUNT(DISTINCT s.id) FROM pkl_pembimbing_kelas ppk JOIN siswa s ON ppk.kelas_id = s.kelas_id WHERE ppk.guru_id = g.id) as total_siswa_bimbingan
            FROM guru g
            JOIN users u ON g.user_id = u.id
            LEFT JOIN pkl_pembimbing_kelas ppk ON g.id = ppk.guru_id
            LEFT JOIN kelas k ON ppk.kelas_id = k.id
            WHERE g.is_pembimbing_pkl = 1
            GROUP BY g.id
            ORDER BY u.name ASC
        ");
        $pembimbingList = $this->db->resultSet();
        $totalPembimbing = count($pembimbingList);

        // 5. Kehadiran Siswa PKL Hari Ini
        $this->db->query("
            SELECT 
                SUM(CASE WHEN a.jam_datang IS NOT NULL AND a.jam_datang != '' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN a.jam_pulang IS NOT NULL AND a.jam_pulang != '' THEN 1 ELSE 0 END) as pulang
            FROM siswa s
            JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal = :tgl
            WHERE s.kelas_id IN ($inKelas)
               OR a.metode = 'pkl'
               OR a.id_pkl_kelompok IS NOT NULL
        ");
        $this->db->bind(':tgl', $today);
        $rowAbsen = $this->db->single();

        $masuk = (int)($rowAbsen->masuk ?? 0);
        $pulang = (int)($rowAbsen->pulang ?? 0);
        $belum = max(0, $totalSiswa - $masuk);

        // 6. Log Riwayat Presensi PKL Terbaru (10 Log Terakhir)
        $this->db->query("
            SELECT 
                s.id as siswa_id,
                s.nis,
                u.name as nama_siswa,
                k.nama_kelas,
                pk.nama_kelompok,
                pk.nama_tempat,
                pk.radius_meter,
                ug.name as pembimbing_nama,
                a.tanggal,
                a.jam_datang,
                a.jam_pulang,
                a.status,
                a.foto_masuk,
                a.foto_pulang,
                a.jarak_meter,
                a.latitude_masuk,
                a.longitude_masuk,
                a.latitude_pulang,
                a.longitude_pulang
            FROM absensi_siswa a
            JOIN siswa s ON a.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN pkl_kelompok_siswa ks ON s.id = ks.siswa_id
            LEFT JOIN pkl_kelompok pk ON ks.kelompok_id = pk.id
            LEFT JOIN guru g ON pk.pembimbing_guru_id = g.id
            LEFT JOIN users ug ON g.user_id = ug.id
            WHERE (s.kelas_id IN ($inKelas) OR a.metode = 'pkl' OR a.id_pkl_kelompok IS NOT NULL OR a.foto_masuk IS NOT NULL)
            ORDER BY a.tanggal DESC, a.jam_datang DESC
            LIMIT 10
        ");
        $recentLogs = $this->db->resultSet();

        return [
            'kelasList'       => $kelasList,
            'totalSiswa'      => $totalSiswa,
            'totalKelompok'   => $totalKelompok,
            'totalPembimbing' => $totalPembimbing,
            'pembimbingList'  => $pembimbingList,
            'masuk'           => $masuk,
            'pulang'          => $pulang,
            'belum'           => $belum,
            'recentLogs'      => $recentLogs,
        ];
    }

    /**
     * Ambil data statistik dan log riwayat presensi PKL untuk Dashboard Guru Pembimbing
     */
    public function getDashboardPembimbingData($userId) {
        $userId = (int)$userId;
        $kelasList = $this->getKelasByPembimbingUserId($userId);
        if (empty($kelasList)) {
            return null;
        }

        $kelasIds = array_map(function($k) { return (int)$k->id; }, $kelasList);
        $inClause = implode(',', $kelasIds);
        $today = date('Y-m-d');

        // Total Siswa PKL Bimbingan
        $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE kelas_id IN ($inClause)");
        $rowSiswa = $this->db->single();
        $totalSiswa = (int)($rowSiswa->total ?? 0);

        // Total Kelompok / Tempat PKL
        $this->db->query("SELECT COUNT(*) as total FROM pkl_kelompok WHERE kelas_id IN ($inClause)");
        $rowKelompok = $this->db->single();
        $totalKelompok = (int)($rowKelompok->total ?? 0);

        // Kehadiran PKL Hari Ini
        $this->db->query("
            SELECT 
                COUNT(DISTINCT s.id) as total_siswa,
                SUM(CASE WHEN a.jam_datang IS NOT NULL AND a.jam_datang != '' THEN 1 ELSE 0 END) as masuk,
                SUM(CASE WHEN a.jam_pulang IS NOT NULL AND a.jam_pulang != '' THEN 1 ELSE 0 END) as pulang
            FROM siswa s
            LEFT JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal = :tgl
            WHERE s.kelas_id IN ($inClause)
        ");
        $this->db->bind(':tgl', $today);
        $rowAbsen = $this->db->single();

        $masuk = (int)($rowAbsen->masuk ?? 0);
        $pulang = (int)($rowAbsen->pulang ?? 0);
        $belum = max(0, $totalSiswa - $masuk);

        // Log Riwayat Presensi PKL Terbaru (Hari ini dan beberapa hari terakhir)
        $this->db->query("
            SELECT 
                s.id as siswa_id,
                s.nis,
                u.name as nama_siswa,
                k.nama_kelas,
                pk.nama_kelompok,
                pk.nama_tempat,
                pk.radius_meter,
                a.tanggal,
                a.jam_datang,
                a.jam_pulang,
                a.status,
                a.foto_masuk,
                a.foto_pulang,
                a.jarak_meter,
                a.latitude_masuk,
                a.longitude_masuk,
                a.latitude_pulang,
                a.longitude_pulang
            FROM absensi_siswa a
            JOIN siswa s ON a.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN pkl_kelompok_siswa ks ON s.id = ks.siswa_id
            LEFT JOIN pkl_kelompok pk ON ks.kelompok_id = pk.id
            WHERE s.kelas_id IN ($inClause)
            ORDER BY a.tanggal DESC, a.jam_datang DESC
            LIMIT 10
        ");
        $recentLogs = $this->db->resultSet();

        return [
            'is_pembimbing' => true,
            'kelasList'     => $kelasList,
            'total_siswa'   => $totalSiswa,
            'total_kelompok'=> $totalKelompok,
            'masuk'         => $masuk,
            'pulang'        => $pulang,
            'belum'         => $belum,
            'recentLogs'    => $recentLogs,
        ];
    }

    /**
     * Ambil daftar kelas yang diampu oleh wali kelas (legacy helper)
     */
    public function getKelasByWaliUserId($waliUserId) {
        $this->db->query("
            SELECT k.*, u.name as wali_nama,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
            FROM kelas k
            LEFT JOIN users u ON k.wali_kelas_id = u.id
            WHERE k.wali_kelas_id = :wuid
            LIMIT 1
        ");
        $this->db->bind(':wuid', $waliUserId);
        return $this->db->single();
    }

    /**
     * Ambil semua kelas berstatus PKL (untuk Admin atau dropdown)
     */
    public function getAllKelasPkl() {
        $this->db->query("
            SELECT k.*, u.name as wali_nama,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa,
                   (SELECT COUNT(*) FROM pkl_kelompok pk WHERE pk.kelas_id = k.id) as total_kelompok
            FROM kelas k
            LEFT JOIN users u ON k.wali_kelas_id = u.id
            WHERE k.status = 'pkl'
            ORDER BY k.nama_kelas ASC
        ");
        $res = $this->db->resultSet();
        // Fallback jika belum ada kelas berstatus 'pkl', tampilkan semua kelas
        if (empty($res)) {
            $this->db->query("
                SELECT k.*, u.name as wali_nama,
                       (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa,
                       (SELECT COUNT(*) FROM pkl_kelompok pk WHERE pk.kelas_id = k.id) as total_kelompok
                FROM kelas k
                LEFT JOIN users u ON k.wali_kelas_id = u.id
                ORDER BY k.nama_kelas ASC
            ");
            $res = $this->db->resultSet();
        }
        return $res;
    }

    /**
     * Ambil detail kelas berdasarkan ID
     */
    public function getKelasById($kelasId) {
        $this->db->query("
            SELECT k.*, u.name as wali_nama,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
            FROM kelas k
            LEFT JOIN users u ON k.wali_kelas_id = u.id
            WHERE k.id = :kid
            LIMIT 1
        ");
        $this->db->bind(':kid', $kelasId);
        return $this->db->single();
    }

    /**
     * Ambil seluruh kelompok PKL dalam satu kelas beserta daftar nama siswanya & guru pembimbing
     */
    public function getKelompokByKelasId($kelasId) {
        $this->db->query("
            SELECT k.*, 
                   COUNT(ks.siswa_id) as jumlah_siswa,
                   ug.name as pembimbing_guru_nama
            FROM pkl_kelompok k
            LEFT JOIN pkl_kelompok_siswa ks ON k.id = ks.kelompok_id
            LEFT JOIN guru g ON k.pembimbing_guru_id = g.id
            LEFT JOIN users ug ON g.user_id = ug.id
            WHERE k.kelas_id = :kid
            GROUP BY k.id
            ORDER BY k.id DESC
        ");
        $this->db->bind(':kid', $kelasId);
        $kelompokList = $this->db->resultSet();

        // Ambil daftar detail siswa untuk masing-masing kelompok
        foreach ($kelompokList as $kel) {
            $this->db->query("
                SELECT s.id as siswa_id, s.nis, u.name as nama
                FROM pkl_kelompok_siswa ks
                JOIN siswa s ON ks.siswa_id = s.id
                JOIN users u ON s.user_id = u.id
                WHERE ks.kelompok_id = :kel_id
                ORDER BY u.name ASC
            ");
            $this->db->bind(':kel_id', $kel->id);
            $kel->anggota = $this->db->resultSet();
        }

        return $kelompokList;
    }

    /**
     * Ambil single kelompok berdasarkan ID
     */
    public function getKelompokById($kelompokId) {
        $this->db->query("
            SELECT k.*, ug.name as pembimbing_guru_nama
            FROM pkl_kelompok k
            LEFT JOIN guru g ON k.pembimbing_guru_id = g.id
            LEFT JOIN users ug ON g.user_id = ug.id
            WHERE k.id = :id 
            LIMIT 1
        ");
        $this->db->bind(':id', $kelompokId);
        $kel = $this->db->single();

        if ($kel) {
            $this->db->query("
                SELECT s.id as siswa_id, s.nis, u.name as nama
                FROM pkl_kelompok_siswa ks
                JOIN siswa s ON ks.siswa_id = s.id
                JOIN users u ON s.user_id = u.id
                WHERE ks.kelompok_id = :id
                ORDER BY u.name ASC
            ");
            $this->db->bind(':id', $kelompokId);
            $kel->anggota = $this->db->resultSet();
            $kel->anggota_ids = array_map(function($a) { return (int)$a->siswa_id; }, $kel->anggota);
        }

        return $kel;
    }

    /**
     * Ambil seluruh siswa dalam kelas, beserta info apakah sudah ada kelompok atau belum
     */
    public function getSiswaByKelasId($kelasId) {
        $this->db->query("
            SELECT s.id as siswa_id, s.nis, u.name as nama,
                   ks.kelompok_id,
                   pk.nama_kelompok,
                   pk.nama_tempat
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN pkl_kelompok_siswa ks ON s.id = ks.siswa_id
            LEFT JOIN pkl_kelompok pk ON ks.kelompok_id = pk.id
            WHERE s.kelas_id = :kid
            ORDER BY u.name ASC
        ");
        $this->db->bind(':kid', $kelasId);
        return $this->db->resultSet();
    }

    /**
     * Simpan / Perbarui Kelompok PKL sekaligus masukkan siswa anggotanya secara grup (per kelompok)
     * Terhubung dengan Guru Pembimbing PKL
     */
    public function saveKelompok($data, $siswaIds = []) {
        $id               = !empty($data['id']) ? (int)$data['id'] : null;
        $kelasId          = (int)$data['kelas_id'];
        $namaKelompok     = trim($data['nama_kelompok']);
        $namaTempat       = trim($data['nama_tempat']);
        $alamat           = trim($data['alamat'] ?? '');
        $linkLokasi       = trim($data['link_lokasi'] ?? '');
        $lat              = (float)$data['latitude'];
        $lng              = (float)$data['longitude'];
        $radius           = !empty($data['radius_meter']) ? (int)$data['radius_meter'] : 50;
        $pembimbingNama   = trim($data['pembimbing_nama'] ?? '');
        $pembimbingKontak = trim($data['pembimbing_kontak'] ?? '');
        $pembimbingGuruId = !empty($data['pembimbing_guru_id']) ? (int)$data['pembimbing_guru_id'] : null;
        $hariKerja        = !empty($data['hari_kerja']) ? $data['hari_kerja'] : '["Senin","Selasa","Rabu","Kamis","Jumat"]';
        if (is_array($hariKerja)) {
            $hariKerja = json_encode($hariKerja);
        }
        $isJamBebas       = isset($data['is_jam_bebas']) ? (int)$data['is_jam_bebas'] : 1;
        $createdBy        = !empty($data['created_by']) ? (int)$data['created_by'] : null;

        // Radius minimal 10m, default 50m jika tidak diisi
        if ($radius < 10) $radius = 10;

        // Jika pembimbing_guru_id belum diset, coba cari dari pkl_pembimbing_kelas
        if (!$pembimbingGuruId) {
            $this->db->query("SELECT guru_id FROM pkl_pembimbing_kelas WHERE kelas_id = :kid LIMIT 1");
            $this->db->bind(':kid', $kelasId);
            $pRow = $this->db->single();
            if ($pRow) {
                $pembimbingGuruId = (int)$pRow->guru_id;
            }
        }

        if ($id) {
            // Update
            $this->db->query("
                UPDATE pkl_kelompok
                SET nama_kelompok      = :nama_kelompok,
                    nama_tempat        = :nama_tempat,
                    alamat             = :alamat,
                    link_lokasi        = :link_lokasi,
                    latitude           = :lat,
                    longitude          = :lng,
                    radius_meter       = :radius,
                    pembimbing_nama    = :pnama,
                    pembimbing_kontak  = :pkontak,
                    pembimbing_guru_id = :pguru_id,
                    hari_kerja         = :hari_kerja,
                    is_jam_bebas       = :jam_bebas,
                    updated_at         = NOW()
                WHERE id = :id AND kelas_id = :kid
            ");
            $this->db->bind(':id', $id);
            $this->db->bind(':kid', $kelasId);
            $this->db->bind(':nama_kelompok', $namaKelompok);
            $this->db->bind(':nama_tempat', $namaTempat);
            $this->db->bind(':alamat', $alamat);
            $this->db->bind(':link_lokasi', $linkLokasi);
            $this->db->bind(':lat', $lat);
            $this->db->bind(':lng', $lng);
            $this->db->bind(':radius', $radius);
            $this->db->bind(':pnama', $pembimbingNama);
            $this->db->bind(':pkontak', $pembimbingKontak);
            $this->db->bind(':pguru_id', $pembimbingGuruId);
            $this->db->bind(':hari_kerja', $hariKerja);
            $this->db->bind(':jam_bebas', $isJamBebas);
            $this->db->execute();
            $kelompokId = $id;
        } else {
            // Insert
            $this->db->query("
                INSERT INTO pkl_kelompok 
                    (kelas_id, nama_kelompok, nama_tempat, alamat, link_lokasi, latitude, longitude, radius_meter, pembimbing_nama, pembimbing_kontak, pembimbing_guru_id, hari_kerja, is_jam_bebas, created_by)
                VALUES 
                    (:kid, :nama_kelompok, :nama_tempat, :alamat, :link_lokasi, :lat, :lng, :radius, :pnama, :pkontak, :pguru_id, :hari_kerja, :jam_bebas, :created_by)
            ");
            $this->db->bind(':kid', $kelasId);
            $this->db->bind(':nama_kelompok', $namaKelompok);
            $this->db->bind(':nama_tempat', $namaTempat);
            $this->db->bind(':alamat', $alamat);
            $this->db->bind(':link_lokasi', $linkLokasi);
            $this->db->bind(':lat', $lat);
            $this->db->bind(':lng', $lng);
            $this->db->bind(':radius', $radius);
            $this->db->bind(':pnama', $pembimbingNama);
            $this->db->bind(':pkontak', $pembimbingKontak);
            $this->db->bind(':pguru_id', $pembimbingGuruId);
            $this->db->bind(':hari_kerja', $hariKerja);
            $this->db->bind(':jam_bebas', $isJamBebas);
            $this->db->bind(':created_by', $createdBy);
            $this->db->execute();
            $kelompokId = (int)$this->db->lastInsertId();
        }

        if (!$kelompokId) {
            return false;
        }

        // 2. Sync Anggota Siswa (Hapus anggota lama yang dilepas dari kelompok ini)
        $this->db->query("DELETE FROM pkl_kelompok_siswa WHERE kelompok_id = :kel_id");
        $this->db->bind(':kel_id', $kelompokId);
        $this->db->execute();

        // 3. Masukkan anggota baru & sinkronkan ke siswa_pkl_lokasi
        if (!empty($siswaIds) && is_array($siswaIds)) {
            foreach ($siswaIds as $sid) {
                $sid = (int)$sid;
                if ($sid <= 0) continue;

                // Pastikan siswa dilepas dari kelompok lain jika ada
                $this->db->query("DELETE FROM pkl_kelompok_siswa WHERE siswa_id = :sid");
                $this->db->bind(':sid', $sid);
                $this->db->execute();

                // Masukkan ke kelompok ini
                $this->db->query("INSERT INTO pkl_kelompok_siswa (kelompok_id, siswa_id) VALUES (:kel_id, :sid)");
                $this->db->bind(':kel_id', $kelompokId);
                $this->db->bind(':sid', $sid);
                $this->db->execute();

                // Sinkronkan ke tabel legacy siswa_pkl_lokasi
                $this->saveLokasiLegacy($sid, $namaTempat, $alamat, $linkLokasi, $lat, $lng, $radius);
            }
        }

        return $kelompokId;
    }

    /**
     * Hapus Kelompok PKL
     */
    public function deleteKelompok($kelompokId) {
        $kelompokId = (int)$kelompokId;

        // Ambil daftar siswa untuk menghapus cache legacy
        $this->db->query("SELECT siswa_id FROM pkl_kelompok_siswa WHERE kelompok_id = :id");
        $this->db->bind(':id', $kelompokId);
        $members = $this->db->resultSet();

        foreach ($members as $m) {
            $this->db->query("DELETE FROM siswa_pkl_lokasi WHERE siswa_id = :sid");
            $this->db->bind(':sid', $m->siswa_id);
            $this->db->execute();
        }

        $this->db->query("DELETE FROM pkl_kelompok WHERE id = :id");
        $this->db->bind(':id', $kelompokId);
        return $this->db->execute();
    }

    /**
     * Simpan / sync ke tabel legacy siswa_pkl_lokasi
     */
    public function saveLokasiLegacy($siswaId, $namaTempat, $alamat, $linkLokasi, $lat, $lng, $radius = 50) {
        $this->db->query("
            INSERT INTO siswa_pkl_lokasi (siswa_id, nama_tempat, alamat, link_lokasi, latitude, longitude, radius_meter)
            VALUES (:sid, :nama, :alamat, :link, :lat, :lng, :radius)
            ON DUPLICATE KEY UPDATE
                nama_tempat = VALUES(nama_tempat),
                alamat = VALUES(alamat),
                link_lokasi = VALUES(link_lokasi),
                latitude = VALUES(latitude),
                longitude = VALUES(longitude),
                radius_meter = VALUES(radius_meter),
                updated_at = NOW()
        ");
        $this->db->bind(':sid', $siswaId);
        $this->db->bind(':nama', $namaTempat);
        $this->db->bind(':alamat', $alamat);
        $this->db->bind(':link', $linkLokasi);
        $this->db->bind(':lat', $lat);
        $this->db->bind(':lng', $lng);
        $this->db->bind(':radius', $radius);
        return $this->db->execute();
    }

    /**
     * Ambil data monitoring presensi PKL siswa per kelas untuk hari tertentu
     */
    public function getMonitoringPresensi($kelasId, $tanggal = null) {
        $tanggal = $tanggal ?? date('Y-m-d');
        $this->db->query("
            SELECT 
                s.id as siswa_id,
                s.nis,
                u.name as nama_siswa,
                pk.nama_kelompok,
                pk.nama_tempat,
                pk.radius_meter,
                ug.name as pembimbing_guru_nama,
                a.id as absensi_id,
                a.jam_datang,
                a.jam_pulang,
                a.status,
                a.foto_masuk,
                a.foto_pulang,
                a.jarak_meter,
                a.latitude_masuk,
                a.longitude_masuk,
                a.latitude_pulang,
                a.longitude_pulang
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN pkl_kelompok_siswa ks ON s.id = ks.siswa_id
            LEFT JOIN pkl_kelompok pk ON ks.kelompok_id = pk.id
            LEFT JOIN guru g ON pk.pembimbing_guru_id = g.id
            LEFT JOIN users ug ON g.user_id = ug.id
            LEFT JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal = :tgl
            WHERE s.kelas_id = :kid
            ORDER BY pk.nama_kelompok ASC, u.name ASC
        ");
        $this->db->bind(':kid', $kelasId);
        $this->db->bind(':tgl', $tanggal);
        return $this->db->resultSet();
    }

    /**
     * Ambil catatan absensi siswa hari ini
     */
    public function getAbsensiToday($siswaId, $tanggal = null) {
        $tanggal = $tanggal ?? date('Y-m-d');
        $this->db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :tgl LIMIT 1");
        $this->db->bind(':sid', $siswaId);
        $this->db->bind(':tgl', $tanggal);
        return $this->db->single();
    }

    /**
     * Hitung jarak dua titik koordinat (Haversine Formula) dalam satuan meter
     */
    public static function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad((float)$lat2 - (float)$lat1);
        $dLng = deg2rad((float)$lng2 - (float)$lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 1);
    }

    /**
     * Parse koordinat lat & lng dari link Google Maps atau string koordinat
     */
    public static function parseCoordinates($input) {
        $input = trim((string)$input);
        if (empty($input)) return null;

        // Format direct -6.123456, 106.123456
        if (preg_match('/([-+]?\d{1,2}\.\d+)[,\s]+([-+]?\d{1,3}\.\d+)/', $input, $m)) {
            return [
                'lat' => (float)$m[1],
                'lng' => (float)$m[2]
            ];
        }

        // Format URL @-6.123456,106.123456
        if (preg_match('/@([-+]?\d{1,2}\.\d+),([-+]?\d{1,3}\.\d+)/', $input, $m)) {
            return [
                'lat' => (float)$m[1],
                'lng' => (float)$m[2]
            ];
        }

        // Parameter ?q=-6.123456,106.123456
        if (preg_match('/[?&]q=([-+]?\d{1,2}\.\d+),([-+]?\d{1,3}\.\d+)/', $input, $m)) {
            return [
                'lat' => (float)$m[1],
                'lng' => (float)$m[2]
            ];
        }

        return null;
    }

    /**
     * Catat presensi masuk PKL
     */
    public function catatAbsenMasuk($siswaId, $tanggal, $jam, $foto, $lat, $lng, $jarak) {
        $this->db->query("
            INSERT INTO absensi_siswa 
                (siswa_id, tanggal, jam_datang, status, keterangan, foto_masuk, latitude_masuk, longitude_masuk, jarak_meter)
            VALUES 
                (:sid, :tgl, :jam, 'pkl', 'Presensi Masuk PKL Mandiri', :foto, :lat, :lng, :jarak)
        ");
        $this->db->bind(':sid', $siswaId);
        $this->db->bind(':tgl', $tanggal);
        $this->db->bind(':jam', $jam);
        $this->db->bind(':foto', $foto);
        $this->db->bind(':lat', $lat);
        $this->db->bind(':lng', $lng);
        $this->db->bind(':jarak', $jarak);
        return $this->db->execute();
    }

    /**
     * Catat presensi pulang PKL
     */
    public function catatAbsenPulang($absensiId, $jam, $foto, $lat, $lng, $jarak) {
        $this->db->query("
            UPDATE absensi_siswa 
            SET 
                jam_pulang = :jam,
                foto_pulang = :foto,
                latitude_pulang = :lat,
                longitude_pulang = :lng,
                jarak_meter = :jarak,
                status = 'pkl'
            WHERE id = :id
        ");
        $this->db->bind(':jam', $jam);
        $this->db->bind(':foto', $foto);
        $this->db->bind(':lat', $lat);
        $this->db->bind(':lng', $lng);
        $this->db->bind(':jarak', $jarak);
        $this->db->bind(':id', $absensiId);
        return $this->db->execute();
    }
}
