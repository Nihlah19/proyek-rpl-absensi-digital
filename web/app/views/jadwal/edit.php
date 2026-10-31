<?php $this->view('templates/header', $data); ?>

<div id="pageContent" class="w-full flex flex-col items-center space-y-6">
    <div class="w-full max-w-3xl space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Edit Jadwal Presensi</h1>
                <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">
                    Atur jam operasional absensi untuk hari 
                    <span class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($data['jadwal']->hari ?? '') ?></span>
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="<?= BASEURL; ?>/jadwal"
                   class="vh-btn-secondary text-xs sm:text-sm flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80">
                <h2 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2 tracking-tight">
                    <i class="fa-solid fa-sliders text-emerald-600 dark:text-emerald-400"></i> Pengaturan Parameter Waktu
                </h2>
            </div>
            <div class="p-5 sm:p-6">
                <form action="<?= BASEURL; ?>/jadwal/update" method="POST" class="space-y-5 text-xs sm:text-sm">
                    <?= class_exists('CsrfHelper') ? CsrfHelper::input() : '' ?>
                    <input type="hidden" name="id" value="<?= $data['jadwal']->id ?? '' ?>">

                    <!-- Status Aktif -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Status Hari</label>
                        <select class="vh-input w-full px-3 py-2 text-xs sm:text-sm"
                                id="status_aktif" name="status_aktif"
                                onchange="this.form.submit()">
                            <option value="1" <?= (isset($data['jadwal']->status_aktif) && $data['jadwal']->status_aktif == 1) ? 'selected' : '' ?>>
                                Aktif — Hari Sekolah
                            </option>
                            <option value="0" <?= (isset($data['jadwal']->status_aktif) && $data['jadwal']->status_aktif == 0) ? 'selected' : '' ?>>
                                Libur
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Jam Mulai Absensi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5" for="jam_mulai_absensi">
                                <i class="fa-regular fa-clock me-1 text-slate-400"></i> Jam Mulai Buka Absen
                            </label>
                            <input type="time" class="vh-input w-full px-3 py-2 text-xs sm:text-sm font-mono"
                                   id="jam_mulai_absensi"
                                   name="jam_mulai_absensi"
                                   value="<?= !empty($data['jadwal']->jam_mulai_absensi) ? substr($data['jadwal']->jam_mulai_absensi, 0, 5) : '' ?>" required>
                            <p class="mt-1 text-[11px] text-slate-400">Mesin absensi mulai bisa digunakan</p>
                        </div>

                        <!-- Jam Datang -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5" for="jam_datang">
                                <i class="fa-regular fa-clock me-1 text-slate-400"></i> Jam Target Datang
                            </label>
                            <input type="time" class="vh-input w-full px-3 py-2 text-xs sm:text-sm font-mono"
                                   id="jam_datang"
                                   name="jam_datang"
                                   value="<?= !empty($data['jadwal']->jam_datang) ? substr($data['jadwal']->jam_datang, 0, 5) : '' ?>" required>
                            <p class="mt-1 text-[11px] text-slate-400">Batas waktu tepat waktu</p>
                        </div>

                        <!-- Batas Terlambat -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5" for="batas_terlambat">
                                <i class="fa-regular fa-clock me-1 text-slate-400"></i> Batas Terlambat
                            </label>
                            <input type="time" class="vh-input w-full px-3 py-2 text-xs sm:text-sm font-mono"
                                   id="batas_terlambat"
                                   name="batas_terlambat"
                                   value="<?= !empty($data['jadwal']->batas_terlambat) ? substr($data['jadwal']->batas_terlambat, 0, 5) : '' ?>" required>
                            <p class="mt-1 text-[11px] text-slate-400">Di atas waktu ini = terlambat</p>
                        </div>

                        <!-- Jam Pulang -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5" for="jam_pulang">
                                <i class="fa-regular fa-clock me-1 text-slate-400"></i> Jam Pulang
                            </label>
                            <input type="time" class="vh-input w-full px-3 py-2 text-xs sm:text-sm font-mono"
                                   id="jam_pulang"
                                   name="jam_pulang"
                                   value="<?= !empty($data['jadwal']->jam_pulang) ? substr($data['jadwal']->jam_pulang, 0, 5) : '' ?>" required>
                            <p class="mt-1 text-[11px] text-slate-400">Siswa otomatis absen pulang; guru mulai scan pulang</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <a href="<?= BASEURL; ?>/jadwal" class="vh-btn-secondary text-xs sm:text-sm">
                            Batal
                        </a>
                        <button type="submit" class="vh-btn-primary text-xs sm:text-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->view('templates/footer'); ?>