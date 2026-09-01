<?php
/**
 * CatatanController.php
 * Fitur catatan:
 *   - Wali Kelas → Siswa (kelas wali)
 *   - Kepala Sekolah → Guru
 *   Dibuat berdasarkan Catatan.php model + sidebar yang sudah ada.
 */
class CatatanController extends Controller
{
    /**
     * List catatan untuk user yang sedang login.
     *   - Siswa: lihat catatan miliknya (dari wali kelas)
     *   - Guru: lihat catatan miliknya (dari kepsek)
     *   - Wali Kelas / Kepsek: bisa tulis catatan baru (form)
     */
    public function index()
    {
        AuthHelper::check();
        $userId = (int)SessionHelper::get('user_id');
        $role   = SessionHelper::get('role');

        $data['judul'] = 'Catatan';

        // ── Ambil catatan yang relevan ──
        if (in_array($role, ['siswa', 'guru'])) {
            $targetId = $this->getTargetId($role, $userId);
            $data['catatan'] = $targetId
                ? $this->model('Catatan')->getByUser($targetId, $role)
                : [];
            $data['targetId'] = $targetId;
            $data['targetRole'] = $role;
        }

        // ── Tampilkan form tulis catatan untuk wali kelas / kepsek ──
        // (Hanya muncul jika user punya hak untuk menulis)
        if (in_array($role, ['walikelas', 'kepsek'])) {
            $data['canWrite'] = true;
            $data['writableRoles'] = ($role === 'walikelas') ? ['siswa'] : ['guru'];

            if ($role === 'walikelas') {
                // Wali kelas: lihat daftar siswa di kelasnya
                $kelas = $this->model('AbsensiMonitor')->getKelasWali($userId);
                $data['kelas'] = $kelas;
                $data['siswas'] = $kelas
                    ? $this->getSiswaByKelas((int)$kelas->id)
                    : [];
            } else {
                // Kepsek: lihat daftar guru
                $data['gurus'] = $this->getSemuaGuru();
            }
        }

        $this->view('templates/header', $data);
        $this->view('catatan/index', $data);
        $this->view('templates/footer');
    }

    /**
     * Proses tulis catatan (POST).
     * Role yang boleh tulis: walikelas (ke siswa), kepsek (ke guru).
     */
    public function store()
    {
        AuthHelper::checkRole(['walikelas', 'kepsek']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        // CSRF
        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        $role = SessionHelper::get('role');
        $authorId = (int)SessionHelper::get('user_id');

        // Validasi
        if (!ValidationHelper::required(['target_role', 'target_id', 'title', 'content'], $_POST)) {
            Flasher::setFlash('gagal', 'Harap isi semua field.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        // Cek otorisasi: walikelas hanya boleh ke siswa, kepsek hanya boleh ke guru
        if ($role === 'walikelas' && $_POST['target_role'] !== 'siswa') {
            Flasher::setFlash('gagal', 'Wali kelas hanya bisa menulis catatan untuk siswa.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }
        if ($role === 'kepsek' && $_POST['target_role'] !== 'guru') {
            Flasher::setFlash('gagal', 'Kepala sekolah hanya bisa menulis catatan untuk guru.', 'danger');
            header('Location: ' . BASEURL . '/catatan');
            exit;
        }

        $data = [
            'author_id'   => $authorId,
            'target_role' => $_POST['target_role'],
            'target_id'   => (int)$_POST['target_id'],
            'title'       => ValidationHelper::sanitize($_POST['title']),
            'content'     => ValidationHelper::sanitize($_POST['content']),
            'tanggal'     => $_POST['tanggal'] ?? date('Y-m-d'),
            'type'        => 'catatan',
        ];

        if ($this->model('Catatan')->create($data) > 0) {
            Flasher::setFlash('berhasil', 'Catatan berhasil ditambahkan.', 'success');
        } else {
            Flasher::setFlash('gagal', 'Gagal menambahkan catatan.', 'danger');
        }

        header('Location: ' . BASEURL . '/catatan');
        exit;
    }

    // ── Helper private ────────────────────────────────────────────────────

    private function getTargetId($role, $userId)
    {
        if ($role === 'siswa') {
            $m = $this->model('AbsensiMonitor');
            return $m->getSiswaIdByUserId($userId);
        }
        if ($role === 'guru') {
            $m = $this->model('AbsensiMonitor');
            return $m->getGuruIdByUserId($userId);
        }
        return null;
    }

    private function getSiswaByKelas($kelasId)
    {
        $this->db->query(
            "SELECT u.id, u.name, u.username
             FROM users u
             JOIN siswa s ON s.user_id = u.id
             WHERE s.kelas_id = :kelas_id
             ORDER BY u.name ASC"
        );
        $this->db->bind(':kelas_id', $kelasId);
        return $this->db->resultSet();
    }

    private function getSemuaGuru()
    {
        $this->db->query(
            "SELECT g.id, u.name, u.username, g.nip
             FROM guru g
             JOIN users u ON u.id = g.user_id
             ORDER BY u.name ASC"
        );
        return $this->db->resultSet();
    }
}
