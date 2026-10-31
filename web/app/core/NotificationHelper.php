<?php
/**
 * NotificationHelper - Engine Pengiriman Notifikasi Realtime & Push PWA
 * Mendukung In-App Notification, Web Notification API, Service Worker Push,
 * dan FCM (Firebase Cloud Messaging) untuk HP Android / iOS.
 */
class NotificationHelper {

    /**
     * Kirim notifikasi ke seorang pengguna spesifik (users.id)
     *
     * @param int $userId ID penerima (users.id)
     * @param string $judul Judul pop-up notifikasi (misal: "Absensi Masuk Berhasil")
     * @param string $pesan Isi pesan notifikasi
     * @param string $tipe Kategori ('kehadiran_masuk', 'kehadiran_pulang', 'pulang_cepat_pengajuan', 'pulang_cepat_approved', 'pulang_cepat_rejected', 'catatan_kepsek', 'catatan_walikelas', 'general')
     * @param string|null $url Link saat notifikasi di-klik (misal: '/catatan', '/izin/approval', '/absensi/siswa')
     * @return int|bool ID notifikasi yang tersimpan atau false jika gagal
     */
    public static function send(int $userId, string $judul, string $pesan, string $tipe = 'general', ?string $url = null) {
        if ($userId <= 0 || empty($judul) || empty($pesan)) {
            return false;
        }

        try {
            $db = new Database();
            $db->query("INSERT INTO notifikasi (user_id, judul, pesan, tipe, url, is_read, created_at)
                        VALUES (:user_id, :judul, :pesan, :tipe, :url, 0, NOW())");
            $db->bind(':user_id', $userId);
            $db->bind(':judul', $judul);
            $db->bind(':pesan', $pesan);
            $db->bind(':tipe', $tipe);
            $db->bind(':url', $url);
            $db->execute();

            $insertId = (int)$db->lastInsertId();

            // Jalankan push ke perangkat mobile di background (jika ada token FCM terdaftar)
            self::dispatchPushToUserDevices($userId, $judul, $pesan, $tipe, $url, $insertId);

            return $insertId;
        } catch (Exception $e) {
            error_log("[NotificationHelper::send] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi ke semua user dengan role tertentu (misal: semua 'admin' atau 'kepsek')
     */
    public static function sendToRole(string $role, string $judul, string $pesan, string $tipe = 'general', ?string $url = null) {
        try {
            $db = new Database();
            $db->query("SELECT id FROM users WHERE role = :role");
            $db->bind(':role', $role);
            $users = $db->resultSet();

            $count = 0;
            foreach ($users as $u) {
                if (self::send((int)$u->id, $judul, $pesan, $tipe, $url)) {
                    $count++;
                }
            }
            return $count;
        } catch (Exception $e) {
            error_log("[NotificationHelper::sendToRole] Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil jumlah notifikasi belum dibaca oleh user
     */
    public static function getUnreadCount(int $userId): int {
        try {
            $db = new Database();
            $db->query("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = :uid AND is_read = 0");
            $db->bind(':uid', $userId);
            $row = $db->single();
            return (int)($row->total ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Ambil daftar notifikasi terbaru untuk user
     */
    public static function getLatest(int $userId, int $limit = 20): array {
        try {
            $db = new Database();
            $db->query("SELECT * FROM notifikasi WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim");
            $db->bind(':uid', $userId);
            $db->bind(':lim', $limit, PDO::PARAM_INT);
            return $db->resultSet() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Tandai notifikasi telah dibaca
     */
    public static function markAsRead(int $userId, ?int $notifId = null): bool {
        try {
            $db = new Database();
            if ($notifId !== null && $notifId > 0) {
                $db->query("UPDATE notifikasi SET is_read = 1 WHERE id = :id AND user_id = :uid");
                $db->bind(':id', $notifId);
                $db->bind(':uid', $userId);
            } else {
                $db->query("UPDATE notifikasi SET is_read = 1 WHERE user_id = :uid AND is_read = 0");
                $db->bind(':uid', $userId);
            }
            return $db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Simpan / Perbarui Push Subscription atau Token FCM dari HP pengguna
     */
    public static function saveSubscription(int $userId, string $endpoint, ?string $p256dh = null, ?string $auth = null, ?string $fcmToken = null, ?string $deviceInfo = null): bool {
        if ($userId <= 0 || empty($endpoint)) {
            return false;
        }

        try {
            $db = new Database();
            // Cek apakah endpoint sudah terdaftar
            $db->query("SELECT id FROM push_subscriptions WHERE user_id = :uid AND endpoint = :ep");
            $db->bind(':uid', $userId);
            $db->bind(':ep', $endpoint);
            $existing = $db->single();

            if ($existing) {
                $db->query("UPDATE push_subscriptions 
                            SET p256dh_key = :p256dh, auth_key = :auth, fcm_token = :fcm, device_info = :dev, updated_at = NOW()
                            WHERE id = :id");
                $db->bind(':id', $existing->id);
            } else {
                $db->query("INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, fcm_token, device_info, created_at)
                            VALUES (:uid, :ep, :p256dh, :auth, :fcm, :dev, NOW())");
                $db->bind(':uid', $userId);
                $db->bind(':ep', $endpoint);
            }

            $db->bind(':p256dh', $p256dh);
            $db->bind(':auth', $auth);
            $db->bind(':fcm', $fcmToken);
            $db->bind(':dev', $deviceInfo);
            return $db->execute();
        } catch (Exception $e) {
            error_log("[NotificationHelper::saveSubscription] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus push subscription jika user logout atau mencabut izin
     */
    public static function removeSubscription(int $userId, string $endpoint): bool {
        try {
            $db = new Database();
            $db->query("DELETE FROM push_subscriptions WHERE user_id = :uid AND endpoint = :ep");
            $db->bind(':uid', $userId);
            $db->bind(':ep', $endpoint);
            return $db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Kirim push notification ke perangkat pengguna jika ada FCM Server Key
     */
    private static function dispatchPushToUserDevices(int $userId, string $judul, string $pesan, string $tipe, ?string $url, int $notifId) {
        $fcmServerKey = getenv('FCM_SERVER_KEY') ?: ($_ENV['FCM_SERVER_KEY'] ?? null);
        if (!$fcmServerKey) {
            return;
        }

        try {
            $db = new Database();
            $db->query("SELECT fcm_token FROM push_subscriptions WHERE user_id = :uid AND fcm_token IS NOT NULL AND fcm_token != ''");
            $db->bind(':uid', $userId);
            $rows = $db->resultSet();

            if (empty($rows)) {
                return;
            }

            $tokens = array_unique(array_filter(array_map(function($r) { return $r->fcm_token; }, $rows)));
            if (empty($tokens)) {
                return;
            }

            $clickUrl = $url ? (rtrim(BASEURL, '/') . '/' . ltrim($url, '/')) : (BASEURL . '/dashboard');
            $iconUrl  = rtrim(BASEURL, '/') . '/assets/img/icon-192.png';

            $payload = [
                'registration_ids' => array_values($tokens),
                'notification' => [
                    'title' => $judul,
                    'body'  => $pesan,
                    'icon'  => $iconUrl,
                    'click_action' => $clickUrl,
                    'vibrate' => [250, 100, 250, 100, 250]
                ],
                'data' => [
                    'notif_id' => (string)$notifId,
                    'type'     => $tipe,
                    'url'      => $clickUrl
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: key=' . $fcmServerKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            error_log("[NotificationHelper::dispatchPush] Error: " . $e->getMessage());
        }
    }
}
