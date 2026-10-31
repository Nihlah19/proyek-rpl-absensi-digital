<?php
/**
 * cron_auto_alfa.php
 * Script background / cron job untuk mencatat status Alfa otomatis bagi siswa yang tidak hadir.
 * 
 * Penggunaan:
 * 1. CLI: php cron_auto_alfa.php
 * 2. Web/HTTP: GET http://domain/api/cron_auto_alfa.php?key=YOUR_CRON_KEY
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/core/Database.php';
require_once __DIR__ . '/../../app/core/Model.php';
require_once __DIR__ . '/../../app/models/Absensi.php';

// Validasi otorisasi jika diakses melalui web browser
$isCli = (php_sapi_name() === 'cli' || defined('STDIN'));

if (!$isCli) {
    header('Content-Type: application/json');
    $secretKey = $_ENV['CRON_KEY'] ?? 'absensi_rfid_cron_secret_2026';
    $inputKey  = $_GET['key'] ?? '';

    if ($inputKey !== $secretKey) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized: Invalid cron key']);
        exit;
    }
}

$absensi = new Absensi();
$resultSiswa = $absensi->generateAlfaSiswaHariIni();
$resultGuru  = $absensi->generateAlfaGuruHariIni();

$combinedResult = [
    'success' => ($resultSiswa['success'] ?? true) && ($resultGuru['success'] ?? true),
    'message' => "Siswa: {$resultSiswa['message']} | Guru: {$resultGuru['message']}",
    'siswa'   => $resultSiswa,
    'guru'    => $resultGuru
];

if ($isCli) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $combinedResult['message'] . PHP_EOL;
} else {
    echo json_encode($combinedResult);
}
