<?php
$activeKelas = $data['activeKelas'] ?? null;
$allKelasPkl = $data['allKelasPkl'] ?? [];
$tanggal     = $data['tanggal'] ?? date('Y-m-d');
$rows        = $data['rows'] ?? [];
$stats       = $data['stats'] ?? ['total' => 0, 'masuk' => 0, 'pulang' => 0, 'belum' => 0];
$userRole    = $data['userRole'] ?? '';
$activeKid   = $activeKelas ? (int)$activeKelas->id : 0;
?>

<div id="pageContent" class="w-full space-y-6">

    <!-- ── PAGE HEADER ── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <span>PKL</span>
                <span>/</span>
                <span class="text-emerald-700 dark:text-emerald-400 font-semibold">Log Data Presensi</span>
                <?php if ($activeKelas): ?>
                    <span>/</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                        <?= htmlspecialchars($activeKelas->nama_kelas) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                Log Data Presensi Siswa PKL
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pantau rekam jejak kehadiran siswa PKL, waktu presensi, foto selfie, dan validasi radius GPS instansi mitra.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/pkl/kelompok<?= $activeKid ? '?kelas_id=' . $activeKid : '' ?>" class="btn-secondary text-xs inline-flex items-center gap-1.5 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                <i class="fa-solid fa-sliders text-emerald-600"></i> Kelola Kelompok PKL
            </a>

            <!-- Selector Kelas untuk Admin / Pembimbing -->
            <?php if (($userRole === 'admin' || count($allKelasPkl) > 1) && !empty($allKelasPkl)): ?>
            <div class="relative inline-block text-left" id="dropdownKelasAdminMon">
                <button type="button" onclick="toggleDropdownKelasMon()" class="btn-secondary text-xs flex items-center gap-2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                    <i class="fa-solid fa-chalkboard text-slate-400"></i>
                    <span>Kelas: <strong><?= $activeKelas ? htmlspecialchars($activeKelas->nama_kelas) : 'Pilih Kelas' ?></strong></span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-1 opacity-60"></i>
                </button>
                <div id="menuKelasAdminMon" class="hidden absolute right-0 mt-2 w-56 rounded-xl bg-white dark:bg-slate-800 shadow-xl border border-slate-200 dark:border-slate-700 z-50 py-1 text-xs">
                    <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pilih Kelas PKL</div>
                    <?php foreach ($allKelasPkl as $kp): ?>
                        <a href="<?= BASEURL; ?>/pkl/monitoring?kelas_id=<?= $kp->id; ?>&tanggal=<?= $tanggal ?>" class="flex items-center justify-between px-3.5 py-2 <?= ($activeKid === (int)$kp->id) ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>">
                            <span><?= htmlspecialchars($kp->nama_kelas); ?></span>
                            <span class="text-[10px] text-slate-400 font-mono"><?= (int)($kp->total_siswa ?? 0) ?> Siswa</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filter Tanggal -->
            <form action="<?= BASEURL; ?>/pkl/monitoring" method="GET" class="flex items-center">
                <?php if ($activeKid): ?>
                    <input type="hidden" name="kelas_id" value="<?= $activeKid ?>">
                <?php endif; ?>
                <div class="relative">
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="px-3 py-2 text-xs font-mono font-medium rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500 shadow-2xs" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- ── STATS ROW (4 METRIC CARDS) ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Card 1: Total Siswa PKL -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Siswa PKL</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                            <?= $stats['total'] ?>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">siswa</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/50 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Tanggal</span>
                <span class="font-mono font-medium text-slate-700 dark:text-slate-300"><?= date('d M Y', strtotime($tanggal)) ?></span>
            </div>
        </div>

        <!-- Card 2: Sedang PKL (Masuk) -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Sedang PKL (Masuk)</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight tabular-nums">
                            <?= $stats['masuk'] ?>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">siswa</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/50 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Sudah Absen Datang</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Aktif di Lokasi</span>
            </div>
        </div>

        <!-- Card 3: Sudah Pulang -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Sudah Pulang</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-sky-600 dark:text-sky-400 font-mono tracking-tight tabular-nums">
                            <?= $stats['pulang'] ?>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">siswa</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/50 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Presensi Lengkap</span>
                <span class="font-mono font-bold text-sky-600 dark:text-sky-400">Selesai</span>
            </div>
        </div>

        <!-- Card 4: Belum Presensi -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Belum Absen</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold <?= $stats['belum'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' ?> font-mono tracking-tight tabular-nums">
                            <?= $stats['belum'] ?>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">siswa</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-700/50 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Status</span>
                <span class="font-mono font-bold <?= $stats['belum'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' ?>">
                    <?= $stats['belum'] > 0 ? 'Belum Hadir' : 'Nihil (Lengkap)' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ── INTERACTIVE FILTER & SEARCH BAR ── -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-3.5 sm:p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
            <input type="text" id="filterSearchPkl" oninput="applyPklFilters()" placeholder="Cari nama siswa, NIS, atau tempat PKL..." class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/60 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </div>

        <!-- Filter Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs">
            <button type="button" onclick="setFilterStatus('all')" id="btnFilterAll" class="px-3 py-1.5 rounded-lg font-bold text-xs transition bg-emerald-600 text-white shadow-2xs">
                Semua (<?= count($rows) ?>)
            </button>
            <button type="button" onclick="setFilterStatus('masuk')" id="btnFilterMasuk" class="px-3 py-1.5 rounded-lg font-semibold text-xs transition bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
                Sedang PKL (<?= $stats['masuk'] ?>)
            </button>
            <button type="button" onclick="setFilterStatus('pulang')" id="btnFilterPulang" class="px-3 py-1.5 rounded-lg font-semibold text-xs transition bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
                Pulang (<?= $stats['pulang'] ?>)
            </button>
            <button type="button" onclick="setFilterStatus('belum')" id="btnFilterBelum" class="px-3 py-1.5 rounded-lg font-semibold text-xs transition bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200">
                Belum (<?= $stats['belum'] ?>)
            </button>
        </div>
    </div>

    <!-- ── TABEL PRESENSI SISWA PKL (DESKTOP / TABLET VIEW) ── -->
    <div class="hidden md:block bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    Data Kehadiran PKL: <?= date('d F Y', strtotime($tanggal)) ?>
                </h2>
            </div>
            <span id="filteredCountBadge" class="px-2.5 py-1 rounded-md text-xs font-mono font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                Menampilkan <?= count($rows) ?> Siswa
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left" id="tabelLogPkl">
                <thead class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-700/80">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12 font-mono">No</th>
                        <th class="px-4 py-3.5">Nama Siswa</th>
                        <th class="px-4 py-3.5">Kelompok &amp; Tempat PKL</th>
                        <th class="px-4 py-3.5">Presensi Masuk</th>
                        <th class="px-4 py-3.5">Presensi Pulang</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl mb-2.5 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada data siswa untuk kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($rows as $r):
                            $statusType = !empty($r->jam_pulang) ? 'pulang' : (!empty($r->jam_datang) ? 'masuk' : 'belum');
                            $searchText = strtolower($r->nama_siswa . ' ' . $r->nis . ' ' . ($r->nama_kelompok ?? '') . ' ' . ($r->nama_tempat ?? ''));
                        ?>
                            <tr class="row-log-pkl hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors h-16"
                                data-status="<?= $statusType ?>"
                                data-search="<?= htmlspecialchars($searchText) ?>">
                                <td class="px-4 py-3 text-center text-slate-400 font-mono tabular-nums"><?= $no++ ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-extrabold text-xs shrink-0 border border-emerald-200 dark:border-emerald-800">
                                            <?= strtoupper(substr($r->nama_siswa, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($r->nama_siswa) ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono">NIS: <?= htmlspecialchars($r->nis) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($r->nama_kelompok)): ?>
                                        <div class="font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($r->nama_kelompok) ?></div>
                                        <div class="text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 mt-0.5">
                                            <i class="fa-solid fa-building text-[10px]"></i>
                                            <?= htmlspecialchars($r->nama_tempat) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            Belum ditentukan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($r->jam_datang)): ?>
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($r->foto_masuk)): ?>
                                                <img src="<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_masuk) ?>"
                                                     alt="Foto Masuk" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 cursor-pointer hover:scale-105 transition-transform shadow-2xs"
                                                     onclick="previewFoto('<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_masuk) ?>', 'Foto Masuk - <?= htmlspecialchars(addslashes($r->nama_siswa)) ?>')">
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-mono font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">
                                                    <?= date('H:i', strtotime($r->jam_datang)) ?> WIB
                                                </div>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                    GPS: <strong class="text-slate-700 dark:text-slate-300 font-mono"><?= $r->jarak_meter !== null ? round((float)$r->jarak_meter) . 'm' : '-' ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-mono">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($r->jam_pulang)): ?>
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($r->foto_pulang)): ?>
                                                <img src="<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_pulang) ?>"
                                                     alt="Foto Pulang" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 cursor-pointer hover:scale-105 transition-transform shadow-2xs"
                                                     onclick="previewFoto('<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_pulang) ?>', 'Foto Pulang - <?= htmlspecialchars(addslashes($r->nama_siswa)) ?>')">
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-mono font-bold text-sky-700 dark:text-sky-400 tabular-nums">
                                                    <?= date('H:i', strtotime($r->jam_pulang)) ?> WIB
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-0.5">Selesai</div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-mono">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if (!empty($r->jam_pulang)): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                        </span>
                                    <?php elseif (!empty($r->jam_datang)): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            <i class="fa-solid fa-briefcase text-[10px]"></i> Sedang PKL
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                            <i class="fa-regular fa-clock text-[10px]"></i> Belum Absen
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── CARD GRID VIEW KHUSUS SMARTPHONE (MOBILE RESPONSIVE EXCELLENCE) ── -->
    <div class="block md:hidden space-y-3" id="cardsLogPkl">
        <?php if (empty($rows)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 text-center text-slate-400 border border-slate-200 dark:border-slate-700">
                <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300"></i>
                <p class="text-xs">Tidak ada data siswa untuk kelas ini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($rows as $r):
                $statusType = !empty($r->jam_pulang) ? 'pulang' : (!empty($r->jam_datang) ? 'masuk' : 'belum');
                $searchText = strtolower($r->nama_siswa . ' ' . $r->nis . ' ' . ($r->nama_kelompok ?? '') . ' ' . ($r->nama_tempat ?? ''));
            ?>
                <div class="row-log-pkl bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs space-y-3"
                     data-status="<?= $statusType ?>"
                     data-search="<?= htmlspecialchars($searchText) ?>">
                    <!-- Header Kartu -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-extrabold text-sm shrink-0 border border-emerald-200 dark:border-emerald-800">
                                <?= strtoupper(substr($r->nama_siswa, 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white"><?= htmlspecialchars($r->nama_siswa) ?></h3>
                                <p class="text-[11px] text-slate-400 font-mono">NIS: <?= htmlspecialchars($r->nis) ?></p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <?php if (!empty($r->jam_pulang)): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shrink-0">
                                <i class="fa-solid fa-circle-check text-[9px]"></i> Selesai
                            </span>
                        <?php elseif (!empty($r->jam_datang)): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shrink-0">
                                <i class="fa-solid fa-briefcase text-[9px]"></i> Sedang PKL
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-600 shrink-0">
                                <i class="fa-regular fa-clock text-[9px]"></i> Belum
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Lokasi Mitra -->
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 truncate">
                            <i class="fa-solid fa-location-dot text-emerald-600 text-xs shrink-0"></i>
                            <span class="font-medium text-slate-700 dark:text-slate-300 truncate">
                                <?= !empty($r->nama_tempat) ? htmlspecialchars($r->nama_tempat) : 'Belum ada lokasi' ?>
                            </span>
                        </div>
                        <?php if (!empty($r->nama_kelompok)): ?>
                            <span class="text-[10px] font-semibold text-slate-400 shrink-0"><?= htmlspecialchars($r->nama_kelompok) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Jam & Foto Masuk vs Pulang -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <!-- Presensi Masuk -->
                        <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/70 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-2">
                            <?php if (!empty($r->foto_masuk)): ?>
                                <img src="<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_masuk) ?>"
                                     alt="Foto Masuk" class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0 cursor-pointer"
                                     onclick="previewFoto('<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_masuk) ?>', 'Foto Datang - <?= htmlspecialchars(addslashes($r->nama_siswa)) ?>')">
                            <?php else: ?>
                                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-xs shrink-0">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400">Datang:</div>
                                <div class="font-mono font-bold text-emerald-700 dark:text-emerald-400 text-xs">
                                    <?= !empty($r->jam_datang) ? date('H:i', strtotime($r->jam_datang)) . ' WIB' : '-' ?>
                                </div>
                                <?php if ($r->jarak_meter !== null): ?>
                                    <div class="text-[9px] text-slate-400 font-mono">GPS: <?= round((float)$r->jarak_meter) ?>m</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Presensi Pulang -->
                        <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/70 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-2">
                            <?php if (!empty($r->foto_pulang)): ?>
                                <img src="<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_pulang) ?>"
                                     alt="Foto Pulang" class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shrink-0 cursor-pointer"
                                     onclick="previewFoto('<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_pulang) ?>', 'Foto Pulang - <?= htmlspecialchars(addslashes($r->nama_siswa)) ?>')">
                            <?php else: ?>
                                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-xs shrink-0">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400">Pulang:</div>
                                <div class="font-mono font-bold text-sky-700 dark:text-sky-400 text-xs">
                                    <?= !empty($r->jam_pulang) ? date('H:i', strtotime($r->jam_pulang)) . ' WIB' : '-' ?>
                                </div>
                                <div class="text-[9px] text-slate-400">Selesai</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- ── MODAL PREVIEW FOTO SELFIE ── -->
<div id="modalFoto" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-lg overflow-hidden animate-fade-in">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white" id="modalFotoTitle">Foto Selfie Presensi</h3>
            <button type="button" onclick="closeModalFoto()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>
        <div class="p-4 text-center bg-slate-950 flex items-center justify-center min-h-[300px]">
            <img id="imgPreview" src="" alt="Preview" class="max-h-[460px] w-auto rounded-xl object-contain shadow-md">
        </div>
        <div class="p-3 bg-slate-50 dark:bg-slate-800 text-center">
            <button type="button" onclick="closeModalFoto()" class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-300">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
let currentStatusFilter = 'all';

function toggleDropdownKelasMon() {
    const m = document.getElementById('menuKelasAdminMon');
    if (m) m.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const drop = document.getElementById('dropdownKelasAdminMon');
    const menu = document.getElementById('menuKelasAdminMon');
    if (drop && menu && !drop.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

function setFilterStatus(status) {
    currentStatusFilter = status;
    const btns = {
        all: document.getElementById('btnFilterAll'),
        masuk: document.getElementById('btnFilterMasuk'),
        pulang: document.getElementById('btnFilterPulang'),
        belum: document.getElementById('btnFilterBelum'),
    };

    Object.keys(btns).forEach(k => {
        if (!btns[k]) return;
        if (k === status) {
            btns[k].className = 'px-3 py-1.5 rounded-lg font-bold text-xs transition bg-emerald-600 text-white shadow-2xs';
        } else {
            btns[k].className = 'px-3 py-1.5 rounded-lg font-semibold text-xs transition bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200';
        }
    });

    applyPklFilters();
}

function applyPklFilters() {
    const q = (document.getElementById('filterSearchPkl')?.value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.row-log-pkl');
    let visibleCount = 0;

    rows.forEach(r => {
        const rowStatus = r.getAttribute('data-status');
        const rowSearch = r.getAttribute('data-search') || '';

        const matchStatus = (currentStatusFilter === 'all') || (rowStatus === currentStatusFilter);
        const matchSearch = (!q) || (rowSearch.indexOf(q) !== -1);

        if (matchStatus && matchSearch) {
            r.style.display = '';
            visibleCount++;
        } else {
            r.style.display = 'none';
        }
    });

    const badge = document.getElementById('filteredCountBadge');
    if (badge) {
        badge.textContent = `Menampilkan ${visibleCount} Siswa`;
    }
}

function previewFoto(url, title) {
    const m = document.getElementById('modalFoto');
    const img = document.getElementById('imgPreview');
    const t = document.getElementById('modalFotoTitle');
    if (m && img) {
        img.src = url;
        if (t) t.textContent = title || 'Foto Selfie Presensi';
        m.classList.remove('hidden');
    }
}

function closeModalFoto() {
    const m = document.getElementById('modalFoto');
    const img = document.getElementById('imgPreview');
    if (m) m.classList.add('hidden');
    if (img) img.src = '';
}
</script>
