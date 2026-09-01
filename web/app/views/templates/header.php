<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <title><?= $data['judul'] ?? 'Absensi RFID' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome (opsional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/chatbot.css">
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= BASEURL; ?>">SMK Absensi</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if(SessionHelper::get('user_id')): ?>
            <li class="nav-item">
            <a class="nav-link" href="<?= BASEURL; ?>/auth/logout">Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item">
            <a class="nav-link" href="<?= BASEURL; ?>/auth/login">Login</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" style="min-height: calc(100vh - 56px);">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false || $_SERVER['REQUEST_URI'] == '/AbsensiRFID/web/public/' ? 'active' : '' ?>" aria-current="page" href="<?= BASEURL; ?>">
                            <i class="fa fa-home"></i> Dashboard
                        </a>
                    </li>
                    <?php if(SessionHelper::get('role') === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/jadwal') !== false && strpos($_SERVER['REQUEST_URI'], '/jadwal/guru') === false ? 'active' : '' ?>" href="<?= BASEURL; ?>/jadwal">
                            <i class="fa fa-calendar"></i> Jadwal Siswa (Global)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/jadwal/guru') !== false ? 'active' : '' ?>" href="<?= BASEURL; ?>/jadwal/guru">
                            <i class="fa fa-calendar-alt"></i> Jadwal Guru
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(in_array(SessionHelper::get('role'), ['siswa', 'guru'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'izin') !== false ? 'active' : '' ?>" href="<?= BASEURL; ?>/izin">
                            <i class="fa fa-envelope"></i> Pengajuan Izin/Sakit
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(in_array(SessionHelper::get('role'), ['walikelas', 'kepsek'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'izin') !== false ? 'active' : '' ?>" href="<?= BASEURL; ?>/izin">
                            <i class="fa fa-check-square"></i> Persetujuan Izin
                        </a>
                    </li>
                    <?php endif; ?>
<?php if(in_array(SessionHelper::get('role'), ['siswa','guru','walikelas','kepsek'])): ?>
    <li class="nav-item">
        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'catatan') !== false ? 'active' : '' ?>" href="<?= BASEURL; ?>/catatan">
            <i class="fa fa-sticky-note"></i> Catatan
        </a>
    </li>
<?php endif; ?>
                    
                    <!-- Menu dinamis bisa ditambahkan di sini berdasarkan role -->
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
