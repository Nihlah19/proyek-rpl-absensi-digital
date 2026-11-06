<?php
/**
 * UsersController.php
 * Kelola pengguna (guru & siswa) oleh admin.
 * Mendukung penugasan Wali Kelas & Guru Pembimbing PKL beserta kelas bimbingannya.
 */
class UsersController extends Controller {

    private $model;

    public function __construct() {
        AuthHelper::checkRole(['admin']);
        $this->model = $this->model('UserManagement');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX – Daftar semua guru & siswa
    // ─────────────────────────────────────────────────────────────────────────

    public function index() {
        $data = [
            'judul'       => 'Kelola Pengguna',
            'guru'        => $this->model->getAllGuru(),
            'siswa'       => $this->model->getAllSiswa(),
            'kelas'       => $this->model->getAllKelas(),
            'allKelasPkl' => $this->model->getAllKelasPkl(),
        ];
        $this->view('templates/header', $data);
        $this->view('users/index', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GURU CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function tambahGuru() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        $name       = ValidationHelper::sanitize($_POST['name'] ?? '');
        $username   = ValidationHelper::sanitize($_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';
        $nip        = ValidationHelper::sanitize($_POST['nip'] ?? '');
        $barcode_id = ValidationHelper::sanitize($_POST['barcode_id'] ?? '');
        $subject    = ValidationHelper::sanitize($_POST['subject'] ?? '');
        $is_pembimbing_pkl    = isset($_POST['is_pembimbing_pkl']) ? 1 : 0;
        $pembimbing_kelas_ids = isset($_POST['pembimbing_kelas_ids']) && is_array($_POST['pembimbing_kelas_ids']) ? $_POST['pembimbing_kelas_ids'] : [];

        if (!ValidationHelper::required([$name, $username, $password, $nip])) {
            Flasher::setFlash('gagal', 'Semua field wajib diisi.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if ($this->model->isUsernameExist($username)) {
            Flasher::setFlash('gagal', 'Username sudah digunakan.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if ($this->model->isNipExist($nip)) {
            Flasher::setFlash('gagal', 'NIP sudah terdaftar.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if (!empty($barcode_id) && $this->model->isBarcodeExist($barcode_id)) {
            Flasher::setFlash('gagal', 'Barcode ID sudah digunakan oleh guru lain.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        $this->model->createGuru([
            'name'                 => $name,
            'username'             => $username,
            'password'             => $password,
            'nip'                  => $nip,
            'barcode_id'           => $barcode_id,
            'subject'              => $subject,
            'is_pembimbing_pkl'    => $is_pembimbing_pkl,
            'pembimbing_kelas_ids' => $pembimbing_kelas_ids,
        ]);

        Flasher::setFlash('berhasil', 'Guru berhasil ditambahkan.', 'success');
        header('Location: ' . BASEURL . '/users');
        exit;
    }

    public function editGuru($guru_id = null) {
        $guru_id = (int)$guru_id;
        if (!$guru_id) {
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/users');
                exit;
            }

            $guru = $this->model->getGuruById($guru_id);
            if (!$guru) {
                Flasher::setFlash('gagal', 'Data tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/users');
                exit;
            }

            $name                 = ValidationHelper::sanitize($_POST['name'] ?? '');
            $username             = ValidationHelper::sanitize($_POST['username'] ?? '');
            $password             = $_POST['password'] ?? '';
            $nip                  = ValidationHelper::sanitize($_POST['nip'] ?? '');
            $barcode_id           = ValidationHelper::sanitize($_POST['barcode_id'] ?? '');
            $subject              = ValidationHelper::sanitize($_POST['subject'] ?? '');
            $wali_kelas_id        = ValidationHelper::sanitize($_POST['wali_kelas_id'] ?? '');
            $pembimbing_pkl_option= ValidationHelper::sanitize($_POST['pembimbing_pkl_option'] ?? '');
            $pembimbing_kelas_ids = isset($_POST['pembimbing_kelas_ids']) && is_array($_POST['pembimbing_kelas_ids']) ? $_POST['pembimbing_kelas_ids'] : [];

            // Evaluasi penugasan pembimbing PKL berdasarkan opsi (Bukan, TKJ, TKR, TSM, atau Kustom)
            if (in_array(strtoupper($pembimbing_pkl_option), ['TKJ', 'TKR', 'TSM'])) {
                $is_pembimbing_pkl = 1;
                // Jika checkbox kelas kosong atau tidak dikirim manual, otomatis ambil seluruh kelas jurusan tersebut
                if (empty($pembimbing_kelas_ids)) {
                    $pembimbing_kelas_ids = $this->model->getKelasIdsByJurusan($pembimbing_pkl_option);
                }
            } elseif ($pembimbing_pkl_option === 'CUSTOM') {
                $is_pembimbing_pkl = !empty($pembimbing_kelas_ids) ? 1 : 0;
            } elseif ($pembimbing_pkl_option === '' && isset($_POST['is_pembimbing_pkl']) && (int)$_POST['is_pembimbing_pkl'] === 1) {
                // Fallback toggle checkbox
                $is_pembimbing_pkl = 1;
            } else {
                // -- Bukan Pembimbing PKL --
                $is_pembimbing_pkl = 0;
                $pembimbing_kelas_ids = [];
            }

            if ($this->model->isUsernameExist($username, $guru->id)) {
                Flasher::setFlash('gagal', 'Username sudah digunakan.', 'danger');
                header('Location: ' . BASEURL . '/users');
                exit;
            }

            if ($this->model->isNipExist($nip, $guru_id)) {
                Flasher::setFlash('gagal', 'NIP sudah terdaftar untuk guru lain.', 'danger');
                header('Location: ' . BASEURL . '/users');
                exit;
            }

            if (!empty($barcode_id) && $this->model->isBarcodeExist($barcode_id, $guru_id)) {
                Flasher::setFlash('gagal', 'Barcode ID sudah terdaftar untuk guru lain.', 'danger');
                header('Location: ' . BASEURL . '/users');
                exit;
            }

            $this->model->updateGuru($guru_id, [
                'name'                 => $name,
                'username'             => $username,
                'password'             => $password,
                'nip'                  => $nip,
                'barcode_id'           => $barcode_id,
                'subject'              => $subject,
                'wali_kelas_id'        => $wali_kelas_id,
                'is_pembimbing_pkl'    => $is_pembimbing_pkl,
                'pembimbing_kelas_ids' => $pembimbing_kelas_ids,
            ]);

            Flasher::setFlash('berhasil', 'Data guru dan pengaturan pembimbing PKL berhasil diperbarui.', 'success');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        $guru             = $this->model->getGuruById($guru_id);
        $allKelasPkl      = $this->model->getAllKelasPkl();
        $assignedKelasIds = $this->model->getPembimbingKelasIds($guru_id);

        // Deteksi opsi pembimbing PKL yang aktif (Bukan, TKJ, TKR, TSM, atau CUSTOM)
        $selectedJurusanPkl = '';
        if (!empty($guru->is_pembimbing_pkl) || !empty($assignedKelasIds)) {
            if (!empty($assignedKelasIds)) {
                $jurusans = [];
                foreach ($allKelasPkl as $kp) {
                    if (in_array((int)$kp->id, $assignedKelasIds)) {
                        $jur = strtoupper(trim($kp->jurusan ?? ''));
                        if ($jur) $jurusans[$jur] = true;
                    }
                }
                $jurKeys = array_keys($jurusans);
                if (count($jurKeys) === 1 && in_array($jurKeys[0], ['TKJ', 'TKR', 'TSM'])) {
                    $selectedJurusanPkl = $jurKeys[0];
                } else {
                    $selectedJurusanPkl = 'CUSTOM';
                }
            } else {
                $selectedJurusanPkl = 'CUSTOM';
            }
        }

        $data = [
            'judul'              => 'Edit Guru',
            'guru'               => $guru,
            'kelas'              => $this->model->getAllKelas(),
            'allKelasPkl'        => $allKelasPkl,
            'assignedKelasIds'   => $assignedKelasIds,
            'selectedJurusanPkl' => $selectedJurusanPkl,
        ];
        $this->view('templates/header', $data);
        $this->view('users/edit_guru', $data);
        $this->view('templates/footer');
    }

    public function hapusGuru($guru_id = null) {
        $guru_id = (int)$guru_id;
        if (!$guru_id) {
            header('Location: ' . BASEURL . '/users'); exit;
        }
        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/users'); exit;
        }
        $this->model->deleteGuru($guru_id);
        Flasher::setFlash('berhasil', 'Guru berhasil dihapus.', 'success');
        header('Location: ' . BASEURL . '/users');
        exit;
    }

    public function barcodeGuru($guru_id = null) {
        $guru_id = (int)$guru_id;
        $guru = $this->model->getGuruById($guru_id);
        if (!$guru) { header('Location: ' . BASEURL . '/users'); exit; }
        $data = ['judul' => 'Barcode Guru', 'guru' => $guru];
        $this->view('templates/header', $data);
        $this->view('users/barcode_guru', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA CRUD
    // ─────────────────────────────────────────────────────────────────────────

    public function tambahSiswa() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        $name     = ValidationHelper::sanitize($_POST['name'] ?? '');
        $username = ValidationHelper::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $nis      = ValidationHelper::sanitize($_POST['nis'] ?? '');
        $kelas_id = (int)($_POST['kelas_id'] ?? 0);
        $rfid_uid   = ValidationHelper::sanitize($_POST['rfid_uid'] ?? '');
        $no_hp_ortu = ValidationHelper::sanitize($_POST['no_hp_ortu'] ?? '');

        if (!ValidationHelper::required([$name, $username, $password, $nis])) {
            Flasher::setFlash('gagal', 'Semua field wajib diisi.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if ($this->model->isUsernameExist($username)) {
            Flasher::setFlash('gagal', 'Username sudah digunakan.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if ($this->model->isNisExist($nis)) {
            Flasher::setFlash('gagal', 'NIS sudah terdaftar.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        if (!empty($rfid_uid) && $this->model->isRfidExist($rfid_uid)) {
            Flasher::setFlash('gagal', 'Kartu RFID UID sudah terdaftar untuk siswa lain.', 'danger');
            header('Location: ' . BASEURL . '/users');
            exit;
        }

        $this->model->createSiswa([
            'name'       => $name,
            'username'   => $username,
            'password'   => $password,
            'nis'        => $nis,
            'kelas_id'   => $kelas_id ?: null,
            'rfid_uid'   => $rfid_uid ?: null,
            'no_hp_ortu' => $no_hp_ortu ?: null,
        ]);

        Flasher::setFlash('berhasil', 'Siswa berhasil ditambahkan.', 'success');
        header('Location: ' . BASEURL . '/users');
        exit;
    }

    public function editSiswa($siswa_id = null) {
        $siswa_id = (int)$siswa_id;
        if (!$siswa_id) {
            header('Location: ' . BASEURL . '/users'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/users'); exit;
            }

            $siswa = $this->model->getSiswaById($siswa_id);
            if (!$siswa) {
                Flasher::setFlash('gagal', 'Data tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/users'); exit;
            }

            $name     = ValidationHelper::sanitize($_POST['name'] ?? '');
            $username = ValidationHelper::sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $nis      = ValidationHelper::sanitize($_POST['nis'] ?? '');
            $kelas_id = (int)($_POST['kelas_id'] ?? 0);
            $rfid_uid   = ValidationHelper::sanitize($_POST['rfid_uid'] ?? '');
            $no_hp_ortu = ValidationHelper::sanitize($_POST['no_hp_ortu'] ?? '');

            if ($this->model->isUsernameExist($username, $siswa->id)) {
                Flasher::setFlash('gagal', 'Username sudah digunakan.', 'danger');
                header('Location: ' . BASEURL . '/users'); exit;
            }

            if ($this->model->isNisExist($nis, $siswa_id)) {
                Flasher::setFlash('gagal', 'NIS sudah terdaftar untuk siswa lain.', 'danger');
                header('Location: ' . BASEURL . '/users'); exit;
            }

            if (!empty($rfid_uid) && $this->model->isRfidExist($rfid_uid, $siswa_id)) {
                Flasher::setFlash('gagal', 'Kartu RFID UID sudah terdaftar untuk siswa lain.', 'danger');
                header('Location: ' . BASEURL . '/users'); exit;
            }

            $this->model->updateSiswa($siswa_id, [
                'name'       => $name,
                'username'   => $username,
                'password'   => $password,
                'nis'        => $nis,
                'kelas_id'   => $kelas_id ?: null,
                'rfid_uid'   => $rfid_uid ?: null,
                'no_hp_ortu' => $no_hp_ortu ?: null,
            ]);

            Flasher::setFlash('berhasil', 'Data siswa berhasil diperbarui.', 'success');
            header('Location: ' . BASEURL . '/users'); exit;
        }

        $data = [
            'judul'  => 'Edit Siswa',
            'siswa'  => $this->model->getSiswaById($siswa_id),
            'kelas'  => $this->model->getAllKelas(),
        ];
        $this->view('templates/header', $data);
        $this->view('users/edit_siswa', $data);
        $this->view('templates/footer');
    }

    public function hapusSiswa($siswa_id = null) {
        $siswa_id = (int)$siswa_id;
        if (!$siswa_id) {
            header('Location: ' . BASEURL . '/users'); exit;
        }
        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/users'); exit;
        }
        $this->model->deleteSiswa($siswa_id);
        Flasher::setFlash('berhasil', 'Siswa berhasil dihapus.', 'success');
        header('Location: ' . BASEURL . '/users');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BULK ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    public function pindahKelasMasal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/users'); exit;
        }

        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/users'); exit;
        }

        $kelas_asal_id = (int)($_POST['kelas_asal_id'] ?? 0);
        $kelas_tujuan_id = (int)($_POST['kelas_tujuan_id'] ?? 0);

        if (!$kelas_asal_id || !$kelas_tujuan_id) {
            Flasher::setFlash('gagal', 'Kelas asal dan tujuan harus dipilih.', 'danger');
            header('Location: ' . BASEURL . '/users'); exit;
        }

        if ($kelas_asal_id === $kelas_tujuan_id) {
            Flasher::setFlash('gagal', 'Kelas asal dan tujuan tidak boleh sama.', 'warning');
            header('Location: ' . BASEURL . '/users'); exit;
        }

        $affected = $this->model->moveSiswaByKelas($kelas_asal_id, $kelas_tujuan_id);
        
        if ($affected > 0) {
            Flasher::setFlash('berhasil', "$affected siswa berhasil dipindahkan ke kelas baru.", 'success');
        } else {
            Flasher::setFlash('info', 'Tidak ada siswa di kelas asal.', 'info');
        }

        header('Location: ' . BASEURL . '/users');
        exit;
    }

    public function hapusSiswaMasal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/users'); exit;
        }

        if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
            Flasher::setFlash('gagal', 'Token tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/users'); exit;
        }

        $kelas_id = (int)($_POST['kelas_id'] ?? 0);

        if (!$kelas_id) {
            Flasher::setFlash('gagal', 'Kelas harus dipilih.', 'danger');
            header('Location: ' . BASEURL . '/users'); exit;
        }

        $affected = $this->model->deleteSiswaByKelas($kelas_id);
        
        if ($affected > 0) {
            Flasher::setFlash('berhasil', "Seluruh siswa ($affected orang) di kelas tersebut berhasil dihapus.", 'success');
        } else {
            Flasher::setFlash('info', 'Tidak ada siswa di kelas tersebut.', 'info');
        }

        header('Location: ' . BASEURL . '/users');
        exit;
    }
}
