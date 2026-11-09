<?php
class KepsekController extends Controller {
    private $laporanModel;

    public function __construct() {
        AuthHelper::checkRole(['kepsek', 'admin']);
        $this->laporanModel = $this->model('Laporan');
    }

    /**
     * Index route: arahkan langsung ke modul Rekap Absensi Guru terpadu
     */
    public function index() {
        $query = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '?tipe=guru';
        header('Location: ' . BASEURL . '/rekapabsensi' . $query);
        exit;
    }

    /**
     * Alias legacy untuk laporan kepsek: alihkan ke modul Rekap Absensi Guru terpadu
     */
    public function laporan() {
        $params = $_GET;
        $params['tipe'] = 'guru';
        header('Location: ' . BASEURL . '/rekapabsensi?' . http_build_query($params));
        exit;
    }

    /**
     * Download rekap absensi guru (CSV)
     */
    public function download_guru() {
        $dari   = $_GET['dari']   ?? date('Y-m-01');
        $sampai = $_GET['sampai'] ?? date('Y-m-d');
        if ($dari > $sampai) {
            $tmp = $dari;
            $dari = $sampai;
            $sampai = $tmp;
        }
        
        $absensiModel = $this->model('Absensi');
        $syncSampai = min($sampai, date('Y-m-d'));
        if ($dari <= $syncSampai) {
            $absensiModel->syncAlfaDanIzinRange($dari, $syncSampai);
        }
        $rows = $this->laporanModel->getRekapGuruAll($dari, $sampai);

        $filename = 'rekap_absensi_guru_' . $dari . '_sd_' . $sampai . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Excel
        fputcsv($out, ['Tanggal', 'NIP', 'Nama Guru', 'Jam Datang', 'Jam Pulang', 'Status', 'Keterangan'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->tanggal,
                $r->nip ?? '-',
                $r->name,
                $r->jam_datang ?? '-',
                $r->jam_pulang ?? '-',
                ucfirst($r->status),
                $r->keterangan ?? '-',
            ], ';');
        }
        fclose($out);
        exit;
    }
}
