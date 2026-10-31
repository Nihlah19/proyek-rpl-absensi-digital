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
            <a href="<?= BASEURL; ?>/rekapabsensi" class="btn-secondary text-xs">
                <i class="fa-solid fa-file-excel text-slate-500"></i> Rekap Absensi
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

    <!-- ── 4 KARTU STATISTIK INTI ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Siswa Hadir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs">
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
                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-sm border border-emerald-200/60 dark:border-emerald-800/50">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Tingkat Kehadiran</span>
                <span class="apex-trend-badge apex-trend-up">
                    <i class="fa-solid fa-arrow-trend-up text-[10px]"></i> <?= $pctSiswaGiat ?>%
                </span>
            </div>
        </div>

        <!-- 2. Dewan Guru Hadir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs">
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
                <div class="w-9 h-9 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 flex items-center justify-center text-sm border border-sky-200/60 dark:border-sky-800/50">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Tingkat Kehadiran</span>
                <span class="apex-trend-badge apex-trend-up">
                    <i class="fa-solid fa-arrow-trend-up text-[10px]"></i> <?= $pctGuruGiat ?>%
                </span>
            </div>
        </div>

        <!-- 3. Izin & Sakit -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Izin &amp; Sakit</span>
                    <div class="mt-2.5 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-bold text-amber-700 dark:text-amber-400 font-mono tracking-tight tabular-nums">
                            <?= ($siswaIzinSakit + $guruIzinSakit) ?>
                        </span>
                        <span class="text-xs text-slate-400">Orang</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center text-sm border border-amber-200/60 dark:border-amber-800/50">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Rincian</span>
                <span class="apex-trend-badge apex-trend-warn">
                    <?= $siswaIzinSakit ?> Siswa &middot; <?= $guruIzinSakit ?> Guru
                </span>
            </div>
        </div>

        <!-- 4. Belum Presensi / Tanpa Keterangan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Presensi</span>
                    <div class="mt-2.5 flex items-baseline gap-2">
                        <span class="text-2xl sm:text-3xl font-bold text-rose-600 dark:text-rose-400 font-mono tracking-tight tabular-nums">
                            <?= ($sa + $ga) ?>
                        </span>
                        <span class="text-xs text-slate-400">Orang</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm border border-rose-200/60 dark:border-rose-800/50">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-500 dark:text-slate-400">Tanpa Keterangan</span>
                <span class="apex-trend-badge apex-trend-down">
                    <?= $sa ?> Siswa &middot; <?= $ga ?> Guru
                </span>
            </div>
        </div>
    </div>

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
</div>

<!-- ── TOAST NOTIFIKASI CONTAINER ── -->
<div id="toastContainer" class="fixed top-6 right-6 z-50 flex flex-col gap-3 pointer-events-none" style="max-width:360px;"></div>

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
setInterval(pollPulangCepat, 10000);

// Auto-redirect jika ada navigasi hash langsung ke modul terkait
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#iot-devices') {
        window.location.href = '<?= BASEURL; ?>/admin/iot';
    }
});
</script>

<?php $this->view('templates/footer'); ?>