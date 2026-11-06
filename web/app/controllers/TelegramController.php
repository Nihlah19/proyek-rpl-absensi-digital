<?php
class TelegramController extends Controller {

    private $db;

    // Daftar 9 bot — sesuai dengan TelegramHelper
    private $bots = [
        ['nama_kelas' => '10 TSM', 'username' => 'walisiswatsm10bot', 'token' => '8938566171:AAF_rCSiPssAr9f7tADYERBuUEbuMzXQIHY'],
        ['nama_kelas' => '11 TSM', 'username' => 'walisiswatsm11bot', 'token' => '8648616809:AAFinip7ugV2_tgK0jL49vsRYKRa4oWOwKA'],
        ['nama_kelas' => '12 TSM', 'username' => 'walisiswatsm12bot', 'token' => '8678221689:AAEirv-fZD2tUj6zaJutpiiNUB31ODpT3yc'],
        ['nama_kelas' => '10 TKR', 'username' => 'walisiswatkr10bot', 'token' => '8679366845:AAHnKoOSMavIuCWS5j4o2JzWwGAGGkN7Vn8'],
        ['nama_kelas' => '11 TKR', 'username' => 'walisiswatkr11bot', 'token' => '8917742440:AAHH55zuWt4VJoNYi64Qac2BO07nj6btUaA'],
        ['nama_kelas' => '12 TKR', 'username' => 'walisiswatkr12bot', 'token' => '8554319039:AAGoqz9j6uEvg06C371qIusK8YziCSfa0_8'],
        ['nama_kelas' => '10 TKJ', 'username' => 'walisiswatkj10bot', 'token' => '8912823759:AAFX9HaLTMUT9GqkYkSeI7OcK1oHk-lVbO8'],
        ['nama_kelas' => '11 TKJ', 'username' => 'walisiswatkj11bot', 'token' => '8621760759:AAFF1y7Oyyn8mXK9km0HeGzpvlXOQvXLcgk'],
        ['nama_kelas' => '12 TKJ', 'username' => 'walisiswatkj12bot', 'token' => '8876403453:AAEzCuMb5ZoM1ALhExYEG7iAkYaRpStVg0U'],
    ];

    public function __construct() {
        AuthHelper::checkRole(['admin']);
        $this->db = new Database();
    }

