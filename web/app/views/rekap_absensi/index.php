<?php
/**
 * View Rekapitulasi Presensi (Siswa, Guru, dan PKL)
 * Desain: Human-Crafted Professional School Administration
 * Standar: Ergonomis, Rapi, Aksesibilitas Tinggi (WCAG 2.1 AA), dan Performa Maksimal
 */

$statusMap = [
    'hadir'     => ['singkat' => 'H',   'label' => 'Hadir',     'badge' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'],
    'terlambat' => ['singkat' => 'T',   'label' => 'Terlambat', 'badge' => 'bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800'],
    'izin'      => ['singkat' => 'I',   'label' => 'Izin',      'badge' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800'],
    'sakit'     => ['singkat' => 'S',   'label' => 'Sakit',     'badge' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800'],
    'alfa'      => ['singkat' => 'A',   'label' => 'Alfa',      'badge' => 'bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800'],
    'pkl'       => ['singkat' => 'PKL', 'label' => 'PKL',       'badge' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800'],
];

$role           = $data['role'] ?? '';
$tipe           = $data['tipe'] ?? 'siswa';
$viewMode       = $data['viewMode'] ?? 'ringkasan';
$kategori       = ($tipe === 'guru') ? 'biasa' : ($data['kategori'] ?? 'biasa');
$isKelasPkl     = ($tipe === 'guru') ? false : ($data['isKelasPkl'] ?? false);
$activeKelasObj = ($tipe === 'guru') ? null : ($data['activeKelasObj'] ?? null);
$filters        = $data['filters'] ?? [];
$stats          = $data['stats'] ?? [];
$akumulasiMap   = $data['akumulasiMap'] ?? [];

// Parameter URL untuk unduh CSV
$csvParams = [
    'tipe'     => $tipe,
    'view'     => $viewMode,
    'kategori' => $kategori,
    'dari'     => $filters['dari'] ?? '',
    'sampai'   => $filters['sampai'] ?? '',
];
if (!empty($filters['status']))   $csvParams['status']   = $filters['status'];
if (!empty($filters['search']))   $csvParams['search']   = $filters['search'];
if (!empty($filters['kelas_id'])) $csvParams['kelas_id'] = $filters['kelas_id'];
$csvUrl = BASEURL . '/rekapabsensi/download?' . http_build_query($csvParams);

// URL Switcher View Mode (Ringkasan vs Detail)
$swView = $filters;
unset($swView['limit'], $swView['offset']);
$swView['tipe']     = $tipe;
$swView['kategori'] = $kategori;

$swView['view']   = 'ringkasan';
$urlViewRingkasan = BASEURL . '/rekapabsensi?' . http_build_query($swView);

$swView['view']   = 'detail';
$urlViewDetail    = BASEURL . '/rekapabsensi?' . http_build_query($swView);

// URL Kategori PKL vs Biasa
$swKat = $filters;
unset($swKat['limit'], $swKat['offset']);
$swKat['tipe'] = $tipe;
$swKat['view'] = $viewMode;

$swKat['kategori'] = 'biasa';
$urlKatBiasa = BASEURL . '/rekapabsensi?' . http_build_query($swKat);

$swKat['kategori'] = 'pkl';
$urlKatPkl = BASEURL . '/rekapabsensi?' . http_build_query($swKat);

// Hitung total kehadiran keseluruhan untuk KPI
$totHadir = (int)($stats['hadir'] ?? 0);
$totTelat = (int)($stats['terlambat'] ?? 0);
$totIzin  = (int)($stats['izin'] ?? 0);
$totSakit = (int)($stats['sakit'] ?? 0);
$totAlfa  = (int)($stats['alfa'] ?? 0);
$totPkl   = (int)($stats['pkl'] ?? 0);
$totalKehadiran = $totHadir + $totTelat + $totPkl;
$totalAkumulasi = $totalKehadiran + $totIzin + $totSakit + $totAlfa;
$persenRata = $totalAkumulasi > 0 ? round(($totalKehadiran / $totalAkumulasi) * 100, 1) : 0;
?>

<div class="w-full space-y-5">
    <!-- ── 1. HEADER HALAMAN & AKSI NAVIGASI ── -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-1" aria-label="Breadcrumb">
                <a href="<?= BASEURL; ?>/dashboard" class="hover:text-slate-800 transition-colors">Dashboard</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 dark:text-slate-200 font-semibold">Rekapitulasi Presensi</span>
            </nav>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight font-heading">
                    <?php if ($tipe === 'guru'): ?>
                        Rekap Kehadiran Guru
                    <?php elseif (in_array($role, ['wali_kelas', 'walikelas']) && !empty($data['waliKelasInfo'])): ?>
                        Rekap Kehadiran Kelas <?= htmlspecialchars($data['waliKelasInfo']->nama_kelas) ?>
                    <?php else: ?>
                        Rekap Kehadiran Siswa
                    <?php endif; ?>
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <?= $tipe === 'guru' ? 'Dewan Guru' : ($kategori === 'pkl' ? 'Siswa PKL' : 'Siswa Reguler') ?>
                </span>
                <?php if ($viewMode === 'ringkasan'): ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Format Raport
                </span>
                <?php else: ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    Log Harian
                </span>
                <?php endif; ?>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                Periode: <strong class="text-slate-800"><?= date('d M Y', strtotime($filters['dari'])) ?></strong> s/d <strong class="text-slate-800"><?= date('d M Y', strtotime($filters['sampai'])) ?></strong>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
            <a href="<?= $csvUrl ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-700 text-white rounded-lg text-xs font-semibold hover:bg-emerald-800 transition-colors shadow-2xs"
               aria-label="Unduh data dalam format CSV">
                <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                <span>Unduh CSV</span>
            </a>
            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-2xs"
                    aria-label="Cetak halaman rekapitulasi">
                <i class="fa-solid fa-print text-slate-500" aria-hidden="true"></i>
                <span>Cetak</span>
            </button>
            <a href="<?= BASEURL ?>/dashboard" 
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-2xs">
                <i class="fa-solid fa-arrow-left text-slate-500" aria-hidden="true"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Peringatan Wali Kelas belum punya kelas binaan -->
    <?php if (in_array($role, ['wali_kelas', 'walikelas']) && empty($data['waliKelasInfo'])): ?>
    <div class="p-3.5 bg-amber-50 border border-amber-300 rounded-xl text-amber-900 text-xs flex items-center gap-2.5">
        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm shrink-0" aria-hidden="true"></i>
        <span>Akun Anda belum ditugaskan ke kelas manapun. Silakan hubungi admin sekolah untuk pengaturan wali kelas.</span>
    </div>
    <?php endif; ?>

    <!-- ── 2. SEGMENTED TABS UNTUK ADMIN (SISWA VS GURU) ── -->
    <?php if (in_array($role, ['admin', 'kepsek'])): ?>
    <?php
        $tabParamSiswa = http_build_query(['tipe' => 'siswa', 'view' => $viewMode, 'dari' => $filters['dari'], 'sampai' => $filters['sampai']]);
        $tabParamGuru  = http_build_query(['tipe' => 'guru',  'view' => $viewMode, 'dari' => $filters['dari'], 'sampai' => $filters['sampai']]);
    ?>
    <div class="inline-flex p-1 bg-slate-200/70 dark:bg-slate-800 rounded-xl border border-slate-300/80 dark:border-slate-700">
        <a href="<?= BASEURL ?>/rekapabsensi?<?= $tabParamSiswa ?>"
           class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 <?= $tipe === 'siswa' ? 'bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?>">
            <i class="fa-solid fa-user-graduate" aria-hidden="true"></i>
            <span>Data Siswa</span>
        </a>
        <a href="<?= BASEURL ?>/rekapabsensi?<?= $tabParamGuru ?>"
           class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 <?= $tipe === 'guru' ? 'bg-white dark:bg-slate-900 text-emerald-800 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?>">
            <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
            <span>Data Guru</span>
        </a>
    </div>
    <?php endif; ?>

    <!-- ── 3. MODE KHUSUS PKL SWITCHER (JIKA KELAS BERSTATUS PKL) ── -->
    <?php if ($tipe === 'siswa' && $isKelasPkl): ?>
    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shrink-0 font-bold">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
            </span>
            <div>
                <p class="text-xs font-bold text-slate-800">
                    Mode Rekap Kelas PKL: <span class="text-indigo-700"><?= htmlspecialchars($activeKelasObj->nama_kelas ?? 'PKL') ?></span>
                </p>
                <p class="text-[11px] text-slate-500">Pilih data presensi yang ingin ditinjau untuk kelas ini.</p>
            </div>
        </div>
        <div class="inline-flex p-0.5 bg-slate-200/80 rounded-lg border border-slate-300">
            <a href="<?= $urlKatBiasa ?>"
               class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all <?= $kategori === 'biasa' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?>">
                Presensi Sekolah
            </a>
            <a href="<?= $urlKatPkl ?>"
               class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all <?= $kategori === 'pkl' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?>">
                Presensi Tempat PKL
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── 4. RINGKASAN METRIK KPI (CLEAN COHESIVE BAR) ── -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 divide-x divide-y sm:divide-y-0 divide-slate-100 dark:divide-slate-800">
            <!-- Metrik 1: Hadir Tepat Waktu -->
            <div class="p-3.5 sm:p-4">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span class="font-medium">Hadir</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white tabular-nums"><?= $totHadir ?></div>
                <span class="text-[11px] text-emerald-700 font-semibold">Tepat Waktu</span>
            </div>

            <!-- Metrik 2: Terlambat -->
            <div class="p-3.5 sm:p-4">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span class="font-medium">Terlambat</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white tabular-nums"><?= $totTelat ?></div>
                <span class="text-[11px] text-amber-700 font-semibold">Masuk Dispensasi</span>
            </div>

            <!-- Metrik 3: Izin -->
            <div class="p-3.5 sm:p-4">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span class="font-medium">Izin</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white tabular-nums"><?= $totIzin ?></div>
                <span class="text-[11px] text-blue-700 font-semibold">Disetujui</span>
            </div>

            <!-- Metrik 4: Sakit -->
            <div class="p-3.5 sm:p-4">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span class="font-medium">Sakit</span>
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                </div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white tabular-nums"><?= $totSakit ?></div>
                <span class="text-[11px] text-purple-700 font-semibold">Keterangan Medis</span>
            </div>

            <!-- Metrik 5: Alfa -->
            <div class="p-3.5 sm:p-4">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span class="font-medium">Alfa</span>
                    <span class="w-2 h-2 rounded-full <?= $totAlfa > 0 ? 'bg-rose-500' : 'bg-slate-300' ?>"></span>
                </div>
                <div class="text-xl font-bold font-mono <?= $totAlfa > 0 ? 'text-rose-700' : 'text-slate-900' ?> tabular-nums"><?= $totAlfa ?></div>
                <span class="text-[11px] <?= $totAlfa > 0 ? 'text-rose-600 font-bold' : 'text-slate-500 font-medium' ?>">Tanpa Keterangan</span>
            </div>

            <!-- Metrik 6: Rata-Rata Kehadiran / PKL -->
            <div class="p-3.5 sm:p-4 bg-slate-50/60 dark:bg-slate-800/40">
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                    <span class="font-medium"><?= ($tipe === 'siswa' && $kategori === 'pkl') ? 'Hadir PKL' : 'Persentase' ?></span>
                    <i class="fa-solid fa-chart-pie text-slate-400 text-xs" aria-hidden="true"></i>
                </div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white tabular-nums">
                    <?= ($tipe === 'siswa' && $kategori === 'pkl') ? $totPkl : ($persenRata . '%') ?>
                </div>
                <span class="text-[11px] text-slate-600 font-semibold">
                    <?= ($tipe === 'siswa' && $kategori === 'pkl') ? 'Siswa di Tempat PKL' : 'Rata-rata Periode' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ── 5. FORM FILTER TERPADU (CLEAN ERGONOMIC FILTER BAR) ── -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs">
        <form method="GET" action="<?= BASEURL ?>/rekapabsensi" class="space-y-3.5">
            <input type="hidden" name="tipe" value="<?= htmlspecialchars($tipe) ?>">
            <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
            <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <!-- Dari Tanggal -->
                <div class="lg:col-span-2">
                    <label for="filter_dari" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Dari Tanggal</label>
                    <input type="date" id="filter_dari" name="dari" value="<?= htmlspecialchars($filters['dari']) ?>"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 transition-all outline-none">
                </div>

                <!-- Sampai Tanggal -->
                <div class="lg:col-span-2">
                    <label for="filter_sampai" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Sampai Tanggal</label>
                    <input type="date" id="filter_sampai" name="sampai" value="<?= htmlspecialchars($filters['sampai']) ?>"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 transition-all outline-none">
                </div>

                <!-- Filter Kelas (Khusus Tab Siswa untuk Admin) -->
                <?php if ($tipe === 'siswa' && $role === 'admin'): ?>
                <div class="lg:col-span-3">
                    <label for="filter_kelas" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Kelas</label>
                    <select id="filter_kelas" name="kelas_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 transition-all outline-none">
                        <option value="">Semua Kelas</option>
                        <?php if (!empty($data['kelasList'])): ?>
                            <?php foreach ($data['kelasList'] as $k): ?>
                                <option value="<?= $k->id ?>" <?= ($filters['kelas_id'] == $k->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k->nama_kelas) ?> (<?= htmlspecialchars($k->jurusan ?? '') ?>) <?= (($k->status ?? '') === 'pkl') ? '[PKL]' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Filter Status (Mode Detail) -->
                <?php if ($viewMode === 'detail'): ?>
                <div class="<?= ($tipe === 'siswa' && $role === 'admin') ? 'lg:col-span-2' : 'lg:col-span-3' ?>">
                    <label for="filter_status" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status Kehadiran</label>
                    <select id="filter_status" name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 transition-all outline-none">
                        <option value="">Semua Status</option>
                        <option value="hadir"     <?= ($filters['status'] === 'hadir')     ? 'selected' : '' ?>>Hadir (H)</option>
                        <option value="terlambat" <?= ($filters['status'] === 'terlambat') ? 'selected' : '' ?>>Terlambat (T)</option>
                        <option value="izin"      <?= ($filters['status'] === 'izin')      ? 'selected' : '' ?>>Izin (I)</option>
                        <option value="sakit"     <?= ($filters['status'] === 'sakit')     ? 'selected' : '' ?>>Sakit (S)</option>
                        <option value="alfa"      <?= ($filters['status'] === 'alfa')      ? 'selected' : '' ?>>Alfa (A)</option>
                        <?php if ($tipe === 'siswa'): ?>
                        <option value="pkl"       <?= ($filters['status'] === 'pkl')       ? 'selected' : '' ?>>PKL</option>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Pencarian Nama / NIS / NIP -->
                <div class="<?= ($viewMode === 'detail') ? (($tipe === 'siswa' && $role === 'admin') ? 'lg:col-span-3' : 'lg:col-span-5') : (($tipe === 'siswa' && $role === 'admin') ? 'lg:col-span-5' : 'lg:col-span-8') ?>">
                    <label for="filter_search" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        <?= $tipe === 'guru' ? 'Cari Nama Guru / NIP' : 'Cari Nama Siswa / NIS' ?>
                    </label>
                    <div class="relative">
                        <input type="text" id="filter_search" name="search" value="<?= htmlspecialchars($filters['search']) ?>"
                               placeholder="<?= $tipe === 'guru' ? 'Ketik nama guru atau NIP...' : 'Ketik nama siswa atau NIS...' ?>"
                               class="w-full pl-8 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-900 focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 transition-all outline-none">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs pointer-events-none" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <!-- Baris Kontrol Bawah: Limit Baris, Preset Tanggal, Tombol Aksi -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs">
                <!-- Limit Baris & Tombol Cepat Tanggal -->
                <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                    <label for="filter_limit" class="text-slate-600 font-medium">Baris:</label>
                    <select id="filter_limit" name="limit" onchange="this.form.submit()" class="px-2 py-1 bg-slate-50 border border-slate-300 rounded-md text-xs font-semibold focus:ring-1 focus:ring-emerald-600 outline-none">
                        <option value="15"  <?= ($data['limit'] == 15)  ? 'selected' : '' ?>>15</option>
                        <option value="25"  <?= ($data['limit'] == 25)  ? 'selected' : '' ?>>25</option>
                        <option value="50"  <?= ($data['limit'] == 50)  ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($data['limit'] == 100) ? 'selected' : '' ?>>100</option>
                    </select>

                    <span class="text-slate-300 mx-1">|</span>
                    <button type="button" onclick="setQuickDate('today')" class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">Hari Ini</button>
                    <button type="button" onclick="setQuickDate('week')"  class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">7 Hari</button>
                    <button type="button" onclick="setQuickDate('month')" class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">Bulan Ini</button>
                </div>

                <!-- Tombol Submit & Reset -->
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="<?= BASEURL ?>/rekapabsensi?tipe=<?= $tipe ?>&view=<?= $viewMode ?>" 
                       class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg font-semibold transition-colors">
                        Reset
                    </a>
                    <button type="submit" 
                            class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-bold transition-all shadow-2xs flex items-center gap-1.5">
                        <i class="fa-solid fa-filter text-xs" aria-hidden="true"></i>
                        <span>Terapkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ── 6. VIEW MODE SWITCHER & KETERANGAN STATUS ── -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="inline-flex p-0.5 bg-slate-200/80 rounded-lg border border-slate-300 self-start sm:self-auto">
            <a href="<?= $urlViewRingkasan ?>"
               class="px-3.5 py-1.5 rounded-md font-semibold transition-all flex items-center gap-1.5 <?= $viewMode === 'ringkasan' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?>">
                <i class="fa-solid fa-table text-emerald-700" aria-hidden="true"></i>
                <span>Rekap Akumulasi</span>
            </a>
            <a href="<?= $urlViewDetail ?>"
               class="px-3.5 py-1.5 rounded-md font-semibold transition-all flex items-center gap-1.5 <?= $viewMode === 'detail' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' ?>">
                <i class="fa-solid fa-list-check text-blue-700" aria-hidden="true"></i>
                <span>Log Transaksi</span>
            </a>
        </div>

        <div class="flex items-center gap-2 text-[11px] text-slate-600 flex-wrap">
            <span class="font-bold text-slate-700">Kode:</span>
            <span class="text-emerald-800 font-semibold">H = Hadir</span> &bull;
            <span class="text-amber-800 font-semibold">T = Telat</span> &bull;
            <span class="text-blue-800 font-semibold">I = Izin</span> &bull;
            <span class="text-purple-800 font-semibold">S = Sakit</span> &bull;
            <span class="text-rose-800 font-semibold">A = Alfa</span>
            <?php if ($tipe === 'siswa'): ?>
            &bull; <span class="text-indigo-800 font-semibold">PKL = Praktik Kerja</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── 7. TABEL DATA REKAPITULASI (CLEAN & ACCESSIBLE TABLE) ── -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/90 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <?php if ($tipe === 'siswa' && $viewMode === 'ringkasan' && $kategori === 'pkl'): ?>
            <!-- ──────── TABLE A: REKAP RINGKASAN SISWA PKL ──────── -->
            <table class="w-full min-w-[760px] text-xs text-left text-slate-700">
                <thead class="text-[11px] font-semibold text-slate-600 uppercase bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 tracking-wider text-slate-600 dark:text-slate-300">
                    <tr>
                        <th scope="col" class="px-3.5 py-3 text-center w-10">No</th>
                        <th scope="col" class="px-3.5 py-3">NIS</th>
                        <th scope="col" class="px-3.5 py-3">Nama Siswa</th>
                        <th scope="col" class="px-3.5 py-3">Tempat / Instansi PKL</th>
                        <th scope="col" class="px-3.5 py-3">Pembimbing</th>
                        <th scope="col" class="px-3 py-3 text-center text-emerald-800" title="Datang PKL">Datang</th>
                        <th scope="col" class="px-3 py-3 text-center text-indigo-800" title="Absen Datang dan Pulang Lengkap">Lengkap</th>
                        <th scope="col" class="px-3 py-3 text-center text-blue-800" title="Izin">I</th>
                        <th scope="col" class="px-3 py-3 text-center text-purple-800" title="Sakit">S</th>
                        <th scope="col" class="px-3 py-3 text-center text-rose-800" title="Alfa">A</th>
                        <th scope="col" class="px-3.5 py-3 text-center">Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 dark:divide-slate-800">
                    <?php if (empty($data['dataAbsensi'])): ?>
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-slate-500">
                            <i class="fa-solid fa-briefcase text-3xl mb-2 text-slate-300 block" aria-hidden="true"></i>
                            <p class="font-semibold text-slate-700">Belum ada data presensi PKL pada periode yang dipilih</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = $data['filters']['offset'] + 1;
                        foreach ($data['dataAbsensi'] as $row):
                            $hadir   = (int)($row->pkl_hadir ?? 0);
                            $lengkap = (int)($row->pkl_lengkap ?? 0);
                            $i       = (int)($row->izin ?? 0);
                            $s       = (int)($row->sakit ?? 0);
                            $a       = (int)($row->alfa ?? 0);
                            $totalHari = $hadir + $i + $s + $a;
                            $pct = $totalHari > 0 ? round(($hadir / $totalHari) * 100, 1) : 0;
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="px-3.5 py-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                            <td class="px-3.5 py-3 font-mono text-slate-500 font-medium"><?= htmlspecialchars($row->nis ?? '-') ?></td>
                            <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row->name) ?></td>
                            <td class="px-3.5 py-3 text-slate-700 font-medium">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-building text-slate-400 text-[10px]" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars($row->nama_tempat ?: ($row->nama_kelompok ?? '-')) ?></span>
                                </div>
                            </td>
                            <td class="px-3.5 py-3 text-slate-600">
                                <?= !empty($row->pembimbing_guru_nama) ? htmlspecialchars($row->pembimbing_guru_nama) : '<span class="text-slate-400 italic">Belum diset</span>' ?>
                            </td>
                            <td class="px-3 py-3 text-center font-mono font-bold text-emerald-800 tabular-nums"><?= $hadir ?></td>
                            <td class="px-3 py-3 text-center font-mono font-bold text-indigo-800 tabular-nums"><?= $lengkap ?></td>
                            <td class="px-3 py-3 text-center font-mono tabular-nums <?= $i > 0 ? 'text-blue-700 font-bold' : 'text-slate-400' ?>"><?= $i ?></td>
                            <td class="px-3 py-3 text-center font-mono tabular-nums <?= $s > 0 ? 'text-purple-700 font-bold' : 'text-slate-400' ?>"><?= $s ?></td>
                            <td class="px-3 py-3 text-center font-mono tabular-nums">
                                <?php if ($a > 0): ?>
                                    <span class="inline-flex px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold"><?= $a ?></span>
                                <?php else: ?>
                                    <span class="text-slate-400">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3.5 py-3 text-center font-mono font-bold tabular-nums <?= $pct >= 85 ? 'text-emerald-700' : 'text-amber-700' ?>">
                                <?= $pct ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php elseif ($viewMode === 'ringkasan'): ?>
            <!-- ──────── TABLE B: REKAP RINGKASAN RAPORT (1 BARIS = 1 SISWA/GURU) ──────── -->
            <table class="w-full min-w-[760px] text-xs text-left text-slate-700">
                <thead class="text-[11px] font-semibold text-slate-600 uppercase bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 tracking-wider text-slate-600 dark:text-slate-300">
                    <tr>
                        <th scope="col" class="px-3.5 py-3 text-center w-10">No</th>
                        <?php if ($tipe === 'guru'): ?>
                            <th scope="col" class="px-3.5 py-3">NIP</th>
                            <th scope="col" class="px-3.5 py-3">Nama Guru</th>
                        <?php else: ?>
                            <th scope="col" class="px-3.5 py-3">NIS</th>
                            <th scope="col" class="px-3.5 py-3">Nama Siswa</th>
                            <th scope="col" class="px-3.5 py-3">Kelas</th>
                        <?php endif; ?>
                        <th scope="col" class="px-3 py-3 text-center text-emerald-800" title="Hadir Tepat Waktu">H</th>
                        <th scope="col" class="px-3 py-3 text-center text-amber-800" title="Terlambat">T</th>
                        <th scope="col" class="px-3 py-3 text-center text-blue-800" title="Izin">I</th>
                        <th scope="col" class="px-3 py-3 text-center text-purple-800" title="Sakit">S</th>
                        <th scope="col" class="px-3 py-3 text-center text-rose-800" title="Alfa">A</th>
                        <?php if ($tipe === 'siswa'): ?>
                        <th scope="col" class="px-3 py-3 text-center text-indigo-800" title="PKL">PKL</th>
                        <?php endif; ?>
                        <th scope="col" class="px-3.5 py-3 text-center">Masuk / Total</th>
                        <th scope="col" class="px-3.5 py-3 text-center">Kehadiran</th>
                        <th scope="col" class="px-3.5 py-3 text-center">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 dark:divide-slate-800">
                    <?php if (empty($data['dataAbsensi'])): ?>
                    <tr>
                        <td colspan="13" class="px-4 py-12 text-center text-slate-500">
                            <i class="fa-solid fa-user-xmark text-3xl mb-2 text-slate-300 block" aria-hidden="true"></i>
                            <p class="font-semibold text-slate-700">Tidak ada data yang sesuai dengan filter pencarian</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = $data['filters']['offset'] + 1;
                        foreach ($data['dataAbsensi'] as $row):
                            $h = (int)($row->hadir ?? 0);
                            $t = (int)($row->terlambat ?? 0);
                            $i = (int)($row->izin ?? 0);
                            $s = (int)($row->sakit ?? 0);
                            $a = (int)($row->alfa ?? 0);
                            $pkl = (int)($row->pkl ?? 0);

                            $totalMasuk = $h + $t + $pkl;
                            $totalHari  = $totalMasuk + $i + $s + $a;
                            $pct = $totalHari > 0 ? round(($totalMasuk / $totalHari) * 100, 1) : 0;

                            // Predikat Raport
                            $predikat = 'Sangat Baik';
                            $predColor = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                            if ($a >= 3 || $pct < 75) {
                                $predikat = 'Perlu Perhatian';
                                $predColor = 'bg-rose-50 text-rose-800 border-rose-200';
                            } elseif ($a > 0 || $pct < 85) {
                                $predikat = 'Cukup';
                                $predColor = 'bg-amber-50 text-amber-800 border-amber-200';
                            } elseif ($pct < 95) {
                                $predikat = 'Baik';
                                $predColor = 'bg-blue-50 text-blue-800 border-blue-200';
                            }
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="px-3.5 py-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                            <?php if ($tipe === 'guru'): ?>
                                <td class="px-3.5 py-3 font-mono text-slate-500 font-medium"><?= htmlspecialchars($row->nip ?? '-') ?></td>
                                <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row->name) ?></td>
                            <?php else: ?>
                                <td class="px-3.5 py-3 font-mono text-slate-500 font-medium"><?= htmlspecialchars($row->nis ?? '-') ?></td>
                                <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row->name) ?></td>
                                <td class="px-3.5 py-3 text-slate-600 font-medium whitespace-nowrap">
                                    <span class="inline-flex px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200/80 text-[11px]">
                                        <?= htmlspecialchars($row->nama_kelas ?? '-') ?>
                                    </span>
                                </td>
                            <?php endif; ?>

                            <!-- Hadir -->
                            <td class="px-3 py-3 text-center font-mono font-bold text-emerald-800 tabular-nums"><?= $h ?></td>
                            <!-- Terlambat -->
                            <td class="px-3 py-3 text-center font-mono tabular-nums <?= $t > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' ?>"><?= $t ?></td>
                            <!-- Izin -->
                            <td class="px-3 py-3 text-center font-mono tabular-nums <?= $i > 0 ? 'text-blue-700 font-bold' : 'text-slate-400' ?>"><?= $i ?></td>
                            <!-- Sakit -->
                            <td class="px-3 py-3 text-center font-mono tabular-nums <?= $s > 0 ? 'text-purple-700 font-bold' : 'text-slate-400' ?>"><?= $s ?></td>
                            <!-- Alfa -->
                            <td class="px-3 py-3 text-center font-mono tabular-nums">
                                <?php if ($a > 0): ?>
                                    <span class="inline-flex px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold"><?= $a ?></span>
                                <?php else: ?>
                                    <span class="text-slate-400">0</span>
                                <?php endif; ?>
                            </td>
                            <!-- PKL -->
                            <?php if ($tipe === 'siswa'): ?>
                            <td class="px-3 py-3 text-center font-mono tabular-nums <?= $pkl > 0 ? 'text-indigo-700 font-bold' : 'text-slate-400' ?>"><?= $pkl ?></td>
                            <?php endif; ?>

                            <!-- Total Masuk -->
                            <td class="px-3.5 py-3 text-center font-mono font-semibold text-slate-700 tabular-nums">
                                <?= $totalMasuk ?> <span class="text-[10px] text-slate-400 font-normal">/ <?= $totalHari ?></span>
                            </td>

                            <!-- Persentase Kehadiran -->
                            <td class="px-3.5 py-3 text-center font-mono font-bold tabular-nums <?= $pct >= 85 ? 'text-emerald-700' : ($pct >= 75 ? 'text-amber-700' : 'text-rose-700') ?>">
                                <?= $pct ?>%
                            </td>

                            <!-- Predikat Raport -->
                            <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold border <?= $predColor ?>">
                                    <?= $predikat ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php elseif ($tipe === 'siswa' && $kategori === 'pkl'): ?>
            <!-- ──────── TABLE C: LOG TRANSAKSI HARIAN SISWA PKL ──────── -->
            <table class="w-full min-w-[760px] text-xs text-left text-slate-700">
                <thead class="text-[11px] font-semibold text-slate-600 uppercase bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 tracking-wider text-slate-600 dark:text-slate-300">
                    <tr>
                        <th scope="col" class="px-3.5 py-3 text-center w-10">No</th>
                        <th scope="col" class="px-3.5 py-3 whitespace-nowrap">Tanggal</th>
                        <th scope="col" class="px-3.5 py-3">NIS</th>
                        <th scope="col" class="px-3.5 py-3">Nama Siswa</th>
                        <th scope="col" class="px-3.5 py-3">Tempat PKL</th>
                        <th scope="col" class="px-3 py-3 text-center">Datang</th>
                        <th scope="col" class="px-3 py-3 text-center">Pulang</th>
                        <th scope="col" class="px-3 py-3 text-center">GPS</th>
                        <th scope="col" class="px-3.5 py-3">Pembimbing</th>
                        <th scope="col" class="px-3.5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 dark:divide-slate-800">
                    <?php if (empty($data['dataAbsensi'])): ?>
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-slate-500">
                            <i class="fa-solid fa-map-location-dot text-3xl mb-2 text-slate-300 block" aria-hidden="true"></i>
                            <p class="font-semibold text-slate-700">Belum ada log transaksi kehadiran PKL pada periode ini</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = $data['filters']['offset'] + 1;
                        foreach ($data['dataAbsensi'] as $row):
                            $isComplete = !empty($row->jam_pulang);
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="px-3.5 py-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                            <td class="px-3.5 py-3 font-semibold text-slate-700 whitespace-nowrap">
                                <?= date('d M Y', strtotime($row->tanggal)) ?>
                            </td>
                            <td class="px-3.5 py-3 font-mono text-slate-500 font-medium"><?= htmlspecialchars($row->nis ?? '-') ?></td>
                            <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row->name) ?></td>
                            <td class="px-3.5 py-3 text-slate-700">
                                <span class="font-medium text-slate-800"><?= htmlspecialchars($row->nama_tempat ?: ($row->nama_kelompok ?? '-')) ?></span>
                            </td>
                            <td class="px-3 py-3 text-center whitespace-nowrap font-mono">
                                <?= !empty($row->jam_datang) ? substr($row->jam_datang, 0, 5) : '<span class="text-slate-300">&mdash;</span>' ?>
                            </td>
                            <td class="px-3 py-3 text-center whitespace-nowrap font-mono">
                                <?= !empty($row->jam_pulang) ? substr($row->jam_pulang, 0, 5) : '<span class="text-slate-300">&mdash;</span>' ?>
                            </td>
                            <td class="px-3 py-3 text-center font-mono">
                                <?= $row->jarak_meter !== null ? round($row->jarak_meter) . 'm' : '<span class="text-slate-300">&mdash;</span>' ?>
                            </td>
                            <td class="px-3.5 py-3 text-slate-600">
                                <?= !empty($row->pembimbing_guru_nama) ? htmlspecialchars($row->pembimbing_guru_nama) : '<span class="text-slate-400 italic">Belum diset</span>' ?>
                            </td>
                            <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                <?php if ($isComplete): ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Sedang PKL
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php else: ?>
            <!-- ──────── TABLE D: LOG TRANSAKSI HARIAN REGULER ──────── -->
            <table class="w-full min-w-[760px] text-xs text-left text-slate-700">
                <thead class="text-[11px] font-semibold text-slate-600 uppercase bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700 tracking-wider text-slate-600 dark:text-slate-300">
                    <tr>
                        <th scope="col" class="px-3.5 py-3 text-center w-10">No</th>
                        <th scope="col" class="px-3.5 py-3 whitespace-nowrap">Tanggal</th>
                        <?php if ($tipe === 'guru'): ?>
                            <th scope="col" class="px-3.5 py-3">NIP</th>
                            <th scope="col" class="px-3.5 py-3">Nama Guru</th>
                        <?php else: ?>
                            <th scope="col" class="px-3.5 py-3">NIS</th>
                            <th scope="col" class="px-3.5 py-3">Nama Siswa</th>
                            <th scope="col" class="px-3.5 py-3">Kelas</th>
                        <?php endif; ?>
                        <th scope="col" class="px-3 py-3 text-center">Datang</th>
                        <th scope="col" class="px-3 py-3 text-center">Pulang</th>
                        <th scope="col" class="px-3 py-3 text-center">Status</th>
                        <th scope="col" class="px-3.5 py-3">Keterangan</th>
                        <th scope="col" class="px-3.5 py-3 text-center whitespace-nowrap">Akumulasi (H/T/I/S/A)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 dark:divide-slate-800">
                    <?php if (empty($data['dataAbsensi'])): ?>
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-slate-500">
                            <i class="fa-solid fa-clipboard-question text-3xl mb-2 text-slate-300 block" aria-hidden="true"></i>
                            <p class="font-semibold text-slate-700">Tidak ada log absensi yang sesuai filter</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = $data['filters']['offset'] + 1;
                        foreach ($data['dataAbsensi'] as $row):
                            $stKey = strtolower($row->status ?? 'alfa');
                            $st    = $statusMap[$stKey] ?? ['singkat' => strtoupper($stKey), 'label' => ucfirst($stKey), 'badge' => 'bg-slate-100 text-slate-700 border-slate-200'];

                            $personId = ($tipe === 'guru') ? ($row->guru_id ?? null) : ($row->siswa_id ?? null);
                            $ak = $akumulasiMap[$personId] ?? ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'pkl' => 0];
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <td class="px-3.5 py-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                            <td class="px-3.5 py-3 font-semibold text-slate-700 whitespace-nowrap">
                                <?= date('d M Y', strtotime($row->tanggal)) ?>
                            </td>

                            <?php if ($tipe === 'guru'): ?>
                                <td class="px-3.5 py-3 font-mono text-slate-500 font-medium"><?= htmlspecialchars($row->nip ?? '-') ?></td>
                                <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row->name) ?></td>
                            <?php else: ?>
                                <td class="px-3.5 py-3 font-mono text-slate-500 font-medium"><?= htmlspecialchars($row->nis ?? '-') ?></td>
                                <td class="px-3.5 py-3 font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row->name) ?></td>
                                <td class="px-3.5 py-3 text-slate-600 font-medium whitespace-nowrap">
                                    <span class="inline-flex px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200/80 text-[11px]">
                                        <?= htmlspecialchars($row->nama_kelas ?? '-') ?>
                                    </span>
                                </td>
                            <?php endif; ?>

                            <!-- Datang -->
                            <td class="px-3 py-3 text-center font-mono whitespace-nowrap">
                                <?= !empty($row->jam_datang) ? substr($row->jam_datang, 0, 5) : '<span class="text-slate-300">&mdash;</span>' ?>
                            </td>

                            <!-- Pulang -->
                            <td class="px-3 py-3 text-center font-mono whitespace-nowrap">
                                <?= !empty($row->jam_pulang) ? substr($row->jam_pulang, 0, 5) : '<span class="text-slate-300">&mdash;</span>' ?>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-3 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold border <?= $st['badge'] ?>">
                                    <?= $st['label'] ?>
                                </span>
                            </td>

                            <!-- Keterangan -->
                            <td class="px-3.5 py-3 text-slate-600 max-w-[180px] truncate" title="<?= htmlspecialchars($row->keterangan ?? '') ?>">
                                <?= !empty($row->keterangan) ? htmlspecialchars($row->keterangan) : '<span class="text-slate-300">&mdash;</span>' ?>
                            </td>

                            <!-- Akumulasi -->
                            <td class="px-3.5 py-3 whitespace-nowrap text-center">
                                <span class="font-mono text-[11px] font-semibold text-slate-600">
                                    <?= $ak['hadir'] ?>H / <?= $ak['terlambat'] ?>T / <?= $ak['izin'] ?>I / <?= $ak['sakit'] ?>S / <?= $ak['alfa'] ?>A
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- ── 8. PAGINASI & RINGKASAN DATA (FOOTER TABEL) ── -->
        <?php if ($data['totalData'] > 0): ?>
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
            <p>
                Menampilkan <strong class="text-slate-900"><?= count($data['dataAbsensi']) ?></strong> dari total <strong class="text-slate-900"><?= $data['totalData'] ?></strong> data
            </p>

            <?php if ($data['totalPages'] > 1): ?>
            <nav class="flex items-center gap-1 flex-wrap" aria-label="Navigasi Halaman">
                <?php 
                $baseQuery = $filters;
                unset($baseQuery['limit'], $baseQuery['offset']);
                $queryBaseStr = '&' . http_build_query($baseQuery);
                if (!empty($data['limit'])) $queryBaseStr .= '&limit=' . $data['limit'];
                if (!empty($tipe))          $queryBaseStr .= '&tipe=' . $tipe;
                if (!empty($viewMode))      $queryBaseStr .= '&view=' . $viewMode;
                if (!empty($kategori))      $queryBaseStr .= '&kategori=' . $kategori;

                $curPage  = $data['page'];
                $totPages = $data['totalPages'];
                $startP   = max(1, $curPage - 2);
                $endP     = min($totPages, $curPage + 2);
                ?>

                <!-- Prev -->
                <?php if ($curPage > 1): ?>
                <a href="<?= BASEURL ?>/rekapabsensi?page=<?= $curPage - 1 ?><?= $queryBaseStr ?>"
                   class="px-2.5 py-1 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold"
                   aria-label="Halaman Sebelumnya">
                    &laquo;
                </a>
                <?php endif; ?>

                <?php for($p = $startP; $p <= $endP; $p++): ?>
                <a href="<?= BASEURL ?>/rekapabsensi?page=<?= $p ?><?= $queryBaseStr ?>"
                   class="px-2.5 py-1 rounded font-bold transition-all <?= $p == $curPage ? 'bg-slate-800 text-white' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100' ?>"
                   aria-label="Halaman <?= $p ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>

                <!-- Next -->
                <?php if ($curPage < $totPages): ?>
                <a href="<?= BASEURL ?>/rekapabsensi?page=<?= $curPage + 1 ?><?= $queryBaseStr ?>"
                   class="px-2.5 py-1 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold"
                   aria-label="Halaman Berikutnya">
                    &raquo;
                </a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Script Pembantu Cepat Tanggal -->
<script>
function setQuickDate(range) {
    const dariInput = document.getElementById('filter_dari');
    const sampaiInput = document.getElementById('filter_sampai');
    if (!dariInput || !sampaiInput) return;

    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    const fmt = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

    sampaiInput.value = fmt(now);

    if (range === 'today') {
        dariInput.value = fmt(now);
    } else if (range === 'week') {
        const past = new Date(now.getTime() - 6 * 24 * 60 * 60 * 1000);
        dariInput.value = fmt(past);
    } else if (range === 'month') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        dariInput.value = fmt(firstDay);
    }
}
</script>
