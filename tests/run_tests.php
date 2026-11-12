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
// TEST SUITE 6: Aturan Auto-Alfa Reguler & Guru + Fleksibilitas PKL
// -----------------------------------------------------------
echo "\n--- 6. Testing Auto-Alfa Reguler/Guru & Pengecualian/Fleksibilitas PKL ---\n";

require_once __DIR__ . '/../web/app/models/Absensi.php';
require_once __DIR__ . '/../web/app/models/Pkl.php';
require_once __DIR__ . '/../web/app/models/Laporan.php';

$dbTest = new Database();
$absensiTest = new Absensi();
$pklTest = new Pkl();
$laporanTest = new Laporan();

$testDate = '2024-01-08'; // Senin (hari kerja aktif historis terisolasi)
$insertedIzinIds = [];

try {
    // Pastikan bersih sebelum mulai
    $dbTest->query("DELETE FROM absensi_siswa WHERE tanggal = :t");
    $dbTest->bind(':t', $testDate);
    $dbTest->execute();
    $dbTest->query("DELETE FROM absensi_guru WHERE tanggal = :t");
    $dbTest->bind(':t', $testDate);
    $dbTest->execute();

    // Hitung baseline siswa reguler & PKL dari database
    $dbTest->query("SELECT COUNT(*) AS cnt FROM siswa s JOIN kelas k ON k.id = s.kelas_id LEFT JOIN pkl_kelompok_siswa pks ON pks.siswa_id = s.id WHERE (k.status IS NULL OR LOWER(TRIM(k.status)) != 'pkl') AND pks.id IS NULL");
    $expectedReguler = (int)$dbTest->single()->cnt;

    $dbTest->query("SELECT COUNT(DISTINCT g.id) AS cnt FROM jadwal_guru jg JOIN guru g ON g.id = jg.guru_id WHERE LOWER(jg.hari) = 'senin' AND jg.is_active = 1");
    $expectedGuruSenin = (int)$dbTest->single()->cnt;

    // 6.1 Auto-Alfa Siswa Reguler (PKL harus bebas / 0 alfa)
    $absensiTest->generateAlfaSiswaHariIni($testDate, false);
    $dbTest->query("SELECT COUNT(*) AS cnt FROM absensi_siswa a JOIN siswa s ON s.id = a.siswa_id JOIN kelas k ON k.id = s.kelas_id WHERE a.tanggal = :t AND a.status = 'alfa' AND k.status != 'pkl'");
    $dbTest->bind(':t', $testDate);
    $actualRegulerAlfa = (int)$dbTest->single()->cnt;

    $dbTest->query("SELECT COUNT(*) AS cnt FROM absensi_siswa a JOIN siswa s ON s.id = a.siswa_id JOIN kelas k ON k.id = s.kelas_id WHERE a.tanggal = :t AND a.status = 'alfa' AND k.status = 'pkl'");
    $dbTest->bind(':t', $testDate);
    $actualPklAlfa = (int)$dbTest->single()->cnt;

    assertTest("Auto-alfa menandai seluruh siswa reguler yang tidak scan/izin ($actualRegulerAlfa/$expectedReguler)", $expectedReguler > 0 && $actualRegulerAlfa === $expectedReguler);
    assertTest("Siswa PKL bebas dari aturan auto-alfa sekolah ($actualPklAlfa baris alfa)", $actualPklAlfa === 0);

    // 6.2 Auto-Alfa Guru sesuai jadwal aktif Senin
    $absensiTest->generateAlfaGuruHariIni($testDate, false);
    $dbTest->query("SELECT COUNT(*) AS cnt FROM absensi_guru WHERE tanggal = :t AND status = 'alfa'");
    $dbTest->bind(':t', $testDate);
    $actualGuruAlfa = (int)$dbTest->single()->cnt;
    assertTest("Auto-alfa menandai guru terjadwal aktif yang belum scan QR/izin ($actualGuruAlfa/$expectedGuruSenin)", $expectedGuruSenin > 0 && $actualGuruAlfa === $expectedGuruSenin);

    // 6.3 Approved Izin/Sakit menimpa placeholder alfa pada siswa & guru
    $dbTest->query("SELECT s.id AS siswa_id, s.user_id FROM siswa s JOIN kelas k ON k.id = s.kelas_id WHERE k.status != 'pkl' LIMIT 1");
    $sampleSiswa = $dbTest->single();
    $dbTest->query("SELECT g.id AS guru_id, g.user_id FROM jadwal_guru jg JOIN guru g ON g.id = jg.guru_id WHERE LOWER(jg.hari) = 'senin' AND jg.is_active = 1 LIMIT 1");
    $sampleGuru = $dbTest->single();

    if ($sampleSiswa && $sampleGuru) {
        $dbTest->query("INSERT INTO pengajuan_izin (user_id, tanggal_mulai, tanggal_selesai, jenis, alasan, status) VALUES (:uid, :t, :t, 'sakit', 'Unit Test Sakit', 'approved')");
        $dbTest->bind(':uid', (int)$sampleSiswa->user_id);
        $dbTest->bind(':t', $testDate);
        $dbTest->execute();
        $insertedIzinIds[] = (int)$dbTest->lastInsertId();

        $dbTest->query("INSERT INTO pengajuan_izin (user_id, tanggal_mulai, tanggal_selesai, jenis, alasan, status) VALUES (:uid, :t, :t, 'izin', 'Unit Test Izin Guru', 'approved')");
        $dbTest->bind(':uid', (int)$sampleGuru->user_id);
        $dbTest->bind(':t', $testDate);
        $dbTest->execute();
        $insertedIzinIds[] = (int)$dbTest->lastInsertId();

        $absensiTest->syncAlfaDanIzinRange($testDate, $testDate, false, true, true, 5);

        $dbTest->query("SELECT status FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :t");
        $dbTest->bind(':sid', (int)$sampleSiswa->siswa_id);
        $dbTest->bind(':t', $testDate);
        $syncedSiswa = $dbTest->single();

        $dbTest->query("SELECT status FROM absensi_guru WHERE guru_id = :gid AND tanggal = :t");
        $dbTest->bind(':gid', (int)$sampleGuru->guru_id);
        $dbTest->bind(':t', $testDate);
        $syncedGuru = $dbTest->single();

        assertTest("Izin sakit approved otomatis menggantikan placeholder alfa siswa menjadi 'sakit'", $syncedSiswa && $syncedSiswa->status === 'sakit');
        assertTest("Izin dinas approved otomatis menggantikan placeholder alfa guru menjadi 'izin'", $syncedGuru && $syncedGuru->status === 'izin');
    } else {
        assertTest("Sample siswa & guru tersedia untuk uji sinkronisasi izin", false);
    }

    // 6.4 Input Fleksibel PKL (Libur Industri & Shift Bebas + Rekap PKL)
    $dbTest->query("SELECT s.id FROM siswa s JOIN kelas k ON k.id = s.kelas_id WHERE k.status = 'pkl' LIMIT 1");
    $samplePkl = $dbTest->single();
    if ($samplePkl) {
        $pklSiswaId = (int)$samplePkl->id;
        $okLibur = $pklTest->savePresensiManualPkl($pklSiswaId, $testDate, 'libur_industri', null, null, 'Jadwal libur rolling industri');
        $dbTest->query("SELECT status, keterangan FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :t");
        $dbTest->bind(':sid', $pklSiswaId);
        $dbTest->bind(':t', $testDate);
        $rowLibur = $dbTest->single();
        assertTest("Penyesuaian fleksibel PKL 'libur_industri' tersimpan sebagai izin bernarasi", $okLibur && $rowLibur && $rowLibur->status === 'izin' && strpos($rowLibur->keterangan ?? '', 'Libur Industri') !== false);

        $okShift = $pklTest->savePresensiManualPkl($pklSiswaId, $testDate, 'pkl', '09:30:00', '17:00:00', 'Shift siang bengkel');
        $dbTest->query("SELECT COUNT(*) AS cnt, MAX(status) AS status, MAX(jam_datang) AS jam_datang, MAX(jam_pulang) AS jam_pulang FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :t");
        $dbTest->bind(':sid', $pklSiswaId);
        $dbTest->bind(':t', $testDate);
        $rowShift = $dbTest->single();
        assertTest("Update shift PKL fleksibel menimpa baris sama tanpa duplikat (09:30-17:00)", $okShift && $rowShift && (int)$rowShift->cnt === 1 && $rowShift->status === 'pkl' && $rowShift->jam_datang === '09:30:00');

        $rekapCount = $laporanTest->countRekapPkl(['dari' => $testDate, 'sampai' => $testDate]);
        assertTest("Data PKL fleksibel terbaca di Laporan Rekap PKL (count={$rekapCount})", $rekapCount >= 1);
    } else {
        assertTest("Sample siswa PKL tersedia untuk uji fleksibilitas PKL", false);
    }
} finally {
    if (!empty($insertedIzinIds)) {
        $idsSql = implode(',', array_map('intval', $insertedIzinIds));
        $dbTest->query("DELETE FROM pengajuan_izin WHERE id IN ($idsSql)");
        $dbTest->execute();
    }
    $dbTest->query("DELETE FROM absensi_siswa WHERE tanggal = :t");
    $dbTest->bind(':t', $testDate);
    $dbTest->execute();
    $dbTest->query("DELETE FROM absensi_guru WHERE tanggal = :t");
    $dbTest->bind(':t', $testDate);
    $dbTest->execute();
}

