<?php
require_once __DIR__ . '/../../../app/init.php';

header('Content-Type: application/json');

// Pastikan user login
$userId = SessionHelper::get('user_id');
$role   = SessionHelper::get('role');

if (!$userId || !in_array($role, ['admin', 'wali_kelas', 'walikelas'])) {
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = new Database();
$today = date('Y-m-d');

// 1. Cek notifikasi Pulang Cepat baru (status PENDING)
$notifQuery = "
    SELECT p.id, p.jam_pengajuan, u.name as nama_siswa
    FROM pengajuan_pulang_cepat p
    JOIN siswa s ON p.siswa_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE p.status = 'PENDING' AND p.tanggal = :today
";

if ($role === 'wali_kelas' || $role === 'walikelas') {
    $notifQuery .= " AND p.wali_kelas_id = :userId";
}

$db->query($notifQuery);
$db->bind(':today', $today);
if ($role === 'wali_kelas' || $role === 'walikelas') {
    $db->bind(':userId', $userId);
}
$newRequests = $db->resultSet();


// 2. Buat "hash" aktivitas hari ini untuk mendeteksi perubahan
// Kita ambil total absensi hari ini + total pengajuan
$db->query("
    SELECT 
        (SELECT COUNT(*) FROM absensi_siswa WHERE tanggal = :t1) +
        (SELECT COUNT(*) FROM pengajuan_pulang_cepat WHERE tanggal = :t2) +
        (SELECT COUNT(*) FROM pengajuan_izin WHERE (:t3 BETWEEN tanggal_mulai AND tanggal_selesai)) 
    AS total_activity
");
$db->bind(':t1', $today);
$db->bind(':t2', $today);
$db->bind(':t3', $today);
$activity = $db->single();

$hash = md5($activity->total_activity ?? 0);

echo json_encode([
    'ok' => true,
    'new_requests' => $newRequests,
    'hash' => $hash
]);
