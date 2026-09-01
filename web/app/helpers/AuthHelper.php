<?php
class AuthHelper {
    public static function check() {
        if (!SessionHelper::get('user_id')) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }

    public static function checkRole($roles = []) {
        self::check();
        $userRole = SessionHelper::get('role');
        if (!in_array($userRole, $roles)) {
            header('Location: ' . BASEURL . '/error/forbidden');
            exit;
        }
    }
}
