<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Kelola Jadwal Absensi</h1>
</div>

<?php Flasher::flash(); ?>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Hari</th>
                <th>Status</th>
                <th>Mulai Buka Absen</th>
                <th>Jam Datang</th>
                <th>Batas Terlambat</th>
                <th>Jam Pulang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['jadwal'] as $row): ?>
            <tr>
                <td><strong><?= $row->hari ?></strong></td>
                <td>
                    <?php if($row->status_aktif): ?>
                        <span class="badge bg-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Libur</span>
                    <?php endif; ?>
                </td>
                <td><?= $row->jam_mulai_absensi ?></td>
                <td><?= $row->jam_datang ?></td>
                <td><?= $row->batas_terlambat ?></td>
                <td><?= $row->jam_pulang ?></td>
                <td>
                    <a href="<?= BASEURL; ?>/jadwal/edit/<?= $row->id ?>" class="btn btn-sm btn-primary">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
