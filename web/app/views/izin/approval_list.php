<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Daftar Persetujuan Izin/Sakit</h1>
</div>

<?php Flasher::flash(); ?>

<div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
        <thead>
            <tr>
                <th>No</th>
                <th>Pemohon</th>
                <th>Peran</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Jenis</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['izin'])): ?>
            <tr>
                <td colspan="9" class="text-center">Belum ada pengajuan.</td>
            </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($data['izin'] as $izin): ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars(isset($izin->nama_siswa) ? $izin->nama_siswa : $izin->nama_guru); ?></td>
                    <td><?= isset($izin->nama_siswa) ? 'Siswa (' . $izin->nama_kelas . ')' : 'Guru' ?></td>
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
