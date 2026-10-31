<?php
// Helper map status
$statusMap = [
    'hadir'     => ['singkat' => 'H', 'label' => 'Hadir',     'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
    'terlambat' => ['singkat' => 'T', 'label' => 'Terlambat', 'color' => 'bg-amber-100 text-amber-800 border-amber-200'],
    'izin'      => ['singkat' => 'I', 'label' => 'Izin',      'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
    'sakit'     => ['singkat' => 'S', 'label' => 'Sakit',     'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
    'alfa'      => ['singkat' => 'A', 'label' => 'Alfa',      'color' => 'bg-rose-100 text-rose-800 border-rose-200'],
    'pkl'       => ['singkat' => 'PKL', 'label' => 'PKL',     'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
];

$role         = $data['role'] ?? '';
$tipe         = $data['tipe'] ?? 'siswa';
$viewMode     = $data['viewMode'] ?? 'ringkasan';
$filters      = $data['filters'] ?? [];
$stats        = $data['stats'] ?? [];
$akumulasiMap = $data['akumulasiMap'] ?? [];

// Siapkan parameter URL untuk download CSV dan pagination
$csvParams = [
    'tipe'   => $tipe,
    'view'   => $viewMode,
    'dari'   => $filters['dari'] ?? '',
    'sampai' => $filters['sampai'] ?? '',
];
if (!empty($filters['status']))   $csvParams['status']   = $filters['status'];
if (!empty($filters['search']))   $csvParams['search']   = $filters['search'];
if (!empty($filters['kelas_id'])) $csvParams['kelas_id'] = $filters['kelas_id'];
$csvUrl = BASEURL . '/rekapabsensi/download?' . http_build_query($csvParams);

// URL generator untuk switch view mode
$switchViewParams = $filters;
unset($switchViewParams['limit'], $switchViewParams['offset']);
$switchViewParams['tipe'] = $tipe;

$switchViewParams['view'] = 'ringkasan';
$urlViewRingkasan = BASEURL . '/rekapabsensi?' . http_build_query($switchViewParams);

$switchViewParams['view'] = 'detail';
$urlViewDetail = BASEURL . '/rekapabsensi?' . http_build_query($switchViewParams);
?>

<div class="w-full space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                <?php if ($tipe === 'guru'): ?>
                    Rekap Kehadiran Guru
                <?php elseif (in_array($role, ['wali_kelas', 'walikelas']) && !empty($data['waliKelasInfo'])): ?>
                    Rekap Kehadiran Kelas <?= htmlspecialchars($data['waliKelasInfo']->nama_kelas) ?>
                <?php else: ?>
                    Rekap Kehadiran Siswa
                <?php endif; ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                <?php if ($role === 'kepsek'): ?>
                    Rekapitulasi kehadiran seluruh dewan guru untuk evaluasi dan pemantauan kinerja.
                <?php elseif (in_array($role, ['wali_kelas', 'walikelas'])): ?>
                    <?= !empty($data['waliKelasInfo']) ? 'Data kehadiran khusus siswa kelas ' . htmlspecialchars($data['waliKelasInfo']->nama_kelas) . ' untuk rekap raport & penilaian.' : 'Akun Anda belum terhubung dengan kelas binaan.' ?>
                <?php else: ?>
                    <?= $viewMode === 'ringkasan' ? 'Rekapitulasi akumulasi kehadiran untuk penilaian dan raport' : 'Log riwayat kehadiran harian per tanggal' ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= $csvUrl ?>"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-file-csv"></i> Download CSV
            </a>
            <a href="<?= BASEURL ?>/dashboard"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white text-slate-600 border border-slate-200 rounded-lg text-xs font-semibold hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if (in_array($role, ['wali_kelas', 'walikelas']) && empty($data['waliKelasInfo'])): ?>
    <div class="p-4 mb-6 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-base text-amber-600"></i>
        <span>Akun Anda belum terhubung dengan kelas binaan manapun. Hubungi administrator untuk mengatur penugasan wali kelas.</span>
    </div>
    <?php endif; ?>

    <!-- TABS KHUSUS ADMIN (PILIH TIPE: SISWA / GURU) -->
    <?php if ($role === 'admin'): ?>
    <div class="flex items-center gap-2 border-b border-slate-200 mb-6">
        <?php
            $tabQuerySiswa = http_build_query([
                'tipe'   => 'siswa',
                'view'   => $viewMode,
                'dari'   => $filters['dari'],
                'sampai' => $filters['sampai'],
            ]);
            $tabQueryGuru = http_build_query([
                'tipe'   => 'guru',
                'view'   => $viewMode,
                'dari'   => $filters['dari'],
                'sampai' => $filters['sampai'],
            ]);
        ?>
        <a href="<?= BASEURL ?>/rekapabsensi?<?= $tabQuerySiswa ?>"
           class="px-4 py-2.5 text-xs sm:text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 <?= $tipe === 'siswa' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Data Siswa</span>
        </a>
        <a href="<?= BASEURL ?>/rekapabsensi?<?= $tabQueryGuru ?>"
           class="px-4 py-2.5 text-xs sm:text-sm font-semibold border-b-2 transition-colors flex items-center gap-2 <?= $tipe === 'guru' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Data Guru</span>
        </a>
    </div>
    <?php endif; ?>

    <!-- STATISTIK RINGKASAN -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-medium">Hadir</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= $stats['hadir'] ?? 0 ?></div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-medium">Terlambat</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= $stats['terlambat'] ?? 0 ?></div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-medium">Izin</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= $stats['izin'] ?? 0 ?></div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-medium">Sakit</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= $stats['sakit'] ?? 0 ?></div>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-medium">Alfa</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= $stats['alfa'] ?? 0 ?></div>
        </div>
        <?php if ($tipe === 'siswa'): ?>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-medium">PKL</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= $stats['pkl'] ?? 0 ?></div>
        </div>
        <?php else: ?>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200">
            <span class="text-xs text-slate-500 font-medium">Total Guru</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1"><?= $stats['total_guru'] ?? 0 ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- FILTER CARD LENGKAP -->
    <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 mb-6">
        <form method="GET" action="<?= BASEURL ?>/rekapabsensi" class="space-y-4">
            <input type="hidden" name="tipe" value="<?= htmlspecialchars($tipe) ?>">
            <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <!-- Dari Tanggal -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Dari Tanggal</label>
                    <input type="date" name="dari" value="<?= htmlspecialchars($filters['dari']) ?>"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                </div>

                <!-- Sampai Tanggal -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="<?= htmlspecialchars($filters['sampai']) ?>"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                </div>

                <!-- OPSI FILTER KELAS (HANYA MUNCUL DI TAB SISWA UNTUK ADMIN) -->
                <?php if ($tipe === 'siswa' && $role === 'admin'): ?>
                <div class="lg:col-span-3">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">
                        <i class="fa-solid fa-filter text-emerald-600 mr-1"></i> Filter Kelas
                    </label>
                    <select name="kelas_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                        <option value="">-- Semua Kelas --</option>
                        <?php if (!empty($data['kelasList'])): ?>
                            <?php foreach ($data['kelasList'] as $k): ?>
                                <option value="<?= $k->id ?>" <?= ($filters['kelas_id'] == $k->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k->nama_kelas) ?> (<?= htmlspecialchars($k->tingkat ?? '') ?> <?= htmlspecialchars($k->jurusan ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Filter Status (Hanya pada Mode Detail) -->
                <?php if ($viewMode === 'detail'): ?>
                <div class="<?= ($tipe === 'siswa' && $role === 'admin') ? 'lg:col-span-2' : 'lg:col-span-3' ?>">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Status Absensi</label>
                    <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                        <option value="">-- Semua Status --</option>
                        <option value="hadir"     <?= ($filters['status'] === 'hadir')     ? 'selected' : '' ?>>Hadir (H)</option>
                        <option value="terlambat" <?= ($filters['status'] === 'terlambat') ? 'selected' : '' ?>>Terlambat (T)</option>
                        <option value="izin"      <?= ($filters['status'] === 'izin')      ? 'selected' : '' ?>>Izin (I)</option>
                        <option value="sakit"     <?= ($filters['status'] === 'sakit')     ? 'selected' : '' ?>>Sakit (S)</option>
                        <option value="alfa"      <?= ($filters['status'] === 'alfa')      ? 'selected' : '' ?>>Alfa (A)</option>
                        <?php if ($tipe === 'siswa'): ?>
                        <option value="pkl"       <?= ($filters['status'] === 'pkl')       ? 'selected' : '' ?>>PKL (PKL)</option>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Pencarian Nama / NIS / NIP -->
                <div class="<?= ($viewMode === 'detail') ? (($tipe === 'siswa' && $role === 'admin') ? 'lg:col-span-3' : 'lg:col-span-5') : (($tipe === 'siswa' && $role === 'admin') ? 'lg:col-span-5' : 'lg:col-span-8') ?>">
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">
                        <?= $tipe === 'guru' ? 'Cari Nama Guru / NIP' : 'Cari Nama Siswa / NIS' ?>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>"
                               placeholder="<?= $tipe === 'guru' ? 'Ketik nama guru atau NIP...' : 'Ketik nama siswa atau NIS...' ?>"
                               class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all outline-none">
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Filter & Limit Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs text-slate-500 font-semibold">Tampilkan:</span>
                    <select name="limit" onchange="this.form.submit()" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:ring-1 focus:ring-emerald-500 outline-none">
                        <option value="15"  <?= ($data['limit'] == 15)  ? 'selected' : '' ?>>15 baris</option>
                        <option value="25"  <?= ($data['limit'] == 25)  ? 'selected' : '' ?>>25 baris</option>
                        <option value="50"  <?= ($data['limit'] == 50)  ? 'selected' : '' ?>>50 baris</option>
                        <option value="100" <?= ($data['limit'] == 100) ? 'selected' : '' ?>>100 baris</option>
                    </select>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <a href="<?= BASEURL ?>/rekapabsensi?tipe=<?= $tipe ?>&view=<?= $viewMode ?>"
                       class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                    </a>
                    <button type="submit"
                            class="px-5 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold hover:bg-slate-900 transition-all shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-filter"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- VIEW MODE SWITCHER & LEGENDA STATUS -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <!-- Switcher Mode Tampilan -->
        <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200">
            <a href="<?= $urlViewRingkasan ?>"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2 transition-colors <?= $viewMode === 'ringkasan' ? 'bg-white text-slate-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">
                <i class="fa-solid fa-table text-emerald-600"></i>
                <span>Rekap Akumulasi</span>
            </a>
            <a href="<?= $urlViewDetail ?>"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2 transition-colors <?= $viewMode === 'detail' ? 'bg-white text-slate-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' ?>">
                <i class="fa-solid fa-list text-blue-600"></i>
                <span>Log Harian</span>
            </a>
        </div>

        <!-- Legenda Status Singkat -->
        <div class="flex flex-wrap gap-2 items-center text-xs text-slate-500">
            <span class="font-medium">Keterangan:</span>
            <span class="font-semibold text-emerald-700">H = Hadir</span> &bull;
            <span class="font-semibold text-amber-700">T = Telat</span> &bull;
            <span class="font-semibold text-blue-700">I = Izin</span> &bull;
            <span class="font-semibold text-purple-700">S = Sakit</span> &bull;
            <span class="font-semibold text-rose-700">A = Alfa</span>
            <?php if ($tipe === 'siswa'): ?>
            &bull; <span class="font-semibold text-indigo-700">PKL</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- TABLE AREA -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <?php if ($viewMode === 'ringkasan'): ?>
            <!-- ================= TABLE MODE RINGKASAN RAPORT (1 BARIS = 1 SISWA/GURU) ================= -->
            <table class="w-full min-w-[780px] text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-200 font-semibold tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <?php if ($tipe === 'guru'): ?>
                            <th class="px-4 py-3.5">NIP</th>
                            <th class="px-4 py-3.5">Nama Guru</th>
                        <?php else: ?>
                            <th class="px-4 py-3.5">NIS</th>
                            <th class="px-4 py-3.5">Nama Siswa</th>
                            <th class="px-4 py-3.5">Kelas</th>
                        <?php endif; ?>
                        <th class="px-3 py-3.5 text-center text-emerald-800 font-bold" title="Hadir Tepat Waktu">H</th>
                        <th class="px-3 py-3.5 text-center text-amber-800 font-bold" title="Terlambat">T</th>
                        <th class="px-3 py-3.5 text-center text-blue-800 font-bold" title="Izin">I</th>
                        <th class="px-3 py-3.5 text-center text-purple-800 font-bold" title="Sakit">S</th>
                        <th class="px-3 py-3.5 text-center text-rose-800 font-bold" title="Alfa">A</th>
                        <?php if ($tipe === 'siswa'): ?>
                        <th class="px-3 py-3.5 text-center text-indigo-800 font-bold" title="Praktik Kerja Lapangan">PKL</th>
                        <?php endif; ?>
                        <th class="px-4 py-3.5 text-center font-semibold">Total Masuk</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Kehadiran (%)</th>
                        <th class="px-4 py-3.5 text-center font-semibold">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($data['dataAbsensi'])): ?>
                    <tr>
                        <td colspan="13" class="px-6 py-14 text-center text-slate-400">
                            <i class="fa-solid fa-user-xmark text-4xl mb-3 text-slate-300 block"></i>
                            <p class="font-bold text-slate-600">Tidak ada data siswa/guru yang sesuai kriteria filter</p>
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

                            // Predikat Kehadiran untuk Rapor
                            $predikat = 'Sangat Baik';
                            $predColor = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                            if ($a >= 3 || $pct < 75) {
                                $predikat = 'Perlu Perhatian';
                                $predColor = 'bg-rose-100 text-rose-800 border-rose-200';
                            } elseif ($a > 0 || $pct < 85) {
                                $predikat = 'Cukup';
                                $predColor = 'bg-amber-100 text-amber-800 border-amber-200';
                            } elseif ($pct < 95) {
                                $predikat = 'Baik';
                                $predColor = 'bg-blue-100 text-blue-800 border-blue-200';
                            }
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-mono text-xs"><?= $no++ ?></td>
                            <?php if ($tipe === 'guru'): ?>
                                <td class="px-4 py-3.5 font-mono text-xs text-slate-500"><?= htmlspecialchars($row->nip ?? '-') ?></td>
                                <td class="px-4 py-3.5 font-bold text-slate-800"><?= htmlspecialchars($row->name) ?></td>
                            <?php else: ?>
                                <td class="px-4 py-3.5 font-mono text-xs text-slate-500"><?= htmlspecialchars($row->nis ?? '-') ?></td>
                                <td class="px-4 py-3.5 font-bold text-slate-800"><?= htmlspecialchars($row->name) ?></td>
                                <td class="px-4 py-3.5 text-xs text-slate-600 font-semibold whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200/60">
                                        <?= htmlspecialchars($row->nama_kelas ?? '-') ?>
                                    </span>
                                </td>
                            <?php endif; ?>

                            <!-- Jumlah Hadir (H) -->
                            <td class="px-3 py-3.5 text-center bg-emerald-50/30">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-1.5 rounded-lg text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <?= $h ?>
                                </span>
                            </td>

                            <!-- Jumlah Terlambat (T) -->
                            <td class="px-3 py-3.5 text-center bg-amber-50/30">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-1.5 rounded-lg text-xs font-black bg-amber-100 text-amber-800 border border-amber-200">
                                    <?= $t ?>
                                </span>
                            </td>

                            <!-- Jumlah Izin (I) -->
                            <td class="px-3 py-3.5 text-center bg-blue-50/30">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-1.5 rounded-lg text-xs font-black bg-blue-100 text-blue-800 border border-blue-200">
                                    <?= $i ?>
                                </span>
                            </td>

                            <!-- Jumlah Sakit (S) -->
                            <td class="px-3 py-3.5 text-center bg-purple-50/30">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-1.5 rounded-lg text-xs font-black bg-purple-100 text-purple-800 border border-purple-200">
                                    <?= $s ?>
                                </span>
                            </td>

                            <!-- Jumlah Alfa (A) -->
                            <td class="px-3 py-3.5 text-center bg-rose-50/30">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-1.5 rounded-lg text-xs font-bold <?= $a > 0 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'text-slate-400' ?>">
                                    <?= $a ?>
                                </span>
                            </td>

                            <!-- Jumlah PKL (Khusus Siswa) -->
                            <?php if ($tipe === 'siswa'): ?>
                            <td class="px-3 py-3.5 text-center bg-indigo-50/30">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-1.5 rounded-lg text-xs font-black bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    <?= $pkl ?>
                                </span>
                            </td>
                            <?php endif; ?>

                            <!-- Total Masuk -->
                            <td class="px-4 py-3.5 text-center font-bold text-slate-700">
                                <?= $totalMasuk ?> <span class="text-[10px] text-slate-400 font-normal">/ <?= $totalHari ?> hari</span>
                            </td>

                            <!-- Persentase Kehadiran -->
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200/60 hidden sm:block">
                                        <div class="h-full <?= $pct >= 85 ? 'bg-emerald-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-rose-500') ?>" style="width: <?= min(100, $pct) ?>%"></div>
                                    </div>
                                    <span class="font-bold text-xs <?= $pct >= 85 ? 'text-emerald-700' : ($pct >= 75 ? 'text-amber-700' : 'text-rose-700') ?>">
                                        <?= $pct ?>%
                                    </span>
                                </div>
                            </td>

                            <!-- Predikat Raport -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border <?= $predColor ?>">
                                    <?= $predikat ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php else: ?>
            <!-- ================= TABLE MODE DETAIL LOG HARIAN ================= -->
            <table class="w-full min-w-[750px] text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-600 uppercase bg-slate-50 border-b border-slate-200 font-semibold tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Tanggal</th>
                        <?php if ($tipe === 'guru'): ?>
                            <th class="px-4 py-3.5">NIP</th>
                            <th class="px-4 py-3.5">Nama Guru</th>
                        <?php else: ?>
                            <th class="px-4 py-3.5">NIS</th>
                            <th class="px-4 py-3.5">Nama Siswa</th>
                            <th class="px-4 py-3.5">Kelas</th>
                        <?php endif; ?>
                        <th class="px-4 py-3.5 text-center">Datang</th>
                        <th class="px-4 py-3.5 text-center">Pulang</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5">Keterangan</th>
                        <th class="px-4 py-3.5">Catatan</th>
                        <th class="px-4 py-3.5 whitespace-nowrap text-center">Total (H/T/I/S/A)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($data['dataAbsensi'])): ?>
                    <tr>
                        <td colspan="12" class="px-6 py-14 text-center text-slate-400">
                            <i class="fa-solid fa-clipboard-question text-4xl mb-3 text-slate-300 block"></i>
                            <p class="font-bold text-slate-600">Tidak ada log absensi yang sesuai filter</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = $data['filters']['offset'] + 1;
                        foreach ($data['dataAbsensi'] as $row):
                            $stKey = strtolower($row->status ?? 'alfa');
                            $st    = $statusMap[$stKey] ?? ['singkat' => strtoupper($stKey), 'label' => ucfirst($stKey), 'color' => 'bg-slate-100 text-slate-800 border-slate-200'];

                            $personId = ($tipe === 'guru') ? ($row->guru_id ?? null) : ($row->siswa_id ?? null);
                            $ak = $akumulasiMap[$personId] ?? ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alfa' => 0, 'pkl' => 0];
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-medium text-xs"><?= $no++ ?></td>
                            <td class="px-4 py-3.5 font-semibold text-slate-700 whitespace-nowrap text-xs">
                                <?= date('d M Y', strtotime($row->tanggal)) ?>
                            </td>

                            <?php if ($tipe === 'guru'): ?>
                                <td class="px-4 py-3.5 text-slate-500 font-mono text-xs"><?= htmlspecialchars($row->nip ?? '-') ?></td>
                                <td class="px-4 py-3.5 font-bold text-slate-800"><?= htmlspecialchars($row->name) ?></td>
                            <?php else: ?>
                                <td class="px-4 py-3.5 text-slate-500 font-mono text-xs"><?= htmlspecialchars($row->nis ?? '-') ?></td>
                                <td class="px-4 py-3.5 font-bold text-slate-800"><?= htmlspecialchars($row->name) ?></td>
                                <td class="px-4 py-3.5 text-xs text-slate-600 font-semibold whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200/60">
                                        <?= htmlspecialchars($row->nama_kelas ?? '-') ?>
                                    </span>
                                </td>
                            <?php endif; ?>

                            <!-- Jam Datang -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <?php if (!empty($row->jam_datang)): ?>
                                    <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md font-mono font-semibold text-xs border border-emerald-100">
                                        <?= substr($row->jam_datang, 0, 5) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 font-bold">&mdash;</span>
                                <?php endif; ?>
                            </td>

                            <!-- Jam Pulang -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <?php if (!empty($row->jam_pulang)): ?>
                                    <span class="inline-flex items-center gap-1 text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md font-mono font-semibold text-xs border border-blue-100">
                                        <?= substr($row->jam_pulang, 0, 5) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 font-bold">&mdash;</span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex flex-col items-center gap-0.5">
                                    <span title="<?= $st['label'] ?>"
                                          class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-black border-2 <?= $st['color'] ?> shadow-2xs">
                                        <?= $st['singkat'] ?>
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 tracking-tight leading-none"><?= $st['label'] ?></span>
                                </div>
                            </td>

                            <!-- Keterangan -->
                            <td class="px-4 py-3.5 max-w-[140px]">
                                <?php if (!empty($row->keterangan)): ?>
                                    <span class="text-xs text-slate-600 leading-snug line-clamp-2" title="<?= htmlspecialchars($row->keterangan) ?>">
                                        <?= htmlspecialchars($row->keterangan) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs">&mdash;</span>
                                <?php endif; ?>
                            </td>

                            <!-- Catatan Evaluasi -->
                            <td class="px-4 py-3.5 max-w-[160px]">
                                <?php if (!empty($row->data_catatan)): ?>
                                    <span class="inline-flex items-start gap-1.5 text-xs text-amber-800 bg-amber-50 border border-amber-200/80 rounded-lg px-2.5 py-1.5 leading-snug">
                                        <i class="fa-solid fa-comment-dots mt-0.5 shrink-0 text-amber-500 text-[10px]"></i>
                                        <span class="line-clamp-2" title="<?= htmlspecialchars($row->data_catatan) ?>">
                                            <?= htmlspecialchars($row->data_catatan) ?>
                                        </span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 text-xs">&mdash;</span>
                                <?php endif; ?>
                            </td>

                            <!-- DISAMPING CATATAN: JUMLAH AKUMULASI SISWA (H, T, I, S, A) -->
                            <td class="px-4 py-3.5 bg-emerald-50/30 whitespace-nowrap text-center">
                                <div class="inline-flex items-center gap-1 text-[11px] font-extrabold font-mono">
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200" title="Total Hadir: <?= $ak['hadir'] ?>">
                                        H:<?= $ak['hadir'] ?>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200" title="Total Terlambat: <?= $ak['terlambat'] ?>">
                                        T:<?= $ak['terlambat'] ?>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-200" title="Total Izin: <?= $ak['izin'] ?>">
                                        I:<?= $ak['izin'] ?>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 border border-purple-200" title="Total Sakit: <?= $ak['sakit'] ?>">
                                        S:<?= $ak['sakit'] ?>
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded <?= $ak['alfa'] > 0 ? 'bg-rose-200 text-rose-900 border-rose-300' : 'bg-slate-100 text-slate-500 border-slate-200' ?>" title="Total Alfa: <?= $ak['alfa'] ?>">
                                        A:<?= $ak['alfa'] ?>
                                    </span>
                                    <?php if ($tipe === 'siswa' && !empty($ak['pkl'])): ?>
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 border border-indigo-200" title="Total PKL: <?= $ak['pkl'] ?>">
                                        PKL:<?= $ak['pkl'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <!-- Pagination & Summary Footer -->
        <?php if ($data['totalData'] > 0): ?>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">
                Menampilkan <span class="font-bold text-slate-800"><?= count($data['dataAbsensi']) ?></span> data dari total <span class="font-bold text-slate-800"><?= $data['totalData'] ?></span> baris (<?= $viewMode === 'ringkasan' ? 'orang' : 'transaksi' ?>)
            </p>
            
            <?php if ($data['totalPages'] > 1): ?>
            <div class="flex items-center gap-1 flex-wrap">
                <?php 
                $baseQuery = $filters;
                unset($baseQuery['limit'], $baseQuery['offset']);
                $queryBaseStr = '&' . http_build_query($baseQuery);
                if (!empty($data['limit'])) $queryBaseStr .= '&limit=' . $data['limit'];
                if (!empty($tipe)) $queryBaseStr .= '&tipe=' . $tipe;
                if (!empty($viewMode)) $queryBaseStr .= '&view=' . $viewMode;

                $curPage = $data['page'];
                $totPages = $data['totalPages'];
                $startP = max(1, $curPage - 2);
                $endP   = min($totPages, $curPage + 2);
                ?>

                <!-- Tombol Prev -->
                <?php if ($curPage > 1): ?>
                <a href="<?= BASEURL ?>/rekapabsensi?page=<?= $curPage - 1 ?><?= $queryBaseStr ?>"
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold bg-white text-slate-600 border border-slate-200 hover:bg-slate-50">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                </a>
                <?php endif; ?>

                <?php if ($startP > 1): ?>
                    <a href="<?= BASEURL ?>/rekapabsensi?page=1<?= $queryBaseStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold bg-white text-slate-600 border border-slate-200 hover:bg-slate-50">1</a>
                    <?php if ($startP > 2): ?><span class="px-1 text-slate-400">...</span><?php endif; ?>
                <?php endif; ?>

                <?php for($i = $startP; $i <= $endP; $i++): ?>
                    <a href="<?= BASEURL ?>/rekapabsensi?page=<?= $i ?><?= $queryBaseStr ?>"
                       class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?= $i == $curPage ? 'bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-500' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endP < $totPages): ?>
                    <?php if ($endP < $totPages - 1): ?><span class="px-1 text-slate-400">...</span><?php endif; ?>
                    <a href="<?= BASEURL ?>/rekapabsensi?page=<?= $totPages ?><?= $queryBaseStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold bg-white text-slate-600 border border-slate-200 hover:bg-slate-50"><?= $totPages ?></a>
                <?php endif; ?>

                <!-- Tombol Next -->
                <?php if ($curPage < $totPages): ?>
                <a href="<?= BASEURL ?>/rekapabsensi?page=<?= $curPage + 1 ?><?= $queryBaseStr ?>"
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold bg-white text-slate-600 border border-slate-200 hover:bg-slate-50">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
