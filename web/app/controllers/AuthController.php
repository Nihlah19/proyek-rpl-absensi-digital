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
                SessionHelper::set('avatar_synced_at', time());
                
                $db = new Database();
                $classCode = null;
                if ($user->role === 'wali_kelas' || $user->role === 'walikelas') {
                    $db->query("SELECT class_code FROM kelas WHERE wali_kelas_id = :user_id LIMIT 1");
                    $db->bind(':user_id', $user->id);
                    $kelas = $db->single();
                    if ($kelas) {
                        $classCode = $kelas->class_code;
                        SessionHelper::set('class_code', $kelas->class_code);
                    }
                } elseif ($user->role === 'siswa') {
                    $db->query("SELECT k.status as status_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.user_id = :uid LIMIT 1");
                    $db->bind(':uid', $user->id);
                    $rowS = $db->single();
                    SessionHelper::set('is_student_pkl', ($rowS && ($rowS->status_kelas ?? '') === 'pkl'));
                }

                if (in_array($user->role, ['guru', 'wali_kelas', 'walikelas', 'kepsek'])) {
                    $db->query("SELECT id, barcode_id, is_pembimbing_pkl FROM guru WHERE user_id = :uid LIMIT 1");
                    $db->bind(':uid', $user->id);
                    $rowG = $db->single();
                    if ($rowG) {
                        $bc = !empty($rowG->barcode_id) ? $rowG->barcode_id : sprintf('QR-GURU-%03d', $rowG->id);
                        SessionHelper::set('header_barcode_id', $bc);
                        SessionHelper::set('is_pembimbing_pkl', !empty($rowG->is_pembimbing_pkl));
                    }
                }

                // Terbitkan auth cookie persisten untuk Vercel Serverless & multi-environment
                SessionHelper::issueAuthCookie($user->id, $user->username, $user->role, $user->name, $classCode, null);

                // Pastikan seluruh data sesi tersimpan ke storage/disk sebelum redirect
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }

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

    public function ajax_logout() {
        SessionHelper::destroy();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
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
                header('Location: ' . BASEURL . '/dashboard');
                break;
        }
        exit;
    }
}
