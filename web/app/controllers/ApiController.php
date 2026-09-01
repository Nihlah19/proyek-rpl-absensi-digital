<?php
class ApiController extends Controller {

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

    private function getPayload() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }

    private function validateDevice($payload) {
        if (!isset($payload['device_code'])) {
            $this->respond(false, 'Missing device_code', [], 400);
        }

        $headers = getallheaders();
        $apiKey = $headers['X-Api-Key'] ?? ($headers['x-api-key'] ?? null);
        
        if (!$apiKey) {
            $this->respond(false, 'Missing API Key in headers', [], 401);
        }

        $deviceModel = $this->model('IotDevice');
        $device = $deviceModel->getByDeviceCode($payload['device_code']);

        if (!$device) {
            $this->respond(false, 'Device not found', [], 404);
        }

        if ($device->status !== 'active') {
            $this->respond(false, 'Device is inactive', [], 403);
        }

        if (!password_verify($apiKey, $device->api_key_hash)) {
            // Because our seed uses generic password 'password', testing is easy
            $this->respond(false, 'Invalid API Key', [], 401);
        }

        return $device;
    }

    public function iot($action = '', $type = '') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(false, 'Method Not Allowed', [], 405);
        }

        $payload = $this->getPayload();
        
        if ($action === 'heartbeat') {
            $this->handleHeartbeat($payload);
        } elseif ($action === 'attendance') {
            if ($type === 'student') {
                $this->handleStudentAttendance($payload);
            } elseif ($type === 'teacher') {
                $this->handleTeacherAttendance($payload);
            } else {
                $this->respond(false, 'Unknown attendance type', [], 400);
            }
        } else {
            $this->respond(false, 'Unknown action', [], 404);
        }
    }

    private function handleHeartbeat($payload) {
        $device = $this->validateDevice($payload);
        $deviceModel = $this->model('IotDevice');
        
        $ip_address = $payload['ip_address'] ?? $_SERVER['REMOTE_ADDR'];
        $deviceModel->updateHeartbeat($device->id, $ip_address);
        
        $deviceModel->logActivity($device->id, json_encode($payload), 200);

        $this->respond(true, 'Heartbeat acknowledged', ['timestamp' => date('Y-m-d H:i:s')]);
    }

    private function handleStudentAttendance($payload) {
        $device = $this->validateDevice($payload);
        $deviceModel = $this->model('IotDevice');

        if ($device->device_type !== 'RFID_SISWA') {
            $deviceModel->logActivity($device->id, json_encode($payload), 403);
            $this->respond(false, 'Invalid device type for student', [], 403);
        }

        if (empty($payload['rfid_uid'])) {
            $deviceModel->logActivity($device->id, json_encode($payload), 400);
            $this->respond(false, 'Missing rfid_uid', [], 400);
        }

        $absensiModel = $this->model('Absensi');
        
        // 3. Cari Siswa
        $siswa = $absensiModel->getSiswaByRFID($payload['rfid_uid']);
        if (!$siswa) {
            $deviceModel->logActivity($device->id, json_encode($payload), 404);
            $this->respond(false, 'RFID tidak terdaftar', [], 404);
        }

        // 4. Ambil jadwal
        $jadwal = $absensiModel->getJadwalHariIni();
        if (!$jadwal) {
            $deviceModel->logActivity($device->id, json_encode($payload), 400);
            $this->respond(false, 'Tidak ada jadwal sekolah hari ini', [], 400);
        }

        $tanggal_hari_ini = date('Y-m-d');
        $jam_sekarang = date('H:i:s');
        
        // Cek jika tap terlalu pagi dari jam_mulai_absensi
        if ($jam_sekarang < $jadwal->jam_mulai_absensi) {
            $deviceModel->logActivity($device->id, json_encode($payload), 400);
            $this->respond(false, 'Absensi belum dibuka', [], 400);
        }

        // 5. Cek duplikasi
        $cek = $absensiModel->cekAbsensiSiswa($siswa->id, $tanggal_hari_ini);
        if ($cek) {
            $deviceModel->logActivity($device->id, json_encode($payload), 409);
            $this->respond(false, 'Siswa sudah absen hari ini', [], 409);
        }

        // 6. Tentukan status
        $status = ($jam_sekarang <= $jadwal->batas_terlambat) ? 'hadir' : 'terlambat';

        // 7. Simpan
        $dataInsert = [
            'siswa_id' => $siswa->id,
            'tanggal' => $tanggal_hari_ini,
            'jam_datang' => $jam_sekarang,
            'jam_pulang' => $jadwal->jam_pulang, // Pulang otomatis dari jadwal
            'status' => $status,
            'source_device_id' => $device->id
        ];

        $absensiModel->insertAbsensiSiswa($dataInsert);
        $deviceModel->logActivity($device->id, json_encode($payload), 200);

        $this->respond(true, 'Absensi siswa berhasil', [
            'nama'       => $siswa->name ?? ('Siswa #' . $siswa->id),
            'kelas'      => $siswa->nama_kelas ?? '',
            'status'     => $status,
            'jam_datang' => $jam_sekarang,
            'jam_pulang' => $jadwal->jam_pulang,
            'code'       => 'check_in',
            'att_status' => ($status === 'hadir') ? 'Hadir' : 'Terlambat',
            'time'       => substr($jam_sekarang, 0, 5),
        ]);
    }

    private function handleTeacherAttendance($payload) {
        $device = $this->validateDevice($payload);
        $deviceModel = $this->model('IotDevice');

        if (empty($payload['barcode'])) {
            $deviceModel->logActivity($device->id, json_encode($payload), 400);
            $this->respond(false, 'Missing barcode', [], 400);
        }

        $absensiModel = $this->model('Absensi');
        
        $guru = $absensiModel->getGuruByBarcode($payload['barcode']);
        if (!$guru) {
            $deviceModel->logActivity($device->id, json_encode($payload), 404);
            $this->respond(false, 'Barcode tidak terdaftar', [], 404);
        }

        // ── Gunakan jadwal spesifik per guru ──
        $jadwal = $absensiModel->getJadwalGuruHariIni($guru->id);

        // Jika guru TIDAK punya jadwal aktif hari ini:
        // → Jangan buat absensi
        // → Jangan anggap alfa
        // → Jangan hitung terlambat
        // → Guru tidak wajib scan
        if (!$jadwal) {
            $deviceModel->logActivity($device->id, json_encode($payload), 200);
            $this->respond(true, 'Anda tidak memiliki jadwal hari ini. Tidak perlu absen.', [
                'has_schedule' => false
            ]);
        }

        $tanggal_hari_ini = date('Y-m-d');
        $jam_sekarang = date('H:i:s');

        $cek = $absensiModel->cekAbsensiGuru($guru->id, $tanggal_hari_ini);

        if (!$cek) {
            // ── Scan pertama → Absen Datang ──
            $status = ($jam_sekarang <= $jadwal->batas_terlambat) ? 'hadir' : 'terlambat';

            $dataInsert = [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal_hari_ini,
                'jam_datang' => $jam_sekarang,
                'status' => $status,
                'source_device_id' => $device->id
            ];

            $absensiModel->insertAbsensiGuru($dataInsert);
            $deviceModel->logActivity($device->id, json_encode($payload), 200);

            $this->respond(true, 'Absensi masuk guru berhasil', [
                'type'       => 'check_in',
                'status'     => $status,
                'jam_datang' => $jam_sekarang
            ]);

        } else {
            // ── Scan kedua → Absen Pulang ──
            if ($cek->jam_pulang !== null) {
                $deviceModel->logActivity($device->id, json_encode($payload), 409);
                $this->respond(false, 'Guru sudah absen pulang hari ini', [], 409);
            }

            $absensiModel->updateAbsensiPulangGuru($cek->id, $jam_sekarang);
            $deviceModel->logActivity($device->id, json_encode($payload), 200);

            $this->respond(true, 'Absensi pulang guru berhasil', [
                'type'       => 'check_out',
                'jam_pulang' => $jam_sekarang
            ]);
        }
    }
}
