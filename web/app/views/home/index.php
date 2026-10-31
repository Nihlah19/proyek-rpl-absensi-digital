<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Dashboard Utama</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Sistem Absensi Terintegrasi SMK NU Wargabinangun</p>
    </div>
</div>

<?php Flasher::flash(); ?>

<div class="vh-card overflow-hidden">
    <div class="px-6 py-4 bg-emerald-600 text-white font-semibold text-sm flex items-center gap-2">
        <i class="fa-solid fa-school"></i>
        <span>Selamat Datang di Sistem Absensi SMK</span>
    </div>
    <div class="p-6 space-y-4">
        <div>
            <h5 class="text-sm font-bold text-slate-900 dark:text-white mb-1">Status Sistem</h5>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                Sistem fondasi web presensi digital telah berhasil diinisialisasi dan siap digunakan.
            </p>
        </div>
        
        <div class="<?= strpos($data['db_status'], 'Connected') !== false ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20' ?> rounded-xl px-4 py-3 text-xs sm:text-sm flex items-center gap-3">
            <i class="fa-solid fa-database text-base"></i>
            <span>Status Database: <strong class="font-mono"><?= htmlspecialchars($data['db_status']) ?></strong></span>
        </div>
    </div>
</div>