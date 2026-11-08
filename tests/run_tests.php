<?php
/**
 * Test Suite Mandiri - Proyek Akhir Rekayasa Perangkat Lunak
 * Sistem Presensi Terpadu IoT RFID & Modul Khusus PKL Berbasis AI
 * SMK NU Wargabinangun Cirebon
 */

echo "======================================================\n";
echo "  SUITE PENGUJIAN OTOMATIS (AUTOMATED UNIT TESTS)     \n";
echo "  Sistem Absensi RFID & Presensi Mandiri PKL          \n";
echo "======================================================\n\n";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assertTest($description, $condition) {
    global $totalTests, $passedTests, $failedTests;
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
// TEST SUITE 1: Formula Haversine & Validasi Geofencing PKL
// -----------------------------------------------------------
echo "--- 1. Testing Modul Geofencing & Jarak Haversine PKL ---\n";

function haversineDistance($lat1, $lng1, $lat2, $lng2) {
    $earthRadius = 6371000; // meter
    $dLat = deg2rad((float)$lat2 - (float)$lat1);
    $dLng = deg2rad((float)$lng2 - (float)$lng1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
         sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earthRadius * $c, 1);
}

// Titik A: Gerbang SMK NU Wargabinangun (-6.6872, 108.4121)
// Titik B: Posisi siswa sama persis
$d0 = haversineDistance(-6.6872, 108.4121, -6.6872, 108.4121);
assertTest("Jarak titik identik adalah 0 meter", $d0 === 0.0);

// Titik C: Posisi siswa berjarak ~35 meter (dalam radius toleransi 50m)
$d35 = haversineDistance(-6.68720, 108.41210, -6.68745, 108.41225);
assertTest("Validasi presensi DITERIMA jika jarak ($d35 m) <= radius 50m", $d35 <= 50.0);

// Titik D: Posisi siswa di luar jangkauan radius (>50m, misal 250m)
$d250 = haversineDistance(-6.68720, 108.41210, -6.68900, 108.41350);
assertTest("Validasi presensi DITOLAK jika jarak ($d250 m) > radius 50m", $d250 > 50.0);


// -----------------------------------------------------------
// TEST SUITE 2: Validasi Hari Kerja Dinamis PKL (Wali Kelas)
// -----------------------------------------------------------
echo "\n--- 2. Testing Pengaturan Hari Kerja Dinamis PKL ---\n";

function isHariKerjaAktif($hariIni, $daftarHariKerja) {
    // Array hari kerja yang dicentang wali kelas, misal: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']
    return in_array($hariIni, $daftarHariKerja, true);
}

$hariKerja5Hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
assertTest("Hari Senin terdaftar aktif untuk instansi 5 hari kerja", isHariKerjaAktif('Senin', $hariKerja5Hari) === true);
assertTest("Hari Sabtu berstatus LIBUR untuk instansi 5 hari kerja (Bebas Alfa)", isHariKerjaAktif('Sabtu', $hariKerja5Hari) === false);
assertTest("Hari Minggu berstatus LIBUR otomatis", isHariKerjaAktif('Minggu', $hariKerja5Hari) === false);

$hariKerja6Hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
assertTest("Hari Sabtu terdaftar aktif untuk bengkel DUDI 6 hari kerja", isHariKerjaAktif('Sabtu', $hariKerja6Hari) === true);


// -----------------------------------------------------------
// TEST SUITE 3: Keamanan AI Chatbot (Anti Prompt Injection & Manipulation)
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