// -----------------------------------------------------------
// TEST SUITE 7: Performa, Offline Tailwind CSS & Konten Landing
// -----------------------------------------------------------
echo "\n--- 7. Testing Performa Aset Offline Tailwind & Landing Page ---\n";

$cssPath = __DIR__ . '/../web/public/assets/css/tailwind.css';
$cssContent = @file_get_contents($cssPath) ?: '';
assertTest("Bundel Tailwind CSS terkompilasi & minified tersedia secara lokal", strlen($cssContent) > 20000 && strlen($cssContent) < 150000 && strpos($cssContent, '.bg-forest{') !== false && strpos($cssContent, '.bg-lime{') !== false);
assertTest("Tailwind CSS mencakup dark mode & utilitas custom (shadow-2xs, backdrop-blur-xs, slate)", strpos($cssContent, 'dark\\:') !== false && strpos($cssContent, '.shadow-2xs{') !== false && strpos($cssContent, '.backdrop-blur-xs{') !== false && strpos($cssContent, '.text-slate{') !== false);

$swContent = @file_get_contents(__DIR__ . '/../web/public/sw.js') ?: '';
assertTest("Service Worker (sw.js) pre-cache Tailwind CSS, FontAwesome & font lokal tanpa JIT JS berat", strpos($swContent, './assets/css/tailwind.css') !== false && strpos($swContent, './assets/vendor/fontawesome/css/all.min.css') !== false && strpos($swContent, 'tailwind.min.js') === false && strpos($swContent, 'ignoreSearch: true') !== false);

