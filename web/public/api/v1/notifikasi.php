<?php
/**
 * API Endpoint Notifikasi & Push Subscription PWA
 * Digunakan oleh frontend pwa.js dan notification.js
 */
require_once __DIR__ . '/../../../app/init.php';

header('Content-Type: application/json; charset=utf-8');

$userId = (int)SessionHelper::get('user_id');
if (!$userId) {
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

switch ($action) {
    case 'unread_count':
        $count = NotificationHelper::getUnreadCount($userId);
        echo json_encode(['ok' => true, 'count' => $count]);
        break;

    case 'list':
        $limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 15;
        $items = NotificationHelper::getLatest($userId, $limit);
        $count = NotificationHelper::getUnreadCount($userId);
        echo json_encode([
            'ok' => true,
            'count' => $count,
            'data' => $items
        ]);
        break;

    case 'mark_read':
        $input = json_decode(file_get_contents('php://input'), true);
        $notifId = isset($input['notif_id']) ? (int)$input['notif_id'] : (isset($_POST['notif_id']) ? (int)$_POST['notif_id'] : null);
        $success = NotificationHelper::markAsRead($userId, $notifId);
        $newCount = NotificationHelper::getUnreadCount($userId);
        echo json_encode(['ok' => $success, 'unread_count' => $newCount]);
        break;

    case 'test':
        $testId = NotificationHelper::send(
            $userId,
            '🔔 Tes Notifikasi Realtime',
            'Pop-up dan suara notifikasi berhasil berfungsi di perangkat laptop/HP Anda (' . date('H:i:s') . ')',
            'system_test',
            'dashboard'
        );
        $count = NotificationHelper::getUnreadCount($userId);
        echo json_encode(['ok' => (bool)$testId, 'notif_id' => $testId, 'count' => $count, 'message' => 'Tes notifikasi berhasil dikirim']);
        break;

    case 'subscribe':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $endpoint   = trim($input['endpoint'] ?? '');
        $p256dh     = $input['keys']['p256dh'] ?? ($input['p256dh'] ?? null);
        $auth       = $input['keys']['auth'] ?? ($input['auth'] ?? null);
        $fcmToken   = $input['fcm_token'] ?? null;
        $deviceInfo = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);

        if (empty($endpoint) && !empty($fcmToken)) {
            $endpoint = 'fcm:' . $fcmToken;
        }

        if (empty($endpoint)) {
            echo json_encode(['ok' => false, 'message' => 'Endpoint/Token tidak boleh kosong']);
            exit;
        }

        $saved = NotificationHelper::saveSubscription($userId, $endpoint, $p256dh, $auth, $fcmToken, $deviceInfo);
        echo json_encode(['ok' => $saved, 'message' => $saved ? 'Device berhasil didaftarkan untuk notifikasi' : 'Gagal mendaftarkan device']);
        break;

    case 'unsubscribe':
        $input = json_decode(file_get_contents('php://input'), true);
        $endpoint = trim($input['endpoint'] ?? ($_POST['endpoint'] ?? ''));
        if (!empty($endpoint)) {
            NotificationHelper::removeSubscription($userId, $endpoint);
        }
        echo json_encode(['ok' => true, 'message' => 'Unsubscribed']);
        break;

    default:
        echo json_encode(['ok' => false, 'message' => 'Action tidak dikenali']);
        break;
}
