<?php
$this->view('templates/header', $data);

// Hitung data statistik Siswa
$ts = (int)($data['statsSiswa']->total_siswa ?? 0);
$sh = (int)($data['statsSiswa']->hadir ?? 0);
$st = (int)($data['statsSiswa']->terlambat ?? 0);
$si = (int)($data['statsSiswa']->izin ?? 0);
$ss = (int)($data['statsSiswa']->sakit ?? 0);
$sa = (int)($data['statsSiswa']->alfa ?? 0);
$spkl = (int)($data['statsSiswa']->pkl ?? 0);

$siswaGiat = $sh + $st + $spkl;
$pctSiswaGiat = $ts > 0 ? round(($siswaGiat / $ts) * 100, 1) : 0;
$siswaIzinSakit = $si + $ss;

// Hitung data statistik Guru
$tg = (int)($data['statsGuru']->total_guru ?? 0);
$gh = (int)($data['statsGuru']->hadir ?? 0);
$gt = (int)($data['statsGuru']->terlambat ?? 0);
$gi = (int)($data['statsGuru']->izin ?? 0);
$gs = (int)($data['statsGuru']->sakit ?? 0);
$ga = (int)($data['statsGuru']->alfa ?? 0);

$guruGiat = $gh + $gt;
$pctGuruGiat = $tg > 0 ? round(($guruGiat / $tg) * 100, 1) : 0;
$guruIzinSakit = $gi + $gs;
$totalWargaSekolah = $ts + $tg;
$totalBelumPresensi = $sa + $ga;
$isSesiPagiAdmin = ($siswaGiat === 0 && $guruGiat === 0 && ($siswaIzinSakit + $guruIzinSakit) === 0);

// Format data Trend 7 Hari
$trendLabels = array_column($data['trendHarian'] ?? [], 'label');
$trendSiswa  = array_column($data['trendHarian'] ?? [], 'siswa');
$trendGuru   = array_column($data['trendHarian'] ?? [], 'guru');

// Format data Per Kelas
$kelasLabels = [];
$kelasPcts = [];
$kelasHadirs = [];
$kelasTotals = [];
if (!empty($data['statsPerKelas'])) {
    foreach ($data['statsPerKelas'] as $ks) {
        $kelasLabels[] = $ks->nama_kelas;
        $t = (int)$ks->total_siswa;
        $h = (int)$ks->hadir_count;
        $pct = $t > 0 ? round(($h / $t) * 100, 1) : 0;
        $kelasPcts[] = $pct;
        $kelasHadirs[] = $h;
        $kelasTotals[] = $t;
    }
}
?>