$viewsToCheck = [
    __DIR__ . '/../web/app/views/home/landing.php',
    __DIR__ . '/../web/app/views/auth/login.php',
    __DIR__ . '/../web/app/views/templates/header.php',
    __DIR__ . '/../web/app/views/riwayat/index.php',
    __DIR__ . '/../web/app/views/riwayat/guru.php',
    __DIR__ . '/../web/app/views/errors/404_public.php',
];
$allOfflineReady = true;
foreach ($viewsToCheck as $viewFile) {
    $html = @file_get_contents($viewFile) ?: '';
    if (preg_match('/cdn\.tailwindcss\.com|tailwind\.min\.js|fonts\.googleapis\.com|cdnjs\.cloudflare\.com/i', $html)) {
        $allOfflineReady = false;
        break;
    }
    if (strpos($html, '/assets/css/tailwind.css') === false) {
        $allOfflineReady = false;
        break;
    }
}
assertTest("Seluruh halaman utama bebas CDN render-blocking & bebas JIT JS runtime", $allOfflineReady);

$landingHtml = @file_get_contents(__DIR__ . '/../web/app/views/home/landing.php') ?: '';
assertTest("Landing page menampilkan nama siswa 'NIhlah Auliya' (tanpa Ahmad Fauzi)", strpos($landingHtml, 'NIhlah Auliya') !== false && stripos($landingHtml, 'Ahmad Fauzi') === false);

