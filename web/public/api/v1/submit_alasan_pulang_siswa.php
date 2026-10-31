<?php
require_once __DIR__ . '/../../../app/init.php';

header('Content-Type: application/json');

// Hanya izinkan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

// Cek autentikasi dan role siswa
$userId = (int)SessionHelper::get('user_id');
if (!$userId || SessionHelper::get('role') !== 'siswa') {
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['absensi_id']) || !isset($input['alasan_kategori'])) {
    echo json_encode(['ok' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$pengajuan_id    = (int)$input['absensi_id']; // ini sebenarnya pengajuan_pulang_cepat.id
$alasan_kategori = trim($input['alasan_kategori']);
$alasan_detail   = trim($input['alasan_detail'] ?? '');

// Validasi kategori
$allowed = ['izin', 'sakit', 'lainnya'];
if (!in_array($alasan_kategori, $allowed)) {
    echo json_encode(['ok' => false, 'message' => 'Kategori alasan tidak valid']);
    exit;
}

if ($alasan_kategori === 'lainnya' && $alasan_detail === '') {
    echo json_encode(['ok' => false, 'message' => 'Keterangan tambahan wajib diisi jika memilih Lainnya']);
    exit;
}

$alasanFinal = strtoupper($alasan_kategori) . ($alasan_detail !== '' ? ': ' . $alasan_detail : '');

// Ambil data pengajuan + info siswa + telegram wali kelas dari DB langsung
$db = new Database();
$db->query("
    SELECT 
        p.id, p.siswa_id, p.wali_kelas_id, p.status,
        p.jam_pengajuan, p.tanggal,
        u.id as user_id,
        u.name as nama_siswa,
        kls.nama_kelas,
        kls.telegram_chat_id as wali_chat_id,
        kls.telegram_bot_token
    FROM pengajuan_pulang_cepat p
    JOIN siswa s   ON p.siswa_id      = s.id
    JOIN users u   ON s.user_id       = u.id
    JOIN kelas kls ON s.kelas_id      = kls.id
    LEFT JOIN users w ON p.wali_kelas_id = w.id
    WHERE p.id = :id
");
$db->bind(':id', $pengajuan_id);
$pengajuan = $db->single();

if (!$pengajuan) {
    echo json_encode(['ok' => false, 'message' => 'Data pengajuan tidak ditemukan.']);
    exit;
}

if ($pengajuan->status !== 'DRAFT') {
    echo json_encode(['ok' => false, 'message' => 'Pengajuan sudah pernah disubmit atau tidak bisa diubah.']);
    exit;
}

// Pastikan siswa ini memang pemilik pengajuan
if ((int)$pengajuan->user_id !== $userId) {
    echo json_encode(['ok' => false, 'message' => 'Akses tidak diizinkan.']);
    exit;
}

// Update status ke PENDING
$db->query("UPDATE pengajuan_pulang_cepat 
            SET status = 'PENDING', alasan = :alasan 
            WHERE id = :id AND status = 'DRAFT'");
$db->bind(':alasan', $alasanFinal);
$db->bind(':id', $pengajuan_id);
$db->execute();
$affected = $db->rowCount();

if ($affected > 0) {
    // Kirim notifikasi Telegram ke wali kelas
    try {
        if (!empty($pengajuan->wali_chat_id) && !empty($pengajuan->telegram_bot_token)) {
            $jamTap = date('H:i', strtotime($pengajuan->jam_pengajuan));
            $msg = "<b>🚨 Permohonan Pulang Cepat</b>\n\n"
                 . "<b>Nama:</b> {$pengajuan->nama_siswa}\n"
                 . "<b>Kelas:</b> {$pengajuan->nama_kelas}\n"
                 . "<b>Jam Tap:</b> {$jamTap}\n"
                 . "<b>Kategori:</b> " . strtoupper($alasan_kategori) . "\n"
                 . ($alasan_detail !== '' ? "<b>Keterangan:</b> {$alasan_detail}\n" : "")
                 . "\nSilakan buka sistem untuk memproses permohonan ini.";

            TelegramHelper::sendMessage(
                $pengajuan->wali_chat_id,
                $msg,
                null,
                $pengajuan->telegram_bot_token
            );
        }
    } catch (Exception $e) {
        // Abaikan error Telegram — data tetap tersimpan
    }

    // Kirim notifikasi pop-up & PWA ke wali kelas
    if (!empty($pengajuan->wali_kelas_id)) {
        $jamTap = date('H:i', strtotime($pengajuan->jam_pengajuan));
        $alasanKet = strtoupper($alasan_kategori) . ($alasan_detail !== '' ? ' (' . $alasan_detail . ')' : '');
        NotificationHelper::send(
            (int)$pengajuan->wali_kelas_id,
            "⚠️ Pengajuan Pulang Cepat",
            "Siswa {$pengajuan->nama_siswa} ({$pengajuan->nama_kelas}) mengajukan pulang cepat pukul {$jamTap}. Alasan: {$alasanKet}.",
            "pulang_cepat_pengajuan",
            "/izin/approval"
        );
    }

    echo json_encode(['ok' => true, 'message' => 'Alasan berhasil dikirim. Menunggu persetujuan wali kelas.']);
} else {
    echo json_encode(['ok' => false, 'message' => 'Gagal menyimpan data. Mungkin sudah pernah disubmit.']);
}
