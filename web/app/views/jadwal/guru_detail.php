<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Jadwal Guru</h1>
    <a href="<?= BASEURL; ?>/jadwal/guru" class="btn btn-secondary">Kembali</a>
</div>

<?php Flasher::flash(); ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        Jadwal Mingguan
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Ada Jadwal</th>
                        <th>Jam Datang</th>
                        <th>Batas Terlambat</th>
                        <th>Jam Pulang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['jadwal'] as $j): ?>
                    <tr>
                        <td><strong><?= $j->hari; ?></strong></td>
                        <td>
                            <?php if ($j->is_active): ?>
                                <span class="badge bg-success">Ya</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Tidak</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $j->is_active ? $j->jam_datang : '-'; ?></td>
                        <td><?= $j->is_active ? $j->batas_terlambat : '-'; ?></td>
                        <td><?= $j->is_active ? $j->jam_pulang : '-'; ?></td>
                        <td>
                            <a href="<?= BASEURL; ?>/jadwal/edit_guru/<?= $j->id; ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
