<?php
class RekapAbsensiController extends Controller {
    private $laporanModel;
    private $monitorModel;
    private $db;

    public function __construct() {
        AuthHelper::checkRole(['admin', 'kepsek', 'wali_kelas', 'walikelas']);
        $this->laporanModel = $this->model('Laporan');
        $this->monitorModel = $this->model('AbsensiMonitor');
        $this->db = new Database();
    }

    public function index() {
        $role = SessionHelper::get('role');
        $userId = SessionHelper::get('user_id');
        
        // Tentukan tipe absensi: Siswa atau Guru
        if ($role === 'admin') {
            $tipe = isset($_GET['tipe']) && in_array(strtolower($_GET['tipe']), ['siswa', 'guru']) ? strtolower($_GET['tipe']) : 'siswa';
        } elseif ($role === 'kepsek') {
            $tipe = 'guru';
        } else {
            $tipe = 'siswa';
        }

        // Tentukan mode tampilan: 'ringkasan' (Rekap Per Siswa / Nilai Raport) atau 'detail' (Log Transaksi Harian)
        $viewMode = isset($_GET['view']) && in_array(strtolower($_GET['view']), ['ringkasan', 'detail']) ? strtolower($_GET['view']) : 'ringkasan';

        // Tentukan kategori absensi: 'biasa' (Reguler) atau 'pkl' (Presensi PKL)
        $kategori = isset($_GET['kategori']) && in_array(strtolower($_GET['kategori']), ['biasa', 'pkl']) ? strtolower($_GET['kategori']) : 'biasa';

        $filters = [
            'dari'     => $_GET['dari'] ?? date('Y-m-01'),
            'sampai'   => $_GET['sampai'] ?? date('Y-m-d'),
            'status'   => $_GET['status'] ?? '',
            'search'   => trim($_GET['search'] ?? ''),
            'kelas_id' => $_GET['kelas_id'] ?? '',
        ];

        // Sinkronisasi data Alfa & Izin untuk periode yang dipilih
        // Dibatasi 1x per 10 menit per rentang tanggal agar TTFB instan dan beban server ringan
        $syncKey = 'last_sync_alfa_' . md5(($filters['dari'] ?? '') . '_' . ($filters['sampai'] ?? ''));
        $lastSync = $_SESSION[$syncKey] ?? 0;
        if (time() - $lastSync > 600) {
            $absensiModel = $this->model('Absensi');
            $syncSampai = min($filters['sampai'], date('Y-m-d'));
            if ($filters['dari'] <= $syncSampai) {
                $absensiModel->syncAlfaDanIzinRange($filters['dari'], $syncSampai);
            }
            $_SESSION[$syncKey] = time();
        }

        // Ambil daftar seluruh kelas untuk filter dropdown admin
        $this->db->query("SELECT id, nama_kelas, tingkat, jurusan, status FROM kelas ORDER BY nama_kelas ASC");
        $kelasList = $this->db->resultSet();

        // Pagination
        $limit = isset($_GET['limit']) ? max(5, min(100, (int)$_GET['limit'])) : 25;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;
        $filters['limit'] = $limit;
        $filters['offset'] = $offset;

        $dataAbsensi  = [];
        $totalData    = 0;
        $stats        = [];
        $akumulasiMap = [];

        $waliKelasInfo = null;
        if ($tipe === 'guru') {
            $stats = $this->laporanModel->getStatsRekapGuru($filters);

            if ($viewMode === 'ringkasan') {
                $dataAbsensi = $this->laporanModel->getAkumulasiGuru($filters);
                $totalData   = $this->laporanModel->countAkumulasiGuru($filters);
            } else {
                $dataAbsensi = $this->laporanModel->getRekapGuru($filters);
                $totalData   = $this->laporanModel->countRekapGuru($filters);
                $guruIds     = array_unique(array_filter(array_map(function($r) { return $r->guru_id ?? null; }, $dataAbsensi)));
                $akumulasiMap = $this->laporanModel->getAkumulasiMapGuru($filters['dari'], $filters['sampai'], $guruIds);
            }
        } else {
            // Siswa
            if (in_array($role, ['wali_kelas', 'walikelas'])) {
                // Wali kelas dibatasi khusus kelas asuhannya saja
                $waliKelasInfo = $this->monitorModel->getKelasWali($userId);
                if ($waliKelasInfo) {
                    $filters['kelas_id'] = (int)$waliKelasInfo->id;
                } else {
                    $filters['kelas_id'] = -1; // Kunci ke ID -1 agar tidak menampilkan data siswa lain jika belum ditugaskan kelas
                }
            }

            // Deteksi apakah kelas saat ini berstatus PKL
            $isKelasPkl = false;
            $activeKelasObj = null;
            if (!empty($filters['kelas_id']) && (int)$filters['kelas_id'] > 0) {
                foreach ($kelasList as $k) {
                    if ((int)$k->id === (int)$filters['kelas_id'] && ($k->status ?? '') === 'pkl') {
                        $isKelasPkl = true;
                        $activeKelasObj = $k;
                        break;
                    }
                }
            } elseif (in_array($role, ['wali_kelas', 'walikelas']) && !empty($waliKelasInfo)) {
                if (($waliKelasInfo->status ?? '') === 'pkl') {
                    $isKelasPkl = true;
                    $activeKelasObj = $waliKelasInfo;
                }
            }

            $stats = $this->laporanModel->getStatsRekapSiswa($filters);

            if ($kategori === 'pkl') {
                // Khusus mode Rekap Absensi PKL
                if ($viewMode === 'ringkasan') {
                    $dataAbsensi = $this->laporanModel->getAkumulasiPkl($filters);
                    $totalData   = $this->laporanModel->countAkumulasiPkl($filters);
                } else {
                    $dataAbsensi = $this->laporanModel->getRekapPkl($filters);
                    $totalData   = $this->laporanModel->countRekapPkl($filters);
                }
            } else {
                // Mode Rekap Absensi Biasa (Reguler)
                if ($viewMode === 'ringkasan') {
                    $dataAbsensi = $this->laporanModel->getAkumulasiSiswa($filters);
                    $totalData   = $this->laporanModel->countAkumulasiSiswa($filters);
                } else {
                    $dataAbsensi = $this->laporanModel->getRekapSiswa($filters);
                    $totalData   = $this->laporanModel->countRekapSiswa($filters);
                    $siswaIds    = array_unique(array_filter(array_map(function($r) { return $r->siswa_id ?? null; }, $dataAbsensi)));
                    $akumulasiMap = $this->laporanModel->getAkumulasiMapSiswa($filters['dari'], $filters['sampai'], $siswaIds);
                }
            }
        }

        $totalPages = $totalData > 0 ? ceil($totalData / $limit) : 1;

        $data = [
            'judul'          => 'Rekap Absensi',
            'role'           => $role,
            'tipe'           => $tipe,
            'viewMode'       => $viewMode,
            'kategori'       => $kategori,
            'isKelasPkl'     => $isKelasPkl,
            'activeKelasObj' => $activeKelasObj,
            'filters'        => $filters,
            'dataAbsensi'    => $dataAbsensi,
            'akumulasiMap'   => $akumulasiMap,
            'kelasList'      => $kelasList,
            'waliKelasInfo'  => $waliKelasInfo,
            'stats'          => $stats,
            'page'           => $page,
            'limit'          => $limit,
            'totalPages'     => $totalPages,
            'totalData'      => $totalData
        ];

        $this->view('templates/header', $data);
        $this->view('rekap_absensi/index', $data);
        $this->view('templates/footer');
    }

