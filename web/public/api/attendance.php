<?php
ob_start();
require_once __DIR__ . '/../../app/init.php';
require_once __DIR__ . '/../../app/models/Absensi.php';
require_once __DIR__ . '/../../app/models/Guru.php';
require_once __DIR__ . '/../../app/models/LogError.php';

header('Content-Type: application/json');
$request_id = uniqid('req_');

function respondJson($success, $message, $data = [], $http_code = 200) {
    global $request_id;
    $output = ob_get_clean(); // get any stray output (warnings, notices)
    if (!empty($output)) {
        error_log("STRAY OUTPUT: " . $output);
    }
    http_response_code($http_code);
    
    $json = json_encode([
        'success'    => $success,
        'message'    => $message,
        'data'       => $data,
        'request_id' => $request_id,
        'debug_output' => $output // send it to frontend for debugging
    ]);
    file_put_contents(__DIR__ . '/debug.log', "[$request_id] Response: $json\n", FILE_APPEND);
    echo $json;
    exit;
}

function respondWithError($message, $jenis_error, $barcode_input = null, $http_code = 400, $extraData = []) {
    global $request_id;
    $output = ob_get_clean();
    if (!empty($output)) error_log("STRAY OUTPUT: " . $output);
    try {
        $logModel = new LogError();
        $logModel->insertLog([
            'request_id'   => $request_id,
            'sumber'       => 'scan_kamera',
            'barcode_input'=> $barcode_input,
            'jenis_error'  => $jenis_error,
            'pesan_error'  => $message
        ]);
    } catch (Exception $ex) { /* log gagal tidak kritis */ }
    http_response_code($http_code);
    $res = [
        'success'      => false,
        'message'      => $message,
        'error_type'   => $jenis_error,
        'request_id'   => $request_id,
        'debug_output' => $output
    ];
    if (!empty($extraData)) {
        $res['data'] = $extraData;
    }
    $json = json_encode($res);
    file_put_contents(__DIR__ . '/debug.log', "[$request_id] Error Response: $json\n", FILE_APPEND);
    echo $json;
    exit;
}

