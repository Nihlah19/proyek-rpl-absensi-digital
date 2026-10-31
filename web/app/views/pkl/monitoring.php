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
                <span class="text-slate-800 dark:text-slate-200 font-medium">Monitoring Presensi</span>
                <?php if ($activeKelas): ?>
                    <span>/</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                        <?= htmlspecialchars($activeKelas->nama_kelas) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Monitoring Presensi Siswa PKL
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Pantau kehadiran siswa PKL secara real-time berdasarkan titik radius GPS yang telah divalidasi.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/pkl/kelompok<?= $activeKid ? '?kelas_id=' . $activeKid : '' ?>" class="btn-secondary text-xs">
                <i class="fa-solid fa-layer-group text-emerald-600"></i> Kelola Kelompok PKL
            </a>

            <!-- Selector Kelas untuk Admin -->
            <?php if ($userRole === 'admin' && !empty($allKelasPkl)): ?>
            <div class="relative inline-block text-left" id="dropdownKelasAdminMon">
                <button type="button" onclick="toggleDropdownKelasMon()" class="btn-secondary text-xs flex items-center gap-2">
                    <i class="fa-solid fa-chalkboard text-slate-500"></i>
                    <span>Kelas: <strong><?= $activeKelas ? htmlspecialchars($activeKelas->nama_kelas) : 'Pilih Kelas' ?></strong></span>
                    <i class="fa-solid fa-chevron-down text-[10px] ml-1 opacity-60"></i>
                </button>
                <div id="menuKelasAdminMon" class="hidden absolute right-0 mt-2 w-56 rounded-xl bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 z-50 py-1 text-xs">
                    <div class="px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pilih Kelas PKL</div>
                    <?php foreach ($allKelasPkl as $kp): ?>
                        <a href="<?= BASEURL; ?>/pkl/monitoring?kelas_id=<?= $kp->id; ?>&tanggal=<?= $tanggal ?>" class="flex items-center justify-between px-3.5 py-2 <?= ($activeKid === (int)$kp->id) ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50' ?>">
                            <span><?= htmlspecialchars($kp->nama_kelas); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filter Tanggal -->
            <form action="<?= BASEURL; ?>/pkl/monitoring" method="GET" class="flex items-center">
                <?php if ($userRole === 'admin' && $activeKid): ?>
                    <input type="hidden" name="kelas_id" value="<?= $activeKid ?>">
                <?php endif; ?>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="px-3 py-2 text-xs font-mono rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <!-- ── STATS ROW (4 METRIC CARDS) ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Siswa PKL -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Siswa PKL</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                            <?= $stats['total'] ?>
                        </span>
                        <span class="text-xs text-slate-400">siswa</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Tanggal</span>
                <span class="font-mono text-slate-700 dark:text-slate-300"><?= date('d M Y', strtotime($tanggal)) ?></span>
            </div>
        </div>

        <!-- Card 2: Sedang PKL (Masuk) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Sedang PKL (Masuk)</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tracking-tight tabular-nums">
                            <?= $stats['masuk'] ?>
                        </span>
                        <span class="text-xs text-slate-400">siswa</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Sudah Scan Masuk</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-medium">Aktif di Lokasi</span>
            </div>
        </div>

        <!-- Card 3: Sudah Pulang -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Sudah Pulang</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-sky-600 dark:text-sky-400 font-mono tracking-tight tabular-nums">
                            <?= $stats['pulang'] ?>
                        </span>
                        <span class="text-xs text-slate-400">siswa</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Presensi Lengkap</span>
                <span class="font-mono font-medium text-sky-600 dark:text-sky-400">Selesai</span>
            </div>
        </div>

        <!-- Card 4: Belum Presensi -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Belum Presensi</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold <?= $stats['belum'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' ?> font-mono tracking-tight tabular-nums">
                            <?= $stats['belum'] ?>
                        </span>
                        <span class="text-xs text-slate-400">siswa</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Belum Hadir</span>
                <span class="font-mono font-medium <?= $stats['belum'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' ?>">
                    <?= $stats['belum'] > 0 ? 'Belum Absen' : 'Nihil' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ── TABEL PRESENSI SISWA PKL ── -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs flex flex-col">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">
                    Data Presensi PKL: <?= date('d F Y', strtotime($tanggal)) ?>
                </h2>
            </div>
            <span class="px-2.5 py-1 rounded-md text-xs font-mono font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                <?= count($rows) ?> Siswa Terdaftar
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase bg-slate-50/70 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-center w-12 font-mono">No</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">Kelompok &amp; Tempat PKL</th>
                        <th class="px-4 py-3">Presensi Masuk</th>
                        <th class="px-4 py-3">Presensi Pulang</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-2xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada data siswa untuk kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($rows as $r): ?>
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors h-14">
                                <td class="px-4 py-3 text-center text-slate-400 font-mono tabular-nums"><?= $no++ ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold text-[11px] shrink-0 border border-slate-200 dark:border-slate-700">
                                            <?= strtoupper(substr($r->nama_siswa, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100"><?= htmlspecialchars($r->nama_siswa) ?></div>
                                            <div class="text-[10px] text-slate-400 font-mono">NIS: <?= htmlspecialchars($r->nis) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($r->nama_kelompok)): ?>
                                        <div class="font-medium text-slate-900 dark:text-slate-100"><?= htmlspecialchars($r->nama_kelompok) ?></div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-building text-slate-400"></i>
                                            <?= htmlspecialchars($r->nama_tempat) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            Belum ada kelompok
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if (!empty($r->jam_datang)): ?>
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($r->foto_masuk)): ?>
                                                <img src="<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_masuk) ?>"
                                                     alt="Foto Masuk" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 cursor-pointer hover:opacity-90 transition-opacity"
                                                     onclick="previewFoto('<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_masuk) ?>', 'Foto Masuk - <?= htmlspecialchars(addslashes($r->nama_siswa)) ?>')">
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-mono font-semibold text-emerald-700 dark:text-emerald-400 tabular-nums">
                                                    <?= date('H:i', strtotime($r->jam_datang)) ?> WIB
                                                </div>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                                    Jarak GPS: <strong class="text-slate-800 dark:text-slate-200 font-mono"><?= $r->jarak_meter !== null ? round((float)$r->jarak_meter) . 'm' : '-' ?></strong>
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
                                                     alt="Foto Pulang" class="w-10 h-10 rounded-lg object-cover border border-slate-200 dark:border-slate-700 cursor-pointer hover:opacity-90 transition-opacity"
                                                     onclick="previewFoto('<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($r->foto_pulang) ?>', 'Foto Pulang - <?= htmlspecialchars(addslashes($r->nama_siswa)) ?>')">
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-mono font-semibold text-sky-700 dark:text-sky-400 tabular-nums">
                                                    <?= date('H:i', strtotime($r->jam_pulang)) ?> WIB
                                                </div>
                                                <div class="text-[10px] text-slate-400">Selesai</div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-mono">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if (!empty($r->jam_pulang)): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                        </span>
                                    <?php elseif (!empty($r->jam_datang)): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50">
                                            <i class="fa-solid fa-briefcase text-[10px]"></i> Sedang PKL
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
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

</div>

<!-- ── MODAL PREVIEW FOTO SELFIE ── -->
<div id="modalFoto" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-lg overflow-hidden animate-fade-in">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white" id="modalFotoTitle">Foto Selfie Presensi</h3>
            <button type="button" onclick="closeModalFoto()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>
        <div class="p-4 text-center bg-slate-950 flex items-center justify-center min-h-[300px]">
            <img id="imgPreview" src="" alt="Preview" class="max-h-[460px] w-auto rounded-lg object-contain shadow-md">
        </div>
    </div>
</div>

<script>
function toggleDropdownKelasMon() {
    const el = document.getElementById('menuKelasAdminMon');
    if (el) el.classList.toggle('hidden');
}

document.addEventListener('click', (e) => {
    const drop = document.getElementById('dropdownKelasAdminMon');
    const menu = document.getElementById('menuKelasAdminMon');
    if (drop && menu && !drop.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

function previewFoto(url, title) {
    document.getElementById('modalFotoTitle').innerText = title;
    document.getElementById('imgPreview').src = url;
    document.getElementById('modalFoto').classList.remove('hidden');
}

function closeModalFoto() {
    document.getElementById('modalFoto').classList.add('hidden');
}
</script>
