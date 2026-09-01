<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pengajuan Izin/Sakit Saya</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASEURL; ?>/izin/ajukan" class="btn btn-sm btn-primary">
            <i class="fa fa-plus"></i> Buat Pengajuan Baru
        </a>
    </div>
</div>

<?php Flasher::flash(); ?>

<div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Jenis</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['izin'])): ?>
            <tr>
                <td colspan="7" class="text-center">Belum ada riwayat pengajuan.</td>
            </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($data['izin'] as $izin): ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= date('d/m/Y', strtotime($izin->tanggal_mulai)); ?></td>
                    <td><?= date('d/m/Y', strtotime($izin->tanggal_selesai)); ?></td>
                    <td>
                        <span class="badge bg-<?= $izin->jenis == 'sakit' ? 'warning text-dark' : 'info text-dark' ?>">
                            <?= strtoupper($izin->jenis); ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($izin->alasan); ?></td>
                    <td>
                        <?php if($izin->status == 'pending'): ?>
                            <span class="badge bg-secondary">Menunggu</span>
                        <?php elseif($izin->status == 'approved'): ?>
                            <span class="badge bg-success">Disetujui</span>
                        <?php elseif($izin->status == 'rejected'): ?>
                            <span class="badge bg-danger">Ditolak</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASEURL; ?>/izin/detail/<?= $izin->id; ?>" class="btn btn-sm btn-info text-white">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
