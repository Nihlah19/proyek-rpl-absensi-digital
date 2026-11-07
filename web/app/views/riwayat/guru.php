<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <title><?= $data['judul'] ?? 'Live Monitor Absensi Guru' ?></title>
    <!-- PWA Settings -->
    <link rel="manifest" href="<?= BASEURL; ?>/manifest.json">
    <meta name="theme-color" content="#0F7A4A">
    <link rel="apple-touch-icon" href="<?= BASEURL; ?>/assets/img/apple-touch-icon.png?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASEURL; ?>/assets/img/icon-192.png?v=3">
    <link rel="shortcut icon" href="<?= BASEURL; ?>/assets/img/logo.png?v=3" type="image/png">
    <!-- Local Vendor Libraries (Offline Ready) -->
    <script src="<?= BASEURL; ?>/assets/vendor/tailwindcss/tailwind.min.js"></script>
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fontawesome/css/all.min.css">
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fonts/plus-jakarta-sans/plus-jakarta-sans.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0F7A4A',
                        'primary-dark': '#075C38',
                        'primary-light': '#14B8A6',
                        secondary: '#F59E0B',
                        accent: '#EA580C',
                        success: '#059669',
                    },
                    fontFamily: {
                        sans: ['Inter', 'Plus Jakarta Sans', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        
        /* ── Kotak kamera & Frame Animasi ── */
        .camera-box { position:relative;width:100%;min-height:280px;aspect-ratio:1/1;max-width:320px;margin:0 auto;border-radius:16px;overflow:hidden;background:#0f172a;display:flex;align-items:center;justify-content:center; }
        #qr-reader { width:100% !important;height:100% !important;border:none !important;background:transparent !important; }
        #qr-reader video { width:100% !important;height:100% !important;object-fit:cover !important;border-radius:12px; }
        #qr-reader__scan_region { width:100% !important;height:100% !important; }
        #qr-reader__dashboard,#qr-reader__header_message,#qr-reader__camera_selection,#qr-reader__camera_permission_button { display:none !important; }
        
        .scan-frame { position:absolute;inset:24px;pointer-events:none;z-index:4; }
        .corner { position:absolute;width:28px;height:28px;border:3px solid #3b82f6; }
        .corner-tl { top:0;left:0;border-right:none;border-bottom:none;border-top-left-radius:8px; }
        .corner-tr { top:0;right:0;border-left:none;border-bottom:none;border-top-right-radius:8px; }
        .corner-bl { bottom:0;left:0;border-right:none;border-top:none;border-bottom-left-radius:8px; }
        .corner-br { bottom:0;right:0;border-left:none;border-top:none;border-bottom-right-radius:8px; }
        
        .scan-laser { position:absolute;left:0;right:0;top:0;height:2px;background:#3b82f6;box-shadow:0 0 8px 2px rgba(59,130,246,0.6);animation:scan-laser-move 2.2s ease-in-out infinite; }
        @keyframes scan-laser-move { 0%{top:0%;opacity:.9}48%{top:100%;opacity:.9}50%{opacity:0}52%{top:0%;opacity:0}55%{opacity:.9}100%{top:0%;opacity:.9} }
        
        /* ── Flash visual respon instan ── */
        .camera-box { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .camera-box.flash-success {
            border-color: #10b981 !important;
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.7) !important;
        }
        .camera-box.flash-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.7) !important;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased min-h-screen flex flex-col text-slate-800">

    <!-- Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white border border-slate-200 shadow-2xs p-1.5 shrink-0">
                    <img src="<?= BASEURL; ?>/assets/img/logo.png?v=3" alt="Logo SMK NU Wargabinangun" class="h-full w-full object-contain">
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-tight">SMK NU WARGABINANGUN</h1>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Kolom Kiri: Area Scan Barcode -->
        <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 text-xs shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">Scan QR / Kartu Guru</h2>
                    <p class="text-xs text-slate-500 leading-tight">Arahkan QR ID ke lensa kamera</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5 flex flex-col items-center justify-center min-h-[350px] text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-blue-50/40 to-transparent pointer-events-none"></div>
                <div class="relative z-10 w-full flex flex-col items-center">
                    
                    <!-- Geolocation Geofence Status Badge (Maks 100 Meter) -->
                    <div id="monitor-geo-badge" class="mb-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs transition-all">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span id="monitor-geo-text">Memeriksa GPS lokasi sekolah...</span>
                        <button type="button" id="btn-refresh-geo" onclick="checkMonitorGeo(true)" class="ml-1 text-slate-400 hover:text-slate-700" title="Perbarui Lokasi GPS">
                            <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                        </button>
                    </div>

                    <div class="camera-box border-2 border-slate-200 shadow-inner mb-3 relative max-w-[280px]">
                        <div id="qr-reader"></div>
                        
                        <!-- Animasi Frame -->
                        <div id="scan-frame" class="scan-frame hidden">
                            <span class="corner corner-tl"></span>
                            <span class="corner corner-tr"></span>
                            <span class="corner corner-bl"></span>
                            <span class="corner corner-br"></span>
                            <div class="scan-laser"></div>
                        </div>

                        <!-- Geo-Lock Overlay (Jika di luar jarak 100m atau GPS tidak aktif) -->
                        <div id="monitor-geo-lock" class="absolute inset-0 bg-slate-900/95 backdrop-blur-xs flex flex-col items-center justify-center p-4 text-white z-20 hidden text-center">
                            <div class="w-10 h-10 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mb-2 border border-rose-500/30">
                                <i class="fa-solid fa-location-dot text-base"></i>
                            </div>
                            <h4 id="monitor-geo-lock-title" class="font-bold text-xs text-rose-300 mb-1">Di Luar Jangkauan Sekolah</h4>
                            <p id="monitor-geo-lock-msg" class="text-[11px] text-slate-300 max-w-[220px] mb-2 leading-relaxed">
                                Presensi monitor guru hanya dapat digunakan dalam radius maksimal 100 meter dari titik lokasi sekolah.
                            </p>
                            <p id="monitor-geo-lock-dist" class="text-[10px] font-mono font-semibold text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md mb-2.5">
                                Jarak: Memeriksa...
                            </p>
                            <button type="button" onclick="checkMonitorGeo(true)" class="text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-arrows-rotate text-[11px]"></i> Periksa Lokasi Lagi
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div id="camera-loading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-white z-10">
                            <i class="fa-solid fa-spinner fa-spin text-3xl mb-2 text-blue-400"></i>
                            <p class="text-xs font-medium text-slate-300">Menyiapkan Kamera Scanner...</p>
                        </div>
                    </div>

                    <!-- Camera Selector & Controls -->
                    <div class="flex flex-wrap items-center justify-center gap-1.5 mb-2.5">
                        <select id="camera-select" class="hidden text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 font-medium max-w-[150px] truncate shadow-2xs focus:ring-1 focus:ring-blue-500"></select>
                        <button id="btn-switch-cam" class="hidden text-xs bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-medium px-2.5 py-1.5 rounded-lg shadow-2xs transition">
                            <i class="fa-solid fa-camera-rotate mr-1 text-blue-600"></i> Ganti
                        </button>
                        <button id="btn-upload-scan" class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold px-2.5 py-1.5 rounded-lg border border-blue-200 transition">
                            <i class="fa-solid fa-file-image mr-1"></i> Upload QR
                        </button>
                        <input type="file" id="monitor-file-input" accept="image/*" class="hidden">
                    </div>
                    
                    <h3 class="text-sm font-bold text-slate-800 mt-1 mb-0.5">Siap Memindai QR Guru</h3>
                    <p class="text-xs text-slate-500 mb-2">Tunjukkan kode QR atau kartu ID Guru ke kamera</p>

                    <div id="scan-feedback" class="mt-2 hidden w-full p-2.5 rounded-lg text-xs font-semibold text-center transition-all">
                        <!-- Pesan error / sukses -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Riwayat Absensi Guru -->
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-slate-200 rounded-xl p-4 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600 text-sm shrink-0">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">Riwayat Absensi Guru Hari Ini</h2>
                        <p class="text-xs text-slate-500 leading-tight">Data pemindaian presensi pendidik & tenaga kependidikan terbaru</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-mono font-semibold text-slate-700 tabular-nums">
                        <i class="fa-regular fa-clock text-slate-400 text-xs"></i>
                        <span id="current-time">--:--:-- WIB</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden w-full flex flex-col">
                <!-- Toolbar: Search Bar & Count Badge -->
                <div class="p-3.5 border-b border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" id="monitor-search" placeholder="Cari nama atau NIP guru..."
                            class="w-full pl-9 pr-8 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <button type="button" id="monitor-search-clear" onclick="clearSearch()" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 transition" title="Hapus pencarian">
                            <i class="fa-solid fa-circle-xmark text-xs"></i>
                        </button>
                    </div>
                    <div class="text-xs text-slate-500 flex items-center gap-2 self-start sm:self-auto font-medium">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200/80 font-semibold text-[11px] shadow-2xs">
                            <i class="fa-solid fa-user-check text-[10px]"></i>
                            <span id="total-scanned-count">0 Guru Hadir</span>
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider">
                                <th class="py-3 px-4 font-semibold w-16 text-center">Profil</th>
                                <th class="py-3 px-4 font-semibold">Nama & NIP Guru</th>
                                <th class="py-3 px-4 font-semibold">Waktu Presensi</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody id="riwayat-grid" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="4" class="py-12 text-center text-slate-400 text-xs font-medium">
                                    <i class="fa-solid fa-spinner fa-spin mr-2 text-slate-300"></i> Memuat data absensi guru...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div id="pagination-wrapper" class="p-3 bg-white border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500 font-medium" id="pagination-info">
                        Menampilkan 0 dari 0 data
                    </div>
                    <div class="flex items-center gap-1.5" id="pagination-controls">
                        <button type="button" id="btn-prev-page" onclick="changePage(-1)" disabled
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-2xs">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            <span>Sebelumnya</span>
                        </button>
                        <span class="px-2.5 py-1 text-slate-700 font-semibold" id="page-indicator">
                            Halaman 1 / 1
                        </span>
                        <button type="button" id="btn-next-page" onclick="changePage(1)" disabled
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-2xs">
                            <span>Berikutnya</span>
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-5 text-center border-t border-slate-200 bg-white">
        <p class="text-xs font-medium text-slate-400 select-none">
            &copy; 2026 Nihlah.Auliya. All Rights Reserved
        </p>
    </footer>

    <!-- Libraries -->
    <script src="<?= BASEURL; ?>/assets/vendor/html5-qrcode/html5-qrcode.min.js" type="text/javascript"></script>

    <script>
        const RIWAYAT_BASEURL = '<?= BASEURL; ?>';
        const SCHOOL_LAT = <?= json_encode((float)SCHOOL_LAT); ?>;
        const SCHOOL_LNG = <?= json_encode((float)SCHOOL_LNG); ?>;
        const SCHOOL_MAX_RADIUS = <?= json_encode((int)(defined('SCHOOL_RADIUS_METER') ? SCHOOL_RADIUS_METER : 100)); ?>; // Maksimal 100 Meter
        const GPS_MAX_ACCURACY = 80; // Toleransi akurasi sinyal GPS maksimal 80m

        let monitorGeoState = {
            lat: null,
            lng: null,
            accuracy: null,
            dist: null,
            ok: false,
            checked: false,
            checking: false
        };

        function haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2)*Math.sin(dLat/2)
                    + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)
                    * Math.sin(dLng/2)*Math.sin(dLng/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        function updateGeoUI() {
            const badge = document.getElementById('monitor-geo-badge');
            const textEl = document.getElementById('monitor-geo-text');
            const lockEl = document.getElementById('monitor-geo-lock');
            const lockTitle = document.getElementById('monitor-geo-lock-title');
            const lockMsg = document.getElementById('monitor-geo-lock-msg');
            const lockDist = document.getElementById('monitor-geo-lock-dist');

            if (!badge || !textEl) return;

            if (monitorGeoState.checking) {
                badge.className = 'mb-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 shadow-xs transition-all';
                textEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Memeriksa sinyal GPS...';
                return;
            }

            if (monitorGeoState.ok) {
                badge.className = 'mb-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs transition-all';
                const distRounded = Math.round(monitorGeoState.dist);
                const accRounded = monitorGeoState.accuracy ? Math.round(monitorGeoState.accuracy) : 0;
                textEl.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i> Lokasi: ±${distRounded}m dari sekolah (Akurasi: ±${accRounded}m)`;
                if (lockEl) lockEl.classList.add('hidden');
            } else {
                badge.className = 'mb-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 shadow-xs transition-all';
                if (lockEl) lockEl.classList.remove('hidden');

                if (monitorGeoState.dist !== null && monitorGeoState.dist > SCHOOL_MAX_RADIUS) {
                    const distRounded = Math.round(monitorGeoState.dist);
                    textEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-rose-500 mr-1"></i> Di luar jangkauan (±${distRounded}m, batas 100m)`;
                    if (lockTitle) lockTitle.textContent = 'Di Luar Jangkauan Sekolah';
                    if (lockMsg) lockMsg.textContent = 'Presensi monitor guru hanya dapat dilakukan dalam radius maksimal 100 meter dari titik lokasi sekolah.';
                    if (lockDist) lockDist.textContent = `Jarak Anda saat ini: ±${distRounded} meter dari sekolah (Maksimal 100m)`;
                } else if (monitorGeoState.accuracy !== null && monitorGeoState.accuracy > GPS_MAX_ACCURACY) {
                    const accRounded = Math.round(monitorGeoState.accuracy);
                    textEl.innerHTML = `<i class="fa-solid fa-satellite-dish text-rose-500 mr-1"></i> Akurasi GPS rendah (±${accRounded}m, batas 80m)`;
                    if (lockTitle) lockTitle.textContent = 'Sinyal GPS Kurang Akurat';
                    if (lockMsg) lockMsg.textContent = 'Keakuratan GPS perangkat Anda belum memadai. Pindah ke area terbuka dan gunakan mode GPS High Accuracy.';
                    if (lockDist) lockDist.textContent = `Akurasi terdeteksi: ±${accRounded}m (Batas maksimal 80m)`;
                } else {
                    textEl.innerHTML = `<i class="fa-solid fa-location-crosshairs text-rose-500 mr-1"></i> Izin Akses GPS Diperlukan`;
                    if (lockTitle) lockTitle.textContent = 'Izin Lokasi GPS Diperlukan';
                    if (lockMsg) lockMsg.textContent = 'Mohon aktifkan GPS perangkat Anda dan berikan izin akses lokasi pada browser untuk validasi jarak 100 meter sekolah.';
                    if (lockDist) lockDist.textContent = 'Status GPS: Tidak aktif atau izin ditolak';
                }
            }
        }

        function checkMonitorGeo(isUserRetry = false) {
            if (!navigator.geolocation) {
                monitorGeoState.ok = false;
                monitorGeoState.checked = true;
                monitorGeoState.checking = false;
                updateGeoUI();
                return;
            }

            monitorGeoState.checking = true;
            updateGeoUI();

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const accuracy = pos.coords.accuracy || 0;
                    const dist = haversine(lat, lng, SCHOOL_LAT, SCHOOL_LNG);

                    monitorGeoState.lat = lat;
                    monitorGeoState.lng = lng;
                    monitorGeoState.accuracy = accuracy;
                    monitorGeoState.dist = dist;
                    monitorGeoState.checked = true;
                    monitorGeoState.checking = false;

                    if (accuracy > GPS_MAX_ACCURACY) {
                        monitorGeoState.ok = false;
                    } else if (dist <= SCHOOL_MAX_RADIUS) {
                        monitorGeoState.ok = true;
                    } else {
                        monitorGeoState.ok = false;
                    }

                    updateGeoUI();

                    if (monitorGeoState.ok && !html5Qr && !isCamStarting) {
                        initCamera();
                    }
                },
                function(err) {
                    monitorGeoState.ok = false;
                    monitorGeoState.checked = true;
                    monitorGeoState.checking = false;
                    updateGeoUI();
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Jakarta'
            });
            document.getElementById('current-time').innerText = timeString + ' WIB';
        }
        setInterval(updateClock, 1000);
        updateClock();

        let isProcessing = false;
        let lastScannedCode = null;
        let lastScannedTime = 0;
        const SAME_CODE_DEBOUNCE_MS = 2500; // Tahan kartu yang sama selama 2.5 detik agar tidak submit ganda
        const NEXT_PERSON_COOLDOWN_MS = 750; // Kartu/orang berikutnya bisa langsung scan dalam 750ms!

        const scanFrame = document.getElementById('scan-frame');
        const cameraLoading = document.getElementById('camera-loading');
        const cameraBoxEl = document.querySelector('.camera-box');

        // Audio Beep Synthesizer menggunakan Web Audio API (Instan tanpa file eksternal)
        let audioCtx = null;
        function playScanBeep(type = 'success') {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                
                if (type === 'success') {
                    // Dua nada berurutan yang jernih & ramah telinga (880Hz -> 1320Hz)
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                    osc.frequency.setValueAtTime(1320, audioCtx.currentTime + 0.07);
                    gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.16);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.16);
                } else {
                    // Buzz rendah pertanda gagal/ditolak
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(260, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.2);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.2);
                }
            } catch(e) {
                // Ignore jika browser membatasi autoplay audio
            }
        }

        function triggerBoxFlash(type) {
            if (!cameraBoxEl) return;
            cameraBoxEl.classList.remove('flash-success', 'flash-error');
            if (type === 'success') cameraBoxEl.classList.add('flash-success');
            else if (type === 'error') cameraBoxEl.classList.add('flash-error');
            setTimeout(() => {
                cameraBoxEl.classList.remove('flash-success', 'flash-error');
            }, 450);
        }

        // ── Camera & Scanner State (Mebjas html5-qrcode) ──
        let html5Qr = null;
        let availableCameras = [];
        let currentCamIndex = 0;
        let isCamStarting = false;

        const cameraSelectEl = document.getElementById('camera-select');
        const btnSwitchCamEl = document.getElementById('btn-switch-cam');
        const btnUploadScanEl = document.getElementById('btn-upload-scan');
        const monitorFileInput = document.getElementById('monitor-file-input');

        // File upload scanner handler
        if (btnUploadScanEl && monitorFileInput) {
            btnUploadScanEl.addEventListener('click', () => monitorFileInput.click());
            monitorFileInput.addEventListener('change', async function() {
                const file = this.files[0];
                if (!file) return;

                // Validasi lokasi geofence 100 meter sebelum memproses file
                if (!monitorGeoState.ok) {
                    playScanBeep('error');
                    triggerBoxFlash('error');
                    let errMsg = 'Presensi ditolak. Izin lokasi GPS diperlukan untuk absensi monitor.';
                    if (monitorGeoState.dist !== null && monitorGeoState.dist > SCHOOL_MAX_RADIUS) {
                        errMsg = `Presensi ditolak. Lokasi Anda berada di luar area sekolah (±${Math.round(monitorGeoState.dist)}m dari sekolah, batas maksimal 100m).`;
                    } else if (monitorGeoState.accuracy !== null && monitorGeoState.accuracy > GPS_MAX_ACCURACY) {
                        errMsg = `Presensi ditolak. Akurasi sinyal GPS rendah (±${Math.round(monitorGeoState.accuracy)}m, batas maksimal 80m).`;
                    }
                    showFeedback(false, errMsg);
                    this.value = '';
                    return;
                }

                showFeedback(true, 'Memindai file QR...', true);
                try {
                    const tempScanner = html5Qr || new Html5Qrcode("qr-reader", { verbose: false });
                    let decodedText = null;
                    if (typeof tempScanner.scanFileV2 === 'function') {
                        const res = await tempScanner.scanFileV2(file, true);
                        decodedText = res ? res.decodedText : null;
                    } else {
                        decodedText = await Html5Qrcode.scanFile(file, true);
                    }
                    if (decodedText) {
                        onScanSuccess(decodedText, null);
                    } else {
                        throw new Error('Tidak ada kode QR');
                    }
                } catch(err) {
                    playScanBeep('error');
                    triggerBoxFlash('error');
                    showFeedback(false, 'QR tidak terdeteksi pada gambar. Pastikan gambar jelas.');
                }
                this.value = '';
            });
        }

        // Initialize Camera with mebjas html5-qrcode standard
        async function initCamera() {
            if (isCamStarting) return;
            isCamStarting = true;

            if (!html5Qr) {
                html5Qr = new Html5Qrcode("qr-reader", { verbose: false });
            }

            const qrConfig = {
                fps: 10, // Mebjas standard: 10 FPS for optimal decoding efficiency without CPU spikes
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const size = Math.max(160, Math.floor(minEdge * 0.8));
                    return { width: size, height: size };
                },
                aspectRatio: 1.0,
                disableFlip: false
            };

            if (window.Html5QrcodeSupportedFormats) {
                qrConfig.formatsToSupport = [
                    Html5QrcodeSupportedFormats.QR_CODE,
                    Html5QrcodeSupportedFormats.CODE_128
                ];
            }

            function onReady() {
                cameraLoading.classList.add('hidden');
                scanFrame.classList.remove('hidden');
                isCamStarting = false;
            }

            function onError(err) {
                console.warn("Camera start warning:", err);
                cameraLoading.innerHTML = `
                    <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 text-amber-400"></i>
                    <p class="text-xs font-semibold text-slate-300 text-center px-4">Kamera belum siap atau izin belum diberikan.<br>Klik tombol upload QR atau muat ulang.</p>
                `;
                isCamStarting = false;
            }

            async function startTarget(target) {
                try {
                    if (html5Qr.isScanning) {
                        await html5Qr.stop();
                    }
                } catch(e) {}
                return html5Qr.start(target, qrConfig, onScanSuccess, onScanFailure);
            }

            try {
                // Discover cameras
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    availableCameras = devices;
                    
                    // Populate camera select
                    cameraSelectEl.innerHTML = '';
                    devices.forEach((dev, idx) => {
                        const opt = document.createElement('option');
                        opt.value = dev.id;
                        opt.textContent = dev.label || `Kamera ${idx + 1}`;
                        cameraSelectEl.appendChild(opt);
                    });

                    if (devices.length > 1) {
                        cameraSelectEl.classList.remove('hidden');
                        btnSwitchCamEl.classList.remove('hidden');
                    }

                    // Prioritize back camera for kiosks or first camera
                    let targetId = devices[0].id;
                    const backIndex = devices.findIndex(d => /back|belakang|rear|environment/i.test(d.label || ''));
                    if (backIndex !== -1) {
                        currentCamIndex = backIndex;
                        targetId = devices[backIndex].id;
                    }
                    cameraSelectEl.value = targetId;

                    try {
                        await startTarget(targetId);
                        onReady();
                    } catch(err1) {
                        try {
                            await startTarget({ facingMode: "environment" });
                            onReady();
                        } catch(err2) {
                            await startTarget({ facingMode: "user" });
                            onReady();
                        }
                    }
                } else {
                    // Fallback to constraints
                    try {
                        await startTarget({ facingMode: "environment" });
                        onReady();
                    } catch(err) {
                        await startTarget({ facingMode: "user" });
                        onReady();
                    }
                }
            } catch(e) {
                try {
                    await startTarget({ facingMode: "environment" });
                    onReady();
                } catch(e2) {
                    try {
                        await startTarget({ facingMode: "user" });
                        onReady();
                    } catch(e3) {
                        onError(e3);
                    }
                }
            }
        }

        // Camera select change handler
        if (cameraSelectEl) {
            cameraSelectEl.addEventListener('change', async function() {
                const selectedId = this.value;
                if (!selectedId || !html5Qr) return;
                cameraLoading.classList.remove('hidden');
                cameraLoading.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin text-3xl mb-2 text-blue-400"></i>
                    <p class="text-xs font-semibold text-white">Mengganti Kamera...</p>
                `;
                try {
                    if (html5Qr.isScanning) await html5Qr.stop();
                    const qrConfig = {
                        fps: 10,
                        qrbox: (w, h) => { const s = Math.floor(Math.min(w, h) * 0.8); return { width: s, height: s }; },
                        aspectRatio: 1.0
                    };
                    await html5Qr.start(selectedId, qrConfig, onScanSuccess, onScanFailure);
                    cameraLoading.classList.add('hidden');
                    scanFrame.classList.remove('hidden');
                } catch(e) {
                    console.error("Failed to switch camera:", e);
                }
            });
        }

        // Camera toggle button handler
        if (btnSwitchCamEl) {
            btnSwitchCamEl.addEventListener('click', function() {
                if (!availableCameras.length) return;
                currentCamIndex = (currentCamIndex + 1) % availableCameras.length;
                const nextCam = availableCameras[currentCamIndex];
                if (nextCam) {
                    cameraSelectEl.value = nextCam.id;
                    cameraSelectEl.dispatchEvent(new Event('change'));
                }
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            const now = Date.now();
            if (isProcessing) return;

            // Debounce jika kartu yang sama persis ditempel ulang dalam 2.5 detik
            if (decodedText === lastScannedCode && (now - lastScannedTime) < SAME_CODE_DEBOUNCE_MS) {
                return;
            }

            isProcessing = true;
            lastScannedCode = decodedText;
            lastScannedTime = now;

            // Validasi Geofence GPS (Maksimal 100 meter dari sekolah)
            if (!monitorGeoState.ok) {
                playScanBeep('error');
                triggerBoxFlash('error');
                let errMsg = 'Presensi ditolak. Izin lokasi GPS diperlukan untuk absensi monitor.';
                if (monitorGeoState.dist !== null && monitorGeoState.dist > SCHOOL_MAX_RADIUS) {
                    errMsg = `Presensi ditolak. Lokasi Anda berada di luar area sekolah (±${Math.round(monitorGeoState.dist)}m dari sekolah, batas maksimal 100m).`;
                } else if (monitorGeoState.accuracy !== null && monitorGeoState.accuracy > GPS_MAX_ACCURACY) {
                    errMsg = `Presensi ditolak. Akurasi sinyal GPS rendah (±${Math.round(monitorGeoState.accuracy)}m, batas maksimal 80m).`;
                }
                showFeedback(false, errMsg);
                setTimeout(() => {
                    isProcessing = false;
                }, NEXT_PERSON_COOLDOWN_MS);
                return;
            }

            // Bunyi beep dan kilatan frame instan saat barcode terbaca
            playScanBeep('success');
            triggerBoxFlash('success');
            showFeedback(true, 'Memproses absensi...', true);

            const payload = { 
                barcode: decodedText,
                source: 'monitor_guru',
                monitor_token: '<?= htmlspecialchars($data['monitor_token'] ?? ($_SESSION['monitor_token'] ?? ''), ENT_QUOTES) ?>',
                lat: monitorGeoState.lat,
                lng: monitorGeoState.lng,
                accuracy: monitorGeoState.accuracy
            };

            fetch(RIWAYAT_BASEURL + '/api/attendance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    playScanBeep('success');
                    triggerBoxFlash('success');
                    let detailMsg = res.message || 'Absensi Berhasil';
                    if (res.data && res.data.guru) {
                        const nama = res.data.guru.nama || res.data.guru.name || 'Guru';
                        const tipe = res.data.type === 'check_in' ? 'Masuk' : 'Pulang';
                        const st = (res.data.status || 'Hadir').toUpperCase();
                        detailMsg = `${nama} — ${tipe} (${st})`;
                    }
                    showFeedback(true, detailMsg);
                    fetchRiwayat();
                } else {
                    playScanBeep('error');
                    triggerBoxFlash('error');
                    showFeedback(false, res.message || 'Absensi Gagal');
                }
            })
            .catch(err => {
                playScanBeep('error');
                triggerBoxFlash('error');
                showFeedback(false, 'Terjadi kesalahan jaringan.');
            })
            .finally(() => {
                // Cooldown kilat: hanya 750ms untuk orang/kartu berikutnya!
                setTimeout(() => {
                    isProcessing = false;
                }, NEXT_PERSON_COOLDOWN_MS);
            });
        }

        function onScanFailure(error) {}

        let feedbackTimer = null;
        function showFeedback(isSuccess, message, isProcessingState = false) {
            const fb = document.getElementById('scan-feedback');
            if (feedbackTimer) clearTimeout(feedbackTimer);

            fb.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'border-red-200', 'bg-emerald-100', 'text-emerald-700', 'border-emerald-200', 'bg-blue-100', 'text-blue-700', 'border-blue-200');
            fb.classList.add('border');
            
            if (isProcessingState) {
                fb.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
                fb.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> ${message}`;
            } else if (isSuccess) {
                fb.classList.add('bg-emerald-100', 'text-emerald-700', 'border-emerald-200');
                fb.innerHTML = `<i class="fa-solid fa-circle-check mr-2"></i> ${message}`;
            } else {
                fb.classList.add('bg-red-100', 'text-red-700', 'border-red-200');
                fb.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-2"></i> ${message}`;
            }

            feedbackTimer = setTimeout(() => {
                fb.classList.add('hidden');
            }, 3000);
        }

        let allScannedData = [];
        let searchQuery = '';
        let currentPage = 1;
        const itemsPerPage = 6;

        function fetchRiwayat() {
            fetch(RIWAYAT_BASEURL + '/riwayat/get_latest_absensi_guru')
                .then(res => res.json())
                .then(res => {
                    if (res && res.success && Array.isArray(res.data)) {
                        allScannedData = res.data;
                        renderCurrentPage();
                    }
                })
                .catch(err => console.error(err));
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function clearSearch() {
            const input = document.getElementById('monitor-search');
            if (input) {
                input.value = '';
                searchQuery = '';
                const clearBtn = document.getElementById('monitor-search-clear');
                if (clearBtn) clearBtn.classList.add('hidden');
                currentPage = 1;
                renderCurrentPage();
            }
        }

        function changePage(delta) {
            currentPage += delta;
            renderCurrentPage();
        }

        function renderCurrentPage() {
            const tbody = document.getElementById('riwayat-grid');
            const totalScannedCountEl = document.getElementById('total-scanned-count');
            const paginationInfoEl = document.getElementById('pagination-info');
            const pageIndicatorEl = document.getElementById('page-indicator');
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');
            
            if (!tbody) return;

            // Total count badge
            if (totalScannedCountEl) {
                totalScannedCountEl.textContent = `${allScannedData.length} Guru Hadir`;
            }

            // Filter data by search query
            const filtered = allScannedData.filter(item => {
                if (!searchQuery) return true;
                const nameMatch = (item.name || '').toLowerCase().includes(searchQuery);
                const nipMatch = (item.nip || '').toLowerCase().includes(searchQuery);
                return nameMatch || nipMatch;
            });

            // Calculate pagination
            const totalPages = Math.max(1, Math.ceil(filtered.length / itemsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filtered.length);
            const pageItems = filtered.slice(startIndex, endIndex);

            // Update Pagination UI
            if (paginationInfoEl) {
                if (filtered.length === 0) {
                    paginationInfoEl.textContent = allScannedData.length === 0 
                        ? 'Belum ada data presensi hari ini'
                        : `Tidak ditemukan hasil untuk "${searchQuery}"`;
                } else {
                    paginationInfoEl.textContent = `Menampilkan ${startIndex + 1} - ${endIndex} dari ${filtered.length} guru ${searchQuery ? `(difilter)` : ''}`;
                }
            }

            if (pageIndicatorEl) {
                pageIndicatorEl.textContent = `Halaman ${currentPage} / ${totalPages}`;
            }

            if (btnPrev) {
                btnPrev.disabled = (currentPage <= 1);
            }
            if (btnNext) {
                btnNext.disabled = (currentPage >= totalPages);
            }

            // Empty state check
            if (filtered.length === 0) {
                if (allScannedData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-14 text-center">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-400 shadow-2xs">
                                    <i class="fa-regular fa-clipboard text-xl"></i>
                                </div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-700">Belum Ada Presensi Guru Hari Ini</h3>
                                <p class="text-[11px] sm:text-xs text-slate-400 mt-1 max-w-sm mx-auto">Riwayat presensi akan terisi otomatis saat guru melakukan scan kode QR ID atau kartu RFID.</p>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-12 text-center">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                                </div>
                                <h3 class="text-xs font-semibold text-slate-700">Pencarian Tidak Ditemukan</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada guru hadir dengan nama/NIP "${escapeHtml(searchQuery)}"</p>
                            </td>
                        </tr>
                    `;
                }
                return;
            }

            tbody.innerHTML = '';
            pageItems.forEach(item => {
                const isPulang = Boolean(item.jam_pulang && item.jam_pulang.trim() !== '');
                const rawWaktu = isPulang ? item.jam_pulang : item.jam_datang;
                const status = (item.status || '').toLowerCase();
                
                let rowHover = 'hover:bg-slate-50/60';
                let avatarBg = 'text-slate-500 bg-slate-100 border-slate-200';
                let statusBadge = '';
                let waktuHtml = '';

                // Waktu presensi display
                if (rawWaktu && rawWaktu !== 'null') {
                    const iconClock = isPulang ? 'fa-arrow-right-from-bracket text-emerald-500' : 'fa-clock text-blue-500';
                    waktuHtml = `
                        <span class="inline-flex items-center gap-1.5 font-mono text-xs font-semibold text-slate-700 tabular-nums bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">
                            <i class="fa-regular ${iconClock} text-[11px]"></i>
                            ${escapeHtml(rawWaktu)} WIB
                        </span>
                    `;
                } else {
                    waktuHtml = `
                        <span class="inline-flex items-center gap-1 text-xs text-slate-400 italic">
                            <i class="fa-solid fa-minus text-[10px]"></i> Belum scan
                        </span>
                    `;
                }

                // Status Badge & Row Theme
                if (isPulang) {
                    rowHover = 'hover:bg-emerald-50/40';
                    avatarBg = 'text-emerald-600 bg-emerald-50 border-emerald-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80"><i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i> Pulang</span>`;
                } else if (status === 'gagal') {
                    rowHover = 'hover:bg-rose-50/40';
                    avatarBg = 'text-rose-600 bg-rose-50 border-rose-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/80"><i class="fa-solid fa-ban text-[10px]"></i> Gagal Lokasi</span>`;
                } else if (status === 'hadir') {
                    rowHover = 'hover:bg-blue-50/40';
                    avatarBg = 'text-blue-600 bg-blue-50 border-blue-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80"><i class="fa-solid fa-check text-[10px]"></i> Hadir</span>`;
                } else if (status === 'terlambat') {
                    rowHover = 'hover:bg-amber-50/40';
                    avatarBg = 'text-amber-600 bg-amber-50 border-amber-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80"><i class="fa-solid fa-clock text-[10px]"></i> Terlambat</span>`;
                } else if (status === 'izin') {
                    rowHover = 'hover:bg-purple-50/40';
                    avatarBg = 'text-purple-600 bg-purple-50 border-purple-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200/80"><i class="fa-solid fa-file-lines text-[10px]"></i> Izin</span>`;
                } else if (status === 'sakit') {
                    rowHover = 'hover:bg-amber-50/40';
                    avatarBg = 'text-amber-600 bg-amber-50 border-amber-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/80"><i class="fa-solid fa-notes-medical text-[10px]"></i> Sakit</span>`;
                } else {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">${escapeHtml(status ? status.toUpperCase() : '-')}</span>`;
                }

                const profileAvatar = item.avatar 
                    ? `<img src="${RIWAYAT_BASEURL}/uploads/avatars/${escapeHtml(item.avatar)}" alt="${escapeHtml(item.name)}" class="w-full h-full object-cover">`
                    : `<i class="fa-solid fa-user-tie text-xs"></i>`;

                tbody.innerHTML += `
                    <tr class="transition-colors ${rowHover}">
                        <td class="py-3 px-4 text-center align-middle">
                            <div class="w-9 h-9 mx-auto rounded-full flex items-center justify-center shrink-0 overflow-hidden border ${avatarBg}">
                                ${profileAvatar}
                            </div>
                        </td>
                        <td class="py-3 px-4 align-middle">
                            <div class="font-semibold text-slate-800 text-sm leading-snug">${escapeHtml(item.name || '-')}</div>
                            <div class="text-xs text-slate-500 leading-snug flex items-center gap-1 mt-0.5">
                                <span class="font-mono text-[11px] text-slate-500">NIP: ${escapeHtml(item.nip || '-')}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 align-middle">
                            ${waktuHtml}
                        </td>
                        <td class="py-3 px-4 align-middle">
                            ${statusBadge}
                        </td>
                    </tr>
                `;
            });
        }

        // Event listener search input
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('monitor-search');
            const clearBtn = document.getElementById('monitor-search-clear');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    searchQuery = e.target.value.trim().toLowerCase();
                    if (clearBtn) {
                        if (searchQuery.length > 0) {
                            clearBtn.classList.remove('hidden');
                        } else {
                            clearBtn.classList.add('hidden');
                        }
                    }
                    currentPage = 1;
                    renderCurrentPage();
                });
            }
        });

        // Panggil pertama kali data riwayat
        fetchRiwayat();

        // Polling live setiap 4 detik untuk update realtime monitor guru
        setInterval(fetchRiwayat, 4000);

        // Mulai periksa lokasi GPS sekolah (Maksimal 100 meter)
        checkMonitorGeo();

        // Cek lokasi GPS secara berkala setiap 25 detik
        setInterval(() => {
            checkMonitorGeo();
        }, 25000);

        // Inisialisasi kamera scanner
        initCamera();
    </script>
</body>
</html>
