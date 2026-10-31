<?php
class CatatanController extends Controller
{
    public function index()
    {
        AuthHelper::check();
        $userId  = (int)SessionHelper::get('user_id');
        $role    = SessionHelper::get('role');

        $catatanModel = $this->model('Catatan');
        $catatanList  = $catatanModel->getByUser($userId, $role);
        $catatanTerkirim = [];

        // Siapkan data untuk form (hanya wali_kelas/kepsek yang bisa menulis)
        $siswas = [];
        $gurus  = [];
        if (in_array($role, ['wali_kelas', 'walikelas', 'kepsek'])) {
            $db = new Database();
            if (in_array($role, ['wali_kelas', 'walikelas'])) {
                $catatanTerkirim = $catatanModel->getSentByWaliKelas($userId);
                $db->query("SELECT s.id, u.name, u.username, s.nis FROM users u
                            JOIN siswa s ON s.user_id = u.id
                            WHERE s.kelas_id = (SELECT id FROM kelas WHERE wali_kelas_id = :uid)
                            ORDER BY u.name");
                $db->bind(':uid', $userId);
                $siswas = $db->resultSet();
            } else {
                $db->query("SELECT g.id, u.name, u.username, g.nip FROM users u
                            JOIN guru g ON g.user_id = u.id
                            ORDER BY u.name");
                $gurus  = $db->resultSet();
            }
        }

        $data = [
            'judul'           => 'Catatan',
            'catatanList'     => $catatanList,
            'catatanTerkirim' => $catatanTerkirim,
            'siswas'          => $siswas,
            'gurus'           => $gurus,
        ];

        $this->view('templates/header', $data);
        $this->view('catatan/index', $data);
        $this->view('templates/footer');
    }

    public function store()
    {
        AuthHelper::checkRole(['wali_kelas', 'walikelas', 'kepsek']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        // Cek CSRF
        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        $targetRole = $_POST['target_role'] ?? '';
        $targetId   = (int)($_POST['target_id'] ?? 0);
        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $tanggal    = $_POST['tanggal'] ?? date('Y-m-d');
        $authorId   = (int)SessionHelper::get('user_id');

        // Validasi
        if (empty($targetRole) || $targetId <= 0 || empty($title) || empty($content)) {
            Flasher::setFlash('gagal', 'Harap isi semua field wajib.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        if (!in_array($targetRole, ['siswa', 'guru'])) {
            Flasher::setFlash('gagal', 'Role target tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        // Validasi tanggal
        $tanggalObj = DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$tanggalObj || $tanggalObj->format('Y-m-d') !== $tanggal) {
            Flasher::setFlash('gagal', 'Tanggal tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        $catatanModel = $this->model('Catatan');
        $result = $catatanModel->create([
            'target_role' => $targetRole,
            'target_id'   => $targetId,
            'author_id'   => $authorId,
            'title'       => $title,
            'content'     => $content,
        ]);

        if ($result > 0) {
            // Kirim notifikasi pop-up & PWA ke penerima
            try {
                $dbTarget = new Database();
                if ($targetRole === 'siswa') {
                    $dbTarget->query("SELECT user_id FROM siswa WHERE id = :id");
                    $dbTarget->bind(':id', $targetId);
                    $rowTarget = $dbTarget->single();
                    if ($rowTarget && !empty($rowTarget->user_id)) {
                        NotificationHelper::send(
                            (int)$rowTarget->user_id,
                            "📌 Catatan dari Wali Kelas",
                            "Wali kelas memberikan catatan baru: \"" . $title . "\". Klik untuk membaca.",
                            "catatan_walikelas",
                            "/catatan"
                        );
                    }
                } elseif ($targetRole === 'guru') {
                    $dbTarget->query("SELECT user_id FROM guru WHERE id = :id");
                    $dbTarget->bind(':id', $targetId);
                    $rowTarget = $dbTarget->single();
                    if ($rowTarget && !empty($rowTarget->user_id)) {
                        NotificationHelper::send(
                            (int)$rowTarget->user_id,
                            "📩 Catatan dari Kepala Sekolah",
                            "Kepala Sekolah mengirim catatan baru untuk Anda: \"" . $title . "\". Klik untuk membaca.",
                            "catatan_kepsek",
                            "/catatan"
                        );
                    }
                }
            } catch (Exception $e) {
                error_log("[CatatanController] Error sending notification: " . $e->getMessage());
            }

            Flasher::setFlash('berhasil', 'Catatan berhasil dikirim.', 'success');
        } else {
            Flasher::setFlash('gagal', 'Gagal mengirim catatan. Coba lagi.', 'danger');
        }

        header('Location: ' . BASEURL . '/catatan');
        exit;
    }
}
