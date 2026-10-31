<div class="w-full space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Pengajuan Pulang Cepat</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Daftar siswa yang mengajukan permohonan pulang lebih awal hari ini.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-xs sm:text-sm text-left border-collapse text-slate-600 dark:text-slate-300">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-5 font-semibold">Tanggal</th>
                        <th class="py-3.5 px-5 font-semibold">Siswa</th>
                        <th class="py-3.5 px-5 font-semibold text-center">Kelas</th>
                        <th class="py-3.5 px-5 font-semibold text-center">Jam Tap</th>
                        <th class="py-3.5 px-5 font-semibold">Alasan</th>
                        <th class="py-3.5 px-5 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-5 font-semibold text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if (empty($data['pengajuan'])): ?>
                    <tr>
                        <td colspan="7" class="py-12 px-6 text-center text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                            <p>Belum ada data pengajuan pulang cepat.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($data['pengajuan'] as $p): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="py-3.5 px-5 font-mono text-slate-500 dark:text-slate-400 whitespace-nowrap"><?= date('d/m/Y', strtotime($p->tanggal)) ?></td>
                            <td class="py-3.5 px-5">
                                <span class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($p->nama_siswa) ?></span>
                                <div class="text-[11px] font-mono text-slate-400"><?= htmlspecialchars($p->nis) ?></div>
                            </td>
                            <td class="py-3.5 px-5 text-center font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($p->nama_kelas) ?></td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/50 px-2 py-0.5 rounded-md text-xs font-mono font-medium">
                                    <i class="fa-regular fa-clock"></i> <?= date('H:i', strtotime($p->jam_pengajuan)) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-5 max-w-xs truncate" title="<?= htmlspecialchars($p->alasan) ?>">
                                <?= htmlspecialchars($p->alasan ?: '-') ?>
                            </td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                <?php
                                $status = strtoupper($p->status ?? 'PENDING');
                                if ($status === 'PENDING') {
                                    $badge = 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800/50';
                                } elseif ($status === 'APPROVED') {
                                    $badge = 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50';
                                } elseif ($status === 'REJECTED') {
                                    $badge = 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800/50';
                                } else {
                                    $badge = 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $badge ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                <?php if ($p->status === 'PENDING'): ?>
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button onclick="approvePengajuan(<?= $p->id ?>)" class="p-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors cursor-pointer shadow-xs" title="Setujui">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                        <button onclick="rejectPengajuan(<?= $p->id ?>)" class="p-1.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors cursor-pointer shadow-xs" title="Tolak">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">Selesai</span>
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

<script>
async function approvePengajuan(id) {
    if (!confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')) return;
    
    try {
        const res = await fetch(`<?= BASEURL ?>/pulangcepat/approve/${id}`, { method: 'POST' });
        const json = await res.json();
        alert(json.message);
        if (json.ok) location.reload();
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}

async function rejectPengajuan(id) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (reason === null) return;
    if (reason.trim() === '') {
        alert('Alasan penolakan wajib diisi!');
        return;
    }
    
    try {
        const res = await fetch(`<?= BASEURL ?>/pulangcepat/reject/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason: reason })
        });
        const json = await res.json();
        alert(json.message);
        if (json.ok) location.reload();
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}
</script>
