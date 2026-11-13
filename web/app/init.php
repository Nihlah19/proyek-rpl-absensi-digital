<?php
// Sembunyikan deprecation & notices dari browser agar tidak merusak session header HTTP
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors', '1');

// Aktifkan kompresi output GZIP otomatis (Mengurangi payload transfer 75-85% dan mempercepat TTFB/LCP)
if (!headers_sent() && extension_loaded('zlib')) {
    if (!ini_get('zlib.output_compression')) {
        @ini_set('zlib.output_compression', 'On');
        @ini_set('zlib.output_compression_level', '6');
    }
}

// Konfigurasi cookie sesi sebelum session_start()
// agar kompatibel dengan ngrok (HTTPS), Vercel, maupun localhost (HTTP).
if (!session_id()) {
    $isVercel = isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL']) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false);
    if ($isVercel) {
        $sessDir = sys_get_temp_dir() . '/sessions';
        if (!is_dir($sessDir)) {
            @mkdir($sessDir, 0777, true);
        }
        if (is_dir($sessDir) && is_writable($sessDir)) {
            @session_save_path($sessDir);
        }
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
               || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
               || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 86400 * 7,    // 7 hari agar sesi tetap bertahan
        'path'     => '/',
        'secure'   => $isHttps,     // Wajib true saat HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Load Config
require_once __DIR__ . '/config/config.php';

// Load Core MVC Classes
require_once __DIR__ . '/core/App.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/models/AppSetting.php';
require_once __DIR__ . '/core/TelegramHelper.php';
require_once __DIR__ . '/core/NotificationHelper.php';
require_once __DIR__ . '/core/WhatsAppHelper.php';

// Load Helpers
require_once __DIR__ . '/helpers/SessionHelper.php';
require_once __DIR__ . '/helpers/AuthHelper.php';
require_once __DIR__ . '/helpers/CsrfHelper.php';
require_once __DIR__ . '/helpers/ValidationHelper.php';
require_once __DIR__ . '/helpers/Flasher.php';
require_once __DIR__ . '/helpers/BadgeHelper.php';

// Otomatis verifikasi dan sinkronisasi sesi dari signed auth cookie / database
// Memastikan sesi pengguna aktif tidak hilang saat berpindah antar fitur dashboard
SessionHelper::ensureUserSession();

// Default Error Handler
set_exception_handler(function ($e) {
    // Selalu tulis ke file log kustom
    $logFile = __DIR__ . '/../storage/logs/error.log';
    $logMsg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    @file_put_contents($logFile, $logMsg, FILE_APPEND);
    error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if (defined('APP_ENV') && APP_ENV === 'development') {
        echo "<div style='border:1px solid red; padding:10px; margin:10px; font-family:monospace; background:#fff0f0;'>";
        echo "<h4>Uncaught Exception</h4>";
        echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")<br>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        require_once __DIR__ . '/controllers/ErrorController.php';
        $err = new ErrorController();
        $err->serverError();
    }
    exit;
});

// Tangkap PHP fatal error juga
register_shutdown_function(function () {
    $lastError = error_get_last();
    if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logFile = __DIR__ . '/../storage/logs/error.log';
        $logMsg = '[' . date('Y-m-d H:i:s') . '] FATAL: ' . $lastError['message'] . ' in ' . $lastError['file'] . ':' . $lastError['line'] . PHP_EOL;
        @file_put_contents($logFile, $logMsg, FILE_APPEND);
    }
});
