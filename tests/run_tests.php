<?php
// ===========================================================
// AUTOMATED TEST RUNNER - Absensi RFID & PKL Module
// ===========================================================

echo "======================================================\n";
echo "  SUITE PENGUJIAN OTOMATIS (AUTOMATED UNIT TESTS)     \n";
echo "  Sistem Absensi RFID & Presensi Mandiri PKL          \n";
echo "======================================================\n\n";

$passedTests = 0;
$failedTests = 0;
$totalTests  = 0;

function assertTest($description, $condition) {
    global $passedTests, $failedTests, $totalTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] Test #{$totalTests}: {$description}\n";
    } else {
        $failedTests++;
        echo " [FAIL] Test #{$totalTests}: {$description}\n";
    }
}

// -----------------------------------------------------------
// TEST SUITE 1: Formula Geofencing Haversine (PKL)
// -----------------------------------------------------------
echo "--- 1. Testing Modul Geofencing & Jarak Haversine PKL ---\n";

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Radius bumi dalam meter
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

// 1.1 Koordinat Identik (Jarak harus 0 meter)
$distIdentical = calculateDistance(-6.668725, 108.384210, -6.668725, 108.384210);
assertTest("Jarak titik identik adalah 0 meter", round($distIdentical, 1) === 0.0);

// 1.2 Siswa dalam radius (< 50 meter)
$distInside = calculateDistance(-6.668725, 108.384210, -6.668480, 108.384050); // ~32 meter
assertTest("Validasi presensi DITERIMA jika jarak (" . round($distInside, 1) . " m) <= radius 50m", $distInside <= 50.0);

// 1.3 Siswa di luar radius (> 50 meter)
$distOutside = calculateDistance(-6.668725, 108.384210, -6.671000, 108.384210); // ~252 meter
assertTest("Validasi presensi DITOLAK jika jarak (" . round($distOutside, 1) . " m) > radius 50m", $distOutside > 50.0);


// -----------------------------------------------------------
// TEST SUITE 2: Jadwal & Hari Kerja Dinamis PKL
// -----------------------------------------------------------
echo "\n--- 2. Testing Pengaturan Hari Kerja Dinamis PKL ---\n";

function isHariKerja($hari, $tipeHariKerja = '5_hari') {
    $hari = strtolower($hari);
    if ($hari === 'minggu') return false;
    if ($tipeHariKerja === '5_hari') {
        return in_array($hari, ['senin', 'selasa', 'rabu', 'kamis', 'jumat']);
    } elseif ($tipeHariKerja === '6_hari') {
        return in_array($hari, ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
    }
    return false;
}

assertTest("Hari Senin terdaftar aktif untuk instansi 5 hari kerja", isHariKerja('Senin', '5_hari') === true);
assertTest("Hari Sabtu berstatus LIBUR untuk instansi 5 hari kerja (Bebas Alfa)", isHariKerja('Sabtu', '5_hari') === false);
assertTest("Hari Minggu berstatus LIBUR otomatis", isHariKerja('Minggu', '6_hari') === false);
assertTest("Hari Sabtu terdaftar aktif untuk bengkel DUDI 6 hari kerja", isHariKerja('Sabtu', '6_hari') === true);


// -----------------------------------------------------------
// TEST SUITE 3: Chatbot AI Sanitasi & Anti-Manipulasi RBAC
// -----------------------------------------------------------
echo "\n--- 3. Testing Filter Keamanan & Boundary Asisten AI ---\n";

function detectManipulation($message) {
    $patterns = [
        '/\b(ubah|edit|ganti|hapus|delete|update|set|bikin|manipulasi)\s+(status|absen|kehadiran|alfa|hadir|izin|sakit)\b/i',
        '/\b(jadikan|ubah)\s+(saya|aku|dia|siswa)\s+(hadir|masuk|tepat waktu)\b/i',
        '/\b(tolong|bisa)\s+(hadirkan|absenkan|tandai)\b/i',
        '/\b(hapus|hilangkan)\s+(alfa|terlambat|telat)\b/i',
        '/\b(bypass|hack|drop|truncate|insert|select|union|where)\b/i'
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $message)) {
            return true;
        }
    }
    return false;
}

assertTest("AI memblokir injeksi manipulasi data: 'Tolong ubah absen saya jadi hadir'", detectManipulation('Tolong ubah absen saya jadi hadir') === true);
assertTest("AI memblokir injeksi SQL: 'DROP TABLE absensi'", detectManipulation('DROP TABLE absensi') === true);
assertTest("AI mengizinkan pertanyaan informasi sah: 'Bagaimana rekap kehadiran saya bulan ini?'", detectManipulation('Bagaimana rekap kehadiran saya bulan ini?') === false);


