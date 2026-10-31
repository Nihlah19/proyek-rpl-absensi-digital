<?php
class JadwalController extends Controller {
    public function __construct() {
        // Hanya Admin yang bisa kelola jadwal
        AuthHelper::checkRole(['admin']);
    }

    public function index() {
        $data['judul'] = 'Kelola Jadwal Absensi';
        $data['jadwal'] = $this->model('Jadwal')->getAll();

        $this->view('templates/header', $data);
        $this->view('jadwal/index', $data);
        $this->view('templates/footer');
    }

    public function edit($id) {
        $data['judul'] = 'Edit Jadwal';
        $data['jadwal'] = $this->model('Jadwal')->getById($id);

        if (!$data['jadwal']) {
            Flasher::setFlash('gagal', 'Jadwal tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/jadwal');
            exit;
        }

        $this->view('templates/header', $data);
        $this->view('jadwal/edit', $data);
        $this->view('templates/footer');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!CsrfHelper::check($_POST['csrf_token'])) {
                Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/jadwal');
                exit;
            }

            if ($this->model('Jadwal')->update($_POST) > 0) {
                Flasher::setFlash('berhasil', 'diubah.', 'success');
            } else {
                Flasher::setFlash('tidak ada perubahan', 'disimpan.', 'info');
            }
            
            header('Location: ' . BASEURL . '/jadwal');
            exit;
        }
    }

    // --- Jadwal Guru ---

    public function guru() {
        $data['judul'] = 'Pilih Guru - Jadwal';
        $data['guru'] = $this->model('JadwalGuru')->getSemuaGuru();

        $this->view('templates/header', $data);
        $this->view('jadwal/guru_list', $data);
        $this->view('templates/footer');
    }

    public function detail_guru($guru_id) {
        $data['judul'] = 'Kelola Jadwal Guru';
        $data['jadwal'] = $this->model('JadwalGuru')->getJadwalByGuru($guru_id);
        $data['guru_id'] = $guru_id;
        $data['guru'] = $this->model('JadwalGuru')->getGuruById($guru_id);

        $this->view('templates/header', $data);
        $this->view('jadwal/guru_detail', $data);
        $this->view('templates/footer');
    }

    public function edit_guru($id) {
        $data['judul'] = 'Edit Jadwal Guru';
        $data['jadwal'] = $this->model('JadwalGuru')->getJadwalById($id);

        if (!$data['jadwal']) {
            Flasher::setFlash('gagal', 'Jadwal guru tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/jadwal/guru');
            exit;
        }

        $data['guru'] = $this->model('JadwalGuru')->getGuruById($data['jadwal']->guru_id);

        $this->view('templates/header', $data);
        $this->view('jadwal/guru_edit', $data);
        $this->view('templates/footer');
    }

    public function update_guru() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!CsrfHelper::check($_POST['csrf_token'])) {
                Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/jadwal/guru');
                exit;
            }

            $guru_id = (int)($_POST['guru_id'] ?? 0);
            if ($guru_id <= 0) {
                Flasher::setFlash('gagal', 'Data guru tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/jadwal/guru');
                exit;
            }

            if ($this->model('JadwalGuru')->update($_POST) > 0) {
                Flasher::setFlash('berhasil', 'Jadwal guru berhasil diubah.', 'success');
            } else {
                Flasher::setFlash('gagal', 'Gagal mengubah jadwal.', 'danger');
            }

            header('Location: ' . BASEURL . '/jadwal/detail_guru/' . $guru_id);
            exit;
        }
    }
}
