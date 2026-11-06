<?php
// Entrypoint utama aplikasi Absensi Siswa & PKL SMK NU Wargabinangun
// Berfungsi untuk eksekusi langsung via PHP CLI Server, Docker, maupun hosting root

chdir(__DIR__ . '/web/public');
require_once __DIR__ . '/web/app/init.php';
$app = new App();
