<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Jadwal Guru</h1>
    <a href="<?= BASEURL; ?>/jadwal/detail_guru/<?= $data['jadwal']->guru_id; ?>" class="btn btn-secondary">Batal</a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Hari: <?= $data['jadwal']->hari; ?>
            </div>
            <div class="card-body">
                <form action="<?= BASEURL; ?>/jadwal/update_guru" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate(); ?>">
                    <input type="hidden" name="id" value="<?= $data['jadwal']->id; ?>">
                    <input type="hidden" name="guru_id" value="<?= $data['jadwal']->guru_id; ?>">

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= $data['jadwal']->is_active ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="is_active">Guru ada jadwal pada hari ini</label>
                    </div>

                    <div class="mb-3">
                        <label for="jam_datang" class="form-label">Jam Datang</label>
                        <input type="time" class="form-control" id="jam_datang" name="jam_datang" value="<?= $data['jadwal']->jam_datang; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="batas_terlambat" class="form-label">Batas Terlambat</label>
                        <input type="time" class="form-control" id="batas_terlambat" name="batas_terlambat" value="<?= $data['jadwal']->batas_terlambat; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="jam_pulang" class="form-label">Jam Pulang</label>
                        <input type="time" class="form-control" id="jam_pulang" name="jam_pulang" value="<?= $data['jadwal']->jam_pulang; ?>" required>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isActiveCheckbox = document.getElementById('is_active');
        const timeInputs = document.querySelectorAll('input[type="time"]');

        function toggleTimeInputs() {
            const isChecked = isActiveCheckbox.checked;
            timeInputs.forEach(input => {
                input.disabled = !isChecked;
                if (!isChecked) {
                    input.removeAttribute('required');
                } else {
                    input.setAttribute('required', 'required');
                }
            });
        }

        isActiveCheckbox.addEventListener('change', toggleTimeInputs);
        toggleTimeInputs(); // initial check
    });
</script>