$vercelCfg = json_decode(@file_get_contents(__DIR__ . '/../vercel.json') ?: '', true);
$hasAssetsCache = false;
if (is_array($vercelCfg) && !empty($vercelCfg['routes'])) {
    foreach ($vercelCfg['routes'] as $route) {
        if (($route['src'] ?? '') === '/assets/(.*)' && !empty($route['headers']['Cache-Control'])) {
            $hasAssetsCache = true;
        }
    }
}
assertTest("Konfigurasi vercel.json valid dengan header Cache-Control immutable untuk aset statis", is_array($vercelCfg) && $hasAssetsCache);

// -----------------------------------------------------------
// TEST SUITE 8: IoT Responsiveness & Pemisahan Keterangan vs Status Waktu
// -----------------------------------------------------------
echo "\n--- 8. Testing IoT UI Responsiveness & Pemisahan Keterangan Absensi ---\n";

$iotHtml = @file_get_contents(__DIR__ . '/../web/app/views/admin/iot.php') ?: '';
assertTest("Tampilan IoT (admin/iot.php) memiliki class responsif min-w-0, break-all, flex-wrap, dan sm:flex-row",
    strpos($iotHtml, 'min-w-0') !== false &&
    strpos($iotHtml, 'break-all') !== false &&
    strpos($iotHtml, 'flex-wrap') !== false &&
    strpos($iotHtml, 'sm:flex-row') !== false
);

assertTest("Tailwind CSS terkompilasi mencakup utilitas responsif IoT (.break-all, .min-w-0, .flex-wrap)",
    strpos($cssContent, '.break-all{') !== false &&
    strpos($cssContent, '.min-w-0{') !== false &&
    strpos($cssContent, '.flex-wrap{') !== false
);

$filterViewsToCheck = [
    __DIR__ . '/../web/app/views/rekap_absensi/index.php',
    __DIR__ . '/../web/app/views/absensi/index_siswa.php',
    __DIR__ . '/../web/app/views/absensi/index_guru.php',
    __DIR__ . '/../web/app/views/dashboard/admin.php',
    __DIR__ . '/../web/app/views/dashboard/kepala_sekolah.php',
    __DIR__ . '/../web/app/views/dashboard/wali_kelas.php',
];
$noTerlambatDropdown = true;
foreach ($filterViewsToCheck as $fv) {
    $fvHtml = @file_get_contents($fv) ?: '';
    if (preg_match('/<option[^>]*value=["\']terlambat["\']/i', $fvHtml)) {
        $noTerlambatDropdown = false;
        break;
    }
}
assertTest("Dropdown filter Keterangan Absensi hanya berisi Hadir/Izin/Sakit/Alfa (+PKL) tanpa opsi Terlambat", $noTerlambatDropdown);

$rekapHtml = @file_get_contents(__DIR__ . '/../web/app/views/rekap_absensi/index.php') ?: '';
assertTest("Tabel detail rekap absensi memisahkan kolom Keterangan dan Status Waktu",
    strpos($rekapHtml, '>Keterangan<') !== false &&
    strpos($rekapHtml, '>Status Waktu<') !== false &&
    strpos($rekapHtml, 'Akumulasi (H/I/S/A)') !== false
);

require_once __DIR__ . '/../web/app/models/Laporan.php';
require_once __DIR__ . '/../web/app/models/AbsensiMonitor.php';
$laporanModel = new Laporan();
$monitorModel = new AbsensiMonitor();
$testLateDate = '2099-12-30';

