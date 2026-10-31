<?php 
$this->view('templates/header', $data); 
$role = SessionHelper::get('role') ?? 'siswa';
$name = SessionHelper::get('name') ?? 'Pengguna';
?>

<div class="w-full space-y-6">
    
    <!-- Hero / Greeting Card (Clean Institutional Dual-Theme) -->
    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 p-6 sm:p-8 shadow-sm">
        <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-emerald-500/10 dark:bg-emerald-500/5 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white mb-1.5 font-headline">Selamat Datang, <?= htmlspecialchars($name) ?></h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm max-w-xl leading-relaxed">
                    Sistem Presensi Digital & AI Terintegrasi SMK NU Wargabinangun. Pantau kehadiran dan rekap aktivitas harian secara transparan.
                </p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/60 px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center gap-3.5 shrink-0 shadow-xs">
                <i class="fa-regular fa-clock text-emerald-600 dark:text-emerald-400 text-2xl"></i>
                <div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider font-mono">Waktu Sistem (WIB)</p>
                    <p class="text-slate-800 dark:text-slate-100 font-bold text-lg font-mono tabular-nums leading-none mt-1" id="realtime-clock"><?= date('H:i') ?> WIB</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik & Akses Cepat Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Kolom Kiri: Statistik Utama (Sesuai Role) -->
        <div class="md:col-span-8 space-y-6">
            <h2 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-emerald-600 dark:text-emerald-400"></i> Ringkasan Indikator <?= ucfirst($role) ?>
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <?php if ($role === 'admin' || $role === 'kepsek'): ?>
                <!-- Stats Admin/Kepsek -->
                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-user-graduate text-slate-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 rounded-full border border-slate-200 dark:border-slate-600">Terdaftar</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Total Siswa</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">450 <span class="text-xs font-normal text-slate-400">Siswa</span></h3>
                    </div>
                </div>
                
                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-chalkboard-user text-emerald-600 dark:text-emerald-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-full border border-emerald-200/50 dark:border-emerald-800/40">Aktif</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Total Guru</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">45 <span class="text-xs font-normal text-slate-400">Guru</span></h3>
                    </div>
                </div>

                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-clipboard-check text-amber-600 dark:text-amber-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-full border border-amber-200/50 dark:border-amber-800/40">Hari Ini</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Tingkat Kehadiran</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">92<span class="text-lg font-bold text-slate-400">%</span></h3>
                    </div>
                </div>

                <?php elseif (in_array($role, ['wali_kelas', 'walikelas'])): ?>
                <!-- Stats Wali Kelas -->
                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-users-viewfinder text-slate-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 rounded-full border border-slate-200 dark:border-slate-600">Binaan</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Siswa Kelas Saya</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">32 <span class="text-xs font-normal text-slate-400">Siswa</span></h3>
                    </div>
                </div>
                
                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-user-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-full border border-emerald-200/50 dark:border-emerald-800/40">Hari Ini</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Presensi Hadir</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">30 <span class="text-xs font-normal text-slate-400">Siswa</span></h3>
                    </div>
                </div>

                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-envelope-open-text text-amber-600 dark:text-amber-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-full border border-amber-200/50 dark:border-amber-800/40">Pending</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Permohonan Izin</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">2 <span class="text-xs font-normal text-slate-400">Berkas</span></h3>
                    </div>
                </div>

                <?php else: ?>
                <!-- Stats Siswa / Guru Biasa -->
                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-calendar-check text-emerald-600 dark:text-emerald-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-full border border-emerald-200/50 dark:border-emerald-800/40">Bulan Ini</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Total Hadir</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">18 <span class="text-xs font-normal text-slate-400">Hari</span></h3>
                    </div>
                </div>
                
                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-user-clock text-sky-600 dark:text-sky-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 rounded-full border border-sky-200/50 dark:border-sky-800/40">Bulan Ini</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Izin / Sakit</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">1 <span class="text-xs font-normal text-slate-400">Hari</span></h3>
                    </div>
                </div>

                <div class="vh-card p-5 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-3">
                        <i class="fa-solid fa-user-xmark text-rose-600 dark:text-rose-400 text-lg"></i>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-full border border-rose-200/50 dark:border-rose-800/40">Bulan Ini</span>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] font-semibold uppercase tracking-wider mb-1">Tanpa Keterangan</p>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">0 <span class="text-xs font-normal text-slate-400">Hari</span></h3>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistik Visual (Grafik) -->
            <div class="vh-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-emerald-600 dark:text-emerald-400"></i> Tren Kehadiran Mingguan
                    </h3>
                    <div class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 rounded-full text-[10px] font-bold text-slate-600 dark:text-slate-300">
                        Bulan Ini
                    </div>
                </div>
                <div id="attendanceChart" class="w-full h-[250px] flex items-center justify-center">
                    <!-- Chart renders here -->
                </div>
            </div>
            
            <!-- Information Banner AI Assistant -->
            <div class="vh-card p-5 flex items-start gap-4">
                <i class="fa-solid fa-robot text-emerald-600 dark:text-emerald-400 text-2xl shrink-0 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-0.5">Asisten Cerdas Terintegrasi</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Sistem dilengkapi dengan Gemini AI untuk menjawab pertanyaan seputar rekap presensi, izin siswa/guru, dan analitik sekolah secara instan melalui tombol di sudut kanan bawah.
                    </p>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Quick Actions -->
        <div class="md:col-span-4 space-y-6">
            <h2 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i> Akses Menu Cepat
            </h2>
            
            <div class="grid grid-cols-1 gap-3">
                <a href="<?= BASEURL; ?>/jadwal" class="vh-card p-4 flex items-center gap-3.5 hover:border-emerald-500/50 hover:shadow-md transition-all group">
                    <i class="fa-solid fa-calendar-days text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 text-lg transition-colors shrink-0"></i>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Jadwal Pelajaran</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Cek agenda pelajaran harian</p>
                    </div>
                    <div class="ml-auto text-slate-300 dark:text-slate-600 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </div>
                </a>
                
                <?php if ($role === 'siswa' || $role === 'guru'): ?>
                <a href="<?= BASEURL; ?>/izin/ajukan" class="vh-card p-4 flex items-center gap-3.5 hover:border-emerald-500/50 hover:shadow-md transition-all group">
                    <i class="fa-solid fa-paper-plane text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 text-lg transition-colors shrink-0"></i>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Ajukan Izin / Sakit</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Form permohonan surat izin digital</p>
                    </div>
                    <div class="ml-auto text-slate-300 dark:text-slate-600 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </div>
                </a>
                <?php endif; ?>
                
                <?php if ($role === 'admin' || $role === 'kepsek' || in_array($role, ['wali_kelas', 'walikelas'])): ?>
                <a href="<?= BASEURL; ?>/izin/approval" class="vh-card p-4 flex items-center gap-3.5 hover:border-emerald-500/50 hover:shadow-md transition-all group">
                    <i class="fa-solid fa-check-double text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 text-lg transition-colors shrink-0"></i>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Persetujuan Izin</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Validasi permohonan siswa & guru</p>
                    </div>
                    <div class="ml-auto text-slate-300 dark:text-slate-600 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </div>
                </a>
                <?php endif; ?>

                <a href="<?= BASEURL; ?>/catatan" class="vh-card p-4 flex items-center gap-3.5 hover:border-emerald-500/50 hover:shadow-md transition-all group">
                    <i class="fa-solid fa-clipboard-list text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 text-lg transition-colors shrink-0"></i>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Buku Catatan</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Kelola catatan dan agenda kedinasan</p>
                    </div>
                    <div class="ml-auto text-slate-300 dark:text-slate-600 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </div>
                </a>
            </div>
        </div>
        
    </div>
