<?php
/**
 * TelegramHelper - Helper untuk mengirim notifikasi Telegram
 * 
 * Mendukung multi-bot (1 bot per kelas) dengan mapping berdasarkan nama kelas.
 * Token diambil dari database kolom `telegram_bot_token` di tabel `kelas`.
 * Chat ID diambil dari kolom `telegram_chat_id` di tabel `kelas`.
 * Mendukung notifikasi khusus aktivitas kehadiran siswa PKL (Datang & Pulang).
 */
class TelegramHelper {

    /**
     * Mapping token bot per kelas.
     * Kunci = nama kelas (lowercase, spasi → underscore)
     * Value = token bot dari BotFather
     */
    private static $botTokens = [
        '10_tsm' => '8938566171:AAF_rCSiPssAr9f7tADYERBuUEbuMzXQIHY',
        '11_tsm' => '8648616809:AAFinip7ugV2_tgK0jL49vsRYKRa4oWOwKA',
        '12_tsm' => '8678221689:AAEirv-fZD2tUj6zaJutpiiNUB31ODpT3yc',
        '10_tkr' => '8679366845:AAHnKoOSMavIuCWS5j4o2JzWwGAGGkN7Vn8',
        '11_tkr' => '8917742440:AAHH55zuWt4VJoNYi64Qac2BO07nj6btUaA',
        '12_tkr' => '8554319039:AAGoqz9j6uEvg06C371qIusK8YziCSfa0_8',
        '10_tkj' => '8912823759:AAFX9HaLTMUT9GqkYkSeI7OcK1oHk-lVbO8',
        '11_tkj' => '8621760759:AAFF1y7Oyyn8mXK9km0HeGzpvlXOQvXLcgk',
        '12_tkj' => '8876403453:AAEzCuMb5ZoM1ALhExYEG7iAkYaRpStVg0U',
    ];

    /**
     * Normalisasi nama kelas menjadi kunci mapping token.
     * Contoh: "10 TKJ" → "10_tkj", "11 TSM" → "11_tsm"
     */
    private static function normalizeKelas(string $nama_kelas): string {
        return strtolower(str_replace(' ', '_', trim($nama_kelas)));
    }

    /**
     * Ambil token bot berdasarkan nama kelas.
     * Jika nama kelas tidak ditemukan, fallback ke ENV.
     */
    public static function getTokenForKelas(string $nama_kelas): ?string {
        $key = self::normalizeKelas($nama_kelas);
        if (array_key_exists($key, self::$botTokens)) {
            return self::$botTokens[$key];
        }
        // Fallback ke environment variable (token tunggal)
        return getenv('TELEGRAM_BOT_TOKEN') ?: null;
    }

    /**
     * Kirim pesan Telegram.
     * 
     * @param string $chat_id       - Chat ID grup wali kelas (dari kolom kelas.telegram_chat_id)
     * @param string $message       - Pesan yang akan dikirim (mendukung HTML parse mode)
     * @param string|null $nama_kelas - Nama kelas untuk memilih bot yang tepat (misal: "10 TKJ")
     * @param string|null $custom_token - Token kustom (override, opsional)
     * @return string|bool          - Response dari Telegram API atau false jika gagal
     */
    public static function sendMessage(string $chat_id, string $message, ?string $nama_kelas = null, ?string $custom_token = null): string|bool {
        // Prioritas token: custom_token → token kelas → env fallback
        if ($custom_token) {
            $bot_token = $custom_token;
        } elseif ($nama_kelas) {
            $bot_token = self::getTokenForKelas($nama_kelas);
        } else {
            $bot_token = getenv('TELEGRAM_BOT_TOKEN') ?: null;
        }

        if (!$bot_token || empty($chat_id)) {
            error_log("[TelegramHelper] Gagal kirim: token atau chat_id kosong. Kelas: " . ($nama_kelas ?? 'N/A'));
            return false;
        }

        $url  = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $data = [
            'chat_id'    => $chat_id,
            'text'       => $message,
            'parse_mode' => 'HTML',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout 5 detik agar tidak nge-lag alat
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("[TelegramHelper] Gagal kirim ke Telegram. HTTP: {$httpCode}, Response: {$response}");
        }

        return $response;
    }