try {
    $dbTest->query("SELECT s.id FROM siswa s JOIN kelas k ON k.id = s.kelas_id WHERE k.status = 'aktif' LIMIT 1");
    $sampleSiswaLate = $dbTest->single();
    $dbTest->query("SELECT g.id FROM guru g JOIN users u ON u.id = g.user_id WHERE u.role IN ('guru', 'wali_kelas', 'walikelas', 'kepsek') LIMIT 1");
    $sampleGuruLate = $dbTest->single();

    if ($sampleSiswaLate && $sampleGuruLate) {
        $dbTest->query("INSERT INTO absensi_siswa (siswa_id, tanggal, jam_datang, status, keterangan) VALUES (:sid, :tgl, '07:40:00', 'terlambat', 'Telat test')");
        $dbTest->bind(':sid', $sampleSiswaLate->id);
        $dbTest->bind(':tgl', $testLateDate);
        $dbTest->execute();

        $dbTest->query("INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, status, keterangan) VALUES (:gid, :tgl, '07:45:00', 'terlambat', 'Telat guru test')");
        $dbTest->bind(':gid', $sampleGuruLate->id);
        $dbTest->bind(':tgl', $testLateDate);
        $dbTest->execute();

        $rekapSiswaHadir = $laporanModel->getRekapSiswa(['dari' => $testLateDate, 'sampai' => $testLateDate, 'status' => 'hadir', 'limit' => 10, 'offset' => 0]);
        $rekapGuruHadir  = $laporanModel->getRekapGuru(['dari' => $testLateDate, 'sampai' => $testLateDate, 'status' => 'hadir', 'limit' => 10, 'offset' => 0]);
        $monSiswaHadir   = $monitorModel->getRiwayatSiswa($sampleSiswaLate->id, $testLateDate, $testLateDate, 'hadir', '', 0, 10);
        $monGuruHadir    = $monitorModel->getRiwayatGuru($sampleGuruLate->id, $testLateDate, $testLateDate, 'hadir', '', 0, 10);

        assertTest("Filter Laporan status 'hadir' ikut menampilkan absensi terlambat sebagai Keterangan Hadir (Siswa & Guru)", count($rekapSiswaHadir) >= 1 && count($rekapGuruHadir) >= 1);
        assertTest("Filter Riwayat Pribadi status 'hadir' ikut menampilkan absensi terlambat (Siswa & Guru)", count($monSiswaHadir) >= 1 && count($monGuruHadir) >= 1);
    } else {
        assertTest("Sample siswa & guru tersedia untuk uji filter Hadir", false);
    }
} finally {
    $dbTest->query("DELETE FROM absensi_siswa WHERE tanggal = :t");
    $dbTest->bind(':t', $testLateDate);
    $dbTest->execute();
    $dbTest->query("DELETE FROM absensi_guru WHERE tanggal = :t");
    $dbTest->bind(':t', $testLateDate);
    $dbTest->execute();
}

// -----------------------------------------------------------
// 11. PENGUJIAN FITUR CATATAN CRUD, LOGIN ICON CSS, PKL ANDROID & DOKUMEN RPL
// -----------------------------------------------------------
echo "\n--- Bagian 11: Pengujian Catatan Edit/Hapus, Responsif Android, & Dokumen RPL ---\n";

require_once __DIR__ . '/../web/app/models/Catatan.php';
$catatanModel = new Catatan();

$dbTest->query("SELECT w.id AS wk_id, s.id AS siswa_id FROM users w JOIN kelas k ON k.wali_kelas_id = w.id JOIN siswa s ON s.kelas_id = k.id LIMIT 1");
$catSample = $dbTest->single();
if ($catSample) {
    $createdCat = $catatanModel->create([
        'target_role' => 'siswa',
        'target_id'   => (int)$catSample->siswa_id,
        'author_id'   => (int)$catSample->wk_id,
        'title'       => 'Uji Catatan Awal',
        'content'     => 'Isi catatan percobaan unit test',
    ]);
    $lastCatId = (int)$dbTest->lastInsertId();
    assertTest("Catatan::create berhasil menyisipkan catatan uji", $createdCat > 0 && $lastCatId > 0);

    try {
        $updOk = $catatanModel->updateByAuthor($lastCatId, 'siswa', (int)$catSample->wk_id, 'Uji Catatan Diedit', 'Isi catatan setelah diperbarui');
        $fetchedAfterUpd = $catatanModel->getByIdForAuthor($lastCatId, 'siswa', (int)$catSample->wk_id);
        assertTest(
            "Catatan::updateByAuthor memperbarui judul & isi catatan sesuai penulis",
            $updOk && $fetchedAfterUpd && $fetchedAfterUpd->title === 'Uji Catatan Diedit' && strpos($fetchedAfterUpd->content, 'diperbarui') !== false
        );

        $wrongAuthorUpd = $catatanModel->updateByAuthor($lastCatId, 'siswa', 999999999, 'Hijack', 'Illegal');
        assertTest("Catatan::updateByAuthor menolak modifikasi oleh user selain penulis", $wrongAuthorUpd === false);

        $delOk = $catatanModel->deleteByAuthor($lastCatId, 'siswa', (int)$catSample->wk_id);
        $fetchedAfterDel = $catatanModel->getByIdForAuthor($lastCatId, 'siswa', (int)$catSample->wk_id);
        assertTest("Catatan::deleteByAuthor menghapus catatan secara permanen", $delOk && !$fetchedAfterDel);
    } finally {
        if ($lastCatId > 0) {
            $dbTest->query("DELETE FROM catatan_siswa WHERE id = :cid");
            $dbTest->bind(':cid', $lastCatId);
            $dbTest->execute();
        }
    }
} else {
    assertTest("Sample wali_kelas & siswa tersedia untuk uji CRUD Catatan", false);
}

