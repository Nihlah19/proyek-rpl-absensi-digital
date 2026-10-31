<?php
class AdminController extends Controller {
    private $laporanModel;

    public function __construct() {
        AuthHelper::checkRole(['admin']);
        $this->laporanModel = $this->model('Laporan');
    }

    public function laporan() {
        $tab = $_GET['tab'] ?? 'guru'; // 'guru' or 'siswa'

        $filters = [
            'dari' => $_GET['dari'] ?? '',
            'sampai' => $_GET['sampai'] ?? '',
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        if ($tab === 'guru') {
            $dataAbsensi = $this->laporanModel->getRekapGuru($filters);
            $totalData = $this->laporanModel->countRekapGuru($filters);
            $stats = $this->laporanModel->getStatsRekapGuru($filters);
            
            $totalDays = ($stats['hadir'] + $stats['terlambat'] + $stats['izin'] + $stats['sakit'] + $stats['alfa']) ?: 1;
            $stats['persentase'] = round((($stats['hadir'] + $stats['terlambat']) / $totalDays) * 100, 2);

        } else {
            $filters['jurusan'] = $_GET['jurusan'] ?? '';
            $filters['tingkat'] = $_GET['tingkat'] ?? '';
            
            $dataAbsensi = $this->laporanModel->getRekapSiswa($filters);
            $totalData = $this->laporanModel->countRekapSiswa($filters);
            $stats = $this->laporanModel->getStatsRekapSiswa($filters);
            
            $totalDays = ($stats['hadir'] + $stats['terlambat'] + $stats['izin'] + $stats['sakit'] + $stats['alfa']) ?: 1;
            $stats['persentase'] = round((($stats['hadir'] + $stats['terlambat']) / $totalDays) * 100, 2);
        }

        $totalPages = ceil($totalData / $limit);

        $data = [
            'judul' => 'Laporan Absensi (Admin)',
            'tab' => $tab,
            'filters' => $filters,
            'dataAbsensi' => $dataAbsensi,
            'stats' => $stats,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalData' => $totalData
        ];

        $this->view('templates/header', $data);
        $this->view('laporan/admin', $data);
        $this->view('templates/footer');
    }

    public function generateAlfa() {
        AuthHelper::checkRole(['admin']);
        
        $absensiModel = $this->model('Absensi');
        $resSiswa = $absensiModel->generateAlfaSiswaHariIni();
        $resGuru  = $absensiModel->generateAlfaGuruHariIni();
        
        $totalAlfa = ($resSiswa['total_alfa'] ?? 0) + ($resGuru['total_alfa'] ?? 0);
        $totalIzin = ($resSiswa['total_izin'] ?? 0) + ($resGuru['total_izin'] ?? 0);
        
        $parts = [];
        if (!empty($resSiswa['message'])) $parts[] = $resSiswa['message'];
        if (!empty($resGuru['message']))  $parts[] = $resGuru['message'];
        $msg = implode(' &bull; ', $parts);
        
        if ($totalAlfa > 0 || $totalIzin > 0) {
            Flasher::setFlash($msg, 'Sukses', 'success');
        } else {
            Flasher::setFlash($msg ?: 'Semua siswa dan guru yang terjadwal sudah memiliki catatan presensi hari ini.', 'Pemberitahuan', 'info');
        }
        
        header('Location: ' . BASEURL . '/dashboard/admin');
        exit;
    }

    public function ai_config() {
        AuthHelper::checkRole(['admin']);
        header('Location: ' . BASEURL . '/dashboard/admin#ai-config');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KONFIGURASI IOT & ESP (TAMPILAN MENU PENUH, BUKAN POPUP)
    // ─────────────────────────────────────────────────────────────────────────
    public function iot() {
        AuthHelper::checkRole(['admin']);
        $iotModel = $this->model('IotDevice');
        $devices = $iotModel->getAllDevices();

        // Cari IP lokal server komputer (bukan 127.0.0.1 agar dapat dijangkau ESP fisik)
        $serverIp = gethostbyname(gethostname());
        if (!$serverIp || $serverIp === '127.0.0.1') {
            $serverIp = $_SERVER['SERVER_ADDR'] ?? '192.168.1.100';
            if ($serverIp === '127.0.0.1' || $serverIp === '::1') {
                $serverIp = '192.168.1.100';
            }
        }

        $serverPort = $_SERVER['SERVER_PORT'] ?? '80';
        $portSuffix = ($serverPort !== '80' && $serverPort !== '443') ? ":{$serverPort}" : '';
        $serverHost = "http://{$serverIp}{$portSuffix}";

        // Tentukan perangkat yang aktif dipilih
        $selectedId = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
        $selectedDevice = null;
        if ($selectedId && !empty($devices)) {
            foreach ($devices as $d) {
                if ((int)$d->id === $selectedId) {
                    $selectedDevice = $d;
                    break;
                }
            }
        }
        if (!$selectedDevice && !empty($devices)) {
            $selectedDevice = $devices[0];
        }

        $data = [
            'judul'             => 'Konfigurasi IoT (ESP RFID)',
            'devices'           => $devices,
            'selectedDevice'    => $selectedDevice,
            'serverIp'          => $serverIp,
            'serverPort'        => $serverPort,
            'serverHost'        => $serverHost,
            'baseUrl'           => BASEURL,
            'fullApiEndpoint'   => rtrim(BASEURL, '/') . '/api/iot/attendance/student',
            'heartbeatEndpoint' => rtrim(BASEURL, '/') . '/api/iot/heartbeat'
        ];

        $this->view('templates/header', $data);
        $this->view('admin/iot', $data);
        $this->view('templates/footer');
    }

    public function iot_ping() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        $ip = trim($_POST['ip'] ?? '');
        $deviceId = (int)($_POST['device_id'] ?? 0);

        if (empty($ip)) {
            echo json_encode(['success' => false, 'message' => 'Alamat IP wajib diisi']);
            exit;
        }

        $cleanIp = preg_replace('#^https?://#', '', $ip);
        $cleanIp = rtrim($cleanIp, '/');

        $startTime = microtime(true);
        $targetUrl = "http://{$cleanIp}/ping";
        
        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 4500);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $latency = round((microtime(true) - $startTime) * 1000);
        $iotModel = $this->model('IotDevice');

        if ($httpCode === 200 && $response) {
            $espData = json_decode($response, true);
            $ssid = $espData['wifi_ssid'] ?? null;
            if ($deviceId > 0) {
                $iotModel->updateHeartbeatWithNetwork($deviceId, $cleanIp, $ssid);
            }
            echo json_encode([
                'success' => true,
                'status'  => 'connected',
                'message' => 'Perangkat ESP Merespon (Connected)',
                'latency' => $latency,
                'ip'      => $cleanIp,
                'esp_data'=> $espData
            ]);
        } else {
            // Coba tes socket TCP port 80 jika endpoint /ping belum di-flash ke firmware
            $socket = @fsockopen($cleanIp, 80, $errno, $errstr, 2.5);
            if ($socket) {
                fclose($socket);
                if ($deviceId > 0) {
                    $iotModel->updateHeartbeatWithNetwork($deviceId, $cleanIp);
                }
                echo json_encode([
                    'success' => true,
                    'status'  => 'connected',
                    'message' => 'Perangkat ESP Online di Port 80 (Connected)',
                    'latency' => $latency,
                    'ip'      => $cleanIp,
                    'esp_data'=> ['mode' => 'client', 'status' => 'connected']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'status'  => 'disconnected',
                    'message' => 'Perangkat tidak merespon (Disconnected: ' . ($curlError ?: 'Request Timeout') . ')',
                    'latency' => $latency,
                    'ip'      => $cleanIp
                ]);
            }
        }
        exit;
    }

