<?php
class IzinController extends Controller {

    public function index() {
        AuthHelper::check();
        $role = SessionHelper::get('role');
        
        if (in_array($role, ['siswa', 'guru'])) {
            $this->riwayat();
        } elseif (in_array($role, ['walikelas', 'kepsek'])) {
            $this->approval();
        } else {
            // Admin or other roles
            header('Location: ' . BASEURL);
            exit;
        }
    }

    public function riwayat() {
        AuthHelper::checkRole(['siswa', 'guru']);
        $izinModel = $this->model('Izin');
        $userId = SessionHelper::get('user_id');
        
        $data = [
            'judul' => 'Pengajuan Saya',
            'izin' => $izinModel->getRiwayatUser($userId)
        ];
        
        $this->view('templates/header', $data);
        $this->view('izin/riwayat', $data);
        $this->view('templates/footer');
    }

    public function ajukan() {
        AuthHelper::checkRole(['siswa', 'guru']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Cek CSRF
            if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/izin/ajukan');
                exit;
            }

            // Validasi Input
            if (!ValidationHelper::required(['tanggal_mulai', 'tanggal_selesai', 'jenis', 'alasan'], $_POST)) {
                Flasher::setFlash('gagal', 'Harap isi semua field yang wajib.', 'danger');
                header('Location: ' . BASEURL . '/izin/ajukan');
                exit;
            }

            $userId = SessionHelper::get('user_id');
            $fileBukti = null;

            // Upload File
            if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES['file_bukti'];
                
                // Max 2MB
                if ($file['size'] > 2000000) {
                    Flasher::setFlash('gagal', 'Ukuran file maksimal 2MB.', 'danger');
                    header('Location: ' . BASEURL . '/izin/ajukan');
                    exit;
                }

                // Extension & MIME validation
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
                $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                    Flasher::setFlash('gagal', 'Format file tidak didukung. Gunakan PDF/JPG/PNG.', 'danger');
                    header('Location: ' . BASEURL . '/izin/ajukan');
                    exit;
                }

                // Generate random filename
                $filename = bin2hex(random_bytes(16)) . '.' . $ext;
                
