<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Pilih Guru</h1>
</div>

<?php Flasher::flash(); ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        Daftar Guru
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data['guru'] as $g): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($g->nip); ?></td>
                        <td><?= htmlspecialchars($g->nama_guru); ?></td>
                        <td>
                            <a href="<?= BASEURL; ?>/jadwal/detail_guru/<?= $g->id; ?>" class="btn btn-sm btn-info text-white">
                                <i class="fa fa-calendar"></i> Kelola Jadwal
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
