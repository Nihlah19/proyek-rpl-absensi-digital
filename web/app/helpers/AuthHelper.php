<?php
class AuthHelper {
    public static function check() {
        SessionHelper::ensureUserSession();
        if (!SessionHelper::get('user_id')) {
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }

    public static function checkRole($roles = []) {
        self::check();
        $userRole = SessionHelper::get('role');

        // Normalisasi kesetaraan role wali_kelas dan walikelas
        $normalizedRoles = [];
        foreach ($roles as $r) {
            $normalizedRoles[] = $r;
            if ($r === 'wali_kelas') $normalizedRoles[] = 'walikelas';
            if ($r === 'walikelas') $normalizedRoles[] = 'wali_kelas';
        }

        if (!in_array($userRole, $normalizedRoles)) {
            // Jika role pengguna tidak sesuai dengan hak akses halaman,
            // arahkan kembali ke Dashboard utama pengguna (bukan ke halaman login)
            header('Location: ' . BASEURL . '/dashboard');
            exit;
        }
    }
}
