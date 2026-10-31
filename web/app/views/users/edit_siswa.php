<!-- Halaman Edit Siswa -->
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
            <i class="fa-solid fa-graduation-cap text-primary mr-2"></i>Edit Data Siswa
        </h2>

        <?php if (empty($data['siswa'])): ?>
            <div class="text-center py-12 text-slate-400">
                <i class="fa-solid fa-triangle-exclamation text-3xl mb-3 block text-amber-400"></i>
                <p>Data siswa tidak ditemukan.</p>
                <a href="<?= BASEURL ?>/users" class="text-primary text-sm mt-2 inline-block hover:underline">Kembali ke daftar pengguna</a>
            </div>
        <?php else: ?>
        <form action="<?= BASEURL ?>/users/editSiswa/<?= $data['siswa']->siswa_id ?>" method="POST" class="space-y-4">
            <?= CsrfHelper::input() ?>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required
                           value="<?= htmlspecialchars($data['siswa']->name) ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                           placeholder="Nama siswa">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username *</label>
                    <input type="text" name="username" required
                           value="<?= htmlspecialchars($data['siswa']->username) ?>"
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
                    <label class="block text-sm font-medium text-slate-700 mb-1">NIS *</label>
                    <input type="text" name="nis" required
                           value="<?= htmlspecialchars($data['siswa']->nis) ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                           placeholder="Nomor Induk Siswa">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                    <select name="kelas_id"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($data['kelas'] as $k): ?>
                        <option value="<?= $k->id ?>" <?= (isset($data['siswa']->kelas_id) && $data['siswa']->kelas_id == $k->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k->nama_kelas) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">RFID UID <span class="text-slate-400 text-xs font-normal">(opsional)</span></label>
                        <button type="button" id="btnScanIotEdit" onclick="startIotScanForInput('inputRfidEdit', 'statusScanEdit', this)"
                                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded-md border border-emerald-200 transition-all flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-wifi"></i>
                            <span>Scan dari Alat IoT</span>
                        </button>
                    </div>
                    <input type="text" id="inputRfidEdit" name="rfid_uid"
                           value="<?= htmlspecialchars($data['siswa']->rfid_uid ?? '') ?>"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 font-mono uppercase"
                           placeholder="Misal: A1B2C3D4">
                    <div id="statusScanEdit" class="hidden text-xs mt-1.5 py-1 px-2 rounded-md font-medium"></div>
                    <p class="text-xs text-slate-400 mt-1">Klik tombol <strong>Scan dari Alat IoT</strong> untuk membaca kartu langsung dari sensor tanpa Serial Monitor.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    <i class="fa-brands fa-whatsapp text-emerald-600 mr-1"></i>No. WhatsApp Orang Tua / Wali <span class="text-slate-400 text-xs font-normal">(untuk notifikasi absensi & kepulangan)</span>
                </label>
                <input type="text" name="no_hp_ortu"
                       value="<?= htmlspecialchars($data['siswa']->no_hp_ortu ?? '') ?>"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 font-mono"
                       placeholder="Contoh: 08123456789 atau 628123456789">
                <p class="text-xs text-slate-400 mt-1">Nomor ini otomatis menerima WhatsApp saat siswa berhasil absen pulang.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit"
                        class="flex-1 bg-primary text-white py-2.5 rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
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
let iotInputScanInterval = null;

async function startIotScanForInput(targetInputId, statusBoxId, btnEl) {
    const input = document.getElementById(targetInputId);
    const statusBox = document.getElementById(statusBoxId);
    if (!input || !statusBox) return;

    statusBox.className = "text-xs py-1.5 px-2.5 rounded-lg font-medium bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-between";
    statusBox.innerHTML = `<span><i class="fa-solid fa-spinner fa-spin mr-1"></i> Mode Tambah Kartu Aktif! Tempelkan kartu pada scanner mikrokontroler...</span> <button type="button" onclick="cancelIotScan('${targetInputId}','${statusBoxId}')" class="text-slate-400 hover:text-slate-600 text-xs font-bold ml-2">Batal</button>`;
    statusBox.classList.remove('hidden');

    if (btnEl) btnEl.disabled = true;

    try {
        await fetch('<?= BASEURL ?>/rfid/set_device_mode', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mode: 'regis' })
        });
    } catch (e) {
        console.error(e);
    }

    if (iotInputScanInterval) clearInterval(iotInputScanInterval);

    let baselineUid = input.value.trim();

    iotInputScanInterval = setInterval(async () => {
        try {
            const res = await fetch('<?= BASEURL ?>/rfid/get_latest_scan');
            const json = await res.json();
            if (json.success && json.data && json.data.uid) {
                const uid = json.data.uid;
                if (uid !== baselineUid) {
                    input.value = uid;
                    input.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50');
                    setTimeout(() => input.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50'), 2500);

                    statusBox.className = "text-xs py-1.5 px-2.5 rounded-lg font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5";
                    statusBox.innerHTML = `<i class="fa-solid fa-check text-emerald-600"></i> Kartu terdeteksi: <strong class="font-mono text-emerald-700">${uid}</strong>`;

                    clearInterval(iotInputScanInterval);
                    iotInputScanInterval = null;

                    if (btnEl) btnEl.disabled = false;

                    // Kembalikan alat ke mode absensi
                    await fetch('<?= BASEURL ?>/rfid/set_device_mode', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mode: 'absen' })
                    });
                }
            }
        } catch (e) {}
    }, 1200);
}

async function cancelIotScan(targetInputId, statusBoxId) {
    if (iotInputScanInterval) {
        clearInterval(iotInputScanInterval);
        iotInputScanInterval = null;
    }
    const statusBox = document.getElementById(statusBoxId);
    if (statusBox) statusBox.classList.add('hidden');
    const btn = document.getElementById('btnScanIotEdit');
    if (btn) btn.disabled = false;
    
    // Kembalikan ke mode absen
    await fetch('<?= BASEURL ?>/rfid/set_device_mode', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: 'absen' })
    });
}
</script>
