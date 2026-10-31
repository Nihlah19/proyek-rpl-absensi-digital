<?php
/**
 * /api/geo-config.php
 * Expose konfigurasi geolokasi sekolah ke frontend (hanya untuk user yang sudah login).
 * Tidak mengekspose credentials atau data sensitif lain.
 */
ob_start();
require_once __DIR__ . '/../../app/init.php';

header('Content-Type: application/json');

    // (Bisa diakses publik untuk keperluan monitor / display)

$output = ob_get_clean();
echo json_encode([
    'success' => true,
    'data' => [
        'lat'    => SCHOOL_LAT,
        'lng'    => SCHOOL_LNG,
        'radius' => SCHOOL_RADIUS_METER,
    ],
    'debug_output' => $output
]);
exit;
