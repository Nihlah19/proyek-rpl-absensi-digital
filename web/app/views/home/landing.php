<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul'] ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= BASEURL; ?>"><i class="fa fa-school"></i> SMK Absensi</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link btn btn-primary text-white px-4" href="<?= BASEURL; ?>/auth/login">Login / Masuk</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Sistem Absensi Pintar SMK</h1>
        <p class="lead mb-4">Mengelola absensi siswa, guru, dan pegawai dengan teknologi RFID, Barcode/QR, dan AI terintegrasi Telegram.</p>
        <a href="<?= BASEURL; ?>/auth/login" class="btn btn-light btn-lg px-5 rounded-pill shadow-sm">Mulai Sekarang</a>
    </div>
</header>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-5">
                        <i class="fa fa-id-card feature-icon"></i>
                        <h4 class="card-title">Kartu RFID Siswa</h4>
                        <p class="card-text text-muted">Absensi masuk otomatis dengan sentuhan kartu. Cepat dan akurat setiap hari.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-5">
                        <i class="fa fa-qrcode feature-icon"></i>
                        <h4 class="card-title">QR Scanner Guru</h4>
                        <p class="card-text text-muted">Absensi guru berbasis web camera scanner langsung dari smartphone atau PC.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body py-5">
                        <i class="fa fa-paper-plane feature-icon"></i>
                        <h4 class="card-title">Telegram Notifikasi</h4>
                        <p class="card-text text-muted">Notifikasi langsung ke grup kelas dan wali kelas saat absensi atau izin.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-dark text-white text-center py-4">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y'); ?> SMK Absensi. All Rights Reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
