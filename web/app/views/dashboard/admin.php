<?php
/**
 * views/dashboard/admin.php
 * Dashboard Admin — statistik global siswa + guru + status IoT hari ini
 */
$s  = $data['statsSiswa']  ?? null;
$g  = $data['statsGuru']   ?? null;
$iot = $data['iotDevices'] ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0"><i class="fa fa-gauge-high me-2 text-primary"></i>Dashboard Admin</h1>
        <small class="text-muted">Rekap hari ini — <?= htmlspecialchars($data['tanggal']) ?></small>
    </div>
    <span class="badge bg-primary fs-6">
        <i class="fa fa-user-shield me-1"></i><?= htmlspecialchars(SessionHelper::get('name')) ?>
    </span>
</div>

<!-- ===== SISWA ===== -->
<h5 class="text-muted mb-3"><i class="fa fa-user-graduate me-1"></i>Kehadiran Siswa</h5>
<div class="row g-3 mb-4">
    <?php
    $cardsSiswa = [
        ['label'=>'Total Siswa',  'val'=>$s->total_siswa ?? 0, 'icon'=>'fa-users',            'color'=>'primary'],
        ['label'=>'Hadir',        'val'=>$s->hadir       ?? 0, 'icon'=>'fa-circle-check',     'color'=>'success'],
        ['label'=>'Terlambat',    'val'=>$s->terlambat   ?? 0, 'icon'=>'fa-clock',            'color'=>'warning'],
        ['label'=>'Izin',         'val'=>$s->izin        ?? 0, 'icon'=>'fa-file-circle-check','color'=>'info'],
        ['label'=>'Sakit',        'val'=>$s->sakit       ?? 0, 'icon'=>'fa-kit-medical',      'color'=>'secondary'],
        ['label'=>'Alfa',         'val'=>$s->alfa        ?? 0, 'icon'=>'fa-circle-xmark',     'color'=>'danger'],
    ];
    foreach ($cardsSiswa as $c): ?>
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

<!-- ===== GURU ===== -->
<h5 class="text-muted mb-3"><i class="fa fa-chalkboard-teacher me-1"></i>Kehadiran Guru</h5>
<div class="row g-3 mb-4">
    <?php
    $cardsGuru = [
        ['label'=>'Total Guru',  'val'=>$g->total_guru ?? 0, 'icon'=>'fa-users-line',       'color'=>'primary'],
        ['label'=>'Hadir',       'val'=>$g->hadir      ?? 0, 'icon'=>'fa-circle-check',     'color'=>'success'],
        ['label'=>'Terlambat',   'val'=>$g->terlambat  ?? 0, 'icon'=>'fa-clock',            'color'=>'warning'],
        ['label'=>'Izin',        'val'=>$g->izin       ?? 0, 'icon'=>'fa-file-circle-check','color'=>'info'],
        ['label'=>'Sakit',       'val'=>$g->sakit      ?? 0, 'icon'=>'fa-kit-medical',      'color'=>'secondary'],
        ['label'=>'Alfa',        'val'=>$g->alfa       ?? 0, 'icon'=>'fa-circle-xmark',     'color'=>'danger'],
    ];
    foreach ($cardsGuru as $c): ?>
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

<!-- ===== STATUS IoT ===== -->
<h5 class="text-muted mb-3"><i class="fa fa-microchip me-1"></i>Status Perangkat IoT</h5>
<div class="row g-3 mb-5">
    <?php if (empty($iot)): ?>
        <div class="col-12"><div class="alert alert-warning mb-0">Tidak ada perangkat IoT terdaftar.</div></div>
    <?php else: foreach ($iot as $dev):
        $isOnline = ($dev->iot_status === 'online');
        $badgeClass = $isOnline ? 'success' : 'danger';
        $icon       = $isOnline ? 'fa-wifi' : 'fa-wifi-slash'; ?>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="fs-3 text-<?= $badgeClass ?>"><i class="fa <?= $icon ?>"></i></div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($dev->device_name) ?></div>
                    <small class="text-muted d-block"><?= htmlspecialchars($dev->device_code) ?> &bull; <?= htmlspecialchars($dev->device_type) ?></small>
                    <small class="text-muted"><?= $dev->last_seen ? 'Last seen: ' . date('d/m H:i', strtotime($dev->last_seen)) : 'Belum pernah online' ?></small>
                </div>
                <span class="badge bg-<?= $badgeClass ?>"><?= strtoupper($dev->iot_status) ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