// -----------------------------------------------------------
// TEST SUITE 4: Perhitungan Persentase Tingkat Kehadiran
// -----------------------------------------------------------
echo "\n--- 4. Testing Perhitungan Metrik Tingkat Kehadiran ---\n";

function hitungTingkatKehadiran($hadir, $terlambat, $pkl, $total) {
    if ($total <= 0) return 0.0;
    $giat = $hadir + $terlambat + $pkl;
    return round(($giat / $total) * 100, 1);
}

assertTest("Tingkat kehadiran 0 dari 27 guru menghasilkan 0.0%", hitungTingkatKehadiran(0, 0, 0, 27) === 0.0);
assertTest("Tingkat kehadiran 27 dari 27 guru menghasilkan 100.0%", hitungTingkatKehadiran(25, 2, 0, 27) === 100.0);
assertTest("Tingkat kehadiran 30 dari 36 siswa (termasuk PKL) terhitung akurat (83.3%)", hitungTingkatKehadiran(20, 5, 5, 36) === 83.3);


// -----------------------------------------------------------
// TEST SUITE 5: Chatbot AI Respon Simpel & Akses Data Pribadi
// -----------------------------------------------------------
echo "\n--- 5. Testing Chatbot AI Respon Simpel & Data Pribadi Pengguna ---\n";

require_once __DIR__ . '/../web/app/config/config.php';
require_once __DIR__ . '/../web/app/core/Controller.php';
require_once __DIR__ . '/../web/app/core/Database.php';
require_once __DIR__ . '/../web/app/core/Model.php';
require_once __DIR__ . '/../web/app/models/AppSetting.php';
require_once __DIR__ . '/../web/app/controllers/ChatbotController.php';

class TestChatbotRunner extends ChatbotController {
    public function query($message, $userId, $userRole) {
        $reflection = new ReflectionClass('ChatbotController');
        $method = $reflection->getMethod('handleLocalDatabaseQuery');
        $method->setAccessible(true);
        return $method->invoke($this, $message, $userId, $userRole);
    }
}

$botTest = new TestChatbotRunner();

// 5.1 Test hitung jumlah guru singkat & simpel
$ansGuru = $botTest->query('ada berapa jumlah guru', 28, 'guru');
assertTest("Chatbot menjawab simpel jumlah guru (27 guru): '{$ansGuru}'", strpos($ansGuru, '27 guru') !== false);

// 5.2 Test hitung jumlah siswa singkat & simpel
$ansSiswa = $botTest->query('berapa jumlah siswa', 28, 'guru');
assertTest("Chatbot menjawab simpel jumlah siswa (255 siswa): '{$ansSiswa}'", strpos($ansSiswa, '255 siswa') !== false);

// 5.3 Test identitas Andri Rochman (Guru TKJ)
$ansWhoAndri = $botTest->query('siapa saya', 28, 'guru');
assertTest("Chatbot mengenali identitas Andri Rochman, SKom (Guru TKJ): '{$ansWhoAndri}'", strpos($ansWhoAndri, 'Andri Rochman') !== false && strpos($ansWhoAndri, 'TKJ') !== false);

// 5.4 Test jadwal mengajar Andri
$ansJadwalAndri = $botTest->query('jadwal saya', 28, 'guru');
assertTest("Chatbot menampilkan jadwal mengajar aktif Andri (Selasa & Kamis)", strpos($ansJadwalAndri, 'Selasa') !== false && strpos($ansJadwalAndri, 'Kamis') !== false);

// 5.5 Test identitas Siswa (Amelia Zahra)
$ansWhoSiswa = $botTest->query('siapa saya', 290, 'siswa');
assertTest("Chatbot mengenali identitas siswa (AMELIA ZAHRA, 11 TKJ): '{$ansWhoSiswa}'", strpos($ansWhoSiswa, 'AMELIA ZAHRA') !== false && strpos($ansWhoSiswa, '11 TKJ') !== false);

// 5.6 Test izin siswa
$ansIzinSiswa = $botTest->query('izin saya', 290, 'siswa');
assertTest("Chatbot menampilkan status riwayat permohonan izin siswa", strpos($ansIzinSiswa, 'AMELIA ZAHRA') !== false);


// -----------------------------------------------------------
// RINGKASAN HASIL PENGUJIAN
// -----------------------------------------------------------
echo "\n======================================================\n";
echo "  HASIL PENGUJIAN OTOMATIS                            \n";
echo "======================================================\n";
echo "Total Uji Kasus : {$totalTests}\n";
echo "Lolos (PASS)    : {$passedTests}\n";
echo "Gagal (FAIL)    : {$failedTests}\n";

if ($failedTests === 0) {
    echo "\n>>> STATUS: SEMUA UNIT TEST PASSING DENGAN SUKSES (HIJAU) <<<\n\n";
    exit(0);
} else {
    echo "\n>>> STATUS: PENGUJIAN GAGAL <<<\n\n";
    exit(1);
}
