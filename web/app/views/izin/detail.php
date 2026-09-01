<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Pengajuan</h1>
    <a href="<?= BASEURL; ?>/izin" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<?php Flasher::flash(); ?>
<?php $izin = $data['izin']; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Informasi Pengajuan</div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="30%">Pemohon</th>
                        <td>: <?= htmlspecialchars($izin->name); ?> (<?= htmlspecialchars($izin->role); ?>)</td>
                    </tr>
                    <?php if($izin->role == 'siswa'): ?>
                    <tr>
                        <th>Kelas</th>
                        <td>: <?= htmlspecialchars($izin->nama_kelas); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Jenis</th>
                        <td>: <span class="badge bg-<?= $izin->jenis == 'sakit' ? 'warning text-dark' : 'info text-dark' ?>"><?= strtoupper($izin->jenis); ?></span></td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>: <?= date('d/m/Y', strtotime($izin->tanggal_mulai)); ?> s.d <?= date('d/m/Y', strtotime($izin->tanggal_selesai)); ?></td>
                    </tr>
                    <tr>
                        <th>Alasan</th>
                        <td>: <?= htmlspecialchars($izin->alasan); ?></td>
                    </tr>
                    <tr>
                        <th>Keterangan Tambahan</th>
                        <td>: <?= nl2br(htmlspecialchars($izin->keterangan)); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>: 
                            <?php if($izin->status == 'pending'): ?>
                                <span class="badge bg-secondary">Menunggu</span>
                            <?php elseif($izin->status == 'approved'): ?>
                                <span class="badge bg-success">Disetujui</span>
                            <?php elseif($izin->status == 'rejected'): ?>
                                <span class="badge bg-danger">Ditolak</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if(!empty($izin->file_bukti)): ?>
                    <tr>
                        <th>File Bukti</th>
                        <td>: <a href="<?= BASEURL; ?>/izin/file/<?= $izin->file_bukti; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i> Lihat Lampiran</a></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(!empty($izin->catatan_approval)): ?>
                    <tr>
                        <th>Catatan Penilai</th>
                        <td>: <div class="alert alert-info p-2 mt-1 mb-0"><?= nl2br(htmlspecialchars($izin->catatan_approval)); ?></div></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <?php 
    $userRole = SessionHelper::get('role');
    // Jika role walikelas atau kepsek, dan status masih pending, tampilkan form aksi
    if (in_array($userRole, ['walikelas', 'kepsek']) && $izin->status == 'pending'): 
    ?>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">Tindak Lanjut Pengajuan</div>
            <div class="card-body">
                <form action="<?= BASEURL; ?>/izin/action" method="post">
                    <input type="hidden" name="id" value="<?= $izin->id; ?>">
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Keputusan</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Pilih Keputusan</option>
                            <option value="approved">Setujui</option>
                            <option value="rejected">Tolak</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan pesan untuk pemohon..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Simpan Keputusan</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
