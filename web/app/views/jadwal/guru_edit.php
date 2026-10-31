<?php $this->view('templates/header', $data); ?>
<div id="pageContent" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Jadwal Guru</h1>
            <p class="text-slate-500 text-sm mt-1">
                Guru: <span class="font-bold text-slate-700"><?= htmlspecialchars($data['guru']->nama_guru ?? ('ID: ' . $data['jadwal']->guru_id)) ?></span>
                <?php if (!empty($data['guru']->nip)): ?>
                    <span class="text-slate-400 font-mono text-xs">(NIP: <?= htmlspecialchars($data['guru']->nip) ?>)</span>
                <?php endif; ?>
                &middot; <span class="font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Hari: <?= $data['jadwal']->hari; ?></span>
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/jadwal/detail_guru/<?= $data['jadwal']->guru_id; ?>"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm text-slate-600 rounded-lg hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Form -->
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-warning"></i>Pengaturan Jadwal Mengajar
            </h2>
        </div>
        <div class="p-6">
            <form action="<?= BASEURL; ?>/jadwal/update_guru" method="POST" class="space-y-5 max-w-xl">
                <?= CsrfHelper::input() ?>
                <input type="hidden" name="id" value="<?= $data['jadwal']->id; ?>">
                <input type="hidden" name="guru_id" value="<?= $data['jadwal']->guru_id; ?>">

                <!-- Ada Jadwal -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30"
                               id="is_active"
                               name="is_active"
                               value="1"
                               <?= $data['jadwal']->is_active ? 'checked' : ''; ?>
                               onchange="this.form.submit()">
                        <span class="text-sm font-medium text-slate-700">
                            Guru ada jadwal mengajar pada hari ini
                        </span>
                    </label>
                    <p class="mt-1 text-xs text-slate-400">Jika tidak aktif, jam-jam berikut akan dikunci</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5" id="time-fields">
                    <!-- Jam Datang -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5" for="jam_datang">
                            <i class="fa-regular fa-clock me-1 text-slate-400"></i>Jam Datang
                        </label>
                        <input type="time" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                               id="jam_datang"
                               name="jam_datang"
                               value="<?= $data['jadwal']->jam_datang; ?>" required>
                    </div>

                    <!-- Batas Terlambat -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5" for="batas_terlambat">
                            <i class="fa-regular fa-clock me-1 text-slate-400"></i>Batas Terlambat
                        </label>
                        <input type="time" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                               id="batas_terlambat"
                               name="batas_terlambat"
                               value="<?= $data['jadwal']->batas_terlambat; ?>" required>
                    </div>

                    <!-- Jam Pulang -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5" for="jam_pulang">
                            <i class="fa-regular fa-clock me-1 text-slate-400"></i>Jam Pulang
                        </label>
                        <input type="time" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                               id="jam_pulang"
                               name="jam_pulang"
                               value="<?= $data['jadwal']->jam_pulang; ?>" required>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-success text-white text-sm font-medium rounded-lg hover:bg-success/90 transition-colors shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var isActiveCheckbox = document.getElementById('is_active');
    var timeFields = document.getElementById('time-fields');

    function toggleTimeFields() {
        var isChecked = isActiveCheckbox.checked;
        var inputs = timeFields.querySelectorAll('input[type="time"]');
        inputs.forEach(function (input) {
            if (isChecked) {
                input.removeAttribute('disabled');
                input.removeAttribute('required');
                input.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-slate-50');
                input.classList.add('opacity-100', 'cursor-pointer');
            } else {
                input.setAttribute('disabled', 'true');
                input.classList.add('opacity-50', 'cursor-not-allowed', 'bg-slate-50');
                input.classList.remove('opacity-100', 'cursor-pointer');
            }
        });
    }

    isActiveCheckbox.addEventListener('change', toggleTimeFields);
    toggleTimeFields();
});
</script>
<?php $this->view('templates/footer'); ?>