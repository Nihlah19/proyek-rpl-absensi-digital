<?php $this->view('templates/header', $data); ?>
<div id="pageContent" class="w-full space-y-6">
    <!-- ── PAGE HEADER (CLEAN & HUMBLE) ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-200/80 dark:border-slate-800">
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                    <i class="fa-solid fa-school text-[10px]"></i> Executive Overview
                </span>
                <?php if (!empty($data['pendingIzinCount']) && $data['pendingIzinCount'] > 0): ?>
                    <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        <i class="fa-solid fa-clock text-[10px]"></i> <?= $data['pendingIzinCount'] ?> Menunggu
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Dashboard Kepala Sekolah
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                <strong class="text-slate-700 dark:text-slate-300 font-medium">SMK NU Wargabinangun</strong> &middot; Monitoring presensi & kedisiplinan dewan guru
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap self-start sm:self-center">
            <a href="<?= BASEURL ?>/rekapabsensi?tipe=guru" class="btn-secondary text-xs">
                <i class="fa-solid fa-table-list text-slate-400"></i> Rekap Presensi Guru
            </a>
            <a href="<?= BASEURL ?>/izin/approval" class="btn-primary text-xs">
                <i class="fa-solid fa-clipboard-check"></i> Approval Izin
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <?php if (!empty($data['pendingIzinCount']) && $data['pendingIzinCount'] > 0): ?>
    <!-- ── PENDING APPROVAL ALERT BANNER ── -->
    <div class="bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-900/60 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <div>
                <p class="font-semibold text-amber-900 dark:text-amber-200 text-sm">Menunggu Persetujuan Izin / Sakit Dewan Guru</p>
                <p class="text-slate-600 dark:text-slate-400 text-xs mt-0.5"><span class="font-bold text-amber-600 dark:text-amber-400 font-mono"><?= $data['pendingIzinCount'] ?></span> berkas menunggu konfirmasi.</p>
            </div>
        </div>
        <a href="<?= BASEURL ?>/izin/approval" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1.5 shrink-0 shadow-xs">
            <i class="fa-solid fa-clipboard-check"></i> Proses Sekarang
        </a>
    </div>
    <?php endif; ?>

    <!-- ── GRID DASHBOARD ── -->
    <div class="grid grid-cols-12 gap-6">
        <?php
        $tg = (int)($data['statsGuru']->total_guru ?? 0);
        $gh = (int)($data['statsGuru']->hadir ?? 0);
        $gt = (int)($data['statsGuru']->terlambat ?? 0);
        $gi = (int)($data['statsGuru']->izin ?? 0);
        $gs = (int)($data['statsGuru']->sakit ?? 0);
        $ga = (int)($data['statsGuru']->alfa ?? 0);

        $guruGiat    = $gh + $gt;
        $gis         = $gi + $gs;
        $pctGuruGiat = $tg > 0 ? round(($guruGiat / $tg) * 100, 1) : 0;
        $pctGh       = $tg > 0 ? round(($gh / $tg) * 100, 1) : 0;
        $pctGt       = $tg > 0 ? round(($gt / $tg) * 100, 1) : 0;
        $pctGis      = $tg > 0 ? round(($gis / $tg) * 100, 1) : 0;
        $pctGa       = $tg > 0 ? round(($ga / $tg) * 100, 1) : 0;

        // Indikator jika presensi hari ini belum dimulai (misal sesi pagi hari sebelum jam masuk)
        $isSesiPagi  = ($guruGiat === 0 && $gis === 0);
        ?>

        <!-- Top Row: Executive Attendance Summary & Breakdown Cards -->
        <!-- 1. Executive Summary Card (Tingkat Kehadiran Guru) -->
        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-5 shadow-xs flex flex-col justify-between hover:shadow-sm transition-shadow">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Kehadiran Guru</span>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-sm border border-emerald-200/50 dark:border-emerald-800/40">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>

                <div class="flex items-baseline justify-between gap-2">
                    <div class="flex items-baseline gap-1">
                        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                            <?= $pctGuruGiat ?><span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 ml-0.5">%</span>
                        </h2>
                    </div>

                    <!-- Dynamic Status Pill -->
                    <?php if ($isSesiPagi): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Menunggu Presensi
                        </span>
                    <?php elseif ($pctGuruGiat >= 90): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Optimal
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Sesi Berjalan
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Multi-segment Stacked Progress Bar -->
                <div class="mt-4">
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden flex" title="Hadir Tepat: <?= $pctGh ?>%, Terlambat: <?= $pctGt ?>%, Izin/Sakit: <?= $pctGis ?>%, Belum Presensi: <?= $pctGa ?>%">
                        <?php if ($tg > 0): ?>
                            <?php if ($pctGh > 0): ?>
                                <div class="bg-emerald-500 h-full transition-all duration-500" style="width: <?= $pctGh ?>%;" title="Hadir Tepat (<?= $gh ?>)"></div>
                            <?php endif; ?>
                            <?php if ($pctGt > 0): ?>
                                <div class="bg-amber-500 h-full transition-all duration-500" style="width: <?= $pctGt ?>%;" title="Terlambat (<?= $gt ?>)"></div>
                            <?php endif; ?>
                            <?php if ($pctGis > 0): ?>
                                <div class="bg-sky-500 h-full transition-all duration-500" style="width: <?= $pctGis ?>%;" title="Izin/Sakit (<?= $gis ?>)"></div>
                            <?php endif; ?>
                            <?php if ($pctGa > 0): ?>
                                <div class="bg-slate-300 dark:bg-slate-700 h-full transition-all duration-500" style="width: <?= $pctGa ?>%;" title="Belum Presensi (<?= $ga ?>)"></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="bg-slate-200 dark:bg-slate-700 h-full w-full"></div>
                        <?php endif; ?>
                    </div>

                    <!-- Micro Legend Indicators -->
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 mt-2 font-mono">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                            <span>Hadir: <strong class="text-slate-800 dark:text-slate-200"><?= $guruGiat ?></strong></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-sky-500 inline-block"></span>
                            <span>Izin: <strong class="text-slate-800 dark:text-slate-200"><?= $gis ?></strong></span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                            <span>Belum: <strong class="text-slate-800 dark:text-slate-200"><?= $ga ?></strong></span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span>Total <strong class="font-mono text-slate-900 dark:text-white font-semibold"><?= $tg ?></strong> Dewan Guru</span>
                <span class="font-medium text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-day text-[10px] text-slate-400"></i> Hari Ini
                </span>
            </div>
        </div>

        <!-- 2. Breakdown Cards: 4 Micro Cards -->
        <div class="col-span-12 lg:col-span-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- 1. Hadir Tepat -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-emerald-300 dark:hover:border-emerald-800/80 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Hadir Tepat</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-sm border border-emerald-200/50 dark:border-emerald-800/40">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums"><?= $gh ?></h2>
                        <span class="text-xs text-slate-400 font-medium">guru</span>
                    </div>
                    <div class="mt-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/40">
                            <i class="fa-solid fa-arrow-trend-up text-[9px]"></i> <?= $pctGh ?>%
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Jadwal</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-[11px]">Tepat Waktu</span>
                </div>
            </div>

            <!-- 2. Terlambat -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-amber-300 dark:hover:border-amber-800/80 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Terlambat</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-sm border border-amber-200/50 dark:border-amber-800/40">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <h2 class="text-2xl sm:text-3xl font-bold <?= $gt > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' ?> font-mono tracking-tight tabular-nums"><?= $gt ?></h2>
                        <span class="text-xs text-slate-400 font-medium">guru</span>
                    </div>
                    <div class="mt-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold <?= $gt > 0 ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' ?>">
                            <?= $pctGt ?>% rasio
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Toleransi</span>
                    <span class="font-semibold <?= $gt > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500' ?> text-[11px]">
                        <?= $gt > 0 ? 'Lewat Batas' : 'Nihil' ?>
                    </span>
                </div>
            </div>

            <!-- 3. Izin / Sakit -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-sky-300 dark:hover:border-sky-800/80 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Izin / Sakit</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 text-sm border border-sky-200/50 dark:border-sky-800/40">
                            <i class="fa-solid fa-notes-medical"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <h2 class="text-2xl sm:text-3xl font-bold <?= $gis > 0 ? 'text-sky-600 dark:text-sky-400' : 'text-slate-900 dark:text-white' ?> font-mono tracking-tight tabular-nums"><?= $gis ?></h2>
                        <span class="text-xs text-slate-400 font-medium">guru</span>
                    </div>
                    <div class="mt-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold <?= $gis > 0 ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-200/50 dark:border-sky-800/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' ?>">
                            <?= $gi ?> Izin &middot; <?= $gs ?> Sakit
                        </span>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Berkas</span>
                    <span class="font-semibold text-slate-600 dark:text-slate-300 text-[11px]">Dispensasi</span>
                </div>
            </div>

            <!-- 4. Belum Presensi / Tanpa Keterangan -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Presensi</span>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center <?= $isSesiPagi ? 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700' : ($ga > 0 ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 border border-emerald-200/50') ?> text-sm">
                            <i class="fa-solid <?= $isSesiPagi ? 'fa-user-clock' : ($ga > 0 ? 'fa-user-xmark' : 'fa-check') ?>"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <h2 class="text-2xl sm:text-3xl font-bold <?= $isSesiPagi ? 'text-slate-700 dark:text-slate-300' : ($ga > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') ?> font-mono tracking-tight tabular-nums"><?= $ga ?></h2>
                        <span class="text-xs text-slate-400 font-medium">guru</span>
                    </div>
                    <div class="mt-2">
                        <?php if ($isSesiPagi): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                Sesi Pagi
                            </span>
                        <?php elseif ($ga > 0): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/40">
                                <?= $pctGa ?>% belum hadir
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
                    <span class="font-semibold <?= $isSesiPagi ? 'text-slate-500 dark:text-slate-400' : ($ga > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400') ?> text-[11px]">
                        <?= $isSesiPagi ? 'Menunggu Tapping' : ($ga > 0 ? 'Tanpa Keterangan' : 'Semua Hadir') ?></span>
                </div>
            </div>
        </div>

        <!-- Middle Row: Main Chart & List -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-chart-column"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Grafik Presensi Dewan Guru</h3>
                </div>
                <span class="text-xs font-mono text-slate-400">
                    Realtime Data
                </span>
            </div>
            <div class="p-5 flex-1 relative w-full h-64 min-h-[250px]">
                <canvas id="kepsekChart"></canvas>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Sering Terlambat</h3>
                </div>
                <span class="text-[11px] font-mono text-slate-400">Bulan Ini</span>
            </div>
            <div class="p-5 flex-1 overflow-y-auto pr-1 space-y-2.5" style="max-height: 250px;">
                <?php if (!empty($data['guruTerlambat'])): ?>
                    <?php foreach ($data['guruTerlambat'] as $index => $guru): ?>
                    <div class="p-2.5 bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800 rounded-lg flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-md bg-amber-50 dark:bg-amber-950/60 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xs font-semibold font-mono">
                                <?= $index + 1 ?>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-800 dark:text-slate-200 text-xs truncate max-w-[150px]" title="<?= htmlspecialchars($guru->name) ?>"><?= htmlspecialchars($guru->name) ?></p>
                                <p class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($guru->nip ?? 'Guru') ?></p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold font-mono bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/40 shrink-0">
                            <?= $guru->jumlah_terlambat ?>x
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-full text-center text-slate-400 py-6">
                        <i class="fa-solid fa-circle-check text-2xl mb-1.5 text-emerald-600 dark:text-emerald-400"></i>
                        <p class="text-xs">Semua dewan guru hadir tepat waktu bulan ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Middle Row 2: Tabel Presensi Guru Hari Ini -->
        <div class="col-span-12 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden flex flex-col">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">
                        Daftar Presensi Guru Hari Ini
                    </h3>
                </div>

                <a href="<?= BASEURL ?>/rekapabsensi?tipe=guru" class="btn-secondary text-xs self-start sm:self-auto">
                    <i class="fa-solid fa-table-list text-slate-400"></i> Rekap Absensi Lengkap <i class="fa-solid fa-arrow-right text-[10px] ml-1 text-slate-400"></i>
                </a>
            </div>

            <!-- Filter Cepat -->
            <div class="px-5 py-3 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/70 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800">
                <div class="w-full sm:w-56">
                    <select id="selectKepsekStatus" onchange="filterKepsekDash()"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <option value="">-- Semua Keterangan --</option>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alfa">Alfa</option>
                    </select>
                </div>

                <div class="w-full sm:w-72">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" id="inputKepsekSearch" onkeyup="filterKepsekDash()"
                                placeholder="Cari nama guru atau NIP..."
                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Tabel Guru -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-center w-12 font-mono">No</th>
                            <th class="px-4 py-3 font-mono">NIP</th>
                            <th class="px-4 py-3">Nama Guru</th>
                            <th class="px-4 py-3 text-center">Datang</th>
                            <th class="px-4 py-3 text-center">Pulang</th>
                            <th class="px-4 py-3 text-center">Keterangan</th>
                            <th class="px-4 py-3 text-center">Status Waktu</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyKepsekGuru" class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if (empty($data['absensiGuruHariIni'])): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-400 font-medium">
                                <i class="fa-solid fa-inbox text-2xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Belum ada guru yang melakukan presensi hari ini.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $noKg = 1;
                            foreach ($data['absensiGuruHariIni'] as $row): 
                                $st = strtolower($row->status ?? 'alfa');
                                $ketKey = ($st === 'terlambat') ? 'hadir' : $st;
                            ?>
                            <tr class="row-kepsek-dash hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors h-11"
                                data-nama="<?= htmlspecialchars(strtolower($row->name)) ?>"
                                data-nip="<?= htmlspecialchars(strtolower($row->nip ?? '')) ?>"
                                data-status="<?= htmlspecialchars($ketKey) ?>">
                                <td class="px-4 py-2 text-center text-slate-400 font-mono tabular-nums"><?= $noKg++ ?></td>
                                <td class="px-4 py-2 font-mono text-slate-500 dark:text-slate-400 tabular-nums"><?= htmlspecialchars($row->nip ?? '-') ?></td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold text-[10px] shrink-0">
                                            <?= strtoupper(substr($row->name, 0, 2)) ?>
                                        </div>
                                        <span class="font-medium text-slate-900 dark:text-slate-100 truncate"><?= htmlspecialchars($row->name) ?></span>
                                    </div>
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
                                <td class="px-4 py-2 text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title="<?= htmlspecialchars($row->keterangan ?? '') ?>">
                                    <?= htmlspecialchars($row->keterangan ?? '-') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Middle-Bottom Row: Persetujuan Izin & Sakit Guru -->
        <div class="col-span-12 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden flex flex-col">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-5 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">
                        Persetujuan Izin / Sakit Dewan Guru
                    </h3>
                </div>
                <div class="flex items-center gap-2.5">
                    <?php if (!empty($data['pendingIzinCount']) && $data['pendingIzinCount'] > 0): ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            <i class="fa-solid fa-clock text-[10px]"></i> <?= $data['pendingIzinCount'] ?> Menunggu
                        </span>
                    <?php endif; ?>
                    <a href="<?= BASEURL ?>/izin/approval" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                        Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-center w-12 font-mono">No</th>
                            <th class="px-4 py-3">Nama Guru</th>
                            <th class="px-4 py-3 text-center">Jenis</th>
                            <th class="px-4 py-3 text-center">Rentang Tanggal</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if (empty($data['daftarIzinGuru'])): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400 font-medium">
                                <i class="fa-solid fa-inbox text-2xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                                Belum ada pengajuan izin atau sakit dari dewan guru.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php 
                            $previewIzin = array_slice($data['daftarIzinGuru'], 0, 5);
                            $no = 1;
                            foreach ($previewIzin as $iz): 
                            ?>
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors h-11">
                                <td class="px-4 py-2 text-center text-slate-400 font-mono tabular-nums"><?= $no++ ?></td>
                                <td class="px-4 py-2">
                                    <p class="font-medium text-slate-900 dark:text-slate-100 text-xs"><?= htmlspecialchars($iz->nama_guru) ?></p>
                                    <?php if (!empty($iz->nip)): ?>
                                        <p class="text-[10px] text-slate-400 font-mono tabular-nums">NIP: <?= htmlspecialchars($iz->nip) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider <?= $iz->jenis === 'sakit' ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40' : 'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800/40' ?>">
                                        <?= strtoupper($iz->jenis) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-center font-mono text-xs text-slate-700 dark:text-slate-300 tabular-nums">
                                    <?= date('d/m/Y', strtotime($iz->tanggal_mulai)) ?>
                                    <?php if ($iz->tanggal_mulai !== $iz->tanggal_selesai): ?>
                                        <span class="text-slate-300 dark:text-slate-600">&mdash;</span> <?= date('d/m/Y', strtotime($iz->tanggal_selesai)) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 text-slate-500 dark:text-slate-400 max-w-[240px] truncate" title="<?= htmlspecialchars($iz->alasan) ?>">
                                    <?= htmlspecialchars($iz->alasan) ?>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <?= BadgeHelper::render($iz->status, strtoupper($iz->status)) ?>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <a href="<?= BASEURL ?>/izin/detail/<?= $iz->id ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium <?= $iz->status === 'pending' ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs' : 'btn-secondary' ?> transition-colors">
                                        <?= $iz->status === 'pending' ? '<i class="fa-solid fa-clipboard-check"></i> Proses' : '<i class="fa-solid fa-eye"></i> Detail' ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Row: Sub Chart & Actions -->
        <div class="col-span-12 lg:col-span-6 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Komposisi Presensi Dewan Guru</h3>
                </div>
            </div>
            <div class="relative w-full h-52">
                <canvas id="donutKepsek"></canvas>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-6 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-file-csv"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Unduh Rekap Presensi Guru</h3>
                </div>
                <form id="form-download-kepsek" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="kepsek-dari" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Dari Tanggal</label>
                            <input type="date" id="kepsek-dari" name="dari" value="<?= date('Y-m-01') ?>" class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="kepsek-sampai" class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Sampai Tanggal</label>
                            <input type="date" id="kepsek-sampai" name="sampai" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>
                    <button type="button" onclick="downloadKepsekGuru()" class="btn-primary w-full flex items-center justify-center gap-2 text-xs cursor-pointer mt-1">
                        <i class="fa-solid fa-download"></i> Unduh Laporan CSV
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Chart Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let chartBar = null;
            let chartDonut = null;



            function renderCharts(isDark) {
                const textColor = isDark ? '#94a3b8' : '#64748b';
                const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.7)';

                // Main Bar Chart
                const ctxMain = document.getElementById('kepsekChart')?.getContext('2d');
                if (ctxMain) {
                    if (chartBar) chartBar.destroy();
                    chartBar = new Chart(ctxMain, {
                        type: 'bar',
                        data: {
                            labels: ['Hadir Tepat', 'Terlambat', 'Izin', 'Sakit', 'Alfa'],
                            datasets: [
                                {
                                    label: 'Jumlah Guru',
                                    data: <?= json_encode([(int)$gh, (int)$gt, (int)$gi, (int)$gs, (int)$ga]) ?>,
                                    backgroundColor: [
                                        '#10b981', // Hadir
                                        '#f59e0b', // Telat
                                        '#0284c7', // Izin
                                        '#8b5cf6', // Sakit
                                        '#f43f5e'  // Alfa
                                    ],
                                    borderRadius: 8,
                                    borderSkipped: false,
                                    barPercentage: 0.5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                    titleColor: isDark ? '#ffffff' : '#0f172a',
                                    bodyColor: isDark ? '#cbd5e1' : '#475569',
                                    borderColor: isDark ? '#334155' : '#e2e8f0',
                                    borderWidth: 1,
                                    padding: 12,
                                    boxPadding: 6,
                                    usePointStyle: true,
                                    callbacks: {
                                        label: function(context) {
                                            return ` ${context.parsed.y} Guru`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: { 
                                    beginAtZero: true, 
                                    ticks: { 
                                        stepSize: 1, 
                                        color: textColor,
                                        font: { family: 'JetBrains Mono', size: 11 } 
                                    }, 
                                    grid: { 
                                        color: gridColor,
                                        borderDash: [4, 4]
                                    } 
                                },
                                x: { 
                                    grid: { display: false }, 
                                    ticks: { 
                                        color: textColor,
                                        font: { family: 'DM Sans', weight: '600', size: 11 } 
                                    } 
                                }
                            }
                        }
                    });
                }

                // Donut Chart
                const ctxDonut = document.getElementById('donutKepsek')?.getContext('2d');
                if (ctxDonut) {
                    if (chartDonut) chartDonut.destroy();
                    chartDonut = new Chart(ctxDonut, {
                        type: 'doughnut',
                        data: {
                            labels: ['Hadir Tepat', 'Terlambat', 'Izin / Sakit', 'Alfa'],
                            datasets: [{
                                data: <?= json_encode([(int)$gh, (int)$gt, (int)($gi + $gs), (int)$ga]) ?>,
                                backgroundColor: ['#10b981', '#f59e0b', '#0284c7', '#f43f5e'],
                                borderWidth: 0,
                                hoverOffset: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: {
                                legend: { 
                                    position: 'right', 
                                    labels: { 
                                        usePointStyle: true, 
                                        boxWidth: 8, 
                                        font: { size: 11, family: 'DM Sans', weight: '600' },
                                        color: textColor,
                                        padding: 14
                                    } 
                                },
                                tooltip: {
                                    backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                                    titleColor: isDark ? '#ffffff' : '#0f172a',
                                    bodyColor: isDark ? '#cbd5e1' : '#475569',
                                    borderColor: isDark ? '#334155' : '#e2e8f0',
                                    borderWidth: 1,
                                    padding: 12
                                }
                            }
                        }
                    });
                }
            }

            const isDark = document.documentElement.classList.contains('dark');
            renderCharts(isDark);
            window.addEventListener('themeChanged', e => renderCharts(e.detail.isDark));
        });

        function filterKepsekDash() {
            const status = (document.getElementById('selectKepsekStatus')?.value || '').toLowerCase();
            const q = (document.getElementById('inputKepsekSearch')?.value || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.row-kepsek-dash');
            rows.forEach(r => {
                const rowStatus = r.getAttribute('data-status') || '';
                const rowNama = r.getAttribute('data-nama') || '';
                const rowNip = r.getAttribute('data-nip') || '';
                const matchStatus = !status || rowStatus === status;
                const matchQ = !q || rowNama.includes(q) || rowNip.includes(q);
                r.style.display = (matchStatus && matchQ) ? '' : 'none';
            });
        }

        function downloadKepsekGuru(dari = '', sampai = '') {
            const dariEl = document.getElementById('kepsek-dari');
            const sampaiEl = document.getElementById('kepsek-sampai');
            if (!dari && dariEl) dari = dariEl.value;
            if (!sampai && sampaiEl) sampai = sampaiEl.value;
            if (!dari) dari = '<?= date('Y-m-01') ?>';
            if (!sampai) sampai = '<?= date('Y-m-d') ?>';
            const url = '<?= BASEURL ?>/kepsek/download_guru?dari=' + encodeURIComponent(dari) + '&sampai=' + encodeURIComponent(sampai);
            window.location.href = url;
        }
    </script>
</div>
<?php $this->view('templates/footer'); ?>