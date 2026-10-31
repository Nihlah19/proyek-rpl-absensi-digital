<?php
class RfidController extends Controller {

    private function respond($success, $message, $data = [], $http_code = 200) {
        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }

    public function index() {
        AuthHelper::checkRole(['admin']);
        $model = $this->model('RfidCard');
        
        $db = new Database();
        $db->query("SELECT s.id, u.name, s.nis, k.nama_kelas 
                    FROM siswa s
                    JOIN users u ON u.id = s.user_id
                    LEFT JOIN kelas k ON k.id = s.kelas_id
                    LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active'
                    WHERE r.id IS NULL
                    ORDER BY k.nama_kelas, u.name");
        $students = $db->resultSet();

        $iotModel = $this->model('IotDevice');
        $devices = $iotModel->getAllDevices();
        $activeDevice = !empty($devices) ? $devices[0] : null;

        $data = [
            'judul'        => 'Manajemen RFID',
            'cards'        => $model->getAllCards(),
            'students'     => $students,
            'activeDevice' => $activeDevice
        ];

        $this->view('templates/header', $data);
        $this->view('rfid/index', $data);
        $this->view('templates/footer');
    }

    public function get_device_mode() {
        AuthHelper::checkRole(['admin']);
        $iotModel = $this->model('IotDevice');
        $devices = $iotModel->getAllDevices();
        $device = !empty($devices) ? $devices[0] : null;

        $this->respond(true, 'Status mode perangkat', [
            'app_mode'         => $device->app_mode ?? 'absen',
            'iot_status'       => $device->iot_status ?? 'offline',
            'ip_address'       => $device->ip_address ?? '',
            'last_seen'        => $device->last_seen ?? '-',
            'last_scanned_uid' => $device->last_scanned_uid ?? '',
            'last_scan_time'   => $device->last_scan_time ?? ''
        ]);
    }

    public function set_device_mode() {
        AuthHelper::checkRole(['admin']);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->respond(false, 'Method Not Allowed', [], 405);
        }

        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);
        if (!$payload) {
            $payload = $_POST;
        }

        $mode = $payload['mode'] ?? 'absen';
        if (!in_array($mode, ['absen', 'regis'])) {
            $this->respond(false, 'Mode tidak valid. Gunakan absen atau regis', [], 400);
        }

        $iotModel = $this->model('IotDevice');
        $devices = $iotModel->getAllDevices();
        $device = !empty($devices) ? $devices[0] : null;

        if (!$device) {
            $this->respond(false, 'Perangkat IoT tidak ditemukan', [], 404);
        }

        $iotModel->setAppMode($device->id, $mode);
        $iotModel->logActivity($device->id, "Mode perangkat diubah ke: " . strtoupper($mode), 200);

        // Kirim push HTTP langsung ke IP ESP agar pergantian mode instan (<100ms)
        $directSuccess = false;
        $cleanIp = preg_replace('#^https?://#', '', $device->ip_address ?? '');
        $cleanIp = rtrim($cleanIp, '/');

        if (!empty($cleanIp) && filter_var($cleanIp, FILTER_VALIDATE_IP)) {
            $ch = curl_init("http://{$cleanIp}/set_mode?mode={$mode}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1500);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200) {
                $directSuccess = true;
            }
        }

