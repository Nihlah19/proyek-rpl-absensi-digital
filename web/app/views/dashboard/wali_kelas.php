<?php $this->view('templates/header', $data); ?>

<?php
$kelasNama   = !empty($data['kelas']) ? htmlspecialchars($data['kelas']->nama_kelas) : 'Belum Ditugaskan';
$guruNama    = htmlspecialchars($data['guru']->nama ?? SessionHelper::get('name') ?? 'Wali Kelas');
$absensiWk   = $data['absensiHariIni'] ?? null;
$statsSiswa  = $data['statsSiswa'] ?? null;

$ts   = (int)($statsSiswa->total_siswa ?? 0);
$sh   = (int)($statsSiswa->hadir ?? 0);
$st   = (int)($statsSiswa->terlambat ?? 0);
$si   = (int)($statsSiswa->izin ?? 0);
$ss   = (int)($statsSiswa->sakit ?? 0);
$sa   = (int)($statsSiswa->alfa ?? 0);
$spkl = (int)($statsSiswa->pkl ?? 0);

$siswaGiat     = $sh + $st + $spkl;
$pctSiswaGiat  = $ts > 0 ? round(($siswaGiat / $ts) * 100, 1) : 0;
$pctSiswaTelat = $ts > 0 ? round(($st / $ts) * 100, 1) : 0;
$pctSiswaTepat = $ts > 0 ? round(($sh / $ts) * 100, 1) : 0;
$siswaIzinSakit = $si + $ss;
$pctSiswaIzin  = $ts > 0 ? round(($siswaIzinSakit / $ts) * 100, 1) : 0;
$pctSiswaAlfa  = $ts > 0 ? round(($sa / $ts) * 100, 1) : 0;
$isSesiPagiWk  = ($siswaGiat === 0 && $siswaIzinSakit === 0);
?>

