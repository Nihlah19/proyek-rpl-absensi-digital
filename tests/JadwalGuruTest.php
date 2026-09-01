<?php
/**
 * Business Logic Test: Jadwal Guru
 * 
 * Memastikan bahwa:
 * 1. Guru TANPA jadwal aktif hari ini TIDAK dianggap alpa.
 * 2. Guru DENGAN jadwal aktif hari ini dan BELUM absen dianggap alpa.
 * 3. Guru DENGAN jadwal aktif hari ini dan SUDAH absen TIDAK dianggap alpa.
 * 4. Model Absensi::getJadwalGuruHariIni() mengembalikan null jika jadwal tidak aktif.
 * 5. Model Absensi::getJadwalGuruHariIni() mengembalikan data jika jadwal aktif.
 * 
 * Jalankan: php tests/JadwalGuruTest.php
 */

require_once __DIR__ . '/../web/app/init.php';
require_once __DIR__ . '/../web/app/core/Model.php';
require_once __DIR__ . '/../web/app/core/Database.php';
require_once __DIR__ . '/../web/app/models/Absensi.php';

$db = new Database();
$passed = 0;
$failed = 0;

// ── Helper Functions ──
function hariIndo() {
    $map = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    return $map[date('l')];
}

function assert_test($name, $condition, &$passed, &$failed) {
    if ($condition) {
        echo "  ✓ PASS: $name\n";
        $passed++;
    } else {
        echo "  ✗ FAIL: $name\n";
        $failed++;
    }
}

echo "===========================================\n";
echo " Business Logic Test: Jadwal Guru\n";
echo " Hari ini: " . hariIndo() . " (" . date('Y-m-d') . ")\n";
echo "===========================================\n\n";

// ── Setup: Bersihkan data test sebelumnya ──
$db->query("DELETE FROM absensi_guru WHERE guru_id IN (SELECT id FROM guru WHERE nip IN ('TEST001', 'TEST002', 'TEST003'))");
$db->execute();
$db->query("DELETE FROM jadwal_guru WHERE guru_id IN (SELECT id FROM guru WHERE nip IN ('TEST001', 'TEST002', 'TEST003'))");
$db->execute();
$db->query("DELETE FROM guru WHERE nip IN ('TEST001', 'TEST002', 'TEST003')");
$db->execute();
$db->query("DELETE FROM users WHERE username IN ('test_guru_1', 'test_guru_2', 'test_guru_3')");
$db->execute();

// ── Setup: Buat 3 guru test ──
$guru_ids = [];
$user_ids = [];

for ($i = 1; $i <= 3; $i++) {
    $username = "test_guru_$i";
    $nip = "TEST00$i";
    $name = "Guru Test $i";
    
    $db->query("INSERT INTO users (username, password, name, role) VALUES (:u, :p, :n, 'guru')");
    $db->bind(':u', $username);
    $db->bind(':p', password_hash('test', PASSWORD_DEFAULT));
    $db->bind(':n', $name);
    $db->execute();
    
    $db->query("SELECT id FROM users WHERE username = :u");
    $db->bind(':u', $username);
    $uid = $db->single()->id;
    $user_ids[] = $uid;
    
    $db->query("INSERT INTO guru (user_id, nip, barcode_id) VALUES (:uid, :nip, :bc)");
    $db->bind(':uid', $uid);
    $db->bind(':nip', $nip);
    $db->bind(':bc', "QR-$nip");
    $db->execute();
    
    $db->query("SELECT id FROM guru WHERE nip = :nip");
    $db->bind(':nip', $nip);
    $guru_ids[] = $db->single()->id;
}

$hari = hariIndo();

// ── Guru 1: TANPA jadwal aktif hari ini (is_active = 0) ──
$db->query("INSERT INTO jadwal_guru (guru_id, hari, jam_datang, batas_terlambat, jam_pulang, is_active) VALUES (:gid, :hari, '07:00:00', '07:15:00', '15:00:00', 0)");
$db->bind(':gid', $guru_ids[0]);
$db->bind(':hari', $hari);
$db->execute();

// ── Guru 2: DENGAN jadwal aktif hari ini, BELUM absen ──
$db->query("INSERT INTO jadwal_guru (guru_id, hari, jam_datang, batas_terlambat, jam_pulang, is_active) VALUES (:gid, :hari, '06:45:00', '07:00:00', '15:00:00', 1)");
$db->bind(':gid', $guru_ids[1]);
$db->bind(':hari', $hari);
$db->execute();

