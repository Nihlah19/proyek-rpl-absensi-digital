<?php
class SessionHelper {
    private static function getSecretKey() {
        if (!empty($_ENV['APP_KEY'])) return $_ENV['APP_KEY'];
        if (!empty($_SERVER['APP_KEY'])) return $_SERVER['APP_KEY'];
        $val = getenv('APP_KEY');
        if (!empty($val)) return $val;
        return 'smknu_rfid_secret_auth_token_key_2026_fixed';
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        // Jika $_SESSION kosong atau role belum terisi, pastikan sesi dipulihkan otomatis
        if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
            self::ensureUserSession();
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
     * maupun navigasi fitur dashboard.
     */
    public static function issueAuthCookie($userId, $username, $role, $name, $classCode = null, $avatar = null) {
        // JANGAN pernah simpan data URI base64 atau string gambar ke cookie (limit browser max 4KB)
        $cleanAvatar = null;
        if (!empty($avatar) && is_string($avatar) && strlen($avatar) < 128 && strpos($avatar, 'data:') !== 0) {
            $cleanAvatar = $avatar;
        }

        $payload = [
            'id'  => (int)$userId,
            'u'   => (string)$username,
            'r'   => (string)$role,
            'n'   => (string)$name,
            'c'   => $classCode,
            'av'  => $cleanAvatar,
            'exp' => time() + (86400 * 7), // Berlaku 7 hari
        ];
        $json = json_encode($payload);
        $encoded = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $encoded, self::getSecretKey());
        $token = $encoded . '.' . $sig;

        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
                   || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                   || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
                   || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        $cookieOptions = [
            'expires'  => time() + (86400 * 7),
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        setcookie('smk_auth', $token, $cookieOptions);
        $_COOKIE['smk_auth'] = $token;
    }

    /**
     * Sinkronisasikan session dari auth cookie jika $_SESSION kosong (cold start / beda instance di Vercel)
     */
    public static function syncSessionFromCookie() {
        if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
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

        $_SESSION['user_id']   = (int)$data['id'];
        $_SESSION['username']  = $data['u'] ?? '';
        $_SESSION['role']      = $data['r'] ?? '';
        $_SESSION['name']      = $data['n'] ?? '';
        if (!empty($data['c'])) {
            $_SESSION['class_code'] = $data['c'];
        }
        if (!empty($data['av'])) {
            $_SESSION['avatar'] = $data['av'];
        }
    }

    /**
     * Memastikan sesi pengguna terisi penuh dan konsisten antar request.
     * Jika session hilang di memory, dipulihkan dari auth cookie atau database.
     */
    public static function ensureUserSession() {
        // Langkah 1: Sinkronisasi dari cookie jika session kosong
        self::syncSessionFromCookie();

        // Langkah 2: Jika user_id ada tapi role/name kosong, reload dari database
        if (!empty($_SESSION['user_id']) && (empty($_SESSION['role']) || empty($_SESSION['name']))) {
            try {
                $db = new Database();
                $db->query("SELECT id, username, name, role, avatar FROM users WHERE id = :id LIMIT 1");
                $db->bind(':id', $_SESSION['user_id']);
                $u = $db->single();
                if ($u) {
                    $_SESSION['username'] = $u->username;
                    $_SESSION['name']     = $u->name;
                    $_SESSION['role']     = $u->role;
                    if (!empty($u->avatar)) {
                        $_SESSION['avatar'] = $u->avatar;
                    }
                    if (($u->role === 'wali_kelas' || $u->role === 'walikelas') && empty($_SESSION['class_code'])) {
                        $db->query("SELECT class_code FROM kelas WHERE wali_kelas_id = :uid LIMIT 1");
                        $db->bind(':uid', $u->id);
                        $k = $db->single();
                        if ($k && !empty($k->class_code)) {
                            $_SESSION['class_code'] = $k->class_code;
                        }
                    }
                }
            } catch (Throwable $e) {
                error_log("[SessionHelper::ensureUserSession] DB fallback error: " . $e->getMessage());
            }
        }

        // Langkah 3: Jika user telah login di PHP session tapi cookie smk_auth belum ada, terbitkan cookie
        if (!empty($_SESSION['user_id']) && !empty($_SESSION['role']) && empty($_COOKIE['smk_auth'])) {
            self::issueAuthCookie(
                $_SESSION['user_id'],
                $_SESSION['username'] ?? '',
                $_SESSION['role'],
                $_SESSION['name'] ?? '',
                $_SESSION['class_code'] ?? null,
                null
            );
        }
    }

    /**
     * Hapus cookie autentikasi saat logout
     */
    public static function clearAuthCookie() {
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
                   || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                   || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
                   || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        setcookie('smk_auth', '', [
            'expires'  => time() - 86400,
            'path'     => '/',
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
                $p['path'], '', $p['secure'], $p['httponly']
            );
        }
        @session_destroy();
    }
}