    /**
     * Format pesan notifikasi KEHADIRAN (Masuk) yang siap dikirim.
     */
    public static function formatPesanMasuk(string $nama, string $nis, string $kelas, string $jam, string $status): string {
        $statusEmoji = match ($status) {
            'hadir'     => '✅',
            'terlambat' => '⚠️',
            default     => '❓',
        };
        $statusTeks = strtoupper($status);

        return "<b>📋 NOTIFIKASI KEHADIRAN SISWA</b>\n\n" .
               "👤 <b>Nama :</b> {$nama}\n" .
               "🔖 <b>NIS   :</b> {$nis}\n" .
               "🏫 <b>Kelas :</b> {$kelas}\n" .
               "🕐 <b>Jam   :</b> {$jam}\n\n" .
               "{$statusEmoji} <b>Status: {$statusTeks}</b>";
    }

    /**
     * Format pesan notifikasi REQUEST PULANG CEPAT ke wali kelas.
     */
    public static function formatPesanPulangCepatRequest(
        string $nama, string $nis, string $kelas,
        string $jamRequest, string $jamPulangNormal
    ): string {
        return "<b>⚠️ PERMOHONAN PULANG CEPAT</b>\n\n" .
               "👤 <b>Nama :</b> {$nama}\n" .
               "🔖 <b>NIS   :</b> {$nis}\n" .
               "🏫 <b>Kelas :</b> {$kelas}\n" .
               "🕐 <b>Jam Request :</b> {$jamRequest}\n" .
               "📅 <b>Jam Pulang Normal :</b> {$jamPulangNormal}\n\n" .
               "⏳ Menunggu konfirmasi di website admin/wali kelas.";
    }

    /**
     * Format pesan notifikasi HASIL KONFIRMASI pulang cepat ke wali kelas.
     */
    public static function formatPesanKonfirmasiPulangCepat(
        string $nama, string $nis, string $kelas,
        string $status, string $alasanKategori, string $alasanDetail,
        string $diprosesOleh, string $jamDiproses
    ): string {
        $statusEmoji = $status === 'disetujui' ? '✅' : '❌';
        $statusTeks  = strtoupper($status);
        $alasan      = ucfirst($alasanKategori) . ($alasanDetail ? " — {$alasanDetail}" : '');

        return "<b>{$statusEmoji} PULANG CEPAT {$statusTeks}</b>\n\n" .
               "👤 <b>Nama  :</b> {$nama}\n" .
               "🔖 <b>NIS   :</b> {$nis}\n" .
               "🏫 <b>Kelas :</b> {$kelas}\n" .
               "📋 <b>Alasan :</b> {$alasan}\n" .
               "👨‍💼 <b>Diproses oleh :</b> {$diprosesOleh}\n" .
               "⏰ <b>Jam :</b> {$jamDiproses}";
    }

    /**
     * Format pesan notifikasi KEPULANGAN (Pulang normal) yang siap dikirim.
     */
    public static function formatPesanPulang(string $nama, string $nis, string $kelas, string $jam): string {
        return "<b>📋 NOTIFIKASI KEHADIRAN SISWA</b>\n\n" .
               "👤 <b>Nama :</b> {$nama}\n" .
               "🔖 <b>NIS   :</b> {$nis}\n" .
               "🏫 <b>Kelas :</b> {$kelas}\n" .
               "🕐 <b>Jam   :</b> {$jam}\n\n" .
               "✅ <b>Status: PULANG</b>";
    }

