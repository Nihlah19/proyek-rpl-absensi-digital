<?php
class AuthController extends Controller {
    public function index() {
        // Redirect to login if accessing /auth
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    public function login() {
        // Jika sudah login, redirect sesuai role
        if (SessionHelper::get('user_id')) {
            $this->redirectBasedOnRole(SessionHelper::get('role'));
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Cek CSRF
            if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('gagal', 'Token tidak valid (CSRF).', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }

            // Validasi Input
            if (!ValidationHelper::required(['username', 'password'], $_POST)) {
                Flasher::setFlash('gagal', 'Username dan Password harus diisi.', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }

            $username = ValidationHelper::sanitize($_POST['username']);
            $password = $_POST['password'];

            $userModel = $this->model('User');
            $user = $userModel->getByUsername($username);

            if ($user && password_verify($password, $user->password)) {
                // Regenerate session ID untuk mencegah session fixation
                SessionHelper::regenerate();

                // Set Session
                SessionHelper::set('user_id', $user->id);
                SessionHelper::set('username', $user->username);
                SessionHelper::set('name', $user->name);
                SessionHelper::set('role', $user->role);
                SessionHelper::set('avatar', $user->avatar ?? null);
                
                $classCode = null;
                if ($user->role === 'wali_kelas' || $user->role === 'walikelas') {
                    $db = new Database();
                    $db->query("SELECT class_code FROM kelas WHERE wali_kelas_id = :user_id");
                    $db->bind(':user_id', $user->id);
                    $kelas = $db->single();
                    if ($kelas) {
                        $classCode = $kelas->class_code;
                        SessionHelper::set('class_code', $kelas->class_code);
                    }
                }

                // Terbitkan auth cookie persisten untuk Vercel Serverless
                SessionHelper::issueAuthCookie($user->id, $user->username, $user->role, $user->name, $classCode, $user->avatar ?? null);

                $this->redirectBasedOnRole($user->role);
            } else {
                Flasher::setFlash('gagal', 'Username atau Password salah.', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }
        } else {
            $data['judul'] = 'Login System';
            // Panggil view khusus login (tanpa sidebar)
            $this->view('auth/login', $data);
        }
    }

    public function logout() {
        SessionHelper::destroy();
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    private function redirectBasedOnRole($role) {
        switch ($role) {
            case 'admin':
                header('Location: ' . BASEURL . '/dashboard/admin');
                break;
            case 'guru':
                header('Location: ' . BASEURL . '/dashboard/guru');
                break;
            case 'kepsek':
                header('Location: ' . BASEURL . '/dashboard/kepala_sekolah');
                break;
            case 'wali_kelas':
            case 'walikelas':
                header('Location: ' . BASEURL . '/dashboard/wali_kelas');
                break;
            case 'siswa':
                header('Location: ' . BASEURL . '/dashboard/siswa');
                break;
            default:
                header('Location: ' . BASEURL . '/auth/login');
                break;
        }
        exit;
    }
}
