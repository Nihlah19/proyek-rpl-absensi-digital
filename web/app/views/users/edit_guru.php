<!-- Halaman Edit Guru -->
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASEURL ?>/users"
           class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-xl font-bold text-slate-800 mb-6">
            <i class="fa-solid fa-chalkboard-user text-primary mr-2"></i>Edit Data Guru
        </h2>

        <?php if (empty($data['guru'])): ?>
            <div class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-triangle-exclamation text-3xl mb-3 block text-amber-400"></i>
                <p>Data guru tidak ditemukan.</p>
                <a href="<?= BASEURL ?>/users" class="text-primary text-sm mt-2 inline-block hover:underline">Kembali ke daftar pengguna</a>
            </div>
        <?php else: ?>
        <form action="<?= BASEURL ?>/users/editGuru/<?= $data['guru']->guru_id ?>" method="POST" class="space-y-5">
            <?= CsrfHelper::input() ?>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required
                           value="<?= htmlspecialchars($data['guru']->name) ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                           placeholder="Nama guru">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username *</label>
                    <input type="text" name="username" required
                           value="<?= htmlspecialchars($data['guru']->username) ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                           placeholder="Untuk login">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-slate-400 text-xs font-normal">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                           placeholder="Password baru">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIP *</label>
                    <input type="text" name="nip" required
                           value="<?= htmlspecialchars($data['guru']->nip) ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                           placeholder="Nomor Induk Pegawai">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Barcode ID</label>
                    <input type="text" name="barcode_id"
                           value="<?= htmlspecialchars($data['guru']->barcode_id ?? '') ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 font-mono"
                           placeholder="Misal: QR-GURU-001">
                    <p class="text-xs text-slate-400 mt-1">ID barcode untuk absensi guru.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mata Pelajaran</label>
                    <input type="text" name="subject"
                           value="<?= htmlspecialchars($data['guru']->subject ?? '') ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                           placeholder="Misal: Matematika">
                </div>
            </div>

            <!-- Tugas Tambahan Wali Kelas -->
            <div class="pt-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tugas Tambahan: Wali Kelas</label>
                <select name="wali_kelas_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <option value="">-- Bukan Wali Kelas --</option>
                    <?php foreach ($data['kelas'] as $k): ?>
                        <option value="<?= $k->id ?>" <?= ($data['guru']->wali_kelas_id == $k->id) ? 'selected' : '' ?>>
                            Wali Kelas <?= htmlspecialchars($k->nama_kelas) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-slate-400 mt-1">Pilih kelas jika guru ini ditugaskan sebagai wali kelas.</p>
            </div>

            <!-- Tugas Tambahan (Pembimbing PKL) -->
            <div class="pt-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tugas Tambahan (Pembimbing PKL)</label>
                <select name="pembimbing_pkl_option" id="selectPembimbingPklOption"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        onchange="document.getElementById('inputIsPembimbingPkl').value = this.value ? '1' : '0'">
                    <option value="">-- Bukan Pembimbing PKL --</option>
                    <option value="TKJ" <?= ($data['selectedJurusanPkl'] ?? '') === 'TKJ' ? 'selected' : '' ?>>
                        Pembimbing PKL TKJ (Kelas 12 TKJ)
                    </option>
                    <option value="TKR" <?= ($data['selectedJurusanPkl'] ?? '') === 'TKR' ? 'selected' : '' ?>>
                        Pembimbing PKL TKR (Kelas 12 TKR)
                    </option>
                    <option value="TSM" <?= ($data['selectedJurusanPkl'] ?? '') === 'TSM' ? 'selected' : '' ?>>
                        Pembimbing PKL TSM (Kelas 12 TSM)
                    </option>
                </select>
                <p class="text-xs text-slate-400 mt-1">
                    Siswa PKL berasal dari kelas 12. Pilih penugasan jurusan yang dibimbing oleh guru ini.
                </p>
                <input type="hidden" name="is_pembimbing_pkl" id="inputIsPembimbingPkl" value="<?= !empty($data['guru']->is_pembimbing_pkl) ? '1' : '0' ?>">
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <button type="submit"
                        class="flex-1 bg-primary text-white py-2.5 rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors shadow-sm">
                    <i class="fa-solid fa-save mr-2"></i>Simpan Perubahan
                </button>
                <a href="<?= BASEURL ?>/users"
                   class="px-6 border border-slate-200 text-slate-600 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors text-center">
                    Batal
                </a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('selectPembimbingPklOption');
    const inp = document.getElementById('inputIsPembimbingPkl');
    if (sel && inp) {
        sel.addEventListener('change', function() {
            inp.value = this.value ? '1' : '0';
        });
    }
});
</script>
