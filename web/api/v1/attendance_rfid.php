<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../app/init.php';

// Expect JSON payload: {"device_code":"RFID-001","rfid_uid":"AABBCCDD","timestamp":"2026-08-30 06:52:00"}
$input = json_decode(file_get_contents("php://input"));

if (empty($input->device_code) || empty($input->rfid_uid) || empty($input->timestamp)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payload incomplete.']);
    exit;
}

$device_code = htmlspecialchars(strip_tags($input->device_code));
$rfid_uid    = htmlspecialchars(strip_tags($input->rfid_uid));
$timestamp   = htmlspecialchars(strip_tags($input->timestamp));

// ---------------------------------------------------------------------
// 1. Validate device
// ---------------------------------------------------------------------
$db = new Database();
$db->query("SELECT id, device_name, device_type FROM iot_devices WHERE device_code = :code AND status = 'active'");
$db->bind(':code', $device_code);
$device = $db->single();

if (!$device) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Device tidak terdaftar atau tidak aktif.']);
    exit;
}

// ---------------------------------------------------------------------
// 2. Cari siswa berdasarkan RFID UID
// ---------------------------------------------------------------------
$db->query("SELECT s.id AS siswa_id, s.nis, u.name, k.nama_kelas, k.telegram_chat_id, k.id AS kelas_id
            FROM siswa s 
            JOIN users u ON s.user_id = u.id 
            LEFT JOIN kelas k ON s.kelas_id = k.id 
            WHERE s.rfid_uid = :rfid_uid");
$db->bind(':rfid_uid', $rfid_uid);
$siswa = $db->single();

if (!$siswa) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kartu RFID tidak terdaftar.']);
    exit;
}

// ---------------------------------------------------------------------
// 3. Jadwal hari ini untuk kelas siswa
// ---------------------------------------------------------------------
$hari_indo_map = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];
$hari_ini = date('l', strtotime($timestamp));
$hari_indo = $hari_indo_map[$hari_ini];

$db->query("SELECT * FROM jadwal_siswa WHERE hari = :hari AND status_aktif = 1");
$db->bind(':hari', $hari_indo);
$jadwal = $db->single();

if (!$jadwal) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tidak ada jadwal sekolah hari ini.']);
    exit;
}

// ---------------------------------------------------------------------
// 4. Proses absensi siswa
// ---------------------------------------------------------------------
$tanggal = date('Y-m-d', strtotime($timestamp));
$waktu   = date('H:i:s', strtotime($timestamp));

$db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
$db->bind(':siswa_id', $siswa->siswa_id);
$db->bind(':tanggal', $tanggal);
$absensi = $db->single();

if ($absensi) {
    // Sudah absen hari ini – return informasi sederhana
    echo json_encode(['success' => false, 'message' => 'Siswa sudah absen hari ini.']);
    exit;
}

$status = ($waktu > $jadwal->batas_terlambat) ? 'terlambat' : 'hadir';

$db->query("INSERT INTO absensi_siswa (siswa_id, tanggal, jam_datang, status, source_device_id)
            VALUES (:siswa_id, :tanggal, :jam_datang, :status, :device_id)");
$db->bind(':siswa_id', $siswa->siswa_id);
$db->bind(':tanggal', $tanggal);
$db->bind(':jam_datang', $waktu);
$db->bind(':status', $status);
$db->bind(':device_id', $device->id);
$db->execute();

// ---------------------------------------------------------------------
// 5. Kirim notifikasi Telegram hanya ke wali kelas yang relevan
// ---------------------------------------------------------------------
if (!empty($siswa->telegram_chat_id)) {
    $pesan = "📚 *ABSENSI SISWA*\n\n" .
             "Nama: {$siswa->name}\n" .
             "NIS: {$siswa->nis}\n" .
             "Kelas: {$siswa->nama_kelas}\n" .
             "Jam: {$waktu}\n" .
             "Status: *" . strtoupper($status) . "*";
    TelegramHelper::sendMessage($siswa->telegram_chat_id, $pesan);
}

echo json_encode(['success' => true, 'message' => 'Berhasil absen masuk.', 'data' => [
    'siswa_id' => $siswa->siswa_id,
    'tanggal'  => $tanggal,
    'jam'      => $waktu,
    'status'   => $status
]]);
?>
