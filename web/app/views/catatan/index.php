<?php $this->view('templates/header', $data); ?>

<link href="<?= BASEURL; ?>/assets/vendor/tom-select/css/tom-select.css" rel="stylesheet">
<script src="<?= BASEURL; ?>/assets/vendor/tom-select/js/tom-select.complete.min.js"></script>

<?php
$role = SessionHelper::get('role');
$isAdmin = ($role === 'admin');
$isWriter = in_array($role, ['wali_kelas', 'walikelas', 'kepsek']);
$isWaliKelas = in_array($role, ['wali_kelas', 'walikelas']);
$isKepsek = ($role === 'kepsek');

$catatanList = $data['catatanList'] ?? [];
$catatanTerkirim = $data['catatanTerkirim'] ?? [];
$catatanSiswaAdmin = $data['catatanSiswaAdmin'] ?? [];
$catatanGuruAdmin  = $data['catatanGuruAdmin'] ?? [];

$countSiswaAdmin = count($catatanSiswaAdmin);
$countGuruAdmin  = count($catatanGuruAdmin);
$countTotalAdmin = $countSiswaAdmin + $countGuruAdmin;

// Hitung metrik bulanan
$currentMonth = date('Y-m');
$countBulanIniDiterima = 0;
foreach ($catatanList as $c) {
    $tgl = $c->created_at ?? $c->tanggal ?? '';
    if (strpos($tgl, $currentMonth) === 0) {
        $countBulanIniDiterima++;
    }
}

$countBulanIniTerkirim = 0;
$uniqueTargets = [];
foreach ($catatanTerkirim as $ct) {
    $tgl = $ct->created_at ?? $ct->tanggal ?? '';
    if (strpos($tgl, $currentMonth) === 0) {
        $countBulanIniTerkirim++;
    }
    if (!empty($ct->target_name)) {
        $uniqueTargets[$ct->target_name] = true;
    }
}
$countUniqueTargets = count($uniqueTargets);
$latestAuthor = !empty($catatanList[0]->author_name) ? $catatanList[0]->author_name : '-';
?>

