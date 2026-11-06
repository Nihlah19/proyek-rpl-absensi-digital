<?php
/**
 * PklController.php
 * Controller untuk Manajemen Kelompok & Lokasi PKL melalui Guru Pembimbing PKL & Admin,
 * serta Monitoring Presensi Siswa PKL (Foto Selfie & Validasi GPS).
 */
class PklController extends Controller {

    private Pkl $pklModel;

    public function __construct() {
        $this->pklModel = $this->model('Pkl');
    }

    /**
     * Index route: arahkan sesuai role & status penugasan pengguna
     */
    public function index() {
        AuthHelper::check();
        $userId = (int)SessionHelper::get('user_id');
        $role   = SessionHelper::get('role');

        if ($role === 'siswa') {
            header('Location: ' . BASEURL . '/absensi/pkl');
            exit;
        }

        if ($role === 'admin' || $this->pklModel->isUserPembimbingPkl($userId)) {
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

        header('Location: ' . BASEURL . '/dashboard');
        exit;
    }

    /**
     * Halaman Manajemen Kelompok & Lokasi PKL (Kelola Guru Pembimbing PKL & Admin)
     */
    public function kelompok() {
        AuthHelper::check();
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $isPembimbing = ($userRole === 'admin') || $this->pklModel->isUserPembimbingPkl($userId);
        if (!$isPembimbing) {
            Flasher::setFlash('Akses Dibatasi', 'Pengaturan kelompok dan lokasi PKL dikelola melalui menu Guru Pembimbing PKL.', 'warning');
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $selectedKelasId = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
        $activeKelas     = null;
        $allKelasPkl     = [];

        if ($userRole === 'admin') {
            $allKelasPkl = $this->pklModel->getAllKelasPkl();
            if ($selectedKelasId > 0) {
                $activeKelas = $this->pklModel->getKelasById($selectedKelasId);
            } elseif (!empty($allKelasPkl)) {
                $activeKelas = $allKelasPkl[0];
            }
        } else {
            // Guru Pembimbing PKL: Ambil daftar kelas yang menjadi tanggung jawabnya
            $allKelasPkl = $this->pklModel->getKelasByPembimbingUserId($userId);
            if ($selectedKelasId > 0) {
                // Pastikan kelas yang dipilih ada di dalam hak bimbingannya
                foreach ($allKelasPkl as $kp) {
                    if ((int)$kp->id === $selectedKelasId) {
                        $activeKelas = $kp;
                        break;
                    }
                }
            }
            if (!$activeKelas && !empty($allKelasPkl)) {
                $activeKelas = $allKelasPkl[0];
            }
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

        // Ambil data guru pembimbing saat ini
        $guruData = ($userRole !== 'admin') ? $this->pklModel->getGuruByUserId($userId) : null;

        $data = [
            'judul'         => 'Kelompok & Lokasi PKL',
            'userRole'      => $userRole,
            'isPembimbing'  => $isPembimbing,
            'guruData'      => $guruData,
            'activeKelas'   => $activeKelas,
            'allKelasPkl'   => $allKelasPkl,
            'kelompokList'  => $kelompokList,
            'siswaList'     => $siswaList,
            'stats'         => $stats,
            'allPembimbing' => $this->pklModel->getAllPembimbing(),
        ];

        $this->view('templates/header', $data);
        $this->view('pkl/kelompok', $data);
        $this->view('templates/footer');
    }

    /**
     * Ambil data JSON satu kelompok untuk form modal edit
     */
    public function get_detail_json() {
        AuthHelper::check();
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $isPembimbing = ($userRole === 'admin') || $this->pklModel->isUserPembimbingPkl($userId);
        if (!$isPembimbing) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            exit;
        }

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
     * Simpan / Perbarui Kelompok PKL beserta lokasi Maps dan anggota siswanya
     */
    public function save_kelompok() {
        AuthHelper::check();
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $isPembimbing = ($userRole === 'admin') || $this->pklModel->isUserPembimbingPkl($userId);
        if (!$isPembimbing) {
            Flasher::setFlash('Akses Ditolak', 'Anda tidak memiliki hak akses sebagai Guru Pembimbing PKL.', 'danger');
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

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

        // Validasi akses jika bukan admin: pastikan kelas tersebut berada di bawah tanggung jawab guru pembimbing ini
        if ($userRole !== 'admin') {
            $allowedKelas = $this->pklModel->getKelasByPembimbingUserId($userId);
            $allowedIds = array_map(function($k) { return (int)$k->id; }, $allowedKelas);
            if (!in_array($kelasId, $allowedIds)) {
                Flasher::setFlash('Akses Ditolak', 'Anda tidak memiliki hak bimbingan untuk kelas ini.', 'danger');
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

        // Ambil guru_id pembimbing
        $guru = $this->pklModel->getGuruByUserId($userId);
        $pembimbingGuruId = $guru ? (int)$guru->id : null;

        // Jika admin memilih guru pembimbing spesifik dari form (opsional)
        if ($userRole === 'admin' && !empty($_POST['pembimbing_guru_id'])) {
            $pembimbingGuruId = (int)$_POST['pembimbing_guru_id'];
        }

        $data = [
            'id'                 => $id,
            'kelas_id'           => $kelasId,
            'nama_kelompok'      => $namaKelompok,
            'nama_tempat'        => $namaTempat,
            'alamat'             => $alamat,
            'link_lokasi'        => $linkLokasi,
            'latitude'           => (float)$latInput,
            'longitude'          => (float)$lngInput,
            'radius_meter'       => $radius,
            'pembimbing_nama'    => $pembimbing,
            'pembimbing_kontak'  => $kontak,
            'pembimbing_guru_id' => $pembimbingGuruId,
            'created_by'         => $userId
        ];

        $savedId = $this->pklModel->saveKelompok($data, $siswaIds);

        if ($savedId) {
            $cntAnggota = count($siswaIds);
            Flasher::setFlash('Berhasil', "Kelompok PKL <strong>" . htmlspecialchars($namaKelompok) . "</strong> dan lokasi Maps berhasil disimpan dengan {$cntAnggota} siswa anggota.", 'success');
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
        AuthHelper::check();
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $isPembimbing = ($userRole === 'admin') || $this->pklModel->isUserPembimbingPkl($userId);
        if (!$isPembimbing) {
            Flasher::setFlash('Akses Ditolak', 'Anda tidak memiliki hak akses Guru Pembimbing PKL.', 'danger');
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $kelasId  = (int)($_POST['kelas_id'] ?? 0);

        $kel = $this->pklModel->getKelompokById($id);
        if (!$kel) {
            Flasher::setFlash('Gagal', 'Kelompok PKL tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/pkl/kelompok');
            exit;
        }

        if ($userRole !== 'admin') {
            $allowedKelas = $this->pklModel->getKelasByPembimbingUserId($userId);
            $allowedIds = array_map(function($k) { return (int)$k->id; }, $allowedKelas);
            if (!in_array((int)$kel->kelas_id, $allowedIds)) {
                Flasher::setFlash('Akses Ditolak', 'Anda tidak berwenang menghapus kelompok dari kelas ini.', 'danger');
                header('Location: ' . BASEURL . '/pkl/kelompok');
                exit;
            }
        }

        $this->pklModel->deleteKelompok($id);
        Flasher::setFlash('Berhasil', 'Kelompok PKL dan lokasi Maps berhasil dihapus.', 'success');
        header('Location: ' . BASEURL . '/pkl/kelompok?kelas_id=' . $kelasId);
        exit;
    }

    /**
     * Otomatis deteksi & ekstrak titik Latitude dan Longitude dari Link Google Maps
     */
    public function resolve_gmaps() {
        AuthHelper::check();
        header('Content-Type: application/json; charset=UTF-8');

        $url = trim($_REQUEST['url'] ?? '');
        if (empty($url)) {
            echo json_encode(['success' => false, 'message' => 'URL Google Maps kosong.']);
            exit;
        }

        // Helper regex extraction
        $extractCoords = function($rawUrl) {
            // 1. !3d... !4d... (Google Maps Place format)
            if (preg_match('/!3d([-+]?\d+\.\d+)!4d([-+]?\d+\.\d+)/', $rawUrl, $m)) {
                return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
            }
            // 2. [?&]q=lat,lng
            if (preg_match('/[?&]q=([-+]?\d+\.\d+)[,%2C\s]+([-+]?\d+\.\d+)/', $rawUrl, $m)) {
                return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
            }
            // 3. @lat,lng
            if (preg_match('/@([-+]?\d+\.\d+),([-+]?\d+\.\d+)/', $rawUrl, $m)) {
                return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
            }
            // 4. [?&](query|ll)=lat,lng
            if (preg_match('/[?&](?:query|ll)=([-+]?\d+\.\d+)[,%2C\s]+([-+]?\d+\.\d+)/', $rawUrl, $m)) {
                return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
            }
            // 5. Raw lat, lng input
            if (preg_match('/^\s*([-+]?\d{1,2}\.\d+)[,\s]+([-+]?\d{1,3}\.\d+)\s*$/', $rawUrl, $m)) {
                return ['lat' => (float)$m[1], 'lng' => (float)$m[2]];
            }
            return null;
        };

        // Cek langsung pada URL awal
        $coords = $extractCoords($url);
        if ($coords) {
            echo json_encode([
                'success' => true,
                'lat'     => $coords['lat'],
                'lng'     => $coords['lng'],
                'source'  => 'direct'
            ]);
            exit;
        }

        // Jika shortlink (misal maps.app.goo.gl atau goo.gl/maps), ikuti redirect
        if (strpos($url, 'goo.gl') !== false || strpos($url, 'maps.app.goo.gl') !== false || strpos($url, 'bit.ly') !== false) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_HEADER         => true,
                CURLOPT_NOBODY         => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 6,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            curl_exec($ch);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            if ($effectiveUrl) {
                $coords = $extractCoords($effectiveUrl);
                if ($coords) {
                    echo json_encode([
                        'success'       => true,
                        'lat'           => $coords['lat'],
                        'lng'           => $coords['lng'],
                        'effective_url' => $effectiveUrl,
                        'source'        => 'redirect'
                    ]);
                    exit;
                }
            }
        }

        echo json_encode([
            'success' => false,
            'message' => 'Titik koordinat tidak ditemukan di link Google Maps ini. Silakan masukkan Latitude dan Longitude secara manual.'
        ]);
        exit;
    }

    /**
     * Halaman Monitoring Presensi PKL Siswa Hari Ini (Selfie & Jarak GPS)
     */
    public function monitoring() {
        AuthHelper::check();
        $userId   = (int)SessionHelper::get('user_id');
        $userRole = SessionHelper::get('role');

        $isPembimbing = ($userRole === 'admin') || $this->pklModel->isUserPembimbingPkl($userId);
        if (!$isPembimbing) {
            Flasher::setFlash('Akses Dibatasi', 'Monitoring Presensi PKL hanya dapat diakses oleh Guru Pembimbing PKL dan Admin.', 'warning');
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }

        $selectedKelasId = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
        $tanggal         = !empty($_GET['tanggal']) ? trim($_GET['tanggal']) : date('Y-m-d');
        $allKelasPkl     = [];
        $activeKelas     = null;

        if ($userRole === 'admin') {
            $allKelasPkl = $this->pklModel->getAllKelasPkl();
            if ($selectedKelasId > 0) {
                $activeKelas = $this->pklModel->getKelasById($selectedKelasId);
            } elseif (!empty($allKelasPkl)) {
                $activeKelas = $allKelasPkl[0];
            }
        } else {
            $allKelasPkl = $this->pklModel->getKelasByPembimbingUserId($userId);
            if ($selectedKelasId > 0) {
                foreach ($allKelasPkl as $kp) {
                    if ((int)$kp->id === $selectedKelasId) {
                        $activeKelas = $kp;
                        break;
                    }
                }
            }
            if (!$activeKelas && !empty($allKelasPkl)) {
                $activeKelas = $allKelasPkl[0];
            }
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

    /**
     * Alias route: Log Data Presensi PKL
     */
    public function log() {
        $this->monitoring();
    }
}