try {
    // ── 1 & 2. Cek Akses ──
    // Diizinkan untuk Admin, Guru, atau akses Publik (Kiosk) dari Monitor.
    $userRole = SessionHelper::get('role');
    $is_logged_in = SessionHelper::get('user_id') ? true : false;

    // ── 3. Ambil payload ──
    $rawBody = file_get_contents('php://input');
    if (empty($rawBody) && php_sapi_name() === 'cli') {
        $rawBody = file_get_contents('php://stdin');
    }
    $payload = json_decode($rawBody, true);

    if (empty($payload['barcode']) || !is_string($payload['barcode'])) {
        respondWithError('Barcode tidak ditemukan dalam permintaan.', 'missing_barcode');
    }

    $barcode = trim($payload['barcode']);
    $source  = $payload['source'] ?? '';

    // Cek apakah scan berasal dari Monitor Guru (Live Monitor di sekolah/kiosk)
    $is_monitor = false;
    if (in_array($source, ['monitor', 'monitor_guru', 'kiosk'])) {
        $salt = defined('DB_NAME') ? DB_NAME : 'absensi_rfid';
        $expectedMonitorToken = hash_hmac('sha256', 'kiosk_guru_' . date('Y-m-d'), $salt);
        $inputMonitorToken    = $payload['monitor_token'] ?? ($_SESSION['monitor_token'] ?? '');
        
        // Hanya izinkan jika token monitor valid atau user adalah admin
        if ((!empty($inputMonitorToken) && hash_equals($expectedMonitorToken, $inputMonitorToken)) || $userRole === 'admin') {
            $is_monitor = true;
        } else {
            respondWithError('Presensi ditolak. Token otorisasi monitor tidak valid atau telah kedaluwarsa.', 'invalid_monitor_token', $barcode, 403);
        }
    }

    // Jika bukan dari monitor kiosk, pengguna WAJIB dalam keadaan login
    if (!$is_monitor && !$is_logged_in) {
        respondWithError('Presensi ditolak. Sesi login telah berakhir atau Anda belum login. Silakan login terlebih dahulu.', 'unauthorized', $barcode, 401);
    }

    // ── 4. Cari guru berdasarkan barcode yang di-scan ──
    $absensiModel = new Absensi();
    $guruModel    = new Guru();

    $guru = $absensiModel->getGuruByBarcode($barcode);

    if (!$guru) {
        respondWithError('Barcode tidak terdaftar dalam sistem.', 'barcode_not_found', $barcode, 404);
    }

    // Jika yang login adalah guru, wali kelas, atau kepsek (bukan admin) dan BUKAN dari monitor guru, pastikan barcode miliknya sendiri
    if (!$is_monitor && in_array($userRole, ['guru', 'wali_kelas', 'walikelas', 'kepsek'])) {
        $guruLogin = $guruModel->getByUserId($_SESSION['user_id'] ?? 0);
        if ($guruLogin && $guruLogin->id != $guru->id) {
            respondWithError('Barcode tidak cocok dengan akun Anda. Scan barcode milik Anda sendiri.', 'barcode_mismatch', $barcode, 403);
        }
    }

    // ── 5. Cek jadwal guru (Tolak jika tidak sesuai jadwal) ──
    $tanggal_hari_ini = date('Y-m-d');
    $jam_sekarang     = date('H:i:s');

    $hariMap = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    $hariIni = $hariMap[date('l')] ?? date('l');

    $jadwal = $absensiModel->getJadwalGuruHariIni($guru->id);

    // Jika guru TIDAK memiliki jadwal mengajar/aktif hari ini: TOLAK!
    if (!$jadwal || empty($jadwal->is_active)) {
        respondWithError('Presensi ditolak. Anda tidak memiliki jadwal dinas/mengajar aktif pada hari ' . $hariIni . '.', 'no_schedule', $barcode, 422, [
            'hari' => $hariIni,
            'guru' => [
                'nama' => $guru->name ?? $guru->nama ?? null,
                'nip'  => $guru->nip ?? null
            ]
        ]);
    }

    // ── 5.1 Cek status Izin/Sakit hari ini (Tolak jika sedang Izin/Sakit) ──
    $absensiExisting = $absensiModel->cekAbsensiGuru($guru->id, $tanggal_hari_ini);
    if ($absensiExisting && in_array(strtolower($absensiExisting->status), ['izin', 'sakit'])) {
        $statusLabel = strtoupper($absensiExisting->status);
        $ketText     = !empty($absensiExisting->keterangan) ? ' (Keterangan: ' . $absensiExisting->keterangan . ')' : '';
        respondWithError("Presensi ditolak. Anda telah tercatat {$statusLabel} untuk hari ini{$ketText}. Tidak perlu melakukan presensi.", 'already_on_leave', $barcode, 422, [
            'type'       => 'leave_active',
            'status'     => $absensiExisting->status,
            'keterangan' => $absensiExisting->keterangan,
            'guru'       => [
                'nama' => $guru->name ?? $guru->nama ?? null,
                'nip'  => $guru->nip ?? null
            ]
        ]);
    }

    // Jika scan masuk dilakukan sebelum jam mulai absensi dibuka: TOLAK!
    if (!empty($jadwal->jam_mulai_absensi) && $jadwal->jam_mulai_absensi !== '00:00:00' && $jam_sekarang < $jadwal->jam_mulai_absensi) {
        respondWithError('Presensi masuk ditolak. Presensi untuk jadwal Anda baru dibuka pukul ' . substr($jadwal->jam_mulai_absensi, 0, 5) . ' WIB (Jadwal masuk: ' . substr($jadwal->jam_datang, 0, 5) . ' WIB).', 'early_scan_blocked', $barcode, 422, [
            'jam_mulai_absensi' => $jadwal->jam_mulai_absensi,
            'jam_datang'        => $jadwal->jam_datang,
            'guru' => [
                'nama' => $guru->name ?? $guru->nama ?? null,
                'nip'  => $guru->nip ?? null
            ]
        ]);
    }

    // ── 5.5 Validasi Geolokasi & Keakuratan GPS (Maksimal 100 Meter) ──
    // Berlaku untuk SEMUA scan: baik scan langsung di guru maupun scan QR di monitor guru dashboard admin
    if (!function_exists('haversine')) {
        function haversine($lat1, $lng1, $lat2, $lng2) {
            $R = 6371000; // meters
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);
            $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
            return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
        }
    }

    $dist = null;
    $accuracy = null;

    // Presensi wajib menyertakan koordinat GPS (baik scan personal maupun monitor)
    if (!isset($payload['lat']) || !isset($payload['lng']) || $payload['lat'] === '' || $payload['lng'] === '') {
        respondWithError('Presensi ditolak. Lokasi GPS diperlukan untuk presensi guru (baik scan langsung maupun scan QR monitor). Pastikan GPS perangkat aktif dan izinkan browser mengakses lokasi.', 'location_required', $barcode, 400);
    }

    $lat = floatval($payload['lat']);
    $lng = floatval($payload['lng']);
    $accuracy = isset($payload['accuracy']) ? floatval($payload['accuracy']) : null;

    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || ($lat == 0 && $lng == 0)) {
        respondWithError('Presensi ditolak. Koordinat GPS tidak valid.', 'invalid_coordinates', $barcode, 400);
    }

    // Pastikan keakuratan lokasi:
    // Jika margin error GPS > 80 meter, tolak karena posisi tidak presisi (menggunakan cell-tower atau perkiraan IP)
    $maxAllowedAccuracy = 80; // meter
    if ($accuracy !== null && $accuracy > $maxAllowedAccuracy) {
        respondWithError('Presensi ditolak. Keakuratan GPS tidak memadai (akurasi: ±' . round($accuracy) . 'm, batas maksimal ' . $maxAllowedAccuracy . 'm). Silakan pindah ke area terbuka dan gunakan mode GPS Berakurasi Tinggi (High Accuracy).', 'low_gps_accuracy', $barcode, 422, [
            'accuracy'     => round($accuracy),
            'max_accuracy' => $maxAllowedAccuracy
        ]);
    }

    $dist = haversine($lat, $lng, SCHOOL_LAT, SCHOOL_LNG);
    $maxRadius = defined('SCHOOL_RADIUS_METER') ? (int)SCHOOL_RADIUS_METER : 100;
    if ($maxRadius > 100 || $maxRadius <= 0) {
        $maxRadius = 100; // Maksimal 100 meter sesuai instruksi
    }

    if ($dist > $maxRadius) {
        respondWithError('Presensi ditolak. Lokasi Anda berada di luar area sekolah (' . round($dist) . ' meter dari sekolah, batas maksimal ' . $maxRadius . 'm).', 'out_of_radius', $barcode, 403, [
            'distance'   => round($dist),
            'max_radius' => $maxRadius,
            'accuracy'   => $accuracy !== null ? round($accuracy) : null
        ]);
    }

    // ── 6. Cek absensi existing ──
    $absensiExisting = $absensiModel->cekAbsensiGuru($guru->id, $tanggal_hari_ini);

    if (!$absensiExisting || $absensiExisting->status === 'gagal') {
        // ── Scan pertama ──
        // Jika scan pertama saat/setelah jam pulang: catat sebagai absen pulang
        if ($jam_sekarang >= $jadwal->jam_pulang) {
            $dataInsert = [
                'guru_id'          => $guru->id,
                'tanggal'          => $tanggal_hari_ini,
                'jam_datang'       => null,
                'jam_pulang'       => $jam_sekarang,
                'status'           => 'alfa',
                'keterangan'       => 'Hanya absen pulang (tidak scan masuk)',
                'source_device_id' => null,
            ];

            $absensiModel->insertAbsensiGuru($dataInsert);

            respondJson(true, 'Absensi pulang guru berhasil', [
                'type'       => 'check_out',
                'status'     => 'alfa',
                'jam_pulang' => $jam_sekarang,
                'guru'       => [
                    'nama' => $guru->name ?? $guru->nama ?? null,
                    'nip'  => $guru->nip ?? null,
                ]
            ]);
        }

        // Absen Masuk: <= batas_terlambat -> hadir, <= jamMaksTerlambat (+90m) -> terlambat, > jamMaksTerlambat -> alfa
        $waktuBatasTerlambat = strtotime($jadwal->batas_terlambat);
        $waktuMaksTerlambat  = $waktuBatasTerlambat ? ($waktuBatasTerlambat + (90 * 60)) : strtotime('09:00:00');
        $jamMaksTerlambat    = date('H:i:s', $waktuMaksTerlambat);

        if ($jadwal->jam_pulang < $jamMaksTerlambat && $jadwal->jam_pulang > $jadwal->batas_terlambat) {
            $jamMaksTerlambat = $jadwal->jam_pulang;
        }

        $labelMaksTerlambat = substr($jamMaksTerlambat, 0, 5);

        if ($jam_sekarang <= $jadwal->batas_terlambat) {
            $status     = 'hadir';
            $msgStatus  = 'Hadir Tepat Waktu';
            $keterangan = null;
        } elseif ($jam_sekarang <= $jamMaksTerlambat) {
            $status     = 'terlambat';
            $msgStatus  = 'Terlambat Masuk';
            $keterangan = 'Terlambat datang';
        } else {
            $status     = 'alfa';
            $msgStatus  = "Terlambat > {$labelMaksTerlambat} (Status Alpa)";
            $keterangan = "Terlambat > {$labelMaksTerlambat} (Perlu Verifikasi Kepsek/Piket)";
        }

        $dataInsert = [
            'guru_id'          => $guru->id,
            'tanggal'          => $tanggal_hari_ini,
            'jam_datang'       => $jam_sekarang,
            'status'           => $status,
            'keterangan'       => $keterangan,
            'source_device_id' => null,
        ];

        if ($absensiExisting && $absensiExisting->status === 'gagal') {
            // Update the existing gagal record
            $absensiModel->db->query("UPDATE absensi_guru SET jam_datang = :jam_datang, status = :status, keterangan = :keterangan WHERE id = :id");
            $absensiModel->db->bind(':jam_datang', $jam_sekarang);
            $absensiModel->db->bind(':status', $status);
            $absensiModel->db->bind(':keterangan', $keterangan);
            $absensiModel->db->bind(':id', $absensiExisting->id);
            $absensiModel->db->execute();
        } else {
            try {
                $absensiModel->insertAbsensiGuru($dataInsert);
            } catch (PDOException $pex) {
                // Tangani double-tap simultan
                $recheck = $absensiModel->cekAbsensiGuru($guru->id, $tanggal_hari_ini);
                if ($recheck && $recheck->id) {
                    respondJson(true, 'Absensi masuk guru berhasil (' . $msgStatus . ')', [
                        'type'       => 'check_in',
                        'status'     => $recheck->status,
                        'jam_datang' => $recheck->jam_datang,
                        'jam_pulang' => $recheck->jam_pulang,
                        'distance'   => $dist !== null ? round($dist) : null,
                        'accuracy'   => $accuracy !== null ? round($accuracy) : null,
                        'guru'       => [
                            'nama' => $guru->name ?? $guru->nama ?? null,
                            'nip'  => $guru->nip ?? null,
                        ]
                    ], 200);
                }
                throw $pex;
            }
        }

        $msg = 'Absensi masuk guru berhasil (' . $msgStatus . ')';

        respondJson(true, $msg, [
            'type'       => 'check_in',
            'status'     => $status,
            'jam_datang' => $jam_sekarang,
            'jam_pulang' => null,
            'distance'   => $dist !== null ? round($dist) : null,
            'accuracy'   => $accuracy !== null ? round($accuracy) : null,
            'guru'       => [
                'nama' => $guru->name ?? $guru->nama ?? null,
                'nip'  => $guru->nip ?? null,
            ]
        ], 200);
    }

    if ($absensiExisting->jam_pulang !== null) {
        respondJson(false, 'Absensi hari ini sudah lengkap (masuk dan pulang sudah tercatat).', [
            'type' => 'already_scanned',
            'guru' => ['nama' => $guru->name ?? $guru->nama ?? null, 'nip' => $guru->nip ?? null]
        ], 409);
    }

    // ── Scan kedua: Absen Pulang ──
    if ($jam_sekarang < $jadwal->jam_pulang) {
        respondJson(false, 'Belum waktunya absen pulang (Jadwal pulang jam ' . substr($jadwal->jam_pulang, 0, 5) . ')', [
            'type' => 'early_leave_blocked',
            'jam_pulang_jadwal' => $jadwal->jam_pulang,
            'guru' => ['nama' => $guru->name ?? $guru->nama ?? null, 'nip' => $guru->nip ?? null]
        ], 400);
    }



    $absensiModel->updateAbsensiPulangGuru($absensiExisting->id, $jam_sekarang);

    respondJson(true, 'Absensi pulang guru berhasil', [
        'type'       => 'check_out',
        'jam_pulang' => $jam_sekarang,
        'guru'       => [
            'nama' => $guru->name ?? $guru->nama ?? null,
            'nip'  => $guru->nip ?? null,
        ]
    ]);

} catch (Exception $e) {
    try {
        $logModel = new LogError();
        $logModel->insertLog([
            'request_id'   => $request_id,
            'sumber'       => 'backend',
            'barcode_input'=> $payload['barcode'] ?? null,
            'jenis_error'  => 'exception',
            'pesan_error'  => $e->getMessage() . "\n" . $e->getTraceAsString()
        ]);
    } catch (Exception $ex) { /* log gagal tidak kritis */ }

    http_response_code(500);
    echo json_encode([
        'success'    => false,
        'message'    => 'Terjadi kesalahan internal pada server. Silakan coba lagi.',
        'request_id' => $request_id
    ]);
    exit;
}
