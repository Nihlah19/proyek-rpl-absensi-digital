<?php
/**
 * WaController - Manajemen Tunggal Konfigurasi WhatsApp Multi-Token Fonnte
 * 
 * Fitur:
 * 1. 10 Token Gateway Fonnte (9 Slot Kelas Sesuai Wali + 1 Slot Cadangan Auto-Failover)
 * 2. Sistem Auto-Failover Cerdas (Oper ke Cadangan jika batas max/kuota habis, lalu oper ke token lain yang masih ada)
 * 3. Format Pesan Presensi Siswa (Masuk & Pulang)
 * 4. Uji Coba Pengiriman Pesan
 * 5. Log Riwayat Pengiriman & Auto-Oper Failover
 */
class WaController extends Controller {

    private $db;

    public function __construct() {
        AuthHelper::checkRole(['admin']);
        $this->db = new Database();
        $this->ensureTables();
    }

    private static $tablesEnsured = false;

    /**
     * Memastikan 10 slot wa_config dan kolom pendukung terstruktur dengan benar
     */
    private function ensureTables(): void {
        if (self::$tablesEnsured) return;
        try {
            // Pastikan kolom delay_seconds dan priority ada
            $this->db->query("SHOW COLUMNS FROM wa_config LIKE 'delay_seconds'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE wa_config ADD COLUMN delay_seconds INT NOT NULL DEFAULT 12 AFTER max_per_minute");
                $this->db->execute();
            }

            $this->db->query("SHOW COLUMNS FROM wa_config LIKE 'priority'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE wa_config ADD COLUMN priority INT NOT NULL DEFAULT 1 AFTER delay_seconds");
                $this->db->execute();
            }

