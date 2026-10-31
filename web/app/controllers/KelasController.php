<?php
class KelasController extends Controller {
    private $db;

    public function __construct() {
        AuthHelper::checkRole(['admin']);
        $this->db = new Database();
    }

    public function index() {
        $this->db->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
        $kelasList = $this->db->resultSet();

        $data = [
            'judul' => 'Konfigurasi Telegram',
            'kelas' => $kelasList
        ];

        $this->view('templates/header', $data);
        $this->view('kelas/index', $data);
        $this->view('templates/footer');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $telegram_chat_id  = !empty($_POST['telegram_chat_id'])  ? trim($_POST['telegram_chat_id'])  : null;
            $telegram_bot_token = !empty($_POST['telegram_bot_token']) ? trim($_POST['telegram_bot_token']) : null;
            $status = isset($_POST['status']) && $_POST['status'] === 'pkl' ? 'pkl' : 'aktif';

            $this->db->query("UPDATE kelas SET telegram_chat_id = :telegram_chat_id, telegram_bot_token = :telegram_bot_token, status = :status WHERE id = :id");
            $this->db->bind(':telegram_chat_id', $telegram_chat_id);
            $this->db->bind(':telegram_bot_token', $telegram_bot_token);
            $this->db->bind(':status', $status);
            $this->db->bind(':id', $id);

            if ($this->db->execute()) {
                Flasher::setFlash('Berhasil', 'Pengaturan Telegram kelas berhasil diupdate.', 'success');
            } else {
                Flasher::setFlash('Gagal', 'Terjadi kesalahan saat mengupdate pengaturan Telegram.', 'error');
            }
            
            header('Location: ' . BASEURL . '/kelas');
            exit;
        }
    }
}
