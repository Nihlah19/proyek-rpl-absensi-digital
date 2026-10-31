<?php
/**
 * WhatsAppHelper - Engine Notifikasi WhatsApp Multi-Token dengan Auto-Failover (10 Slot Fonnte)
 * 
 * Aturan Pengiriman Cerdas:
 * 1. 9 Token Wali Kelas (Slot 1..9): Setiap kelas memiliki token pengirim sesuai wali kelasnya.
 * 2. 1 Token Cadangan (Slot 10): Digunakan sebagai garda cadangan jika token kelas mencapai batas kuota (exhausted/limit).
 * 3. Auto-Oper Token Alternatif: Jika token cadangan juga limit, sistem otomatis mengoper ke token kelas lain
 *    yang masih memiliki kuota/aktif agar notifikasi presensi siswa tetap terkirim dengan aman.
 * 4. Switch Notifikasi per Siswa: Memeriksa apakah siswa diizinkan menerima notifikasi (wa_notif_active).
 * 5. Normalisasi nomor telepon otomatis (format internasional 628...).
 */
class WhatsAppHelper {

    /**
     * Default Template Notifikasi Masuk (Hadir)
     */
    public const DEFAULT_TEMPLATE_MASUK = "*Pemberitahuan Presensi Masuk Siswa*\n{nama_sekolah}\n\nYth. Orang Tua / Wali Murid,\n\nDiberitahukan bahwa ananda:\n• *Nama*: {nama_siswa}\n• *Kelas*: {kelas}\n• *NIS*: {nis}\n• *Pukul*: {jam_masuk} WIB\n• *Tanggal*: {tanggal}\n• *Status*: {status}\n\nAnanda telah tiba di sekolah dan siap mengikuti kegiatan pembelajaran hari ini. Terima kasih atas perhatian dan kerja samanya.";

    /**
     * Default Template Notifikasi Pulang
     */
    public const DEFAULT_TEMPLATE_PULANG = "*Pemberitahuan Kepulangan Siswa*\n{nama_sekolah}\n\nYth. Orang Tua / Wali Murid,\n\nDiberitahukan bahwa ananda:\n• *Nama*: {nama_siswa}\n• *Kelas*: {kelas}\n• *NIS*: {nis}\n• *Pukul*: {jam_pulang} WIB\n• *Tanggal*: {tanggal}\n• *Status*: 👋 *Telah Selesai Absen Pulang Sekolah*\n\nAnanda telah tercatat melakukan presensi kepulangan di sistem sekolah dan telah meninggalkan area sekolah. Terima kasih atas kerja samanya.";