    /**
     * Format notifikasi kehadiran khusus siswa PKL (Datang / Pulang).
     * Memuat informasi: Nama, NIS/NISN, Kelas, Nama Tempat/Lokasi PKL, Waktu & Tanggal, Status (Datang/Pulang).
     */
    public static function formatPesanPkl(
        string $nama,
        string $nis,
        string $kelas,
        string $namaTempat,
        string $jam,
        string $tanggal,
        string $statusAktivitas,
        ?float $jarakMeter = null
    ): string {
        $isDatang = (stripos($statusAktivitas, 'datang') !== false || stripos($statusAktivitas, 'masuk') !== false);
        $statusEmoji = $isDatang ? '✅' : '🏠';
        $statusTeks  = $isDatang ? 'DATANG (CHECK-IN)' : 'PULANG (CHECK-OUT)';

        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $timeTs = strtotime($tanggal);
        $tglFormatted = date('d', $timeTs) . ' ' . ($bulanIndo[(int)date('m', $timeTs)] ?? date('F', $timeTs)) . ' ' . date('Y', $timeTs);

        $jarakStr = ($jarakMeter !== null) ? "\n📏 <b>Jarak GPS :</b> " . number_format($jarakMeter, 1, ',', '.') . " meter" : "";

        return "<b>📢 NOTIFIKASI PRESENSI SISWA PKL</b>\n\n" .
               "👤 <b>Nama :</b> {$nama}\n" .
               "🔖 <b>NIS/NISN :</b> {$nis}\n" .
               "🏫 <b>Kelas :</b> {$kelas}\n" .
               "🏢 <b>Tempat PKL :</b> {$namaTempat}\n" .
               "📅 <b>Tanggal :</b> {$tglFormatted}\n" .
               "🕐 <b>Waktu :</b> {$jam} WIB" .
               $jarakStr . "\n\n" .
               "{$statusEmoji} <b>Status: {$statusTeks}</b>";
    }

    /**
     * Kirim notifikasi Telegram khusus presensi PKL ke grup khusus atau chat kelas.
     * Hanya dikirim untuk siswa PKL setelah absensi berhasil disimpan ke database.
     */
    public static function sendPklNotification(
        $siswa,
        string $namaTempat,
        string $statusAktivitas,
        string $jam,
        string $tanggal,
        ?float $jarakMeter = null
    ): bool {
        // Cek switch aktif
        if (class_exists('AppSetting') && AppSetting::get('telegram_pkl_enabled', '1') === '0') {
            return false;
        }

        $nama  = $siswa->nama ?? $siswa->name ?? 'Siswa';
        $nis   = $siswa->nis ?? '-';
        $kelas = $siswa->nama_kelas ?? '-';

        $message = self::formatPesanPkl($nama, $nis, $kelas, $namaTempat, $jam, $tanggal, $statusAktivitas, $jarakMeter);

        // 1. Bot token yang digunakan: SELALU dari bot kelas siswa masing-masing
        // Misal kelas 12 TKJ memakai bot 12 TKJ, 11 TSM memakai bot 11 TSM
        $botToken = !empty($siswa->telegram_bot_token) ? $siswa->telegram_bot_token : self::getTokenForKelas($kelas);

        // 2. Tentukan Chat ID Penerima (Pembimbing PKL):
        // Prioritas:
        // a. telegram_chat_id_pkl pada kelas siswa
        // b. pembimbing_telegram_chat_id dari akun Guru Pembimbing
        // c. telegram_pkl_chat_id dari pengaturan global AppSetting
        // d. fallback ke telegram_chat_id kelas siswa
        $chatId = null;
        if (!empty($siswa->telegram_chat_id_pkl)) {
            $chatId = $siswa->telegram_chat_id_pkl;
        } elseif (!empty($siswa->pembimbing_telegram_chat_id)) {
            $chatId = $siswa->pembimbing_telegram_chat_id;
        } elseif (class_exists('AppSetting') && !empty(AppSetting::get('telegram_pkl_chat_id'))) {
            $chatId = AppSetting::get('telegram_pkl_chat_id');
        } elseif (!empty($siswa->telegram_chat_id)) {
            $chatId = $siswa->telegram_chat_id;
        }

        if (empty($chatId) || empty($botToken)) {
            error_log("[TelegramHelper] Presensi PKL batal kirim: chatId atau botToken kosong untuk kelas: {$kelas}");
            return false;
        }

        $res = self::sendMessage($chatId, $message, $kelas, $botToken);
        return ($res !== false);
    }
}
