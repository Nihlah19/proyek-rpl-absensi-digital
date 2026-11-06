<?php
// Konfigurasi cookie sesi sebelum session_start()
// agar kompatibel dengan ngrok (HTTPS), Vercel, maupun localhost (HTTP).
if (!session_id()) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',           // Kosong = gunakan domain host aktif secara otomatis
        'secure'   => $isHttps,     // Wajib true saat HTTPS
        'httponly' => true,
        'samesite' => $isHttps ? 'None' : 'Lax',
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
