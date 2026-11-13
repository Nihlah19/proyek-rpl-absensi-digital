<?php
class CatatanController extends Controller
{
    public function index()
    {
        AuthHelper::check();
        $userId  = (int)SessionHelper::get('user_id');
        $role    = SessionHelper::get('role');

        $catatanModel      = $this->model('Catatan');
        $catatanList       = [];
        $catatanTerkirim   = [];
        $catatanSiswaAdmin = [];
        $catatanGuruAdmin  = [];

        // Siapkan data untuk form (hanya wali_kelas/kepsek yang bisa menulis)
        $siswas = [];
        $gurus  = [];

        if ($role === 'admin') {
            // Administrator mengelola seluruh catatan terkirim
            $catatanSiswaAdmin = $catatanModel->getAllCatatanSiswa();
            $catatanGuruAdmin  = $catatanModel->getAllCatatanGuru();
        } elseif (in_array($role, ['wali_kelas', 'walikelas', 'kepsek'])) {
            $catatanList = $catatanModel->getByUser($userId, $role);
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
                $catatanTerkirim = $catatanModel->getSentByKepsek($userId);
                $db->query("SELECT g.id, u.name, u.username, g.nip FROM users u
                            JOIN guru g ON g.user_id = u.id
                            ORDER BY u.name");
                $gurus  = $db->resultSet();
            }
        } else {
            // Siswa atau Guru (Pembaca catatan)
            $catatanList = $catatanModel->getByUser($userId, $role);
        }

        $data = [
            'judul'             => 'Catatan',
            'catatanList'       => $catatanList,
            'catatanTerkirim'   => $catatanTerkirim,
            'catatanSiswaAdmin' => $catatanSiswaAdmin,
            'catatanGuruAdmin'  => $catatanGuruAdmin,
            'siswas'            => $siswas,
            'gurus'             => $gurus,
            'role'              => $role,
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

    /**
     * Redirect route /catatan/daftar ke /catatan utama
     */
    public function daftar()
    {
        header('Location: ' . BASEURL . '/catatan');
        exit;
    }

    public function list()
    {
        header('Location: ' . BASEURL . '/catatan');
        exit;
    }

    /**
     * Edit / perbarui isi dan judul catatan yang telah dibuat
     * Diizinkan untuk Admin (semua catatan) serta Penulis (Wali Kelas & Kepsek)
     */
    public function update()
    {
        AuthHelper::checkRole(['admin', 'wali_kelas', 'walikelas', 'kepsek']);
        $redirect = '/catatan';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . $redirect);
            exit;
        }

        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . $redirect);
            exit;
        }

        $id         = (int)($_POST['id'] ?? 0);
        $targetRole = trim($_POST['target_role'] ?? '');
        $title      = trim($_POST['title'] ?? '');
        $content    = trim($_POST['content'] ?? '');
        $authorId   = (int)SessionHelper::get('user_id');
        $role       = SessionHelper::get('role');

        if ($id <= 0 || !in_array($targetRole, ['siswa', 'guru']) || $content === '') {
            Flasher::setFlash('gagal', 'Data pembaruan catatan tidak valid atau isi kosong.', 'danger');
            header('Location: ' . BASEURL . $redirect);
            exit;
        }

        $catatanModel = $this->model('Catatan');

        if ($role === 'admin') {
            $updated = $catatanModel->updateByAdmin($id, $targetRole, $title, $content);
        } else {
            $allowedTarget = ($role === 'kepsek') ? 'guru' : 'siswa';
            if ($targetRole !== $allowedTarget) {
                Flasher::setFlash('gagal', 'Akses target catatan tidak diizinkan.', 'danger');
                header('Location: ' . BASEURL . $redirect);
                exit;
            }
            $updated = $catatanModel->updateByAuthor($id, $targetRole, $authorId, $title, $content);
        }

        if ($updated) {
            Flasher::setFlash('berhasil', 'Catatan berhasil diperbarui.', 'success');
        } else {
            Flasher::setFlash('gagal', 'Gagal memperbarui catatan atau catatan tidak ditemukan.', 'danger');
        }

        header('Location: ' . BASEURL . $redirect);
        exit;
    }

    /**
     * Hapus catatan secara permanen
     * Diizinkan untuk Admin (semua catatan) serta Penulis (Wali Kelas & Kepsek)
     */
    public function delete()
    {
        AuthHelper::checkRole(['admin', 'wali_kelas', 'walikelas', 'kepsek']);
        $redirect = '/catatan';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . $redirect);
            exit;
        }

        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . $redirect);
            exit;
        }

        $id         = (int)($_POST['id'] ?? 0);
        $targetRole = trim($_POST['target_role'] ?? '');
        $authorId   = (int)SessionHelper::get('user_id');
        $role       = SessionHelper::get('role');

        if ($id <= 0 || !in_array($targetRole, ['siswa', 'guru'])) {
            Flasher::setFlash('gagal', 'Catatan tidak valid untuk dihapus.', 'danger');
            header('Location: ' . BASEURL . $redirect);
            exit;
        }

        $catatanModel = $this->model('Catatan');

        if ($role === 'admin') {
            $deleted = $catatanModel->deleteByAdmin($id, $targetRole);
        } else {
            $allowedTarget = ($role === 'kepsek') ? 'guru' : 'siswa';
            if ($targetRole !== $allowedTarget) {
                Flasher::setFlash('gagal', 'Akses hapus catatan tidak diizinkan.', 'danger');
                header('Location: ' . BASEURL . $redirect);
                exit;
            }
            $deleted = $catatanModel->deleteByAuthor($id, $targetRole, $authorId);
        }

        if ($deleted) {
            Flasher::setFlash('berhasil', 'Catatan berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('gagal', 'Gagal menghapus catatan atau Anda tidak memiliki akses.', 'danger');
        }

        header('Location: ' . BASEURL . $redirect);
        exit;
    }
}
