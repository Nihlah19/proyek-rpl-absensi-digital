<?php
/**
 * web/public/api/attendance.php
 * API endpoint terpisah untuk absensi dari ESP32 / scanner
 * Dipisah dari App.php router biar lebih clean.
 *
 * Usage:
 *   POST {BASE_URL}/api/attendance
 *   Headers: Content-Type: application/json, X-Device-Code, X-Device-Secret
 *   Body: JSON sesuai tipe (student / teacher)
 */

require_once __DIR__ . '/../app/init.php';

use App\Controllers\ApiController;

$api = new ApiController();
$api->iot('attendance', 'student');  // default — ditimpa oleh action di method iot()
