<?php
/**
 * DashboardController.php
 * Mengisi data dari AbsensiMonitor ke masing-masing view dashboard.
 * Business logic absensi TIDAK diubah.
 */
class DashboardController extends Controller {

    private AbsensiMonitor $monitor;

    public function __construct() {
        $this->monitor = $this->model('AbsensiMonitor');
    }

    /** Redirect ke dashboard sesuai role */
    public function index() {
        AuthHelper::check();
        $role = SessionHelper::get('role');
        $map = [
            'admin'      => '/dashboard/admin',
            'guru'       => '/dashboard/guru',
            'kepsek'     => '/dashboard/kepala-sekolah',
            'walikelas'  => '/dashboard/wali-kelas',
            'siswa'      => '/dashboard/siswa',
        ];
        $url = $map[$role] ?? '/auth/login';
        header('Location: ' . BASEURL . $url);
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN
    // ─────────────────────────────────────────────────────────────────────────
    public function admin() {
        AuthHelper::checkRole(['admin']);
        $data = [
            'judul'       => 'Dashboard Admin',
            'statsSiswa'  => $this->monitor->getStatsSiswaHariIni(),
            'statsGuru'   => $this->monitor->getStatsGuruHariIni(),
            'iotDevices'  => $this->monitor->getStatusIotDevices(),
            'tanggal'     => date('d F Y'),
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/admin', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GURU
    // ─────────────────────────────────────────────────────────────────────────
    public function guru() {
        AuthHelper::checkRole(['guru']);
        $userId = (int)SessionHelper::get('user_id');
        $guruId = $this->monitor->getGuruIdByUserId($userId);

        $data = [
            'judul'        => 'Dashboard Guru',
            'absensiHariIni' => $guruId ? $this->monitor->getAbsensiGuruHariIni($guruId) : null,
            'guru_id'      => $guruId,
            'tanggal'      => date('d F Y'),
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/guru', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KEPALA SEKOLAH
    // ─────────────────────────────────────────────────────────────────────────
    public function kepala_sekolah() {
        AuthHelper::checkRole(['kepsek']);
        $data = [
            'judul'            => 'Dashboard Kepala Sekolah',
            'statsGuru'        => $this->monitor->getStatsGuruHariIni(),
            'guruTerlambat'    => $this->monitor->getGuruSeringTerlambat(5),
            'tanggal'          => date('d F Y'),
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/kepala_sekolah', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WALI KELAS
    // ─────────────────────────────────────────────────────────────────────────
    public function wali_kelas() {
        AuthHelper::checkRole(['walikelas']);
        $userId  = (int)SessionHelper::get('user_id');
        $kelas   = $this->monitor->getKelasWali($userId);
        $kelasId = $kelas ? (int)$kelas->id : null;

        $data = [
            'judul'          => 'Dashboard Wali Kelas',
            'kelas'          => $kelas,
            'statsSiswa'     => $kelasId ? $this->monitor->getStatsSiswaKelas($kelasId) : null,
            'siswaTerlambat' => $kelasId ? $this->monitor->getSiswaSaringTerlambat($kelasId, 5) : [],
            'tanggal'        => date('d F Y'),
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/wali_kelas', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA
    // ─────────────────────────────────────────────────────────────────────────
    public function siswa() {
        AuthHelper::checkRole(['siswa']);
        $userId  = (int)SessionHelper::get('user_id');
        $siswaId = $this->monitor->getSiswaIdByUserId($userId);

        $data = [
            'judul'          => 'Dashboard Siswa',
            'absensiHariIni' => $siswaId ? $this->monitor->getAbsensiSiswaHariIni($siswaId) : null,
            'tanggal'        => date('d F Y'),
        ];
        $this->view('templates/header', $data);
        $this->view('dashboard/siswa', $data);
        $this->view('templates/footer');
    }
}