// ── Guru 3: DENGAN jadwal aktif hari ini, SUDAH absen hadir ──
$db->query("INSERT INTO jadwal_guru (guru_id, hari, jam_datang, batas_terlambat, jam_pulang, is_active) VALUES (:gid, :hari, '06:45:00', '07:00:00', '15:00:00', 1)");
$db->bind(':gid', $guru_ids[2]);
$db->bind(':hari', $hari);
$db->execute();

$db->query("INSERT INTO absensi_guru (guru_id, tanggal, jam_datang, status) VALUES (:gid, :tgl, '06:50:00', 'hadir')");
$db->bind(':gid', $guru_ids[2]);
$db->bind(':tgl', date('Y-m-d'));
$db->execute();

// ═════════════════════════════════════════
// TEST GROUP 1: Model Absensi::getJadwalGuruHariIni()
// ═════════════════════════════════════════
echo "── Test Group 1: Absensi::getJadwalGuruHariIni() ──\n";

$absensi = new Absensi();

// Test 1.1: Guru tanpa jadwal aktif → null
$jadwal1 = $absensi->getJadwalGuruHariIni($guru_ids[0]);
assert_test(
    "Guru tanpa jadwal aktif → getJadwalGuruHariIni() mengembalikan NULL",
    $jadwal1 === false || $jadwal1 === null,
    $passed, $failed
);

// Test 1.2: Guru dengan jadwal aktif → data jadwal
$jadwal2 = $absensi->getJadwalGuruHariIni($guru_ids[1]);
assert_test(
    "Guru dengan jadwal aktif → getJadwalGuruHariIni() mengembalikan data",
    $jadwal2 !== false && $jadwal2 !== null,
    $passed, $failed
);

// Test 1.3: Verifikasi data jadwal benar
if ($jadwal2) {
    assert_test(
        "Jam datang sesuai (06:45:00)",
        $jadwal2->jam_datang === '06:45:00',
        $passed, $failed
    );
    assert_test(
        "Batas terlambat sesuai (07:00:00)",
        $jadwal2->batas_terlambat === '07:00:00',
        $passed, $failed
    );
    assert_test(
        "Jam pulang sesuai (15:00:00)",
        $jadwal2->jam_pulang === '15:00:00',
        $passed, $failed
    );
}

// ═════════════════════════════════════════
// TEST GROUP 2: Logika Alfa (berdasarkan jadwal + absensi)
// ═════════════════════════════════════════
echo "\n── Test Group 2: Logika Alfa (Guru tanpa jadwal ≠ Alpa) ──\n";

// Query: hitung alfa hanya jika jadwal aktif DAN belum ada absensi
$queryAlfa = "SELECT g.id, 
    CASE 
        WHEN jg.is_active = 1 AND ab.id IS NULL THEN 1 
        ELSE 0 
    END AS is_alfa
    FROM guru g
    LEFT JOIN jadwal_guru jg ON jg.guru_id = g.id AND jg.hari = :hari
    LEFT JOIN absensi_guru ab ON ab.guru_id = g.id AND ab.tanggal = :today
    WHERE g.id = :gid";

// Test 2.1: Guru 1 — tanpa jadwal aktif → TIDAK alfa
$db->query($queryAlfa);
$db->bind(':hari', $hari);
$db->bind(':today', date('Y-m-d'));
$db->bind(':gid', $guru_ids[0]);
$result1 = $db->single();
assert_test(
    "Guru tanpa jadwal aktif → TIDAK dianggap alpa (is_alfa = 0)",
    (int)$result1->is_alfa === 0,
    $passed, $failed
);

// Test 2.2: Guru 2 — jadwal aktif, belum absen → ALFA
$db->query($queryAlfa);
$db->bind(':hari', $hari);
$db->bind(':today', date('Y-m-d'));
$db->bind(':gid', $guru_ids[1]);
$result2 = $db->single();
assert_test(
    "Guru dengan jadwal aktif & belum absen → dianggap ALPA (is_alfa = 1)",
    (int)$result2->is_alfa === 1,
    $passed, $failed
);

