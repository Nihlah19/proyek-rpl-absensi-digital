<?php
/**
 * DashboardController.php
 * Mengisi data dari AbsensiMonitor ke masing-masing view dashboard.
 * Business logic absensi TIDAK diubah.
 */
class DashboardController extends Controller {

    private AbsensiMonitor $monitor;
    private Laporan $laporan;

    public function __construct() {
        $this->monitor = $this->model('AbsensiMonitor');
        $this->laporan = $this->model('Laporan');
    }

    /** Redirect ke dashboard sesuai role */
    public function index() {
        AuthHelper::check();
        $role = SessionHelper::get('role');
        $map = [
            'admin'      => '/dashboard/admin',
            'guru'       => '/dashboard/guru',
            'kepsek'     => '/dashboard/kepala_sekolah',
            'wali_kelas' => '/dashboard/wali_kelas',
            'walikelas'  => '/dashboard/wali_kelas',
            'siswa'      => '/dashboard/siswa',
        ];
        $url = $map[$role] ?? '/auth/login';
        header('Location: ' . BASEURL . $url);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN
    // ─────────────────────────────────────────────────────────────────────────
    public function admin() {
        AuthHelper::checkRole(['admin']);

        $db = new Database();
        $db->query("SELECT id, nama_kelas, tingkat, jurusan FROM kelas ORDER BY nama_kelas ASC");
        $kelasList = $db->resultSet();

        $today = date('Y-m-d');

        // Otomatis sinkronisasi Alfa jika jam kepulangan sekolah hari ini telah usai
        // dan masih ada siswa/guru yang belum tercatat presensi (tidak scan RFID / QR)
        $absensiModel = $this->model('Absensi');
        $jadwalSiswa = $absensiModel->getJadwalHariIni();
        if ($jadwalSiswa && !empty($jadwalSiswa->status_aktif) && !empty($jadwalSiswa->jam_pulang)) {
            $nowTime = date('H:i:s');
            if ($nowTime >= $jadwalSiswa->jam_pulang) {
                $absensiModel->generateAlfaSiswaHariIni($today);
                $absensiModel->generateAlfaGuruHariIni($today);
            }
        }

        $absensiSiswaHariIni = $this->laporan->getRekapSiswa(['dari' => $today, 'sampai' => $today, 'limit' => 50, 'offset' => 0]);
        $absensiGuruHariIni  = $this->laporan->getRekapGuru(['dari' => $today, 'sampai' => $today, 'limit' => 50, 'offset' => 0]);

        // Ringkasan status konfigurasi WhatsApp & Mode IoT
        $waStats = null;
        try {
            $db->query("SELECT COUNT(*) as total_slots, 
                               SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_slots,
                               SUM(CASE WHEN token IS NOT NULL AND TRIM(token) != '' THEN 1 ELSE 0 END) as configured_slots 
                        FROM wa_config");
            $waStats = $db->single();
        } catch (Exception $e) {
            $waStats = null;
        }

        $currentAppMode = AppSetting::get('app_mode', 'absen');

        // Data Monitoring & Tata Kelola Siswa PKL untuk Dashboard Admin
        $pklModel     = $this->model('Pkl');
        $adminPklData = $pklModel->getAdminPklDashboardData();

        $data = [
            'judul'               => 'Dashboard Admin',
            'adminPkl'            => $adminPklData,
            'statsSiswa'          => $this->monitor->getStatsSiswaHariIni(),
            'statsGuru'           => $this->monitor->getStatsGuruHariIni(),
            'trendHarian'         => $this->monitor->getTrendKehadiran7Hari(),
            'statsPerKelas'       => $this->monitor->getStatsKehadiranPerKelasHariIni(),
            'iotDevices'          => $this->monitor->getStatusIotDevices(),
            'aiConfig'            => AppSetting::getAiConfig(),
            'waStats'             => $waStats,
            'currentAppMode'      => $currentAppMode,
            'izinPulangHariIni'   => $this->monitor->getSiswaIzinPulangCepatHariIni(),
            'tanggal'             => date('d F Y'),
            'kelasList'           => $kelasList,
            'absensiSiswaHariIni' => $absensiSiswaHariIni,
            'absensiGuruHariIni'  => $absensiGuruHariIni,
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/admin', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GURU
    // ─────────────────────────────────────────────────────────────────────────
    public function guru() {
        AuthHelper::checkRole(['guru']);
        $userId = (int)SessionHelper::get('user_id');
        $guruId = $this->monitor->getGuruIdByUserId($userId);

        // Ambil data guru dari database untuk ditampilkan di dashboard
        $db = new Database();
        $db->query("SELECT g.*, u.name as nama, u.username FROM guru g JOIN users u ON g.user_id = u.id WHERE g.id = :gid");
        $db->bind(':gid', $guruId);
        $guruData = $db->single();

        $statsBulanan   = $guruId ? $this->monitor->getStatsBulananGuru($guruId) : null;
        $riwayatGuru    = $guruId ? $this->monitor->getRiwayatGuru($guruId, '', '', '', '', 0, 5) : [];
        $jadwalHariIni  = $guruId ? $this->monitor->getJadwalHariIniGuru($guruId) : null;

        // Data Pembimbing PKL (Pengaturan & Log Riwayat Presensi PKL)
        $isPembimbingPkl = false;
        $pklDashboard    = null;
        $pklModel        = $this->model('Pkl');
        if ($pklModel->isUserPembimbingPkl($userId)) {
            $isPembimbingPkl = true;
            $pklDashboard    = $pklModel->getDashboardPembimbingData($userId);
        }

        $data = [
            'judul'           => 'Dashboard Guru',
            'absensiHariIni'  => $guruId ? $this->monitor->getAbsensiGuruHariIni($guruId) : null,
            'guru'            => $guruData,
            'guru_id'         => $guruId,
            'statsBulanan'    => $statsBulanan,
            'riwayatGuru'     => $riwayatGuru,
            'jadwalHariIni'   => $jadwalHariIni,
            'isPembimbingPkl' => $isPembimbingPkl,
            'pklDashboard'    => $pklDashboard,
            'tanggal'         => date('d F Y'),
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/guru', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KEPALA SEKOLAH
    // ─────────────────────────────────────────────────────────────────────────
    public function kepala_sekolah() {
        AuthHelper::checkRole(['kepsek']);
        $izinModel = $this->model('Izin');
        $daftarIzinGuru = $izinModel->getDaftarPersetujuanKepsek();

        $pendingIzinCount = 0;
        foreach ($daftarIzinGuru as $iz) {
            if ($iz->status === 'pending') {
                $pendingIzinCount++;
            }
        }

        $today = date('Y-m-d');
        $absensiGuruHariIni = $this->laporan->getRekapGuru([
            'dari'   => $today,
            'sampai' => $today,
            'limit'  => 50,
            'offset' => 0
        ]);

        $data = [
            'judul'              => 'Dashboard Kepala Sekolah',
            'statsGuru'          => $this->monitor->getStatsGuruHariIni(),
            'guruTerlambat'      => $this->monitor->getGuruSeringTerlambat(5),
            'daftarIzinGuru'     => $daftarIzinGuru,
            'pendingIzinCount'   => $pendingIzinCount,
            'tanggal'            => date('d F Y'),
            'absensiGuruHariIni' => $absensiGuruHariIni,
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/kepala_sekolah', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WALI KELAS
    // ─────────────────────────────────────────────────────────────────────────
    public function wali_kelas() {
        AuthHelper::checkRole(['wali_kelas', 'walikelas']);
        $userId  = (int)SessionHelper::get('user_id');
        $kelas   = $this->monitor->getKelasWali($userId);
        $kelasId = $kelas ? (int)$kelas->id : null;
        $guruId  = $this->monitor->getGuruIdByUserId($userId);

        // Ambil data guru dari database untuk absensi wali kelas
        $db = new Database();
        $db->query("SELECT g.*, u.name as nama, u.username FROM guru g JOIN users u ON g.user_id = u.id WHERE g.id = :gid");
        $db->bind(':gid', $guruId);
        $guruData = $db->single();

        $today = date('Y-m-d');
        $absensiSiswaHariIni = $kelasId ? $this->laporan->getRekapSiswa([
            'dari'     => $today,
            'sampai'   => $today,
            'kelas_id' => $kelasId,
            'limit'    => 50,
            'offset'   => 0
        ]) : [];

        $statsBulananGuru = $guruId ? $this->monitor->getStatsBulananGuru($guruId) : null;
        $jadwalHariIni    = $guruId ? $this->monitor->getJadwalHariIniGuru($guruId) : null;

        // Data Pembimbing PKL (Pengaturan & Log Riwayat Presensi PKL jika merangkap)
        $isPembimbingPkl = false;
        $pklDashboard    = null;
        $pklModel        = $this->model('Pkl');
        if ($pklModel->isUserPembimbingPkl($userId)) {
            $isPembimbingPkl = true;
            $pklDashboard    = $pklModel->getDashboardPembimbingData($userId);
        }

        $data = [
            'judul'               => 'Dashboard Wali Kelas',
            'kelas'               => $kelas,
            'statsSiswa'          => $kelasId ? $this->monitor->getStatsSiswaKelas($kelasId) : null,
            'siswaTerlambat'      => $kelasId ? $this->monitor->getSiswaSaringTerlambat($kelasId, 5) : [],
            'absensiHariIni'      => $guruId ? $this->monitor->getAbsensiGuruHariIni($guruId) : null,
            'guru'                => $guruData,
            'guru_id'             => $guruId,
            'statsBulananGuru'    => $statsBulananGuru,
            'jadwalHariIni'       => $jadwalHariIni,
            'isPembimbingPkl'     => $isPembimbingPkl,
            'pklDashboard'        => $pklDashboard,
            'tanggal'             => date('d F Y'),
            'absensiSiswaHariIni' => $absensiSiswaHariIni,
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/wali_kelas', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA
    // ─────────────────────────────────────────────────────────────────────────
    public function siswa() {
        AuthHelper::checkRole(['siswa']);
        $userId  = (int)SessionHelper::get('user_id');
        $siswaId = $this->monitor->getSiswaIdByUserId($userId);

        $pengajuanModel = $this->model('PengajuanPulangCepat');

        $db = new Database();
        $db->query("
            SELECT s.*, u.name as nama, u.username, k.nama_kelas, k.status as status_kelas, rc.uid as rfid_uid, rc.status as rfid_status
            FROM siswa s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN rfid_cards rc ON rc.student_id = s.id
            WHERE s.id = :sid
        ");
        $db->bind(':sid', $siswaId);
        $siswaData = $db->single();

        $isPkl = ($siswaData && isset($siswaData->status_kelas) && $siswaData->status_kelas === 'pkl');
        $lokasiPkl = null;
        if ($isPkl && $siswaId) {
            $pklModel = $this->model('Pkl');
            $lokasiPkl = $pklModel->getLokasiBySiswaId($siswaId);
        }

        $statsBulanan   = $siswaId ? $this->monitor->getStatsBulananSiswa($siswaId) : null;
        $riwayatSiswa   = $siswaId ? $this->monitor->getRiwayatSiswa($siswaId, '', '', '', '', 0, 5) : [];
        $jadwalHariIni  = $this->monitor->getJadwalHariIniSiswa();

        $data = [
            'judul'               => 'Dashboard Siswa',
            'siswa'               => $siswaData,
            'kelasNama'           => $siswaData->nama_kelas ?? 'Belum ditentukan',
            'isPkl'               => $isPkl,
            'lokasiPkl'           => $lokasiPkl,
            'absensiHariIni'      => $siswaId ? $this->monitor->getAbsensiSiswaHariIni($siswaId) : null,
            'butuh_alasan_pulang' => $siswaId ? $this->monitor->cekPulangCepatSiswa($siswaId) : false,
            'pengajuanHariIni'    => $siswaId ? $pengajuanModel->getActiveBySiswaAndDate($siswaId, date('Y-m-d')) : null,
            'statsBulanan'        => $statsBulanan,
            'riwayatSiswa'        => $riwayatSiswa,
            'jadwalHariIni'       => $jadwalHariIni,
            'tanggal'             => date('d F Y'),
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/siswa', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DOWNLOAD CSV — ADMIN
    // ─────────────────────────────────────────────────────────────────────────

    /** Download rekap absensi siswa (CSV) */
    public function download_siswa() {
        AuthHelper::checkRole(['admin']);
        $dari   = $_GET['dari']   ?? date('Y-m-01');
        $sampai = $_GET['sampai'] ?? date('Y-m-d');
        
        $absensiModel = $this->model('Absensi');
        $absensiModel->syncAlfaDanIzinRange($dari, min($sampai, date('Y-m-d')));
        $rows   = $this->laporan->getRekapSiswaAll($dari, $sampai);

        $filename = 'rekap_absensi_siswa_' . $dari . '_sd_' . $sampai . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        $out = fopen('php://output', 'w');
        // BOM agar Excel bisa baca UTF-8
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jam Datang', 'Jam Pulang', 'Status', 'Keterangan'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->tanggal,
                $r->nis,
                $r->name,
                $r->nama_kelas,
                $r->jam_datang ?? '-',
                $r->jam_pulang ?? '-',
                ucfirst($r->status),
                $r->keterangan ?? '-',
            ], ';');
        }
        fclose($out);
        exit;
    }

    /** Download rekap absensi guru (CSV) */
    public function download_guru() {
        AuthHelper::checkRole(['admin']);
        $dari   = $_GET['dari']   ?? date('Y-m-01');
        $sampai = $_GET['sampai'] ?? date('Y-m-d');
        
        $absensiModel = $this->model('Absensi');
        $absensiModel->syncAlfaDanIzinRange($dari, min($sampai, date('Y-m-d')));
        $rows   = $this->laporan->getRekapGuruAll($dari, $sampai);

        $filename = 'rekap_absensi_guru_' . $dari . '_sd_' . $sampai . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Tanggal', 'NIP', 'Nama Guru', 'Jam Datang', 'Jam Pulang', 'Status', 'Keterangan'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->tanggal,
                $r->nip,
                $r->name,
                $r->jam_datang ?? '-',
                $r->jam_pulang ?? '-',
                ucfirst($r->status),
                $r->keterangan ?? '-',
            ], ';');
        }
        fclose($out);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DOWNLOAD CSV — WALI KELAS
    // ─────────────────────────────────────────────────────────────────────────

    /** Download rekap absensi siswa per kelas (CSV) */
    public function download_kelas() {
        AuthHelper::checkRole(['wali_kelas', 'walikelas']);
        $userId  = (int)SessionHelper::get('user_id');
        $kelas   = $this->monitor->getKelasWali($userId);
        if (!$kelas) {
            die('Kelas tidak ditemukan.');
        }
        $dari   = $_GET['dari']   ?? date('Y-m-01');
        $sampai = $_GET['sampai'] ?? date('Y-m-d');
        
        $absensiModel = $this->model('Absensi');
        $absensiModel->syncAlfaDanIzinRange($dari, min($sampai, date('Y-m-d')));
        $rows   = $this->laporan->getRekapSiswaByKelas((int)$kelas->id, $dari, $sampai);

        $kelasSlug = preg_replace('/[^a-zA-Z0-9_]/', '_', $kelas->nama_kelas);
        $filename  = 'rekap_absensi_kelas_' . $kelasSlug . '_' . $dari . '_sd_' . $sampai . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jam Datang', 'Jam Pulang', 'Status', 'Keterangan'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->tanggal,
                $r->nis,
                $r->name,
                $r->nama_kelas,
                $r->jam_datang ?? '-',
                $r->jam_pulang ?? '-',
                ucfirst($r->status),
                $r->keterangan ?? '-',
            ], ';');
        }
        fclose($out);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IOT DEVICES MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function get_iot_devices() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');
        $devices = $this->monitor->getStatusIotDevices();
        echo json_encode(['success' => true, 'data' => $devices]);
        exit;
    }

    public function update_iot_device() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $device_name = trim($_POST['device_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!$id || empty($device_name)) {
            echo json_encode(['success' => false, 'message' => 'Nama perangkat wajib diisi']);
            exit;
        }

        $dataUpdate = [
            'device_name' => $device_name,
            'location'    => $location,
            'status'      => in_array($status, ['active', 'inactive']) ? $status : 'active'
        ];

        // Ambil data perangkat saat ini
        $deviceModel = $this->model('IotDevice');
        $currentDev = $deviceModel->getDeviceById($id);

        // Jika admin mengisi remote config WiFi / Host / Password
        $remote_ssid = trim($_POST['remote_ssid'] ?? '');
        $remote_pass = trim($_POST['remote_pass'] ?? '');
        $remote_host = trim($_POST['remote_host'] ?? '');

        // Jika hanya mengganti sandi tanpa mengetik ulang nama SSID
        if (!empty($remote_pass) && empty($remote_ssid) && $currentDev && !empty($currentDev->wifi_ssid)) {
            $remote_ssid = $currentDev->wifi_ssid;
        }

        if (!empty($remote_ssid)) {
            $dataUpdate['pending_ssid'] = $remote_ssid;
            $dataUpdate['pending_pass'] = $remote_pass;
        }
        if (!empty($remote_host)) {
            // Normalisasi host
            if (!str_starts_with($remote_host, 'http://') && !str_starts_with($remote_host, 'https://')) {
                $remote_host = 'http://' . $remote_host;
            }
            $remote_host = rtrim($remote_host, '/');
            $dataUpdate['pending_host'] = $remote_host;
        }

        // Cek jika admin ingin menghapus / membatalkan pending sync
        if (!empty($_POST['cancel_pending'])) {
            $deviceModel->clearPendingConfig($id);
            unset($dataUpdate['pending_ssid'], $dataUpdate['pending_pass'], $dataUpdate['pending_host']);
        }

        $result = $deviceModel->updateDeviceDetails($id, $dataUpdate);

        // Coba Direct Realtime Push ke ESP jika sedang online di IP-nya
        $directAcc = false;
        if ($result && empty($_POST['cancel_pending']) && (!empty($remote_ssid) || !empty($remote_pass) || !empty($remote_host))) {
            if ($currentDev && !empty($currentDev->ip_address) && $currentDev->ip_address !== '-' && filter_var($currentDev->ip_address, FILTER_VALIDATE_IP)) {
                $targetHost = !empty($remote_host) ? $remote_host : rtrim(BASEURL, '/');
                $targetHost = preg_replace('#/web/public.*#', '', $targetHost);
                $targetSsid = !empty($remote_ssid) ? $remote_ssid : ($currentDev->wifi_ssid ?? '');

                $postFields = http_build_query([
                    'ssid' => $targetSsid,
                    'pass' => $remote_pass,
                    'host' => $targetHost
                ]);

                $ch = curl_init("http://{$currentDev->ip_address}/save");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                $curlResp = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $directAcc = true;
                    $deviceModel->clearPendingConfig($id);
                    $deviceModel->logActivity($id, "Direct Push ACC: Berhasil kirim config baru langsung ke {$currentDev->ip_address}", 200);
                }
            }
        }

        if ($result) {
            if ($directAcc) {
                $msg = 'ESP langsung merespon & ACC perubahan sandi/jaringan! Alat sedang merestart ke WiFi baru.';
            } elseif (!empty($remote_ssid) || !empty($remote_host) || !empty($remote_pass)) {
                $msg = 'Konfigurasi disimpan. ESP akan otomatis merespon & ACC saat heartbeat berikutnya (maks 10 detik).';
            } elseif (!empty($_POST['cancel_pending'])) {
                $msg = 'Konfigurasi diperbarui & antrean pending berhasil dibatalkan.';
            } else {
                $msg = 'Perangkat berhasil diperbarui!';
            }
            echo json_encode(['success' => true, 'message' => $msg, 'direct_acc' => $directAcc]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui perangkat']);
        }
        exit;
    }

    public function cancel_pending_iot() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID perangkat tidak valid']);
            exit;
        }

        $deviceModel = $this->model('IotDevice');
        $result = $deviceModel->clearPendingConfig($id);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Sinkronisasi pending berhasil dibatalkan.' : 'Gagal membatalkan sinkronisasi pending.'
        ]);
        exit;
    }

    public function get_server_info() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');
        
        $localIp = gethostbyname(gethostname());
        if ($localIp === '127.0.0.1' || empty($localIp)) {
            $localIp = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        }
        $host = 'http://' . $localIp;

        echo json_encode([
            'success'     => true,
            'local_ip'    => $localIp,
            'server_host' => $host,
            'base_url'    => BASEURL
        ]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KONFIGURASI AI CHATBOT (GEMINI API KEY & MODELING)
    // ─────────────────────────────────────────────────────────────────────────
    public function get_ai_config() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');
        $config = AppSetting::getAiConfig();
        echo json_encode([
            'success'        => true,
            'data'           => $config,
            'default_models' => AppSetting::getDefaultModels()
        ]);
        exit;
    }

    public function fetch_gemini_models() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');
        $apiKey = trim($_POST['api_key'] ?? '');
        $res = AppSetting::fetchGeminiModels($apiKey);
        echo json_encode($res);
        exit;
    }

    public function test_gemini_api() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');
        $apiKey = trim($_POST['api_key'] ?? '');
        $model  = trim($_POST['model'] ?? 'gemini-2.5-flash');
        $res = AppSetting::testGeminiPing($apiKey, $model);
        echo json_encode($res);
        exit;
    }

    public function save_ai_config() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');
        $apiKey = trim($_POST['api_key'] ?? '');
        $model  = trim($_POST['model'] ?? 'gemini-2.5-flash');
        $res = AppSetting::saveAiConfig($apiKey, $model);
        echo json_encode($res);
        exit;
    }
}

