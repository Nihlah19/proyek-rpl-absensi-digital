<?php $this->view('templates/header', $data); ?>

<?php
$siswaObj     = $data['siswa'] ?? null;
$siswaNama    = htmlspecialchars($siswaObj->nama ?? SessionHelper::get('name') ?? 'Siswa');
$siswaNis     = htmlspecialchars($siswaObj->nis ?? '-');
$siswaKelas   = htmlspecialchars($data['kelasNama'] ?? 'Kelas belum ditentukan');
$rfidUid      = htmlspecialchars($siswaObj->rfid_uid ?? '');
$rfidStatus   = strtolower($siswaObj->rfid_status ?? 'inactive');

$absensiToday = $data['absensiHariIni'] ?? null;
$statsBulan   = $data['statsBulanan'] ?? null;
$jadwalToday  = $data['jadwalHariIni'] ?? null;
$riwayatList  = $data['riwayatSiswa'] ?? [];
$isPkl        = $data['isPkl'] ?? false;
$lokasiPkl    = $data['lokasiPkl'] ?? null;

$totalHadirBulan = ((int)($statsBulan->hadir ?? 0)) + ((int)($statsBulan->terlambat ?? 0)) + ((int)($statsBulan->pkl ?? 0));
$totalTepatWaktu = (int)($statsBulan->hadir ?? 0);
$totalTerlambat  = (int)($statsBulan->terlambat ?? 0);
$totalIzinSakit  = (int)($statsBulan->izin_sakit ?? 0);
?>

