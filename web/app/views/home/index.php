<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<?php Flasher::flash(); ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        Selamat Datang di Sistem Absensi SMK
    </div>
    <div class="card-body">
        <h5 class="card-title">Status Sistem</h5>
        <p class="card-text">
            Sistem fondasi web telah berhasil diinisialisasi.
        </p>
        <div class="alert <?= strpos($data['db_status'], 'Connected') !== false ? 'alert-success' : 'alert-danger' ?>" role="alert">
            <i class="fa fa-database"></i> Database Status: <strong><?= $data['db_status'] ?></strong>
        </div>
    </div>
</div>
