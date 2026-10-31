<?php
$siswaObj       = $data['siswa'] ?? null;
$siswaNama      = htmlspecialchars($siswaObj->nama ?? SessionHelper::get('name') ?? 'Siswa');
$siswaNis       = htmlspecialchars($siswaObj->nis ?? '-');
$siswaKelas     = htmlspecialchars($siswaObj->nama_kelas ?? 'Kelas PKL');
$waliNama       = htmlspecialchars($siswaObj->wali_nama ?? 'Wali Kelas');
$lokasiPkl      = $data['lokasiPkl'] ?? null;
$absensiHariIni = $data['absensiHariIni'] ?? null;
$sudahMasuk     = ($absensiHariIni && !empty($absensiHariIni->jam_datang));
$sudahPulang    = ($absensiHariIni && !empty($absensiHariIni->jam_pulang));

$registeredLat  = $lokasiPkl ? (float)$lokasiPkl->latitude : null;
$registeredLng  = $lokasiPkl ? (float)$lokasiPkl->longitude : null;
$registeredRad  = $lokasiPkl ? (int)($lokasiPkl->radius_meter ?? 50) : 50;
if ($registeredRad < 10) $registeredRad = 50;

$namaKelompok   = $lokasiPkl ? htmlspecialchars($lokasiPkl->nama_kelompok ?? 'Kelompok PKL') : '';
$namaTempat     = $lokasiPkl ? htmlspecialchars($lokasiPkl->nama_tempat ?? '') : '';
?>