<div id="pageContent" class="w-full space-y-6">
    <!-- ── PAGE HEADER (Clean & Structured) ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Statistik Presensi</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Realtime
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/admin/iot" class="btn-secondary text-xs">
                <i class="fa-solid fa-microchip text-slate-500"></i> Perangkat IoT
            </a>
            <a href="<?= BASEURL; ?>/rekapabsensi?tipe=siswa" class="btn-secondary text-xs">
                <i class="fa-solid fa-graduation-cap text-emerald-600"></i> Rekap Siswa
            </a>
            <a href="<?= BASEURL; ?>/rekapabsensi?tipe=guru" class="btn-secondary text-xs">
                <i class="fa-solid fa-chalkboard-user text-sky-600"></i> Rekap Guru
            </a>
            <button type="button" onclick="location.reload()" class="btn-primary text-xs cursor-pointer">
                <i class="fa-solid fa-arrows-rotate"></i> Muat Ulang
            </button>
        </div>
    </div>

    <!-- ── NOTIFIKASI PERMOHONAN PULANG CEPAT (Jika Ada) ── -->
    <div id="bannerPulangCepat" class="hidden bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/90 dark:border-amber-800/60 rounded-xl p-4 flex items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0 text-base border border-amber-200 dark:border-amber-700/50">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-amber-950 dark:text-amber-100 text-xs sm:text-sm">Ada permohonan izin pulang yang menunggu konfirmasi</p>
                <p class="text-amber-800 dark:text-amber-300 text-xs mt-0.5 font-medium truncate" id="bannerPulangCepatDetail">Memuat data permohonan...</p>
            </div>
        </div>
        <a href="<?= BASEURL ?>/izin/approval" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 shrink-0 shadow-xs">
            <i class="fa-solid fa-check"></i>
            <span>Proses</span>
        </a>
    </div>

    <!-- ── 4 KARTU STATISTIK INTI (EXECUTIVE DESIGN SYSTEM) ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Siswa Hadir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs hover:border-emerald-300 dark:hover:border-emerald-800/80 transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Siswa Hadir Hari Ini</span>
                    <div class="mt-2.5 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                            <?= $siswaGiat ?>
                        </span>
                        <span class="text-xs text-slate-400">/ <?= $ts ?> siswa</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm border border-emerald-200/50 dark:border-emerald-800/40">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Tingkat Kehadiran</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-mono font-semibold text-xs bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                    <i class="fa-solid fa-arrow-trend-up text-[9px]"></i> <?= $pctSiswaGiat ?>%
                </span>
            </div>
        </div>

        <!-- 2. Dewan Guru Hadir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs hover:border-sky-300 dark:hover:border-sky-800/80 transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Dewan Guru Hadir</span>
                    <div class="mt-2.5 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                            <?= $guruGiat ?>
                        </span>
                        <span class="text-xs text-slate-400">/ <?= $tg ?> pengajar</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm border border-sky-200/50 dark:border-sky-800/40">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <a href="<?= BASEURL; ?>/rekapabsensi?tipe=guru" class="text-slate-500 hover:text-sky-600 dark:text-slate-400 dark:hover:text-sky-300 transition-colors flex items-center gap-1 font-medium">
                    Rekap Presensi Guru <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-mono font-semibold text-xs bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-200/50 dark:border-sky-800/40">
                    <i class="fa-solid fa-arrow-trend-up text-[9px]"></i> <?= $pctGuruGiat ?>%
                </span>
            </div>
        </div>

        <!-- 3. Izin & Sakit -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs hover:border-amber-300 dark:hover:border-amber-800/80 transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Izin &amp; Sakit</span>
                    <div class="mt-2.5 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-bold <?= ($siswaIzinSakit + $guruIzinSakit) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' ?> font-mono tracking-tight tabular-nums">
                            <?= ($siswaIzinSakit + $guruIzinSakit) ?>
                        </span>
                        <span class="text-xs text-slate-400">Orang</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm border border-amber-200/50 dark:border-amber-800/40">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Rincian</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-mono font-semibold text-xs <?= ($siswaIzinSakit + $guruIzinSakit) > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' ?>">
                    <?= $siswaIzinSakit ?> Siswa &middot; <?= $guruIzinSakit ?> Guru
                </span>
            </div>
        </div>

        <!-- 4. Belum Presensi / Tanpa Keterangan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Presensi</span>
                    <div class="mt-2.5 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-bold <?= $isSesiPagiAdmin ? 'text-slate-700 dark:text-slate-300' : ($totalBelumPresensi > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') ?> font-mono tracking-tight tabular-nums">
                            <?= $totalBelumPresensi ?>
                        </span>
                        <span class="text-xs text-slate-400">Orang</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm <?= $isSesiPagiAdmin ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' : ($totalBelumPresensi > 0 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 border border-emerald-200/50') ?>">
                    <i class="fa-solid <?= $isSesiPagiAdmin ? 'fa-user-clock' : ($totalBelumPresensi > 0 ? 'fa-user-xmark' : 'fa-check') ?>"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400"><?= $isSesiPagiAdmin ? 'Status Sesi' : 'Tanpa Keterangan' ?></span>
                <?php if ($isSesiPagiAdmin): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-mono font-semibold text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        Menunggu Presensi Pagi
                    </span>
                <?php elseif ($totalBelumPresensi > 0): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-mono font-semibold text-xs bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40">
                        <?= $sa ?> Siswa &middot; <?= $ga ?> Guru
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-mono font-semibold text-xs bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                        Lengkap 100%
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── PUSAT PEMANTAUAN & TATA KELOLA SISWA PKL (ADMIN) ── -->
    <?php if (!empty($data['adminPkl'])): ?>
        <?php $this->view('dashboard/panel_admin_pkl', ['adminPkl' => $data['adminPkl']]); ?>
    <?php endif; ?>

    <!-- ── BARIS GRAFIK 1: DIAGRAM BATANG & TREN KEHADIRAN ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- 1. DIAGRAM BATANG: Status Presensi Hari Ini (Siswa vs Guru) -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 sm:p-6 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-100 dark:border-slate-800 flex-wrap gap-2">
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>Komparasi Status Presensi Hari Ini</span>
                </h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Siswa
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span> Guru
                    </span>
                </div>
            </div>
            <div class="relative w-full h-64 sm:h-72">
                <canvas id="chartBarStatus"></canvas>
            </div>
        </div>

        <!-- 2. STATISTIK GRAFIK: Tren Kehadiran 7 Hari Terakhir -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 sm:p-6 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-100 dark:border-slate-800 flex-wrap gap-2">
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Tren Kehadiran 7 Hari Terakhir</span>
                </h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-2 h-0.5 bg-emerald-600"></span> Siswa
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                        <span class="w-2 h-0.5 bg-sky-600"></span> Guru
                    </span>
                </div>
            </div>
            <div class="relative w-full h-64 sm:h-72">
                <canvas id="chartLineTrend"></canvas>
            </div>
        </div>
    </div>

    <!-- ── BARIS GRAFIK 2: DIAGRAM LINGKARAN & DIAGRAM BATANG PER KELAS ── -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-5">
        <!-- 3. DIAGRAM LINGKARAN: Komposisi Siswa -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-xs">
            <div class="pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-emerald-600 dark:text-emerald-400"></i>
                    <span>Komposisi Siswa</span>
                </h3>
            </div>
            <div class="relative w-full h-56">
                <canvas id="donutSiswa"></canvas>
            </div>
        </div>

        <!-- 4. DIAGRAM LINGKARAN: Komposisi Guru -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-xs">
            <div class="pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-sky-600 dark:text-sky-400"></i>
                    <span>Komposisi Guru</span>
                </h3>
            </div>
            <div class="relative w-full h-56">
                <canvas id="donutGuru"></canvas>
            </div>
        </div>

        <!-- 5. DIAGRAM BATANG: Tingkat Kehadiran per Kelas -->
        <div class="md:col-span-2 lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 flex flex-col justify-between shadow-xs">
            <div class="pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-chart-simple text-amber-600 dark:text-amber-400"></i>
                    <span>Kehadiran per Kelas</span>
                </h3>
            </div>
            <div class="relative w-full h-56">
                <canvas id="chartBarKelas"></canvas>
            </div>
        </div>
    </div>

    <!-- ── DAFTAR PRESENSI GURU HARI INI (ADMIN VIEW & REKAP QUICK ACCESS) ── -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm border border-sky-200/50 dark:border-sky-800/40">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">Presensi Dewan Guru Hari Ini</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Monitoring kehadiran real-time pendidik &amp; tenaga kependidikan</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="<?= BASEURL; ?>/dashboard/download_guru" class="btn-secondary text-xs">
                    <i class="fa-solid fa-file-csv text-emerald-600"></i> Unduh CSV Hari Ini
                </a>
                <a href="<?= BASEURL; ?>/rekapabsensi?tipe=guru" class="btn-primary text-xs">
                    <i class="fa-solid fa-table-list"></i> Buka Rekap Guru Lengkap <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Filter & Pencarian Cepat Guru -->
        <div class="px-4 py-3 bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="w-full sm:w-56">
                <select id="selectAdminGuruStatus" onchange="filterAdminGuruDash()"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-sky-500">
                    <option value="">-- Semua Keterangan --</option>
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alfa">Alfa</option>
                </select>
            </div>
            <div class="w-full sm:w-72 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                <input type="text" id="inputAdminGuruSearch" onkeyup="filterAdminGuruDash()"
                       placeholder="Cari nama guru atau NIP..."
                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-sky-500">
            </div>
        </div>

        <!-- Tabel Presensi Guru Hari Ini -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Guru &amp; NIP</th>
                        <th class="py-3 px-4 text-center">Jam Datang</th>
                        <th class="py-3 px-4 text-center">Jam Pulang</th>
                        <th class="py-3 px-4 text-center">Keterangan</th>
                        <th class="py-3 px-4 text-center">Status Waktu</th>
                        <th class="py-3 px-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    <?php if (!empty($data['absensiGuruHariIni'])): ?>
                        <?php $noG = 1; foreach ($data['absensiGuruHariIni'] as $g):
                            $rawSt = strtolower($g->status ?? 'alfa');
                            $ketKey = ($rawSt === 'terlambat') ? 'hadir' : $rawSt;
                        ?>
                            <tr class="row-admin-guru-dash hover:bg-slate-50/60 dark:hover:bg-slate-800/50 transition-colors"
                                data-status="<?= htmlspecialchars($ketKey) ?>"
                                data-nama="<?= strtolower(htmlspecialchars($g->name ?? '')) ?>"
                                data-nip="<?= strtolower(htmlspecialchars($g->nip ?? '')) ?>">
                                <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]"><?= $noG++ ?></td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-900 dark:text-white block"><?= htmlspecialchars($g->name ?? '-') ?></span>
                                    <span class="text-[11px] text-slate-400 font-mono">NIP: <?= htmlspecialchars($g->nip ?? '-') ?></span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-xs">
                                    <?= !empty($g->jam_datang) ? substr($g->jam_datang, 0, 5) : '<span class="text-slate-400">-</span>' ?>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-xs">
                                    <?= !empty($g->jam_pulang) ? substr($g->jam_pulang, 0, 5) : '<span class="text-slate-400">-</span>' ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php
                                    $badgeCls = match($ketKey) {
                                        'hadir'     => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                        'izin'      => 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                        'sakit'     => 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                        'alfa'      => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700',
                                        default     => 'bg-slate-50 text-slate-600 border-slate-200'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border <?= $badgeCls ?>">
                                        <?= ucfirst($ketKey ?: 'Alfa') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <?php if ($rawSt === 'hadir'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800">
                                            Tepat Waktu
                                        </span>
                                    <?php elseif ($rawSt === 'terlambat'): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800">
                                            Terlambat
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-xs">
                                    <?= htmlspecialchars($g->keterangan ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                <i class="fa-solid fa-inbox text-2xl mb-2 block opacity-40"></i>
                                Belum ada riwayat presensi guru untuk hari ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── TOAST NOTIFIKASI CONTAINER ── -->
<div id="toastContainer" class="fixed top-6 inset-x-3 sm:inset-x-auto sm:right-6 z-50 flex flex-col gap-3 pointer-events-none max-w-[calc(100vw-1.5rem)] sm:max-w-[360px]"></div>

<!-- ── SCRIPTS INITIALIZE CHARTS & STATISTIK ── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let chartBarStatusInst = null;
    let chartLineTrendInst = null;
    let chartDonutSiswaInst = null;
    let chartDonutGuruInst = null;
    let chartBarKelasInst = null;

    function renderAllCharts(isDark) {
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? 'rgba(35, 53, 39, 0.7)' : 'rgba(227, 232, 225, 0.8)';
        const siswaColor = isDark ? '#9fe870' : '#2e6d22';
        const guruColor  = isDark ? '#38bdf8' : '#0284c7';
        const fontInter = "'Inter', sans-serif";
        const fontMono  = "'JetBrains Mono', monospace";

        // ── 1. DIAGRAM BATANG: Status Presensi Siswa vs Guru ──
        const ctxBarStatus = document.getElementById('chartBarStatus')?.getContext('2d');
        if (ctxBarStatus) {
            if (chartBarStatusInst) chartBarStatusInst.destroy();
            chartBarStatusInst = new Chart(ctxBarStatus, {
                type: 'bar',
                data: {
                    labels: ['Hadir', 'Telat', 'Izin', 'Sakit', 'Alfa', 'PKL'],
                    datasets: [
                        {
                            label: 'Siswa',
                            data: [<?= (int)$sh ?>, <?= (int)$st ?>, <?= (int)$si ?>, <?= (int)$ss ?>, <?= (int)$sa ?>, <?= (int)$spkl ?>],
                            backgroundColor: siswaColor,
                            borderRadius: 8,
                            barPercentage: 0.65,
                            categoryPercentage: 0.75
                        },
                        {
                            label: 'Guru',
                            data: [<?= (int)$gh ?>, <?= (int)$gt ?>, <?= (int)$gi ?>, <?= (int)$gs ?>, <?= (int)$ga ?>, 0],
                            backgroundColor: guruColor,
                            borderRadius: 8,
                            barPercentage: 0.65,
                            categoryPercentage: 0.75
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#141f17' : '#ffffff',
                            titleColor: isDark ? '#ffffff' : '#163300',
                            bodyColor: isDark ? '#cbd5e1' : '#475569',
                            borderColor: isDark ? '#233527' : '#e3e8e1',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { family: fontInter, weight: '700', size: 12 },
                            bodyFont: { family: fontMono, size: 11 },
                            boxPadding: 4
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: textColor, font: { family: fontMono, size: 10 } },
                            grid: { color: gridColor, borderDash: [4, 4] }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor, font: { family: fontInter, weight: '600', size: 11 } }
                        }
                    }
                }
            });
        }

        // ── 2. STATISTIK GRAFIK: Tren 7 Hari Terakhir (Line Chart with Gradient) ──
        const canvasLine = document.getElementById('chartLineTrend');
        const ctxLineTrend = canvasLine?.getContext('2d');
        if (ctxLineTrend) {
            if (chartLineTrendInst) chartLineTrendInst.destroy();
            
            // Build dynamic gradient fill
            const gradSiswa = ctxLineTrend.createLinearGradient(0, 0, 0, 260);
            gradSiswa.addColorStop(0, isDark ? 'rgba(159, 232, 112, 0.28)' : 'rgba(46, 109, 34, 0.22)');
            gradSiswa.addColorStop(1, 'rgba(46, 109, 34, 0.0)');

            const gradGuru = ctxLineTrend.createLinearGradient(0, 0, 0, 260);
            gradGuru.addColorStop(0, isDark ? 'rgba(56, 189, 248, 0.25)' : 'rgba(2, 132, 199, 0.20)');
            gradGuru.addColorStop(1, 'rgba(2, 132, 199, 0.0)');

            chartLineTrendInst = new Chart(ctxLineTrend, {
                type: 'line',
                data: {
                    labels: <?= json_encode($trendLabels) ?>,
                    datasets: [
                        {
                            label: 'Siswa Hadir',
                            data: <?= json_encode($trendSiswa) ?>,
                            borderColor: siswaColor,
                            backgroundColor: gradSiswa,
                            borderWidth: 2.5,
                            tension: 0.38,
                            fill: true,
                            pointBackgroundColor: siswaColor,
                            pointBorderColor: isDark ? '#141f17' : '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 7
                        },
                        {
                            label: 'Guru Hadir',
                            data: <?= json_encode($trendGuru) ?>,
                            borderColor: guruColor,
                            backgroundColor: gradGuru,
                            borderWidth: 2.5,
                            tension: 0.38,
                            fill: true,
                            pointBackgroundColor: guruColor,
                            pointBorderColor: isDark ? '#141f17' : '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#141f17' : '#ffffff',
                            titleColor: isDark ? '#ffffff' : '#163300',
                            bodyColor: isDark ? '#cbd5e1' : '#475569',
                            borderColor: isDark ? '#233527' : '#e3e8e1',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { family: fontInter, weight: '700', size: 12 },
                            bodyFont: { family: fontMono, size: 11 },
                            boxPadding: 4
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: textColor, font: { family: fontMono, size: 10 } },
                            grid: { color: gridColor, borderDash: [4, 4] }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor, font: { family: fontInter, weight: '600', size: 11 } }
                        }
                    }
                }
            });
        }

        // ── 3. DIAGRAM LINGKARAN: Komposisi Siswa ──
        const ctxDonutSiswa = document.getElementById('donutSiswa')?.getContext('2d');
        if (ctxDonutSiswa) {
            if (chartDonutSiswaInst) chartDonutSiswaInst.destroy();
            chartDonutSiswaInst = new Chart(ctxDonutSiswa, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Telat', 'Izin/Sakit', 'Alfa'],
                    datasets: [{
                        data: [<?= (int)($sh + $spkl) ?>, <?= (int)$st ?>, <?= (int)($si + $ss) ?>, <?= (int)$sa ?>],
                        backgroundColor: [siswaColor, '#f59e0b', '#0284c7', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6,
                                font: { size: 10.5, family: fontInter, weight: '500' },
                                color: textColor
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#141f17' : '#ffffff',
                            titleColor: isDark ? '#ffffff' : '#163300',
                            bodyColor: isDark ? '#cbd5e1' : '#475569',
                            borderColor: isDark ? '#233527' : '#e3e8e1',
                            borderWidth: 1,
                            padding: 8,
                            cornerRadius: 8
                        }
                    }
                }
            });
        }

        // ── 4. DIAGRAM LINGKARAN: Komposisi Guru ──
        const ctxDonutGuru = document.getElementById('donutGuru')?.getContext('2d');
        if (ctxDonutGuru) {
            if (chartDonutGuruInst) chartDonutGuruInst.destroy();
            chartDonutGuruInst = new Chart(ctxDonutGuru, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Telat', 'Izin/Sakit', 'Alfa'],
                    datasets: [{
                        data: [<?= (int)$gh ?>, <?= (int)$gt ?>, <?= (int)($gi + $gs) ?>, <?= (int)$ga ?>],
                        backgroundColor: [guruColor, '#f59e0b', '#06b6d4', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6,
                                font: { size: 10.5, family: fontInter, weight: '500' },
                                color: textColor
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#141f17' : '#ffffff',
                            titleColor: isDark ? '#ffffff' : '#163300',
                            bodyColor: isDark ? '#cbd5e1' : '#475569',
                            borderColor: isDark ? '#233527' : '#e3e8e1',
                            borderWidth: 1,
                            padding: 8,
                            cornerRadius: 8
                        }
                    }
                }
            });
        }

        // ── 5. DIAGRAM BATANG: Tingkat Kehadiran per Kelas (%) ──
        const ctxBarKelas = document.getElementById('chartBarKelas')?.getContext('2d');
        if (ctxBarKelas) {
            if (chartBarKelasInst) chartBarKelasInst.destroy();
            chartBarKelasInst = new Chart(ctxBarKelas, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($kelasLabels) ?>,
                    datasets: [{
                        label: 'Persentase Hadir (%)',
                        data: <?= json_encode($kelasPcts) ?>,
                        backgroundColor: isDark ? '#fbbf24' : '#d97706',
                        borderRadius: 6,
                        barPercentage: 0.65
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#141f17' : '#ffffff',
                            titleColor: isDark ? '#ffffff' : '#163300',
                            bodyColor: isDark ? '#cbd5e1' : '#475569',
                            borderColor: isDark ? '#233527' : '#e3e8e1',
                            borderWidth: 1,
                            padding: 9,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '% Hadir';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 25,
                                color: textColor,
                                font: { family: fontMono, size: 10 },
                                callback: val => val + '%'
                            },
                            grid: { color: gridColor }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { family: fontInter, weight: '600', size: 10 },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    }
                }
            });
        }
    }

    const isDark = document.documentElement.classList.contains('dark');
    renderAllCharts(isDark);
    window.addEventListener('themeChanged', e => renderAllCharts(e.detail.isDark));
});