    /**
     * Normalisasi nomor telepon ke format internasional Indonesia (62xxx)
     */
    public static function normalizePhone(?string $phone): string {
        if (empty($phone)) return '';
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean)) return '';

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Ambil seluruh slot token Fonnte aktif yang belum habis kuotanya
     */
    public static function getAvailableSlots(): array {
        return self::getSlotsForClass(null);
    }

    /**
     * Ambil urutan slot token cerdas untuk suatu kelas tertentu:
     * 1. Token Kelas Wali Siswa (Prioritas Utama)
     * 2. Token Cadangan (Slot 10 / Backup Failover)
     * 3. Token Kelas Lain yang masih aktif & memiliki sisa kuota (Oper darurat)
     */
    public static function getSlotsForClass(?int $kelasId = null): array {
        try {
            $db = new Database();
            $currentMonth = date('Y-m');

            // Reset otomatis status exhausted jika bulan sudah berganti
            $db->query("UPDATE wa_config SET is_exhausted = 0, exhausted_reason = NULL 
                        WHERE is_exhausted = 1 AND (exhausted_month IS NULL OR exhausted_month != :curr_m)");
            $db->bind(':curr_m', $currentMonth);
            $db->execute();

            // Ambil semua slot aktif yang memiliki token dan belum habis kuota
            $db->query("SELECT c.*, k.nama_kelas, u.name as wali_nama
                        FROM wa_config c
                        LEFT JOIN kelas k ON k.id = c.kelas_id
                        LEFT JOIN users u ON u.id = k.wali_kelas_id
                        WHERE c.is_active = 1 
                          AND c.token IS NOT NULL 
                          AND c.token != '' 
                          AND c.is_exhausted = 0
                        ORDER BY c.priority ASC, c.id ASC");
            $allActive = $db->resultSet() ?: [];

            if (empty($allActive)) {
                return [];
            }

            $targetSlot = null;
            $backupSlot = null;
            $otherSlots = [];

            foreach ($allActive as $slot) {
                if ($kelasId && (int)$slot->kelas_id === (int)$kelasId) {
                    $targetSlot = $slot;
                } elseif (empty($slot->kelas_id) || (int)$slot->id === 10 || (int)$slot->priority === 10 || stripos($slot->label, 'cadangan') !== false) {
                    $backupSlot = $slot;
                } else {
                    $otherSlots[] = $slot;
                }
            }

            // Susun antrean failover teratur
            $ordered = [];
            if ($targetSlot) {
                $ordered[] = $targetSlot;
            }
            if ($backupSlot) {
                $ordered[] = $backupSlot;
            }
            foreach ($otherSlots as $other) {
                $ordered[] = $other;
            }

            return $ordered;
        } catch (Exception $e) {
            error_log('[WhatsAppHelper::getSlotsForClass] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format tanggal Indonesia (contoh: Senin, 24 Oktober 2026)
     */
    public static function formatTanggalIndo(?string $date = null): string {
        $timestamp = !empty($date) ? strtotime($date) : time();
        if (!$timestamp) {
            $timestamp = time();
        }
        $hariArr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulanArr = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $hari  = $hariArr[(int)date('w', $timestamp)];
        $tgl   = date('j', $timestamp);
        $bulan = $bulanArr[(int)date('n', $timestamp)];
        $tahun = date('Y', $timestamp);

        return "{$hari}, {$tgl} {$bulan} {$tahun}";
    }

    /**
     * Format template pesan masuk (hadir)
     */
    public static function formatPesanMasuk(string $nama, string $kelas, string $jam, ?string $nis = '-', ?string $statusText = '✅ Hadir Tepat Waktu', ?string $tanggal = null, ?string $customTemplate = null): string {
        $template    = $customTemplate ?: AppSetting::get('wa_notif_masuk_template', self::DEFAULT_TEMPLATE_MASUK);
        $tglIndo     = $tanggal ? self::formatTanggalIndo($tanggal) : self::formatTanggalIndo(date('Y-m-d'));
        $jamClean    = substr($jam, 0, 5);
        $namaSekolah = AppSetting::get('nama_sekolah', 'SMK NU Wargabinangun');

        $replacements = [
            '{nama_siswa}'   => $nama,
            '{nama}'         => $nama,
            '{kelas}'        => $kelas,
            '{nis}'          => $nis ?: '-',
            '{jam_masuk}'    => $jamClean,
            '{jam}'          => $jamClean,
            '{status}'       => $statusText,
            '{status_masuk}' => $statusText,
            '{tanggal}'      => $tglIndo,
            '{nama_sekolah}' => $namaSekolah,
            '{sekolah}'      => $namaSekolah,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Format template pesan kepulangan (pulang)
     */
    public static function formatPesanPulang(string $nama, string $kelas, string $jam, ?string $nis = '-', ?string $tanggal = null, ?string $customTemplate = null): string {
        $template    = $customTemplate ?: AppSetting::get('wa_notif_pulang_template', self::DEFAULT_TEMPLATE_PULANG);
        $tglIndo     = $tanggal ? self::formatTanggalIndo($tanggal) : self::formatTanggalIndo(date('Y-m-d'));
        $jamClean    = substr($jam, 0, 5);
        $namaSekolah = AppSetting::get('nama_sekolah', 'SMK NU Wargabinangun');

        $replacements = [
            '{nama_siswa}'   => $nama,
            '{nama}'         => $nama,
            '{kelas}'        => $kelas,
            '{nis}'          => $nis ?: '-',
            '{jam_pulang}'   => $jamClean,
            '{jam}'          => $jamClean,
            '{tanggal}'      => $tglIndo,
            '{nama_sekolah}' => $namaSekolah,
            '{sekolah}'      => $namaSekolah,
            '{status}'       => 'Telah Selesai Absen Pulang Sekolah',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Kirim Notifikasi WhatsApp Masuk (Hadir)
     */
    public static function sendNotifMasuk(object|array $siswa, string $jamMasuk, string $statusText = '✅ Hadir Tepat Waktu', ?string $tanggal = null): array {
        $siswaObj = is_array($siswa) ? (object)$siswa : $siswa;

        $isEnabled = AppSetting::get('wa_notif_masuk_enabled', '1');
        if ($isEnabled !== '1' && $isEnabled !== 1 && $isEnabled !== true) {
            return ['success' => false, 'message' => 'Fitur Notifikasi WA Masuk (Hadir) sedang dinonaktifkan di pengaturan sistem.'];
        }

        $siswaData = self::resolveSiswaData($siswaObj);
        if (!$siswaData) {
            return ['success' => false, 'message' => 'Data siswa tidak ditemukan.'];
        }

        if (isset($siswaData->wa_notif_active) && (int)$siswaData->wa_notif_active === 0) {
            return ['success' => false, 'message' => "Notifikasi WA dinonaktifkan untuk siswa {$siswaData->name}."];
        }

        if (empty($siswaData->no_hp_ortu)) {
            return ['success' => false, 'message' => "Nomor WhatsApp orang tua belum terdata untuk siswa {$siswaData->name}."];
        }

        $namaSiswa = $siswaData->name ?? 'Siswa';
        $namaKelas = $siswaData->nama_kelas ?? ($siswaData->class_code ?? '-');
        $nis       = $siswaData->nis ?? '-';
        $message   = self::formatPesanMasuk($namaSiswa, $namaKelas, $jamMasuk, $nis, $statusText, $tanggal);
        $kelasId   = !empty($siswaData->kelas_id) ? (int)$siswaData->kelas_id : null;

        return self::send($siswaData->no_hp_ortu, $message, (int)$siswaData->id, 'masuk', null, $kelasId);
    }

    /**
     * Kirim Notifikasi WhatsApp Pulang
     */
    public static function sendNotifPulang(object|array $siswa, string $jamPulang, ?string $tanggal = null): array {
        $siswaObj = is_array($siswa) ? (object)$siswa : $siswa;

        $isEnabled = AppSetting::get('wa_notif_pulang_enabled', '1');
        if ($isEnabled !== '1' && $isEnabled !== 1 && $isEnabled !== true) {
            return ['success' => false, 'message' => 'Fitur Notifikasi WA Pulang sedang dinonaktifkan di pengaturan sistem.'];
        }

        $siswaData = self::resolveSiswaData($siswaObj);
        if (!$siswaData) {
            return ['success' => false, 'message' => 'Data siswa tidak ditemukan.'];
        }

        if (isset($siswaData->wa_notif_active) && (int)$siswaData->wa_notif_active === 0) {
            return ['success' => false, 'message' => "Notifikasi WA dinonaktifkan untuk siswa {$siswaData->name}."];
        }

        if (empty($siswaData->no_hp_ortu)) {
            return ['success' => false, 'message' => "Nomor WhatsApp orang tua belum terdata untuk siswa {$siswaData->name}."];
        }

        $namaSiswa = $siswaData->name ?? 'Siswa';
        $namaKelas = $siswaData->nama_kelas ?? ($siswaData->class_code ?? '-');
        $nis       = $siswaData->nis ?? '-';
        $message   = self::formatPesanPulang($namaSiswa, $namaKelas, $jamPulang, $nis, $tanggal);
        $kelasId   = !empty($siswaData->kelas_id) ? (int)$siswaData->kelas_id : null;

        return self::send($siswaData->no_hp_ortu, $message, (int)$siswaData->id, 'pulang', null, $kelasId);
    }

    /**
     * Kirim pesan WhatsApp dengan AUTO-FAILOVER (10 TOKEN FONNTE)
     * Prioritas:
     * 1. Token Kelas Wali Siswa
     * 2. Token Cadangan (Slot 10)
     * 3. Token Kelas Lain yang Masih Ada Kuota
     */
    public static function send(string $targetPhone, string $message, ?int $siswaId = null, string $notifType = 'pulang', ?int $forcedConfigId = null, ?int $kelasId = null): array {
        $normalizedPhone = self::normalizePhone($targetPhone);
        if (empty($normalizedPhone)) {
            return ['success' => false, 'message' => 'Nomor WhatsApp tujuan tidak valid atau kosong.'];
        }

        // Jika kelasId belum diketahui, cari dari tabel siswa
        if (!$kelasId && $siswaId) {
            try {
                $db = new Database();
                $db->query("SELECT kelas_id FROM siswa WHERE id = :id LIMIT 1");
                $db->bind(':id', $siswaId);
                $row = $db->single();
                if ($row && !empty($row->kelas_id)) {
                    $kelasId = (int)$row->kelas_id;
                }
            } catch (Exception $e) {}
        }

        // Ambil urutan slot token yang tersedia
        $slots = [];
        if ($forcedConfigId) {
            $single = self::getConfigById($forcedConfigId);
            if ($single && !empty($single->token)) {
                $slots = [$single];
            }
        }
        if (empty($slots)) {
            $slots = self::getSlotsForClass($kelasId);
        }

        if (empty($slots)) {
            $queueId = self::insertQueue([
                'config_id'    => null,
                'siswa_id'     => $siswaId,
                'target_phone' => $normalizedPhone,
                'message'      => $message,
                'notif_type'   => $notifType,
                'status'       => 'pending',
                'response'     => 'Seluruh 10 Token Fonnte tidak tersedia, belum diisi, atau telah mencapai batas limit kuota',
                'sent_at'      => null
            ]);
            return [
                'success'  => false,
                'message'  => 'Seluruh slot Token Fonnte belum diisi token atau telah mencapai batas kuota maksimal.',
                'queue_id' => $queueId
            ];
        }

        $lastError     = '';
        $usedSlot      = null;
        $failoverChain = [];

        // Loop coba slot berurutan sesuai prioritas (Auto Failover)
        foreach ($slots as $slot) {
            $usedSlot = $slot;
            $token    = trim($slot->token);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => 'https://api.fonnte.com/send',
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query([
                    'target'      => $normalizedPhone,
                    'message'     => $message,
                    'countryCode' => '62'
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: ' . $token
                ]
            ]);

            $responseStr = curl_exec($ch);
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError   = curl_error($ch);
            curl_close($ch);

            $resData = json_decode($responseStr, true);

            // Cek status pengiriman dari Fonnte
            if ($httpCode === 200 && is_array($resData) && !empty($resData['status'])) {
                self::updateDeviceUsage($slot->id);

                $failoverDesc = '';
                if (!empty($failoverChain)) {
                    $failoverDesc = ' (Auto-Oper dari: ' . implode(' -> ', $failoverChain) . ')';
                }

                $slotLabel = !empty($slot->nama_kelas) ? "Slot #{$slot->id} ({$slot->nama_kelas})" : "Slot #{$slot->id} (" . ($slot->label ?? 'Cadangan') . ")";
                $process = strtolower((string)($resData['process'] ?? 'sent'));
                $isPending = ($process === 'pending');

                $queueId = self::insertQueue([
                    'config_id'    => $slot->id,
                    'siswa_id'     => $siswaId,
                    'target_phone' => $normalizedPhone,
                    'message'      => $message,
                    'notif_type'   => $notifType,
                    'status'       => $isPending ? 'pending' : 'sent',
                    'response'     => ($isPending ? "Pending di antrean Fonnte via " : "Sukses via ") . "{$slotLabel}{$failoverDesc}: " . $responseStr,
                    'sent_at'      => $isPending ? null : date('Y-m-d H:i:s')
                ]);

                if ($isPending) {
                    $devNum = !empty($slot->device_number) ? $slot->device_number : 'Device Fonnte';
                    return [
                        'success'     => true,
                        'is_pending'  => true,
                        'process'     => 'pending',
                        'message'     => "Pesan masuk ke antrean Fonnte via {$slotLabel}, namun berstatus PENDING karena WhatsApp pengirim ({$devNum}) sedang offline atau belum tersinkronisasi di HP.",
                        'slot_id'     => $slot->id,
                        'device'      => $devNum,
                        'queue_id'    => $queueId,
                        'raw'         => $resData
                    ];
                }

                return [
                    'success'    => true,
                    'is_pending' => false,
                    'process'    => 'sent',
                    'message'    => "Notifikasi WhatsApp berhasil terkirim via {$slotLabel}{$failoverDesc}",
                    'slot_id'    => $slot->id,
                    'queue_id'   => $queueId,
                    'raw'        => $resData
                ];
            }

            // Jika gagal, analisa alasan gagal
            $reason = strtolower($resData['reason'] ?? ($resData['message'] ?? ($curlError ?: "HTTP {$httpCode}")));
            $lastError = $reason;

            // Indikasi kuota bulanan/harian Fonnte habis atau token limit/expired
            $isQuotaExhausted = (
                str_contains($reason, 'quota') ||
                str_contains($reason, 'limit') ||
                str_contains($reason, 'habis') ||
                str_contains($reason, 'insufficient') ||
                str_contains($reason, 'package') ||
                str_contains($reason, 'expired') ||
                str_contains($reason, 'balance') ||
                str_contains($reason, 'device disconnected') ||
                str_contains($reason, 'qr required') ||
                str_contains($reason, 'unauthorized') ||
                str_contains($reason, 'invalid token')
            );

            $slotName = !empty($slot->nama_kelas) ? $slot->nama_kelas : ($slot->label ?: "Slot #{$slot->id}");

            if ($isQuotaExhausted) {
                self::markSlotExhausted($slot->id, $reason);
                $failoverChain[] = "{$slotName} [Kuota Limit]";
                error_log("[WhatsAppHelper] Slot #{$slot->id} ({$slotName}) limit: {$reason}. Mengoper ke token berikutnya...");
            } else {
                $failoverChain[] = "{$slotName} [{$reason}]";
            }

            // Lanjut ke slot berikutnya di loop!
            continue;
        }

        // Jika seluruh slot dalam antrean gagal
        $queueId = self::insertQueue([
            'config_id'    => $usedSlot ? $usedSlot->id : null,
            'siswa_id'     => $siswaId,
            'target_phone' => $normalizedPhone,
            'message'      => $message,
            'notif_type'   => $notifType,
            'status'       => 'failed',
            'response'     => "Seluruh slot gagal dicoba. Terakhir di Slot #" . ($usedSlot->id ?? '?') . ": " . $lastError,
            'sent_at'      => null
        ]);

        return [
            'success'  => false,
            'message'  => "Gagal mengirim WhatsApp setelah mencoba seluruh slot token: " . $lastError,
            'queue_id' => $queueId
        ];
    }

    /**
     * Tandai slot Fonnte sebagai habis kuota bulanan
     */
    public static function markSlotExhausted(int $slotId, string $reason): void {
        try {
            $db = new Database();
            $db->query("UPDATE wa_config SET 
                is_exhausted = 1,
                exhausted_reason = :reason,
                exhausted_month = :month
                WHERE id = :id");
            $db->bind(':reason', substr($reason, 0, 250));
            $db->bind(':month',  date('Y-m'));
            $db->bind(':id',     $slotId);
            $db->execute();
        } catch (Exception $e) {
            error_log('[WhatsAppHelper::markSlotExhausted] Error: ' . $e->getMessage());
        }
    }

    /**
     * Reset manual status habis kuota untuk suatu slot
     */
    public static function resetSlotExhausted(int $slotId): bool {
        try {
            $db = new Database();
            $db->query("UPDATE wa_config SET is_exhausted = 0, exhausted_reason = NULL, exhausted_month = NULL WHERE id = :id");
            $db->bind(':id', $slotId);
            return $db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Cek status koneksi perangkat Fonnte
     */
    public static function checkDevice(?string $token = null): array {
        if (empty($token)) {
            $slots = self::getAvailableSlots();
            $token = !empty($slots) ? $slots[0]->token : '';
        }

        if (empty($token)) {
            return [
                'success' => false,
                'status'  => 'unconfigured',
                'message' => 'Token API Fonnte belum diisi.'
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.fonnte.com/device',
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . trim($token)
            ]
        ]);

        $responseStr = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        $resData = json_decode($responseStr, true);

        if ($httpCode === 200 && is_array($resData)) {
            $deviceStatus = strtolower((string)($resData['device_status'] ?? ($resData['status'] ?? 'unknown')));
            $statusBool   = filter_var($resData['status'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isConnected  = ($deviceStatus === 'connect' || $deviceStatus === 'connected' || $statusBool === true);

            $deviceInfo = [
                'name'          => $resData['name'] ?? 'Device Fonnte',
                'device'        => $resData['device'] ?? '-',
                'device_status' => $deviceStatus,
                'package'       => $resData['package'] ?? 'Free / Reguler',
                'quota'         => $resData['quota'] ?? ($resData['messages'] ?? '-'),
                'expired'       => $resData['expired'] ?? '-'
            ];

            return [
                'success'       => true,
                'status'        => $isConnected,
                'connected'     => $isConnected,
                'device_status' => $deviceStatus,
                'name'          => $deviceInfo['name'],
                'device'        => $deviceInfo['device'],
                'package'       => $deviceInfo['package'],
                'quota'         => $deviceInfo['quota'],
                'expired'       => $deviceInfo['expired'],
                'device_info'   => $deviceInfo,
                'raw'           => $resData
            ];
        }

        $errMsg = $curlError ?: ($resData['reason'] ?? ($resData['message'] ?? ($responseStr ?: "HTTP Code {$httpCode}")));
        return [
            'success'   => false,
            'status'    => false,
            'connected' => false,
            'message'   => $errMsg,
            'raw'       => $resData
        ];
    }

    /**
     * Helper: Resolve data siswa dari database lengkap dengan wa_notif_active & no_hp_ortu
     */
    private static function resolveSiswaData(object $siswaObj): ?object {
        if (isset($siswaObj->wa_notif_active) && isset($siswaObj->no_hp_ortu) && isset($siswaObj->name) && isset($siswaObj->kelas_id)) {
            return $siswaObj;
        }

        $siswaId = $siswaObj->id ?? ($siswaObj->siswa_id ?? null);
        if (!$siswaId) return null;

        try {
            $db = new Database();
            $db->query("SELECT s.id, s.nis, s.no_hp_ortu, s.wa_notif_active, s.kelas_id, u.name, k.nama_kelas
                        FROM siswa s
                        JOIN users u ON u.id = s.user_id
                        LEFT JOIN kelas k ON k.id = s.kelas_id
                        WHERE s.id = :id LIMIT 1");
            $db->bind(':id', $siswaId);
            return $db->single() ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Ambil device berdasarkan ID
     */
    public static function getConfigById(int $id): ?object {
        try {
            $db = new Database();
            $db->query("SELECT c.*, k.nama_kelas, u.name as wali_nama
                        FROM wa_config c
                        LEFT JOIN kelas k ON k.id = c.kelas_id
                        LEFT JOIN users u ON u.id = k.wali_kelas_id
                        WHERE c.id = :id");
            $db->bind(':id', $id);
            return $db->single() ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update hitungan dan timestamp penggunaan device
     */
    private static function updateDeviceUsage(int $configId): void {
        try {
            $db = new Database();
            $today = date('Y-m-d');
            $db->query("UPDATE wa_config SET 
                last_used_at = NOW(),
                total_sent_today = CASE WHEN total_sent_today_date = :today THEN total_sent_today + 1 ELSE 1 END,
                total_sent_today_date = :today
                WHERE id = :id");
            $db->bind(':today', $today);
            $db->bind(':id', $configId);
            $db->execute();
        } catch (Exception $e) {
            error_log('[WhatsAppHelper::updateDeviceUsage] ' . $e->getMessage());
        }
    }

    /**
     * Simpan record ke tabel wa_queue
     */
    private static function insertQueue(array $data): int {
        try {
            $db = new Database();
            $db->query("INSERT INTO wa_queue (config_id, siswa_id, target_phone, message, notif_type, status, response, sent_at, created_at)
                        VALUES (:config_id, :siswa_id, :target_phone, :message, :notif_type, :status, :response, :sent_at, NOW())");
            $db->bind(':config_id',    $data['config_id']);
            $db->bind(':siswa_id',     $data['siswa_id']);
            $db->bind(':target_phone', $data['target_phone']);
            $db->bind(':message',      $data['message']);
            $db->bind(':notif_type',   $data['notif_type']);
            $db->bind(':status',       $data['status']);
            $db->bind(':response',     $data['response']);
            $db->bind(':sent_at',      $data['sent_at']);
            $db->execute();
            return (int)$db->lastInsertId();
        } catch (Exception $e) {
            error_log('[WhatsAppHelper::insertQueue] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Dapatkan QR Code WhatsApp dari Fonnte untuk pairing
     */
    public static function getQrCode(string $token): array {
        if (empty($token)) {
            return ['success' => false, 'message' => 'Token Fonnte tidak boleh kosong.'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.fonnte.com/qr',
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['type' => 'qr']),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . trim($token)
            ]
        ]);
        $responseStr = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        $resData = json_decode($responseStr, true);

        if ($httpCode === 200 && is_array($resData)) {
            if (!empty($resData['url'])) {
                return [
                    'success'   => true,
                    'connected' => false,
                    'qr'        => $resData['url'],
                    'message'   => 'Silakan scan QR Code berikut dengan aplikasi WhatsApp di HP Anda.'
                ];
            }
            if (($resData['reason'] ?? '') === 'device already connect') {
                return [
                    'success'   => true,
                    'connected' => true,
                    'message'   => 'Perangkat WhatsApp sudah terhubung.'
                ];
            }
        }

        return [
            'success' => false,
            'message' => $resData['reason'] ?? ($curlError ?: 'Gagal memuat QR Code dari Fonnte.')
        ];
    }

    /**
     * Putuskan koneksi perangkat Fonnte untuk pairing ulang
     */
    public static function disconnectDevice(string $token): array {
        if (empty($token)) {
            return ['success' => false, 'message' => 'Token Fonnte tidak boleh kosong.'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.fonnte.com/disconnect',
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . trim($token)
            ]
        ]);
        $responseStr = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        curl_close($ch);

        $resData = json_decode($responseStr, true);
        return [
            'success' => ($httpCode === 200),
            'message' => $resData['detail'] ?? ($resData['reason'] ?? 'Perangkat telah diputuskan.'),
            'raw'     => $resData
        ];
    }
}
