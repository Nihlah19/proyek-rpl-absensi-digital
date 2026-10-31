<?php
require_once __DIR__ . '/../../app/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!$payload) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$request_id = uniqid('client_');

$logModel = new LogError();
$logModel->insertLog([
    'request_id'    => $request_id,
    'sumber'        => 'client',
    'barcode_input' => $payload['barcode'] ?? null,
    'jenis_error'   => substr($payload['jenis_error'] ?? 'unknown_client_error', 0, 100),
    'pesan_error'   => $payload['pesan_error'] ?? 'Tidak ada pesan detail',
    'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? null,
    'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? null
]);

echo json_encode([
    'success' => true,
    'request_id' => $request_id
]);

