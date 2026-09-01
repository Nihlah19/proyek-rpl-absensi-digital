<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../app/init.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->rfid_uid)) {
    $rfid_uid = htmlspecialchars(strip_tags($data->rfid_uid));
    
    $db = new Database();
    
    // Cari siswa dan kelasnya
    $db->query("SELECT s.id as siswa_id, s.nis, u.name, k.nama_kelas, k.telegram_chat_id 
                FROM siswa s 
                JOIN users u ON s.user_id = u.id 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                WHERE s.rfid_uid = :rfid_uid");
    $db->bind(':rfid_uid', $rfid_uid);
    $siswa = $db->single();
    
    if (!$siswa) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Kartu RFID tidak terdaftar.']);
        exit;
    }
    
    // Check Jadwal Hari Ini untuk siswa
    $hari_ini = date('l');
    $hari_indo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $hari_ini_indo = $hari_indo[$hari_ini];
    
    $db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1");
    $db->bind(':hari', $hari_ini_indo);
    $jadwal = $db->single();
    
    if (!$jadwal) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada jadwal sekolah hari ini.']);
        exit;
    }
    
    // Check Absensi
    $tanggal = date('Y-m-d');
    $waktu_sekarang = date('H:i:s');
    
    $db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
    $db->bind(':siswa_id', $siswa->siswa_id);
    $db->bind(':tanggal', $tanggal);
    $absensi = $db->single();
    
    if (!$absensi) {
        // Absen Masuk
        $status = ($waktu_sekarang > $jadwal->batas_terlambat) ? 'terlambat' : 'hadir';
        
        $db->query("INSERT INTO absensi_siswa (siswa_id, tanggal, jam_datang, status) VALUES (:siswa_id, :tanggal, :jam_datang, :status)");
        $db->bind(':siswa_id', $siswa->siswa_id);
        $db->bind(':tanggal', $tanggal);
        $db->bind(':jam_datang', $waktu_sekarang);
        $db->bind(':status', $status);
        $db->execute();
        
        // Kirim Telegram Notifikasi
        if (!empty($siswa->telegram_chat_id)) {
            $pesan = "📚 *ABSENSI SISWA*\n\n" .
                     "Nama: " . $siswa->name . "\n" .
                     "NIS: " . $siswa->nis . "\n" .
                     "Kelas: " . $siswa->nama_kelas . "\n" .
                     "Jam: " . $waktu_sekarang . "\n" .
                     "Status: *" . strtoupper($status) . "*";
            TelegramHelper::sendMessage($siswa->telegram_chat_id, $pesan);
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Berhasil absen masuk: ' . $siswa->name]);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Siswa sudah absen hari ini.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data RFID tidak valid.']);
}
