<?php
$activeKelas  = $data['activeKelas'] ?? null;
$allKelasPkl  = $data['allKelasPkl'] ?? [];
$kelompokList = $data['kelompokList'] ?? [];
$siswaList    = $data['siswaList'] ?? [];
$stats        = $data['stats'] ?? ['total_siswa' => 0, 'sudah_kelompok' => 0, 'belum_kelompok' => 0, 'total_kelompok' => 0];
$userRole     = $data['userRole'] ?? '';
$activeKid    = $activeKelas ? (int)$activeKelas->id : 0;
?>

<div id="pageContent" class="w-full space-y-6">

    <!-- ── PAGE HEADER (CLEAN & HUMBLE) ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-1 border-b border-slate-200/80 dark:border-slate-800">
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                    <i class="fa-solid fa-briefcase text-[10px]"></i> PKL Management
                </span>
                <?php if ($activeKelas): ?>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Kelas <?= htmlspecialchars($activeKelas->nama_kelas) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Kelompok &amp; Lokasi PKL
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Atur penempatan kelompok siswa dan koordinat lokasi presensi instansi mitra
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap self-start sm:self-center">
            <!-- Selector Kelas untuk Admin -->
            <?php if ($userRole === 'admin' && !empty($allKelasPkl)): ?>
            <div class="relative inline-block text-left" id="dropdownKelasAdmin">
                <button type="button" onclick="toggleDropdownKelas()" class="btn-secondary text-xs flex items-center gap-2">
                    <i class="fa-solid fa-chalkboard text-slate-400"></i>
                    <span>Kelas: <strong><?= $activeKelas ? htmlspecialchars($activeKelas->nama_kelas) : 'Pilih Kelas' ?></strong></span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-1 opacity-60"></i>
                </button>
                <div id="menuKelasAdmin" class="hidden absolute right-0 mt-2 w-56 rounded-xl bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 z-50 py-1.5">
                    <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pilih Kelas PKL</div>
                    <?php foreach ($allKelasPkl as $kp): ?>
                        <a href="<?= BASEURL; ?>/pkl/kelompok?kelas_id=<?= $kp->id; ?>" class="flex items-center justify-between px-3.5 py-1.5 text-xs <?= ($activeKid === (int)$kp->id) ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>">
                            <span><?= htmlspecialchars($kp->nama_kelas); ?></span>
                            <span class="font-mono text-[10px] text-slate-400"><?= (int)$kp->total_siswa ?> Siswa</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <a href="<?= BASEURL; ?>/pkl/monitoring<?= $activeKid ? '?kelas_id=' . $activeKid : '' ?>" class="btn-secondary text-xs">
                <i class="fa-solid fa-chart-line text-slate-400"></i> Monitoring Presensi
            </a>

            <?php if ($activeKelas): ?>
            <button type="button" class="btn-primary text-xs cursor-pointer" onclick="openAddKelompokModal()">
                <i class="fa-solid fa-plus"></i> Tambah Kelompok
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── NOTIFIKASI FLASH ── -->
    <?php Flasher::flash(); ?>

    <?php if (!$activeKelas): ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-8 text-center max-w-xl mx-auto space-y-3 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl mx-auto">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Kelas PKL Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Anda belum ditugaskan sebagai wali kelas untuk kelas berstatus PKL, atau belum ada kelas yang berstatus PKL di sistem.
                Silakan hubungi Administrator untuk mengatur status kelas menjadi PKL.
            </p>
        </div>
    <?php else: ?>

        <!-- ── 4 KPI METRICS ── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Siswa -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Total Siswa Kelas</span>
                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                                <?= $stats['total_siswa'] ?>
                            </span>
                            <span class="text-xs text-slate-400 font-sans">Siswa</span>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                    <span>Kelas</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($activeKelas->nama_kelas) ?></span>
                </div>
            </div>

            <!-- Card 2: Kelompok PKL -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Kelompok PKL</span>
                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight tabular-nums">
                                <?= $stats['total_kelompok'] ?>
                            </span>
                            <span class="text-xs text-slate-400 font-sans">Instansi</span>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-sm">
                        <i class="fa-solid fa-building"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                    <span>Titik Lokasi</span>
                    <span class="font-medium text-emerald-600 dark:text-emerald-400">Terdaftar</span>
                </div>
            </div>

            <!-- Card 3: Sudah Berkelompok -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Sudah Berkelompok</span>
                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight tabular-nums">
                                <?= $stats['sudah_kelompok'] ?>
                            </span>
                            <span class="text-xs text-slate-400 font-sans">Siswa</span>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-sm">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                    <span>Presensi Mandiri</span>
                    <span class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold text-[11px]">Ready</span>
                </div>
            </div>

            <!-- Card 4: Belum Ada Kelompok -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 shadow-xs flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 block">Belum Berkelompok</span>
                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span class="text-2xl sm:text-3xl font-bold <?= $stats['belum_kelompok'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' ?> font-mono tracking-tight tabular-nums">
                                <?= $stats['belum_kelompok'] ?>
                            </span>
                            <span class="text-xs text-slate-400 font-sans">Siswa</span>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 text-sm">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                    <span>Status</span>
                    <span class="font-semibold text-[11px] <?= $stats['belum_kelompok'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' ?>">
                        <?= $stats['belum_kelompok'] > 0 ? 'Pending' : 'Lengkap' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ── MAIN CONTENT (2 COLUMNS: 8 / 4) ── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ── LEFT COLUMN (8 COLS): DAFTAR KELOMPOK PKL ── -->
            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl shadow-xs overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Daftar Kelompok &amp; Tempat PKL</h2>
                        </div>
                        <button type="button" class="btn-primary text-xs cursor-pointer" onclick="openAddKelompokModal()">
                            <i class="fa-solid fa-plus"></i> Buat Kelompok Baru
                        </button>
                    </div>

                    <div class="p-5">
                        <?php if (empty($kelompokList)): ?>
                            <div class="text-center py-10 px-4 bg-slate-50/50 dark:bg-slate-900/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mx-auto mb-2.5">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">Belum Ada Kelompok PKL</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-4">
                                    Silakan buat kelompok PKL baru dan masukkan siswa anggota sesuai tempat instansi magang.
                                </p>
                                <button type="button" class="btn-primary inline-flex items-center gap-2 text-xs cursor-pointer" onclick="openAddKelompokModal()">
                                    <i class="fa-solid fa-plus"></i> Buat Kelompok PKL Pertama
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3.5">
                                <?php foreach ($kelompokList as $kel): ?>
                                    <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800 space-y-3">
                                        
                                        <!-- Top Row: Nama Kelompok & Aksi -->
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 pb-3 border-b border-slate-200/70 dark:border-slate-800">
                                            <div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                                        <?= htmlspecialchars($kel->nama_kelompok) ?>
                                                    </h3>
                                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40">
                                                        <?= count($kel->anggota) ?> Siswa
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-mono text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                                        Radius: <?= (int)$kel->radius_meter ?>m
                                                    </span>
                                                </div>
                                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                                                    <i class="fa-solid fa-building text-emerald-600 dark:text-emerald-400"></i>
                                                    <strong class="text-slate-800 dark:text-slate-200 font-medium"><?= htmlspecialchars($kel->nama_tempat) ?></strong>
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-1.5 self-start sm:self-auto">
                                                <button type="button" class="btn-secondary text-xs px-2.5 py-1" onclick="openEditKelompokModal(<?= $kel->id ?>)" title="Edit Kelompok">
                                                    <i class="fa-solid fa-pen-to-square text-slate-400"></i> Edit
                                                </button>
                                                <button type="button" class="btn-secondary text-xs px-2.5 py-1 text-rose-600 dark:text-rose-400 hover:border-rose-300 dark:hover:border-rose-800" onclick="confirmDeleteKelompok(<?= $kel->id ?>, '<?= htmlspecialchars(addslashes($kel->nama_kelompok)) ?>')" title="Hapus Kelompok">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Middle Row: Alamat & Koordinat -->
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
                                            <div class="md:col-span-7 flex items-start gap-2 text-slate-600 dark:text-slate-400 leading-relaxed">
                                                <i class="fa-solid fa-location-dot text-rose-500 mt-0.5 shrink-0"></i>
                                                <div>
                                                    <div><?= htmlspecialchars($kel->alamat ?: 'Alamat belum diisi') ?></div>
                                                    <?php if (!empty($kel->link_lokasi)): ?>
                                                        <a href="<?= htmlspecialchars($kel->link_lokasi) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-400 font-semibold hover:underline mt-0.5">
                                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i> Buka Google Maps
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="md:col-span-5 font-mono text-[11px] text-slate-500 dark:text-slate-400 space-y-0.5 bg-slate-100/70 dark:bg-slate-900/60 p-2.5 rounded-lg border border-slate-200/80 dark:border-slate-800">
                                                <div>Lat: <span class="font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($kel->latitude) ?></span></div>
                                                <div>Lng: <span class="font-semibold text-slate-800 dark:text-slate-200"><?= htmlspecialchars($kel->longitude) ?></span></div>
                                                <?php if (!empty($kel->pembimbing_nama)): ?>
                                                    <div class="font-sans text-[11px] text-slate-700 dark:text-slate-300 pt-1 border-t border-slate-200 dark:border-slate-800 mt-1">
                                                        <i class="fa-solid fa-user-tie text-emerald-600 dark:text-emerald-400 mr-1"></i>
                                                        PIC: <strong><?= htmlspecialchars($kel->pembimbing_nama) ?></strong>
                                                        <?= !empty($kel->pembimbing_kontak) ? '(' . htmlspecialchars($kel->pembimbing_kontak) . ')' : '' ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Bottom Row: Siswa Anggota -->
                                        <div>
                                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">Siswa Anggota:</span>
                                            <?php if (empty($kel->anggota)): ?>
                                                <span class="text-xs text-slate-400 italic">Belum ada siswa yang dimasukkan ke kelompok ini.</span>
                                            <?php else: ?>
                                                <div class="flex flex-wrap gap-1.5">
                                                    <?php foreach ($kel->anggota as $ang): ?>
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-xs">
                                                            <div class="w-4 h-4 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[9px] font-bold">
                                                                <?= strtoupper(substr($ang->nama, 0, 1)) ?>
                                                            </div>
                                                            <span><?= htmlspecialchars($ang->nama) ?></span>
                                                            <span class="text-[10px] text-slate-400 font-mono">(<?= htmlspecialchars($ang->nis) ?>)</span>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT COLUMN (4 COLS): STATUS PENUGASAN SISWA ── -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Status Penugasan Siswa</h3>
                        </div>
                    </div>

                    <!-- Filter Pencarian Sidebar -->
                    <div class="p-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            <input type="text" id="searchSidebarSiswa" onkeyup="filterSidebarSiswa()" placeholder="Cari nama atau NIS siswa..." class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="p-0">
                        <div id="sidebarSiswaContainer" class="divide-y divide-slate-100 dark:divide-slate-800 text-xs overflow-y-auto" style="max-height: 500px;">
                            <?php foreach ($siswaList as $s): ?>
                                <div class="sidebar-siswa-item p-3 flex items-center justify-between gap-3 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors"
                                     data-nama="<?= htmlspecialchars(strtolower($s->nama)) ?>"
                                     data-nis="<?= htmlspecialchars(strtolower($s->nis)) ?>">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-[11px] font-bold shrink-0">
                                            <?= strtoupper(substr($s->nama, 0, 1)) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-900 dark:text-slate-100 truncate"><?= htmlspecialchars($s->nama) ?></div>
                                            <div class="text-[10px] text-slate-400 font-mono">NIS: <?= htmlspecialchars($s->nis) ?></div>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <?php if (!empty($s->kelompok_id)): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 max-w-[130px]" title="<?= htmlspecialchars(($s->nama_kelompok ?? '') . ' - ' . ($s->nama_tempat ?? '')) ?>">
                                                <i class="fa-solid fa-check text-[9px] shrink-0"></i>
                                                <span class="truncate"><?= htmlspecialchars($s->nama_kelompok ?? 'Ada Kelompok') ?></span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50">
                                                <i class="fa-solid fa-clock text-[9px]"></i> Belum ada
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div id="sidebarSiswaEmpty" class="hidden text-center py-6 text-slate-400 text-xs">
                                <i class="fa-solid fa-user-xmark text-xl mb-1 block"></i>
                                Siswa tidak ditemukan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: TAMBAH / EDIT KELOMPOK PKL                                           -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalKelompok" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-fade-in">
        <form action="<?= BASEURL; ?>/pkl/save_kelompok" method="POST" id="formKelompok" class="flex flex-col flex-1 overflow-hidden">
            <input type="hidden" name="id" id="kelompok_id" value="">
            <input type="hidden" name="kelas_id" value="<?= $activeKid ?>">

            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white" id="modalTitle">Tambah Kelompok PKL</h3>
                </div>
                <button type="button" onclick="closeModalKelompok()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-1.5 rounded-lg">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Kelompok <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_kelompok" id="inputNamaKelompok" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" required placeholder="Contoh: Kelompok Telkom A">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Tempat / Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_tempat" id="inputNamaTempat" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" required placeholder="Contoh: PT Telekomunikasi Indonesia">
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap Perusahaan</label>
                    <textarea name="alamat" id="inputAlamat" rows="2" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500 resize-none" placeholder="Alamat lengkap perusahaan..."></textarea>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Link Google Maps</label>
                    <input type="text" name="link_lokasi" id="inputLinkLokasi" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" oninput="parseGmapsLink(this.value)" placeholder="Tempel link Google Maps untuk otomatis ambil koordinat...">
                </div>

                <!-- Koordinat & Radius -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Latitude <span class="text-rose-500">*</span></label>
                        <input type="text" name="latitude" id="inputLatitude" class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" required placeholder="-6.123456">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Longitude <span class="text-rose-500">*</span></label>
                        <input type="text" name="longitude" id="inputLongitude" class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" required placeholder="108.123456">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Radius Toleransi GPS <span class="text-rose-500">*</span></label>
                        <select name="radius_meter" id="inputRadius" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="25">25 meter</option>
                            <option value="50" selected>50 meter</option>
                            <option value="100">100 meter</option>
                            <option value="150">150 meter</option>
                            <option value="200">200 meter</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="button" class="btn-secondary text-xs inline-flex items-center gap-1.5" onclick="getBrowserGps()">
                        <i class="fa-solid fa-location-crosshairs text-emerald-600"></i> Ambil Titik GPS Perangkat Saat Ini
                    </button>
                </div>

                <!-- Pembimbing PIC -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Pembimbing Lapangan / PIC</label>
                        <input type="text" name="pembimbing_nama" id="inputPembimbing" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="Nama PIC perusahaan">
                    </div>
                    <div>
                        <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Kontak / WhatsApp PIC</label>
                        <input type="text" name="pembimbing_kontak" id="inputKontak" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" placeholder="No. WhatsApp PIC">
                    </div>
                </div>

                <!-- Multi-select Siswa Anggota -->
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-2">
                        <label class="font-medium text-slate-700 dark:text-slate-300">
                            Pilih Siswa Anggota Kelompok <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" class="text-emerald-700 dark:text-emerald-400 font-medium hover:underline" onclick="selectAllStudents(true)">Pilih Semua</button>
                            <span class="text-slate-300">&bull;</span>
                            <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" onclick="selectAllStudents(false)">Batal Pilih</button>
                        </div>
                    </div>

                    <!-- Search Filter Input untuk Anggota -->
                    <div class="relative mb-2.5">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" id="searchAnggotaInput" onkeyup="filterAnggotaPicker()" placeholder="Cari nama siswa atau NIS..." class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        <button type="button" id="btnClearSearchAnggota" onclick="clearSearchAnggota()" class="hidden absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 text-xs">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 max-h-56 overflow-y-auto">
                        <?php if (empty($siswaList)): ?>
                            <div class="text-slate-400 text-center py-4">Tidak ada data siswa di kelas ini.</div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="studentPickerGrid">
                                <?php foreach ($siswaList as $s): ?>
                                    <label class="student-picker-item flex items-start gap-2.5 p-2 rounded-lg hover:bg-white dark:hover:bg-slate-700/60 cursor-pointer border border-transparent hover:border-slate-200 dark:hover:border-slate-600 transition-colors"
                                           data-nama="<?= htmlspecialchars(strtolower($s->nama)) ?>"
                                           data-nis="<?= htmlspecialchars(strtolower($s->nis)) ?>">
                                        <input type="checkbox" name="siswa_ids[]" value="<?= $s->siswa_id ?>" class="chk-siswa mt-1 rounded text-emerald-600 focus:ring-emerald-500">
                                        <div class="text-xs">
                                            <div class="font-medium text-slate-800 dark:text-slate-200"><?= htmlspecialchars($s->nama) ?></div>
                                            <div class="text-[10px] text-slate-400 font-mono">NIS: <?= htmlspecialchars($s->nis) ?></div>
                                            <?php if (!empty($s->nama_kelompok)): ?>
                                                <span class="inline-block mt-0.5 text-[10px] text-slate-500 dark:text-slate-400 bg-slate-200/60 dark:bg-slate-700 px-1.5 py-0.2 rounded font-medium">
                                                    Saat ini: <?= htmlspecialchars($s->nama_kelompok) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div id="searchAnggotaEmpty" class="hidden text-center py-6 text-slate-400 text-xs">
                                <i class="fa-solid fa-user-xmark text-xl mb-1 block"></i>
                                Tidak ada siswa yang cocok dengan kata kunci pencarian.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2 bg-slate-50/50 dark:bg-slate-900/50">
                <button type="button" onclick="closeModalKelompok()" class="btn-secondary text-xs">Batal</button>
                <button type="submit" class="btn-primary text-xs px-5">Simpan Kelompok</button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: KONFIRMASI HAPUS KELOMPOK                                           -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 text-center space-y-4 animate-fade-in">
        <form action="<?= BASEURL; ?>/pkl/delete_kelompok" method="POST">
            <input type="hidden" name="id" id="hapus_kelompok_id">
            <input type="hidden" name="kelas_id" value="<?= $activeKid ?>">

            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mx-auto">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Hapus Kelompok PKL?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Hapus kelompok <strong id="hapus_kelompok_nama" class="text-slate-900 dark:text-white"></strong>? Siswa dalam kelompok ini akan menjadi tidak memiliki kelompok.
            </p>
            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" onclick="closeModalHapus()" class="btn-secondary text-xs px-4">Batal</button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-medium transition-colors shadow-xs">
                    Ya, Hapus Kelompok
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDropdownKelas() {
    const el = document.getElementById('menuKelasAdmin');
    if (el) el.classList.toggle('hidden');
}