            $this->db->query("SHOW COLUMNS FROM wa_config LIKE 'is_exhausted'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE wa_config ADD COLUMN is_exhausted TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active");
                $this->db->execute();
            }

            $this->db->query("SHOW COLUMNS FROM wa_config LIKE 'exhausted_reason'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE wa_config ADD COLUMN exhausted_reason VARCHAR(255) NULL AFTER is_exhausted");
                $this->db->execute();
            }

            $this->db->query("SHOW COLUMNS FROM wa_config LIKE 'exhausted_month'");
            if (!$this->db->single()) {
                $this->db->query("ALTER TABLE wa_config ADD COLUMN exhausted_month VARCHAR(7) NULL AFTER exhausted_reason");
                $this->db->execute();
            }

            // Inisialisasi 10 Slot: 9 Kelas Wali + 1 Cadangan
            $slotsData = [
                1  => ['label' => 'Slot 1: Kelas 10 TKJ', 'kelas_id' => 10, 'priority' => 1],
                2  => ['label' => 'Slot 2: Kelas 10 TKR', 'kelas_id' => 7,  'priority' => 2],
                3  => ['label' => 'Slot 3: Kelas 10 TSM', 'kelas_id' => 4,  'priority' => 3],
                4  => ['label' => 'Slot 4: Kelas 11 TKJ', 'kelas_id' => 2,  'priority' => 4],
                5  => ['label' => 'Slot 5: Kelas 11 TKR', 'kelas_id' => 8,  'priority' => 5],
                6  => ['label' => 'Slot 6: Kelas 11 TSM', 'kelas_id' => 5,  'priority' => 6],
                7  => ['label' => 'Slot 7: Kelas 12 TKJ', 'kelas_id' => 3,  'priority' => 7],
                8  => ['label' => 'Slot 8: Kelas 12 TKR', 'kelas_id' => 9,  'priority' => 8],
                9  => ['label' => 'Slot 9: Kelas 12 TSM', 'kelas_id' => 6,  'priority' => 9],
                10 => ['label' => 'Slot 10: Token Cadangan (Backup Failover)', 'kelas_id' => null, 'priority' => 10],
            ];

            foreach ($slotsData as $id => $info) {
                $this->db->query("SELECT id FROM wa_config WHERE id = :id");
                $this->db->bind(':id', $id);
                if (!$this->db->single()) {
                    $this->db->query("INSERT INTO wa_config (id, label, kelas_id, token, device_number, max_per_minute, delay_seconds, priority, is_active)
                                      VALUES (:id, :label, :kelas_id, '', '', 5, 12, :priority, 1)");
                    $this->db->bind(':id',       $id);
                    $this->db->bind(':label',    $info['label']);
                    $this->db->bind(':kelas_id', $info['kelas_id']);
                    $this->db->bind(':priority', $info['priority']);
                    $this->db->execute();
                }
            }
            self::$tablesEnsured = true;
        } catch (Exception $e) {
            error_log('[WaController::ensureTables] ' . $e->getMessage());
        }
    }

    /**
     * HALAMAN UTAMA TUNGGAL: Konfigurasi WhatsApp Terpadu
     */
    public function index() {
        // 1. Statistik Pengiriman Antrean
        $this->db->query("SELECT 
            COUNT(CASE WHEN status = 'sent' AND DATE(created_at) = CURDATE() THEN 1 END) as sent_today,
            COUNT(CASE WHEN status = 'sent' AND notif_type = 'masuk' AND DATE(created_at) = CURDATE() THEN 1 END) as masuk_today,
            COUNT(CASE WHEN status = 'sent' AND notif_type = 'pulang' AND DATE(created_at) = CURDATE() THEN 1 END) as pulang_today,
            COUNT(CASE WHEN status = 'failed' AND DATE(created_at) = CURDATE() THEN 1 END) as failed_today,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_total,
            COUNT(CASE WHEN status = 'sent' THEN 1 END) as sent_total,
            COUNT(*) as total_all
            FROM wa_queue");
        $statsRaw = $this->db->single();

        $sentToday    = (int)($statsRaw->sent_today ?? 0);
        $masukToday   = (int)($statsRaw->masuk_today ?? 0);
        $pulangToday  = (int)($statsRaw->pulang_today ?? 0);
        $failedToday  = (int)($statsRaw->failed_today ?? 0);
        $pendingTotal = (int)($statsRaw->pending_total ?? 0);
        $sentTotal    = (int)($statsRaw->sent_total ?? 0);
        $totalAll     = (int)($statsRaw->total_all ?? 0);
        $successRate  = ($sentTotal + $failedToday > 0) ? round(($sentTotal / ($sentTotal + $failedToday)) * 100, 1) : 100;

        // 2. Status & Format Template Pesan Masuk & Pulang
        $isEnabledMasuk  = AppSetting::get('wa_notif_masuk_enabled', '1');
        $templateMasuk   = AppSetting::get('wa_notif_masuk_template', WhatsAppHelper::DEFAULT_TEMPLATE_MASUK);
        $isEnabledPulang = AppSetting::get('wa_notif_pulang_enabled', '1');
        $templatePulang  = AppSetting::get('wa_notif_pulang_template', WhatsAppHelper::DEFAULT_TEMPLATE_PULANG);

        // 3. Log Riwayat Pengiriman Terbaru (15 log terakhir)
        $this->db->query("SELECT q.*, u.name as nama_siswa, s.nis, k.nama_kelas, c.label as device_label
                          FROM wa_queue q
                          LEFT JOIN siswa s ON s.id = q.siswa_id
                          LEFT JOIN users u ON u.id = s.user_id
                          LEFT JOIN kelas k ON k.id = s.kelas_id
                          LEFT JOIN wa_config c ON c.id = q.config_id
                          ORDER BY q.id DESC LIMIT 15");
        $recentLogs = $this->db->resultSet() ?: [];

        // 4. Data Siswa untuk Uji Coba Pengiriman
        $this->db->query("SELECT s.id, u.name, s.nis, s.no_hp_ortu, s.kelas_id, k.nama_kelas
                          FROM siswa s
                          JOIN users u ON u.id = s.user_id
                          LEFT JOIN kelas k ON k.id = s.kelas_id
                          ORDER BY (s.no_hp_ortu IS NOT NULL AND s.no_hp_ortu != '') DESC, u.name ASC
                          LIMIT 500");
        $siswaList = $this->db->resultSet() ?: [];

        // 5. Data 10 Slot Token Fonnte (9 Kelas Wali + 1 Cadangan)
        $this->db->query("SELECT c.*, k.nama_kelas, k.class_code, u.name as wali_nama
                          FROM wa_config c
                          LEFT JOIN kelas k ON k.id = c.kelas_id
                          LEFT JOIN users u ON u.id = k.wali_kelas_id
                          ORDER BY c.priority ASC, c.id ASC");
        $allSlots = $this->db->resultSet() ?: [];

        // Kelompokkan 9 Slot Kelas dan 1 Slot Cadangan
        $classSlots   = [];
        $cadanganSlot = null;
        $totalFilled  = 0;
        $totalActive  = 0;

        foreach ($allSlots as $slot) {
            if (!empty($slot->token)) {
                $totalFilled++;
            }
            if (!empty($slot->is_active) && empty($slot->is_exhausted)) {
                $totalActive++;
            }

            if ((int)$slot->id === 10 || stripos($slot->label, 'cadangan') !== false) {
                $cadanganSlot = $slot;
            } else {
                $classSlots[] = $slot;
            }
        }

        $data = [
            'judul'           => 'Konfigurasi WhatsApp',
            'activeMenu'      => 'wa_config',
            'allSlots'        => $allSlots,
            'classSlots'      => $classSlots,
            'cadanganSlot'    => $cadanganSlot,
            'totalFilled'     => $totalFilled,
            'totalActive'     => $totalActive,
            'isEnabledMasuk'  => ($isEnabledMasuk === '1' || $isEnabledMasuk === 1 || $isEnabledMasuk === true),
            'templateMasuk'   => $templateMasuk,
            'isEnabledPulang' => ($isEnabledPulang === '1' || $isEnabledPulang === 1 || $isEnabledPulang === true),
            'templatePulang'  => $templatePulang,
            'stats'           => [
                'sent_today'   => $sentToday,
                'masuk_today'  => $masukToday,
                'pulang_today' => $pulangToday,
                'failed_today' => $failedToday,
                'pending'      => $pendingTotal,
                'sent_total'   => $sentTotal,
                'total_all'    => $totalAll,
                'success_rate' => $successRate,
            ],
            'recentLogs'      => $recentLogs,
            'siswaList'       => $siswaList,
        ];

        $this->view('templates/header', $data);
        $this->view('admin/wa_config', $data);
        $this->view('templates/footer');
    }

    /**
     * Alias routes (redirect ke index tunggal /wa)
     */
    public function config() {
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    public function notif_masuk() {
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    public function notif_pulang() {
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Simpan Semua 10 Token Sekaligus (Batch Save)
     */
    public function save_all_tokens() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $tokens  = $_POST['tokens'] ?? [];
        $devices = $_POST['device_numbers'] ?? [];
        $actives = $_POST['is_actives'] ?? [];

        foreach ($tokens as $slotId => $token) {
            $slotId = (int)$slotId;
            if ($slotId < 1 || $slotId > 10) continue;

            $tokenClean = trim($token);
            $devClean   = WhatsAppHelper::normalizePhone(trim($devices[$slotId] ?? ''));
            $isActive   = isset($actives[$slotId]) ? 1 : 0;

            $this->db->query("UPDATE wa_config SET 
                token         = :token,
                device_number = :dev,
                is_active     = :act
                WHERE id = :id");
            $this->db->bind(':token', $tokenClean);
            $this->db->bind(':dev',   $devClean);
            $this->db->bind(':act',   $isActive);
            $this->db->bind(':id',    $slotId);
            $this->db->execute();
        }

        Flasher::setFlash('Berhasil Disimpan', 'Konfigurasi 10 Token Fonnte (9 Kelas Wali + 1 Cadangan) berhasil diperbarui.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Simpan satu slot konfigurasi
     */
    public function save_slot() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $slotId       = (int)($_POST['slot_id'] ?? 0);
        $token        = trim($_POST['token'] ?? '');
        $deviceNumber = WhatsAppHelper::normalizePhone(trim($_POST['device_number'] ?? ''));
        $isActive     = isset($_POST['is_active']) ? 1 : 0;

        if ($slotId >= 1 && $slotId <= 10) {
            $this->db->query("UPDATE wa_config SET 
                token         = :token,
                device_number = :dev_num,
                is_active     = :is_act
                WHERE id = :id");
            $this->db->bind(':token',   $token);
            $this->db->bind(':dev_num', $deviceNumber);
            $this->db->bind(':is_act',  $isActive);
            $this->db->bind(':id',      $slotId);
            $this->db->execute();

            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => "Slot #{$slotId} berhasil diperbarui."]);
                exit;
            }

            Flasher::setFlash('Slot Diperbarui', "Pengaturan Token Fonnte Slot #{$slotId} berhasil disimpan.", 'success');
        }

        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Simpan konfigurasi umum / delay anti-banned
     */
    public function save_config() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $maxPerMin = max(1, (int)($_POST['max_per_minute'] ?? 5));
        $delaySec  = max(5, (int)($_POST['delay_seconds'] ?? 12));

        $this->db->query("UPDATE wa_config SET max_per_minute = :max_pm, delay_seconds = :delay_sec");
        $this->db->bind(':max_pm',    $maxPerMin);
        $this->db->bind(':delay_sec', $delaySec);
        $this->db->execute();

        Flasher::setFlash('Konfigurasi Disimpan', 'Pengaturan jeda anti-banned berhasil diperbarui untuk seluruh slot token.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Reset status limit kuota suatu slot
     */
    public function reset_quota() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $slotId = (int)($_POST['slot_id'] ?? 0);
        if ($slotId > 0) {
            WhatsAppHelper::resetSlotExhausted($slotId);
            Flasher::setFlash('Status Kuota Direset', "Status batas limit kuota pada Slot #{$slotId} berhasil direset ke status aktif.", 'success');
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "Status kuota Slot #{$slotId} berhasil direset."]);
            exit;
        }

        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Toggle fitur notifikasi WA masuk (Aktif/Nonaktif)
     */
    public function toggle_masuk() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $current = AppSetting::get('wa_notif_masuk_enabled', '1');
        $newVal  = ($current === '1' || $current === 1) ? '0' : '1';
        AppSetting::set('wa_notif_masuk_enabled', $newVal, 'Status aktif notifikasi WhatsApp saat absen masuk (hadir) siswa');

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'enabled' => ($newVal === '1'),
                'message' => ($newVal === '1') 
                    ? 'Notifikasi WA absen masuk (hadir) DIAKTIFKAN!' 
                    : 'Notifikasi WA absen masuk (hadir) DINONAKTIFKAN.'
            ]);
            exit;
        }

        Flasher::setFlash('Pengaturan Diperbarui', ($newVal === '1') 
            ? 'Notifikasi WA saat siswa absen masuk berhasil diaktifkan.' 
            : 'Notifikasi WA saat siswa absen masuk dinonaktifkan.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Toggle fitur notifikasi WA pulang (Aktif/Nonaktif)
     */
    public function toggle_pulang() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $current = AppSetting::get('wa_notif_pulang_enabled', '1');
        $newVal  = ($current === '1' || $current === 1) ? '0' : '1';
        AppSetting::set('wa_notif_pulang_enabled', $newVal, 'Status aktif notifikasi WhatsApp saat absen pulang siswa');

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'enabled' => ($newVal === '1'),
                'message' => ($newVal === '1') 
                    ? 'Notifikasi WA absen pulang DIAKTIFKAN!' 
                    : 'Notifikasi WA absen pulang DINONAKTIFKAN.'
            ]);
            exit;
        }

        Flasher::setFlash('Pengaturan Diperbarui', ($newVal === '1') 
            ? 'Notifikasi WA saat siswa absen pulang berhasil diaktifkan.' 
            : 'Notifikasi WA saat siswa absen pulang dinonaktifkan.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Simpan format pesan template masuk
     */
    public function save_template_masuk() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $template = trim($_POST['template'] ?? '');
        if (empty($template)) {
            $template = WhatsAppHelper::DEFAULT_TEMPLATE_MASUK;
        }

        AppSetting::set('wa_notif_masuk_template', $template, 'Format template pesan WhatsApp notifikasi kehadiran siswa');

        Flasher::setFlash('Berhasil Disimpan', 'Format pesan notifikasi presensi masuk (hadir) berhasil disimpan.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Reset format pesan masuk ke default
     */
    public function reset_template_masuk() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        AppSetting::set('wa_notif_masuk_template', WhatsAppHelper::DEFAULT_TEMPLATE_MASUK, 'Format template pesan WhatsApp notifikasi kehadiran siswa');
        Flasher::setFlash('Reset Berhasil', 'Format pesan masuk telah dikembalikan ke standar bawaan.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    /**
     * Simpan format pesan template pulang
     */
    public function save_template() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        $template = trim($_POST['template'] ?? '');
        if (empty($template)) {
            $template = WhatsAppHelper::DEFAULT_TEMPLATE_PULANG;
        }

        AppSetting::set('wa_notif_pulang_template', $template, 'Format template pesan WhatsApp notifikasi kepulangan siswa');

        Flasher::setFlash('Berhasil Disimpan', 'Format pesan notifikasi presensi pulang berhasil disimpan.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    public function save_template_pulang() {
        $this->save_template();
    }

    /**
     * Reset format pesan pulang ke default
     */
    public function reset_template() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wa'); exit;
        }

        AppSetting::set('wa_notif_pulang_template', WhatsAppHelper::DEFAULT_TEMPLATE_PULANG, 'Format template pesan WhatsApp notifikasi kepulangan siswa');
        Flasher::setFlash('Reset Berhasil', 'Format pesan kepulangan telah dikembalikan ke standar bawaan.', 'success');
        header('Location: ' . BASEURL . '/wa');
        exit;
    }

    public function reset_template_pulang() {
        $this->reset_template();
    }

    /**
     * AJAX: Cek status koneksi perangkat Fonnte
     */
    public function check_device() {
        header('Content-Type: application/json');
        $token = trim($_POST['token'] ?? '');
        $slotId = (int)($_POST['slot_id'] ?? 0);

        if (empty($token) && $slotId > 0) {
            $slot = WhatsAppHelper::getConfigById($slotId);
            $token = $slot ? $slot->token : '';
        }

        if (empty($token)) {
            echo json_encode([
                'success' => false,
                'status'  => false,
                'message' => 'Token API Fonnte belum diisi. Silakan masukkan token Anda terlebih dahulu.'
            ]);
            exit;
        }

        $result = WhatsAppHelper::checkDevice($token);

        // Jika berhasil terhubung dan ada slotId, otomatis simpan token & nomor perangkat ke slot tersebut
        if (!empty($result['success']) && !empty($result['connected']) && $slotId > 0) {
            $devNum = $result['device_info']['device'] ?? ($result['device'] ?? '');
            try {
                $this->db->query("UPDATE wa_config SET 
                    token = :token,
                    device_number = CASE WHEN (device_number IS NULL OR device_number = '' OR device_number = '0') AND :dev != '-' THEN :dev ELSE device_number END,
                    is_active = 1,
                    is_exhausted = 0,
                    exhausted_reason = NULL,
                    exhausted_month = NULL
                    WHERE id = :id");
                $this->db->bind(':token', $token);
                $this->db->bind(':dev',   $devNum);
                $this->db->bind(':id',    $slotId);
                $this->db->execute();
            } catch (Exception $e) {
                // Jangan gagalkan response jika update db gagal
            }
        }

        echo json_encode($result);
        exit;
    }

    /**
     * AJAX: Uji coba kirim pesan WhatsApp (Mendukung 'masuk' maupun 'pulang' & pemilihan slot)
     */
    public function test_send() {
        header('Content-Type: application/json');

        $phone       = trim($_POST['phone'] ?? '');
        $namaSiswa   = trim($_POST['nama_siswa'] ?? 'Nihlah Auliya');
        $kelas       = trim($_POST['kelas'] ?? '10 TKJ');
        $nis         = trim($_POST['nis'] ?? '20000');
        $jam         = trim($_POST['jam'] ?? date('H:i'));
        $customPesan = trim($_POST['custom_message'] ?? '');
        $notifType   = trim($_POST['notif_type'] ?? 'pulang');
        $slotId      = !empty($_POST['slot_id']) ? (int)$_POST['slot_id'] : null;

        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp tujuan wajib diisi.']);
            exit;
        }

        if (!empty($customPesan)) {
            $message = $customPesan;
        } elseif ($notifType === 'masuk') {
            $statusText = trim($_POST['status_text'] ?? '✅ Hadir Tepat Waktu');
            $message    = WhatsAppHelper::formatPesanMasuk($namaSiswa, $kelas, $jam, $nis, $statusText);
        } else {
            $message    = WhatsAppHelper::formatPesanPulang($namaSiswa, $kelas, $jam, $nis);
        }

        $siswaId = null;
        $kelasId = null;
        if (!empty($_POST['siswa_id'])) {
            $siswaId = (int)$_POST['siswa_id'];
        }
        if (!empty($_POST['kelas_id'])) {
            $kelasId = (int)$_POST['kelas_id'];
        }

        $res = WhatsAppHelper::send($phone, $message, $siswaId, $notifType, $slotId, $kelasId);
        echo json_encode($res);
        exit;
    }

    /**
     * AJAX: Uji coba kirim pesan Masuk (backward compatibility)
     */
    public function test_send_masuk() {
        $_POST['notif_type'] = 'masuk';
        $_POST['jam'] = $_POST['jam_masuk'] ?? date('H:i');
        $this->test_send();
    }

    /**
     * AJAX: Kirim ulang pesan dari wa_queue
     */
    public function resend_queue() {
        header('Content-Type: application/json');
        $queueId = (int)($_POST['queue_id'] ?? 0);

        if (!$queueId) {
            echo json_encode(['success' => false, 'message' => 'Queue ID tidak valid.']);
            exit;
        }

        $this->db->query("SELECT * FROM wa_queue WHERE id = :id");
        $this->db->bind(':id', $queueId);
        $queue = $this->db->single();

        if (!$queue) {
            echo json_encode(['success' => false, 'message' => 'Data antrean tidak ditemukan.']);
            exit;
        }

        $res = WhatsAppHelper::send($queue->target_phone, $queue->message, $queue->siswa_id, $queue->notif_type, $queue->config_id);

        $newStatus = $res['success'] ? (!empty($res['is_pending']) ? 'pending' : 'sent') : 'failed';
        $this->db->query("UPDATE wa_queue SET 
            status   = :status,
            attempt  = attempt + 1,
            response = :resp,
            sent_at  = CASE WHEN :is_succ = 1 AND :is_pend = 0 THEN NOW() ELSE sent_at END
            WHERE id = :id");
        $this->db->bind(':status',  $newStatus);
        $this->db->bind(':resp',    $res['message'] . ' | ' . json_encode($res['raw'] ?? ''));
        $this->db->bind(':is_succ', $res['success'] ? 1 : 0);
        $this->db->bind(':is_pend', !empty($res['is_pending']) ? 1 : 0);
        $this->db->bind(':id',      $queueId);
        $this->db->execute();

        echo json_encode($res);
        exit;
    }

    /**
     * AJAX: Ambil QR Code WhatsApp untuk slot tertentu
     */
    public function get_qr() {
        header('Content-Type: application/json');
        $slotId = (int)($_POST['slot_id'] ?? 0);
        $token  = trim($_POST['token'] ?? '');

        if (empty($token) && $slotId > 0) {
            $slot = WhatsAppHelper::getConfigById($slotId);
            $token = $slot ? $slot->token : '';
        }

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token Fonnte untuk slot ini belum diisi.']);
            exit;
        }

        $res = WhatsAppHelper::getQrCode($token);
        echo json_encode($res);
        exit;
    }

    /**
     * AJAX: Putuskan koneksi WhatsApp (Disconnect) dan minta QR baru
     */
    public function reconnect_device() {
        header('Content-Type: application/json');
        $slotId = (int)($_POST['slot_id'] ?? 0);
        $token  = trim($_POST['token'] ?? '');

        if (empty($token) && $slotId > 0) {
            $slot = WhatsAppHelper::getConfigById($slotId);
            $token = $slot ? $slot->token : '';
        }

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token Fonnte untuk slot ini belum diisi.']);
            exit;
        }

        // Putuskan koneksi terlebih dahulu
        WhatsAppHelper::disconnectDevice($token);
        // Beri jeda 1 detik agar socket Fonnte bersih
        sleep(1);
        // Dapatkan QR code baru
        $res = WhatsAppHelper::getQrCode($token);
        echo json_encode($res);
        exit;
    }
}
