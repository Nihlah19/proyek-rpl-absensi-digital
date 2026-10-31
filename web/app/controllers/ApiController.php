<?php
class ApiController extends Controller {

    private function respond($success, $message, $data = [], $http_code = 200) {
        // Hapus output sebelumnya jika ada error PHP yang bocor
        if (ob_get_level()) ob_clean();

        http_response_code($http_code);
        header('Content-Type: application/json');
        header('Connection: close');
        
        $response = json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        header('Content-Length: ' . strlen($response));
        echo $response;
        
        if (session_id()) session_write_close();
        @ob_flush();
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        
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
        } elseif ($action === 'check') {
            $this->handleCheckRfid($payload);
        } else {
            $this->respond(false, 'Unknown action', [], 404);
        }
    }

    private function handleCheckRfid($payload) {
        $device = $this->validateDevice($payload);
        
        if (empty($payload['rfid_uid'])) {
            $this->respond(false, 'Missing rfid_uid', [], 400);
        }

        // Simpan scan terakhir untuk polling di dashboard (Mode Tambah Kartu)
        $deviceModel = $this->model('IotDevice');
        $deviceModel->updateLastScan($device->id, $payload['rfid_uid']);
        $deviceModel->logActivity($device->id, "Check RFID / Regis Scan: " . $payload['rfid_uid'], 200);

        $absensiModel = $this->model('Absensi');
        $siswa = $absensiModel->getSiswaByRFID($payload['rfid_uid']);
        
        if (!$siswa) {
            $this->respond(true, 'UID diterima (Kartu Baru Belum Terdaftar)', [
                'uid'   => $payload['rfid_uid'],
                'used'  => false,
                'owner' => null,
                'kelas' => null
            ]);
        }

        $this->respond(true, 'UID diterima (Kartu Sudah Dipakai)', [
            'uid'   => $payload['rfid_uid'],
            'used'  => true,
            'owner' => $siswa->name,
            'kelas' => $siswa->nama_kelas
        ]);
    }

    private function handleHeartbeat($payload) {
        $device = $this->validateDevice($payload);
        $deviceModel = $this->model('IotDevice');
        
        $ip_address = $payload['ip_address'] ?? $_SERVER['REMOTE_ADDR'];
        $wifi_ssid  = $payload['wifi_ssid'] ?? null;
        $deviceModel->updateHeartbeatWithNetwork($device->id, $ip_address, $wifi_ssid);
        
        $deviceModel->logActivity($device->id, json_encode($payload), 200);

        $freshDevice = $deviceModel->getDeviceById($device->id);
        $appMode = $freshDevice->app_mode ?? 'absen';

        $responseData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'app_mode'  => $appMode
        ];

        // Cek jika admin mengubah konfigurasi jaringan dari dashboard admin (OTA remote config)
        if ($freshDevice && (!empty($freshDevice->pending_host) || !empty($freshDevice->pending_ssid) || !empty($freshDevice->pending_pass))) {
            $targetSsid = !empty($freshDevice->pending_ssid) ? $freshDevice->pending_ssid : ($freshDevice->wifi_ssid ?? '');
            $responseData['config_update'] = [
                'wifi_ssid' => $targetSsid,
                'wifi_pass' => $freshDevice->pending_pass ?? '',
                'api_host'  => $freshDevice->pending_host ?? ''
            ];
            $deviceModel->logActivity($device->id, 'OTA Config dikirim ke alat: SSID=' . $targetSsid, 200);
            $deviceModel->clearPendingConfig($device->id);
        }

        $this->respond(true, 'Heartbeat acknowledged', $responseData);
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

        // PENTING: Selalu rekam scan terakhir ke database agar web dashboard dapat menangkap UID secara instan
        $deviceModel->updateLastScan($device->id, $payload['rfid_uid']);

        // JIKA ALAT SEDANG DI-SET KE MODE REGISTRASI KARTU:
        if (($device->app_mode ?? 'absen') === 'regis') {
            $absensiModel = $this->model('Absensi');
            $siswa = $absensiModel->getSiswaByRFID($payload['rfid_uid']);
            $deviceModel->logActivity($device->id, "Scan Mode Registrasi: " . $payload['rfid_uid'], 200);
            $this->respond(true, 'Mode Tambah Kartu Aktif: Kartu berhasil dibaca', [
                'code'       => 'mode_regis',
                'uid'        => $payload['rfid_uid'],
                'mode'       => 'regis',
                'is_used'    => $siswa ? true : false,
                'owner_name' => $siswa ? $siswa->name : null
            ]);
        }

        $absensiModel = $this->model('Absensi');
        
        // 3. Cari Siswa
        $siswa = $absensiModel->getSiswaByRFID($payload['rfid_uid']);
        if (!$siswa) {
            $deviceModel->logActivity($device->id, json_encode($payload), 404);
            $this->respond(false, 'RFID tidak terdaftar', [
                'uid'  => $payload['rfid_uid'],
                'code' => 'unregistered'
            ], 404);
        }

        // 4. Ambil jadwal
        $jadwal = $absensiModel->getJadwalHariIni();
        if (!$jadwal) {
            $deviceModel->logActivity($device->id, json_encode($payload), 400);
            $this->respond(false, 'Jadwal Tidak Ada Hari Ini', ['code' => 'no_schedule'], 400);
        }

        $tanggal_hari_ini = date('Y-m-d');
        $jam_sekarang = date('H:i:s');
        
        // Cek jika tap terlalu pagi dari jam_mulai_absensi
        if ($jam_sekarang < $jadwal->jam_mulai_absensi) {
            $deviceModel->logActivity($device->id, json_encode($payload), 400);
            $this->respond(false, 'Belum Jam Masuk', ['code' => 'too_early'], 400);
        }

        // 5. Cek duplikasi
        $cek = $absensiModel->cekAbsensiSiswa($siswa->id, $tanggal_hari_ini);
        
        // Jika siswa tercatat izin atau sakit: TOLAK
        if ($cek && in_array(strtolower($cek->status), ['izin', 'sakit'])) {
            $statusLabel = strtoupper($cek->status);
            $ketText     = !empty($cek->keterangan) ? ' (' . $cek->keterangan . ')' : '';
            $deviceModel->logActivity($device->id, json_encode($payload), 422);
            $this->respond(false, "Presensi ditolak: Siswa tercatat {$statusLabel} hari ini{$ketText}. Tidak perlu tap kartu.", [
                'type'       => 'leave_active',
                'status'     => $cek->status,
                'keterangan' => $cek->keterangan
            ], 422);
        }

        $isPlaceholderAlfa = ($cek && strtolower($cek->status) === 'alfa' && $cek->jam_datang === null && $cek->jam_pulang === null);

        if (!$cek || $isPlaceholderAlfa) {
            // ── Scan Pertama (Belum ada data absensi hari ini / update placeholder alfa) ──
            
            // Jika scan pertama kali dilakukan saat atau setelah jam pulang (misal jam pulang 14:00 lalu scan jam 14:30):
            // Tetap tercatat pulang dan absen pulang berhasil!
            if ($jam_sekarang >= $jadwal->jam_pulang) {
                $dataInsert = [
                    'siswa_id'         => $siswa->id,
                    'tanggal'          => $tanggal_hari_ini,
                    'jam_datang'       => null,
                    'jam_pulang'       => $jam_sekarang,
                    'status'           => 'alfa', // Status alfa karena pagi tidak absen datang
                    'keterangan'       => 'Hanya absen pulang (tidak scan masuk)',
                    'source_device_id' => $device->id
                ];

                if ($isPlaceholderAlfa) {
                    $db = new Database();
                    $db->query("UPDATE absensi_siswa SET jam_pulang = :jam_pulang, status = :status, keterangan = :keterangan, source_device_id = :device_id WHERE id = :id");
                    $db->bind(':jam_pulang', $jam_sekarang);
                    $db->bind(':status', 'alfa');
                    $db->bind(':keterangan', 'Hanya absen pulang (tidak scan masuk)');
                    $db->bind(':device_id', $device->id);
                    $db->bind(':id', $cek->id);
                    $db->execute();
                } else {
                    $absensiModel->insertAbsensiSiswa($dataInsert);
                }
                $deviceModel->logActivity($device->id, json_encode($payload), 200);

                if (!empty($siswa->telegram_chat_id) && !empty($siswa->telegram_bot_token)) {
                    $botToken = $siswa->telegram_bot_token;
                    $message = "<b>Notifikasi Kehadiran Pulang</b>\n\n"
                             . "Nama: {$siswa->name}\n"
                             . "Kelas: {$siswa->nama_kelas}\n"
                             . "Waktu Pulang: {$jam_sekarang}\n"
                             . "Status: ⚠️ Pulang (Tidak Absen Masuk - Alpa)";
                    
                    $chat_id = $siswa->telegram_chat_id;
                    register_shutdown_function(function() use ($chat_id, $message, $botToken) {
                        TelegramHelper::sendMessage($chat_id, $message, null, $botToken);
                    });
                }

                // --- WhatsApp Notification Pulang ke Orang Tua ---
                $siswaSnapshot = $siswa;
                register_shutdown_function(function() use ($siswaSnapshot, $jam_sekarang) {
                    WhatsAppHelper::sendNotifPulang($siswaSnapshot, $jam_sekarang);
                });

                if (!empty($siswa->user_id)) {
                    NotificationHelper::send(
                        (int)$siswa->user_id,
                        "⚠️ Presensi Pulang (Tanpa Masuk)",
                        "Halo {$siswa->name}, absen pulang Anda tercatat pukul " . substr($jam_sekarang, 0, 5) . " WIB.",
                        "kehadiran_pulang",
                        "/absensi/siswa"
                    );
                }

                $this->respond(true, 'Absensi pulang siswa berhasil', [
                    'nama'       => $siswa->name ?? ('Siswa #' . $siswa->id),
                    'kelas'      => $siswa->nama_kelas ?? '',
                    'status'     => 'alfa',
                    'jam_datang' => null,
                    'jam_pulang' => $jam_sekarang,
                    'code'       => 'check_out',
                    'att_status' => 'Pulang',
                    'time'       => substr($jam_sekarang, 0, 5),
                ]);
            }

            // ── Absen Datang (Pagi / Siang Sebelum Jam Pulang) ──
            // Kebijakan:
            // 1. <= batas_terlambat (misal 08:00) -> Hadir
            // 2. > batas_terlambat s/d 09:00     -> Terlambat
            // 3. > 09:00 (Opsi C)                 -> Tetap terekam jamnya, tapi status 'alfa' (Perlu Verifikasi Piket)
            $keterangan = null;
            if ($jam_sekarang <= $jadwal->batas_terlambat) {
                $status     = 'hadir';
                $statusText = '✅ Hadir Tepat Waktu';
                $attStatus  = 'Hadir';
                $respMsg    = 'Hadir Tepat Waktu';
            } elseif ($jam_sekarang <= '09:00:00') {
                $status     = 'terlambat';
                $statusText = '⚠️ Terlambat Masuk';
                $attStatus  = 'Terlambat';
                $respMsg    = 'Terlambat Masuk';
                $keterangan = 'Terlambat masuk sekolah';
            } else {
                // Lewat jam 09:00 -> Tetap terekam jam datangnya, tapi otomatis status 'alfa'
                $status     = 'alfa';
                $statusText = '❌ Alpa (Terlambat > 09:00, Perlu Konfirmasi Piket)';
                $attStatus  = 'Alpa Telat';
                $respMsg    = 'Terlambat > 09:00 (Status Alpa)';
                $keterangan = 'Terlambat > 09:00 (Perlu Verifikasi Guru Piket)';
            }

            $dataInsert = [
                'siswa_id'         => $siswa->id,
                'tanggal'          => $tanggal_hari_ini,
                'jam_datang'       => $jam_sekarang,
                'jam_pulang'       => null, // Biarkan null, agar bisa absen pulang
                'status'           => $status,
                'keterangan'       => $keterangan,
                'source_device_id' => $device->id
            ];

            if ($isPlaceholderAlfa) {
                $db = new Database();
                $db->query("UPDATE absensi_siswa SET jam_datang = :jam_datang, jam_pulang = NULL, status = :status, keterangan = :keterangan, source_device_id = :device_id WHERE id = :id");
                $db->bind(':jam_datang', $jam_sekarang);
                $db->bind(':status', $status);
                $db->bind(':keterangan', $keterangan);
                $db->bind(':device_id', $device->id);
                $db->bind(':id', $cek->id);
                $db->execute();
            } else {
                $absensiModel->insertAbsensiSiswa($dataInsert);
            }
            $deviceModel->logActivity($device->id, json_encode($payload), 200);

            // --- Telegram Notification Logic ---
            if (!empty($siswa->telegram_chat_id) && !empty($siswa->telegram_bot_token)) {
                $botToken = $siswa->telegram_bot_token;
                $message = "<b>Notifikasi Kehadiran Masuk</b>\n\n"
                         . "Nama: {$siswa->name}\n"
                         . "Kelas: {$siswa->nama_kelas}\n"
                         . "Waktu: {$jam_sekarang}\n"
                         . "Status: {$statusText}";
                
                $chat_id = $siswa->telegram_chat_id;
                register_shutdown_function(function() use ($chat_id, $message, $botToken) {
                    TelegramHelper::sendMessage($chat_id, $message, null, $botToken);
                });
            }
            // -----------------------------------

            // --- WhatsApp Notification Masuk (Hadir) ke Orang Tua ---
            $siswaSnapshotMasuk = $siswa;
            $statusTextMasuk    = $statusText;
            register_shutdown_function(function() use ($siswaSnapshotMasuk, $jam_sekarang, $statusTextMasuk) {
                WhatsAppHelper::sendNotifMasuk($siswaSnapshotMasuk, $jam_sekarang, $statusTextMasuk);
            });

            if (!empty($siswa->user_id)) {
                $statusClean = strip_tags($statusText);
                NotificationHelper::send(
                    (int)$siswa->user_id,
                    "✅ Presensi Masuk Berhasil",
                    "Halo {$siswa->name}, presensi masuk Anda tercatat pukul " . substr($jam_sekarang, 0, 5) . " WIB. Status: {$statusClean}.",
                    "kehadiran_masuk",
                    "/absensi/siswa"
                );
            }
            if (!empty($siswa->wali_kelas_id)) {
                NotificationHelper::send(
                    (int)$siswa->wali_kelas_id,
                    "📋 Presensi: {$siswa->name}",
                    "Siswa {$siswa->name} ({$siswa->nama_kelas}) presensi masuk pukul " . substr($jam_sekarang, 0, 5) . " WIB ({$attStatus}).",
                    "kehadiran_masuk",
                    "/dashboard"
                );
            }

            $this->respond(true, $respMsg, [
                'nama'       => $siswa->name ?? ('Siswa #' . $siswa->id),
                'kelas'      => $siswa->nama_kelas ?? '',
                'status'     => $status,
                'jam_datang' => $jam_sekarang,
                'jam_pulang' => null,
                'code'       => 'check_in',
                'att_status' => $attStatus,
                'time'       => substr($jam_sekarang, 0, 5),
            ]);
        } else {
            // ── Scan Kedua ──
            if ($cek->jam_pulang !== null) {
                $deviceModel->logActivity($device->id, json_encode($payload), 409);
                $this->respond(false, 'Siswa sudah absen pulang hari ini', [], 409);
            }

            // Cek apakah sudah waktunya pulang (mendukung shift malam / lintas hari)
            $is_belum_waktunya_pulang = false;
            if ($jadwal->jam_pulang < $jadwal->jam_mulai_absensi) {
                // Shift malam (misal masuk 22:30, pulang 01:30)
                if ($jam_sekarang >= $jadwal->jam_mulai_absensi || $jam_sekarang < $jadwal->jam_pulang) {
                    $is_belum_waktunya_pulang = true;
                }
            } else {
                // Shift normal (misal masuk 07:00, pulang 15:00)
                if ($jam_sekarang < $jadwal->jam_pulang) {
                    $is_belum_waktunya_pulang = true;
                }
            }

            if ($is_belum_waktunya_pulang) {
                // Belum jam pulang — Jangan catat sebagai pulang normal.
                // Masukkan ke pengajuan pulang cepat dengan status DRAFT jika belum ada.
                $pengajuanModel = $this->model('PengajuanPulangCepat');
                $existing = $pengajuanModel->getActiveBySiswaAndDate($siswa->id, $tanggal_hari_ini);
                
                if (!$existing) {
                    $wali_kelas_id = $pengajuanModel->getWaliKelasId($siswa->id);
                    if ($wali_kelas_id) {
                        $pengajuanModel->createDraft([
                            'siswa_id' => $siswa->id,
                            'wali_kelas_id' => $wali_kelas_id,
                            'tanggal' => $tanggal_hari_ini,
                            'jam_pengajuan' => $jam_sekarang,
                            'jam_masuk' => $cek->jam_datang,
                            'jam_pulang_jadwal' => $jadwal->jam_pulang
                        ]);
                    }
                }

                $deviceModel->logActivity($device->id, json_encode($payload), 200);
                
                $this->respond(true, 'Pengajuan pulang cepat dibuat. Silakan isi form di dashboard.', [
                    'nama'       => $siswa->name ?? ('Siswa #' . $siswa->id),
                    'kelas'      => $siswa->nama_kelas ?? '',
                    'status'     => $cek->status,
                    'jam_datang' => $cek->jam_datang,
                    'jam_pulang' => null, // Belum dianggap pulang
                    'code'       => 'early_leave',
                    'att_status' => 'Plg Cepat',
                    'time'       => substr($jam_sekarang, 0, 5),
                ]);
            }

            // Sudah jam pulang — catat absen pulang
            $absensiModel->updateAbsensiPulangSiswa($cek->id, $jam_sekarang);
            $deviceModel->logActivity($device->id, json_encode($payload), 200);

            // --- Telegram Notification Logic (Pulang) ---
            if (!empty($siswa->telegram_chat_id) && !empty($siswa->telegram_bot_token)) {
                $botToken = $siswa->telegram_bot_token;
                $message = "<b>Notifikasi Kehadiran Pulang</b>\n\n"
                         . "Nama: {$siswa->name}\n"
                         . "Kelas: {$siswa->nama_kelas}\n"
                         . "Waktu: {$jam_sekarang}\n"
                         . "Status: ✅ Pulang";
                
                $chat_id = $siswa->telegram_chat_id;
                register_shutdown_function(function() use ($chat_id, $message, $botToken) {
                    TelegramHelper::sendMessage($chat_id, $message, null, $botToken);
                });
            }
            // -----------------------------------

            // --- WhatsApp Notification Pulang ke Orang Tua ---
            $siswaSnapshot = $siswa;
            register_shutdown_function(function() use ($siswaSnapshot, $jam_sekarang) {
                WhatsAppHelper::sendNotifPulang($siswaSnapshot, $jam_sekarang);
            });

            if (!empty($siswa->user_id)) {
                NotificationHelper::send(
                    (int)$siswa->user_id,
                    "👋 Presensi Pulang Berhasil",
                    "Halo {$siswa->name}, checkout presensi pulang tercatat pukul " . substr($jam_sekarang, 0, 5) . " WIB. Hati-hati di perjalanan pulang!",
                    "kehadiran_pulang",
                    "/absensi/siswa"
                );
            }
            if (!empty($siswa->wali_kelas_id)) {
                NotificationHelper::send(
                    (int)$siswa->wali_kelas_id,
                    "👋 Siswa Pulang: {$siswa->name}",
                    "Siswa {$siswa->name} ({$siswa->nama_kelas}) telah checkout pulang pukul " . substr($jam_sekarang, 0, 5) . " WIB.",
                    "kehadiran_pulang",
                    "/dashboard"
                );
            }

            $this->respond(true, 'Absensi pulang siswa berhasil', [
                'nama'       => $siswa->name ?? ('Siswa #' . $siswa->id),
                'kelas'      => $siswa->nama_kelas ?? '',
                'status'     => $cek->status,
                'jam_datang' => $cek->jam_datang,
                'jam_pulang' => $jam_sekarang,
                'code'       => 'check_out',
                'att_status' => 'Pulang',
                'time'       => substr($jam_sekarang, 0, 5),
            ]);
        }
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

        // Jika guru TIDAK punya jadwal aktif hari ini: TOLAK
        if (!$jadwal || empty($jadwal->is_active)) {
            $deviceModel->logActivity($device->id, json_encode($payload), 422);
            $this->respond(false, 'Presensi ditolak: Anda tidak memiliki jadwal dinas/mengajar aktif hari ini.', [
                'has_schedule' => false,
                'code'         => 'no_schedule'
            ], 422);
        }

        $tanggal_hari_ini = date('Y-m-d');
        $jam_sekarang = date('H:i:s');

        // Jika scan masuk dilakukan sebelum jam mulai absensi dibuka: TOLAK
        if (!empty($jadwal->jam_mulai_absensi) && $jadwal->jam_mulai_absensi !== '00:00:00' && $jam_sekarang < $jadwal->jam_mulai_absensi) {
            $deviceModel->logActivity($device->id, json_encode($payload), 422);
            $this->respond(false, 'Presensi belum dibuka. Jam mulai absensi: ' . substr($jadwal->jam_mulai_absensi, 0, 5) . ' WIB', [
                'type'      => 'early_scan_blocked',
                'jam_mulai' => $jadwal->jam_mulai_absensi
            ], 422);
        }

        $cek = $absensiModel->cekAbsensiGuru($guru->id, $tanggal_hari_ini);

        // Jika guru tercatat izin atau sakit: TOLAK
        if ($cek && in_array(strtolower($cek->status), ['izin', 'sakit'])) {
            $statusLabel = strtoupper($cek->status);
            $ketText     = !empty($cek->keterangan) ? ' (' . $cek->keterangan . ')' : '';
            $deviceModel->logActivity($device->id, json_encode($payload), 422);
            $this->respond(false, "Presensi ditolak: Anda telah tercatat {$statusLabel} hari ini{$ketText}. Tidak perlu presensi.", [
                'type'       => 'leave_active',
                'status'     => $cek->status,
                'keterangan' => $cek->keterangan
            ], 422);
        }

        $isPlaceholderAlfaGuru = ($cek && strtolower($cek->status) === 'alfa' && $cek->jam_datang === null && $cek->jam_pulang === null);

        if (!$cek || $isPlaceholderAlfaGuru) {
            // ── Scan pertama guru ──
            if ($jam_sekarang >= $jadwal->jam_pulang) {
                // Guru baru tap saat/setelah jam pulang -> tetap catat pulang berhasil
                $dataInsert = [
                    'guru_id'          => $guru->id,
                    'tanggal'          => $tanggal_hari_ini,
                    'jam_datang'       => null,
                    'jam_pulang'       => $jam_sekarang,
                    'status'           => 'alfa',
                    'keterangan'       => 'Hanya absen pulang (tidak scan masuk)',
                    'source_device_id' => $device->id
                ];

                if ($isPlaceholderAlfaGuru) {
                    $db = new Database();
                    $db->query("UPDATE absensi_guru SET jam_pulang = :jam_pulang, status = :status, keterangan = :keterangan, source_device_id = :device_id WHERE id = :id");
                    $db->bind(':jam_pulang', $jam_sekarang);
                    $db->bind(':status', 'alfa');
                    $db->bind(':keterangan', 'Hanya absen pulang (tidak scan masuk)');
                    $db->bind(':device_id', $device->id);
                    $db->bind(':id', $cek->id);
                    $db->execute();
                } else {
                    $absensiModel->insertAbsensiGuru($dataInsert);
                }
                $deviceModel->logActivity($device->id, json_encode($payload), 200);

                $this->respond(true, 'Absensi pulang guru berhasil', [
                    'type'       => 'check_out',
                    'jam_pulang' => $jam_sekarang,
                    'att_status' => 'Pulang'
                ]);
            }

            // Absen Datang Guru: toleransi dinamis (+90 menit dari batas_terlambat)
            $waktuBatasTerlambat = strtotime($jadwal->batas_terlambat);
            $waktuMaksTerlambat  = $waktuBatasTerlambat ? ($waktuBatasTerlambat + (90 * 60)) : strtotime('09:00:00');
            $jamMaksTerlambat    = date('H:i:s', $waktuMaksTerlambat);

            if ($jadwal->jam_pulang < $jamMaksTerlambat && $jadwal->jam_pulang > $jadwal->batas_terlambat) {
                $jamMaksTerlambat = $jadwal->jam_pulang;
            }

            $labelMaksTerlambat = substr($jamMaksTerlambat, 0, 5);
            $keterangan = null;

            if ($jam_sekarang <= $jadwal->batas_terlambat) {
                $status    = 'hadir';
                $attStatus = 'Hadir';
                $respMsg   = 'Hadir Tepat Waktu';
            } elseif ($jam_sekarang <= $jamMaksTerlambat) {
                $status     = 'terlambat';
                $attStatus  = 'Terlambat';
                $respMsg    = 'Terlambat Masuk';
                $keterangan = 'Terlambat datang';
            } else {
                $status     = 'alfa';
                $attStatus  = 'Alpa Telat';
                $respMsg    = "Terlambat > {$labelMaksTerlambat} (Status Alpa)";
                $keterangan = "Terlambat > {$labelMaksTerlambat} (Perlu Verifikasi Kepsek/Piket)";
            }

            $dataInsert = [
                'guru_id'          => $guru->id,
                'tanggal'          => $tanggal_hari_ini,
                'jam_datang'       => $jam_sekarang,
                'status'           => $status,
                'keterangan'       => $keterangan,
                'source_device_id' => $device->id
            ];

            if ($isPlaceholderAlfaGuru) {
                $db = new Database();
                $db->query("UPDATE absensi_guru SET jam_datang = :jam_datang, jam_pulang = NULL, status = :status, keterangan = :keterangan, source_device_id = :device_id WHERE id = :id");
                $db->bind(':jam_datang', $jam_sekarang);
                $db->bind(':status', $status);
                $db->bind(':keterangan', $keterangan);
                $db->bind(':device_id', $device->id);
                $db->bind(':id', $cek->id);
                $db->execute();
            } else {
                $absensiModel->insertAbsensiGuru($dataInsert);
            }
            $deviceModel->logActivity($device->id, json_encode($payload), 200);

            if (!empty($guru->user_id)) {
                NotificationHelper::send(
                    (int)$guru->user_id,
                    "📋 Presensi Guru Berhasil",
                    "Presensi masuk Anda berhasil tercatat pada pukul " . substr($jam_sekarang, 0, 5) . " WIB ({$attStatus}).",
                    "kehadiran_masuk",
                    "/dashboard"
                );
            }

            $this->respond(true, $respMsg, [
                'type'       => 'check_in',
                'status'     => $status,
                'jam_datang' => $jam_sekarang,
                'att_status' => $attStatus,
                'time'       => substr($jam_sekarang, 0, 5)
            ]);

        } else {
            // ── Scan kedua → Absen Pulang ──
            if ($cek->jam_pulang !== null) {
                $deviceModel->logActivity($device->id, json_encode($payload), 409);
                $this->respond(false, 'Guru sudah absen pulang hari ini', [], 409);
            }

            // Cegah absen pulang jika belum waktunya pulang
            if ($jam_sekarang < $jadwal->jam_pulang) {
                $deviceModel->logActivity($device->id, json_encode($payload), 400);
                $this->respond(false, 'Belum Waktunya Pulang (Jam ' . substr($jadwal->jam_pulang, 0, 5) . ')', [
                    'type' => 'early_leave_blocked',
                    'jam_pulang_jadwal' => $jadwal->jam_pulang
                ], 400);
            }

            $absensiModel->updateAbsensiPulangGuru($cek->id, $jam_sekarang);
            $deviceModel->logActivity($device->id, json_encode($payload), 200);

            if (!empty($guru->user_id)) {
                NotificationHelper::send(
                    (int)$guru->user_id,
                    "🏠 Presensi Pulang Guru",
                    "Presensi pulang Anda tercatat pukul " . substr($jam_sekarang, 0, 5) . " WIB. Selamat beristirahat!",
                    "kehadiran_pulang",
                    "/dashboard"
                );
            }

            $this->respond(true, 'Absensi pulang guru berhasil', [
                'type'       => 'check_out',
                'jam_pulang' => $jam_sekarang,
                'att_status' => 'Pulang'
            ]);
        }
    }
}
