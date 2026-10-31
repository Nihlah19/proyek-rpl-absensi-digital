<?php $this->view('templates/header', $data); ?>
<div id="pageContent" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kelola Jadwal Guru</h1>
            <p class="text-slate-500 text-sm mt-1">
                Guru: <span class="font-bold text-slate-700"><?= htmlspecialchars($data['guru']->nama_guru ?? ('ID: ' . $data['guru_id'])) ?></span>
                <?php if (!empty($data['guru']->nip)): ?>
                    <span class="text-slate-400 font-mono text-xs">(NIP: <?= htmlspecialchars($data['guru']->nip) ?>)</span>
                <?php endif; ?>
                &middot; Atur jadwal mengajar per hari (Senin &ndash; Sabtu)
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/jadwal/guru"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-600 rounded-lg hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Jadwal Guru Table -->
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-calendar-week text-secondary"></i>Jadwal Mingguan
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider w-20">Hari</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider w-24">Ada Jadwal</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Jam Datang</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Batas Terlambat</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Jam Pulang</th>
                        <th class="text-center px-5 py-3 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($data['jadwal'] as $j): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-medium text-slate-700"><?= $j->hari; ?></span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <?php if ($j->is_active): ?>
                                <?= BadgeHelper::render('aktif', 'Ya') ?>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                    <i class="fa-solid fa-circle text-[8px]"></i> Tidak
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600 font-mono">
                            <?= $j->is_active ? $j->jam_datang : '-' ?>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600 font-mono">
                            <?= $j->is_active ? $j->batas_terlambat : '-' ?>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600 font-mono">
                            <?= $j->is_active ? $j->jam_pulang : '-' ?>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="<?= BASEURL; ?>/jadwal/edit_guru/<?= $j->id; ?>"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-warning/10 text-warning text-xs font-medium rounded-lg hover:bg-warning/20 transition-colors border border-warning/20">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->view('templates/footer'); ?>