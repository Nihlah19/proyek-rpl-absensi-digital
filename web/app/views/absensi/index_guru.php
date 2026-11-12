<div class="w-full space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight">
                <?= htmlspecialchars($data['judul'] ?? 'Riwayat Absensi') ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Riwayat catatan kehadiran Anda sebagai dewan guru.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700">
        <form method="GET" action="<?= BASEURL ?>/absensi/guru" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Dari Tanggal</label>
                <input type="date" name="dari" value="<?= htmlspecialchars($data['dari'] ?? '') ?>" class="vh-input w-full px-3 py-2 text-xs sm:text-sm font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Sampai Tanggal</label>
                <input type="date" name="sampai" value="<?= htmlspecialchars($data['sampai'] ?? '') ?>" class="vh-input w-full px-3 py-2 text-xs sm:text-sm font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Keterangan</label>
                <select name="status" class="vh-input w-full px-3 py-2 text-xs sm:text-sm">
                    <option value="">Semua Keterangan</option>
                    <option value="hadir" <?= ($data['status'] ?? '') === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                    <option value="izin" <?= ($data['status'] ?? '') === 'izin' ? 'selected' : '' ?>>Izin</option>
                    <option value="sakit" <?= ($data['status'] ?? '') === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                    <option value="alfa" <?= ($data['status'] ?? '') === 'alfa' ? 'selected' : '' ?>>Alfa</option>
                </select>
            </div>
            <div>
                <button type="submit" class="vh-btn-primary w-full text-xs sm:text-sm flex items-center justify-center gap-1.5 py-2.5">
                    <i class="fa-solid fa-filter"></i> Filter Data
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[550px] text-xs sm:text-sm text-left text-slate-600 dark:text-slate-300">
                <thead class="text-xs text-slate-500 dark:text-slate-400 uppercase bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-center w-12">No</th>
                        <th class="px-5 py-3.5 font-semibold">Tanggal</th>
                        <th class="px-5 py-3.5 font-semibold text-center">Jam Datang</th>
                        <th class="px-5 py-3.5 font-semibold text-center">Jam Pulang</th>
                        <th class="px-5 py-3.5 font-semibold text-center">Keterangan</th>
                        <th class="px-5 py-3.5 font-semibold text-center">Status Waktu</th>
                        <th class="px-5 py-3.5 font-semibold">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php if (empty($data['rows'])): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                            <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>
                            <p>Tidak ada data absensi pada periode ini.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = (($data['page'] ?? 1) - 1) * ($data['perPage'] ?? 15) + 1;
                        foreach ($data['rows'] as $row): 
                        ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-5 py-3.5 text-center font-mono text-slate-400 dark:text-slate-500"><?= $no++ ?></td>
                            <td class="px-5 py-3.5 font-medium text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= date('d M Y', strtotime($row->tanggal)) ?></td>
                            <td class="px-5 py-3.5 text-center">
                                <?php if($row->jam_datang): ?>
                                    <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-md font-medium text-xs font-mono border border-emerald-200/60 dark:border-emerald-800/40">
                                        <i class="fa-regular fa-clock"></i> <?= substr($row->jam_datang, 0, 5) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 dark:text-slate-600 font-bold">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <?php if($row->jam_pulang): ?>
                                    <span class="inline-flex items-center gap-1.5 text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/50 px-2 py-0.5 rounded-md font-medium text-xs font-mono border border-sky-200/60 dark:border-sky-800/40">
                                        <i class="fa-regular fa-clock"></i> <?= substr($row->jam_pulang, 0, 5) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 dark:text-slate-600 font-bold">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <?php
                                $rawSt = strtolower((string)($row->status ?? 'alfa'));
                                $ketKey = ($rawSt === 'terlambat') ? 'hadir' : $rawSt;
                                $ketLabels = [
                                    'hadir' => 'Hadir',
                                    'izin'  => 'Izin',
                                    'sakit' => 'Sakit',
                                    'alfa'  => 'Alfa',
                                ];
                                $statusColors = [
                                    'hadir'     => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
                                    'izin'      => 'bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
                                    'sakit'     => 'bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
                                    'alfa'      => 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
                                ];
                                $colorClass = $statusColors[$ketKey] ?? 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border-slate-200 dark:border-slate-600';
                                $ketText = $ketLabels[$ketKey] ?? ucfirst($ketKey);
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold border <?= $colorClass ?> uppercase tracking-wider">
                                    <?= htmlspecialchars($ketText) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <?php if ($rawSt === 'hadir'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold border bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50">
                                        Tepat Waktu
                                    </span>
                                <?php elseif ($rawSt === 'terlambat'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold border bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800/50">
                                        Terlambat
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 dark:text-slate-600 font-bold">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                <?= htmlspecialchars($row->keterangan ?? '-') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php 
        $total = $data['total'] ?? 0;
        $perPage = $data['perPage'] ?? 15;
        $page = $data['page'] ?? 1;
        $totalPages = $perPage > 0 ? ceil($total / $perPage) : 1;
        
        if ($totalPages > 1): 
        ?>
        <div class="px-5 py-3.5 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($data['rows']) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total ?></span> data
            </p>
            <div class="flex items-center gap-1">
                <?php for($i=1; $i<=$totalPages; $i++): ?>
                    <a href="<?= BASEURL ?>/absensi/guru?dari=<?= $data['dari'] ?? '' ?>&sampai=<?= $data['sampai'] ?? '' ?>&status=<?= $data['status'] ?? '' ?>&page=<?= $i ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold transition-colors <?= $i == $page ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>