<div id="pageContent" class="w-full space-y-6">
    <!-- ── HEADER SECTION ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Catatan Bimbingan & Konseling</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">
                <?= $isAdmin ? 'Kelola dan moderasi seluruh catatan pembinaan siswa & guru' : 'Daftar catatan pembinaan dan bimbingan konseling' ?>
            </p>
        </div>
        <div class="flex gap-2.5 flex-wrap items-center">
            <a href="<?= BASEURL; ?>/dashboard" class="vh-btn-secondary text-xs sm:text-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-arrow-left text-slate-400"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- ── 3 METRIC STAT CARDS ROW ── -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <?php if ($isAdmin): ?>
            <!-- Administrator: Metrik Keseluruhan Catatan -->
            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Catatan Siswa</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        <?= $countSiswaAdmin ?> <span class="text-xs font-sans font-medium text-slate-400">Pesan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Dari Wali Kelas ke Siswa</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-100 dark:border-emerald-800/50">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Catatan Guru</p>
                    <p class="text-2xl font-bold text-sky-600 dark:text-sky-400 font-mono tabular-nums">
                        <?= $countGuruAdmin ?> <span class="text-xs font-sans font-medium text-slate-400">Pesan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Dari Kepala Sekolah ke Guru</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg shrink-0 border border-sky-100 dark:border-sky-800/50">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Catatan</p>
                    <p class="text-2xl font-bold text-teal-600 dark:text-teal-400 font-mono tabular-nums">
                        <?= $countTotalAdmin ?> <span class="text-xs font-sans font-medium text-slate-400">Pesan</span>
                    </p>
                    <p class="text-[11px] text-teal-600 dark:text-teal-400 font-medium">Semua catatan di sistem</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg shrink-0 border border-teal-100 dark:border-teal-800/50">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
            </div>
        <?php elseif (!$isWriter): ?>
            <!-- Siswa / Guru: Metrik Penerima -->
            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Catatan</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">
                        <?= count($catatanList) ?> <span class="text-xs font-sans font-medium text-slate-400">Pesan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Diterima dari pembimbing</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-100 dark:border-emerald-800/50">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bulan Ini</p>
                    <p class="text-2xl font-bold text-sky-600 dark:text-sky-400 font-mono tabular-nums">
                        <?= $countBulanIniDiterima ?> <span class="text-xs font-sans font-medium text-slate-400">Catatan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Periode <?= date('F Y') ?></p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg shrink-0 border border-sky-100 dark:border-sky-800/50">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pembimbing Terakhir</p>
                    <p class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate max-w-[170px]" title="<?= htmlspecialchars($latestAuthor) ?>">
                        <?= htmlspecialchars($latestAuthor) ?>
                    </p>
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">Pengirim catatan aktif</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg shrink-0 border border-teal-100 dark:border-teal-800/50">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>

        <?php elseif ($isWaliKelas): ?>
            <!-- Wali Kelas: Metrik Terkirim & Diterima -->
            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Catatan ke Siswa</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        <?= count($catatanTerkirim) ?> <span class="text-xs font-sans font-medium text-slate-400">Pesan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Telah dikirimkan ke siswa</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-100 dark:border-emerald-800/50">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Siswa Terbina</p>
                    <p class="text-2xl font-bold text-sky-600 dark:text-sky-400 font-mono tabular-nums">
                        <?= $countUniqueTargets ?> <span class="text-xs font-sans font-medium text-slate-400">Siswa</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Menerima arahan bimbingan</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg shrink-0 border border-sky-100 dark:border-sky-800/50">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Dari Kepala Sekolah</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">
                        <?= count($catatanList) ?> <span class="text-xs font-sans font-medium text-slate-400">Pesan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Instruksi & pembinaan pimpinan</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0 border border-amber-100 dark:border-amber-800/50">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
            </div>

        <?php else: ?>
            <!-- Kepala Sekolah: Metrik Kepsek -->
            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Catatan Guru</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">
                        <?= count($catatanList) ?> <span class="text-xs font-sans font-medium text-slate-400">Catatan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Arahan pimpinan kepada guru</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-100 dark:border-emerald-800/50">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bulan Ini</p>
                    <p class="text-2xl font-bold text-sky-600 dark:text-sky-400 font-mono tabular-nums">
                        <?= $countBulanIniDiterima ?> <span class="text-xs font-sans font-medium text-slate-400">Pesan</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Periode <?= date('F Y') ?></p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg shrink-0 border border-sky-100 dark:border-sky-800/50">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>

            <div class="vh-card p-4 sm:p-5 flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Dewan Guru Terbina</p>
                    <p class="text-2xl font-bold text-teal-600 dark:text-teal-400 font-mono tabular-nums">
                        <?= count($data['gurus'] ?? []) ?> <span class="text-xs font-sans font-medium text-slate-400">Guru</span>
                    </p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Guru aktif terdaftar</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center text-lg shrink-0 border border-teal-100 dark:border-teal-800/50">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── MAIN CONTENT GRID ── -->
    <?php if ($isAdmin): ?>
        <!-- ── ADMINISTRATOR: MANAJEMEN CATATAN TERKIRIM DENGAN EDIT & HAPUS ── -->
        <div class="vh-card overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <i class="fa-solid fa-list-check text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">Riwayat Catatan Bimbingan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola, moderasi, edit, atau hapus seluruh riwayat catatan yang telah dikirim oleh Wali Kelas dan Kepala Sekolah</p>
                    </div>
                </div>
                <div class="w-full sm:w-64">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                        <input type="text" id="searchCatatanAdmin" onkeyup="filterCatatanAdmin()"
                               placeholder="Cari penerima, pengirim, judul..."
                               class="vh-input w-full pl-9 pr-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            <!-- Tab Switcher: Semua vs Catatan Siswa vs Catatan Guru -->
            <div class="px-6 pt-3 pb-0 border-b border-slate-100 dark:border-slate-700/60 flex gap-4 bg-slate-50/50 dark:bg-slate-900/30 overflow-x-auto">
                <button type="button" onclick="switchAdminCatatanTab('semua')" id="tabBtnAdminSemua"
                        class="pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <i class="fa-solid fa-layer-group text-[11px]"></i> Semua Catatan (<?= $countTotalAdmin ?>)
                </button>
                <button type="button" onclick="switchAdminCatatanTab('siswa')" id="tabBtnAdminSiswa"
                        class="pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <i class="fa-solid fa-graduation-cap text-[11px]"></i> Catatan Siswa (<?= $countSiswaAdmin ?>)
                </button>
                <button type="button" onclick="switchAdminCatatanTab('guru')" id="tabBtnAdminGuru"
                        class="pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <i class="fa-solid fa-chalkboard-user text-[11px]"></i> Catatan Guru (<?= $countGuruAdmin ?>)
                </button>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Panel Admin 0: Semua Catatan Gabungan (Siswa + Guru) -->
                <?php
                $allAdminNotes = array_merge($catatanSiswaAdmin, $catatanGuruAdmin);
                usort($allAdminNotes, function($a, $b) {
                    $ta = !empty($a->created_at) ? strtotime($a->created_at) : 0;
                    $tb = !empty($b->created_at) ? strtotime($b->created_at) : 0;
                    return $tb - $ta;
                });
                ?>
                <div id="panelAdminSemua" class="space-y-4">
                    <?php if (!empty($allAdminNotes)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($allAdminNotes as $can): 
                                $isSiswaItem = (($can->target_role ?? '') === 'siswa');
                            ?>
                                <div class="catatan-admin-item p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/50 hover:border-emerald-500/50 transition-all flex flex-col justify-between space-y-3"
                                     data-target="<?= htmlspecialchars(strtolower($can->target_name ?? '')) ?>"
                                     data-author="<?= htmlspecialchars(strtolower($can->author_name ?? '')) ?>"
                                     data-title="<?= htmlspecialchars(strtolower($can->title ?? '')) ?>"
                                     data-content="<?= htmlspecialchars(strtolower($can->content ?? '')) ?>">
                                    <div class="space-y-2.5">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-8 h-8 rounded-lg <?= $isSiswaItem ? 'bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300' : 'bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300' ?> flex items-center justify-center text-xs shrink-0">
                                                    <i class="fa-solid <?= $isSiswaItem ? 'fa-user-graduate' : 'fa-chalkboard-user' ?>"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                                            <?= htmlspecialchars($can->target_name ?? '-') ?>
                                                        </h3>
                                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold <?= $isSiswaItem ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40' : 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800/40' ?>">
                                                            <?= $isSiswaItem ? 'Siswa' : 'Guru' ?>
                                                        </span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-400 font-mono">
                                                        <?php if ($isSiswaItem): ?>
                                                            NIS: <?= htmlspecialchars($can->nis ?? '-') ?> &bull; <?= htmlspecialchars($can->nama_kelas ?? 'Kelas') ?>
                                                        <?php else: ?>
                                                            NIP: <?= htmlspecialchars($can->nip ?? '-') ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shrink-0">
                                                <i class="fa-regular fa-clock text-emerald-600 dark:text-emerald-400"></i>
                                                <?= !empty($can->created_at) ? date('d M Y, H:i', strtotime($can->created_at)) : '-' ?>
                                            </span>
                                        </div>

                                        <div class="p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                <i class="fa-solid fa-tag text-[10px] text-emerald-600 dark:text-emerald-400"></i>
                                                <?= htmlspecialchars($can->title ?? 'Catatan') ?>
                                            </p>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed break-words">
                                                <?= nl2br(htmlspecialchars($can->content ?? '')) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Pengirim: <strong class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($can->author_name ?? ($isSiswaItem ? 'Wali Kelas' : 'Kepala Sekolah')) ?></strong>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    onclick='openEditCatatanModal(<?= (int)$can->id ?>, "<?= $isSiswaItem ? 'siswa' : 'guru' ?>", <?= json_encode($can->target_name ?? "-") ?>, <?= json_encode($can->title ?? "Catatan") ?>, <?= json_encode($can->content ?? "") ?>)'
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition cursor-pointer"
                                                    title="Edit Catatan">
                                                <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                            </button>
                                            <button type="button"
                                                    onclick='openDeleteCatatanModal(<?= (int)$can->id ?>, "<?= $isSiswaItem ? 'siswa' : 'guru' ?>", <?= json_encode($can->target_name ?? "-") ?>, <?= json_encode($can->title ?? "Catatan") ?>)'
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-slate-700 hover:border-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer"
                                                    title="Hapus Catatan">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 px-4">
                            <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                            <p class="text-slate-400 text-xs">Belum ada riwayat catatan bimbingan di sistem.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Panel Admin 1: Catatan Siswa (Wali Kelas -> Siswa) -->
                <div id="panelAdminSiswa" class="space-y-4 hidden">
                    <?php if (!empty($catatanSiswaAdmin)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($catatanSiswaAdmin as $cs): ?>
                                <div class="catatan-admin-item p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/50 hover:border-emerald-500/50 transition-all flex flex-col justify-between space-y-3"
                                     data-target="<?= htmlspecialchars(strtolower($cs->target_name ?? '')) ?>"
                                     data-author="<?= htmlspecialchars(strtolower($cs->author_name ?? '')) ?>"
                                     data-title="<?= htmlspecialchars(strtolower($cs->title ?? '')) ?>"
                                     data-content="<?= htmlspecialchars(strtolower($cs->content ?? '')) ?>">
                                    <div class="space-y-2.5">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-xs shrink-0">
                                                    <i class="fa-solid fa-user-graduate"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                                        <?= htmlspecialchars($cs->target_name ?? '-') ?>
                                                    </h3>
                                                    <p class="text-[11px] text-slate-400 font-mono">
                                                        NIS: <?= htmlspecialchars($cs->nis ?? '-') ?> &bull; <?= htmlspecialchars($cs->nama_kelas ?? 'Kelas') ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shrink-0">
                                                <i class="fa-regular fa-clock text-emerald-600 dark:text-emerald-400"></i>
                                                <?= !empty($cs->created_at) ? date('d M Y, H:i', strtotime($cs->created_at)) : '-' ?>
                                            </span>
                                        </div>

                                        <div class="p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                <i class="fa-solid fa-tag text-[10px] text-emerald-600 dark:text-emerald-400"></i>
                                                <?= htmlspecialchars($cs->title ?? 'Catatan') ?>
                                            </p>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed break-words">
                                                <?= nl2br(htmlspecialchars($cs->content ?? '')) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Pengirim: <strong class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($cs->author_name ?? 'Wali Kelas') ?></strong>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    onclick='openEditCatatanModal(<?= (int)$cs->id ?>, "siswa", <?= json_encode($cs->target_name ?? "-") ?>, <?= json_encode($cs->title ?? "Catatan") ?>, <?= json_encode($cs->content ?? "") ?>)'
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition cursor-pointer"
                                                    title="Edit Catatan">
                                                <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                            </button>
                                            <button type="button"
                                                    onclick='openDeleteCatatanModal(<?= (int)$cs->id ?>, "siswa", <?= json_encode($cs->target_name ?? "-") ?>, <?= json_encode($cs->title ?? "Catatan") ?>)'
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-slate-700 hover:border-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer"
                                                    title="Hapus Catatan">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 px-4">
                            <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                            <p class="text-slate-400 text-xs">Belum ada catatan yang dikirim oleh Wali Kelas.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Panel Admin 2: Catatan Guru (Kepsek -> Guru) -->
                <div id="panelAdminGuru" class="space-y-4 hidden">
                    <?php if (!empty($catatanGuruAdmin)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($catatanGuruAdmin as $cg): ?>
                                <div class="catatan-admin-item p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/50 hover:border-emerald-500/50 transition-all flex flex-col justify-between space-y-3"
                                     data-target="<?= htmlspecialchars(strtolower($cg->target_name ?? '')) ?>"
                                     data-author="<?= htmlspecialchars(strtolower($cg->author_name ?? '')) ?>"
                                     data-title="<?= htmlspecialchars(strtolower($cg->title ?? '')) ?>"
                                     data-content="<?= htmlspecialchars(strtolower($cg->content ?? '')) ?>">
                                    <div class="space-y-2.5">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300 flex items-center justify-center text-xs shrink-0">
                                                    <i class="fa-solid fa-chalkboard-user"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                                        <?= htmlspecialchars($cg->target_name ?? '-') ?>
                                                    </h3>
                                                    <p class="text-[11px] text-slate-400 font-mono">
                                                        NIP: <?= htmlspecialchars($cg->nip ?? '-') ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shrink-0">
                                                <i class="fa-regular fa-clock text-emerald-600 dark:text-emerald-400"></i>
                                                <?= !empty($cg->created_at) ? date('d M Y, H:i', strtotime($cg->created_at)) : '-' ?>
                                            </span>
                                        </div>

                                        <div class="p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                <i class="fa-solid fa-tag text-[10px] text-emerald-600 dark:text-emerald-400"></i>
                                                <?= htmlspecialchars($cg->title ?? 'Catatan') ?>
                                            </p>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed break-words">
                                                <?= nl2br(htmlspecialchars($cg->content ?? '')) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Pengirim: <strong class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($cg->author_name ?? 'Kepala Sekolah') ?></strong>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    onclick='openEditCatatanModal(<?= (int)$cg->id ?>, "guru", <?= json_encode($cg->target_name ?? "-") ?>, <?= json_encode($cg->title ?? "Catatan") ?>, <?= json_encode($cg->content ?? "") ?>)'
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition cursor-pointer"
                                                    title="Edit Catatan">
                                                <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                            </button>
                                            <button type="button"
                                                    onclick='openDeleteCatatanModal(<?= (int)$cg->id ?>, "guru", <?= json_encode($cg->target_name ?? "-") ?>, <?= json_encode($cg->title ?? "Catatan") ?>)'
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-slate-700 hover:border-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer"
                                                    title="Hapus Catatan">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 px-4">
                            <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                            <p class="text-slate-400 text-xs">Belum ada catatan yang dikirim oleh Kepala Sekolah.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif (!$isWriter): ?>
        <!-- ── SISWA & GURU (READERS): 8 COLS LIST + 4 COLS INFO ── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri (8 Kolom): Daftar Catatan Diterima -->
            <div class="lg:col-span-8 vh-card overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                            <i class="fa-solid fa-clipboard-list text-base"></i>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">Riwayat Catatan Bimbingan</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pesan pembinaan dan evaluasi resmi dari pembimbing</p>
                        </div>
                    </div>
                    <div class="w-full sm:w-64">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                            <input type="text" id="searchCatatanReader" onkeyup="filterCatatanReader()"
                                   placeholder="Cari judul atau isi pesan..."
                                   class="vh-input w-full pl-9 pr-3 py-2 text-xs">
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <?php if (!empty($catatanList)): ?>
                        <div class="space-y-4" id="catatanReaderContainer">
                            <?php foreach ($catatanList as $catatan): ?>
                                <div class="catatan-card-item p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/50 hover:border-emerald-500/50 transition-all space-y-3"
                                     data-title="<?= htmlspecialchars(strtolower($catatan->title ?? '')) ?>"
                                     data-content="<?= htmlspecialchars(strtolower($catatan->content ?? '')) ?>"
                                     data-author="<?= htmlspecialchars(strtolower($catatan->author_name ?? '')) ?>">
                                    
                                    <div class="flex items-start justify-between gap-3 flex-wrap">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-xs shrink-0">
                                                <i class="fa-solid fa-note-sticky"></i>
                                            </span>
                                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                                <?= htmlspecialchars($catatan->title ?? 'Catatan') ?>
                                            </h3>
                                        </div>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-mono text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80">
                                            <i class="fa-regular fa-clock text-emerald-600 dark:text-emerald-400 text-[10px]"></i>
                                            <?= !empty($catatan->created_at) ? date('d M Y, H:i', strtotime($catatan->created_at)) . ' WIB' : '-' ?>
                                        </span>
                                    </div>

                                    <div class="p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                                        <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-200 whitespace-pre-wrap leading-relaxed">
                                            <?= nl2br(htmlspecialchars($catatan->content ?? '')) ?>
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                                        <span class="inline-flex items-center gap-1.5 font-medium">
                                            <i class="fa-solid fa-user-pen text-slate-400"></i>
                                            Dari: <strong class="text-slate-800 dark:text-slate-200"><?= htmlspecialchars($catatan->author_name) ?></strong>
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-mono">Bimbingan Khusus</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 px-4">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl mx-auto mb-3">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Belum Ada Catatan</h3>
                            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm max-w-sm mx-auto">
                                Anda belum menerima pesan pembinaan atau catatan khusus dari pembimbing saat ini.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kolom Kanan (4 Kolom): Panduan & Informasi Konseling -->
            <div class="lg:col-span-4 space-y-6">
                <div class="vh-card p-6 space-y-3.5">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <i class="fa-solid fa-circle-info text-emerald-600 dark:text-emerald-400 text-base"></i>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tentang Layanan Catatan</h3>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Layanan ini menjadi media pembinaan resmi antara Wali Kelas / Pimpinan Sekolah dengan Anda untuk menyampaikan motivasi, pengingat tugas, evaluasi kedisiplinan, maupun apresiasi prestasi.
                    </p>
                </div>

                <div class="vh-card p-6 space-y-3.5">
                    <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <i class="fa-solid fa-lightbulb text-amber-500 text-base"></i>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Panduan & Petunjuk</h3>
                    </div>
                    <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2.5">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0 text-[11px]"></i>
                            <span>Periksa catatan bimbingan Anda secara rutin setiap pekan.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0 text-[11px]"></i>
                            <span>Jika memerlukan konsultasi lebih lanjut, hubungi langsung Wali Kelas Anda di sekolah.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0 text-[11px]"></i>
                            <span>Semua catatan tersimpan dengan aman dan bersifat personal bagi akun Anda.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ── WALI KELAS & KEPSEK (WRITERS): 7 COLS HISTORY + 5 COLS FORM ── -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri (7 Kolom): Riwayat Catatan Bimbingan -->
            <div class="lg:col-span-7 space-y-6">
                <div class="vh-card overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-base"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">Riwayat Catatan Bimbingan</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    <?= $isWaliKelas ? 'Arsip catatan yang dikirim ke siswa kelas' : 'Arsip arahan bimbingan kepada dewan guru' ?>
                                </p>
                            </div>
                        </div>

                        <div class="w-full sm:w-56">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                <input type="text" id="searchCatatanWriter" onkeyup="filterCatatanWriter()"
                                       placeholder="Cari nama, judul..."
                                       class="vh-input w-full pl-9 pr-3 py-2 text-xs">
                            </div>
                        </div>
                    </div>

                    <?php if ($isWaliKelas): ?>
                        <!-- Tab Selector untuk Wali Kelas -->
                        <div class="px-6 pt-3 pb-0 border-b border-slate-100 dark:border-slate-700/60 flex gap-4 bg-slate-50/50 dark:bg-slate-900/30">
                            <button type="button" onclick="switchWkCatatanTab('terkirim')" id="tabBtnTerkirim"
                                    class="pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition-colors flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-paper-plane text-[11px]"></i> Terkirim ke Siswa (<?= count($catatanTerkirim) ?>)
                            </button>
                            <button type="button" onclick="switchWkCatatanTab('diterima')" id="tabBtnDiterima"
                                    class="pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-envelope-open-text text-[11px]"></i> Dari Kepsek (<?= count($catatanList) ?>)
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="p-4 sm:p-6">
                        <!-- Panel 1: Catatan Terkirim (Wali Kelas ke Siswa atau Kepsek ke Guru) -->
                        <div id="panelCatatanTerkirim" class="space-y-4">
                            <?php 
                            $listToDisplay = $catatanTerkirim;
                            $editTargetRole = $isKepsek ? 'guru' : 'siswa';
                            if (!empty($listToDisplay)): 
                            ?>
                                <?php foreach ($listToDisplay as $c): ?>
                                    <div class="catatan-writer-item p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/50 hover:border-emerald-500/50 transition-all space-y-3"
                                         data-target="<?= htmlspecialchars(strtolower($c->target_name ?? '')) ?>"
                                         data-title="<?= htmlspecialchars(strtolower($c->title ?? '')) ?>"
                                         data-content="<?= htmlspecialchars(strtolower($c->content ?? '')) ?>">
                                        
                                        <div class="flex items-start justify-between gap-3 flex-wrap">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-xs shrink-0">
                                                    <i class="fa-solid <?= $isKepsek ? 'fa-chalkboard-user' : 'fa-user-graduate' ?>"></i>
                                                </span>
                                                <div class="min-w-0">
                                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                                        <?= htmlspecialchars($c->target_name ?? '-') ?>
                                                    </h3>
                                                    <?php if (!empty($c->nis)): ?>
                                                        <p class="text-[11px] text-slate-400 font-mono">NIS: <?= htmlspecialchars($c->nis) ?></p>
                                                    <?php elseif (!empty($c->nip)): ?>
                                                        <p class="text-[11px] text-slate-400 font-mono">NIP: <?= htmlspecialchars($c->nip) ?></p>
                                                    <?php elseif (!empty($c->target_username)): ?>
                                                        <p class="text-[11px] text-slate-400 font-mono">@<?= htmlspecialchars($c->target_username) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-mono text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80">
                                                    <i class="fa-regular fa-clock text-emerald-600 dark:text-emerald-400 text-[10px]"></i>
                                                    <?= !empty($c->created_at) ? date('d M Y, H:i', strtotime($c->created_at)) : '-' ?>
                                                </span>
                                                <button type="button"
                                                        onclick='openEditCatatanModal(<?= (int)$c->id ?>, <?= json_encode($editTargetRole) ?>, <?= json_encode($c->target_name ?? "-") ?>, <?= json_encode($c->title ?? "Catatan") ?>, <?= json_encode($c->content ?? "") ?>)'
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition cursor-pointer"
                                                        title="Edit Catatan">
                                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                                </button>
                                                <button type="button"
                                                        onclick='openDeleteCatatanModal(<?= (int)$c->id ?>, <?= json_encode($editTargetRole) ?>, <?= json_encode($c->target_name ?? "-") ?>, <?= json_encode($c->title ?? "Catatan") ?>)'
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-slate-700 hover:border-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer"
                                                        title="Hapus Catatan">
                                                    <i class="fa-solid fa-trash-can text-[10px]"></i> Hapus
                                                </button>
                                            </div>
                                        </div>

                                        <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                <i class="fa-solid fa-tag text-[10px] text-emerald-600 dark:text-emerald-400"></i>
                                                <?= htmlspecialchars($c->title ?? 'Catatan') ?>
                                            </p>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 whitespace-pre-wrap leading-relaxed break-words">
                                                <?= nl2br(htmlspecialchars($c->content ?? '')) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-10 px-4">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-2.5">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-0.5">Belum Ada Catatan Dikirim</h3>
                                    <p class="text-slate-400 text-xs">Gunakan formulir di sebelah kanan untuk menulis catatan bimbingan baru.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($isWaliKelas): ?>
                            <!-- Panel 2: Catatan Diterima dari Kepsek (Khusus Wali Kelas) -->
                            <div id="panelCatatanDiterima" class="space-y-4 hidden">
                                <?php if (!empty($catatanList)): ?>
                                    <?php foreach ($catatanList as $ck): ?>
                                        <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-900/50 space-y-3">
                                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/60 text-sky-700 dark:text-sky-300 flex items-center justify-center text-xs shrink-0">
                                                        <i class="fa-solid fa-user-tie"></i>
                                                    </span>
                                                    <div>
                                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                                            <?= htmlspecialchars($ck->title ?? 'Instruksi Pimpinan') ?>
                                                        </h3>
                                                        <p class="text-[11px] text-slate-400">Dari: <?= htmlspecialchars($ck->author_name) ?></p>
                                                    </div>
                                                </div>
                                                <span class="text-[11px] font-mono text-slate-400">
                                                    <?= !empty($ck->created_at) ? date('d M Y, H:i', strtotime($ck->created_at)) : '-' ?>
                                                </span>
                                            </div>
                                            <div class="p-3.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                                                <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">
                                                    <?= nl2br(htmlspecialchars($ck->content ?? '')) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-10 px-4">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                                        <p class="text-slate-400 text-xs">Belum ada catatan khusus dari Kepala Sekolah.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan (5 Kolom): Form Tulis Catatan Baru -->
            <div class="lg:col-span-5 space-y-6">
                <div class="vh-card overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-pen-to-square text-emerald-600 dark:text-emerald-400 text-base"></i>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Tulis Catatan Bimbingan</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    <?= $isWaliKelas ? 'Kirim pesan bimbingan ke siswa kelas' : 'Kirim arahan bimbingan kepada guru' ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="<?= BASEURL ?>/catatan/store" class="space-y-4">
                            <?= CsrfHelper::input() ?>

                            <?php if ($isWaliKelas): ?>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider" for="target_id">Pilih Siswa Target</label>
                                    <select class="w-full" name="target_id" id="target_id" required>
                                        <option value="">Pilih Siswa...</option>
                                        <?php foreach ($data['siswas'] ?? [] as $siswa): ?>
                                            <option value="<?= $siswa->id ?>">
                                                <?= htmlspecialchars($siswa->name) ?> (<?= htmlspecialchars($siswa->username) ?>)<?= !empty($siswa->nis) ? ' - NIS: ' . htmlspecialchars($siswa->nis) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="target_role" value="siswa">
                                </div>
                            <?php else: ?>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider" for="target_id">Pilih Guru Target</label>
                                    <select class="w-full" name="target_id" id="target_id" required>
                                        <option value="">Pilih Guru...</option>
                                        <?php foreach ($data['gurus'] ?? [] as $guru): ?>
                                            <option value="<?= $guru->id ?>">
                                                <?= htmlspecialchars($guru->name) ?> &mdash; NIP: <?= htmlspecialchars($guru->nip ?? '-') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="target_role" value="guru">
                                </div>
                            <?php endif; ?>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider" for="title">Judul Catatan</label>
                                <input type="text"
                                       class="vh-input w-full px-3.5 py-2.5 text-xs"
                                       name="title" id="title"
                                       placeholder="Misal: Motivasi Belajar / Peringatan Kehadiran" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider" for="content">Isi Pesan Catatan</label>
                                <textarea class="vh-input w-full p-3.5 text-xs resize-y leading-relaxed"
                                          name="content" id="content"
                                          rows="5"
                                          placeholder="Tuliskan isi bimbingan, arahan, atau motivasi secara rinci..." required></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider" for="tanggal">Tanggal Catatan</label>
                                <input type="date"
                                       class="vh-input w-full px-3.5 py-2.5 text-xs font-mono"
                                       name="tanggal" id="tanggal"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <button type="submit" class="vh-btn-primary w-full py-2.5 flex items-center justify-center gap-2 cursor-pointer shadow-xs">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Kirim Catatan Bimbingan</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Modern styling for Tom Select with Dark Mode support */
.ts-control {
    border: 1px solid #cbd5e1 !important;
    border-radius: 0.75rem !important;
    padding: 0.625rem 0.875rem !important;
    font-size: 0.8125rem !important;
    background-color: #ffffff !important;
    color: #0f172a !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
    transition: all 0.2s ease;
}
.dark .ts-control {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
}
.ts-control.focus {
    border-color: #059669 !important;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
}
.dark .ts-control.focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
}
.ts-dropdown {
    border-radius: 0.75rem !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    z-index: 50 !important;
    background-color: #ffffff !important;
}
.dark .ts-dropdown {
    background-color: #1e293b !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
.ts-dropdown .option {
    padding: 0.625rem 0.875rem !important;
    font-size: 0.8125rem !important;
}
.ts-dropdown .option.active, .ts-dropdown .option:hover {
    background-color: #ecfdf5 !important;
    color: #065f46 !important;
}
.dark .ts-dropdown .option.active, .dark .ts-dropdown .option:hover {
    background-color: #334155 !important;
    color: #ffffff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetSelect = document.getElementById('target_id');
    if (targetSelect && typeof TomSelect !== 'undefined') {
        const isSiswa = <?= $isWaliKelas ? 'true' : 'false' ?>;
        const tsTarget = new TomSelect('#target_id', {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: isSiswa ? "Cari nama, NIS, atau username siswa..." : "Cari nama atau NIP guru...",
            maxOptions: 500,
            searchField: ['text']
        });

        const formCatatan = targetSelect.closest('form');
        if (formCatatan) {
            formCatatan.addEventListener('submit', function(e) {
                if (!targetSelect.value) {
                    e.preventDefault();
                    alert(isSiswa ? 'Harap pilih siswa target terlebih dahulu.' : 'Harap pilih guru target terlebih dahulu.');
                    tsTarget.focus();
                }
            });
        }
    }
});

function switchWkCatatanTab(tab) {
    const panelTerkirim = document.getElementById('panelCatatanTerkirim');
    const panelDiterima = document.getElementById('panelCatatanDiterima');
    const btnTerkirim   = document.getElementById('tabBtnTerkirim');
    const btnDiterima   = document.getElementById('tabBtnDiterima');

    if (tab === 'terkirim') {
        panelTerkirim?.classList.remove('hidden');
        panelDiterima?.classList.add('hidden');
        btnTerkirim.className = 'pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition-colors flex items-center gap-1.5 cursor-pointer';
        btnDiterima.className = 'pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer';
    } else {
        panelTerkirim?.classList.add('hidden');
        panelDiterima?.classList.remove('hidden');
        btnDiterima.className = 'pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition-colors flex items-center gap-1.5 cursor-pointer';
        btnTerkirim.className = 'pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer';
    }
}

function switchAdminCatatanTab(tab) {
    const panelSemua = document.getElementById('panelAdminSemua');
    const panelSiswa = document.getElementById('panelAdminSiswa');
    const panelGuru  = document.getElementById('panelAdminGuru');
    const btnSemua   = document.getElementById('tabBtnAdminSemua');
    const btnSiswa   = document.getElementById('tabBtnAdminSiswa');
    const btnGuru    = document.getElementById('tabBtnAdminGuru');

    const activeClass = 'pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-600 dark:text-emerald-400 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap';
    const inactiveClass = 'pb-3 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap';

    if (tab === 'semua') {
        panelSemua?.classList.remove('hidden');
        panelSiswa?.classList.add('hidden');
        panelGuru?.classList.add('hidden');
        if (btnSemua) btnSemua.className = activeClass;
        if (btnSiswa) btnSiswa.className = inactiveClass;
        if (btnGuru)  btnGuru.className  = inactiveClass;
    } else if (tab === 'siswa') {
        panelSemua?.classList.add('hidden');
        panelSiswa?.classList.remove('hidden');
        panelGuru?.classList.add('hidden');
        if (btnSemua) btnSemua.className = inactiveClass;
        if (btnSiswa) btnSiswa.className = activeClass;
        if (btnGuru)  btnGuru.className  = inactiveClass;
    } else {
        panelSemua?.classList.add('hidden');
        panelSiswa?.classList.add('hidden');
        panelGuru?.classList.remove('hidden');
        if (btnSemua) btnSemua.className = inactiveClass;
        if (btnSiswa) btnSiswa.className = inactiveClass;
        if (btnGuru)  btnGuru.className  = activeClass;
    }
}

function filterCatatanAdmin() {
    const q = (document.getElementById('searchCatatanAdmin')?.value || '').toLowerCase().trim();
    const items = document.querySelectorAll('.catatan-admin-item');
    items.forEach(el => {
        const target  = el.getAttribute('data-target') || '';
        const author  = el.getAttribute('data-author') || '';
        const title   = el.getAttribute('data-title') || '';
        const content = el.getAttribute('data-content') || '';
        const match = !q || target.includes(q) || author.includes(q) || title.includes(q) || content.includes(q);
        el.style.display = match ? '' : 'none';
    });
}

function filterCatatanReader() {
    const q = (document.getElementById('searchCatatanReader')?.value || '').toLowerCase().trim();
    const items = document.querySelectorAll('.catatan-card-item');
    items.forEach(el => {
        const title   = el.getAttribute('data-title') || '';
        const content = el.getAttribute('data-content') || '';
        const author  = el.getAttribute('data-author') || '';
        const match = !q || title.includes(q) || content.includes(q) || author.includes(q);
        el.style.display = match ? '' : 'none';
    });
}

function filterCatatanWriter() {
    const q = (document.getElementById('searchCatatanWriter')?.value || '').toLowerCase().trim();
    const items = document.querySelectorAll('.catatan-writer-item');
    items.forEach(el => {
        const target  = el.getAttribute('data-target') || '';
        const title   = el.getAttribute('data-title') || '';
        const content = el.getAttribute('data-content') || '';
        const match = !q || target.includes(q) || title.includes(q) || content.includes(q);
        el.style.display = match ? '' : 'none';
    });
}

function openEditCatatanModal(id, targetRole, targetName, title, content) {
    document.getElementById('editCatatanId').value = id;
    document.getElementById('editCatatanRole').value = targetRole;
    document.getElementById('editCatatanTargetLabel').textContent = targetName || '-';
    document.getElementById('editCatatanTitle').value = title || '';
    document.getElementById('editCatatanContent').value = content || '';
    document.getElementById('modalEditCatatan').classList.remove('hidden');
}

function closeEditCatatanModal() {
    document.getElementById('modalEditCatatan').classList.add('hidden');
}

function openDeleteCatatanModal(id, targetRole, targetName, title) {
    document.getElementById('deleteCatatanId').value = id;
    document.getElementById('deleteCatatanRole').value = targetRole;
    document.getElementById('deleteCatatanSummary').textContent = (title || 'Catatan') + ' (' + (targetName || '-') + ')';
    document.getElementById('modalDeleteCatatan').classList.remove('hidden');
}

function closeDeleteCatatanModal() {
    document.getElementById('modalDeleteCatatan').classList.add('hidden');
}
</script>

<!-- Modal Edit Catatan -->
<div id="modalEditCatatan" class="fixed inset-0 z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-t-2xl sm:rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-lg overflow-hidden">
        <form action="<?= BASEURL; ?>/catatan/update" method="POST">
            <?= CsrfHelper::input() ?>
            <input type="hidden" name="id" id="editCatatanId" value="">
            <input type="hidden" name="target_role" id="editCatatanRole" value="">
            <input type="hidden" name="redirect" value="/catatan">

            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Catatan</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Penerima: <strong id="editCatatanTargetLabel" class="text-emerald-700 dark:text-emerald-400">-</strong></p>
                </div>
                <button type="button" onclick="closeEditCatatanModal()" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul / Perihal Catatan</label>
                    <input type="text" name="title" id="editCatatanTitle" required maxlength="150"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Isi Catatan <span class="text-rose-500">*</span></label>
                    <textarea name="content" id="editCatatanContent" rows="5" required
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
            </div>

            <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" onclick="closeEditCatatanModal()" class="vh-btn-secondary justify-center text-xs">Batal</button>
                <button type="submit" class="vh-btn-primary justify-center text-xs px-5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Catatan -->
<div id="modalDeleteCatatan" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 text-center space-y-4">
        <form action="<?= BASEURL; ?>/catatan/delete" method="POST">
            <?= CsrfHelper::input() ?>
            <input type="hidden" name="id" id="deleteCatatanId" value="">
            <input type="hidden" name="target_role" id="deleteCatatanRole" value="">
            <input type="hidden" name="redirect" value="/catatan">

            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mx-auto">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Hapus Catatan Ini?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Catatan <strong id="deleteCatatanSummary" class="text-slate-800 dark:text-slate-200"></strong> akan dihapus permanen.
            </p>
            <div class="flex flex-col-reverse sm:flex-row items-center justify-center gap-2 pt-2">
                <button type="button" onclick="closeDeleteCatatanModal()" class="vh-btn-secondary w-full sm:w-auto justify-center text-xs">Batal</button>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-trash-can"></i> Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<?php $this->view('templates/footer'); ?>