<div id="pageContent" class="w-full space-y-6">
    <!-- ── PAGE HEADER (CLEAN & HUMBLE) ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-200/80 dark:border-slate-800">
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold <?= $isPkl ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/40' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40' ?>">
                    <i class="fa-solid <?= $isPkl ? 'fa-briefcase text-[10px]' : 'fa-graduation-cap text-[10px]' ?>"></i>
                    <?= $siswaKelas ?> <?= $isPkl ? '(PKL)' : '' ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Halo, <?= $siswaNama ?>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                NIS: <span class="font-mono text-slate-700 dark:text-slate-300"><?= $siswaNis ?></span> &middot; Portal presensi siswa & riwayat kehadiran
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap self-start sm:self-center">
            <?php if ($isPkl): ?>
                <a href="<?= BASEURL; ?>/absensi/pkl" class="px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-camera"></i> Presensi PKL
                </a>
            <?php endif; ?>
            <a href="<?= BASEURL; ?>/absensi/siswa" class="btn-secondary text-xs">
                <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Riwayat Absensi
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <?php if ($isPkl): ?>
    <!-- ── PKL BANNER NOTIFICATION ── -->
    <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/70 dark:border-indigo-900/60 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base shrink-0">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-indigo-950 dark:text-indigo-200">Status Praktik Kerja Lapangan (PKL)</h3>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">Aktif</span>
                </div>
                <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-0.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot"></i>
                    <?= $lokasiPkl ? htmlspecialchars($lokasiPkl->nama_tempat) : 'Lokasi tempat PKL belum diatur' ?>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= BASEURL; ?>/absensi/pkl" class="px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors shadow-xs flex items-center gap-1.5">
                <span>Buka Presensi PKL</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── 4 KPI METRICS ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Status Hari Ini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
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
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <span><?= $absensiToday && $absensiToday->jam_datang ? 'Masuk: ' . date('H:i', strtotime($absensiToday->jam_datang)) . ' WIB' : 'Mesin RFID Ready' ?></span>
                <i class="fa-solid fa-fingerprint text-slate-400"></i>
            </div>
        </div>

        <!-- Card 2: Kehadiran Bulan Ini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
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
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <span>Tepat: <strong class="font-mono text-emerald-600 dark:text-emerald-400"><?= $totalTepatWaktu ?></strong> hr</span>
                <span class="font-mono text-slate-600 dark:text-slate-300 font-semibold text-[11px]"><?= date('M Y') ?></span>
            </div>
        </div>

        <!-- Card 3: Terlambat Bulan Ini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Terlambat Bulan Ini</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400 font-mono tracking-tight tabular-nums">
                            <?= $totalTerlambat ?>
                        </span>
                        <span class="text-xs text-slate-400 font-sans">Kali</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-sm">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <span>Toleransi Jam</span>
                <span class="font-mono font-semibold <?= $totalTerlambat > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' ?>">
                    <?= $totalTerlambat > 0 ? 'Perlu Disiplin' : 'Nihil' ?>
                </span>
            </div>
        </div>

        <!-- Card 4: Izin & Sakit -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Izin & Sakit</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-sky-600 dark:text-sky-400 font-mono tracking-tight tabular-nums">
                            <?= $totalIzinSakit ?>
                        </span>
                        <span class="text-xs text-slate-400 font-sans">Hari</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 text-sm">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <span>Pengajuan Surat</span>
                <span class="font-mono text-sky-600 dark:text-sky-400 font-semibold text-[11px]">Terverifikasi</span>
            </div>
        </div>
    </div>

    <!-- ── MAIN CONTENT (2 COLUMNS: 7 / 5) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- ── LEFT COLUMN (7 COLS): PRESENSI HARI INI & RIWAYAT ── -->
        <div class="lg:col-span-7 space-y-6">
            <!-- 1. Presensi Hari Ini Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Status Presensi Siswa Hari Ini</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        <i class="fa-solid fa-satellite-dish text-emerald-600 dark:text-emerald-400 text-[10px]"></i> Live Sync
                    </span>
                </div>

                <div class="p-5">
                    <?php if ($absensiToday): ?>
                        <?php
                        $statusAbsen = strtolower($absensiToday->status ?? '');
                        $jamDatang = $absensiToday->jam_datang ? date('H:i', strtotime($absensiToday->jam_datang)) : '-';
                        $jamPulang = $absensiToday->jam_pulang ? date('H:i', strtotime($absensiToday->jam_pulang)) : '-';
                        ?>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 flex-wrap gap-2">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Status Kehadiran Hari Ini:</span>
                                <div class="flex items-center gap-2">
                                    <?= BadgeHelper::render($absensiToday->status, strtoupper($absensiToday->status)) ?>
                                    <?php if (!empty($absensiToday->keterangan)): ?>
                                        <span class="text-xs text-slate-500 dark:text-slate-400 italic">
                                            (<?= htmlspecialchars($absensiToday->keterangan) ?>)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Jam Masuk -->
                                <div class="p-3.5 rounded-lg bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jam Masuk (Datang)</span>
                                        <div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                        </div>
                                    </div>
                                    <p class="text-xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                                        <?= $jamDatang !== '-' ? $jamDatang . ' <span class="text-xs font-sans text-slate-400 font-normal">WIB</span>' : '<span class="text-slate-400">-</span>' ?>
                                    </p>
                                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1 font-medium">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Presensi masuk terekam
                                    </p>
                                </div>

                                <!-- Jam Pulang -->
                                <div class="p-3.5 rounded-lg bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jam Kepulangan</span>
                                        <div class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                        </div>
                                    </div>
                                    <p class="text-xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                                        <?= $jamPulang !== '-' ? $jamPulang . ' <span class="text-xs font-sans text-slate-400 font-normal">WIB</span>' : '<span class="text-slate-400">-</span>' ?>
                                    </p>
                                    <p class="text-[11px] <?= $jamPulang !== '-' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?> mt-1 flex items-center gap-1 font-medium">
                                        <i class="fa-solid <?= $jamPulang !== '-' ? 'fa-circle-check' : 'fa-clock' ?> text-[10px]"></i>
                                        <?= $jamPulang !== '-' ? 'Presensi pulang terekam' : 'Belum scan pulang' ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (!$absensiToday->jam_pulang && in_array($statusAbsen, ['hadir', 'terlambat'])): ?>
                                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/40 rounded-lg flex items-center gap-2.5 text-xs text-amber-800 dark:text-amber-300">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400"></i>
                                    <span>Anda belum melakukan presensi pulang hari ini.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Belum Presensi -->
                        <div class="text-center py-8 px-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl mx-auto mb-2.5">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Belum Ada Presensi Hari Ini</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs mb-3">
                                Silakan lakukan tap kartu RFID atau presensi melalui petugas di gerbang.
                            </p>
                            <a href="<?= BASEURL; ?>/absensi/siswa" class="btn-primary inline-flex items-center gap-2 text-xs">
                                <i class="fa-solid fa-clock-rotate-left"></i> Lihat Riwayat Kehadiran
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. Pengajuan Pulang Cepat Status (If exists) -->
            <?php if (isset($data['pengajuanHariIni']) && $data['pengajuanHariIni'] && $data['pengajuanHariIni']->status !== 'DRAFT'): ?>
                <?php $pStatus = strtoupper($data['pengajuanHariIni']->status); ?>
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-person-walking-arrow-right"></i>
                            </div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Status Permohonan Pulang Cepat</h2>
                        </div>
                        <?= BadgeHelper::render($pStatus, $pStatus) ?>
                    </div>
                    <div class="p-5">
                        <div class="p-3.5 bg-slate-50/70 dark:bg-slate-900/50 rounded-lg border border-slate-200/80 dark:border-slate-800 space-y-2.5">
                            <div>
                                <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Alasan Kepulangan:</p>
                                <p class="text-xs font-semibold text-slate-900 dark:text-slate-100 mt-1">
                                    <?= htmlspecialchars($data['pengajuanHariIni']->alasan) ?>
                                </p>
                            </div>
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-2 text-xs font-mono text-slate-500 dark:text-slate-400">
                                <span><i class="fa-regular fa-clock mr-1 text-emerald-600 dark:text-emerald-400"></i> Diajukan: <?= date('H:i', strtotime($data['pengajuanHariIni']->jam_pengajuan)) ?> WIB</span>
                                <span>Status: <strong class="<?= $pStatus === 'APPROVED' ? 'text-emerald-600 dark:text-emerald-400' : ($pStatus === 'REJECTED' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400') ?>"><?= $pStatus ?></strong></span>
                            </div>
                            <?php if ($pStatus === 'REJECTED' && !empty($data['pengajuanHariIni']->rejected_reason)): ?>
                                <div class="p-2.5 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-md text-xs border border-rose-200 dark:border-rose-800/40 mt-1.5">
                                    <strong>Catatan Wali Kelas:</strong> <?= htmlspecialchars($data['pengajuanHariIni']->rejected_reason) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 3. Riwayat Presensi 5 Hari Terakhir -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Riwayat Kehadiran 5 Hari Terakhir</h2>
                    </div>
                    <a href="<?= BASEURL; ?>/absensi/siswa" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
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
                                        <td class="px-4 py-2 text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title="<?= htmlspecialchars($item->keterangan ?? '') ?>">
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

        <!-- ── RIGHT COLUMN (5 COLS): KARTU SISWA & JAM SEKOLAH ── -->
        <div class="lg:col-span-5 space-y-6">
            <!-- 1. Kartu Identitas Siswa & RFID -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-id-card-clip"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Kartu Pelajar &amp; RFID</h2>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400">Digital ID</span>
                </div>
                <div class="p-5 space-y-3.5">
                    <!-- Student Card Badge -->
                    <div class="relative overflow-hidden rounded-xl p-4 text-white shadow-xs bg-[#163300] dark:bg-slate-800 border border-emerald-950 dark:border-slate-700">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-[#9fe870]">Kartu Pelajar Digital</span>
                                <h3 class="text-base font-bold text-white mt-1 leading-snug"><?= $siswaNama ?></h3>
                                <p class="text-xs text-slate-300 font-mono mt-0.5">NIS: <?= $siswaNis ?></p>
                            </div>
                            <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-[#9fe870] text-base border border-white/20">
                                <i class="fa-solid fa-microchip"></i>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between text-xs">
                            <div>
                                <p class="text-[10px] text-slate-300 uppercase tracking-wider">Kelas</p>
                                <p class="font-bold text-[#9fe870]"><?= $siswaKelas ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-300 uppercase tracking-wider">Status RFID</p>
                                <?php if (!empty($rfidUid)): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-mono text-[#9fe870] font-semibold">
                                        <i class="fa-solid fa-check text-[10px]"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-mono text-amber-300 font-semibold">
                                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i> Belum Terdaftar
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Info -->
                    <div class="p-3.5 rounded-lg bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">UID Kartu RFID:</span>
                            <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">
                                <?= !empty($rfidUid) ? $rfidUid : '<span class="text-slate-400 italic">Belum dipindai</span>' ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400">No. Kontak Orang Tua:</span>
                            <span class="font-mono font-semibold text-slate-900 dark:text-slate-100">
                                <?= htmlspecialchars($siswaObj->no_hp_ortu ?? '-') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Jam Operasional & Aturan Kehadiran Hari Ini -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Jadwal Sekolah Hari Ini</h2>
                    </div>
                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400">
                        <?= date('l') ?>
                    </span>
                </div>
                <div class="p-5 space-y-2.5">
                    <?php if ($jadwalToday): ?>
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-door-open"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-900 dark:text-slate-100">Batas Masuk Tepat Waktu</p>
                            </div>
                            <span class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <?= $jadwalToday->jam_datang ? substr($jadwalToday->jam_datang, 0, 5) . ' WIB' : '07:00 WIB' ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-900 dark:text-slate-100">Batas Keterlambatan</p>
                            </div>
                            <span class="font-mono text-xs font-semibold text-amber-600 dark:text-amber-400">
                                <?= $jadwalToday->batas_terlambat ? substr($jadwalToday->batas_terlambat, 0, 5) . ' WIB' : '07:15 WIB' ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-md bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-person-walking-arrow-right"></i>
                                </div>
                                <p class="text-xs font-medium text-slate-900 dark:text-slate-100">Jam Kepulangan Resmi</p>
                            </div>
                            <span class="font-mono text-xs font-semibold text-sky-600 dark:text-sky-400">
                                <?= $jadwalToday->jam_pulang ? substr($jadwalToday->jam_pulang, 0, 5) . ' WIB' : '15:30 WIB' ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 text-slate-400 text-xs">
                            <i class="fa-regular fa-calendar-xmark text-2xl mb-2 block"></i>
                            Tidak ada jadwal operasional khusus hari ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<?php if (isset($data['butuh_alasan_pulang']) && $data['butuh_alasan_pulang'] && (!isset($data['pengajuanHariIni']) || !$data['pengajuanHariIni'] || $data['pengajuanHariIni']->status === 'DRAFT')): ?>
<!-- MODAL ALASAN PULANG CEPAT (BLOCKING) -->
<div id="modalAlasanPulang" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md animate-fade-in" id="modalBoxPulang">
    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between rounded-t-2xl">
      <div class="flex items-center gap-2.5">
          <i class="fa-solid fa-clock text-amber-600 dark:text-amber-400 text-base"></i>
          <h3 class="text-base font-bold text-slate-900 dark:text-white">Pulang Lebih Awal</h3>
      </div>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1 rounded-lg">
          <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>
    <div class="p-6">
      <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
        Anda terdeteksi melakukan scan pulang sebelum jam jadwal kepulangan resmi (Pukul <span class="font-mono font-semibold text-slate-900 dark:text-white"><?= date('H:i', strtotime($data['butuh_alasan_pulang']->jadwal_pulang)) ?> WIB</span>). Mohon lengkapi permohonan alasan:
      </p>

      <form id="formAlasanPulang">
        <input type="hidden" id="absensiId" value="<?= $data['butuh_alasan_pulang']->id ?>">
        
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2.5">Pilih Alasan Kepulangan:</p>
        <div class="grid grid-cols-3 gap-2.5 mb-4">
          <label class="alasan-btn cursor-pointer">
            <input type="radio" name="alasan_kategori" value="izin" class="sr-only">
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-center hover:border-emerald-500 transition-all text-xs font-semibold text-slate-600 dark:text-slate-300 alasan-option" data-val="izin">
              <i class="fa-solid fa-file-signature text-xl mb-1.5 text-sky-600 dark:text-sky-400 block"></i>Izin
            </div>
          </label>
          <label class="alasan-btn cursor-pointer">
            <input type="radio" name="alasan_kategori" value="sakit" class="sr-only">
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-center hover:border-emerald-500 transition-all text-xs font-semibold text-slate-600 dark:text-slate-300 alasan-option" data-val="sakit">
              <i class="fa-solid fa-notes-medical text-xl mb-1.5 text-rose-600 dark:text-rose-400 block"></i>Sakit
            </div>
          </label>
          <label class="alasan-btn cursor-pointer">
            <input type="radio" name="alasan_kategori" value="lainnya" class="sr-only">
            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 text-center hover:border-emerald-500 transition-all text-xs font-semibold text-slate-600 dark:text-slate-300 alasan-option" data-val="lainnya">
              <i class="fa-solid fa-ellipsis text-xl mb-1.5 text-slate-500 dark:text-slate-400 block"></i>Lainnya
            </div>
          </label>
        </div>
        
        <textarea id="alasanDetail"
          class="vh-input w-full p-3 resize-none text-xs"
          rows="3" placeholder="Keterangan rincian alasan pulang (wajib jika memilih 'Lainnya')..."></textarea>
      </form>
      
      <div id="errorMsg" class="hidden text-xs text-rose-600 dark:text-rose-400 mt-2 font-medium"></div>
    </div>

    <div class="px-6 pb-6 flex justify-end gap-2.5">
      <button onclick="closeModal()" class="vh-btn-secondary">
          Batal
      </button>
      <button id="btnKirimAlasan" onclick="kirimAlasan()" class="vh-btn-primary">
          Kirim Permohonan
      </button>
    </div>
  </div>
</div>

<style>
.alasan-option.selected {
  border-color: #059669 !important;
  background-color: #ecfdf5 !important;
  color: #065f46 !important;
  box-shadow: 0 0 0 1px #059669;
}
.dark .alasan-option.selected {
  border-color: #10b981 !important;
  background-color: rgba(6, 78, 59, 0.4) !important;
  color: #a7f3d0 !important;
  box-shadow: 0 0 0 1px #10b981;
}
</style>
<script>
// Main TTS logic
window.addEventListener('load', () => {
    if ('speechSynthesis' in window) {
        const msg = new SpeechSynthesisUtterance("alasan pulang cepat hari ini");
        msg.lang = 'id-ID';
        msg.rate = 1.0;
        window.speechSynthesis.speak(msg);
        
        const playOnInteract = () => {
            if (!window.speechSynthesis.speaking) {
                window.speechSynthesis.speak(msg);
            }
            document.removeEventListener('click', playOnInteract);
        };
        document.addEventListener('click', playOnInteract);
    }
});

// Alasan pill selection
document.querySelectorAll('.alasan-btn').forEach(label => {
  label.addEventListener('click', () => {
    document.querySelectorAll('.alasan-option').forEach(el => el.classList.remove('selected'));
    label.querySelector('.alasan-option').classList.add('selected');
    label.querySelector('input').checked = true;
  });
});

function closeModal() {
    document.getElementById('modalAlasanPulang').classList.add('hidden');
}

async function kirimAlasan() {
    const absensiId = document.getElementById('absensiId').value;
    const checked = document.querySelector('input[name="alasan_kategori"]:checked');
    const detail = document.getElementById('alasanDetail').value.trim();
    const errorMsg = document.getElementById('errorMsg');
    
    if (!checked) {
        errorMsg.textContent = 'Harap pilih salah satu alasan (Izin/Sakit/Lainnya).';
        errorMsg.classList.remove('hidden');
        return;
    }
    
    const kategori = checked.value;
    if (kategori === 'lainnya' && !detail) {
        errorMsg.textContent = 'Harap isi keterangan tambahan jika memilih "Lainnya".';
        errorMsg.classList.remove('hidden');
        return;
    }
    
    errorMsg.classList.add('hidden');
    
    const btn = document.getElementById('btnKirimAlasan');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
    
    try {
        const res = await fetch('<?= BASEURL ?>/api/v1/submit_alasan_pulang_siswa.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                absensi_id: absensiId,
                alasan_kategori: kategori,
                alasan_detail: detail
            })
        });
        const json = await res.json();
        
        if (json.ok) {
            alert('Alasan berhasil dikirim dan menunggu persetujuan wali kelas.');
            location.reload();
        } else {
            errorMsg.textContent = json.message || 'Terjadi kesalahan.';
            errorMsg.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = 'Kirim Permohonan';
        }
    } catch (e) {
        errorMsg.textContent = 'Gagal menghubungi server.';
        errorMsg.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = 'Kirim Permohonan';
    }
}
</script>
<?php endif; ?>

<?php $this->view('templates/footer'); ?>