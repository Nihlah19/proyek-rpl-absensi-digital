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
// BASEURL diturunkan secara dinamis dari HTTP host yang aktif (localhost, ngrok, Vercel, dsb).
// Ini memastikan semua redirect dan form action selalu menggunakan host yang benar.
if (isset($_SERVER['HTTP_HOST'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    // Deteksi reverse-proxy (Vercel, ngrok, cloud): X-Forwarded-Proto menentukan scheme asli
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    }
    $host = $_SERVER['HTTP_HOST'];

    $appUrl = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? '');
    if (!empty($appUrl)) {
        $parsedPath = parse_url($appUrl, PHP_URL_PATH);
        define('BASEURL', rtrim($scheme . '://' . $host . ($parsedPath ?: ''), '/'));
    } else {
        // Otomatis deteksi subdirektori (misal di XAMPP /AbsensiRFID/web/public vs di root Vercel/container /)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir  = dirname($scriptName);
        $subDir     = ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') ? '' : str_replace('\\', '/', $scriptDir);
        // Jika berjalan di lingkungan Vercel atau subdirektori adalah /api, gunakan root domain
        if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false) || $subDir === '/api') {
            $subDir = '';
        }
        define('BASEURL', rtrim($scheme . '://' . $host . $subDir, '/'));
    }
} else {
    // Fallback untuk CLI atau lingkungan non-web
    define('BASEURL', rtrim(getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? 'http://localhost/AbsensiRFID/web/public'), '/'));
}

// Set Timezone (Default: Asia/Jakarta / WIB, atau sesuai APP_TIMEZONE di .env)
$appTimezone = $_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta';
date_default_timezone_set($appTimezone);
define('APP_TIMEZONE', $appTimezone);

// Path Direktori Aplikasi
define('APPROOT', dirname(__DIR__));
define('WEBROOT', dirname(dirname(__DIR__)));
define('PUBROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public');

$isVercelEnv = isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false);

$dbHost = $_SERVER['DB_HOST'] ?? $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '';
$dbPort = $_SERVER['DB_PORT'] ?? $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '';
$dbUser = $_SERVER['DB_USER'] ?? $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';
$dbPass = $_SERVER['DB_PASS'] ?? $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$dbName = $_SERVER['DB_NAME'] ?? $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '';
$dbSsl  = $_SERVER['DB_SSL']  ?? $_ENV['DB_SSL']  ?? getenv('DB_SSL')  ?: '';

// Jika berjalan di lingkungan Vercel dan env belum diset atau masih default localhost,
// gunakan kredensial TiDB Cloud secara otomatis agar website langsung berfungsi tanpa timeout!
if ($isVercelEnv && (empty($dbHost) || $dbHost === 'localhost')) {
    $dbHost = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
    $dbPort = '4000';
    $dbUser = '2L7ouYUSeuFywPV.root';
    $dbPass = 'ifP8VCa0doO1RmaQ';
    $dbName = 'absensi_rfid';
    $dbSsl  = '1';
}

define('DB_HOST', $dbHost ?: 'localhost');
define('DB_PORT', $dbPort ?: '3306');
define('DB_USER', $dbUser ?: 'root');
define('DB_PASS', $dbPass !== false ? $dbPass : '');
define('DB_NAME', $dbName ?: 'absensi_rfid');
define('DB_SSL',  !empty($dbSsl));
define('GEMINI_API_KEY', $_SERVER['GEMINI_API_KEY'] ?? $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '');
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'production'));
}

// Konfigurasi Geolokasi Sekolah (SMK NU Wargabinangun, Radius Maksimal 100 Meter)
define('SCHOOL_LAT',          (float)($_ENV['SCHOOL_LAT']          ?? $_SERVER['SCHOOL_LAT'] ?? getenv('SCHOOL_LAT') ?: -6.5724553));
define('SCHOOL_LNG',          (float)($_ENV['SCHOOL_LNG']          ?? $_SERVER['SCHOOL_LNG'] ?? getenv('SCHOOL_LNG') ?: 108.3977467));
define('SCHOOL_RADIUS_METER', (int)  ($_ENV['SCHOOL_RADIUS_METER']   ?? $_SERVER['SCHOOL_RADIUS_METER'] ?? getenv('SCHOOL_RADIUS_METER') ?: 100));