<div id="pageContent" class="w-full space-y-6">
    <!-- ── PAGE HEADER (CLEAN & HUMBLE) ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-200/80 dark:border-slate-800">
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                    <i class="fa-solid fa-graduation-cap text-[10px]"></i> Kelas <?= $kelasNama ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Dashboard Wali Kelas
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Wali Kelas: <strong class="text-slate-700 dark:text-slate-300 font-medium"><?= $guruNama ?></strong> &middot; Monitoring presensi rombel kelas
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-center">
            <button type="button" onclick="openScanModal()" class="btn-primary text-xs cursor-pointer">
                <i class="fa-solid fa-qrcode"></i> Scan Absensi
            </button>
            <a href="<?= BASEURL; ?>/rekapabsensi" class="btn-secondary text-xs">
                <i class="fa-solid fa-table-list text-slate-400"></i> Rekap Absensi
            </a>
        </div>
    </div>

    <?php if (!empty($data['kelas']) && ($data['kelas']->status ?? '') === 'pkl'): ?>
    <!-- ── BANNER KELAS PKL AKTIF ── -->
    <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/70 dark:border-indigo-900/60 rounded-xl p-4 flex items-center justify-between gap-4 flex-wrap shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <div>
                <p class="font-semibold text-indigo-950 dark:text-indigo-200 text-sm">Kelas <?= $kelasNama ?> Berstatus PKL (Praktik Kerja Lapangan)</p>
                <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-0.5">Kelola penempatan kelompok siswa dan pantau presensi harian di instansi mitra.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= BASEURL ?>/pkl/monitoring" class="btn-secondary text-xs">
                <i class="fa-solid fa-camera text-indigo-600 dark:text-indigo-400"></i> Monitoring Presensi
            </a>
            <a href="<?= BASEURL ?>/pkl/kelompok" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-layer-group"></i> Kelola Kelompok
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── NOTIFIKASI PULANG CEPAT BANNER ── -->
    <div id="bannerPulangCepat" class="hidden bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900/60 rounded-xl p-4 flex items-center justify-between gap-4 flex-wrap shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-person-walking-arrow-right"></i>
            </div>
            <div>
                <p class="font-semibold text-amber-900 dark:text-amber-200 text-sm">Permohonan Pulang Cepat Menunggu Konfirmasi</p>
                <p class="text-amber-700 dark:text-amber-300 text-xs mt-0.5" id="bannerPulangCepatDetail">Memuat rincian permohonan...</p>
            </div>
        </div>
        <a href="<?= BASEURL ?>/pulang_cepat" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 shrink-0 shadow-xs">
            <i class="fa-solid fa-clipboard-check"></i> Proses Sekarang
        </a>
    </div>

    <?php Flasher::flash(); ?>

    <?php if (!empty($data['isPembimbingPkl']) && !empty($data['pklDashboard'])): ?>
        <!-- ── PANEL GURU PEMBIMBING PKL (PENGATURAN & LOG RIWAYAT ABSENSI PKL) ── -->
        <?php $this->view('dashboard/panel_pembimbing_pkl', ['pkl' => $data['pklDashboard']]); ?>
    <?php endif; ?>

    <!-- ── 5 METRIC STAT CARDS ROW (PROFESSIONAL EXECUTIVE DESIGN) ── -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- 1. Total Siswa -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:shadow-sm transition-shadow">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Siswa</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm border border-slate-200 dark:border-slate-700">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums"><?= $ts ?></span>
                    <span class="text-xs text-slate-400 font-medium">siswa</span>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        Rombel Kelas
                    </span>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Kelas</span>
                <span class="text-slate-800 dark:text-slate-200 font-semibold font-mono text-[11px]"><?= $kelasNama ?></span>
            </div>
        </div>

        <!-- 2. Hadir Tepat Waktu -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-emerald-300 dark:hover:border-emerald-800/80 transition-colors">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Hadir Tepat</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-sm border border-emerald-200/50 dark:border-emerald-800/40">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums"><?= $sh ?></span>
                    <span class="text-xs text-slate-400 font-medium">siswa</span>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                        <i class="fa-solid fa-arrow-trend-up text-[9px]"></i> <?= $pctSiswaTepat ?>%
                    </span>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Total Masuk</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400 font-mono text-[11px]"><?= $siswaGiat ?> Hadir</span>
            </div>
        </div>

        <!-- 3. Terlambat -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-amber-300 dark:hover:border-amber-800/80 transition-colors">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Terlambat</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-sm border border-amber-200/50 dark:border-amber-800/40">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-bold <?= $st > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' ?> font-mono tracking-tight tabular-nums"><?= $st ?></span>
                    <span class="text-xs text-slate-400 font-medium">siswa</span>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold <?= $st > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' ?>">
                        <?= $pctSiswaTelat ?>% rasio
                    </span>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Toleransi</span>
                <span class="font-semibold <?= $st > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500' ?> text-[11px]">
                    <?= $st > 0 ? 'Lewat Batas' : 'Nihil' ?>
                </span>
            </div>
        </div>

        <!-- 4. Izin / Sakit -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-sky-300 dark:hover:border-sky-800/80 transition-colors">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Izin / Sakit</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 text-sm border border-sky-200/50 dark:border-sky-800/40">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-bold <?= $siswaIzinSakit > 0 ? 'text-sky-600 dark:text-sky-400' : 'text-slate-900 dark:text-white' ?> font-mono tracking-tight tabular-nums"><?= $siswaIzinSakit ?></span>
                    <span class="text-xs text-slate-400 font-medium">siswa</span>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold <?= $siswaIzinSakit > 0 ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-200/50 dark:border-sky-800/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' ?>">
                        <?= $si ?> Izin &middot; <?= $ss ?> Sakit
                    </span>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Rincian</span>
                <span class="text-slate-700 dark:text-slate-300 font-semibold font-mono text-[11px]"><?= $pctSiswaIzin ?>% dispensasi</span>
            </div>
        </div>

        <!-- 5. Belum Presensi / Alfa / PKL -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-colors col-span-2 sm:col-span-1">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Presensi</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center <?= $isSesiPagiWk ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' : ($sa > 0 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 border border-emerald-200/50') ?> text-sm">
                        <i class="fa-solid <?= $isSesiPagiWk ? 'fa-user-clock' : ($sa > 0 ? 'fa-user-xmark' : 'fa-check') ?>"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-bold <?= $isSesiPagiWk ? 'text-slate-700 dark:text-slate-300' : ($sa > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') ?> font-mono tracking-tight tabular-nums"><?= $sa ?></span>
                    <span class="text-xs text-slate-400 font-medium">siswa</span>
                </div>
                <div class="mt-2">
                    <?php if ($isSesiPagiWk): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            Sesi Pagi
                        </span>
                    <?php elseif ($spkl > 0): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/40">
                            <?= $spkl ?> PKL &middot; <?= $sa ?> Alfa
                        </span>
                    <?php elseif ($sa > 0): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40">
                            <?= $pctSiswaAlfa ?>% belum hadir
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                            Lengkap 100%
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Status</span>
                <span class="font-semibold text-[11px] <?= $isSesiPagiWk ? 'text-slate-500 dark:text-slate-400' : ($sa > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') ?>">
                    <?= $isSesiPagiWk ? 'Menunggu Tapping' : ($spkl > 0 ? "$spkl PKL Aktif" : ($sa > 0 ? 'Perlu Cek' : 'Lengkap')) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ── MIDDLE SECTION: 2 COLUMNS (7 / 5) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- ── LEFT COLUMN (7 COLS): GRAFIK & UNDUH REKAP ── -->
        <div class="lg:col-span-7 space-y-6">
            <!-- 1. Grafik Distribusi Kehadiran Kelas -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-chart-column"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Analisis Kehadiran Siswa Hari Ini</h2>
                    </div>
                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400"><?= $data['tanggal'] ?></span>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Progress Summary Boxes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-lg border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Tingkat Kehadiran</span>
                                <p class="text-xs text-slate-400 mt-0.5"><?= $siswaGiat ?> dari <?= $ts ?> siswa aktif</p>
                            </div>
                            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums"><?= $pctSiswaGiat ?>%</span>
                        </div>
                        <div class="p-3.5 rounded-lg border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Rasio Terlambat</span>
                                <p class="text-xs text-slate-400 mt-0.5"><?= $st ?> siswa datang terlambat</p>
                            </div>
                            <span class="text-xl font-bold text-amber-600 dark:text-amber-400 font-mono tabular-nums"><?= $pctSiswaTelat ?>%</span>
                        </div>
                    </div>

                    <!-- Canvas Bar Chart -->
                    <div class="relative w-full h-56 pt-2">
                        <canvas id="wkChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- 2. Unduh Rekap Presensi Kelas (CSV) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-file-csv"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Ekspor Rekap Kehadiran Kelas (CSV)</h2>
                    </div>
                </div>
                <div class="p-5">
                    <?php if ($data['kelas']): ?>
                        <div class="flex flex-col sm:flex-row gap-3 items-end">
                            <div class="flex-1 w-full">
                                <label for="wk-dari" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Dari Tanggal</label>
                                <input type="date" id="wk-dari" value="<?= date('Y-m-01') ?>"
                                       class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div class="flex-1 w-full">
                                <label for="wk-sampai" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Sampai Tanggal</label>
                                <input type="date" id="wk-sampai" value="<?= date('Y-m-d') ?>"
                                       class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </div>
                            <div class="shrink-0 w-full sm:w-auto">
                                <button type="button" onclick="downloadWaliKelas()"
                                        class="btn-primary w-full sm:w-auto flex items-center justify-center gap-2 cursor-pointer text-xs">
                                    <i class="fa-solid fa-download"></i>
                                    <span>Unduh File CSV</span>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <i class="fa-solid fa-circle-exclamation text-2xl text-slate-300 dark:text-slate-600 mb-1.5 block"></i>
                            Anda belum ditugaskan ke kelas manapun.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── RIGHT COLUMN (5 COLS): PRESENSI PRIBADI & SISWA SERING TERLAMBAT ── -->
        <div class="lg:col-span-5 space-y-6">
            <!-- 1. Presensi Pribadi Wali Kelas -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-user-clock"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Presensi Pribadi</h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400"><?= $guruNama ?></p>
                        </div>
                    </div>
                    <button type="button" onclick="openScanModal()" class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:text-emerald-800 transition-colors flex items-center gap-1.5 cursor-pointer bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-md border border-emerald-200/60 dark:border-emerald-800/40">
                        <i class="fa-solid fa-qrcode text-emerald-600 dark:text-emerald-400"></i> Scan QR
                    </button>
                </div>
                <div class="p-5">
                    <?php if (!empty($absensiWk)): ?>
                        <?php $statusWk = strtolower($absensiWk->status ?? ''); ?>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Status Kehadiran:</span>
                                <?= BadgeHelper::render($absensiWk->status, strtoupper($absensiWk->status)) ?>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mb-0.5">Jam Masuk</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white font-mono tabular-nums">
                                        <?= $absensiWk->jam_datang ? date('H:i', strtotime($absensiWk->jam_datang)) . ' <span class="text-[10px] text-slate-400 font-sans font-normal">WIB</span>' : '<span class="text-slate-400">-</span>' ?>
                                    </p>
                                </div>
                                <div class="p-3 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium uppercase tracking-wider mb-0.5">Jam Pulang</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white font-mono tabular-nums">
                                        <?= $absensiWk->jam_pulang ? date('H:i', strtotime($absensiWk->jam_pulang)) . ' <span class="text-[10px] text-slate-400 font-sans font-normal">WIB</span>' : '<span class="text-slate-400">-</span>' ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (!$absensiWk->jam_pulang && in_array($statusWk, ['hadir', 'terlambat'])): ?>
                                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/40 rounded-lg flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2 text-xs text-amber-800 dark:text-amber-300 font-medium">
                                        <i class="fa-solid fa-circle-exclamation text-amber-600 dark:text-amber-400"></i>
                                        <span>Belum scan pulang hari ini</span>
                                    </div>
                                    <button type="button" onclick="openScanModal()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-semibold transition-colors shadow-xs">
                                        Scan Pulang
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-slate-50/70 dark:bg-slate-900/50 rounded-xl border border-slate-200/80 dark:border-slate-800 text-center space-y-2">
                            <i class="fa-solid fa-id-card text-emerald-600 dark:text-emerald-400 text-xl"></i>
                            <div>
                                <h3 class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white">Belum Presensi Hari Ini</h3>
                            </div>
                            <button type="button" onclick="openScanModal()" class="btn-primary text-xs py-1.5 px-3 inline-flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-qrcode"></i> Presensi Sekarang
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. Siswa Sering Terlambat -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Siswa Sering Terlambat</h2>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400">Bulan Ini</span>
                </div>
                <div class="p-5">
                    <?php if (!empty($data['siswaTerlambat'])): ?>
                        <div class="space-y-2.5">
                            <?php foreach ($data['siswaTerlambat'] as $index => $siswa): ?>
                                <div class="flex items-center justify-between p-2.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-950/60 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xs font-semibold font-mono">
                                            <?= $index + 1 ?>
                                        </span>
                                        <div>
                                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[160px]"><?= htmlspecialchars($siswa->name) ?></p>
                                            <p class="text-[11px] text-slate-400"><?= $siswa->jumlah_terlambat ?>x keterlambatan</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold font-mono bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/40">
                                        <?= $siswa->jumlah_terlambat ?>x
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-slate-400">
                            <i class="fa-solid fa-circle-check text-xl text-emerald-600 dark:text-emerald-400 mb-1.5 block"></i>
                            <p class="text-xs">Tidak ada siswa yang sering terlambat bulan ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── BOTTOM SECTION: TABEL PRESENSI SISWA KELAS HARI INI ── -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span>Daftar Presensi Siswa Hari Ini</span>
                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400">&mdash; Kelas <?= $kelasNama ?> (<?= count($data['absensiSiswaHariIni']) ?> Siswa)</span>
                </h3>
            </div>

            <a href="<?= BASEURL ?>/rekapabsensi" class="btn-secondary self-start sm:self-auto text-xs">
                <i class="fa-solid fa-table-list text-slate-400"></i> Rekap Absensi Kelas <i class="fa-solid fa-arrow-right text-[10px] ml-0.5 text-slate-400"></i>
            </a>
        </div>

        <!-- Filter Cepat -->
        <div class="px-5 py-3 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
            <div class="w-full sm:w-48">
                <select id="selectWkStatus" onchange="filterWaliKelasDash()"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">-- Semua Keterangan --</option>
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alfa">Alfa</option>
                    <option value="pkl">PKL</option>
                </select>
            </div>

            <div class="w-full sm:w-72">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" id="inputWkSearch" onkeyup="filterWaliKelasDash()"
                           placeholder="Cari nama atau NIS siswa..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
        </div>

        <!-- Tabel Siswa -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-center w-12 font-mono">No</th>
                        <th class="px-4 py-3 font-mono">NIS</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3 text-center">Datang</th>
                        <th class="px-4 py-3 text-center">Pulang</th>
                        <th class="px-4 py-3 text-center">Keterangan</th>
                        <th class="px-4 py-3 text-center">Status Waktu</th>
                        <th class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody id="tbodyWkSiswa" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($data['absensiSiswaHariIni'])): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-400 font-medium">
                                <i class="fa-solid fa-inbox text-2xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                <?= $data['kelas'] ? 'Belum ada data presensi siswa hari ini.' : 'Anda belum ditugaskan ke kelas manapun.' ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $noWk = 1;
                        foreach ($data['absensiSiswaHariIni'] as $row): 
                            $st = strtolower($row->status ?? 'alfa');
                            $ketKey = ($st === 'terlambat') ? 'hadir' : $st;
                        ?>
                        <tr class="row-wk-dash hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors h-11"
                            data-nama="<?= htmlspecialchars(strtolower($row->name)) ?>"
                            data-nis="<?= htmlspecialchars(strtolower($row->nis ?? '')) ?>"
                            data-status="<?= htmlspecialchars($ketKey) ?>">
                            <td class="px-4 py-2 text-center text-slate-400 font-mono tabular-nums"><?= $noWk++ ?></td>
                            <td class="px-4 py-2 font-mono text-slate-500 dark:text-slate-400 tabular-nums"><?= htmlspecialchars($row->nis ?? '-') ?></td>
                            <td class="px-4 py-2 font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                                    <?= strtoupper(substr($row->name, 0, 1)) ?>
                                </div>
                                <span class="truncate"><?= htmlspecialchars($row->name) ?></span>
                            </td>
                            <td class="px-4 py-2 text-center font-mono font-medium text-emerald-600 dark:text-emerald-400 tabular-nums">
                                <?= $row->jam_datang ? substr($row->jam_datang, 0, 5) : '&mdash;' ?>
                            </td>
                            <td class="px-4 py-2 text-center font-mono font-medium text-slate-700 dark:text-slate-300 tabular-nums">
                                <?= $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '&mdash;' ?>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <?= BadgeHelper::render($ketKey, strtoupper($ketKey)) ?>
                            </td>
                            <td class="px-4 py-2 text-center whitespace-nowrap">
                                <?php if ($st === 'hadir'): ?>
                                    <?= BadgeHelper::render('hadir', 'Tepat Waktu') ?>
                                <?php elseif ($st === 'terlambat'): ?>
                                    <?= BadgeHelper::render('terlambat', 'Terlambat') ?>
                                <?php else: ?>
                                    <span class="text-slate-400">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-slate-500 dark:text-slate-400 max-w-[180px] truncate" title="<?= htmlspecialchars($row->keterangan ?? '') ?>">
                                <?= htmlspecialchars($row->keterangan ?? '-') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── JAVASCRIPT CHARTS & FILTER ── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('wkChart')?.getContext('2d');
    if (!ctx) return;
    
    let wkChartInstance = null;
    function createWkChart(isDark) {
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? '#334155' : '#e2e8f0';

        if (wkChartInstance) wkChartInstance.destroy();

        wkChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Hadir', 'Telat', 'Izin', 'Sakit', 'Alfa', 'PKL'],
                datasets: [{
                    label: 'Siswa',
                    data: <?= json_encode([(int)$sh, (int)$st, (int)$si, (int)$ss, (int)$sa, (int)$spkl]) ?>,
                    backgroundColor: [
                        '#059669', // Hadir
                        '#d97706', // Telat
                        '#0284c7', // Izin
                        '#9333ea', // Sakit
                        '#e11d48', // Alfa
                        '#4f46e5'  // PKL
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#0f172a' : '#ffffff',
                        titleColor: isDark ? '#ffffff' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { 
                            stepSize: 1,
                            color: textColor,
                            font: { family: 'JetBrains Mono' }
                        },
                        grid: { color: gridColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: textColor,
                            font: { family: 'DM Sans', weight: '500' } 
                        }
                    }
                }
            }
        });
    }

    const isDark = document.documentElement.classList.contains('dark');
    createWkChart(isDark);
    window.addEventListener('themeChanged', e => createWkChart(e.detail.isDark));
});

