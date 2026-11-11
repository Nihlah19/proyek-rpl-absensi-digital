<?php
class Absensi extends Model {

    /**
     * Helper: Konversi nama hari Inggris ke Indonesia.
     */
    public function getHariIndo($date = null) {
        $hariMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $dayName = $date ? date('l', strtotime($date)) : date('l');
        return $hariMap[$dayName] ?? 'Senin';
    }

    /**
     * Jadwal siswa hari ini (global, dari tabel jadwal_siswa).
     */
    public function getJadwalHariIni() {
        $this->db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1");
        $this->db->bind(':hari', $this->getHariIndo());
        return $this->db->single();
    }

    /**
     * Jadwal guru hari ini (spesifik per guru, dari tabel jadwal_guru).
     * Mengembalikan null jika guru tidak punya jadwal aktif hari ini
     * → artinya guru TIDAK wajib absen, TIDAK dianggap alfa.
     */
    public function getJadwalGuruHariIni($guru_id) {
        $this->db->query("SELECT * FROM jadwal_guru WHERE guru_id = :guru_id AND hari = :hari AND is_active = 1");
        $this->db->bind(':guru_id', $guru_id);
        $this->db->bind(':hari', $this->getHariIndo());
        return $this->db->single();
    }

    // STUDENT
    public function getSiswaByRFID($rfid) {
        $this->db->query("
            SELECT s.id, s.user_id, s.nis, s.kelas_id, s.no_hp_ortu, k.wali_kelas_id, u.name, k.nama_kelas, k.class_code, k.telegram_chat_id, k.telegram_bot_token, r.status as card_status
            FROM rfid_cards r
            JOIN siswa s ON s.id = r.student_id
            JOIN users u ON u.id = s.user_id
            LEFT JOIN kelas k ON k.id = s.kelas_id
            WHERE r.uid = :rfid AND r.status = 'active'
        ");
        $this->db->bind(':rfid', $rfid);
        return $this->db->single();
    }

    public function cekAbsensiSiswa($siswa_id, $tanggal) {
        $this->db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
        $this->db->bind(':siswa_id', $siswa_id);
        $this->db->bind(':tanggal', $tanggal);
        return $this->db->single();
    }

    public function insertAbsensiSiswa($data) {
        $this->db->query("INSERT INTO absensi_siswa (siswa_id, tanggal, jam_datang, jam_pulang, status, keterangan, source_device_id) VALUES (:siswa_id, :tanggal, :jam_datang, :jam_pulang, :status, :keterangan, :source_device_id)");
        $this->db->bind(':siswa_id', $data['siswa_id']);
        $this->db->bind(':tanggal', $data['tanggal']);
        $this->db->bind(':jam_datang', $data['jam_datang']);
        $this->db->bind(':jam_pulang', $data['jam_pulang'] ?? null);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':keterangan', $data['keterangan'] ?? null);
        $this->db->bind(':source_device_id', $data['source_device_id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateAbsensiPulangSiswa($id, $jam_pulang) {
        $this->db->query("UPDATE absensi_siswa SET jam_pulang = :jam_pulang WHERE id = :id");
        $this->db->bind(':jam_pulang', $jam_pulang);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // TEACHER
    public function getGuruByBarcode($barcode) {
        $clean = trim((string)$barcode);
        $this->db->query("SELECT g.*, u.name as nama, u.name, u.role 
                          FROM guru g 
                          JOIN users u ON g.user_id = u.id 
                          WHERE g.barcode_id = :barcode 
                             OR (g.nip IS NOT NULL AND g.nip != '' AND g.nip = :barcode_nip)
                          LIMIT 1");
        $this->db->bind(':barcode', $clean);
        $this->db->bind(':barcode_nip', $clean);
        return $this->db->single();
    }

    public function cekAbsensiGuru($guru_id, $tanggal) {
        $this->db->query("SELECT * FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = :tanggal");
        $this->db->bind(':guru_id', $guru_id);
        $this->db->bind(':tanggal', $tanggal);
        return $this->db->single();
    }

    public function insertAbsensiGuru($data) {
        $this->db->query("INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, jam_pulang, status, keterangan, source_device_id) VALUES (:guru_id, :tanggal, :jam_datang, :jam_pulang, :status, :keterangan, :source_device_id)");
        $this->db->bind(':guru_id', $data['guru_id']);
        $this->db->bind(':tanggal', $data['tanggal']);
        $this->db->bind(':jam_datang', $data['jam_datang']);
        $this->db->bind(':jam_pulang', $data['jam_pulang'] ?? null);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':keterangan', $data['keterangan'] ?? null);
        $this->db->bind(':source_device_id', $data['source_device_id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateAbsensiPulangGuru($id, $jam_pulang) {
        $this->db->query("UPDATE absensi_guru SET jam_pulang = :jam_pulang WHERE id = :id");
        $this->db->bind(':jam_pulang', $jam_pulang);
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Generate data absensi otomatis untuk siswa yang belum hadir hari ini (Auto-Alfa / Sync Izin).
     * Dapat dijalankan oleh admin via tombol di dashboard atau via scheduler/cron job harian.
     * 
     * Aturan:
     * 1. Cek apakah hari ini ada jadwal sekolah aktif (jadwal_siswa). Jika libur, tidak dijalankan.
     * 2. Ambil seluruh siswa aktif yang BELUM memiliki catatan di absensi_siswa untuk tanggal tersebut.
     * 3. Jika siswa memiliki pengajuan izin/sakit yang sudah disetujui (approved) pada tanggal tersebut,
     *    masukkan status 'izin' atau 'sakit' sesuai jenis pengajuannya.
     * 4. Jika tidak ada izin disetujui, masukkan status 'alfa' dengan keterangan 'Tidak hadir tanpa keterangan (Otomatis)'.
     */
    /**
     * Tentukan apakah batas waktu auto-alfa untuk tanggal target sudah tercapai.
     * Hari sebelum hari ini selalu langsung diproses; hari depan tidak diproses.
     */
    private function isCutoffPassed($tanggal, $batasTerlambat = null, $jamPulang = null, $respectCutoff = false) {
        $today = date('Y-m-d');
        if ($tanggal > $today) return false;
        if ($tanggal < $today || !$respectCutoff) return true;

        $candidates = [];
        foreach ([$batasTerlambat, $jamPulang] as $t) {
            if (!empty($t) && $t !== '00:00:00') {
                $candidates[] = $t;
            }
        }
        if (empty($candidates)) return true;

        $cutoff = min($candidates);
        return date('H:i:s') >= $cutoff;
    }

    /**
     * Sinkronisasi placeholder alfa -> izin/sakit jika pengajuan izin telah disetujui pada tanggal terkait.
     */
    private function syncApprovedIzinOntoAlfa($table, $fkCol, $roleTable, $dari, $sampai = null) {
        $sampai = $sampai ?: $dari;
        $this->db->query("
            UPDATE {$table} a
            JOIN {$roleTable} r ON r.id = a.{$fkCol}
            JOIN pengajuan_izin pi ON pi.user_id = r.user_id
                AND pi.status = 'approved'
                AND a.tanggal BETWEEN pi.tanggal_mulai AND pi.tanggal_selesai
            SET a.status = CASE WHEN LOWER(pi.jenis) = 'sakit' THEN 'sakit' ELSE 'izin' END,
                a.keterangan = CASE
                    WHEN pi.alasan IS NOT NULL AND TRIM(pi.alasan) != '' THEN CONCAT('Izin disetujui: ', pi.alasan)
                    ELSE 'Izin disetujui'
                END
            WHERE a.tanggal BETWEEN :d1 AND :d2
              AND a.status = 'alfa'
              AND a.jam_datang IS NULL
              AND a.jam_pulang IS NULL
        ");
        $this->db->bind(':d1', $dari);
        $this->db->bind(':d2', $sampai);
        $this->db->execute();
    }

    /**
     * Insert massal absensi izin/sakit/alfa untuk siswa reguler (non-PKL) pada tanggal tertentu.
     */
    private function bulkInsertAlfaSiswaDate($tanggal) {
        $this->syncApprovedIzinOntoAlfa('absensi_siswa', 'siswa_id', 'siswa', $tanggal, $tanggal);

        $this->db->query("
            INSERT INTO absensi_siswa (siswa_id, tanggal, jam_datang, jam_pulang, status, keterangan)
            SELECT
                s.id,
                :tgl1,
                NULL,
                NULL,
                CASE
                    WHEN MAX(LOWER(pi.jenis)) = 'sakit' THEN 'sakit'
                    WHEN MAX(pi.id) IS NOT NULL THEN 'izin'
                    ELSE 'alfa'
                END AS status_auto,
                CASE
                    WHEN MAX(pi.id) IS NOT NULL THEN
                        COALESCE(NULLIF(CONCAT('Izin disetujui: ', COALESCE(MAX(pi.alasan), '')), 'Izin disetujui: '), 'Izin disetujui')
                    ELSE 'Tidak melakukan presensi (tanpa scan RFID / izin)'
                END AS ket_auto
            FROM siswa s
            JOIN users u ON u.id = s.user_id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN pkl_kelompok_siswa pks ON pks.siswa_id = s.id
            LEFT JOIN absensi_siswa a ON a.siswa_id = s.id AND a.tanggal = :tgl2
            LEFT JOIN pengajuan_izin pi ON pi.user_id = s.user_id
                AND pi.status = 'approved'
                AND :tgl3 BETWEEN pi.tanggal_mulai AND pi.tanggal_selesai
            WHERE (k.status IS NULL OR LOWER(TRIM(k.status)) != 'pkl')
              AND pks.id IS NULL
              AND a.id IS NULL
            GROUP BY s.id
        ");
        $this->db->bind(':tgl1', $tanggal);
        $this->db->bind(':tgl2', $tanggal);
        $this->db->bind(':tgl3', $tanggal);
        $this->db->execute();
    }

    /**
     * Insert massal absensi izin/sakit/alfa untuk guru berjadwal aktif pada tanggal tertentu.
     */
    private function bulkInsertAlfaGuruDate($tanggal, $hariIndo, $useFallbackAllGuru = false, $respectCutoff = false) {
        $this->syncApprovedIzinOntoAlfa('absensi_guru', 'guru_id', 'guru', $tanggal, $tanggal);

        $isToday = ($tanggal === date('Y-m-d'));
        $nowTime = date('H:i:s');

        if ($useFallbackAllGuru) {
            $this->db->query("
                INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, jam_pulang, status, keterangan)
                SELECT
                    g.id,
                    :tgl1,
                    NULL,
                    NULL,
                    CASE
                        WHEN MAX(LOWER(pi.jenis)) = 'sakit' THEN 'sakit'
                        WHEN MAX(pi.id) IS NOT NULL THEN 'izin'
                        ELSE 'alfa'
                    END AS status_auto,
                    CASE
                        WHEN MAX(pi.id) IS NOT NULL THEN
                            COALESCE(NULLIF(CONCAT('Izin disetujui: ', COALESCE(MAX(pi.alasan), '')), 'Izin disetujui: '), 'Izin disetujui')
                        ELSE 'Tidak melakukan presensi (tanpa scan QR / izin)'
                    END AS ket_auto
                FROM guru g
                JOIN users u ON u.id = g.user_id
                LEFT JOIN absensi_guru a ON a.guru_id = g.id AND a.tanggal = :tgl2
                LEFT JOIN pengajuan_izin pi ON pi.user_id = g.user_id
                    AND pi.status = 'approved'
                    AND :tgl3 BETWEEN pi.tanggal_mulai AND pi.tanggal_selesai
                WHERE u.role IN ('guru', 'wali_kelas', 'walikelas')
                  AND a.id IS NULL
                GROUP BY g.id
            ");
            $this->db->bind(':tgl1', $tanggal);
            $this->db->bind(':tgl2', $tanggal);
            $this->db->bind(':tgl3', $tanggal);
            $this->db->execute();
            return;
        }

        $cutoffClause = ($isToday && $respectCutoff)
            ? " AND :now_time >= LEAST(COALESCE(NULLIF(j.batas_terlambat, '00:00:00'), '23:59:59'), COALESCE(NULLIF(j.jam_pulang, '00:00:00'), '23:59:59')) "
            : "";

        $this->db->query("
            INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, jam_pulang, status, keterangan)
            SELECT
                g.id,
                :tgl1,
                NULL,
                NULL,
                CASE
                    WHEN MAX(LOWER(pi.jenis)) = 'sakit' THEN 'sakit'
                    WHEN MAX(pi.id) IS NOT NULL THEN 'izin'
                    ELSE 'alfa'
                END AS status_auto,
                CASE
                    WHEN MAX(pi.id) IS NOT NULL THEN
                        COALESCE(NULLIF(CONCAT('Izin disetujui: ', COALESCE(MAX(pi.alasan), '')), 'Izin disetujui: '), 'Izin disetujui')
                    ELSE 'Tidak melakukan presensi (tanpa scan QR / izin)'
                END AS ket_auto
            FROM jadwal_guru j
            JOIN guru g ON g.id = j.guru_id
            JOIN users u ON u.id = g.user_id
            LEFT JOIN absensi_guru a ON a.guru_id = g.id AND a.tanggal = :tgl2
            LEFT JOIN pengajuan_izin pi ON pi.user_id = g.user_id
                AND pi.status = 'approved'
                AND :tgl3 BETWEEN pi.tanggal_mulai AND pi.tanggal_selesai
            WHERE j.hari = :hari
              AND j.is_active = 1
              AND a.id IS NULL
              {$cutoffClause}
            GROUP BY g.id
        ");
        $this->db->bind(':tgl1', $tanggal);
        $this->db->bind(':tgl2', $tanggal);
        $this->db->bind(':tgl3', $tanggal);
        $this->db->bind(':hari', $hariIndo);
        if ($isToday && $respectCutoff) {
            $this->db->bind(':now_time', $nowTime);
        }
        $this->db->execute();
    }

    /**
     * Generate data absensi Alfa & Izin untuk siswa reguler (non-PKL) yang belum memiliki catatan presensi.
     * Siswa PKL dibebaskan dari aturan auto-alfa sekolah karena jadwal mengikuti kondisi industri masing-masing.
     */
    public function generateAlfaSiswaHariIni($tanggal = null, $respectCutoff = false) {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        if ($tanggal > date('Y-m-d')) {
            return [
                'success' => false,
                'message' => 'Tanggal belum berlangsung.',
                'total_alfa' => 0,
                'total_izin' => 0
            ];
        }

        $hariIndo = $this->getHariIndo($tanggal);
        $this->db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1 LIMIT 1");
        $this->db->bind(':hari', $hariIndo);
        $jadwal = $this->db->single();
        if (!$jadwal) {
            $this->db->query("SELECT COUNT(*) as c FROM absensi_siswa WHERE tanggal = :tgl");
            $this->db->bind(':tgl', $tanggal);
            $hasAtt = (int)($this->db->single()->c ?? 0);
            if ($hasAtt === 0) {
                return [
                    'success' => false,
                    'message' => "Hari ini ($hariIndo) bukan hari sekolah aktif / libur. Tidak ada absensi yang di-generate.",
                    'total_alfa' => 0,
                    'total_izin' => 0
                ];
            }
        }

        if ($jadwal && !$this->isCutoffPassed($tanggal, $jadwal->batas_terlambat ?? null, $jadwal->jam_pulang ?? null, $respectCutoff)) {
            return [
                'success' => true,
                'message' => 'Batas waktu presensi siswa hari ini belum berakhir.',
                'total_alfa' => 0,
                'total_izin' => 0
            ];
        }

        $this->db->query("
            SELECT
                SUM(CASE WHEN a.status = 'alfa' THEN 1 ELSE 0 END) AS alfa_cnt,
                SUM(CASE WHEN a.status IN ('izin','sakit') THEN 1 ELSE 0 END) AS izin_cnt
            FROM absensi_siswa a
            JOIN siswa s ON s.id = a.siswa_id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN pkl_kelompok_siswa pks ON pks.siswa_id = s.id
            WHERE a.tanggal = :tgl
              AND (k.status IS NULL OR LOWER(TRIM(k.status)) != 'pkl')
              AND pks.id IS NULL
        ");
        $this->db->bind(':tgl', $tanggal);
        $before = $this->db->single();

        $this->bulkInsertAlfaSiswaDate($tanggal);

        $this->db->query("
            SELECT
                SUM(CASE WHEN a.status = 'alfa' THEN 1 ELSE 0 END) AS alfa_cnt,
                SUM(CASE WHEN a.status IN ('izin','sakit') THEN 1 ELSE 0 END) AS izin_cnt
            FROM absensi_siswa a
            JOIN siswa s ON s.id = a.siswa_id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN pkl_kelompok_siswa pks ON pks.siswa_id = s.id
            WHERE a.tanggal = :tgl
              AND (k.status IS NULL OR LOWER(TRIM(k.status)) != 'pkl')
              AND pks.id IS NULL
        ");
        $this->db->bind(':tgl', $tanggal);
        $after = $this->db->single();

        $countAlfa = max(0, (int)($after->alfa_cnt ?? 0) - (int)($before->alfa_cnt ?? 0));
        $countIzin = max(0, (int)($after->izin_cnt ?? 0) - (int)($before->izin_cnt ?? 0));

        if ($countAlfa === 0 && $countIzin === 0) {
            return [
                'success' => true,
                'message' => 'Semua siswa reguler sudah memiliki catatan absensi.',
                'total_alfa' => 0,
                'total_izin' => 0
            ];
        }

        return [
            'success' => true,
            'message' => "Berhasil memproses absensi siswa reguler: {$countAlfa} siswa tercatat Alfa (tanpa scan RFID/izin), {$countIzin} siswa tercatat Izin/Sakit.",
            'total_alfa' => $countAlfa,
            'total_izin' => $countIzin
        ];
    }

    /**
     * Generate data absensi otomatis untuk guru yang memiliki jadwal dinas/mengajar aktif hari ini
     * tetapi belum scan QR hingga jam kedatangan/kepulangan berakhir (Auto-Alfa / Sync Izin Guru).
     */
    public function generateAlfaGuruHariIni($tanggal = null, $respectCutoff = false) {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        if ($tanggal > date('Y-m-d')) {
            return [
                'success'    => false,
                'message'    => 'Tanggal belum berlangsung.',
                'total_alfa' => 0,
                'total_izin' => 0
            ];
        }

        $hariIndo = $this->getHariIndo($tanggal);

        // Cek apakah ada jadwal guru aktif untuk hari ini
        $this->db->query("SELECT COUNT(*) as c FROM jadwal_guru WHERE hari = :hari AND is_active = 1");
        $this->db->bind(':hari', $hariIndo);
        $countJadwalHari = (int)($this->db->single()->c ?? 0);

        $useFallback = false;
        if ($countJadwalHari === 0) {
            $this->db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1 LIMIT 1");
            $this->db->bind(':hari', $hariIndo);
            $jadwalSiswa = $this->db->single();
            if (!$jadwalSiswa) {
                return [
                    'success'    => false,
                    'message'    => "Hari ini ($hariIndo) tidak memiliki jadwal dinas guru aktif.",
                    'total_alfa' => 0,
                    'total_izin' => 0
                ];
            }
            if (!$this->isCutoffPassed($tanggal, $jadwalSiswa->batas_terlambat ?? null, $jadwalSiswa->jam_pulang ?? null, $respectCutoff)) {
                return [
                    'success'    => true,
                    'message'    => 'Batas waktu presensi guru hari ini belum berakhir.',
                    'total_alfa' => 0,
                    'total_izin' => 0
                ];
            }
            $useFallback = true;
        }

        $this->db->query("
            SELECT
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa_cnt,
                SUM(CASE WHEN status IN ('izin','sakit') THEN 1 ELSE 0 END) AS izin_cnt
            FROM absensi_guru
            WHERE tanggal = :tgl
        ");
        $this->db->bind(':tgl', $tanggal);
        $before = $this->db->single();

        $this->bulkInsertAlfaGuruDate($tanggal, $hariIndo, $useFallback, $respectCutoff);

        $this->db->query("
            SELECT
                SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) AS alfa_cnt,
                SUM(CASE WHEN status IN ('izin','sakit') THEN 1 ELSE 0 END) AS izin_cnt
            FROM absensi_guru
            WHERE tanggal = :tgl
        ");
        $this->db->bind(':tgl', $tanggal);
        $after = $this->db->single();

        $countAlfa = max(0, (int)($after->alfa_cnt ?? 0) - (int)($before->alfa_cnt ?? 0));
        $countIzin = max(0, (int)($after->izin_cnt ?? 0) - (int)($before->izin_cnt ?? 0));

        if ($countAlfa === 0 && $countIzin === 0) {
            return [
                'success'    => true,
                'message'    => "Semua guru yang memiliki jadwal hari {$hariIndo} sudah memiliki catatan absensi.",
                'total_alfa' => 0,
                'total_izin' => 0
            ];
        }

        return [
            'success'    => true,
            'message'    => "Berhasil memproses absensi guru: {$countAlfa} guru tercatat Alfa (tanpa scan QR/izin), {$countIzin} guru tercatat Izin/Sakit.",
            'total_alfa' => $countAlfa,
            'total_izin' => $countIzin
        ];
    }

    /**
     * Sinkronisasi cepat data Alfa & Izin untuk rentang tanggal (digunakan saat ekspor Excel, dashboard, atau monitoring log).
     * Mengecek tanggal yang belum lengkap secara batch dan membebaskan siswa PKL dari auto-alfa sekolah.
     */
    public function syncAlfaDanIzinRange($dari, $sampai, $respectTodayCutoff = true, $syncSiswa = true, $syncGuru = true, $maxDays = 62) {
        $today = date('Y-m-d');
        $start = strtotime($dari);
        $end   = min(strtotime($sampai), strtotime($today));

        if (!$start || !$end || $start > $end) return;

        if ($maxDays > 0) {
            $minAllowed = strtotime('-' . ($maxDays - 1) . ' days', $end);
            if ($start < $minAllowed) {
                $start = $minAllowed;
            }
        }

        $rangeStart = date('Y-m-d', $start);
        $rangeEnd   = date('Y-m-d', $end);

        // 1. Pastikan izin disetujui selalu memperbarui placeholder alfa pada rentang tanggal
        if ($syncSiswa) {
            $this->syncApprovedIzinOntoAlfa('absensi_siswa', 'siswa_id', 'siswa', $rangeStart, $rangeEnd);
        }
        if ($syncGuru) {
            $this->syncApprovedIzinOntoAlfa('absensi_guru', 'guru_id', 'guru', $rangeStart, $rangeEnd);
        }

        // 2. Muat jadwal aktif siswa per hari
        $this->db->query("SELECT hari, batas_terlambat, jam_pulang FROM jadwal_siswa WHERE status_aktif = 1");
        $aktifRows = $this->db->resultSet();
        $jadwalSiswaMap = [];
        foreach ($aktifRows ?: [] as $r) {
            $jadwalSiswaMap[$r->hari] = $r;
        }

        // 3. Hitung jumlah siswa reguler (non-PKL) & rekaman per tanggal
        $expectedSiswaCount = 0;
        $recordedSiswaMap   = [];
        if ($syncSiswa) {
            $this->db->query("
                SELECT COUNT(*) AS cnt
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN pkl_kelompok_siswa pks ON pks.siswa_id = s.id
                WHERE (k.status IS NULL OR LOWER(TRIM(k.status)) != 'pkl')
                  AND pks.id IS NULL
            ");
            $expectedSiswaCount = (int)($this->db->single()->cnt ?? 0);

            if ($expectedSiswaCount > 0) {
                $this->db->query("
                    SELECT a.tanggal, COUNT(DISTINCT a.siswa_id) AS cnt
                    FROM absensi_siswa a
                    JOIN siswa s ON s.id = a.siswa_id
                    LEFT JOIN kelas k ON s.kelas_id = k.id
                    LEFT JOIN pkl_kelompok_siswa pks ON pks.siswa_id = s.id
                    WHERE a.tanggal BETWEEN :d1 AND :d2
                      AND (k.status IS NULL OR LOWER(TRIM(k.status)) != 'pkl')
                      AND pks.id IS NULL
                    GROUP BY a.tanggal
                ");
                $this->db->bind(':d1', $rangeStart);
                $this->db->bind(':d2', $rangeEnd);
                foreach ($this->db->resultSet() ?: [] as $row) {
                    $recordedSiswaMap[$row->tanggal] = (int)$row->cnt;
                }
            }
        }

        // 4. Muat konfigurasi jadwal guru aktif per hari & rekaman per tanggal
        $activeGuruCountHari = [];
        $recordedGuruMap     = [];
        $totalGuruCount      = 0;
        if ($syncGuru) {
            $this->db->query("SELECT hari, COUNT(*) AS act_cnt FROM jadwal_guru WHERE is_active = 1 GROUP BY hari");
            foreach ($this->db->resultSet() ?: [] as $row) {
                $activeGuruCountHari[$row->hari] = (int)$row->act_cnt;
            }
            $this->db->query("SELECT COUNT(*) AS cnt FROM guru g JOIN users u ON u.id = g.user_id WHERE u.role IN ('guru', 'wali_kelas', 'walikelas')");
            $totalGuruCount = (int)($this->db->single()->cnt ?? 0);

            $this->db->query("
                SELECT tanggal, COUNT(DISTINCT guru_id) AS cnt
                FROM absensi_guru
                WHERE tanggal BETWEEN :d1 AND :d2
                GROUP BY tanggal
            ");
            $this->db->bind(':d1', $rangeStart);
            $this->db->bind(':d2', $rangeEnd);
            foreach ($this->db->resultSet() ?: [] as $row) {
                $recordedGuruMap[$row->tanggal] = (int)$row->cnt;
            }
        }

        // 5. Hanya proses tanggal yang benar-benar belum lengkap
        for ($curr = $start; $curr <= $end; $curr += 86400) {
            $tgl      = date('Y-m-d', $curr);
            $hariIndo = $this->getHariIndo($tgl);
            $jSiswa   = $jadwalSiswaMap[$hariIndo] ?? null;

            if ($syncSiswa && $expectedSiswaCount > 0) {
                $recSiswa = $recordedSiswaMap[$tgl] ?? 0;
                if ($recSiswa < $expectedSiswaCount && ($jSiswa !== null || $recSiswa > 0)) {
                    $cutoffOk = !$jSiswa || $this->isCutoffPassed($tgl, $jSiswa->batas_terlambat ?? null, $jSiswa->jam_pulang ?? null, $respectTodayCutoff);
                    if ($cutoffOk) {
                        $this->bulkInsertAlfaSiswaDate($tgl);
                    }
                }
            }

            if ($syncGuru) {
                $activeGuruHari = $activeGuruCountHari[$hariIndo] ?? 0;
                $recGuru        = $recordedGuruMap[$tgl] ?? 0;
                if ($activeGuruHari > 0) {
                    if ($recGuru < $activeGuruHari) {
                        $this->bulkInsertAlfaGuruDate($tgl, $hariIndo, false, $respectTodayCutoff);
                    }
                } elseif ($jSiswa !== null && $totalGuruCount > 0) {
                    if ($recGuru < $totalGuruCount) {
                        $cutoffOk = $this->isCutoffPassed($tgl, $jSiswa->batas_terlambat ?? null, $jSiswa->jam_pulang ?? null, $respectTodayCutoff);
                        if ($cutoffOk) {
                            $this->bulkInsertAlfaGuruDate($tgl, $hariIndo, true, $respectTodayCutoff);
                        }
                    }
                }
            }
        }
    }

    /**
     * Sinkronisasi otomatis dengan throttle agar dashboard, log kehadiran, rekap, dan live monitor tetap cepat.
     */
    public function syncAlfaAutoThrottled($dari = null, $sampai = null, $ttlSeconds = 60, $syncSiswa = true, $syncGuru = true, $useGlobalLock = false, $respectTodayCutoff = true) {
        $today  = date('Y-m-d');
        $dari   = !empty($dari) ? $dari : $today;
        $sampai = !empty($sampai) ? min($sampai, $today) : $today;
        if ($dari > $sampai) return;

        $lockHash = md5($dari . '|' . $sampai . '|' . (int)$syncSiswa . '|' . (int)$syncGuru);

        if (!$useGlobalLock && session_status() === PHP_SESSION_ACTIVE) {
            $sessKey  = 'last_auto_alfa_sync_' . $lockHash;
            $lastSync = (int)($_SESSION[$sessKey] ?? 0);
            if (time() - $lastSync < $ttlSeconds) {
                return;
            }
            $this->syncAlfaDanIzinRange($dari, $sampai, $respectTodayCutoff, $syncSiswa, $syncGuru);
            $_SESSION[$sessKey] = time();
            return;
        }

        $settingKey = 'alfa_sync_ts_' . substr($lockHash, 0, 12);
        try {
            $this->db->query("SELECT setting_value FROM app_settings WHERE setting_key = :k LIMIT 1");
            $this->db->bind(':k', $settingKey);
            $row    = $this->db->single();
            $lastTs = $row ? (int)$row->setting_value : 0;
            if (time() - $lastTs < $ttlSeconds) {
                return;
            }
            $this->db->query("INSERT INTO app_settings (setting_key, setting_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE setting_value = :v2");
            $this->db->bind(':k', $settingKey);
            $this->db->bind(':v', (string)time());
            $this->db->bind(':v2', (string)time());
            $this->db->execute();
        } catch (Throwable $e) {
            // Lanjutkan sync meskipun setting gagal diakses
        }

        $this->syncAlfaDanIzinRange($dari, $sampai, $respectTodayCutoff, $syncSiswa, $syncGuru);
    }
}
