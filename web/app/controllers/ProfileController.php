<?php

class ProfileController extends Controller {

    public function index() {
        AuthHelper::check();
        
        $user_id = SessionHelper::get('user_id');
        $role = SessionHelper::get('role');
        
        $db = new Database();
        
        // Ambil data user dasar
        $db->query("SELECT id, username, name, role, avatar FROM users WHERE id = :id");
        $db->bind(':id', $user_id);
        $user = $db->single();

        // Jika user tidak ditemukan di DB (session tidak valid), redirect ke login
        if (!$user) {
            SessionHelper::destroy();
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
        
        $profileData = null;
        if (in_array($role, ['guru', 'wali_kelas', 'walikelas', 'kepsek'])) {
            $db->query("SELECT id as guru_id, nip, barcode_id, subject FROM guru WHERE user_id = :uid");
            $db->bind(':uid', $user_id);
            $profileData = $db->single();

            // Jika belum ada data guru di DB tapi role adalah guru/wali/kepsek, otomatis buatkan
            if (!$profileData) {
                $db->query("SELECT MAX(id) as max_id FROM guru");
                $maxRow = $db->single();
                $nextId = ($maxRow->max_id ?? 0) + 1;
                $newBarcode = sprintf('QR-GURU-%03d', $nextId);

                $db->query("INSERT INTO guru (user_id, nip, barcode_id, subject) VALUES (:uid, :nip, :bc, :subj)");
                $db->bind(':uid', $user_id);
                $db->bind(':nip', '-');
                $db->bind(':bc', $newBarcode);
                $db->bind(':subj', 'Umum');
                $db->execute();

                $db->query("SELECT id as guru_id, nip, barcode_id, subject FROM guru WHERE user_id = :uid");
                $db->bind(':uid', $user_id);
                $profileData = $db->single();
            } elseif (empty($profileData->barcode_id)) {
                // Jika barcode_id masih kosong atau null di DB, sinkronkan otomatis
                $newBarcode = sprintf('QR-GURU-%03d', $profileData->guru_id);
                $db->query("UPDATE guru SET barcode_id = :bc WHERE id = :gid");
                $db->bind(':bc', $newBarcode);
                $db->bind(':gid', $profileData->guru_id);
                $db->execute();
                $profileData->barcode_id = $newBarcode;
            }
        } elseif ($role === 'siswa') {
            $db->query("SELECT s.nis, r.uid as rfid_uid, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id LEFT JOIN rfid_cards r ON r.student_id = s.id AND r.status = 'active' WHERE s.user_id = :uid");
            $db->bind(':uid', $user_id);
            $profileData = $db->single();
        }

        $data = [
            'title' => 'Profil Saya',
            'user' => $user,
            'profileData' => $profileData
        ];
        
        $this->view('templates/header', $data);
        $this->view('profile/index', $data);
        $this->view('templates/footer');
    }

    public function update() {
        AuthHelper::check();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            $user_id = SessionHelper::get('user_id');
            $name = trim($_POST['name'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $db = new Database();
            
            // Proses Upload Avatar jika ada
            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . $fileExtension;
                    
                    // Folder upload tujuan
                    $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $destPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $avatarPath = $newFileName;
                        
                        // Hapus avatar lama jika ada
                        $db->query("SELECT avatar FROM users WHERE id = :id");
                        $db->bind(':id', $user_id);
                        $oldAvatarRow = $db->single();
                        $oldAvatar = $oldAvatarRow ? $oldAvatarRow->avatar : null;
                        if ($oldAvatar && file_exists($uploadDir . $oldAvatar)) {
                            unlink($uploadDir . $oldAvatar);
                        }
                    }
                } else {
                    Flasher::setFlash('gagal', 'Ekstensi file tidak diizinkan. Gunakan JPG, PNG, atau WEBP.', 'danger');
                    header('Location: ' . BASEURL . '/profile');
                    exit;
                }
            }
            
            // Update Data
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                if ($avatarPath) {
                    $db->query("UPDATE users SET name = :name, password = :password, avatar = :avatar WHERE id = :id");
                    $db->bind(':avatar', $avatarPath);
                } else {
                    $db->query("UPDATE users SET name = :name, password = :password WHERE id = :id");
                }
                $db->bind(':password', $hashedPassword);
            } else {
                if ($avatarPath) {
                    $db->query("UPDATE users SET name = :name, avatar = :avatar WHERE id = :id");
                    $db->bind(':avatar', $avatarPath);
                } else {
                    $db->query("UPDATE users SET name = :name WHERE id = :id");
                }
            }
            
            $db->bind(':name', $name);
            $db->bind(':id', $user_id);
            
            $success = $db->execute();

            // Update NIP for guru, wali_kelas, walikelas, kepsek
            if (in_array(SessionHelper::get('role'), ['guru', 'wali_kelas', 'walikelas', 'kepsek']) && isset($_POST['nip'])) {
                $nip = trim($_POST['nip']);
                $db->query("UPDATE guru SET nip = :nip WHERE user_id = :uid");
                $db->bind(':nip', $nip);
                $db->bind(':uid', $user_id);
                $db->execute();
            }
            
            if ($success) {
                SessionHelper::set('name', $name); // Update nama di session agar UI navbar berubah
                if ($avatarPath) {
                    SessionHelper::set('avatar', $avatarPath);
                } else {
                    $db->query("SELECT avatar FROM users WHERE id = :id");
                    $db->bind(':id', $user_id);
                    $uAvRow = $db->single();
                    if ($uAvRow && !empty($uAvRow->avatar)) {
                        SessionHelper::set('avatar', $uAvRow->avatar);
                    }
                }
                Flasher::setFlash('berhasil', 'Profil berhasil diperbarui.', 'success');
            } else {
                Flasher::setFlash('gagal', 'Profil gagal diperbarui.', 'danger');
            }
            
            header('Location: ' . BASEURL . '/profile');
            exit;
        }
    }

    /**
     * Sinkronkan atau regenerate barcode guru agar selalu valid dan sinkron.
     */
    public function syncBarcode() {
        AuthHelper::check();
        $user_id = SessionHelper::get('user_id');
        $role = SessionHelper::get('role');

        if (!in_array($role, ['guru', 'wali_kelas', 'walikelas', 'kepsek', 'admin'])) {
            header('Location: ' . BASEURL . '/profile');
            exit;
        }

        $db = new Database();
        $db->query("SELECT id, barcode_id FROM guru WHERE user_id = :uid");
        $db->bind(':uid', $user_id);
        $guru = $db->single();

        if ($guru) {
            $newBarcode = sprintf('QR-GURU-%03d', $guru->id);
            // Cek apakah kode ini sudah dipakai oleh guru lain
            $db->query("SELECT id FROM guru WHERE barcode_id = :bc AND id != :gid");
            $db->bind(':bc', $newBarcode);
            $db->bind(':gid', $guru->id);
            if ($db->single()) {
                $newBarcode = 'QR-GURU-' . $guru->id . '-' . rand(100, 999);
            }

            $db->query("UPDATE guru SET barcode_id = :bc WHERE id = :gid");
            $db->bind(':bc', $newBarcode);
            $db->bind(':gid', $guru->id);
            $db->execute();

            Flasher::setFlash('berhasil', 'Barcode ID berhasil disinkronkan: ' . $newBarcode, 'success');
        } else {
            Flasher::setFlash('gagal', 'Data guru tidak ditemukan untuk disinkronkan.', 'danger');
        }

        header('Location: ' . BASEURL . '/profile');
        exit;
    }
}
