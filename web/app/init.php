<?php
// Load Config
require_once 'config/config.php';

// Load Core MVC Classes
require_once 'core/App.php';
require_once 'core/Controller.php';
require_once 'core/Database.php';
require_once 'core/Model.php';
require_once 'core/TelegramHelper.php';

// Load Helpers
require_once 'helpers/SessionHelper.php';
require_once 'helpers/AuthHelper.php';
require_once 'helpers/CsrfHelper.php';
require_once 'helpers/ValidationHelper.php';
require_once 'helpers/Flasher.php';

// Default Error Handler
set_exception_handler(function($e) {
    // Log error to storage/logs/error.log
    $logFile = __DIR__ . '/../storage/logs/error.log';
    $message = date('[Y-m-d H:i:s]') . " " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
    error_log($message, 3, $logFile);
    
    // Redirect to 500 error page
    if ($_ENV['APP_ENV'] !== 'development') {
        header('Location: ' . BASEURL . '/error/serverError');
        exit;
    } else {
        echo "<h1>Fatal Error</h1>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        exit;
    }
});