<div id="pageContent" class="w-full space-y-6">

    <!-- ── PAGE HEADER ── -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <span>PKL</span>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200 font-medium">Presensi Mandiri</span>
                <span class="opacity-40">&bull;</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                    <?= $siswaKelas ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Presensi Selfie &amp; Validasi GPS
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                <span class="font-medium text-slate-800 dark:text-slate-200"><?= $siswaNama ?></span>
                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                <span class="font-mono text-xs text-slate-500 dark:text-slate-400">NIS: <?= $siswaNis ?></span>
                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">Wali: <?= $waliNama ?></span>
                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <i class="fa-regular fa-calendar-check text-emerald-600"></i>
                    <?= date('d F Y') ?>
                </span>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= BASEURL; ?>/dashboard/siswa" class="btn-secondary text-xs">
                <i class="fa-solid fa-arrow-left"></i> Dashboard Siswa
            </a>
        </div>
    </div>

    <!-- ── NOTIFIKASI FLASH ── -->
    <?php Flasher::flash(); ?>

    <?php if (!$lokasiPkl): ?>
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- STATE JIKA SISWA BELUM DIATUR KELOMPOK OLEH WALI KELAS             -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl p-8 sm:p-12 text-center max-w-xl mx-auto space-y-4 shadow-xs">
            <div class="w-14 h-14 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl mx-auto">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Anda Belum Terdaftar di Kelompok PKL</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                Anda belum terdaftar dalam kelompok PKL manapun. Silakan hubungi Wali Kelas (<strong><?= $waliNama ?></strong>) untuk ditugaskan ke tempat PKL.
            </p>
            <div class="pt-2">
                <a href="<?= BASEURL; ?>/dashboard/siswa" class="btn-primary inline-flex items-center gap-2 text-xs">
                    <i class="fa-solid fa-house"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    <?php else: ?>

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- STATE JIKA SISWA SUDAH DIATUR KELOMPOK OLEH WALI KELAS             -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ── KOLOM KIRI (7 COLS): KAMERA & PRESENSI ── -->
            <div class="lg:col-span-7 space-y-6">

                <!-- 1. Card Kamera Viewport -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
                    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Kamera Presensi &amp; Verifikasi GPS</h2>
                        </div>
                        <button type="button" class="btn-secondary text-xs" onclick="toggleCameraFacing()">
                            <i class="fa-solid fa-camera-rotate text-emerald-600"></i> Ganti Kamera
                        </button>
                    </div>

                    <div class="p-4 sm:p-5 space-y-4">
                        <!-- Viewport Kamera Modern -->
                        <div class="relative w-full rounded-xl overflow-hidden bg-slate-950 aspect-4/3 max-h-[380px] flex items-center justify-center border border-slate-800">
                            <video id="videoFeed" autoplay playsinline muted class="w-full h-full object-cover"></video>
                            <canvas id="photoCanvas" class="hidden"></canvas>

                            <!-- Loading overlay -->
                            <div id="cameraLoading" class="absolute inset-0 flex flex-col items-center justify-center text-white bg-slate-950/80 p-4 text-center space-y-2">
                                <div class="w-7 h-7 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin"></div>
                                <div class="text-xs text-slate-300">Menghubungkan kamera perangkat...</div>
                                <button type="button" class="btn-secondary text-xs mt-2 bg-slate-800 text-white border-slate-700" onclick="startCamera()">Aktifkan Kamera</button>
                            </div>

                            <!-- Viewport Guides -->
                            <div class="absolute inset-0 pointer-events-none border border-emerald-500/20 rounded-xl flex items-center justify-center">
                                <div class="w-44 h-44 border-2 border-dashed border-emerald-400/40 rounded-full"></div>
                            </div>
                        </div>

                        <!-- Panel Jarak GPS Realtime -->
                        <div id="distanceAlert" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-4 transition-colors">
                            <div class="space-y-0.5">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Jarak ke Titik Tempat PKL:</span>
                                <div class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                                    <span id="txtDistance">-</span>
                                </div>
                                <div id="txtDistanceStatus" class="text-xs text-slate-500 dark:text-slate-400">
                                    Mendeteksi koordinat GPS...
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn-secondary text-xs inline-flex items-center gap-1.5" onclick="refreshGpsLocation()">
                                    <i id="iconRefresh" class="fa-solid fa-arrows-rotate text-emerald-600"></i> Refresh GPS
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Presensi -->
                        <div>
                            <?php if ($sudahMasuk && $sudahPulang): ?>
                                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-center space-y-1">
                                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto text-sm mb-1">
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <p class="font-semibold text-emerald-900 dark:text-emerald-200 text-sm">Presensi PKL Hari Ini Sudah Lengkap</p>
                                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-mono">
                                        Masuk: <?= date('H:i', strtotime($absensiHariIni->jam_datang)) ?> WIB &bull; 
                                        Pulang: <?= date('H:i', strtotime($absensiHariIni->jam_pulang)) ?> WIB
                                    </p>
                                </div>
                            <?php elseif ($sudahMasuk): ?>
                                <button type="button" id="btnSubmitPresensi" onclick="takeSnapshotAndSubmit('pulang')" class="btn-primary w-full py-3 text-sm font-semibold flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-camera"></i> Ambil Foto Selfie &amp; Absen Pulang
                                </button>
                                <p class="text-center text-xs text-slate-400 dark:text-slate-500 mt-2">
                                    Presensi masuk tercatat pukul <strong class="font-mono text-emerald-600 dark:text-emerald-400"><?= date('H:i', strtotime($absensiHariIni->jam_datang)) ?> WIB</strong>.
                                </p>
                            <?php else: ?>
                                <button type="button" id="btnSubmitPresensi" onclick="takeSnapshotAndSubmit('masuk')" class="btn-primary w-full py-3 text-sm font-semibold flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-camera"></i> Ambil Foto Selfie &amp; Absen Masuk
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- 2. Rekap Presensi Hari Ini Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
                    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Catatan Presensi Hari Ini</h2>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Presensi Masuk -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Presensi Masuk</span>
                                    <?php if ($sudahMasuk): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Sudah</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">Belum</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($sudahMasuk && !empty($absensiHariIni->foto_masuk)): ?>
                                    <div class="rounded-lg overflow-hidden bg-slate-950 h-32 border border-slate-700/50">
                                        <img src="<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($absensiHariIni->foto_masuk) ?>" alt="Foto Masuk" class="w-full h-full object-cover">
                                    </div>
                                <?php endif; ?>
                                <div class="text-xs text-slate-600 dark:text-slate-400">
                                    Waktu: <strong class="font-mono text-slate-900 dark:text-slate-100"><?= $sudahMasuk ? date('H:i:s', strtotime($absensiHariIni->jam_datang)) . ' WIB' : '-' ?></strong>
                                </div>
                            </div>

                            <!-- Presensi Pulang -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Presensi Pulang</span>
                                    <?php if ($sudahPulang): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Sudah</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">Belum</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($sudahPulang && !empty($absensiHariIni->foto_pulang)): ?>
                                    <div class="rounded-lg overflow-hidden bg-slate-950 h-32 border border-slate-700/50">
                                        <img src="<?= BASEURL ?>/uploads/absensi_pkl/<?= htmlspecialchars($absensiHariIni->foto_pulang) ?>" alt="Foto Pulang" class="w-full h-full object-cover">
                                    </div>
                                <?php endif; ?>
                                <div class="text-xs text-slate-600 dark:text-slate-400">
                                    Waktu: <strong class="font-mono text-slate-900 dark:text-slate-100"><?= $sudahPulang ? date('H:i:s', strtotime($absensiHariIni->jam_pulang)) . ' WIB' : '-' ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── KOLOM KANAN (5 COLS): DETAIL TEMPAT PKL (READ-ONLY) ── -->
            <div class="lg:col-span-5 space-y-6">

                <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs">
                    <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Tempat PKL Terdaftar</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                            Terverifikasi
                        </span>
                    </div>

                    <div class="p-4 sm:p-5 space-y-3.5 text-xs">
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider block mb-0.5">Kelompok PKL:</span>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white"><?= $namaKelompok ?></div>
                        </div>

                        <div>
                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider block mb-0.5">Nama Perusahaan / Tempat:</span>
                            <div class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?= $namaTempat ?></div>
                        </div>

                        <div>
                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider block mb-0.5">Alamat Tempat PKL:</span>
                            <div class="text-slate-700 dark:text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($lokasiPkl->alamat ?: 'Alamat belum diatur oleh Wali Kelas')) ?></div>
                        </div>

                        <div>
                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider block mb-0.5">Titik Koordinat Resmi:</span>
                            <div class="font-mono text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 space-y-0.5 text-xs">
                                <div>Lat: <strong class="text-slate-900 dark:text-white"><?= htmlspecialchars($lokasiPkl->latitude) ?></strong></div>
                                <div>Lng: <strong class="text-slate-900 dark:text-white"><?= htmlspecialchars($lokasiPkl->longitude) ?></strong></div>
                            </div>
                            <div class="text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-between text-[11px]">
                                <span>Batas toleransi radius:</span>
                                <strong class="font-mono text-emerald-600 dark:text-emerald-400"><?= $registeredRad ?> meter</strong>
                            </div>
                        </div>

                        <?php if (!empty($lokasiPkl->pembimbing_nama)): ?>
                        <div>
                            <span class="text-[11px] font-medium text-slate-400 uppercase tracking-wider block mb-0.5">Pembimbing Lapangan / PIC:</span>
                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                <?= htmlspecialchars($lokasiPkl->pembimbing_nama) ?>
                                <?= !empty($lokasiPkl->pembimbing_kontak) ? '(' . htmlspecialchars($lokasiPkl->pembimbing_kontak) . ')' : '' ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($lokasiPkl->link_lokasi)): ?>
                        <div class="pt-1">
                            <a href="<?= htmlspecialchars($lokasiPkl->link_lokasi) ?>" target="_blank" rel="noopener noreferrer" class="btn-secondary text-xs w-full flex items-center justify-center gap-2">
                                <i class="fa-solid fa-arrow-up-right-from-square text-emerald-600"></i> Buka Titik di Google Maps
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

    <?php endif; ?>

