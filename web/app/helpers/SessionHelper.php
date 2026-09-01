<?php
class SessionHelper {
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
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
        session_regenerate_id(true);
    }

    /**
     * Hancurkan session secara menyeluruh dan hapus cookie.
     */
    public static function destroy() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
        session_destroy();
    }
}

