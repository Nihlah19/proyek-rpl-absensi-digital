<?php
class RiwayatController extends Controller {

    // Daftar method yang boleh diakses tanpa login (endpoint publik untuk monitor)
    private $publicMethods = ['get_latest_absensi', 'get_latest_absensi_guru'];

    public function __construct() {
        // Halaman Live Monitor bersifat publik dan tidak memerlukan login
    }

    public function index() {
        $data['judul'] = 'Live Monitor Absensi';
        $this->view('riwayat/index', $data);
    }

    public function get_latest_absensi() {
        $db = new Database();
        
        $today = date('Y-m-d');
        $db->query("
            SELECT ab.id, u.name, u.avatar, k.nama_kelas, ab.jam_datang, ab.jam_pulang, ab.status, ab.tanggal
            FROM absensi_siswa ab
            JOIN siswa s ON ab.siswa_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE ab.tanggal = :today
              AND (ab.jam_datang IS NOT NULL OR ab.jam_pulang IS NOT NULL)
            ORDER BY COALESCE(ab.jam_pulang, ab.jam_datang) DESC, ab.id DESC
            LIMIT 100
        ");
        $db->bind(':today', $today);
        $result = $db->resultSet();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }

    public function guru() {
        $data['judul'] = 'Live Monitor Absensi Guru';
        // Generate daily token untuk otentikasi kiosk monitor sekolah
        $salt = defined('DB_NAME') ? DB_NAME : 'absensi_rfid';
        $monitorToken = hash_hmac('sha256', 'kiosk_guru_' . date('Y-m-d'), $salt);
        $_SESSION['monitor_token'] = $monitorToken;
        $data['monitor_token'] = $monitorToken;

        $this->view('riwayat/guru', $data);
    }

    public function get_latest_absensi_guru() {
        $db = new Database();
        
        $today = date('Y-m-d');
        $db->query("
            SELECT ag.id, u.name, u.avatar, g.nip, ag.jam_datang, ag.jam_pulang, ag.status, ag.tanggal
            FROM absensi_guru ag
            JOIN guru g ON ag.guru_id = g.id
            JOIN users u ON g.user_id = u.id
            WHERE ag.tanggal = :today
              AND (ag.jam_datang IS NOT NULL OR ag.jam_pulang IS NOT NULL)
            ORDER BY COALESCE(ag.jam_pulang, ag.jam_datang) DESC, ag.id DESC
            LIMIT 100
        ");
        $db->bind(':today', $today);
        $result = $db->resultSet();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }
}