</div>

<!-- ── MODAL HASIL PRESENSI ── -->
<div id="modalResult" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-sm p-6 text-center space-y-4 animate-fade-in">
        <div id="modalIcon" class="text-3xl"></div>
        <h3 id="modalTitle" class="text-base font-semibold text-slate-900 dark:text-white">Hasil Presensi</h3>
        <p id="modalMessage" class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed"></p>
        <button type="button" class="btn-primary text-xs w-full py-2.5" onclick="closeResultModal()">
            Tutup
        </button>
    </div>
</div>

<script>
// ═════════════════════════════════════════════════════════════════════════════
// CLIENT LOGIC: CAMERA, GEOLOCATION, DAN VALIDASI JARAK RADIUS
// ═════════════════════════════════════════════════════════════════════════════

const REGISTERED_LAT = <?= $registeredLat !== null ? $registeredLat : 'null' ?>;
const REGISTERED_LNG = <?= $registeredLng !== null ? $registeredLng : 'null' ?>;
const MAX_RADIUS     = <?= $registeredRad ?>;

let currentStream = null;
let currentFacing = 'user';
let userLat       = null;
let userLng       = null;
let userDist      = null;

function haversineDistance(lat1, lon1, lat2, lon2) {
    if (lat1 === null || lon1 === null || lat2 === null || lon2 === null) return null;
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c * 10) / 10;
}

