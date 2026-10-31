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
    public function generateAlfaSiswaHariIni($tanggal = null) {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        $hariIndo = $this->getHariIndo($tanggal);
        $this->db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1");
        $this->db->bind(':hari', $hariIndo);
        $jadwal = $this->db->single();
        if (!$jadwal) {
            // Periksa juga apakah pada tanggal ini sudah ada presensi siswa yang masuk
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

        // Ambil semua siswa yang belum tercatat absensi pada tanggal ini (Kecuali kelas PKL yang berstatus scan bebas)
        $this->db->query("
            SELECT s.id as siswa_id, s.user_id, u.name 
            FROM siswa s
            JOIN users u ON u.id = s.user_id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE (k.status IS NULL OR k.status != 'pkl')
              AND s.id NOT IN (
                SELECT siswa_id FROM absensi_siswa WHERE tanggal = :tgl
            )
        ");
        $this->db->bind(':tgl', $tanggal);
        $unrecorded = $this->db->resultSet();

        if (empty($unrecorded)) {
            return [
                'success' => true,
                'message' => 'Semua siswa sudah memiliki catatan absensi.',
                'total_alfa' => 0,
                'total_izin' => 0
            ];
        }

        $countAlfa = 0;
        $countIzin = 0;

        foreach ($unrecorded as $siswa) {
            // Cek apakah ada pengajuan izin yang disetujui (approved)
            $this->db->query("
                SELECT jenis, alasan FROM pengajuan_izin 
                WHERE user_id = :uid 
                AND status = 'approved' 
                AND :tgl BETWEEN tanggal_mulai AND tanggal_selesai 
                LIMIT 1
            ");
            $this->db->bind(':uid', $siswa->user_id);
            $this->db->bind(':tgl', $tanggal);
            $izin = $this->db->single();

            if ($izin) {
                $status = ($izin->jenis === 'sakit') ? 'sakit' : 'izin';
                $keterangan = $izin->alasan ? 'Izin disetujui: ' . $izin->alasan : 'Izin disetujui';
                $countIzin++;
            } else {
                $status = 'alfa';
                $keterangan = 'Tidak hadir tanpa scan RFID';
                $countAlfa++;
            }

            $this->db->query("
                INSERT INTO absensi_siswa (siswa_id, tanggal, jam_datang, jam_pulang, status, keterangan)
                VALUES (:sid, :tgl, NULL, NULL, :status, :ket)
            ");
            $this->db->bind(':sid', $siswa->siswa_id);
            $this->db->bind(':tgl', $tanggal);
            $this->db->bind(':status', $status);
            $this->db->bind(':ket', $keterangan);
            $this->db->execute();
        }

        return [
            'success' => true,
            'message' => "Berhasil memproses absensi siswa: {$countAlfa} siswa tercatat Alfa (tanpa scan RFID), {$countIzin} siswa tercatat Izin/Sakit.",
            'total_alfa' => $countAlfa,
            'total_izin' => $countIzin
        ];
    }

    /**
     * Generate data absensi otomatis untuk guru yang memiliki jadwal dinas/mengajar aktif hari ini
     * tetapi belum scan QR hingga jam kedatangan/kepulangan berakhir (Auto-Alfa / Sync Izin Guru).
     */
    public function generateAlfaGuruHariIni($tanggal = null) {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        $hariIndo = $this->getHariIndo($tanggal);

        // 1. Ambil guru yang MEMILIKI JADWAL AKTIF hari ini tetapi belum scan QR (belum ada catatan absensi_guru)
        $this->db->query("
            SELECT g.id as guru_id, g.user_id, u.name, MIN(j.id) as jadwal_id, MAX(j.jam_pulang) as jam_pulang
            FROM jadwal_guru j
            JOIN guru g ON g.id = j.guru_id
            JOIN users u ON u.id = g.user_id
            WHERE j.hari = :hari 
              AND j.is_active = 1
              AND g.id NOT IN (
                  SELECT guru_id FROM absensi_guru WHERE tanggal = :tgl
              )
            GROUP BY g.id, g.user_id, u.name
        ");
        $this->db->bind(':hari', $hariIndo);
        $this->db->bind(':tgl', $tanggal);
        $unrecorded = $this->db->resultSet();

        // 2. Fallback: Jika pada hari ini tidak ada entri terpisah di jadwal_guru (misal hari Minggu/kegiatan khusus),
        // tetapi jadwal_siswa aktif sekolah, periksa seluruh guru aktif yang belum scan QR:
        if (empty($unrecorded)) {
            $this->db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1");
            $this->db->bind(':hari', $hariIndo);
            $jadwalSiswa = $this->db->single();

            // Cek apakah ada jadwal guru sama sekali pada hari ini
            $this->db->query("SELECT COUNT(*) as c FROM jadwal_guru WHERE hari = :hari AND is_active = 1");
            $this->db->bind(':hari', $hariIndo);
            $countJadwalHari = (int)($this->db->single()->c ?? 0);

            if ($countJadwalHari === 0 && $jadwalSiswa) {
                // Hari sekolah aktif, maka seluruh dewan guru aktif yang belum scan QR diproses
                $this->db->query("
                    SELECT g.id as guru_id, g.user_id, u.name
                    FROM guru g
                    JOIN users u ON u.id = g.user_id
                    WHERE u.role IN ('guru', 'wali_kelas', 'walikelas')
                      AND g.id NOT IN (
                          SELECT guru_id FROM absensi_guru WHERE tanggal = :tgl
                      )
                ");
                $this->db->bind(':tgl', $tanggal);
                $unrecorded = $this->db->resultSet();
            }
        }

        if (empty($unrecorded)) {
            return [
                'success'    => true,
                'message'    => "Semua guru yang memiliki jadwal hari {$hariIndo} sudah memiliki catatan absensi.",
                'total_alfa' => 0,
                'total_izin' => 0
            ];
        }

        $countAlfa = 0;
        $countIzin = 0;

        foreach ($unrecorded as $guru) {
            // Cek apakah ada pengajuan izin yang disetujui (approved)
            $this->db->query("
                SELECT jenis, alasan FROM pengajuan_izin 
                WHERE user_id = :uid 
                AND status = 'approved' 
                AND :tgl BETWEEN tanggal_mulai AND tanggal_selesai 
                LIMIT 1
            ");
            $this->db->bind(':uid', $guru->user_id);
            $this->db->bind(':tgl', $tanggal);
            $izin = $this->db->single();

            if ($izin) {
                $status = ($izin->jenis === 'sakit') ? 'sakit' : 'izin';
                $keterangan = $izin->alasan ? 'Izin disetujui: ' . $izin->alasan : 'Izin disetujui';
                $countIzin++;
            } else {
                $status = 'alfa';
                $keterangan = 'Tidak hadir tanpa scan QR';
                $countAlfa++;
            }

            $this->db->query("
                INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, jam_pulang, status, keterangan)
                VALUES (:gid, :tgl, NULL, NULL, :status, :ket)
            ");
            $this->db->bind(':gid', $guru->guru_id);
            $this->db->bind(':tgl', $tanggal);
            $this->db->bind(':status', $status);
            $this->db->bind(':ket', $keterangan);
            $this->db->execute();
        }

        return [
            'success'    => true,
            'message'    => "Berhasil memproses absensi guru: {$countAlfa} guru tercatat Alfa (tanpa scan QR), {$countIzin} guru tercatat Izin/Sakit.",
            'total_alfa' => $countAlfa,
            'total_izin' => $countIzin
        ];
    }

    /**
     * Sinkronisasi data Alfa & Izin untuk suatu rentang tanggal (digunakan saat ekspor Excel atau monitoring rekap).
     * Memastikan seluruh siswa yang tidak tap RFID dan guru yang tidak scan QR otomatis tercatat Alfa jika tidak ada izin/sakit.
     */
    public function syncAlfaDanIzinRange($dari, $sampai) {
        $start = strtotime($dari);
        $end = min(strtotime($sampai), strtotime(date('Y-m-d')));
        
        if ($start > $end) return;

        // Ambil daftar hari yang aktif dari jadwal_siswa
        $this->db->query("SELECT hari FROM jadwal_siswa WHERE status_aktif = 1");
        $aktifRows = $this->db->resultSet();
        $hariAktif = array_map(function($r) { return $r->hari; }, $aktifRows ?: []);

        for ($curr = $start; $curr <= $end; $curr += 86400) {
            $tgl = date('Y-m-d', $curr);
            $hariIndo = $this->getHariIndo($tgl);

            // Cek apakah hari ini sekolah aktif
            $isAktif = in_array($hariIndo, $hariAktif);
            if (!$isAktif) {
                // Periksa apakah ada transaksi absensi di tanggal ini
                $this->db->query("SELECT COUNT(*) as c FROM absensi_siswa WHERE tanggal = :tgl");
                $this->db->bind(':tgl', $tgl);
                $hasAtt = (int)($this->db->single()->c ?? 0);
                if ($hasAtt > 0) {
                    $isAktif = true;
                }
            }

            if ($isAktif) {
                $this->generateAlfaSiswaHariIni($tgl);
                $this->generateAlfaGuruHariIni($tgl);
            }
        }
    }
}
