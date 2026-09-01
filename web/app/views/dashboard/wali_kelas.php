<?php
/**
 * views/dashboard/wali_kelas.php
 * Dashboard Wali Kelas — statistik kelasnya sendiri + siswa sering terlambat
 */
$k  = $data['kelas']          ?? null;
$s  = $data['statsSiswa']     ?? null;
$terlambat = $data['siswaTerlambat'] ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0"><i class="fa fa-person-chalkboard me-2 text-success"></i>Dashboard Wali Kelas</h1>
        <small class="text-muted">
            <?php if ($k): ?>
                Kelas: <strong><?= htmlspecialchars($k->nama_kelas) ?></strong> &bull;
            <?php endif; ?>
            <?= htmlspecialchars($data['tanggal']) ?>
        </small>
    </div>
    <span class="badge bg-success fs-6">
        <i class="fa fa-user me-1"></i><?= htmlspecialchars(SessionHelper::get('name')) ?>
    </span>
</div>

<?php if (!$k): ?>
<div class="alert alert-warning"><i class="fa fa-triangle-exclamation me-1"></i>Anda belum ditugaskan sebagai wali kelas manapun.</div>
<?php else: ?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label'=>'Total Siswa', 'val'=>$s->total_siswa ?? 0, 'icon'=>'fa-users',            'color'=>'primary'],
        ['label'=>'Hadir',       'val'=>$s->hadir       ?? 0, 'icon'=>'fa-circle-check',     'color'=>'success'],
        ['label'=>'Terlambat',   'val'=>$s->terlambat   ?? 0, 'icon'=>'fa-clock',            'color'=>'warning'],
        ['label'=>'Izin',        'val'=>$s->izin        ?? 0, 'icon'=>'fa-file-circle-check','color'=>'info'],
        ['label'=>'Sakit',       'val'=>$s->sakit       ?? 0, 'icon'=>'fa-kit-medical',      'color'=>'secondary'],
        ['label'=>'Alfa',        'val'=>$s->alfa        ?? 0, 'icon'=>'fa-circle-xmark',     'color'=>'danger'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 text-center stat-card stat-<?= $c['color'] ?>">
            <div class="card-body py-3">
                <i class="fa <?= $c['icon'] ?> fa-2x text-<?= $c['color'] ?> mb-2"></i>
                <h3 class="mb-0 fw-bold"><?= (int)$c['val'] ?></h3>
                <small class="text-muted"><?= $c['label'] ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Siswa sering terlambat -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning bg-opacity-10 border-0">
        <h6 class="mb-0"><i class="fa fa-clock text-warning me-2"></i>Siswa Sering Terlambat Bulan Ini</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($terlambat)): ?>
        <div class="p-3 text-muted">Tidak ada siswa yang terlambat bulan ini.</div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Nama Siswa</th><th class="text-center">Jml Terlambat</th></tr>
            </thead>
            <tbody>
            <?php foreach ($terlambat as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t->name) ?></td>
                <td class="text-center"><span class="badge bg-warning text-dark"><?= (int)$t->jumlah_terlambat ?>x</span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
