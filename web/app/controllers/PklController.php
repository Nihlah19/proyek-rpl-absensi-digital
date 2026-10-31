<?php
/**
 * PklController.php
 * Controller untuk Manajemen Kelompok & Lokasi PKL (Wali Kelas & Admin)
 * dan Monitoring Presensi Mandiri PKL Siswa.
 */
class PklController extends Controller {

    private Pkl $pklModel;

    public function __construct() {
        $this->pklModel = $this->model('Pkl');
    }

    /**
     * Index route: arahkan sesuai role pengguna
     */
    public function index() {
        $role = SessionHelper::get('role');
        if ($role === 'siswa') {
            header('Location: ' . BASEURL . '/absensi/pkl');
            exit;
        }

        if (in_array($role, ['wali_kelas', 'walikelas', 'admin'])) {
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

        header('Location: ' . BASEURL . '/dashboard');
        exit;
    }

    /**
     * Halaman Manajemen Kelompok & Lokasi PKL
     */
    public function kelompok() {
        AuthHelper::checkRole(['wali_kelas', 'walikelas', 'admin']);
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $selectedKelasId = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
        $allKelasPkl     = $this->pklModel->getAllKelasPkl();
        $activeKelas     = null;

        if ($userRole === 'admin') {
            if ($selectedKelasId > 0) {
                $activeKelas = $this->pklModel->getKelasById($selectedKelasId);
            } elseif (!empty($allKelasPkl)) {
                $activeKelas = $allKelasPkl[0];
            }
        } else {
            // Wali Kelas: Ambil kelas yang diampu oleh wali kelas ini
            $activeKelas = $this->pklModel->getKelasByWaliUserId($userId);
        }

        $kelompokList = [];
        $siswaList    = [];
        $stats = [
            'total_siswa'       => 0,
            'sudah_kelompok'    => 0,
            'belum_kelompok'    => 0,
            'total_kelompok'    => 0
        ];

        if ($activeKelas) {
            $kelompokList = $this->pklModel->getKelompokByKelasId((int)$activeKelas->id);
            $siswaList    = $this->pklModel->getSiswaByKelasId((int)$activeKelas->id);

            $stats['total_siswa']    = count($siswaList);
            $stats['total_kelompok'] = count($kelompokList);

            foreach ($siswaList as $s) {
                if (!empty($s->kelompok_id)) {
                    $stats['sudah_kelompok']++;
                } else {
                    $stats['belum_kelompok']++;
                }
            }
        }

        $data = [
            'judul'         => 'Kelompok & Lokasi PKL',
            'userRole'      => $userRole,
            'activeKelas'   => $activeKelas,
            'allKelasPkl'   => $allKelasPkl,
            'kelompokList'  => $kelompokList,
            'siswaList'     => $siswaList,
            'stats'         => $stats,
        ];

        $this->view('templates/header', $data);
        $this->view('pkl/kelompok', $data);
        $this->view('templates/footer');
    }

    /**
     * Ambil data JSON satu kelompok untuk form modal edit
     */
    public function get_detail_json() {
        AuthHelper::checkRole(['wali_kelas', 'walikelas', 'admin']);
        header('Content-Type: application/json; charset=UTF-8');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID kelompok tidak valid']);
            exit;
        }

        $kel = $this->pklModel->getKelompokById($id);
        if (!$kel) {
            echo json_encode(['success' => false, 'message' => 'Kelompok PKL tidak ditemukan']);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $kel]);
        exit;
    }

    /**
     * Simpan / Perbarui Kelompok PKL beserta anggota siswanya (Group Assignment)
     */
    public function save_kelompok() {
        AuthHelper::checkRole(['wali_kelas', 'walikelas', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $id           = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $kelasId      = (int)($_POST['kelas_id'] ?? 0);
        $namaKelompok = trim($_POST['nama_kelompok'] ?? '');
        $namaTempat   = trim($_POST['nama_tempat'] ?? '');
        $alamat       = trim($_POST['alamat'] ?? '');
        $linkLokasi   = trim($_POST['link_lokasi'] ?? '');
        $latInput     = trim($_POST['latitude'] ?? '');
        $lngInput     = trim($_POST['longitude'] ?? '');
        $radius       = !empty($_POST['radius_meter']) ? (int)$_POST['radius_meter'] : 50;
        $pembimbing   = trim($_POST['pembimbing_nama'] ?? '');
        $kontak       = trim($_POST['pembimbing_kontak'] ?? '');
        $siswaIds     = isset($_POST['siswa_ids']) && is_array($_POST['siswa_ids']) ? $_POST['siswa_ids'] : [];

        // Validasi akses jika wali_kelas: pastikan kelas miliknya
        if ($userRole !== 'admin') {
            $waliKelas = $this->pklModel->getKelasByWaliUserId($userId);
            if (!$waliKelas || (int)$waliKelas->id !== $kelasId) {
                Flasher::setFlash('Akses Ditolak', 'Anda tidak memiliki hak akses untuk mengelola kelas ini.', 'danger');
                header('Location: ' . BASEURL . '/pkl/kelompok');
                exit;
            }
        }

        // Coba auto-parse koordinat dari Google Maps Link jika latitude/longitude kosong
        if ((empty($latInput) || empty($lngInput)) && !empty($linkLokasi)) {
            $parsed = Pkl::parseCoordinates($linkLokasi);
            if ($parsed) {
                $latInput = $parsed['lat'];
                $lngInput = $parsed['lng'];
            }
        }

        if (empty($namaKelompok) || empty($namaTempat)) {
            Flasher::setFlash('Gagal', 'Nama kelompok dan nama tempat / instansi PKL wajib diisi.', 'danger');
            header('Location: ' . BASEURL . '/pkl/kelompok?kelas_id=' . $kelasId);
            exit;
        }

        if (!is_numeric($latInput) || !is_numeric($lngInput)) {
            Flasher::setFlash('Gagal', 'Koordinat Latitude dan Longitude tidak valid. Masukkan angka desimal atau gunakan link Google Maps yang tepat.', 'danger');
            header('Location: ' . BASEURL . '/pkl/kelompok?kelas_id=' . $kelasId);
            exit;
        }

        $data = [
            'id'                => $id,
            'kelas_id'          => $kelasId,
            'nama_kelompok'     => $namaKelompok,
            'nama_tempat'       => $namaTempat,
            'alamat'            => $alamat,
            'link_lokasi'       => $linkLokasi,
            'latitude'          => (float)$latInput,
            'longitude'         => (float)$lngInput,
            'radius_meter'      => $radius,
            'pembimbing_nama'   => $pembimbing,
            'pembimbing_kontak' => $kontak,
            'created_by'        => $userId
        ];

        $savedId = $this->pklModel->saveKelompok($data, $siswaIds);

        if ($savedId) {
            $cntAnggota = count($siswaIds);
            Flasher::setFlash('Berhasil', "Kelompok PKL <strong>" . htmlspecialchars($namaKelompok) . "</strong> berhasil disimpan dengan {$cntAnggota} siswa anggota.", 'success');
        } else {
            Flasher::setFlash('Gagal', 'Terjadi kesalahan sistem saat menyimpan data kelompok PKL.', 'danger');
        }

        header('Location: ' . BASEURL . '/pkl/kelompok?kelas_id=' . $kelasId);
        exit;
    }

    /**
     * Hapus Kelompok PKL
     */
    public function delete_kelompok() {
        AuthHelper::checkRole(['wali_kelas', 'walikelas', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $kelasId  = (int)($_POST['kelas_id'] ?? 0);
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $kel = $this->pklModel->getKelompokById($id);
        if (!$kel) {
            Flasher::setFlash('Gagal', 'Kelompok PKL tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

        if ($userRole !== 'admin') {
            $waliKelas = $this->pklModel->getKelasByWaliUserId($userId);
            if (!$waliKelas || (int)$waliKelas->id !== (int)$kel->kelas_id) {
                Flasher::setFlash('Akses Ditolak', 'Anda tidak berwenang menghapus kelompok dari kelas ini.', 'danger');
                header('Location: ' . BASEURL . '/pkl/kelompok');
                exit;
            }
        }

        $this->pklModel->deleteKelompok($id);
        Flasher::setFlash('Berhasil', 'Kelompok PKL dan penugasan anggotanya berhasil dihapus.', 'success');
        header('Location: ' . BASEURL . '/pkl/kelompok?kelas_id=' . $kelasId);
        exit;
    }

    /**
     * Halaman Monitoring Presensi PKL Siswa Hari Ini (Selfie & Jarak GPS)
     */
    public function monitoring() {
        AuthHelper::checkRole(['wali_kelas', 'walikelas', 'admin']);
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $selectedKelasId = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
        $tanggal         = !empty($_GET['tanggal']) ? trim($_GET['tanggal']) : date('Y-m-d');
        $allKelasPkl     = $this->pklModel->getAllKelasPkl();
        $activeKelas     = null;

        if ($userRole === 'admin') {
            if ($selectedKelasId > 0) {
                $activeKelas = $this->pklModel->getKelasById($selectedKelasId);
            } elseif (!empty($allKelasPkl)) {
                $activeKelas = $allKelasPkl[0];
            }
        } else {
            $activeKelas = $this->pklModel->getKelasByWaliUserId($userId);
        }

        $rows = [];
        $stats = ['total' => 0, 'masuk' => 0, 'pulang' => 0, 'belum' => 0];

        if ($activeKelas) {
            $rows = $this->pklModel->getMonitoringPresensi((int)$activeKelas->id, $tanggal);
            $stats['total'] = count($rows);
            foreach ($rows as $r) {
                if (!empty($r->jam_pulang)) {
                    $stats['pulang']++;
                } elseif (!empty($r->jam_datang)) {
                    $stats['masuk']++;
                } else {
                    $stats['belum']++;
                }
            }
        }

        $data = [
            'judul'       => 'Monitoring Presensi PKL',
            'userRole'    => $userRole,
            'activeKelas' => $activeKelas,
            'allKelasPkl' => $allKelasPkl,
            'tanggal'     => $tanggal,
            'rows'        => $rows,
            'stats'       => $stats,
        ];

        $this->view('templates/header', $data);
        $this->view('pkl/monitoring', $data);
        $this->view('templates/footer');
    }
}
