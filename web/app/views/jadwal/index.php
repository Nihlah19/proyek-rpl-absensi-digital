<?php $this->view('templates/header', $data); ?>

<div id="pageContent" class="w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Kelola Jadwal Absensi</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Kelola hari libur dan jam operasional absensi sekolah</p>
        </div>
        <div class="flex gap-2 flex-wrap items-center">
            <?php 
                $totalAktif = 0;
                if (!empty($data['jadwal']) && is_array($data['jadwal'])) {
                    foreach ($data['jadwal'] as $j) {
                        if (!empty($j->status_aktif)) $totalAktif++;
                    }
                }
            ?>
            <span class="text-xs text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center gap-1.5">
                <i class="fa-regular fa-calendar-check text-emerald-600 dark:text-emerald-400"></i>
                Total Hari Sekolah: <span class="font-semibold text-slate-800 dark:text-white"><?= $totalAktif ?> Hari</span>
            </span>
        </div>
    </div>

    <!-- Jadwal Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="fa-solid fa-list text-emerald-600 dark:text-emerald-400"></i> Data Jadwal Operasional
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-xs sm:text-sm text-left">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-5 py-3.5 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider">Hari</th>
                        <th class="text-center px-4 py-3.5 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider">Status</th>
                        <th class="text-center px-4 py-3.5 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider">Mulai Buka Absen</th>
                        <th class="text-center px-4 py-3.5 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider">Jam Datang</th>
                        <th class="text-center px-4 py-3.5 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider">Batas Terlambat</th>
                        <th class="text-center px-4 py-3.5 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider">Jam Pulang</th>
                        <th class="text-center px-5 py-3.5 w-24 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if (!empty($data['jadwal']) && is_array($data['jadwal'])): ?>
                        <?php foreach($data['jadwal'] as $row): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($row->hari ?? '') ?></span>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <?php if (class_exists('BadgeHelper')): ?>
                                    <?= $row->status_aktif ? BadgeHelper::render('aktif', 'Aktif') : BadgeHelper::render('nonaktif', 'Libur') ?>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full <?= $row->status_aktif ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' ?>">
                                        <?= $row->status_aktif ? 'Aktif' : 'Libur' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-center text-slate-600 dark:text-slate-400 font-mono text-xs sm:text-sm whitespace-nowrap">
                                <?= !empty($row->jam_mulai_absensi) ? substr($row->jam_mulai_absensi, 0, 5) : '-' ?>
                            </td>
                            <td class="px-4 py-4 text-center text-slate-600 dark:text-slate-400 font-mono text-xs sm:text-sm whitespace-nowrap">
                                <?= !empty($row->jam_datang) ? substr($row->jam_datang, 0, 5) : '-' ?>
                            </td>
                            <td class="px-4 py-4 text-center text-slate-600 dark:text-slate-400 font-mono text-xs sm:text-sm whitespace-nowrap">
                                <?= !empty($row->batas_terlambat) ? substr($row->batas_terlambat, 0, 5) : '-' ?>
                            </td>
                            <td class="px-4 py-4 text-center text-slate-600 dark:text-slate-400 font-mono text-xs sm:text-sm whitespace-nowrap">
                                <?= !empty($row->jam_pulang) ? substr($row->jam_pulang, 0, 5) : '-' ?>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <a href="<?= BASEURL; ?>/jadwal/edit/<?= $row->id ?>"
                                   class="vh-btn-primary text-xs py-1.5 px-3">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400 dark:text-slate-500">
                                Belum ada data jadwal yang tersedia.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $this->view('templates/footer'); ?>