                // Save out of public directory or in specific storage
                // We'll store it in `web/storage/uploads/izin`
                $uploadDir = __DIR__ . '/../../storage/uploads/izin/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $fileBukti = $filename;
                } else {
                    Flasher::setFlash('gagal', 'Gagal mengupload file bukti.', 'danger');
                    header('Location: ' . BASEURL . '/izin/ajukan');
                    exit;
                }
            }

            $data = [
                'user_id' => $userId,
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'],
                'jenis' => $_POST['jenis'],
                'alasan' => $_POST['alasan'],
                'keterangan' => $_POST['keterangan'] ?? '',
                'file_bukti' => $fileBukti
            ];

            $izinModel = $this->model('Izin');
            if ($izinModel->ajukan($data) > 0) {
                // Notifikasi Telegram
                $db = new Database();
                $db->query("SELECT u.name as pengaju, k.nama_kelas, w.telegram_chat_id as wali_chat_id
                            FROM users u 
                            LEFT JOIN siswa s ON s.user_id = u.id
                            LEFT JOIN kelas k ON s.kelas_id = k.id
                            LEFT JOIN users w ON k.wali_kelas_id = w.id
                            WHERE u.id = :id");
                $db->bind(':id', $userId);
                $info = $db->single();
                
                if ($info && !empty($info->wali_chat_id)) {
                    $pesan = "📋 *PENGAJUAN " . strtoupper($_POST['jenis']) . "*\n\n" .
                             "Siswa: " . $info->pengaju . "\n" .
                             "Kelas: " . $info->nama_kelas . "\n" .
                             "Tanggal: " . $_POST['tanggal_mulai'] . " s/d " . $_POST['tanggal_selesai'] . "\n" .
                             "Alasan: " . $_POST['alasan'] . "\n\n" .
                             "Silakan buka sistem untuk melakukan approval.";
                    TelegramHelper::sendMessage($info->wali_chat_id, $pesan);
                }

                Flasher::setFlash('berhasil', 'Pengajuan berhasil dikirim.', 'success');
                header('Location: ' . BASEURL . '/izin');
            } else {
                Flasher::setFlash('gagal', 'Gagal mengirim pengajuan.', 'danger');
                header('Location: ' . BASEURL . '/izin/ajukan');
            }
            exit;
        }

        $data = [
            'judul' => 'Buat Pengajuan'
        ];
        $this->view('templates/header', $data);
        $this->view('izin/ajukan', $data);
        $this->view('templates/footer');
    }

    public function approval() {
        AuthHelper::checkRole(['walikelas', 'kepsek']);
        $izinModel = $this->model('Izin');
        $userId = SessionHelper::get('user_id');
        $role = SessionHelper::get('role');
        
        if ($role == 'walikelas') {
            $list = $izinModel->getDaftarPersetujuanWali($userId);
        } else {
            $list = $izinModel->getDaftarPersetujuanKepsek();
        }

        $data = [
            'judul' => 'Daftar Approval',
            'izin' => $list
        ];
        
        $this->view('templates/header', $data);
        $this->view('izin/approval_list', $data);
        $this->view('templates/footer');
    }

    public function detail($id) {
        AuthHelper::check();
        $izinModel = $this->model('Izin');
        $izin = $izinModel->getById($id);
        
        if (!$izin) {
            Flasher::setFlash('gagal', 'Data tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/izin');
            exit;
        }

        $userId = SessionHelper::get('user_id');
        $role = SessionHelper::get('role');

        // Access control
        if ($role == 'siswa' || $role == 'guru') {
            if ($izin->user_id != $userId) {
                header('Location: ' . BASEURL . '/izin');
                exit;
            }
        } elseif ($role == 'walikelas') {
            if ($izin->role != 'siswa') { // Assuming strict binding handled via query, simple check here
                header('Location: ' . BASEURL . '/izin');
                exit;
            }
        } elseif ($role == 'kepsek') {
            if ($izin->role != 'guru') {
                header('Location: ' . BASEURL . '/izin');
                exit;
            }
        }

        $data = [
            'judul' => 'Detail Pengajuan',
            'izin' => $izin
        ];
        $this->view('templates/header', $data);
        $this->view('izin/detail', $data);
        $this->view('templates/footer');
    }

    public function action() {
        AuthHelper::checkRole(['walikelas', 'kepsek']);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Cek CSRF
            if (!CsrfHelper::check($_POST['csrf_token'] ?? '')) {
                Flasher::setFlash('gagal', 'Token CSRF tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/izin/approval');
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? ''; // 'approved' or 'rejected'
            $catatan = ValidationHelper::sanitize($_POST['catatan'] ?? '');
            $userId = (int)SessionHelper::get('user_id');
            
            if ($id <= 0 || !in_array($status, ['approved', 'rejected'])) {
                header('Location: ' . BASEURL . '/izin/approval');
                exit;
            }

            $izinModel = $this->model('Izin');
            $pengajuan = $izinModel->getById($id);
            
            if (!$pengajuan) {
                Flasher::setFlash('gagal', 'Data tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/izin/approval');
                exit;
            }

            // Verifikasi authorization lebih ketat
            $role = SessionHelper::get('role');
            if ($role === 'walikelas' && $pengajuan->role !== 'siswa') {
                header('Location: ' . BASEURL . '/izin/approval');
                exit;
            }
            if ($role === 'kepsek' && $pengajuan->role !== 'guru') {
                header('Location: ' . BASEURL . '/izin/approval');
                exit;
            }

            if ($izinModel->updateStatus($id, $status, $userId, $catatan) > 0) {
                if ($status == 'approved') {
                    $izinModel->tambahAbsensiDariIzin($pengajuan);
                }
                
                // Notifikasi Telegram setelah disetujui/ditolak
                $db = new Database();
                $db->query("SELECT u.telegram_chat_id as pengaju_chat_id, w.telegram_chat_id as wali_chat_id 
                            FROM users u 
                            LEFT JOIN siswa s ON s.user_id = u.id
                            LEFT JOIN kelas k ON s.kelas_id = k.id
                            LEFT JOIN users w ON k.wali_kelas_id = w.id
                            WHERE u.id = :id");
                $db->bind(':id', $pengajuan->user_id);
                $info = $db->single();

                if ($info) {
                    $pesan = "Pengajuan " . strtoupper($pengajuan->jenis) . " Anda telah *" . strtoupper($status) . "* oleh Approval.\nCatatan: " . $catatan;
                    
                    // Kirim ke Pengaju (Jika ada chat ID)
                    if (!empty($info->pengaju_chat_id)) {
                        TelegramHelper::sendMessage($info->pengaju_chat_id, $pesan);
                    }
                    
                    // Kirim info tembusan ke Wali Kelas (jika disetujui)
                    if (!empty($info->wali_chat_id) && $status == 'approved') {
                        TelegramHelper::sendMessage($info->wali_chat_id, "Pengajuan atas nama ID User " . $pengajuan->user_id . " telah disetujui dan diabsenkan.");
                    }
                }

                Flasher::setFlash('berhasil', 'Status pengajuan berhasil diperbarui.', 'success');
            } else {
                Flasher::setFlash('gagal', 'Gagal memperbarui status pengajuan.', 'danger');
            }
            
            header('Location: ' . BASEURL . '/izin/approval');
            exit;
        }
    }

    public function file($filename) {
        AuthHelper::check();
        // Prevent path traversal
        $filename = basename($filename);
        $filepath = __DIR__ . '/../../storage/uploads/izin/' . $filename;
        
        if (file_exists($filepath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filepath);
            finfo_close($finfo);
            
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        } else {
            http_response_code(404);
            echo "File not found.";
        }
    }
}