</div>

<script src="<?= BASEURL; ?>/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script>
    // Realtime clock in Asia/Jakarta (WIB)
    setInterval(() => {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            timeZone: 'Asia/Jakarta'
        });
        const sec = now.getSeconds();
        const parts = timeStr.split(':');
        const colon = sec % 2 === 0 ? ':' : '<span style="visibility:hidden">:</span>';
        const el = document.getElementById('realtime-clock');
        if(el) el.innerHTML = (parts[0] || '00') + colon + (parts[1] || '00') + ' <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400">WIB</span>';
    }, 1000);

    // Render Chart using ApexCharts with Dual-Theme Light/Dark adaptation
    document.addEventListener("DOMContentLoaded", function() {
        const role = '<?= $role ?>';
        let chartInstance = null;
        
        function getChartOptions(isDark) {
            const textColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? '#334155' : '#e2e8f0';
            const headingColor = isDark ? '#f8fafc' : '#0f172a';

            if (role === 'admin' || role === 'kepsek' || role === 'wali_kelas' || role === 'walikelas') {
                return {
                    series: [{
                        name: 'Hadir',
                        data: [44, 55, 57, 56, 61, 58, 63]
                    }, {
                        name: 'Izin/Sakit',
                        data: [7, 8, 10, 9, 8, 10, 9]
                    }, {
                        name: 'Alfa',
                        data: [2, 3, 2, 1, 3, 3, 4]
                    }],
                    chart: {
                        type: 'bar',
                        height: 250,
                        toolbar: { show: false },
                        fontFamily: 'DM Sans, sans-serif',
                        background: 'transparent',
                        parentHeightOffset: 0
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '42%',
                            borderRadius: 4
                        },
                    },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: {
                        categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: textColor, fontFamily: 'DM Sans' } }
                    },
                    yaxis: { 
                        labels: { 
                            style: { colors: textColor, fontFamily: 'JetBrains Mono' } 
                        } 
                    },
                    grid: {
                        borderColor: gridColor
                    },
                    fill: { opacity: 1 },
                    colors: ['#059669', '#0284c7', '#e11d48'],
                    legend: {
                        labels: { colors: textColor }
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                        y: { formatter: function (val) { return val + " Orang" } }
                    }
                };
            } else {
                return {
                    series: [18, 1, 0],
                    labels: ['Hadir', 'Izin/Sakit', 'Alfa'],
                    chart: {
                        type: 'donut',
                        height: 250,
                        fontFamily: 'DM Sans, sans-serif',
                        background: 'transparent',
                        parentHeightOffset: 0
                    },
                    colors: ['#059669', '#0284c7', '#e11d48'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '72%',
                                labels: {
                                    show: true,
                                    name: { show: true, fontSize: '12px', fontWeight: 600, color: textColor },
                                    value: { show: true, fontSize: '24px', fontWeight: 700, color: headingColor, fontFamily: 'DM Sans' },
                                    total: {
                                        show: true,
                                        showAlways: true,
                                        label: 'Total',
                                        color: textColor,
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + " Hari"
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { width: 0 },
                    legend: { position: 'right', offsetY: 30, labels: { colors: textColor } }
                };
            }
        }

        const chartEl = document.querySelector("#attendanceChart");
        if (chartEl && typeof ApexCharts !== 'undefined') {
            const isDark = document.documentElement.classList.contains('dark');
            chartInstance = new ApexCharts(chartEl, getChartOptions(isDark));
            chartInstance.render();

            // Re-render chart on theme change
            window.addEventListener('themeChanged', function(e) {
                if (chartInstance) {
                    chartInstance.updateOptions(getChartOptions(e.detail.isDark));
                }
            });
        }
    });
</script>

<?php $this->view('templates/footer'); ?>