async function startCamera() {
    const video = document.getElementById('videoFeed');
    const loading = document.getElementById('cameraLoading');
    if (!video) return;

    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: currentFacing, width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: false
        });
        currentStream = stream;
        video.srcObject = stream;
        video.play();
        if (loading) loading.classList.add('hidden');
    } catch (e) {
        console.warn('Fallback basic camera:', e);
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            currentStream = stream;
            video.srcObject = stream;
            video.play();
            if (loading) loading.classList.add('hidden');
        } catch (err) {
            if (loading) {
                loading.classList.remove('hidden');
                loading.innerHTML = '<div class="text-xs text-rose-400">Kamera tidak dapat diakses. Pastikan izin kamera aktif pada browser perangkat Anda.</div>';
            }
        }
    }
}

function toggleCameraFacing() {
    currentFacing = (currentFacing === 'user') ? 'environment' : 'user';
    startCamera();
}

function initGeolocation() {
    if (!navigator.geolocation) {
        updateDistanceDisplay(null, 'Browser tidak mendukung geolokasi GPS');
        return;
    }

    navigator.geolocation.getCurrentPosition(onGpsSuccess, onGpsError, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });

    navigator.geolocation.watchPosition(onGpsSuccess, onGpsError, {
        enableHighAccuracy: true,
        maximumAge: 3000
    });
}

function onGpsSuccess(pos) {
    userLat = pos.coords.latitude;
    userLng = pos.coords.longitude;

    if (REGISTERED_LAT !== null && REGISTERED_LNG !== null) {
        userDist = haversineDistance(userLat, userLng, REGISTERED_LAT, REGISTERED_LNG);
        updateDistanceDisplay(userDist);
    } else {
        updateDistanceDisplay(null, 'Lokasi tempat PKL belum didaftarkan oleh Wali Kelas');
    }
}

function onGpsError(err) {
    updateDistanceDisplay(null, 'Izin GPS belum diizinkan atau sinyal GPS belum aktif');
}

function refreshGpsLocation() {
    const icon = document.getElementById('iconRefresh');
    if (icon) icon.classList.add('fa-spin');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                onGpsSuccess(pos);
                if (icon) icon.classList.remove('fa-spin');
            },
            (err) => {
                onGpsError(err);
                if (icon) icon.classList.remove('fa-spin');
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
        );
    }
}

