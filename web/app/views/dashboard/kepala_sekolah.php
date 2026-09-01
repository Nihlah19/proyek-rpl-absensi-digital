<?php
/**
 * views/dashboard/kepala_sekolah.php
 * Dashboard Kepala Sekolah — statistik guru hari ini + guru sering terlambat
 */
$g = $data['statsGuru']     ?? null;
$terlambat = $data['guruTerlambat'] ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0"><i class="fa fa-building-columns me-2 text-secondary"></i>Dashboard Kepala Sekolah</h1>
        <small class="text-muted">Rekap Guru — <?= htmlspecialchars($data['tanggal']) ?></small>
    </div>
    <span class="badge bg-secondary fs-6">
        <i class="fa fa-user-tie me-1"></i><?= htmlspecialchars(SessionHelper::get('name')) ?>
    </span>
</div>

<!-- Stat cards guru -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label'=>'Total Guru',  'val'=>$g->total_guru ?? 0, 'icon'=>'fa-users-line',       'color'=>'primary'],
        ['label'=>'Hadir',       'val'=>$g->hadir      ?? 0, 'icon'=>'fa-circle-check',     'color'=>'success'],
        ['label'=>'Terlambat',   'val'=>$g->terlambat  ?? 0, 'icon'=>'fa-clock',            'color'=>'warning'],
        ['label'=>'Izin',        'val'=>$g->izin       ?? 0, 'icon'=>'fa-file-circle-check','color'=>'info'],
        ['label'=>'Sakit',       'val'=>$g->sakit      ?? 0, 'icon'=>'fa-kit-medical',      'color'=>'secondary'],
        ['label'=>'Alfa',        'val'=>$g->alfa       ?? 0, 'icon'=>'fa-circle-xmark',     'color'=>'danger'],
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

<!-- Guru sering terlambat -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning bg-opacity-10 border-0">
        <h6 class="mb-0"><i class="fa fa-clock text-warning me-2"></i>Guru Sering Terlambat Bulan Ini</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($terlambat)): ?>
        <div class="p-3 text-muted">Tidak ada guru yang terlambat bulan ini.</div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Nama Guru</th><th class="text-center">Jml Terlambat</th></tr>
            </thead>
            <tbody>
            <?php foreach ($terlambat as $i => $t): ?>
            <tr>
                <td class="text-muted"><?= $i + 1 ?></td>
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
