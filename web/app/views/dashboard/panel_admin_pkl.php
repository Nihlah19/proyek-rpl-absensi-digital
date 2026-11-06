<?php
/**
 * panel_admin_pkl.php
 * Komponen Dashboard Admin:
 * Pusat Pemantauan & Tata Kelola Siswa PKL, Guru Pembimbing, dan Log Presensi Real-Time.
 */
$ap = $data['adminPkl'] ?? null;
if (!$ap) return;
?>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs space-y-6">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-white flex items-center justify-center text-xl shadow-sm shrink-0">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                        Pusat Pemantauan &amp; Tata Kelola Siswa PKL
                    </h2>
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        Fitur Terintegrasi
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Monitoring presensi mandiri geofencing, foto selfie kamera, notifikasi Telegram multi-bot, dan pengelolaan guru pembimbing per jurusan.
                </p>
            </div>
        </div>

        <!-- 5 Tombol Pintas Admin PKL -->
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/users" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition-colors">
                <i class="fa-solid fa-user-tie"></i> Kelola Pembimbing
            </a>
            <a href="<?= BASEURL; ?>/pkl/kelompok" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition-colors">
                <i class="fa-solid fa-map-location-dot"></i> Kelompok &amp; Maps
            </a>
            <a href="<?= BASEURL; ?>/pkl/monitoring" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-100 transition-colors">
                <i class="fa-solid fa-camera-retro"></i> Monitoring Presensi
            </a>
            <a href="<?= BASEURL; ?>/telegram" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 hover:bg-sky-100 transition-colors">
                <i class="fa-brands fa-telegram"></i> Setup Bot Telegram
            </a>
            <a href="<?= BASEURL; ?>/rekapabsensi" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 transition-colors">
                <i class="fa-solid fa-file-excel"></i> Rekap PKL
            </a>
        </div>
    </div>

    <!-- 4 Metric Cards PKL -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Total Siswa PKL -->
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 rounded-xl p-4">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Siswa PKL</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums"><?= $ap['totalSiswa'] ?></span>
                        <span class="text-xs text-slate-400">siswa</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-[11px] text-slate-400">
                Dari kelas berstatus PKL aktif
            </div>
        </div>

        <!-- 2. Guru Pembimbing PKL -->
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 rounded-xl p-4">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Pembimbing PKL</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-indigo-600 dark:text-indigo-400 font-mono tracking-tight tabular-nums"><?= $ap['totalPembimbing'] ?></span>
                        <span class="text-xs text-slate-400">guru</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-[11px] text-slate-400">
                Teralokasi per jurusan (TKJ, TKR, TSM)
            </div>
        </div>

        <!-- 3. Mitra DU/DI / Kelompok -->
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 rounded-xl p-4">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Mitra DU-DI / Kelompok</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums"><?= $ap['totalKelompok'] ?></span>
                        <span class="text-xs text-slate-400">titik</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-building"></i>
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-[11px] text-slate-400">
                Terverifikasi koordinat Google Maps
            </div>
        </div>

        <!-- 4. Kehadiran Siswa PKL Hari Ini -->
        <div class="bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl p-4">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider block">Presensi PKL Hari Ini</span>
                    <div class="mt-2 flex items-baseline gap-1.5">
                        <span class="text-2xl sm:text-3xl font-bold text-emerald-700 dark:text-emerald-300 font-mono tracking-tight tabular-nums"><?= $ap['masuk'] ?></span>
                        <span class="text-xs text-emerald-600/70">/ <?= $ap['totalSiswa'] ?> siswa</span>
                    </div>
                </div>
                <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
            </div>
            <div class="mt-2.5 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/60 text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center justify-between">
                <span>Pulang: <?= $ap['pulang'] ?> siswa</span>
                <span>Belum: <?= $ap['belum'] ?> siswa</span>
            </div>
        </div>
    </div>

    <!-- Dual Content Grid: Daftar Pembimbing & Log Presensi -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        <!-- Tabel Guru Pembimbing PKL (5 Cols) -->
        <div class="lg:col-span-5 bg-slate-50/80 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200/70 dark:border-slate-700">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-chalkboard-user text-indigo-600"></i> Guru Pembimbing PKL
                </h3>
                <a href="<?= BASEURL; ?>/users" class="text-[11px] text-indigo-600 hover:underline font-semibold">
                    Kelola Guru &rarr;
                </a>
            </div>

            <?php if (empty($ap['pembimbingList'])): ?>
                <div class="text-center py-6 text-slate-400 text-xs">
                    <i class="fa-solid fa-user-xmark text-2xl mb-1.5 text-slate-300 block"></i>
                    Belum ada guru yang ditugaskan sebagai pembimbing PKL.
                    <a href="<?= BASEURL; ?>/users" class="mt-1 block text-indigo-600 font-semibold hover:underline">Tugaskan Sekarang</a>
                </div>
            <?php else: ?>
                <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                    <?php foreach ($ap['pembimbingList'] as $pem): ?>
                    <div class="p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 flex items-start justify-between gap-2 shadow-2xs">
                        <div class="min-w-0">
                            <div class="font-bold text-xs text-slate-800 dark:text-slate-100 truncate">
                                <?= htmlspecialchars($pem->guru_nama) ?>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                NIP: <?= htmlspecialchars($pem->nip ?? '-') ?>
                            </div>
                            <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                <?php if (!empty($pem->bimbingan_jurusan)): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold uppercase bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200">
                                        <?= htmlspecialchars($pem->bimbingan_jurusan) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Kelas: <strong><?= htmlspecialchars($pem->bimbingan_kelas ?? 'Belum ada kelas') ?></strong>
                                </span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 block font-mono">
                                <?= (int)$pem->total_siswa_bimbingan ?> siswa
                            </span>
                            <a href="<?= BASEURL; ?>/users/editGuru/<?= $pem->id ?>" class="text-[10px] text-slate-400 hover:text-indigo-600 hover:underline inline-block mt-1">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabel Log Riwayat Presensi Real-Time Siswa PKL (7 Cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 p-4 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> Log Presensi Siswa PKL Terkini
                </h3>
                <a href="<?= BASEURL; ?>/pkl/monitoring" class="text-[11px] text-indigo-600 hover:underline font-semibold">
                    Monitoring Lengkap &rarr;
                </a>
            </div>

            <?php if (empty($ap['recentLogs'])): ?>
                <div class="text-center py-10 text-slate-400 text-xs">
                    <i class="fa-solid fa-clipboard-user text-3xl mb-2 text-slate-300 block"></i>
                    Belum ada rekaman presensi mandiri siswa PKL hari ini.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700 max-h-72 overflow-y-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 uppercase tracking-wider text-[10px] font-semibold sticky top-0 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-3 py-2.5">Siswa</th>
                                <th class="px-3 py-2.5">Tempat PKL</th>
                                <th class="px-3 py-2.5">Waktu</th>
                                <th class="px-3 py-2.5 text-center">Selfie</th>
                                <th class="px-3 py-2.5">Jarak</th>
                                <th class="px-3 py-2.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                            <?php foreach ($ap['recentLogs'] as $lg): ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 truncate max-w-[130px]"><?= htmlspecialchars($lg->nama_siswa) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($lg->nama_kelas ?? '') ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-700 dark:text-slate-200 truncate max-w-[130px]"><?= htmlspecialchars($lg->nama_tempat ?? $lg->nama_kelompok ?? 'DU/DI') ?></div>
                                    <div class="text-[10px] text-slate-400">Pembimbing: <?= htmlspecialchars($lg->pembimbing_nama ?? '-') ?></div>
                                </td>
                                <td class="px-3 py-2 font-mono text-[11px]">
                                    <div><i class="fa-solid fa-arrow-right-to-bracket text-emerald-500 mr-1 text-[9px]"></i> <?= $lg->jam_datang ? date('H:i', strtotime($lg->jam_datang)) : '-' ?></div>
                                    <?php if ($lg->jam_pulang): ?>
                                    <div class="text-slate-400"><i class="fa-solid fa-arrow-right-from-bracket text-blue-500 mr-1 text-[9px]"></i> <?= date('H:i', strtotime($lg->jam_pulang)) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <?php if (!empty($lg->foto_masuk)): ?>
                                        <a href="<?= BASEURL; ?>/<?= htmlspecialchars($lg->foto_masuk) ?>" target="_blank" class="inline-block" title="Lihat Foto Selfie">
                                            <img src="<?= BASEURL; ?>/<?= htmlspecialchars($lg->foto_masuk) ?>" alt="Selfie" class="w-7 h-7 rounded-md object-cover border border-slate-200 dark:border-slate-700 shadow-2xs hover:scale-110 transition-transform">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2">
                                    <?php if ($lg->jarak_meter !== null): ?>
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold <?= $lg->jarak_meter <= ($lg->radius_meter ?? 50) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                                            <?= $lg->jarak_meter ?>m
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[10px]">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <?php if ($lg->jam_pulang): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            SELESAI
                                        </span>
                                    <?php elseif ($lg->jam_datang): ?>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            HADIR
                                        </span>
                                    <?php else: ?>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-medium bg-slate-100 text-slate-600">
                                            <?= strtoupper($lg->status ?? 'BELUM') ?>
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
</div>
