<?php
// Entrypoint Serverless Function Vercel untuk Absensi Siswa & PKL
// SMK NU Wargabinangun

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

// 1. Tangani direct call ke endpoint API jika dipanggil
if (strpos($parsedPath, '/web/public/api/') === 0 || (strpos($parsedPath, '/api/') === 0 && $parsedPath !== '/api/index.php')) {
    $script = preg_replace('#^/(web/public/api|api)/#', '', $parsedPath);
    $apiFile = __DIR__ . '/../web/public/api/' . $script;
    if (file_exists($apiFile) && is_file($apiFile)) {
        require $apiFile;
        exit;
    }
}

// 2. Tangani file statis (assets, uploads, favicon, manifest, sw.js) jika diteruskan Vercel ke runtime
$urlParam = $_GET['url'] ?? '';
$candidate = $urlParam ?: ltrim($parsedPath, '/');
$candidate = preg_replace('#^web/public/#', '', $candidate);

if (preg_match('#^(assets/|uploads/|favicon\.ico|manifest\.json|sw\.js|offline\.html)#', $candidate)) {
    $filePath = realpath(__DIR__ . '/../web/public/' . $candidate);
    $pubDir   = realpath(__DIR__ . '/../web/public');
    if ($filePath && $pubDir && strpos($filePath, $pubDir) === 0 && is_file($filePath)) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css'   => 'text/css; charset=UTF-8',
            'js'    => 'application/javascript; charset=UTF-8',
            'json'  => 'application/json; charset=UTF-8',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'ico'   => 'image/x-icon',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'eot'   => 'application/vnd.ms-fontobject',
            'webp'  => 'image/webp'
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header("Content-Type: {$contentType}");
        header("Cache-Control: public, max-age=31536000, immutable");
        header("Content-Length: " . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

// 3. Set working directory ke web/public
chdir(__DIR__ . '/../web/public');

// 4. Muat inisialisasi aplikasi MVC
require_once __DIR__ . '/../web/app/init.php';

// 5. Jalankan controller & method MVC
$app = new App();
