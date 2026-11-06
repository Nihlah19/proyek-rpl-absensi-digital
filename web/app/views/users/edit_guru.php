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

            <!-- Tugas Tambahan: Pembimbing PKL -->
            <div class="pt-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Tugas Tambahan (Pembimbing PKL)</label>
                <select name="pembimbing_pkl_option" id="selectPembimbingPklOption"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        onchange="handlePembimbingPklOptionChange(this.value)">
                    <option value="">-- Bukan Pembimbing PKL --</option>
                    <option value="TKJ" <?= ($data['selectedJurusanPkl'] ?? '') === 'TKJ' ? 'selected' : '' ?>>Pembimbing PKL TKJ</option>
                    <option value="TKR" <?= ($data['selectedJurusanPkl'] ?? '') === 'TKR' ? 'selected' : '' ?>>Pembimbing PKL TKR</option>
                    <option value="TSM" <?= ($data['selectedJurusanPkl'] ?? '') === 'TSM' ? 'selected' : '' ?>>Pembimbing PKL TSM</option>
                    <option value="CUSTOM" <?= ($data['selectedJurusanPkl'] ?? '') === 'CUSTOM' ? 'selected' : '' ?>>Pilih Kelas Tertentu (Kustom)...</option>
                </select>
                <p class="text-xs text-slate-400 mt-1">Pilih penugasan guru sebagai pembimbing siswa PKL (Bukan, TKJ, TKR, TSM, atau kustom kelas).</p>
                <input type="hidden" name="is_pembimbing_pkl" id="inputIsPembimbingPkl" value="<?= !empty($data['guru']->is_pembimbing_pkl) ? '1' : '0' ?>">
            </div>

            <!-- ── DETAIL KELAS TANGGUNG JAWAB PEMBIMBING PKL ── -->
            <div id="containerPklKelas" class="<?= !empty($data['selectedJurusanPkl']) || !empty($data['guru']->is_pembimbing_pkl) ? '' : 'hidden' ?> p-4 bg-indigo-50/50 dark:bg-slate-800/60 rounded-xl border border-indigo-100 dark:border-slate-700 space-y-3 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Kelas PKL yang Dibimbing (Tanggung Jawab)
                        </label>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Centang kelas yang menjadi tanggung jawab guru ini untuk mengatur kelompok, lokasi Maps, dan memantau kehadiran siswa.
                        </p>
                    </div>
                    <span id="badgePklInfo" class="text-[11px] font-bold px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200">
                        <?= !empty($data['selectedJurusanPkl']) && $data['selectedJurusanPkl'] !== 'CUSTOM' ? 'Jurusan ' . htmlspecialchars($data['selectedJurusanPkl']) : 'Pilihan Kelas' ?>
                    </span>
                </div>

                <?php if (empty($data['allKelasPkl'])): ?>
                    <div class="text-xs text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-200">
                        Belum ada kelas yang terdaftar. Anda dapat menambahkan kelas pada menu Data Kelas.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-56 overflow-y-auto pt-1">
                        <?php foreach ($data['allKelasPkl'] as $kp):
                            $isAssigned = in_array((int)$kp->id, $data['assignedKelasIds'] ?? []);
                        ?>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-indigo-400 cursor-pointer transition-colors group">
                            <input type="checkbox" name="pembimbing_kelas_ids[]" value="<?= $kp->id ?>"
                                   data-jurusan="<?= htmlspecialchars($kp->jurusan ?? '') ?>"
                                   class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 pkl-kelas-checkbox"
                                   <?= $isAssigned ? 'checked' : '' ?>
                                   onchange="onCheckboxKelasChange()">
                            <div class="min-w-0">
                                <div class="font-bold text-xs text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 transition-colors">
                                    <?= htmlspecialchars($kp->nama_kelas) ?>
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    Jurusan: <span class="font-semibold text-slate-600 dark:text-slate-300"><?= htmlspecialchars($kp->jurusan ?? '-') ?></span>
                                    &middot; Status: <span class="uppercase font-semibold <?= ($kp->status ?? '') === 'pkl' ? 'text-indigo-600' : 'text-slate-500' ?>"><?= htmlspecialchars($kp->status ?? 'aktif') ?></span>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
function handlePembimbingPklOptionChange(val) {
    const container = document.getElementById('containerPklKelas');
    const hiddenIsPem = document.getElementById('inputIsPembimbingPkl');
    const badgeInfo = document.getElementById('badgePklInfo');
    const checkboxes = document.querySelectorAll('.pkl-kelas-checkbox');

    if (!val) {
        // Bukan Pembimbing PKL
        if (hiddenIsPem) hiddenIsPem.value = '0';
        if (container) container.classList.add('hidden');
        checkboxes.forEach(cb => cb.checked = false);
        return;
    }

    if (hiddenIsPem) hiddenIsPem.value = '1';
    if (container) container.classList.remove('hidden');

    if (val === 'CUSTOM') {
        if (badgeInfo) badgeInfo.textContent = 'Kustom Kelas';
        return;
    }

    if (badgeInfo) badgeInfo.textContent = 'Jurusan ' + val;

    // Otomatis centang kelas yang jurusannya cocok dengan pilihan (TKJ, TKR, TSM)
    checkboxes.forEach(cb => {
        const jur = (cb.getAttribute('data-jurusan') || '').toUpperCase().trim();
        if (jur === val.toUpperCase()) {
            cb.checked = true;
        } else {
            cb.checked = false;
        }
    });
}

function onCheckboxKelasChange() {
    const select = document.getElementById('selectPembimbingPklOption');
    const hiddenIsPem = document.getElementById('inputIsPembimbingPkl');
    const badgeInfo = document.getElementById('badgePklInfo');
    const checkboxes = Array.from(document.querySelectorAll('.pkl-kelas-checkbox'));
    const checkedBoxes = checkboxes.filter(cb => cb.checked);

    if (checkedBoxes.length === 0) {
        if (select && select.value !== '') select.value = 'CUSTOM';
        if (hiddenIsPem) hiddenIsPem.value = '0';
        if (badgeInfo) badgeInfo.textContent = 'Belum Ada Kelas Dipilih';
        return;
    }

    if (hiddenIsPem) hiddenIsPem.value = '1';

    // Cek apakah semua yang dicentang berasal dari satu jurusan yang sama
    const jurusans = Array.from(new Set(checkedBoxes.map(cb => (cb.getAttribute('data-jurusan') || '').toUpperCase().trim()))).filter(Boolean);
    if (jurusans.length === 1 && ['TKJ', 'TKR', 'TSM'].includes(jurusans[0])) {
        // Cek apakah seluruh kelas jurusan tersebut tercentang
        const totalJurusanBoxes = checkboxes.filter(cb => (cb.getAttribute('data-jurusan') || '').toUpperCase().trim() === jurusans[0]);
        if (totalJurusanBoxes.length === checkedBoxes.length && select) {
            select.value = jurusans[0];
            if (badgeInfo) badgeInfo.textContent = 'Jurusan ' + jurusans[0];
            return;
        }
    }

    if (select) select.value = 'CUSTOM';
    if (badgeInfo) badgeInfo.textContent = 'Kustom Kelas (' + checkedBoxes.length + ')';
}
</script>