function filterWaliKelasDash() {
    const status = (document.getElementById('selectWkStatus')?.value || '').toLowerCase();
    const q = (document.getElementById('inputWkSearch')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.row-wk-dash');
    rows.forEach(r => {
        const rowStatus = r.getAttribute('data-status') || '';
        const rowNama = r.getAttribute('data-nama') || '';
        const rowNis = r.getAttribute('data-nis') || '';
        const matchStatus = !status || rowStatus === status;
        const matchQ = !q || rowNama.includes(q) || rowNis.includes(q);
        r.style.display = (matchStatus && matchQ) ? '' : 'none';
    });
}

function downloadWaliKelas() {
    const dari   = document.getElementById('wk-dari').value;
    const sampai = document.getElementById('wk-sampai').value;
    if (!dari || !sampai) { alert('Pilih rentang tanggal terlebih dahulu.'); return; }
    if (dari > sampai)    { alert('Tanggal "Dari" tidak boleh lebih besar dari "Sampai".'); return; }
    window.location.href = '<?= BASEURL ?>/dashboard/download_kelas?dari=' + dari + '&sampai=' + sampai;
}

// ── PULANG CEPAT NOTIFICATION SYSTEM ──
const WK_BASE_URL = '<?= BASEURL ?>';
let wkKnownIds = new Set();

function wkToast(msg, type = 'warning', link = null) {
    let c = document.getElementById('wkToastContainer');
    if (!c) {
        c = document.createElement('div');
        c.id = 'wkToastContainer';
        c.className = 'fixed top-6 right-6 z-50 flex flex-col gap-3 pointer-events-none';
        c.style.maxWidth = '360px';
        document.body.appendChild(c);
    }
    const toast = document.createElement('div');
    toast.className = `pointer-events-auto bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium px-4 py-3 rounded-xl shadow-xl flex items-start gap-3 border border-slate-200 dark:border-slate-700`;
    toast.style.cssText = 'animation:fadeInToast .25s ease;';
    toast.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 flex-shrink-0"></i><div class="flex-1">${msg}</div>${link?`<a href="${link}" class="flex-shrink-0 vh-btn-primary py-1 px-2.5 text-[11px]">Proses</a>`:''}`;
    c.appendChild(toast);
    setTimeout(() => toast.remove(), 6000);
}

let currentDashboardHash = null;
async function wkPollPulangCepat() {
    try {
        const res  = await fetch(WK_BASE_URL + '/api/v1/dashboard_poll.php');
        const json = await res.json();
        if (!json.ok) return;
        const banner = document.getElementById('bannerPulangCepat');
        const detail = document.getElementById('bannerPulangCepatDetail');
        if (json.new_requests && json.new_requests.length > 0) {
            banner.classList.remove('hidden');
            const names = json.new_requests.slice(0,2).map(p => p.nama_siswa).join(', ');
            detail.textContent = `${json.new_requests.length} permohonan: ${names}${json.new_requests.length>2?' dan lainnya':''}`;
        } else {
            banner.classList.add('hidden');
        }
        
        currentDashboardHash = json.hash;
        
        let hasNew = false;
        json.new_requests.forEach(p => {
            if (!wkKnownIds.has(p.id)) {
                wkKnownIds.add(p.id);
                wkToast(`<b>${p.nama_siswa}</b> minta izin pulang jam <b>${String(p.jam_pengajuan).substring(0,5)}</b>`, 'warning', WK_BASE_URL + '/pulang_cepat');
                hasNew = true;
            }
        });

        if (hasNew && 'speechSynthesis' in window) {
            const msg = new SpeechSynthesisUtterance("siswa minta izin");
            msg.lang = 'id-ID';
            window.speechSynthesis.speak(msg);
        }
    } catch (_) {}
}

wkPollPulangCepat();
setInterval(function() {
    if (!document.hidden) wkPollPulangCepat();
}, 15000);

const wkStyle = document.createElement('style');
wkStyle.textContent = '@keyframes fadeInToast{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:none}}';
document.head.appendChild(wkStyle);
</script>

<?php $this->view('templates/scan_modal', $data); ?>
<?php $this->view('templates/footer'); ?>
