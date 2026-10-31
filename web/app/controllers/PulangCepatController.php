<?php

class PulangCepatController extends Controller {
    
    private $pengajuanModel;
    
    public function __construct() {
        AuthHelper::check();
        $this->pengajuanModel = $this->model('PengajuanPulangCepat');
    }

    public function index() {
        AuthHelper::checkRole(['admin', 'wali_kelas']);

        $userId = (int)SessionHelper::get('user_id');
        $role   = SessionHelper::get('role');
        $waliKelasId = ($role === 'wali_kelas') ? $userId : null;
        
        $data = [
            'judul'     => 'Pengajuan Pulang Cepat',
            'pengajuan' => $this->pengajuanModel->getDaftarPengajuan($waliKelasId)
        ];

        $this->view('templates/header', $data);
        $this->view('pulang_cepat/index', $data);
        $this->view('templates/footer');
    }

    public function approve($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $role = SessionHelper::get('role');
        $userId = (int)SessionHelper::get('user_id');
        
        if (!in_array($role, ['admin', 'wali_kelas'])) {
            echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $db = new Database();
        
        try {
            $db->query("START TRANSACTION");
            $db->execute();

            // 1. Ambil pengajuan dengan row lock
            $db->query("SELECT * FROM pengajuan_pulang_cepat WHERE id = :id FOR UPDATE");
            $db->bind(':id', $id);
            $lockedPengajuan = $db->single();

            // 2. Pastikan status masih PENDING
            if (!$lockedPengajuan || $lockedPengajuan->status !== 'PENDING') {
                $db->query("ROLLBACK");
                $db->execute();
                echo json_encode(['ok' => false, 'message' => 'Status sudah diproses atau data tidak ditemukan']);
                exit;
            }

            // 3. Validasi authorization
            if ($role === 'wali_kelas' && $lockedPengajuan->wali_kelas_id != $userId) {
                $db->query("ROLLBACK");
                $db->execute();
                echo json_encode(['ok' => false, 'message' => 'Anda tidak memiliki hak untuk menyetujui pengajuan ini']);
                exit;
            }

            // 4. Update status = APPROVED
            $db->query("UPDATE pengajuan_pulang_cepat SET status = 'APPROVED', approved_by = :approved_by, approved_at = NOW() WHERE id = :id");
            $db->bind(':id', $id);
            $db->bind(':approved_by', $userId);
            $db->execute();

            // 5. Update absensi_siswa sesuai dengan alasan yang diajukan (sakit / izin)
            $alasanText = trim($lockedPengajuan->alasan ?? '');
            $alasanUpper = strtoupper($alasanText);
            if (strpos($alasanUpper, 'SAKIT') !== false) {
                $targetStatus = 'sakit';
            } elseif (strpos($alasanUpper, 'IZIN') !== false) {
                $targetStatus = 'izin';
            } else {
                $targetStatus = 'izin';
            }

            $jamPulangFmt = !empty($lockedPengajuan->jam_pengajuan) ? substr($lockedPengajuan->jam_pengajuan, 0, 5) : '';
            $ketText = "Pulang Cepat (" . ($alasanText ?: 'Disetujui') . ")";
            if ($jamPulangFmt) {
                $ketText .= " - Jam {$jamPulangFmt}";
            }

            $db->query("UPDATE absensi_siswa SET jam_pulang = :jam_pulang, status = :status, keterangan = :keterangan WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
            $db->bind(':jam_pulang', $lockedPengajuan->jam_pengajuan);
            $db->bind(':status', $targetStatus);
            $db->bind(':keterangan', $ketText);
            $db->bind(':siswa_id', $lockedPengajuan->siswa_id);
            $db->bind(':tanggal', $lockedPengajuan->tanggal);
            $db->execute();

            // 6. Commit transaction
            $db->query("COMMIT");
            $db->execute();

            // Send telegram to student if available
            try {
                $db->query("SELECT u.id as user_id, u.telegram_chat_id, k.telegram_bot_token 
                            FROM users u 
                            JOIN siswa s ON u.id = s.user_id 
                            JOIN kelas k ON s.kelas_id = k.id 
                            WHERE s.id = :siswa_id");
                $db->bind(':siswa_id', $lockedPengajuan->siswa_id);
                $siswaInfo = $db->single();

                if ($siswaInfo) {
                    if (!empty($siswaInfo->user_id)) {
                        NotificationHelper::send(
                            (int)$siswaInfo->user_id,
                            "🎉 Pulang Cepat Disetujui",
                            "Pengajuan pulang cepat Anda telah disetujui. Jam kepulangan tercatat: " . date('H:i', strtotime($lockedPengajuan->jam_pengajuan)) . " WIB.",
                            "pulang_cepat_approved",
                            "/absensi/siswa"
                        );
                    }
                    if (!empty($siswaInfo->telegram_chat_id) && !empty($siswaInfo->telegram_bot_token)) {
                        $msg = "✅ <b>Pengajuan Pulang Cepat Disetujui</b>\n\n"
                             . "Pengajuan pulang cepat Anda telah disetujui.\n"
                             . "Jam Kepulangan: " . date('H:i', strtotime($lockedPengajuan->jam_pengajuan));
                        require_once __DIR__ . '/../core/TelegramHelper.php';
                        TelegramHelper::sendMessage($siswaInfo->telegram_chat_id, $msg, null, $siswaInfo->telegram_bot_token);
                    }
                }
            } catch (Exception $e) {}

            echo json_encode(['ok' => true, 'message' => 'Pengajuan disetujui']);
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $db->execute();
            echo json_encode(['ok' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function reject($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $role = SessionHelper::get('role');
        $userId = (int)SessionHelper::get('user_id');
        
        if (!in_array($role, ['admin', 'wali_kelas'])) {
            echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $reason = $input['reason'] ?? '';

        $db = new Database();
        
        try {
            $db->query("START TRANSACTION");
            $db->execute();

            $db->query("SELECT * FROM pengajuan_pulang_cepat WHERE id = :id FOR UPDATE");
            $db->bind(':id', $id);
            $lockedPengajuan = $db->single();

            if (!$lockedPengajuan || $lockedPengajuan->status !== 'PENDING') {
                $db->query("ROLLBACK");
                $db->execute();
                echo json_encode(['ok' => false, 'message' => 'Status sudah diproses atau data tidak ditemukan']);
                exit;
            }

            if ($role === 'wali_kelas' && $lockedPengajuan->wali_kelas_id != $userId) {
                $db->query("ROLLBACK");
                $db->execute();
                echo json_encode(['ok' => false, 'message' => 'Anda tidak memiliki hak untuk menolak pengajuan ini']);
                exit;
            }

            $db->query("UPDATE pengajuan_pulang_cepat SET status = 'REJECTED', rejected_by = :rejected_by, rejected_at = NOW(), rejected_reason = :reason WHERE id = :id");
            $db->bind(':id', $id);
            $db->bind(':rejected_by', $userId);
            $db->bind(':reason', $reason);
            $db->execute();

            $db->query("COMMIT");
            $db->execute();

            // Send telegram to student if available
            try {
                $db->query("SELECT u.id as user_id, u.telegram_chat_id, k.telegram_bot_token 
                            FROM users u 
                            JOIN siswa s ON u.id = s.user_id 
                            JOIN kelas k ON s.kelas_id = k.id 
                            WHERE s.id = :siswa_id");
                $db->bind(':siswa_id', $lockedPengajuan->siswa_id);
                $siswaInfo = $db->single();

                if ($siswaInfo) {
                    if (!empty($siswaInfo->user_id)) {
                        NotificationHelper::send(
                            (int)$siswaInfo->user_id,
                            "❌ Pulang Cepat Ditolak",
                            "Pengajuan pulang cepat Anda ditolak" . ($reason ? ": " . $reason : ".") . " Silakan lanjutkan kegiatan belajar di sekolah.",
                            "pulang_cepat_rejected",
                            "/absensi/siswa"
                        );
                    }
                    if (!empty($siswaInfo->telegram_chat_id) && !empty($siswaInfo->telegram_bot_token)) {
                        $msg = "❌ <b>Pengajuan Pulang Cepat Ditolak</b>\n\n"
                             . "Alasan Penolakan: " . htmlspecialchars($reason) . "\n\n"
                             . "Silakan lakukan absensi pulang normal saat jam sekolah berakhir.";
                        require_once __DIR__ . '/../core/TelegramHelper.php';
                        TelegramHelper::sendMessage($siswaInfo->telegram_chat_id, $msg, null, $siswaInfo->telegram_bot_token);
                    }
                }
            } catch (Exception $e) {}

            echo json_encode(['ok' => true, 'message' => 'Pengajuan ditolak']);
        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $db->execute();
            echo json_encode(['ok' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    public function detail($id) {
        AuthHelper::checkRole(['admin', 'wali_kelas']);

        $userId = (int)SessionHelper::get('user_id');
        $role   = SessionHelper::get('role');
        $pengajuan = $this->pengajuanModel->getById($id);

        if (!$pengajuan) {
            Flasher::setFlash('gagal', 'Data tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/izin/approval');
            exit;
        }

        if ($role === 'wali_kelas' && $pengajuan->wali_kelas_id != $userId) {
            Flasher::setFlash('gagal', 'Unauthorized.', 'danger');
            header('Location: ' . BASEURL . '/izin/approval');
            exit;
        }

        $data = [
            'judul' => 'Detail Pulang Cepat',
            'pengajuan' => $pengajuan
        ];

        $this->view('templates/header', $data);
        $this->view('pulang_cepat/detail', $data);
        $this->view('templates/footer');
    }

    public function action() {
        AuthHelper::checkRole(['admin', 'wali_kelas']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/izin/approval');
            exit;
        }

        $role   = SessionHelper::get('role');
        $userId = (int)SessionHelper::get('user_id');

        $id = (int)$_POST['id'];
        $status = $_POST['status'] ?? ''; // 'APPROVED' or 'REJECTED'
        $catatan = $_POST['catatan'] ?? '';

        if (!in_array($status, ['APPROVED', 'REJECTED'])) {
            Flasher::setFlash('gagal', 'Status tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/pulang_cepat/detail/' . $id);
            exit;
        }

        if ($status === 'REJECTED' && trim($catatan) === '') {
            Flasher::setFlash('gagal', 'Catatan wajib diisi jika ditolak.', 'danger');
            header('Location: ' . BASEURL . '/pulang_cepat/detail/' . $id);
            exit;
        }

        $db = new Database();
        
        try {
            $db->query("START TRANSACTION");
            $db->execute();

            $db->query("SELECT * FROM pengajuan_pulang_cepat WHERE id = :id FOR UPDATE");
            $db->bind(':id', $id);
            $lockedPengajuan = $db->single();

            if (!$lockedPengajuan || $lockedPengajuan->status !== 'PENDING') {
                $db->query("ROLLBACK");
                $db->execute();
                Flasher::setFlash('gagal', 'Status sudah diproses atau data tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/izin/approval');
                exit;
            }

            if ($role === 'wali_kelas' && $lockedPengajuan->wali_kelas_id != $userId) {
                $db->query("ROLLBACK");
                $db->execute();
                Flasher::setFlash('gagal', 'Unauthorized.', 'danger');
                header('Location: ' . BASEURL . '/izin/approval');
                exit;
            }

            if ($status === 'APPROVED') {
                $db->query("UPDATE pengajuan_pulang_cepat SET status = 'APPROVED', approved_by = :user_id, approved_at = NOW() WHERE id = :id");
                $db->bind(':id', $id);
                $db->bind(':user_id', $userId);
                $db->execute();

                // Update absensi_siswa sesuai alasan yang diajukan (sakit / izin)
                $alasanText = trim($lockedPengajuan->alasan ?? '');
                $alasanUpper = strtoupper($alasanText);
                if (strpos($alasanUpper, 'SAKIT') !== false) {
                    $targetStatus = 'sakit';
                } elseif (strpos($alasanUpper, 'IZIN') !== false) {
                    $targetStatus = 'izin';
                } else {
                    $targetStatus = 'izin';
                }

                $jamPulangFmt = !empty($lockedPengajuan->jam_pengajuan) ? substr($lockedPengajuan->jam_pengajuan, 0, 5) : '';
                $ketText = "Pulang Cepat (" . ($alasanText ?: 'Disetujui') . ")";
                if ($jamPulangFmt) {
                    $ketText .= " - Jam {$jamPulangFmt}";
                }

                $db->query("UPDATE absensi_siswa SET jam_pulang = :jam, status = :status, keterangan = :keterangan WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
                $db->bind(':jam', $lockedPengajuan->jam_pengajuan);
                $db->bind(':status', $targetStatus);
                $db->bind(':keterangan', $ketText);
                $db->bind(':siswa_id', $lockedPengajuan->siswa_id);
                $db->bind(':tanggal', $lockedPengajuan->tanggal);
                $db->execute();

            } else {
                $db->query("UPDATE pengajuan_pulang_cepat SET status = 'REJECTED', rejected_by = :user_id, rejected_at = NOW(), rejected_reason = :reason WHERE id = :id");
                $db->bind(':id', $id);
                $db->bind(':user_id', $userId);
                $db->bind(':reason', $catatan);
                $db->execute();
            }

            $db->query("COMMIT");
            $db->execute();

            // Send telegram
            try {
                $db->query("SELECT u.id as user_id, u.telegram_chat_id, k.telegram_bot_token 
                            FROM users u 
                            JOIN siswa s ON u.id = s.user_id 
                            JOIN kelas k ON s.kelas_id = k.id 
                            WHERE s.id = :siswa_id");
                $db->bind(':siswa_id', $lockedPengajuan->siswa_id);
                $siswaInfo = $db->single();

                if ($siswaInfo) {
                    if (!empty($siswaInfo->user_id)) {
                        if ($status === 'APPROVED') {
                            NotificationHelper::send(
                                (int)$siswaInfo->user_id,
                                "🎉 Pulang Cepat Disetujui",
                                "Pengajuan pulang cepat Anda telah disetujui. Jam kepulangan tercatat: " . date('H:i', strtotime($lockedPengajuan->jam_pengajuan)) . " WIB.",
                                "pulang_cepat_approved",
                                "/absensi/siswa"
                            );
                        } else {
                            NotificationHelper::send(
                                (int)$siswaInfo->user_id,
                                "❌ Pulang Cepat Ditolak",
                                "Pengajuan pulang cepat Anda ditolak" . ($catatan ? ": " . $catatan : ".") . " Silakan lanjutkan belajar di sekolah.",
                                "pulang_cepat_rejected",
                                "/absensi/siswa"
                            );
                        }
                    }
                    if (!empty($siswaInfo->telegram_chat_id) && !empty($siswaInfo->telegram_bot_token)) {
                        if ($status === 'APPROVED') {
                            $msg = "✅ <b>Pengajuan Pulang Cepat Disetujui</b>\n\nJam Kepulangan: " . date('H:i', strtotime($lockedPengajuan->jam_pengajuan));
                        } else {
                            $msg = "❌ <b>Pengajuan Pulang Cepat Ditolak</b>\n\nAlasan Penolakan: " . htmlspecialchars($catatan);
                        }
                        require_once __DIR__ . '/../core/TelegramHelper.php';
                        TelegramHelper::sendMessage($siswaInfo->telegram_chat_id, $msg, null, $siswaInfo->telegram_bot_token);
                    }
                }
            } catch (Exception $e) {}

            Flasher::setFlash('berhasil', 'Pengajuan berhasil ' . ($status === 'APPROVED' ? 'disetujui' : 'ditolak') . '.', 'success');
            header('Location: ' . BASEURL . '/izin/approval');
            exit;

        } catch (Exception $e) {
            $db->query("ROLLBACK");
            $db->execute();
            Flasher::setFlash('gagal', 'Terjadi kesalahan sistem: ' . $e->getMessage(), 'danger');
            header('Location: ' . BASEURL . '/pulang_cepat/detail/' . $id);
            exit;
        }
    }
}
