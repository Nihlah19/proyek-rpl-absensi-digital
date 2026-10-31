<?php
class KepsekController extends Controller {
    private $laporanModel;

    public function __construct() {
        AuthHelper::checkRole(['kepsek', 'admin']); // Allow admin as well just in case, but prompt says kepsek only? Wait, "Kepala Sekolah tidak boleh mendapatkan akses Admin". But kepsek dashboard shouldn't allow admin. We'll stick to Kepsek only.
        AuthHelper::checkRole(['kepsek']);
        $this->laporanModel = $this->model('Laporan');
    }

    public function laporan() {
        $filters = [
            'dari' => $_GET['dari'] ?? '',
            'sampai' => $_GET['sampai'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        // Custom filter bulan
        $bulan = $_GET['bulan'] ?? date('Y-m');
        if (empty($filters['dari']) && empty($filters['sampai'])) {
            $filters['dari'] = $bulan . '-01';
            $filters['sampai'] = date('Y-m-t', strtotime($bulan . '-01'));
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        $dataAbsensi = $this->laporanModel->getRekapGuru($filters);
        $totalData = $this->laporanModel->countRekapGuru($filters);
        $stats = $this->laporanModel->getStatsRekapGuru($filters);
        
        $totalDays = ($stats['hadir'] + $stats['terlambat'] + $stats['izin'] + $stats['sakit'] + $stats['alfa']) ?: 1;
        $stats['persentase'] = round((($stats['hadir'] + $stats['terlambat']) / $totalDays) * 100, 2);

        // Calculate chart data (Rekap Kehadiran per Hari)
        $chartData = $this->laporanModel->getChartGuru($filters['dari'], $filters['sampai']);

        $totalPages = ceil($totalData / $limit);

        $data = [
            'judul' => 'Monitoring Absensi Guru (Kepsek)',
            'filters' => $filters,
            'bulan' => $bulan,
            'dataAbsensi' => $dataAbsensi,
            'stats' => $stats,
            'chartData' => $chartData,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalData' => $totalData
        ];

        $this->view('templates/header', $data);
        $this->view('laporan/kepsek', $data);
        $this->view('templates/footer');
    }

    public function download_guru() {
        $dari   = $_GET['dari']   ?? date('Y-m-01');
        $sampai = $_GET['sampai'] ?? date('Y-m-d');
        
        $absensiModel = $this->model('Absensi');
        $absensiModel->syncAlfaDanIzinRange($dari, min($sampai, date('Y-m-d')));
        $rows   = $this->laporanModel->getRekapGuruAll($dari, $sampai);

        $filename = 'rekap_absensi_guru_' . $dari . '_sd_' . $sampai . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Tanggal', 'NIP', 'Nama Guru', 'Jam Datang', 'Jam Pulang', 'Status', 'Keterangan'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->tanggal,
                $r->nip,
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
