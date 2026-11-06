<?php
// Entrypoint Serverless Function Vercel untuk Absensi Siswa & PKL
// SMK NU Wargabinangun

// Tangani direct call ke endpoint API jika dipanggil
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

if (strpos($parsedPath, '/web/public/api/') === 0 || (strpos($parsedPath, '/api/') === 0 && $parsedPath !== '/api/index.php')) {
    $script = preg_replace('#^/(web/public/api|api)/#', '', $parsedPath);
    $apiFile = __DIR__ . '/../web/public/api/' . $script;
    if (file_exists($apiFile) && is_file($apiFile)) {
        require $apiFile;
        exit;
    }
}

// Set working directory ke web/public
chdir(__DIR__ . '/../web/public');

// Muat inisialisasi aplikasi MVC
require_once __DIR__ . '/../web/app/init.php';

// Jalankan controller & method MVC
$app = new App();