$catCtrlSrc = file_get_contents(__DIR__ . '/../web/app/controllers/CatatanController.php');
assertTest(
    "CatatanController memiliki endpoint daftar(), update(), dan delete()",
    strpos($catCtrlSrc, 'function daftar') !== false && strpos($catCtrlSrc, 'function update') !== false && strpos($catCtrlSrc, 'function delete') !== false
);

assertTest("View khusus halaman Daftar Catatan (catatan/daftar.php) tersedia", file_exists(__DIR__ . '/../web/app/views/catatan/daftar.php'));

$loginSrc = file_get_contents(__DIR__ . '/../web/app/views/auth/login.php');
assertTest(
    "Login UI memperbaiki override CSS padding ikon (.wise-input.pl-11 & .wise-input-icon-left)",
    strpos($loginSrc, '.wise-input.pl-11') !== false && strpos($loginSrc, 'wise-input-icon-right') !== false
);

$pklKelSrc = file_get_contents(__DIR__ . '/../web/app/views/pkl/kelompok.php');
$pklMonSrc = file_get_contents(__DIR__ . '/../web/app/views/pkl/monitoring.php');
assertTest(
    "Pengaturan PKL (kelompok & monitoring) menerapkan layout responsif Android (left-0 sm:left-auto sm:right-0, min-w-0, break-words)",
    strpos($pklKelSrc, 'left-0 sm:left-auto sm:right-0') !== false && strpos($pklKelSrc, 'break-words') !== false && strpos($pklMonSrc, 'left-0 sm:left-auto sm:right-0') !== false
);

$readmeSrc = file_get_contents(__DIR__ . '/../README.md');
assertTest(
    "README.md memuat identitas wajib (Nihlah Auliya, 41257146, Sunyaragi Nexus, Absensi Digital Siswa SMK, Video Links & Setup)",
    strpos($readmeSrc, 'Nihlah Auliya') !== false
    && strpos($readmeSrc, '41257146') !== false
    && strpos($readmeSrc, 'Sunyaragi Nexus') !== false
    && strpos($readmeSrc, 'Absensi Digital Siswa SMK') !== false
    && strpos($readmeSrc, 'Video Progres 1') !== false
    && strpos($readmeSrc, 'Video Progres 2') !== false
    && strpos($readmeSrc, 'Video Final Demo') !== false
    && strpos($readmeSrc, 'Petunjuk Instalasi') !== false
);

$pdfPath = __DIR__ . '/../docs/Dokumen_Lengkap_RPL_Nihlah_Auliya_41257146.pdf';
$pdfHeader = file_exists($pdfPath) ? file_get_contents($pdfPath, false, null, 0, 5) : '';
assertTest(
    "Berkas PDF Dokumen Lengkap Resmi tersedia dan valid (%PDF, >= 100KB)",
    file_exists($pdfPath) && $pdfHeader === '%PDF-' && filesize($pdfPath) >= 100000
);


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