    public function iot_save_ip() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 1);
        $ip = trim($_POST['ip'] ?? '');

        if (empty($ip)) {
            echo json_encode(['success' => false, 'message' => 'Alamat IP tidak boleh kosong']);
            exit;
        }

        $cleanIp = preg_replace('#^https?://#', '', $ip);
        $cleanIp = rtrim($cleanIp, '/');

        $iotModel = $this->model('IotDevice');
        $device = $iotModel->getDeviceById($id);
        if (!$device) {
            $device = $iotModel->getByDeviceCode('ESP32_RFID_001');
            if ($device) $id = $device->id;
        }

        $iotModel->updateHeartbeatWithNetwork($id, $cleanIp);

        echo json_encode([
            'success' => true,
            'message' => "Alamat IP {$cleanIp} berhasil disimpan ke sistem",
            'ip'      => $cleanIp
        ]);
        exit;
    }

    public function iot_switch_mode() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        $ip = trim($_POST['ip'] ?? '');
        $mode = trim($_POST['mode'] ?? 'client');

        if (empty($ip)) {
            echo json_encode(['success' => false, 'message' => 'Alamat IP ESP diperlukan']);
            exit;
        }

        $cleanIp = preg_replace('#^https?://#', '', $ip);
        $cleanIp = rtrim($cleanIp, '/');

        $targetUrl = "http://{$cleanIp}/switch_mode?mode={$mode}";
        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            echo json_encode([
                'success' => true,
                'message' => "Perintah ganti ke Mode " . strtoupper($mode) . " berhasil dikirim ke ESP",
                'mode'    => $mode
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Gagal mengirim perintah ganti mode ke ESP di {$cleanIp}. Pastikan ESP menyala dan terhubung.",
                'mode'    => $mode
            ]);
        }
        exit;
    }

    public function iot_save_config() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 1);
        $ip = trim($_POST['ip'] ?? '');
        $ssid = trim($_POST['ssid'] ?? '');
        $pass = trim($_POST['pass'] ?? '');
        $host = trim($_POST['host'] ?? '');

        // Normalisasi host ESP: selalu skema http://host[:port] tanpa subfolder
        if (!empty($host)) {
            $host = preg_replace('#/(AbsensiRFID|web|public).*#i', '', $host);
            if (!str_starts_with($host, 'http://') && !str_starts_with($host, 'https://')) {
                $host = 'http://' . $host;
            }
            $host = rtrim($host, '/');
        }

        $cleanIp = preg_replace('#^https?://#', '', $ip);
        $cleanIp = rtrim($cleanIp, '/');

        $iotModel = $this->model('IotDevice');
        $currentDev = $iotModel->getDeviceById($id);
        if (!$currentDev) {
            $currentDev = $iotModel->getByDeviceCode('ESP32_RFID_001');
            if ($currentDev) $id = $currentDev->id;
        }

        if (empty($ssid) && $currentDev && !empty($currentDev->wifi_ssid)) {
            $ssid = $currentDev->wifi_ssid;
        }

        $deviceName = !empty($_POST['device_name']) ? trim($_POST['device_name']) : ($currentDev->device_name ?? 'RFID Scanner');
        $location   = !empty($_POST['location']) ? trim($_POST['location']) : ($currentDev->location ?? 'Gerbang Utama');
        $status     = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : ($currentDev->status ?? 'active');

        $dataUpdate = [
            'device_name' => $deviceName,
            'location'    => $location,
            'status'      => $status,
            'pending_ssid'=> $ssid,
            'pending_host'=> $host
        ];
        if (!empty($pass)) {
            $dataUpdate['pending_pass'] = $pass;
        }

        $iotModel->updateDeviceDetails($id, $dataUpdate);

        $directSuccess = false;
        if (!empty($cleanIp) && filter_var($cleanIp, FILTER_VALIDATE_IP)) {
            $ch = curl_init("http://{$cleanIp}/save");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'ssid' => $ssid,
                'pass' => $pass,
                'host' => $host
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3000);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200) {
                $directSuccess = true;
                $iotModel->clearPendingConfig($id);
                $iotModel->logActivity($id, "Direct Push Sukses: Mengirim config baru ke {$cleanIp}", 200);
            }
        }

        echo json_encode([
            'success'     => true,
            'direct_push' => $directSuccess,
            'message'     => $directSuccess 
                ? 'Konfigurasi langsung diterima & di-ACC oleh ESP! Alat sedang restart ke jaringan baru.' 
                : 'Konfigurasi disimpan. ESP akan otomatis merespon & menerapkan konfigurasi saat heartbeat berikutnya (maks 10 detik).'
        ]);
        exit;
    }

    public function iot_cancel_pending() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 1);
        $iotModel = $this->model('IotDevice');
        $res = $iotModel->clearPendingConfig($id);

        echo json_encode([
            'success' => (bool)$res,
            'message' => $res ? 'Antrean sinkronisasi pending berhasil dibatalkan.' : 'Gagal membatalkan antrean pending.'
        ]);
        exit;
    }

    public function iot_get_status() {
        AuthHelper::checkRole(['admin']);
        header('Content-Type: application/json');

        $id = (int)($_GET['id'] ?? 0);
        $iotModel = $this->model('IotDevice');
        $device = $id > 0 ? $iotModel->getDeviceById($id) : null;
        if (!$device) {
            $devices = $iotModel->getAllDevices();
            $device = !empty($devices) ? $devices[0] : null;
        }

        echo json_encode([
            'success' => true,
            'device'  => $device
        ]);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PROXY / ALIAS UNTUK FITUR WHATSAPP ADMIN
    // ─────────────────────────────────────────────────────────────────────────
    public function wa_notif_masuk() {
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    public function wa_notif_pulang() {
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    public function wa_config() {
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    public function whatsapp() {
        header('Location: ' . BASEURL . '/wa');
        exit;
    }
}
