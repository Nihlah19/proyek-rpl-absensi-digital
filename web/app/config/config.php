<?php
// Lakukan parse .env secara sederhana
$envPath = __DIR__ . '/../../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim(trim($value), '"\'');
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Konfigurasi Konstanta
// BASEURL diturunkan secara dinamis dari HTTP host yang aktif (localhost, ngrok, dsb).
// Ini memastikan semua redirect dan form action selalu menggunakan host yang benar,
// tanpa perlu mengubah .env setiap kali URL ngrok berubah.
// APP_URL di .env digunakan sebagai fallback jika tidak berjalan via web server.
if (isset($_SERVER['HTTP_HOST'])) {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    // Deteksi ngrok atau reverse-proxy: X-Forwarded-Proto menentukan scheme asli
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    }
    $host = $_SERVER['HTTP_HOST'];
    // Ambil subfolder path dari APP_URL (misalnya: /AbsensiRFID/web/public)
    $appUrl     = $_ENV['APP_URL'] ?? 'http://localhost/AbsensiRFID/web/public';
    $parsedPath = parse_url($appUrl, PHP_URL_PATH) ?? '/AbsensiRFID/web/public';
    define('BASEURL', rtrim($scheme . '://' . $host . $parsedPath, '/'));
} else {
    // Fallback untuk CLI atau lingkungan non-web
    define('BASEURL', rtrim($_ENV['APP_URL'] ?? 'http://localhost/AbsensiRFID/web/public', '/'));
}

// Set Timezone (Default: Asia/Jakarta / WIB, atau sesuai APP_TIMEZONE di .env)
$appTimezone = $_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta';
date_default_timezone_set($appTimezone);
define('APP_TIMEZONE', $appTimezone);

// Path Direktori Aplikasi
define('APPROOT', dirname(__DIR__));
define('WEBROOT', dirname(dirname(__DIR__)));
define('PUBROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'absensi_rfid');
define('GEMINI_API_KEY', $_ENV['GEMINI_API_KEY'] ?? '');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');

// Konfigurasi Geolokasi Sekolah (SMK NU Wargabinangun, Radius Maksimal 100 Meter)
define('SCHOOL_LAT',          (float)($_ENV['SCHOOL_LAT']          ?? $_SERVER['SCHOOL_LAT'] ?? getenv('SCHOOL_LAT') ?: -6.5724553));
define('SCHOOL_LNG',          (float)($_ENV['SCHOOL_LNG']          ?? $_SERVER['SCHOOL_LNG'] ?? getenv('SCHOOL_LNG') ?: 108.3977467));
define('SCHOOL_RADIUS_METER', (int)  ($_ENV['SCHOOL_RADIUS_METER']   ?? $_SERVER['SCHOOL_RADIUS_METER'] ?? getenv('SCHOOL_RADIUS_METER') ?: 100));