// ── PULANG CEPAT POLLING & TOAST ──
var appBaseUrl = window.BASE_URL || '<?= BASEURL ?>';
let knownPendingIds = new Set();

function tampilToastAdmin(msg, type = 'warning', link = null) {
    const c = document.getElementById('toastContainer');
    if (!c) return;
    const toast = document.createElement('div');
    toast.className = 'pointer-events-auto bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-medium px-4 py-3 rounded-xl shadow-xl flex items-start gap-3 border border-slate-200 dark:border-slate-700 animate-fadeIn';
    toast.style.cssText = 'max-width:360px;';
    const icon = type === 'success' ? 'fa-circle-check text-emerald-600' : type === 'error' ? 'fa-circle-xmark text-rose-600' : 'fa-triangle-exclamation text-amber-500';
    toast.innerHTML = `<i class="fa-solid ${icon} mt-0.5 shrink-0 text-sm"></i>
        <div class="flex-1">${msg}</div>
        ${link ? `<a href="${link}" class="shrink-0 vh-btn-primary py-1 px-2.5 text-[11px]">Proses</a>` : ''}`;
    c.appendChild(toast);
    setTimeout(() => toast.remove(), 6000);
}

async function pollPulangCepat() {
    try {
        const res = await fetch(appBaseUrl + '/api/v1/dashboard_poll.php');
        const json = await res.json();
        if (!json.ok) return;

        const banner = document.getElementById('bannerPulangCepat');
        const detail = document.getElementById('bannerPulangCepatDetail');

        if (json.new_requests && json.new_requests.length > 0) {
            if (banner) banner.classList.remove('hidden');
            const names = json.new_requests.slice(0, 2).map(p => p.nama_siswa).join(', ');
            const extra = json.new_requests.length > 2 ? ` dan ${json.new_requests.length - 2} lainnya` : '';
            if (detail) detail.textContent = `${json.new_requests.length} permohonan: ${names}${extra}`;
        } else {
            if (banner) banner.classList.add('hidden');
        }

        json.new_requests.forEach(p => {
            if (!knownPendingIds.has(p.id)) {
                knownPendingIds.add(p.id);
                tampilToastAdmin(
                    `<b>${p.nama_siswa}</b> mengajukan izin pulang (${String(p.jam_pengajuan).substring(0, 5)})`,
                    'warning',
                    appBaseUrl + '/izin/approval'
                );
            }
        });
    } catch (_) {}
}

pollPulangCepat();
setInterval(function() {
    if (!document.hidden) pollPulangCepat();
}, 15000);

// Auto-redirect jika ada navigasi hash langsung ke modul terkait
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#iot-devices') {
        window.location.href = '<?= BASEURL; ?>/admin/iot';
    }
});
function filterAdminGuruDash() {
    const status = (document.getElementById('selectAdminGuruStatus')?.value || '').toLowerCase();
    const q = (document.getElementById('inputAdminGuruSearch')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.row-admin-guru-dash');
    rows.forEach(r => {
        const rowStatus = r.getAttribute('data-status') || '';
        const rowNama = r.getAttribute('data-nama') || '';
        const rowNip = r.getAttribute('data-nip') || '';
        const matchStatus = !status || rowStatus === status;
        const matchQ = !q || rowNama.includes(q) || rowNip.includes(q);
        r.style.display = (matchStatus && matchQ) ? '' : 'none';
    });
}
</script>

<?php $this->view('templates/footer'); ?>