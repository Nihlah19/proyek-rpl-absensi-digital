<?php
/**
 * panel_pembimbing_pkl.php
 * Komponen Dashboard untuk Guru Pembimbing PKL:
 * Menampilkan Akses Cepat Pengaturan PKL dan Log Riwayat Presensi Siswa PKL Terbaru.
 */
$pkl = $data['pkl'] ?? null;
if (!$pkl) return;
?>

<div class="bg-white dark:bg-slate-900 border border-indigo-200/90 dark:border-indigo-900/60 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-indigo-100 dark:border-indigo-900/40">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-sm shrink-0">
                <i class="fa-solid fa-user-tie"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 dark:text-slate-100">
                        Panel Guru Pembimbing PKL
                    </h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        Pembimbing Aktif
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Kelas bimbingan: 
                    <?php 
                    $kelasNames = array_map(function($k) { return htmlspecialchars($k->nama_kelas); }, $pkl['kelasList'] ?? []);
                    echo !empty($kelasNames) ? '<strong class="text-indigo-600 dark:text-indigo-400">' . implode(', ', $kelasNames) . '</strong>' : '<em>Belum ada kelas teralokasi</em>';
                    ?>
                </p>
            </div>
        </div>

        <!-- 2 Tombol Utama: Pengaturan PKL & Log Riwayat / Monitoring -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/pkl/kelompok"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white transition-colors shadow-xs">
                <i class="fa-solid fa-sliders"></i> Pengaturan Kelompok &amp; Lokasi PKL
            </a>
            <a href="<?= BASEURL; ?>/pkl/monitoring"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 transition-colors">
                <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i> Log Riwayat &amp; Monitoring Presensi
            </a>
        </div>
    </div>

    <!-- 4 Metric Cards PKL -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700">
            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 block">Total Siswa Bimbingan</span>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono"><?= $pkl['total_siswa'] ?? 0 ?></span>
                <span class="text-xs text-slate-400">siswa</span>
            </div>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700">
            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 block">Tempat / DU-DI Mitra</span>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono"><?= $pkl['total_kelompok'] ?? 0 ?></span>
                <span class="text-xs text-slate-400">mitra</span>
            </div>
        </div>
        <div class="p-3.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60">
            <span class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400 block">Hadir / Masuk Hari Ini</span>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-xl sm:text-2xl font-bold text-emerald-700 dark:text-emerald-300 font-mono"><?= $pkl['masuk'] ?? 0 ?></span>
                <span class="text-xs text-emerald-600/70">siswa</span>
            </div>
        </div>
        <div class="p-3.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60">
            <span class="text-[11px] font-medium text-amber-700 dark:text-amber-400 block">Belum Presensi Hari Ini</span>
            <div class="mt-1 flex items-baseline gap-1">
                <span class="text-xl sm:text-2xl font-bold text-amber-700 dark:text-amber-300 font-mono"><?= $pkl['belum'] ?? 0 ?></span>
                <span class="text-xs text-amber-600/70">siswa</span>
            </div>
        </div>
    </div>

    <!-- Log Riwayat Absensi PKL Table -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-600"></i> Log Riwayat Presensi Siswa PKL (Terbaru)
            </h3>
            <a href="<?= BASEURL; ?>/pkl/monitoring" class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-semibold hover:underline flex items-center gap-1">
                Buka Seluruh Monitoring <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <?php if (empty($pkl['recentLogs'])): ?>
            <div class="text-center py-8 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-dashed border-slate-200 dark:border-slate-700 text-slate-400">
                <i class="fa-solid fa-clipboard-user text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                <p class="text-xs font-medium">Belum ada riwayat aktivitas presensi siswa PKL.</p>
                <a href="<?= BASEURL; ?>/pkl/kelompok" class="mt-2 inline-block text-xs text-indigo-600 hover:underline font-semibold">
                    Atur Kelompok &amp; Lokasi PKL Sekarang &rarr;
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 uppercase tracking-wider text-[11px] font-semibold border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Tempat PKL</th>
                            <th class="px-4 py-3">Waktu Presensi</th>
                            <th class="px-4 py-3 text-center">Foto Selfie</th>
                            <th class="px-4 py-3">Status Lokasi</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                        <?php foreach ($pkl['recentLogs'] as $log): ?>
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800 dark:text-slate-100"><?= htmlspecialchars($log->nama_siswa) ?></div>
                                <div class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($log->nis ?? '-') ?> &middot; <?= htmlspecialchars($log->nama_kelas ?? '') ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-700 dark:text-slate-200"><?= htmlspecialchars($log->nama_tempat ?? $log->nama_kelompok ?? 'Belum Ditentukan') ?></div>
                                <div class="text-[11px] text-slate-400"><?= htmlspecialchars($log->nama_kelompok ?? '') ?></div>
                            </td>
                            <td class="px-4 py-3 font-mono">
                                <div><i class="fa-solid fa-arrow-right-to-bracket text-emerald-500 mr-1"></i> <?= $log->jam_datang ? date('H:i', strtotime($log->jam_datang)) : '-' ?></div>
                                <?php if ($log->jam_pulang): ?>
                                <div class="text-slate-400"><i class="fa-solid fa-arrow-right-from-bracket text-blue-500 mr-1"></i> <?= date('H:i', strtotime($log->jam_pulang)) ?></div>
                                <?php endif; ?>
                                <div class="text-[10px] text-slate-400 mt-0.5"><?= date('d/m/Y', strtotime($log->tanggal)) ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if (!empty($log->foto_masuk)): ?>
                                    <a href="<?= BASEURL; ?>/<?= htmlspecialchars($log->foto_masuk) ?>" target="_blank" class="inline-block relative group" title="Lihat Foto Selfie">
                                        <img src="<?= BASEURL; ?>/<?= htmlspecialchars($log->foto_masuk) ?>" alt="Selfie" class="w-9 h-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700 shadow-2xs group-hover:scale-105 transition-transform">
                                        <span class="absolute inset-0 bg-black/30 rounded-lg opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] transition-opacity">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($log->jarak_meter !== null): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold <?= $log->jarak_meter <= ($log->radius_meter ?? 50) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-950 dark:text-red-300' ?>">
                                        <i class="fa-solid fa-location-dot text-[9px]"></i> <?= $log->jarak_meter ?> m
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 text-[11px]">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($log->jam_pulang): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200">
                                        Selesai PKL
                                    </span>
                                <?php elseif ($log->jam_datang): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200">
                                        Sedang PKL
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        <?= strtoupper($log->status ?? 'BELUM') ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
