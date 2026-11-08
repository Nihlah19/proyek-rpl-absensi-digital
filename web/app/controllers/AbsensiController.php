<?php
/**
 * AbsensiController.php
 * Riwayat absensi dengan search, filter tanggal, filter status, dan pagination.
 * Serta Presensi Mandiri Siswa PKL (Foto + Validasi GPS) & Notifikasi Telegram PKL.
 */
class AbsensiController extends Controller {

    private AbsensiMonitor $monitor;
    private const PER_PAGE = 5;

    public function __construct() {
        $this->monitor = $this->model('AbsensiMonitor');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GURU — riwayat absensinya sendiri
    // ─────────────────────────────────────────────────────────────────────────
    public function guru() {
        AuthHelper::checkRole(['guru']);
        $userId = (int)SessionHelper::get('user_id');
        $guruId = $this->monitor->getGuruIdByUserId($userId);

        $dari   = trim($_GET['dari']   ?? '');
        $sampai = trim($_GET['sampai'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $search = trim($_GET['search'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $rows  = $guruId ? $this->monitor->getRiwayatGuru($guruId, $dari, $sampai, $status, $search, $offset, self::PER_PAGE) : [];
        $total = $guruId ? $this->monitor->countRiwayatGuru($guruId, $dari, $sampai, $status, $search) : 0;

        $data = [
            'judul'    => 'Riwayat Absensi',
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => self::PER_PAGE,
            'dari'     => $dari,
            'sampai'   => $sampai,
            'status'   => $status,
            'search'   => $search,
        ];
        $this->view('templates/header', $data);
        $this->view('absensi/index_guru', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA — riwayat absensinya sendiri
    // ─────────────────────────────────────────────────────────────────────────
    public function siswa() {
        AuthHelper::checkRole(['siswa']);
        $userId  = (int)SessionHelper::get('user_id');
        $siswaId = $this->monitor->getSiswaIdByUserId($userId);

        $dari   = trim($_GET['dari']   ?? '');
        $sampai = trim($_GET['sampai'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $rows  = $siswaId ? $this->monitor->getRiwayatSiswa($siswaId, $dari, $sampai, $status, '', $offset, self::PER_PAGE) : [];
        $total = $siswaId ? $this->monitor->countRiwayatSiswa($siswaId, $dari, $sampai, $status) : 0;

        $data = [
            'judul'   => 'Riwayat Absensi',
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'perPage' => self::PER_PAGE,
            'dari'    => $dari,
            'sampai'  => $sampai,
            'status'  => $status,
        ];
        $this->view('templates/header', $data);
        $this->view('absensi/index_siswa', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA PKL — Presensi Mandiri (Foto Selfie + Lokasi GPS <= radius)
    // ─────────────────────────────────────────────────────────────────────────
    public function pkl() {
        AuthHelper::checkRole(['siswa']);
        $userId   = (int)SessionHelper::get('user_id');
        $pklModel = $this->model('Pkl');
        $siswaData = $pklModel->getSiswaPklDetail($userId);

        if (!$siswaData) {
            Flasher::setFlash('Error', 'Data profil siswa tidak ditemukan.', 'error');
            header('Location: ' . BASEURL . '/dashboard/siswa');
            exit;
        }

        // Cek apakah kelas berstatus PKL
        if (($siswaData->status_kelas ?? '') !== 'pkl') {
            Flasher::setFlash('Akses Dibatasi', 'Fitur Presensi Mandiri PKL hanya untuk siswa yang berada di kelas berstatus PKL.', 'warning');
            header('Location: ' . BASEURL . '/dashboard/siswa');
            exit;
        }

        $siswaId = (int)$siswaData->id;
        $lokasiPkl = $pklModel->getLokasiBySiswaId($siswaId);
        $absensiHariIni = $pklModel->getAbsensiToday($siswaId, date('Y-m-d'));

        $data = [
            'judul'          => 'Presensi Mandiri PKL',
            'siswa'          => $siswaData,
            'lokasiPkl'      => $lokasiPkl,
            'absensiHariIni' => $absensiHariIni,
            'tanggal'        => date('d F Y'),
        ];

        $this->view('templates/header', $data);
        $this->view('absensi/pkl', $data);
        $this->view('templates/footer');
    }

    /**
     * Simpan / Perbarui Lokasi PKL Siswa (Proteksi: Hanya oleh Guru Pembimbing / Admin)
     */
    public function save_lokasi_pkl() {
        AuthHelper::checkRole(['siswa']);
        Flasher::setFlash('Informasi', 'Penetapan dan perubahan lokasi PKL dikelola langsung melalui Guru Pembimbing PKL untuk mencegah kecurangan.', 'warning');
        header('Location: ' . BASEURL . '/absensi/pkl');
        exit;
    }

    /**
     * Submit Presensi PKL (Selfie Camera + Validasi Jarak GPS + Notifikasi Telegram)
     */
    public function submit_pkl() {
        AuthHelper::checkRole(['siswa']);
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        $userId   = (int)SessionHelper::get('user_id');
        $pklModel = $this->model('Pkl');
        $siswaData = $pklModel->getSiswaPklDetail($userId);

        if (!$siswaData || ($siswaData->status_kelas ?? '') !== 'pkl') {
            echo json_encode(['success' => false, 'message' => 'Status Anda bukan siswa kelas PKL.']);
            exit;
        }

        $siswaId = (int)$siswaData->id;
        $lokasiPkl = $pklModel->getLokasiBySiswaId($siswaId);

        if (!$lokasiPkl) {
            echo json_encode(['success' => false, 'message' => 'Anda belum didaftarkan ke kelompok tempat PKL oleh Guru Pembimbing PKL. Silakan hubungi Guru Pembimbing PKL Anda.']);
            exit;
        }

        // Ambil data payload JSON atau POST
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: $_POST;

        $latUser = isset($body['latitude']) ? (float)$body['latitude'] : null;
        $lngUser = isset($body['longitude']) ? (float)$body['longitude'] : null;
        $fotoBase64 = $body['foto'] ?? '';

        if ($latUser === null || $lngUser === null) {
            echo json_encode(['success' => false, 'message' => 'Titik koordinat GPS tidak terdeteksi. Pastikan izin lokasi (GPS) pada browser/perangkat Anda aktif.']);
            exit;
        }

        if (empty($fotoBase64)) {
            echo json_encode(['success' => false, 'message' => 'Foto selfie wajib diambil langsung melalui kamera.']);
            exit;
        }

        // 1. Hitung Jarak ke Lokasi PKL (Validasi Server-Side Haversine)
        $jarakMeter = Pkl::calculateDistance($latUser, $lngUser, (float)$lokasiPkl->latitude, (float)$lokasiPkl->longitude);
        $maxRadius = (int)($lokasiPkl->radius_meter ?? 50);
        if ($maxRadius < 10) $maxRadius = 50;

        // Jika melebihi batas radius -> Presensi GAGAL!
        if ($jarakMeter > $maxRadius) {
            echo json_encode([
                'success' => false,
                'message' => "Presensi GAGAL! Jarak Anda ({$jarakMeter} meter) melebihi batas maksimal {$maxRadius} meter dari lokasi PKL terdaftar ({$lokasiPkl->nama_tempat}). Anda wajib berada di tempat PKL.",
                'jarak'   => $jarakMeter,
                'radius'  => $maxRadius
            ]);
            exit;
        }

        // 2. Decode dan Simpan Foto
        $uploadDir = (defined('PUBROOT') ? PUBROOT : dirname(__DIR__, 2) . '/public') . '/uploads/absensi_pkl/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        // Bersihkan header base64 jika ada
        if (preg_match('/^data:image\/(\w+);base64,/', $fotoBase64, $type)) {
            $fotoBase64 = substr($fotoBase64, strpos($fotoBase64, ',') + 1);
            $ext = strtolower($type[1]);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $ext = 'jpg';
            }
        } else {
            $ext = 'jpg';
        }

        $decoded = base64_decode($fotoBase64);
        if (!$decoded) {
            echo json_encode(['success' => false, 'message' => 'Format gambar selfie tidak valid.']);
            exit;
        }

        $today = date('Y-m-d');
        $nowTime = date('H:i:s');
        $filename = 'pkl_' . $siswaId . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (file_put_contents($destPath, $decoded) === false) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file foto ke server.']);
            exit;
        }

        // 3. Cek Status Presensi Hari Ini (Mencegah absensi & notifikasi ganda)
        $absensiToday = $pklModel->getAbsensiToday($siswaId, $today);

        $tempatPklNama = !empty($lokasiPkl->nama_tempat) ? $lokasiPkl->nama_tempat : ($lokasiPkl->nama_kelompok ?? 'Lokasi PKL');

        if (!$absensiToday) {
            // Belum ada absen hari ini -> ABSEN MASUK (DATANG)
            $saved = $pklModel->catatAbsenMasuk($siswaId, $today, $nowTime, $filename, $latUser, $lngUser, $jarakMeter);
            if ($saved) {
                // 1. Notifikasi Telegram PKL (Protected against failure)
                try {
                    TelegramHelper::sendPklNotification($siswaData, $tempatPklNama, 'Datang', $nowTime, $today, $jarakMeter);
                } catch (Throwable $e) {
                    error_log("[Telegram PKL Error] " . $e->getMessage());
                }

                // 2. Notifikasi Web In-App untuk Siswa & Guru Pembimbing
                try {
                    NotificationHelper::send($userId, 'Presensi Datang PKL Berhasil', "Presensi datang Anda di {$tempatPklNama} tercatat pukul {$nowTime} WIB.", 'pkl_masuk', 'absensi/pkl');
                    $pembimbing = $pklModel->getPembimbingByKelasId($siswaData->kelas_id);
                    if ($pembimbing && !empty($pembimbing->user_id)) {
                        NotificationHelper::send((int)$pembimbing->user_id, 'Presensi PKL Datang: ' . $siswaData->name, "{$siswaData->name} ({$siswaData->nama_kelas}) telah presensi datang di {$tempatPklNama} pukul {$nowTime} WIB (Jarak GPS: {$jarakMeter}m).", 'pkl_masuk', 'pkl/monitoring?kelas_id=' . $siswaData->kelas_id);
                    }
                } catch (Throwable $e) {
                    error_log("[Notif PKL Error] " . $e->getMessage());
                }

                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => true,
                    'type'    => 'masuk',
                    'message' => "Presensi Datang PKL Berhasil! Waktu: {$nowTime} WIB. Jarak ke titik PKL: {$jarakMeter} meter. Notifikasi telah dikirim ke Telegram.",
                    'jam'     => $nowTime,
                    'jarak'   => $jarakMeter
                ]);
            } else {
                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menyimpan presensi datang ke database.'
                ]);
            }
            exit;
        } elseif (empty($absensiToday->jam_pulang)) {
            // Sudah masuk, belum absen pulang -> ABSEN PULANG
            $saved = $pklModel->catatAbsenPulang($absensiToday->id, $nowTime, $filename, $latUser, $lngUser, $jarakMeter);
            if ($saved) {
                // 1. Notifikasi Telegram PKL (Protected against failure)
                try {
                    TelegramHelper::sendPklNotification($siswaData, $tempatPklNama, 'Pulang', $nowTime, $today, $jarakMeter);
                } catch (Throwable $e) {
                    error_log("[Telegram PKL Error] " . $e->getMessage());
                }

                // 2. Notifikasi Web In-App untuk Siswa & Guru Pembimbing
                try {
                    NotificationHelper::send($userId, 'Presensi Pulang PKL Berhasil', "Presensi pulang Anda di {$tempatPklNama} tercatat pukul {$nowTime} WIB.", 'pkl_pulang', 'absensi/pkl');
                    $pembimbing = $pklModel->getPembimbingByKelasId($siswaData->kelas_id);
                    if ($pembimbing && !empty($pembimbing->user_id)) {
                        NotificationHelper::send((int)$pembimbing->user_id, 'Presensi PKL Pulang: ' . $siswaData->name, "{$siswaData->name} ({$siswaData->nama_kelas}) telah presensi pulang di {$tempatPklNama} pukul {$nowTime} WIB.", 'pkl_pulang', 'pkl/monitoring?kelas_id=' . $siswaData->kelas_id);
                    }
                } catch (Throwable $e) {
                    error_log("[Notif PKL Error] " . $e->getMessage());
                }

                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => true,
                    'type'    => 'pulang',
                    'message' => "Presensi Pulang PKL Berhasil! Waktu: {$nowTime} WIB. Jarak ke titik PKL: {$jarakMeter} meter. Notifikasi telah dikirim ke Telegram.",
                    'jam'     => $nowTime,
                    'jarak'   => $jarakMeter
                ]);
            } else {
                if (ob_get_length()) ob_clean();
                echo json_encode([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menyimpan presensi pulang ke database.'
                ]);
            }
            exit;
        } else {
            // Sudah absen masuk dan pulang
            echo json_encode([
                'success' => false,
                'message' => "Anda sudah menyelesaikan Presensi Datang ({$absensiToday->jam_datang}) dan Pulang ({$absensiToday->jam_pulang}) untuk hari ini."
            ]);
            exit;
        }
    }
}
