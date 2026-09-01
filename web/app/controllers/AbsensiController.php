<?php
/**
 * AbsensiController.php
 * Riwayat absensi dengan search, filter tanggal, filter status, dan pagination.
 * Hanya mengambil data — tidak mengubah business logic absensi.
 */
class AbsensiController extends Controller {

    private AbsensiMonitor $monitor;
    private const PER_PAGE = 15;

    public function __construct() {
        $this->monitor = $this->model('AbsensiMonitor');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GURU — riwayat absensinya sendiri
    // ─────────────────────────────────────────────────────────────────────────
    public function guru() {
        AuthHelper::checkRole(['guru']);
        $userId = (int)SessionHelper::get('user_id');
        $guruId = $this->monitor->getGuruIdByUserId($userId);

        $dari   = trim($_GET['dari']   ?? '');
        $sampai = trim($_GET['sampai'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $search = trim($_GET['search'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $rows  = $guruId ? $this->monitor->getRiwayatGuru($guruId, $dari, $sampai, $status, $search, $offset, self::PER_PAGE) : [];
        $total = $guruId ? $this->monitor->countRiwayatGuru($guruId, $dari, $sampai, $status, $search) : 0;

        $data = [
            'judul'    => 'Riwayat Absensi',
            'rows'     => $rows,
            'total'    => $total,
            'page'     => $page,
            'perPage'  => self::PER_PAGE,
            'dari'     => $dari,
            'sampai'   => $sampai,
            'status'   => $status,
            'search'   => $search,
        ];
        $this->view('templates/header', $data);
        $this->view('absensi/index_guru', $data);
        $this->view('templates/footer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SISWA — riwayat absensinya sendiri
    // ─────────────────────────────────────────────────────────────────────────
    public function siswa() {
        AuthHelper::checkRole(['siswa']);
        $userId  = (int)SessionHelper::get('user_id');
        $siswaId = $this->monitor->getSiswaIdByUserId($userId);

        $dari   = trim($_GET['dari']   ?? '');
        $sampai = trim($_GET['sampai'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $rows  = $siswaId ? $this->monitor->getRiwayatSiswa($siswaId, $dari, $sampai, $status, '', $offset, self::PER_PAGE) : [];
        $total = $siswaId ? $this->monitor->countRiwayatSiswa($siswaId, $dari, $sampai, $status) : 0;

        $data = [
            'judul'   => 'Riwayat Absensi',
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'perPage' => self::PER_PAGE,
            'dari'    => $dari,
            'sampai'  => $sampai,
            'status'  => $status,
        ];
        $this->view('templates/header', $data);
        $this->view('absensi/index_siswa', $data);
        $this->view('templates/footer');
    }
}
