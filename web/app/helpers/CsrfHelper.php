<?php
class CsrfHelper {
    public static function generate() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function check($token) {
        if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }

    public static function input() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}