document.addEventListener('click', (e) => {
    const drop = document.getElementById('dropdownKelasAdmin');
    const menu = document.getElementById('menuKelasAdmin');
    if (drop && menu && !drop.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

// Live Search di Modal Pemilihan Anggota
function filterAnggotaPicker() {
    const input = document.getElementById('searchAnggotaInput');
    const q = (input?.value || '').toLowerCase().trim();
    const btnClear = document.getElementById('btnClearSearchAnggota');
    if (btnClear) {
        if (q) btnClear.classList.remove('hidden');
        else btnClear.classList.add('hidden');
    }

    const items = document.querySelectorAll('.student-picker-item');
    let visibleCount = 0;
    items.forEach(el => {
        const nama = el.getAttribute('data-nama') || '';
        const nis = el.getAttribute('data-nis') || '';
        const match = !q || nama.includes(q) || nis.includes(q);
        el.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });

    const emptyMsg = document.getElementById('searchAnggotaEmpty');
    if (emptyMsg) {
        emptyMsg.style.display = (visibleCount === 0 && q) ? '' : 'none';
    }
}

function clearSearchAnggota() {
    const input = document.getElementById('searchAnggotaInput');
    if (input) {
        input.value = '';
        filterAnggotaPicker();
        input.focus();
    }
}

// Live Search di Sidebar Status Siswa
function filterSidebarSiswa() {
    const q = (document.getElementById('searchSidebarSiswa')?.value || '').toLowerCase().trim();
    const items = document.querySelectorAll('.sidebar-siswa-item');
    let visibleCount = 0;
    items.forEach(el => {
        const nama = el.getAttribute('data-nama') || '';
        const nis = el.getAttribute('data-nis') || '';
        const match = !q || nama.includes(q) || nis.includes(q);
        el.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });

    const emptyMsg = document.getElementById('sidebarSiswaEmpty');
    if (emptyMsg) {
        emptyMsg.style.display = (visibleCount === 0 && q) ? '' : 'none';
    }
}

function openAddKelompokModal() {
    document.getElementById('modalTitle').innerText = 'Tambah Kelompok PKL Baru';
    document.getElementById('formKelompok').reset();
    document.getElementById('kelompok_id').value = '';
    document.getElementById('inputRadius').value = '50';
    document.querySelectorAll('.chk-siswa').forEach(chk => chk.checked = false);
    clearSearchAnggota();
    document.getElementById('modalKelompok').classList.remove('hidden');
}

function closeModalKelompok() {
    document.getElementById('modalKelompok').classList.add('hidden');
}

async function openEditKelompokModal(kelompokId) {
    document.getElementById('modalTitle').innerText = 'Edit Kelompok PKL';
    document.getElementById('formKelompok').reset();
    document.getElementById('kelompok_id').value = kelompokId;
    clearSearchAnggota();

    try {
        const res = await fetch(`<?= BASEURL ?>/pkl/get_detail_json?id=${kelompokId}`);
        const result = await res.json();

        if (result.success && result.data) {
            const d = result.data;
            document.getElementById('inputNamaKelompok').value = d.nama_kelompok || '';
            document.getElementById('inputNamaTempat').value   = d.nama_tempat || '';
            document.getElementById('inputAlamat').value       = d.alamat || '';
            document.getElementById('inputLinkLokasi').value   = d.link_lokasi || '';
            document.getElementById('inputLatitude').value     = d.latitude || '';
            document.getElementById('inputLongitude').value    = d.longitude || '';
            document.getElementById('inputRadius').value       = d.radius_meter || '50';
            document.getElementById('inputPembimbing').value   = d.pembimbing_nama || '';
            document.getElementById('inputKontak').value       = d.pembimbing_kontak || '';

            const memberIds = d.anggota_ids || [];
            document.querySelectorAll('.chk-siswa').forEach(chk => {
                const sId = parseInt(chk.value);
                chk.checked = memberIds.includes(sId);
            });

            document.getElementById('modalKelompok').classList.remove('hidden');
        } else {
            alert(result.message || 'Gagal memuat data kelompok.');
        }
    } catch (e) {
        console.error(e);
        alert('Gagal mengambil data dari server.');
    }
}

function confirmDeleteKelompok(id, name) {
    document.getElementById('hapus_kelompok_id').value = id;
    document.getElementById('hapus_kelompok_nama').innerText = name;
    document.getElementById('modalHapus').classList.remove('hidden');
}

function closeModalHapus() {
    document.getElementById('modalHapus').classList.add('hidden');
}

function selectAllStudents(val) {
    document.querySelectorAll('.student-picker-item').forEach(item => {
        if (item.style.display !== 'none') {
            const chk = item.querySelector('.chk-siswa');
            if (chk) chk.checked = val;
        }
    });
}

function parseGmapsLink(val) {
    if (!val) return;
    val = val.trim();
    let match = val.match(/[?&]q=([-+]?\d{1,2}\.\d+),([-+]?\d{1,3}\.\d+)/);
    if (!match) match = val.match(/@([-+]?\d{1,2}\.\d+),([-+]?\d{1,3}\.\d+)/);
    if (!match) match = val.match(/([-+]?\d{1,2}\.\d+)[,\s]+([-+]?\d{1,3}\.\d+)/);
    if (match) {
        document.getElementById('inputLatitude').value = match[1];
        document.getElementById('inputLongitude').value = match[2];
    }
}

function getBrowserGps() {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung geolokasi GPS.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('inputLatitude').value  = pos.coords.latitude.toFixed(6);
            document.getElementById('inputLongitude').value = pos.coords.longitude.toFixed(6);
            alert('Titik koordinat berhasil diambil dari GPS perangkat Anda.');
        },
        (err) => {
            alert('Gagal mengambil koordinat GPS. Pastikan izin lokasi diaktifkan.');
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
}
</script>
