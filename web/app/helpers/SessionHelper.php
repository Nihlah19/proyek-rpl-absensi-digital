<?php
class SessionHelper {
    private static function getSecretKey() {
        return getenv('APP_KEY') ?: 'smknu_rfid_secret_auth_token_key_2026';
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        // Jika $_SESSION kosong (cold start / beda instance di Vercel), pulihkan otomatis dari auth cookie
        if (empty($_SESSION['user_id'])) {
            self::syncSessionFromCookie();
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public static function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Regenerate session ID — panggil setelah login berhasil untuk
     * mencegah session fixation attack.
     */
    public static function regenerate() {
        @session_regenerate_id(true);
    }

    /**
     * Terbitkan token autentikasi persisten yang ditandatangani HMAC
     * agar sesi login tidak hilang di lingkungan serverless multi-instance (Vercel)
     */
    public static function issueAuthCookie($userId, $username, $role, $name, $classCode = null, $avatar = null) {
        $payload = [
            'id'  => (int)$userId,
            'u'   => $username,
            'r'   => $role,
            'n'   => $name,
            'c'   => $classCode,
            'av'  => $avatar,
            'exp' => time() + (86400 * 7), // Berlaku 7 hari
        ];
        $json = json_encode($payload);
        $encoded = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $encoded, self::getSecretKey());
        $token = $encoded . '.' . $sig;

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        setcookie('smk_auth', $token, [
            'expires'  => time() + (86400 * 7),
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        $_COOKIE['smk_auth'] = $token;
    }

    /**
     * Sinkronisasikan session dari auth cookie jika $_SESSION kosong (cold start / beda instance di Vercel)
     */
    public static function syncSessionFromCookie() {
        if (!empty($_SESSION['user_id'])) {
            return;
        }

        if (empty($_COOKIE['smk_auth'])) {
            return;
        }

        $parts = explode('.', $_COOKIE['smk_auth'], 2);
        if (count($parts) !== 2) {
            return;
        }

        list($encoded, $sig) = $parts;
        $expectedSig = hash_hmac('sha256', $encoded, self::getSecretKey());
        if (!hash_equals($expectedSig, $sig)) {
            return;
        }

        $decodedJson = base64_decode(strtr($encoded, '-_', '+/'));
        if (!$decodedJson) return;

        $data = json_decode($decodedJson, true);
        if (!$data || !is_array($data)) return;

        if (isset($data['exp']) && $data['exp'] < time()) {
            self::clearAuthCookie();
            return;
        }

        $_SESSION['user_id']   = $data['id'];
        $_SESSION['username']  = $data['u'];
        $_SESSION['role']      = $data['r'];
        $_SESSION['name']      = $data['n'];
        if (!empty($data['c'])) {
            $_SESSION['class_code'] = $data['c'];
        }
        if (!empty($data['av'])) {
            $_SESSION['avatar'] = $data['av'];
        }
    }

    /**
     * Hapus cookie autentikasi saat logout
     */
    public static function clearAuthCookie() {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        setcookie('smk_auth', '', [
            'expires'  => time() - 86400,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['smk_auth']);
    }

    /**
     * Hancurkan session secara menyeluruh dan hapus cookie.
     */
    public static function destroy() {
        $_SESSION = [];
        self::clearAuthCookie();
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
        @session_destroy();
    }
}
