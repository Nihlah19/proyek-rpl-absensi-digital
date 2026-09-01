<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Jadwal: <?= $data['jadwal']->hari ?></h1>
    <a href="<?= BASEURL; ?>/jadwal" class="btn btn-secondary">Kembali</a>
</div>

<div class="card col-md-6">
    <div class="card-body">
        <form action="<?= BASEURL; ?>/jadwal/update" method="POST">
            <?= CsrfHelper::input() ?>
            <input type="hidden" name="id" value="<?= $data['jadwal']->id ?>">
            
            <div class="mb-3">
                <label for="status_aktif" class="form-label">Status Hari</label>
                <select class="form-select" id="status_aktif" name="status_aktif">
                    <option value="1" <?= $data['jadwal']->status_aktif == 1 ? 'selected' : '' ?>>Aktif (Hari Sekolah)</option>
                    <option value="0" <?= $data['jadwal']->status_aktif == 0 ? 'selected' : '' ?>>Libur</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="jam_mulai_absensi" class="form-label">Jam Mulai Buka Mesin Absen (Pagi)</label>
                <input type="time" class="form-control" id="jam_mulai_absensi" name="jam_mulai_absensi" value="<?= $data['jadwal']->jam_mulai_absensi ?>" required>
            </div>

            <div class="mb-3">
                <label for="jam_datang" class="form-label">Jam Target Datang (Tepat Waktu)</label>
                <input type="time" class="form-control" id="jam_datang" name="jam_datang" value="<?= $data['jadwal']->jam_datang ?>" required>
            </div>

            <div class="mb-3">
                <label for="batas_terlambat" class="form-label">Batas Keterlambatan (Setelah ini dianggap Terlambat)</label>
                <input type="time" class="form-control" id="batas_terlambat" name="batas_terlambat" value="<?= $data['jadwal']->batas_terlambat ?>" required>
            </div>

            <div class="mb-3">
                <label for="jam_pulang" class="form-label">Jam Pulang (Otomatis bagi Siswa, Mulai Scan bagi Guru)</label>
                <input type="time" class="form-control" id="jam_pulang" name="jam_pulang" value="<?= $data['jadwal']->jam_pulang ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>