    public function download() {
        $role   = SessionHelper::get('role');
        $userId = SessionHelper::get('user_id');
        
        $dari   = $_GET['dari']   ?? date('Y-m-01');
        $sampai = $_GET['sampai'] ?? date('Y-m-d');
        $viewMode = isset($_GET['view']) && in_array(strtolower($_GET['view']), ['ringkasan', 'detail']) ? strtolower($_GET['view']) : 'ringkasan';
        $kategori = isset($_GET['kategori']) && in_array(strtolower($_GET['kategori']), ['biasa', 'pkl']) ? strtolower($_GET['kategori']) : 'biasa';

        // Tentukan tipe
        if ($role === 'admin') {
            $tipe = isset($_GET['tipe']) && in_array(strtolower($_GET['tipe']), ['siswa', 'guru']) ? strtolower($_GET['tipe']) : 'siswa';
        } elseif ($role === 'kepsek') {
            $tipe = 'guru';
        } else {
            $tipe = 'siswa';
        }

        $extraFilters = [];
        if (!empty($_GET['status']))   $extraFilters['status']   = $_GET['status'];
        if (!empty($_GET['search']))   $extraFilters['search']   = trim($_GET['search']);
        if (!empty($_GET['kelas_id'])) $extraFilters['kelas_id'] = (int)$_GET['kelas_id'];

        // Sinkronisasi data Alfa & Izin untuk periode yang diekspor
        // Siswa tanpa tap RFID dan guru tanpa scan QR otomatis tercatat Alfa jika tidak ada izin/sakit
        $absensiModel = $this->model('Absensi');
        $syncSampai = min($sampai, date('Y-m-d'));
        if ($dari <= $syncSampai) {
            $absensiModel->syncAlfaDanIzinRange($dari, $syncSampai);
        }

        if ($kategori === 'pkl' && $tipe === 'siswa') {
            if ($viewMode === 'ringkasan') {
                $rows = $this->laporanModel->getAkumulasiPklAll($dari, $sampai, $extraFilters);
                $filename = 'rekap_akumulasi_presensi_pkl_' . $dari . '_sd_' . $sampai . '.csv';

                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Pragma: no-cache');

                $out = fopen('php://output', 'w');
                fputs($out, "\xEF\xBB\xBF");

                fputcsv($out, ['REKAPITULASI PRESENSI PKL SISWA'], ';');
                fputcsv($out, ['Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai))], ';');
                fputcsv($out, ['Dicetak: ' . date('d/m/Y H:i:s')], ';');
                fputcsv($out, [''], ';');

                fputcsv($out, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Tempat PKL', 'Guru Pembimbing', 'Total Hadir Datang', 'Hadir Lengkap (Pulang)', 'Izin', 'Sakit', 'Alfa', 'Persentase (%)'], ';');

                $no = 1;
                foreach ($rows as $r) {
                    $totalMasuk = (int)($r->pkl_hadir ?? 0);
                    $totalHari  = $totalMasuk + (int)($r->izin ?? 0) + (int)($r->sakit ?? 0) + (int)($r->alfa ?? 0);
                    $pct = $totalHari > 0 ? round(($totalMasuk / $totalHari) * 100, 1) : 0;

                    fputcsv($out, [
                        $no++,
                        $r->nis ?? '-',
                        $r->name,
                        $r->nama_kelas,
                        $r->nama_tempat ?? ($r->nama_kelompok ?? '-'),
                        $r->pembimbing_guru_nama ?? '-',
                        $r->pkl_hadir ?? 0,
                        $r->pkl_lengkap ?? 0,
                        $r->izin ?? 0,
                        $r->sakit ?? 0,
                        $r->alfa ?? 0,
                        $pct . '%',
                    ], ';');
                }

                fclose($out);
                exit;
            } else {
                // Detail logs PKL
                $rows = $this->laporanModel->getRekapPklAll($dari, $sampai, $extraFilters);
                $filename = 'log_detail_presensi_pkl_' . $dari . '_sd_' . $sampai . '.csv';

                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Pragma: no-cache');

                $out = fopen('php://output', 'w');
                fputs($out, "\xEF\xBB\xBF");

                fputcsv($out, ['LOG DETAIL PRESENSI PKL SISWA'], ';');
                fputcsv($out, ['Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai))], ';');
                fputcsv($out, ['Dicetak: ' . date('d/m/Y H:i:s')], ';');
                fputcsv($out, [''], ';');

                fputcsv($out, ['No', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Tempat PKL', 'Guru Pembimbing', 'Jam Datang', 'Jam Pulang', 'Jarak GPS (Meter)', 'Keterangan'], ';');

                $no = 1;
                foreach ($rows as $r) {
                    $tgl = date('d/m/Y', strtotime($r->tanggal));
                    fputcsv($out, [
                        $no++,
                        $tgl,
                        $r->nis ?? '-',
                        $r->name,
                        $r->nama_kelas,
                        $r->nama_tempat ?? ($r->nama_kelompok ?? '-'),
                        $r->pembimbing_guru_nama ?? '-',
                        $r->jam_datang ? substr($r->jam_datang, 0, 5) : '-',
                        $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-',
                        $r->jarak_meter !== null ? number_format($r->jarak_meter, 1) . ' m' : '-',
                        $r->keterangan ?? 'Presensi PKL',
                    ], ';');
                }

                fclose($out);
                exit;
            }
        }

        if ($viewMode === 'ringkasan') {
            // ================= EXPORT MODE RINGKASAN RAPORT (PER ORANG) =================
            if ($tipe === 'guru') {
                $rows = $this->laporanModel->getAkumulasiGuruAll($dari, $sampai, $extraFilters);
                $filename = 'rekap_nilai_kehadiran_guru_' . $dari . '_sd_' . $sampai . '.csv';

                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Pragma: no-cache');

                $out = fopen('php://output', 'w');
                fputs($out, "\xEF\xBB\xBF");

                fputcsv($out, ['REKAP NILAI KEHADIRAN GURU (AKUMULASI)'], ';');
                fputcsv($out, ['Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai))], ';');
                fputcsv($out, ['Dicetak: ' . date('d/m/Y H:i')], ';');
                fputcsv($out, [''], ';');

                fputcsv($out, ['No', 'NIP', 'Nama Guru', 'Hadir (H)', 'Terlambat (T)', 'Izin (I)', 'Sakit (S)', 'Alfa (A)', 'Total Masuk', 'Persentase (%)'], ';');

                $no = 1;
                foreach ($rows as $r) {
                    $totalMasuk = (int)$r->hadir + (int)$r->terlambat;
                    $totalHari  = $totalMasuk + (int)$r->izin + (int)$r->sakit + (int)$r->alfa;
                    $pct = $totalHari > 0 ? round(($totalMasuk / $totalHari) * 100, 1) : 0;

                    fputcsv($out, [
                        $no++,
                        $r->nip ?? '-',
                        $r->name,
                        $r->hadir ?? 0,
                        $r->terlambat ?? 0,
                        $r->izin ?? 0,
                        $r->sakit ?? 0,
                        $r->alfa ?? 0,
                        $totalMasuk,
                        $pct . '%',
                    ], ';');
                }

                fclose($out);
                exit;
            } else {
                // Siswa
                $namaKelasHeader = 'SEMUA KELAS';
                $kelasSlug = 'semua_kelas';

                if (in_array($role, ['wali_kelas', 'walikelas'])) {
                    $kelas = $this->monitorModel->getKelasWali($userId);
                    if ($kelas) {
                        $extraFilters['kelas_id'] = (int)$kelas->id;
                        $namaKelasHeader = 'KELAS ' . strtoupper($kelas->nama_kelas);
                        $kelasSlug = preg_replace('/[^a-zA-Z0-9_]/', '_', $kelas->nama_kelas);
                    } else {
                        $extraFilters['kelas_id'] = -1;
                        $namaKelasHeader = 'BELUM DITUGASKAN KELAS';
                        $kelasSlug = 'tanpa_kelas';
                    }
                } elseif (!empty($extraFilters['kelas_id'])) {
                    $this->db->query("SELECT nama_kelas FROM kelas WHERE id = :kid");
                    $this->db->bind(':kid', $extraFilters['kelas_id']);
                    $kObj = $this->db->single();
                    if ($kObj) {
                        $namaKelasHeader = 'KELAS ' . strtoupper($kObj->nama_kelas);
                        $kelasSlug = preg_replace('/[^a-zA-Z0-9_]/', '_', $kObj->nama_kelas);
                    }
                }

                $rows = $this->laporanModel->getAkumulasiSiswaAll($dari, $sampai, $extraFilters);
                $filename = 'rekap_nilai_kehadiran_siswa_' . $kelasSlug . '_' . $dari . '_sd_' . $sampai . '.csv';

                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Pragma: no-cache');

                $out = fopen('php://output', 'w');
                fputs($out, "\xEF\xBB\xBF");

                fputcsv($out, ['REKAP NILAI KEHADIRAN SISWA (FORMAT RAPORT) - ' . $namaKelasHeader], ';');
                fputcsv($out, ['Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai))], ';');
                fputcsv($out, ['Dicetak: ' . date('d/m/Y H:i')], ';');
                fputcsv($out, [''], ';');

                fputcsv($out, ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Hadir (H)', 'Terlambat (T)', 'Izin (I)', 'Sakit (S)', 'Alfa (A)', 'PKL', 'Total Masuk', 'Persentase (%)'], ';');

                $no = 1;
                foreach ($rows as $r) {
                    $totalMasuk = (int)$r->hadir + (int)$r->terlambat + (int)$r->pkl;
                    $totalHari  = $totalMasuk + (int)$r->izin + (int)$r->sakit + (int)$r->alfa;
                    $pct = $totalHari > 0 ? round(($totalMasuk / $totalHari) * 100, 1) : 0;

                    fputcsv($out, [
                        $no++,
                        $r->nis ?? '-',
                        $r->name,
                        $r->nama_kelas ?? '-',
                        $r->hadir ?? 0,
                        $r->terlambat ?? 0,
                        $r->izin ?? 0,
                        $r->sakit ?? 0,
                        $r->alfa ?? 0,
                        $r->pkl ?? 0,
                        $totalMasuk,
                        $pct . '%',
                    ], ';');
                }

                fclose($out);
                exit;
            }
        }

        // ================= EXPORT MODE DETAIL TRANSAKSI HARIAN =================
        $singkatanStatus = function($status) {
            $map = [
                'hadir'     => 'H',
                'terlambat' => 'T',
                'izin'      => 'I',
                'sakit'     => 'S',
                'alfa'      => 'A',
                'pkl'       => 'PKL',
            ];
            return $map[strtolower($status)] ?? strtoupper($status);
        };

        if ($tipe === 'guru') {
            $rows = $this->laporanModel->getRekapGuruAll($dari, $sampai, $extraFilters);
            $filename = 'log_detail_absensi_guru_' . $dari . '_sd_' . $sampai . '.csv';
            
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, ['DETAIL LOG ABSENSI GURU'], ';');
            fputcsv($out, ['Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai))], ';');
            fputcsv($out, ['Dicetak: ' . date('d/m/Y H:i')], ';');
            fputcsv($out, [''], ';');

            fputcsv($out, ['No', 'Tanggal', 'NIP', 'Nama Guru', 'Jam Datang', 'Jam Pulang', 'Status', 'Keterangan Absensi', 'Catatan Evaluasi'], ';');
            
            $no = 1;
            foreach ($rows as $r) {
                $tgl = date('d/m/Y', strtotime($r->tanggal));
                $statusLabel = $singkatanStatus($r->status) . ' - ' . ucfirst($r->status);
                fputcsv($out, [
                    $no++,
                    $tgl,
                    $r->nip ?? '-',
                    $r->name,
                    $r->jam_datang ? substr($r->jam_datang, 0, 5) : '-',
                    $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-',
                    $statusLabel,
                    $r->keterangan ?? '-',
                    $r->data_catatan ?? '-',
                ], ';');
            }

            fclose($out);
            exit;
        } else {
            // Siswa
            $namaKelasHeader = 'SEMUA KELAS';
            $kelasSlug = 'semua_kelas';

            if (in_array($role, ['wali_kelas', 'walikelas'])) {
                $kelas = $this->monitorModel->getKelasWali($userId);
                if ($kelas) {
                    $extraFilters['kelas_id'] = (int)$kelas->id;
                    $namaKelasHeader = 'KELAS ' . strtoupper($kelas->nama_kelas);
                    $kelasSlug = preg_replace('/[^a-zA-Z0-9_]/', '_', $kelas->nama_kelas);
                } else {
                    $extraFilters['kelas_id'] = -1;
                    $namaKelasHeader = 'BELUM DITUGASKAN KELAS';
                    $kelasSlug = 'tanpa_kelas';
                }
            } elseif (!empty($extraFilters['kelas_id'])) {
                $this->db->query("SELECT nama_kelas FROM kelas WHERE id = :kid");
                $this->db->bind(':kid', $extraFilters['kelas_id']);
                $kObj = $this->db->single();
                if ($kObj) {
                    $namaKelasHeader = 'KELAS ' . strtoupper($kObj->nama_kelas);
                    $kelasSlug = preg_replace('/[^a-zA-Z0-9_]/', '_', $kObj->nama_kelas);
                }
            }

            $rows = $this->laporanModel->getRekapSiswaAll($dari, $sampai, $extraFilters);
            $filename = 'log_detail_absensi_siswa_' . $kelasSlug . '_' . $dari . '_sd_' . $sampai . '.csv';
            
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, ['DETAIL LOG ABSENSI SISWA - ' . $namaKelasHeader], ';');
            fputcsv($out, ['Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai))], ';');
            fputcsv($out, ['Dicetak: ' . date('d/m/Y H:i')], ';');
            fputcsv($out, [''], ';');

            fputcsv($out, ['No', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jam Datang', 'Jam Pulang', 'Status', 'Keterangan Absensi', 'Catatan Evaluasi'], ';');
            
            $no = 1;
            foreach ($rows as $r) {
                $tgl = date('d/m/Y', strtotime($r->tanggal));
                $statusLabel = $singkatanStatus($r->status) . ' - ' . ucfirst($r->status);
                fputcsv($out, [
                    $no++,
                    $tgl,
                    $r->nis ?? '-',
                    $r->name,
                    $r->nama_kelas ?? '-',
                    $r->jam_datang ? substr($r->jam_datang, 0, 5) : '-',
                    $r->jam_pulang ? substr($r->jam_pulang, 0, 5) : '-',
                    $statusLabel,
                    $r->keterangan ?? '-',
                    $r->data_catatan ?? '-',
                ], ';');
            }

            fclose($out);
            exit;
        }
    }
}