    public function index() {
        // Pastikan kolom tersedia
        foreach ([
            "ALTER TABLE kelas ADD COLUMN telegram_bot_token VARCHAR(100) NULL",
            "ALTER TABLE kelas ADD COLUMN telegram_chat_id VARCHAR(50) NULL",
            "ALTER TABLE kelas ADD COLUMN telegram_chat_id_pkl VARCHAR(50) NULL",
        ] as $sql) {
            try { $this->db->query($sql); $this->db->execute(); } catch (Exception $e) { /* sudah ada */ }
        }

        // Simpan token ke DB jika belum
        foreach ($this->bots as $bot) {
            $this->db->query("SELECT id FROM kelas WHERE nama_kelas LIKE :nama");
            $this->db->bind(':nama', '%' . trim($bot['nama_kelas']) . '%');
            $found = $this->db->single();
            if ($found) {
                $this->db->query("UPDATE kelas SET telegram_bot_token = :token WHERE id = :id");
                $this->db->bind(':token', $bot['token']);
                $this->db->bind(':id',    $found->id);
                $this->db->execute();
            }
        }

        $kelasData = $this->getKelasData();
        $flash     = [];
        $results   = [];

        // ─── Handle POST ────────────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // SAVE ALL
            if (isset($_POST['save_all'])) {
                $saved = 0;
                foreach ($this->bots as $bot) {
                    $keyWali = 'uid_' . preg_replace('/\s+/', '_', $bot['nama_kelas']);
                    $keyPkl  = 'uid_pkl_' . preg_replace('/\s+/', '_', $bot['nama_kelas']);
                    $newWali = trim($_POST[$keyWali] ?? '');
                    $newPkl  = trim($_POST[$keyPkl] ?? '');
                    $dbId    = $kelasData[$bot['nama_kelas']]['db_id'] ?? null;

                    if ($dbId) {
                        $this->db->query("
                            UPDATE kelas 
                            SET telegram_chat_id = :uid,
                                telegram_chat_id_pkl = :uid_pkl
                            WHERE id = :id
                        ");
                        $this->db->bind(':uid', !empty($newWali) ? $newWali : null);
                        $this->db->bind(':uid_pkl', !empty($newPkl) ? $newPkl : null);
                        $this->db->bind(':id',  $dbId);
                        $this->db->execute();

                        $kelasData[$bot['nama_kelas']]['chat_id']     = $newWali;
                        $kelasData[$bot['nama_kelas']]['chat_id_pkl'] = $newPkl;
                        $saved++;
                    }
                }
                Flasher::setFlash('Berhasil', "Konfigurasi ID penerima untuk {$saved} kelas berhasil disimpan!", 'success');
                header('Location: ' . BASEURL . '/telegram');
                exit;
            }

            // SAVE SATU KELAS
            if (isset($_POST['save_single'])) {
                $namaKelas  = $_POST['kelas_name'] ?? '';
                $newUID     = trim($_POST['single_uid'] ?? '');
                $newUIDPkl  = trim($_POST['single_uid_pkl'] ?? '');
                $dbId       = $kelasData[$namaKelas]['db_id'] ?? null;

                if ($dbId) {
                    $this->db->query("
                        UPDATE kelas 
                        SET telegram_chat_id = :uid,
                            telegram_chat_id_pkl = :uid_pkl
                        WHERE id = :id
                    ");
                    $this->db->bind(':uid', !empty($newUID) ? $newUID : null);
                    $this->db->bind(':uid_pkl', !empty($newUIDPkl) ? $newUIDPkl : null);
                    $this->db->bind(':id',  $dbId);
                    $this->db->execute();
                    Flasher::setFlash('Berhasil', "ID penerima Telegram kelas {$namaKelas} berhasil disimpan!", 'success');
                } else {
                    Flasher::setFlash('Gagal', 'Kelas tidak ditemukan di database.', 'error');
                }
                header('Location: ' . BASEURL . '/telegram');
                exit;
            }

            // TEST BOT REGULER (WALI KELAS)
            if (isset($_POST['test_single'])) {
                $namaKelas = $_POST['kelas_name'] ?? '';
                $chatId    = $kelasData[$namaKelas]['chat_id'] ?? '';
                $token     = '';
                foreach ($this->bots as $b) {
                    if ($b['nama_kelas'] === $namaKelas) { $token = $b['token']; break; }
                }
                if (!empty($chatId) && !empty($token)) {
                    $pesan = "🔔 <b>TEST NOTIFIKASI WALI KELAS</b>\n\n🏫 Kelas: <b>{$namaKelas}</b>\n⏰ " . date('d/m/Y H:i:s') . "\n\n✅ Jalur notifikasi reguler siap digunakan.";
                    $r = $this->sendTelegram($token, $chatId, $pesan);
                    $results[$namaKelas] = $r;
                    if ($r['ok']) {
                        Flasher::setFlash('Berhasil', "Test notifikasi reguler kelas {$namaKelas} berhasil dikirim!", 'success');
                    } else {
                        Flasher::setFlash('Gagal', "Test gagal: " . ($r['description'] ?? 'Unknown error'), 'error');
                    }
                } else {
                    Flasher::setFlash('Gagal', "Isi User ID Wali Kelas untuk {$namaKelas} terlebih dahulu.", 'error');
                }
                header('Location: ' . BASEURL . '/telegram');
                exit;
            }

            // TEST BOT PKL (PEMBIMBING PKL)
            if (isset($_POST['test_single_pkl'])) {
                $namaKelas = $_POST['kelas_name'] ?? '';
                $chatId    = $kelasData[$namaKelas]['chat_id_pkl'] ?? '';
                $token     = '';
                foreach ($this->bots as $b) {
                    if ($b['nama_kelas'] === $namaKelas) { $token = $b['token']; break; }
                }
                if (!empty($chatId) && !empty($token)) {
                    $testData = [
                        'nama'            => 'Siswa Contoh PKL',
                        'nis'             => '20260101',
                        'nama_kelas'      => $namaKelas,
                        'nama_tempat'     => 'PT Industri Mitra Teknologi (Test)',
                        'jam'             => date('H:i'),
                        'tanggal'         => date('Y-m-d'),
                        'statusAktivitas' => 'Datang',
                        'jarakMeter'      => 14.5
                    ];
                    $pesan = TelegramHelper::formatPesanPkl(
                        $testData['nama'],
                        $testData['nis'],
                        $testData['nama_kelas'],
                        $testData['nama_tempat'],
                        $testData['jam'],
                        $testData['tanggal'],
                        $testData['statusAktivitas'],
                        $testData['jarakMeter']
                    );
                    $r = $this->sendTelegram($token, $chatId, $pesan);
                    $results[$namaKelas . '_pkl'] = $r;
                    if ($r['ok']) {
                        Flasher::setFlash('Berhasil', "Test notifikasi PKL kelas {$namaKelas} berhasil dikirim ke Pembimbing PKL!", 'success');
                    } else {
                        Flasher::setFlash('Gagal', "Test PKL gagal: " . ($r['description'] ?? 'Unknown error'), 'error');
                    }
                } else {
                    Flasher::setFlash('Gagal', "Isi User ID Pembimbing PKL untuk {$namaKelas} terlebih dahulu.", 'error');
                }
                header('Location: ' . BASEURL . '/telegram');
                exit;
            }

            // TEST SEMUA
            if (isset($_POST['test_all'])) {
                $ok = 0; $err = 0;
                foreach ($this->bots as $bot) {
                    $chatId = $kelasData[$bot['nama_kelas']]['chat_id'] ?? '';
                    if (!empty($chatId)) {
                        $pesan = "🔔 <b>TEST NOTIFIKASI</b>\n\n🏫 Kelas: <b>{$bot['nama_kelas']}</b>\n🤖 @{$bot['username']}\n⏰ " . date('d/m/Y H:i:s');
                        $r = $this->sendTelegram($bot['token'], $chatId, $pesan);
                        $results[$bot['nama_kelas']] = $r;
                        $r['ok'] ? $ok++ : $err++;
                    } else {
                        $results[$bot['nama_kelas']] = ['ok' => false, 'description' => 'User ID belum diisi'];
                        $err++;
                    }
                }
                $type = ($err === 0) ? 'success' : (($ok > 0) ? 'warning' : 'error');
                Flasher::setFlash('Test Selesai', "Test: {$ok} berhasil, {$err} gagal.", $type);
                $_SESSION['tele_results'] = $results;
                header('Location: ' . BASEURL . '/telegram');
                exit;
            }
        }

        // Ambil hasil test dari session jika ada
        $results = $_SESSION['tele_results'] ?? [];
        unset($_SESSION['tele_results']);

        $totalSet    = count(array_filter($kelasData, fn($k) => !empty($k['chat_id'])));
        $totalSetPkl = count(array_filter($kelasData, fn($k) => !empty($k['chat_id_pkl'])));
        $totalOk     = !empty($results) ? count(array_filter($results, fn($r) => $r['ok'])) : 0;
        $totalErr    = !empty($results) ? count($results) - $totalOk : 0;

        $data = [
            'judul'       => 'Setup Telegram Bot',
            'bots'        => $this->bots,
            'kelasData'   => $kelasData,
            'results'     => $results,
            'totalSet'    => $totalSet,
            'totalSetPkl' => $totalSetPkl,
            'totalOk'     => $totalOk,
            'totalErr'    => $totalErr,
        ];

        $this->view('templates/header', $data);
        $this->view('admin/telegram_setup', $data);
        $this->view('templates/footer');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────
    private function getKelasData(): array {
        $kelasData = [];
        foreach ($this->bots as $bot) {
            $this->db->query("SELECT id, nama_kelas, telegram_chat_id, telegram_chat_id_pkl, status FROM kelas WHERE nama_kelas LIKE :nama");
            $this->db->bind(':nama', '%' . trim($bot['nama_kelas']) . '%');
            $found     = $this->db->single();
            $chatId    = '';
            $chatIdPkl = '';
            if ($found) {
                if (!empty($found->telegram_chat_id) && strpos($found->telegram_chat_id, ':') === false) {
                    $chatId = $found->telegram_chat_id;
                }
                if (!empty($found->telegram_chat_id_pkl) && strpos($found->telegram_chat_id_pkl, ':') === false) {
                    $chatIdPkl = $found->telegram_chat_id_pkl;
                }
            }
            $kelasData[$bot['nama_kelas']] = [
                'db_id'       => $found->id ?? null,
                'chat_id'     => $chatId,
                'chat_id_pkl' => $chatIdPkl,
                'status'      => $found->status ?? 'aktif',
            ];
        }
        return $kelasData;
    }

    private function sendTelegram(string $token, string $chat_id, string $text): array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => "https://api.telegram.org/bot{$token}/sendMessage",
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => http_build_query(['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML']),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return json_decode($resp, true) ?: ['ok' => false, 'description' => 'Tidak ada respon'];
    }
}