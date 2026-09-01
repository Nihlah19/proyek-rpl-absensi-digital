<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Buat Pengajuan Baru</h1>
    <a href="<?= BASEURL; ?>/izin" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<?php Flasher::flash(); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="<?= BASEURL; ?>/izin/ajukan" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate(); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jenis" class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select class="form-select" id="jenis" name="jenis" required>
                            <option value="">Pilih Jenis</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin">Izin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="alasan" class="form-label">Alasan Singkat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="alasan" name="alasan" placeholder="Misal: Sakit Demam, Acara Keluarga" required>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Penjelasan lebih detail..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="file_bukti" class="form-label">Upload Surat/Bukti (Opsional namun disarankan)</label>
                        <input type="file" class="form-control" id="file_bukti" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Format: JPG, PNG, PDF. Maksimal 2MB.</div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Kirim Pengajuan</button>
                </form>
            </div>
        </div>
    </div>
</div>