        $labelMode = ($mode === 'regis') ? 'Mode Tambah Kartu' : 'Mode Scan Absensi';
        $this->respond(true, "Berhasil beralih ke {$labelMode}", [
            'mode'        => $mode,
            'direct_push' => $directSuccess,
            'ip'          => $cleanIp
        ]);
    }

    public function get_latest_scan() {
        AuthHelper::checkRole(['admin']);
        $db = new Database();
        $db->query("SELECT id, device_code, ip_address, app_mode, last_scanned_uid, last_scan_time 
                    FROM iot_devices 
                    WHERE device_code = 'ESP32_RFID_001' 
                      AND last_scan_time >= DATE_SUB(NOW(), INTERVAL 60 SECOND)");
        $result = $db->single();

        if ($result && !empty($result->last_scanned_uid)) {
            $model = $this->model('RfidCard');
            $existing = $model->getCardByUid($result->last_scanned_uid);
            
            $owner = null;
            if ($existing) {
                // Get student name for the existing card
                $db->query("SELECT u.name FROM users u JOIN siswa s ON s.user_id = u.id WHERE s.id = :sid");
                $db->bind(':sid', $existing->student_id);
                $owner_data = $db->single();
                $owner = $owner_data ? $owner_data->name : 'Unknown';
            }

            $this->respond(true, 'UID terbaru', [
                'uid'       => $result->last_scanned_uid,
                'used'      => $existing ? true : false,
                'owner'     => $owner,
                'scan_time' => $result->last_scan_time,
                'app_mode'  => $result->app_mode ?? 'absen'
            ]);
        } else {
            $this->respond(false, 'Belum ada scan', []);
        }
    }

    public function detail($id = null) {
        AuthHelper::checkRole(['admin']);
        if (!$id) {
            $this->respond(false, 'Missing ID', [], 400);
        }
        $model = $this->model('RfidCard');
        $card = $model->getCardById($id);
        if ($card) {
            $this->respond(true, 'Card details', $card);
        } else {
            $this->respond(false, 'Card not found', [], 404);
        }
    }

    public function assign() {
        AuthHelper::checkRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Method Not Allowed', [], 405);
        }

        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);

        if (empty($payload['uid']) || empty($payload['student_id'])) {
            $this->respond(false, 'Missing UID or student_id', [], 400);
        }

        $uid = trim($payload['uid']);
        $student_id = (int)$payload['student_id'];
        $force_move = isset($payload['force_move']) ? (bool)$payload['force_move'] : false;

        $model = $this->model('RfidCard');
        
        // Cek duplikasi UID
        $existingCard = $model->getCardByUid($uid);
        if ($existingCard) {
            if (!$force_move) {
                $this->respond(false, 'UID sudah terdaftar', [], 409);
            } else {
                // Delete existing relation first
                $model->deleteCard($existingCard->id);
            }
        }

        // Cek apakah siswa sudah punya kartu
        $existingStudentCard = $model->getCardByStudentId($student_id);
        if ($existingStudentCard) {
            if (!$force_move) {
                $this->respond(false, 'Siswa sudah memiliki kartu yang terdaftar', [], 409);
            } else {
                // Delete existing card for student
                $model->deleteCard($existingStudentCard->id);
            }
        }

        // Cek apakah siswa ada
        $userModel = $this->model('UserManagement');
        $siswa = $userModel->getSiswaById($student_id);
        if (!$siswa) {
            $this->respond(false, 'Siswa tidak ditemukan', [], 404);
        }

        // Assign card
        if ($model->assignCard($uid, $student_id)) {
            $this->respond(true, 'Kartu berhasil dihubungkan ke siswa', []);
        } else {
            $this->respond(false, 'Gagal menghubungkan kartu', [], 500);
        }
    }

    public function update_status($id = null) {
        AuthHelper::checkRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->respond(false, 'Method Not Allowed', [], 405);
        }

        if (!$id) {
            $this->respond(false, 'Missing ID', [], 400);
        }

        $json = file_get_contents('php://input');
        $payload = json_decode($json, true);

        if (!isset($payload['status']) || !in_array($payload['status'], ['active', 'inactive'])) {
            $this->respond(false, 'Invalid status', [], 400);
        }

        $model = $this->model('RfidCard');
        $card = $model->getCardById($id);
        if (!$card) {
            $this->respond(false, 'Card not found', [], 404);
        }

        if ($model->updateStatus($id, $payload['status'])) {
            $this->respond(true, 'Status kartu berhasil diperbarui', []);
        } else {
            $this->respond(false, 'Gagal memperbarui status', [], 500);
        }
    }

    public function delete($id = null) {
        AuthHelper::checkRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->respond(false, 'Method Not Allowed', [], 405);
        }

        if (!$id) {
            $this->respond(false, 'Missing ID', [], 400);
        }

        $model = $this->model('RfidCard');
        $card = $model->getCardById($id);
        if (!$card) {
            $this->respond(false, 'Card not found', [], 404);
        }

        if ($model->deleteCard($id)) {
            $this->respond(true, 'Hubungan kartu berhasil dihapus', []);
        } else {
            $this->respond(false, 'Gagal menghapus kartu', [], 500);
        }
    }
}
