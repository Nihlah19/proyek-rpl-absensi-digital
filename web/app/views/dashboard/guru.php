<?php $this->view('templates/header', $data); ?>

<?php
$guruNama     = htmlspecialchars($data['guru']->nama ?? 'Bapak/Ibu Guru');
$guruNip      = htmlspecialchars($data['guru']->nip ?? '-');
$guruSubject  = htmlspecialchars($data['guru']->subject ?? $data['guru']->bidang_studi ?? 'Guru Mata Pelajaran');
$guruBarcode  = htmlspecialchars($data['guru']->barcode_id ?? $data['guru']->nip ?? '');
$absensiToday = $data['absensiHariIni'] ?? null;
$statsBulan   = $data['statsBulanan'] ?? null;
$jadwalToday  = $data['jadwalHariIni'] ?? null;
$riwayatList  = $data['riwayatGuru'] ?? [];

$totalHadirBulan = ((int)($statsBulan->hadir ?? 0)) + ((int)($statsBulan->terlambat ?? 0));
$totalTepatWaktu = (int)($statsBulan->hadir ?? 0);
$totalTerlambat  = (int)($statsBulan->terlambat ?? 0);
$totalIzinSakit  = (int)($statsBulan->izin_sakit ?? 0);
?>

<div id="pageContent" class="w-full space-y-6">
    <!-- ── PAGE HEADER (CLEAN & HUMBLE) ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-200/80 dark:border-slate-800">
        <div class="space-y-1">
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Dashboard Presensi Guru
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Selamat datang, <strong class="text-slate-700 dark:text-slate-300 font-medium"><?= $guruNama ?></strong> &middot; NIP: <span class="font-mono"><?= $guruNip ?></span>
            </p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-center">
            <button type="button" onclick="openScanModal()" class="btn-primary text-xs cursor-pointer">
                <i class="fa-solid fa-qrcode"></i> Scan Absensi
            </button>
            <a href="<?= BASEURL; ?>/rekapabsensi/guru" class="btn-secondary text-xs">
                <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Riwayat Lengkap
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- ── 4 RINGKASAN METRIK KARTU ── -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Status Hari Ini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Status Hari Ini</span>
                    <div class="mt-2">
                        <?php if ($absensiToday): ?>
                            <?= BadgeHelper::render($absensiToday->status, strtoupper($absensiToday->status)) ?>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Belum Presensi
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-sm">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Waktu Masuk</span>
                <span class="font-mono text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                    <?= $absensiToday && $absensiToday->jam_datang ? date('H:i', strtotime($absensiToday->jam_datang)) . ' WIB' : 'Pagi ini' ?>
                </span>
            </div>
        </div>

        <!-- Card 2: Kehadiran Bulan Ini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Kehadiran Bulan Ini</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                            <?= $totalHadirBulan ?>
                        </span>
                        <span class="text-xs text-slate-400 font-sans">Hari</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Periode</span>
                <span class="text-slate-600 dark:text-slate-300 font-semibold text-[11px]"><?= date('F Y') ?></span>
            </div>
        </div>

        <!-- Card 3: Tepat Waktu -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Tepat Waktu</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight tabular-nums">
                            <?= $totalTepatWaktu ?>
                        </span>
                        <span class="text-xs text-slate-400 font-sans">Hari</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-sm">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Kepatuhan</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-[11px]">Disiplin</span>
            </div>
        </div>

        <!-- Card 4: Terlambat / Izin -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Terlambat / Izin</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400 font-mono tracking-tight tabular-nums">
                            <?= $totalTerlambat ?>
                        </span>
                        <span class="text-xs text-slate-300 dark:text-slate-600 font-sans">/</span>
                        <span class="text-2xl sm:text-3xl font-bold text-sky-600 dark:text-sky-400 font-mono tracking-tight tabular-nums">
                            <?= $totalIzinSakit ?>
                        </span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                <span class="text-slate-400">Rincian</span>
                <span class="text-slate-600 dark:text-slate-400 font-mono text-[11px]">
                    <?= $totalTerlambat ?>x Telat &middot; <?= $totalIzinSakit ?>x Izin
                </span>
            </div>
        </div>
    </div>

    <?php if (!empty($data['isPembimbingPkl']) && !empty($data['pklDashboard'])): ?>
        <!-- ── PANEL GURU PEMBIMBING PKL (PENGATURAN & LOG RIWAYAT ABSENSI PKL) ── -->
        <?php $this->view('dashboard/panel_pembimbing_pkl', ['pkl' => $data['pklDashboard']]); ?>
    <?php endif; ?>

    <!-- ── MAIN CONTENT (2 COLUMNS: 7 / 5) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- ── LEFT COLUMN (7 COLS): PRESENSI HARI INI & RIWAYAT ── -->
        <div class="lg:col-span-7 space-y-6">
            <!-- 1. Presensi Hari Ini Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Presensi Kehadiran Hari Ini</h2>
                    </div>
                </div>

                <div class="p-5">
                    <?php if ($absensiToday): ?>
                        <?php $statusAbsen = strtolower($absensiToday->status ?? ''); ?>

                        <?php if (in_array($statusAbsen, ['izin', 'sakit'])): ?>
                            <div class="p-4 bg-sky-50 dark:bg-sky-950/40 border border-sky-200/70 dark:border-sky-800/40 rounded-xl flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-900/60 text-sky-600 dark:text-sky-300 flex items-center justify-center shrink-0 text-base">
                                    <i class="fa-solid fa-notes-medical"></i>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="text-sm font-semibold text-sky-900 dark:text-sky-200">Pengajuan <?= ucfirst($statusAbsen) ?> Disetujui</h4>
                                        <?= BadgeHelper::render($statusAbsen, strtoupper($statusAbsen)) ?>
                                    </div>
                                    <p class="text-xs text-sky-700 dark:text-sky-300">
                                        Kehadiran Anda tercatat sebagai <strong class="text-sky-900 dark:text-white"><?= strtoupper($statusAbsen) ?></strong>.
                                    </p>
                                    <?php if (!empty($absensiToday->keterangan)): ?>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 italic pt-1 border-t border-sky-200/40 dark:border-sky-800/30">
                                            Catatan: "<?= htmlspecialchars($absensiToday->keterangan) ?>"
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Status Kehadiran:</span>
                                    <?= BadgeHelper::render($absensiToday->status, strtoupper($absensiToday->status)) ?>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Jam Datang -->
                                    <div class="p-3.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jam Masuk (Datang)</span>
                                            <i class="fa-solid fa-arrow-right-to-bracket text-emerald-600 dark:text-emerald-400 text-xs"></i>
                                        </div>
                                        <p class="text-xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">
                                            <?= $absensiToday->jam_datang ? date('H:i', strtotime($absensiToday->jam_datang)) . ' <span class="text-xs font-sans text-slate-400 font-normal">WIB</span>' : '<span class="text-slate-400">-</span>' ?>
                                        </p>
                                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1 font-medium">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Presensi masuk tercatat
                                        </p>
                                    </div>

                                    <!-- Jam Pulang -->
                                    <div class="p-3.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jam Kepulangan</span>
                                            <i class="fa-solid fa-arrow-right-from-bracket text-slate-400 text-xs"></i>
                                        </div>
                                        <p class="text-xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">
                                            <?= $absensiToday->jam_pulang ? date('H:i', strtotime($absensiToday->jam_pulang)) . ' <span class="text-xs font-sans text-slate-400 font-normal">WIB</span>' : '<span class="text-slate-400">-</span>' ?>
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-1">
                                            <?= $absensiToday->jam_pulang ? 'Presensi pulang tercatat' : 'Belum melakukan scan pulang' ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if (!$absensiToday->jam_pulang && in_array($statusAbsen, ['hadir', 'terlambat'])): ?>
                                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/40 rounded-lg flex items-center justify-between gap-3 flex-wrap">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-circle-exclamation text-amber-600 dark:text-amber-400 text-sm shrink-0"></i>
                                            <p class="text-xs text-amber-800 dark:text-amber-300 font-medium">
                                                Anda belum melakukan presensi pulang hari ini.
                                            </p>
                                        </div>
                                        <button type="button" onclick="openScanModal()" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-semibold transition-colors shrink-0 shadow-xs flex items-center gap-1.5 cursor-pointer">
                                            <i class="fa-solid fa-qrcode"></i> Scan Pulang
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Belum Presensi -->
                        <div class="text-center py-6 px-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl mx-auto mb-2.5">
                                <i class="fa-solid fa-id-badge"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Belum Melakukan Presensi Hari Ini</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs mb-3">
                                Silakan lakukan scan QR atau tap RFID untuk mencatat jam kedatangan.
                            </p>
                            <button type="button" onclick="openScanModal()" class="btn-primary cursor-pointer inline-flex items-center gap-2 text-xs">
                                <i class="fa-solid fa-qrcode"></i> Presensi Masuk Sekarang
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. Riwayat Presensi 5 Hari Terakhir -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Riwayat Kehadiran 5 Hari Terakhir</h2>
                    </div>
                    <a href="<?= BASEURL; ?>/rekapabsensi/guru" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                        Lihat Semua <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3 text-center">Datang</th>
                                <th class="px-4 py-3 text-center">Pulang</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php if (empty($riwayatList)): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                        Belum ada rekapan riwayat kehadiran sebelumnya.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayatList as $item): ?>
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors h-11">
                                        <td class="px-4 py-2 font-medium text-slate-900 dark:text-slate-100">
                                            <?= date('d M Y', strtotime($item->tanggal)) ?>
                                        </td>
                                        <td class="px-4 py-2 text-center font-mono font-medium text-emerald-600 dark:text-emerald-400 tabular-nums">
                                            <?= $item->jam_datang ? substr($item->jam_datang, 0, 5) : '&mdash;' ?>
                                        </td>
                                        <td class="px-4 py-2 text-center font-mono font-medium text-slate-700 dark:text-slate-300 tabular-nums">
                                            <?= $item->jam_pulang ? substr($item->jam_pulang, 0, 5) : '&mdash;' ?>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <?= BadgeHelper::render($item->status, strtoupper($item->status)) ?>
                                        </td>
                                        <td class="px-4 py-2 text-slate-500 dark:text-slate-400 max-w-[180px] truncate" title="<?= htmlspecialchars($item->keterangan ?? '') ?>">
                                            <?= htmlspecialchars($item->keterangan ?? '-') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ── RIGHT COLUMN (5 COLS): QR CODE & JADWAL KERJA ── -->
        <div class="lg:col-span-5 space-y-6">
            <!-- 1. QR Code Presensi Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">QR Code Presensi Pribadi</h2>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400">Kartu Guru</span>
                </div>
                <div class="p-5 text-center space-y-3">
                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 inline-block shadow-xs">
                        <div id="guruDashboardQrCode" class="flex justify-center"></div>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-slate-900 dark:text-white"><?= $guruNama ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">NIP: <?= $guruNip ?></p>
                        <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-mono font-medium border border-slate-200 dark:border-slate-700">
                            <i class="fa-solid fa-barcode text-emerald-600 dark:text-emerald-400"></i>
                            <span><?= $guruBarcode ?: '-' ?></span>
                        </div>
                    </div>

                    <div class="pt-1 flex justify-center gap-2">
                        <button type="button" onclick="downloadGuruQr()" class="btn-secondary text-xs px-3.5 py-1.5 inline-flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-download text-slate-400"></i> Unduh QR PNG
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. Jadwal Jam Kerja Hari Ini -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-business-time"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Jadwal Jam Kerja Hari Ini</h2>
                    </div>
                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400">
                        <?= date('l') ?>
                    </span>
                </div>
                <div class="p-5 space-y-2.5">
                    <?php if ($jadwalToday): ?>
                        <div class="flex items-center justify-between p-2.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-800 dark:text-slate-200">Mulai Buka Presensi</p>
                            </div>
                            <span class="font-mono text-xs font-semibold text-slate-800 dark:text-slate-200">
                                <?= $jadwalToday->jam_mulai_absensi ? substr($jadwalToday->jam_mulai_absensi, 0, 5) . ' WIB' : '06:30 WIB' ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-door-open"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-800 dark:text-slate-200">Jam Masuk Kerja</p>
                            </div>
                            <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <?= $jadwalToday->jam_datang ? substr($jadwalToday->jam_datang, 0, 5) . ' WIB' : '07:00 WIB' ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-800 dark:text-slate-200">Batas Toleransi Terlambat</p>
                            </div>
                            <span class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400">
                                <?= $jadwalToday->batas_terlambat ? substr($jadwalToday->batas_terlambat, 0, 5) . ' WIB' : '07:15 WIB' ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-person-walking-arrow-right"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-800 dark:text-slate-200">Jam Pulang Kerja</p>
                            </div>
                            <span class="font-mono text-xs font-semibold text-sky-600 dark:text-sky-400">
                                <?= $jadwalToday->jam_pulang ? substr($jadwalToday->jam_pulang, 0, 5) . ' WIB' : '15:30 WIB' ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <i class="fa-regular fa-calendar-xmark text-2xl mb-2 block"></i>
                            Tidak ada data jadwal kerja khusus hari ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Generator & Live Clock Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Generate QR Code Guru
    const qrContainer = document.getElementById('guruDashboardQrCode');
    const qrText = "<?= !empty($guruBarcode) ? $guruBarcode : 'GURU-' . ($data['guru_id'] ?? '0') ?>";
    if (qrContainer && typeof QRCode !== 'undefined') {
        new QRCode(qrContainer, {
            text: qrText,
            width: 140,
            height: 140,
            colorDark: "#0f172a",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }
});

function downloadGuruQr() {
    const img = document.querySelector('#guruDashboardQrCode img');
    if (img && img.src) {
        const a = document.createElement('a');
        a.href = img.src;
        a.download = "QR-Presensi-<?= !empty($guruBarcode) ? $guruBarcode : 'Guru' ?>.png";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    } else {
        const canvas = document.querySelector('#guruDashboardQrCode canvas');
        if (canvas) {
            const a = document.createElement('a');
            a.href = canvas.toDataURL("image/png");
            a.download = "QR-Presensi-<?= !empty($guruBarcode) ? $guruBarcode : 'Guru' ?>.png";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    }
}
</script>

<?php $this->view('templates/scan_modal'); ?>
<?php $this->view('templates/footer'); ?>