// Test 2.3: Guru 3 — jadwal aktif, sudah absen → TIDAK alfa
$db->query($queryAlfa);
$db->bind(':hari', $hari);
$db->bind(':today', date('Y-m-d'));
$db->bind(':gid', $guru_ids[2]);
$result3 = $db->single();
assert_test(
    "Guru dengan jadwal aktif & sudah absen → TIDAK dianggap alpa (is_alfa = 0)",
    (int)$result3->is_alfa === 0,
    $passed, $failed
);

// ═════════════════════════════════════════
// TEST GROUP 3: Scan pertama = Datang, Scan kedua = Pulang
// ═════════════════════════════════════════
echo "\n── Test Group 3: Logika Scan (pertama=datang, kedua=pulang) ──\n";

// Test 3.1: Guru 2 belum ada absensi
$cek = $absensi->cekAbsensiGuru($guru_ids[1], date('Y-m-d'));
assert_test(
    "Guru belum absen → cekAbsensiGuru() mengembalikan false/null",
    $cek === false || $cek === null,
    $passed, $failed
);

// Test 3.2: Guru 3 sudah absen datang, belum pulang
$cek3 = $absensi->cekAbsensiGuru($guru_ids[2], date('Y-m-d'));
assert_test(
    "Guru sudah absen datang → cekAbsensiGuru() mengembalikan data",
    $cek3 !== false && $cek3 !== null,
    $passed, $failed
);

if ($cek3) {
    assert_test(
        "Guru sudah absen datang → jam_pulang masih NULL (belum scan pulang)",
        $cek3->jam_pulang === null,
        $passed, $failed
    );
    
    // Simulasi scan pulang
    $absensi->updateAbsensiPulangGuru($cek3->id, '15:05:00');
    
    $cek3b = $absensi->cekAbsensiGuru($guru_ids[2], date('Y-m-d'));
    assert_test(
        "Setelah scan kedua → jam_pulang terisi (15:05:00)",
        $cek3b->jam_pulang === '15:05:00',
        $passed, $failed
    );
}

// ═════════════════════════════════════════
// TEST GROUP 4: Guru tanpa row jadwal sama sekali
// ═════════════════════════════════════════
echo "\n── Test Group 4: Guru tanpa row jadwal di database ──\n";

// Buat guru baru tanpa insert jadwal sama sekali
$db->query("INSERT INTO users (username, password, name, role) VALUES ('test_guru_nojadwal', :p, 'Guru No Jadwal', 'guru')");
$db->bind(':p', password_hash('test', PASSWORD_DEFAULT));
$db->execute();

$db->query("SELECT id FROM users WHERE username = 'test_guru_nojadwal'");
$uid_nj = $db->single()->id;

$db->query("INSERT INTO guru (user_id, nip, barcode_id) VALUES (:uid, 'TEST999', 'QR-TEST999')");
$db->bind(':uid', $uid_nj);
$db->execute();

$db->query("SELECT id FROM guru WHERE nip = 'TEST999'");
$gid_nj = $db->single()->id;

$jadwal_nj = $absensi->getJadwalGuruHariIni($gid_nj);
assert_test(
    "Guru tanpa row jadwal di DB → getJadwalGuruHariIni() mengembalikan NULL",
    $jadwal_nj === false || $jadwal_nj === null,
    $passed, $failed
);

// ── Cleanup ──
echo "\n── Cleanup ──\n";

$db->query("DELETE FROM absensi_guru WHERE guru_id IN (SELECT id FROM guru WHERE nip IN ('TEST001', 'TEST002', 'TEST003', 'TEST999'))");
$db->execute();
$db->query("DELETE FROM jadwal_guru WHERE guru_id IN (SELECT id FROM guru WHERE nip IN ('TEST001', 'TEST002', 'TEST003', 'TEST999'))");
$db->execute();
$db->query("DELETE FROM guru WHERE nip IN ('TEST001', 'TEST002', 'TEST003', 'TEST999')");
$db->execute();
$db->query("DELETE FROM users WHERE username IN ('test_guru_1', 'test_guru_2', 'test_guru_3', 'test_guru_nojadwal')");
$db->execute();

echo "  Data test berhasil dibersihkan.\n";

// ── Hasil Akhir ──
echo "\n===========================================\n";
echo " HASIL: $passed PASS / $failed FAIL\n";
echo "===========================================\n";

exit($failed > 0 ? 1 : 0);