function updateDistanceDisplay(dist, errorMsg = null) {
    const txtDistance = document.getElementById('txtDistance');
    const txtStatus   = document.getElementById('txtDistanceStatus');
    const alertBox    = document.getElementById('distanceAlert');

    if (!txtDistance) return;

    if (errorMsg || dist === null) {
        txtDistance.innerText = '-';
        txtStatus.innerText = errorMsg || 'Menunggu koordinat GPS...';
        alertBox.className = 'p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-4 transition-colors';
        return;
    }

    const rounded = Math.round(dist);
    txtDistance.innerText = rounded + ' meter';

    if (rounded > MAX_RADIUS) {
        alertBox.className = 'p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 flex items-center justify-between gap-4 transition-colors';
        txtStatus.innerHTML = `<span class="text-rose-600 dark:text-rose-400 font-medium">Di luar radius (${rounded}m > batas ${MAX_RADIUS}m).</span> Anda harus berada di lokasi PKL.`;
    } else {
        alertBox.className = 'p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-between gap-4 transition-colors';
        txtStatus.innerHTML = `<span class="text-emerald-600 dark:text-emerald-400 font-medium">Dalam radius valid (${rounded}m &le; ${MAX_RADIUS}m).</span> Posisi terverifikasi.`;
    }
}

async function takeSnapshotAndSubmit(actionType) {
    if (REGISTERED_LAT === null || REGISTERED_LNG === null) {
        showModal(false, 'Lokasi PKL Belum Ditentukan', 'Wali Kelas Anda belum menentukan titik lokasi tempat PKL.');
        return;
    }

    if (userLat === null || userLng === null) {
        showModal(false, 'GPS Belum Terdeteksi', 'Pastikan izin lokasi (GPS) pada perangkat Anda telah diaktifkan, lalu klik Refresh GPS.');
        return;
    }

    const currentDist = haversineDistance(userLat, userLng, REGISTERED_LAT, REGISTERED_LNG);
    if (currentDist > MAX_RADIUS) {
        showModal(false, 'Presensi Ditolak', `Jarak Anda saat ini (${Math.round(currentDist)} meter) melebihi batas radius ${MAX_RADIUS} meter dari tempat PKL. Absensi ditolak.`);
        return;
    }

    const video = document.getElementById('videoFeed');
    const canvas = document.getElementById('photoCanvas');
    if (!video || !canvas) return;

    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const photoBase64 = canvas.toDataURL('image/jpeg', 0.85);

    const btnSubmit = document.getElementById('btnSubmitPresensi');
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memproses presensi...`;
    }

    try {
        const response = await fetch('<?= BASEURL ?>/absensi/submit_pkl', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: actionType,
                latitude: userLat,
                longitude: userLng,
                foto: photoBase64
            })
        });

        const res = await response.json();
        if (res.success) {
            showModal(true, 'Presensi Berhasil', res.message);
        } else {
            showModal(false, 'Presensi Gagal', res.message);
        }
    } catch (err) {
        console.error(err);
        showModal(false, 'Terjadi Kesalahan', 'Gagal menghubungi server. Pastikan koneksi internet Anda lancar.');
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `<i class="fa-solid fa-camera mr-1"></i> ${actionType === 'pulang' ? 'Ambil Foto Selfie & Absen Pulang' : 'Ambil Foto Selfie & Absen Masuk'}`;
        }
    }
}

function showModal(isSuccess, title, message) {
    const iconEl = document.getElementById('modalIcon');
    const titleEl = document.getElementById('modalTitle');
    const msgEl = document.getElementById('modalMessage');

    titleEl.innerText = title;
    msgEl.innerText = message;

    if (isSuccess) {
        iconEl.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-500"></i>';
    } else {
        iconEl.innerHTML = '<i class="fa-solid fa-circle-xmark text-rose-500"></i>';
    }

    document.getElementById('modalResult').classList.remove('hidden');
}

function closeResultModal() {
    document.getElementById('modalResult').classList.add('hidden');
    window.location.reload();
}

document.addEventListener('DOMContentLoaded', () => {
    startCamera();
    initGeolocation();
});
</script>
