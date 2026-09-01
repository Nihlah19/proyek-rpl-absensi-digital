<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../app/init.php';

// Auth check - should be logged in as guru
if (!SessionHelper::get('user_id') || SessionHelper::get('role') !== 'guru') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->barcode_id)) {
    $barcode_id = htmlspecialchars(strip_tags($data->barcode_id));
    $user_id = SessionHelper::get('user_id');

    $db = new Database();
    
    // Validate Guru
    $db->query("SELECT id, barcode_id FROM guru WHERE user_id = :user_id");
    $db->bind(':user_id', $user_id);
    $guru = $db->single();
    
    if (!$guru || $guru->barcode_id !== $barcode_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'QR Code tidak cocok dengan akun Anda.']);
        exit;
    }
    
    // Check Jadwal Hari Ini
    $hari_ini = date('l');
    $hari_indo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $hari_ini_indo = $hari_indo[$hari_ini];
    
    $db->query("SELECT * FROM jadwal_guru WHERE guru_id = :guru_id AND hari = :hari AND is_active = 1");
    $db->bind(':guru_id', $guru->id);
    $db->bind(':hari', $hari_ini_indo);
    $jadwal = $db->single();
    
    if (!$jadwal) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki jadwal hari ini.']);
        exit;
    }
    
    // Check Absensi
    $tanggal = date('Y-m-d');
    $waktu_sekarang = date('H:i:s');
    
    $db->query("SELECT * FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = :tanggal");
    $db->bind(':guru_id', $guru->id);
    $db->bind(':tanggal', $tanggal);
    $absensi = $db->single();
    
    if (!$absensi) {
        // Absen Masuk
        $status = ($waktu_sekarang > $jadwal->batas_terlambat) ? 'terlambat' : 'hadir';
        
        $db->query("INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, status) VALUES (:guru_id, :tanggal, :jam_datang, :status)");
        $db->bind(':guru_id', $guru->id);
        $db->bind(':tanggal', $tanggal);
        $db->bind(':jam_datang', $waktu_sekarang);
        $db->bind(':status', $status);
        $db->execute();
        
        echo json_encode(['status' => 'success', 'message' => 'Absen Masuk Berhasil (' . $status . '). Waktu: ' . $waktu_sekarang]);
    } else {
        if ($absensi->jam_pulang == null) {
            // Absen Pulang
            $db->query("UPDATE absensi_guru SET jam_pulang = :jam_pulang WHERE id = :id");
            $db->bind(':jam_pulang', $waktu_sekarang);
            $db->bind(':id', $absensi->id);
            $db->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Absen Pulang Berhasil. Waktu: ' . $waktu_sekarang]);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Anda sudah melakukan absen masuk dan pulang hari ini.']);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
}
