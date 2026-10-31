<div class="w-full space-y-6">
    
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- HEADER & BREADCRUMB SECTION                                             -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- HEADER SECTION                                                          -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="flex items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Konfigurasi mode ESP
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= BASEURL; ?>/dashboard/admin" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold border border-slate-200 dark:border-slate-600 transition inline-flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-arrow-left text-[11px]"></i> Dashboard
            </a>
        </div>
    </div>

    <?php
    $devices = !empty($data['devices']) ? $data['devices'] : [];
    $activeDevice = $data['selectedDevice'] ?? (!empty($devices) ? $devices[0] : null);
    $activeIp = $activeDevice ? ($activeDevice->ip_address ?? '') : '';
    if ($activeIp === '-' || $activeIp === '0.0.0.0') $activeIp = '';
    
    $serverIp   = $data['serverIp'] ?? '192.168.1.100';
    $serverPort = $data['serverPort'] ?? '80';
    $serverHost = $data['serverHost'] ?? "http://{$serverIp}";
    $fullApiEndpoint = $data['fullApiEndpoint'] ?? (rtrim(BASEURL, '/') . '/api/iot/attendance/student');
    $heartbeatEndpoint = $data['heartbeatEndpoint'] ?? (rtrim(BASEURL, '/') . '/api/iot/heartbeat');
    $isLocalhost = (strpos(BASEURL, 'localhost') !== false || strpos(BASEURL, '127.0.0.1') !== false);
    ?>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- TAB PEMILIHAN MODE ESP (CLIENT & AP)                                    -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200 dark:border-slate-700 w-full sm:w-auto self-start">
        <button type="button" id="tabModeClient" onclick="handleTabClick('client')"
                class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs border border-slate-200/80 dark:border-slate-600 inline-flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-wifi text-emerald-600 dark:text-emerald-400"></i>
            <span>1. Mode Client (Jaringan Sekolah & Ping ESP)</span>
        </button>
        <button type="button" id="tabModeAp" onclick="handleTabClick('ap')"
                class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white inline-flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-tower-broadcast text-amber-600 dark:text-amber-400"></i>
            <span>2. Mode Access Point (Hotspot Pengaturan)</span>
        </button>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- KONTEN TAB 1: MODE CLIENT (UJI PING & KONEKSI ESP)                      -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div id="sectionModeClient" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Kolom Kiri: Status Koneksi & Input IP ESP -->
            <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4">
                
                <!-- Status Row -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Status Koneksi Ping</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span id="clientStatusBadge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                <span class="w-2 h-2 rounded-full bg-slate-400" id="clientStatusDot"></span>
                                <span id="clientStatusText">Belum Diuji</span>
                            </span>
                            <span id="clientLatencyBadge" class="text-xs font-mono font-medium text-slate-500 dark:text-slate-400"></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Target Host Server</span>
                        <span class="text-xs font-mono font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-500/20" id="currentHostBadge"><?= htmlspecialchars($serverHost) ?></span>
                    </div>
                </div>

                <!-- Input IP Section -->
                <div>
                    <label for="inputEspIp" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Alamat IP ESP (Mode Client)
                    </label>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-network-wired text-xs"></i>
                            </div>
                            <input type="text" id="inputEspIp" value="<?= htmlspecialchars($activeIp) ?>"
                                   placeholder="Contoh: 192.168.1.50"
                                   class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-mono font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-emerald-600 focus:bg-white dark:focus:bg-slate-900 transition shadow-xs">
                        </div>
                        <button type="button" onclick="pingEspDevice(false)" id="btnPingEsp"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-xs inline-flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                            <i class="fa-solid fa-bolt text-[11px]"></i> Cek Ping
                        </button>
                        <button type="button" onclick="saveEspIp()" id="btnSaveIp"
                                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-lg text-xs transition shadow-xs inline-flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                            <i class="fa-solid fa-floppy-disk text-[11px]"></i> Simpan IP
                        </button>
                    </div>
                </div>

                <!-- Alert Sinkronisasi Host Server ESP (Jika Berbeda) -->
                <div id="hostMismatchWarning" class="hidden p-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl text-xs text-amber-950 dark:text-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                        </div>
                        <div>
                            <span class="font-bold text-amber-900 dark:text-amber-200 block">Host ESP Berbeda: <span id="mismatchHostEsp" class="font-mono text-rose-600 dark:text-rose-400">192.168.4.100</span></span>
                            <span class="text-[11px] text-amber-800 dark:text-amber-300">ESP mengirim data absen ke IP di atas. Samakan ke <strong><?= htmlspecialchars($serverHost) ?></strong>.</span>
                        </div>
                    </div>
                    <button type="button" onclick="syncHostToEsp()" id="btnSyncHost"
                            class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs transition shadow-xs inline-flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate text-[11px]"></i> Sinkronkan ke ESP
                    </button>
                </div>

            </div>

            <!-- Kolom Kanan: Status Layar LCD Fisik -->
            <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Status Tampilan LCD Fisik</h3>
                    <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600">LCD 16x2</span>
                </div>

                <!-- Info Box Tampilan LCD Sederhana & Bersih -->
                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700 font-mono text-xs space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700/80 pb-2 text-slate-700 dark:text-slate-200" id="lcdPreviewLine1">
                        <span>IP ESP (Client):</span>
                        <span class="text-[10px] text-slate-400 font-sans font-bold">STANDBY</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-800 dark:text-slate-100 font-bold" id="lcdPreviewLine2">
                        <span>IP: <?= htmlspecialchars($activeIp ?: '192.168.1.15') ?></span>
                        <span class="text-[10px] text-slate-400"><i class="fa-solid fa-wifi text-[10px]"></i></span>
                    </div>
                </div>

                <!-- Tombol Tindakan -->
                <div class="flex items-center gap-2 pt-1">
                    <button type="button" onclick="testCardScanAnimation()" class="flex-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-lg text-xs transition cursor-pointer inline-flex items-center justify-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-id-card text-emerald-600 dark:text-emerald-400 text-[11px]"></i> Tes Simulasi LCD
                    </button>
                    <button type="button" onclick="requestSwitchMode('ap')" class="px-3 py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 font-semibold rounded-lg text-xs transition cursor-pointer inline-flex items-center justify-center gap-1.5 shadow-xs shrink-0">
                        <i class="fa-solid fa-tower-broadcast text-amber-600 dark:text-amber-400 text-[10px]"></i> Beralih ke AP
                    </button>
                </div>
            </div>

        </div>

        <!-- Input Hidden untuk referensi ID dan IP Perangkat Aktif -->
        <input type="hidden" id="formDeviceId" value="<?= $activeDevice ? $activeDevice->id : 1 ?>">
        <input type="hidden" id="formHiddenIp" value="<?= htmlspecialchars($activeIp) ?>">

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- KONTEN TAB 2: MODE ACCESS POINT (GANTI WI-FI & SANDI ESP)               -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div id="sectionModeAp" class="hidden space-y-6">
        
        <!-- Bar Status Mode AP -->
        <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl p-4 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 flex items-center justify-center shrink-0 border border-amber-200 dark:border-amber-500/30 text-sm">
                        <i class="fa-solid fa-tower-broadcast"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-amber-950 dark:text-amber-200">Mode Access Point (Hotspot Pengaturan)</h3>
                        <p class="text-[11px] text-amber-800 dark:text-amber-300">ESP otomatis kembali ke Mode Client setelah konfigurasi disimpan.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <button type="button" id="btnCheckApStatus" onclick="checkIfConnectedToClient()"
                            class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <i id="iconCheckApStatus" class="fa-solid fa-arrows-rotate text-[11px]"></i>
                        <span>Cek Status Terhubung</span>
                    </button>
                    <button type="button" onclick="requestSwitchMode('client')"
                            class="px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-arrow-rotate-left text-[11px]"></i>
                        <span>Kembali ke Client</span>
                    </button>
                    <div class="bg-white dark:bg-slate-800 px-3 py-1 rounded-lg border border-amber-200 dark:border-amber-500/30 text-center shrink-0 shadow-xs">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Timer AP</span>
                        <span class="text-xs font-mono font-bold text-amber-800 dark:text-amber-400" id="apTimerDisplay">05:00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid Kredensial Wi-Fi AP & Info Layar LCD -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Kolom Kiri: Kredensial Hotspot ESP -->
            <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Kredensial Wi-Fi Hotspot</h3>
                    <span class="text-[11px] font-mono text-amber-800 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-500/20">192.168.4.1</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Nama Wi-Fi (SSID)</span>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-slate-800 dark:text-slate-100">ESP-ABSENSI-RFID</span>
                            <button type="button" onclick="copyToClipboard('ESP-ABSENSI-RFID', this)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-xs transition cursor-pointer" title="Salin">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kata Sandi Wi-Fi</span>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-slate-800 dark:text-slate-100">12345678</span>
                            <button type="button" onclick="copyToClipboard('12345678', this)" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-xs transition cursor-pointer" title="Salin">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="sm:col-span-2 p-3.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider block">Web Portal Konfigurasi</span>
                            <a href="http://192.168.4.1" target="_blank" class="text-xs font-mono font-bold text-emerald-700 dark:text-emerald-400 hover:underline flex items-center gap-1.5">
                                <span>http://192.168.4.1</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        </div>
                        <a href="http://192.168.4.1" target="_blank" class="px-3 py-1.5 bg-white dark:bg-slate-800 hover:bg-emerald-50 text-emerald-800 dark:text-emerald-300 rounded-lg text-xs font-semibold border border-emerald-200 dark:border-emerald-500/30 shadow-xs transition">
                            Buka Portal
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Info LCD Layar AP -->
            <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Layar LCD (Mode AP)</h3>
                    <span class="text-[11px] font-mono text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-500/20">Hotspot</span>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700 font-mono text-xs space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700/80 pb-2 text-slate-700 dark:text-slate-200">
                        <span>AP: ESP-ABSENSI</span>
                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-sans font-bold">5 MENIT</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-800 dark:text-slate-100 font-bold">
                        <span>PW: 12345678</span>
                        <span class="text-[10px] text-slate-400"><i class="fa-solid fa-tower-broadcast text-[10px]"></i></span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- RIWAYAT DIAGNOSTIK & LOG PING JARINGAN (MINIMALIS MONOSPACE)            -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">
                <i class="fa-solid fa-terminal text-emerald-600 dark:text-emerald-400"></i>
                <span>Log Diagnostik Jaringan & Aktivitas IoT</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-slate-400 font-mono" id="logCountBadge">0 entri</span>
                <button type="button" onclick="clearConsoleLog()" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-medium px-2 py-0.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer">
                    Bersihkan Log
                </button>
            </div>
        </div>

        <div id="pingLogConsole" class="bg-slate-900 dark:bg-slate-950 rounded-lg p-3 font-mono text-xs text-slate-300 max-h-48 overflow-y-auto space-y-1 border border-slate-800">
            <div class="text-slate-500">[System Ready] Menunggu pengujian konektivitas perangkat ESP...</div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- SCRIPT LOGIKA JAVASCRIPT & SINKRONISASI REALTIME                            -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<script>
const BASE_URL = '<?= rtrim(BASEURL, "/") ?>';
const SERVER_HOST = '<?= htmlspecialchars($serverHost) ?>';
let allDevices = <?= json_encode($devices) ?>;
let currentDeviceId = <?= $activeDevice ? (int)$activeDevice->id : 1 ?>;
let currentActiveMode = 'client';
let apTimerInterval = null;
let apRemainingSeconds = 300;
let statusPollingInterval = null;
let logCounter = 1;

// ── Utilitas Logging ke Dark Terminal ─────────────────────────────────────────
function logToConsole(msg, type = 'info') {
    const con = document.getElementById('pingLogConsole');
    if (!con) return;
    const now = new Date().toLocaleTimeString('id-ID');
    const color = type === 'success' ? 'text-emerald-400' 
                : (type === 'error' ? 'text-rose-400' 
                : (type === 'warn' ? 'text-amber-400' 
                : 'text-slate-300'));
    
    const icon = type === 'success' ? '<i class="fa-solid fa-check text-[10px] mr-1"></i>' 
               : (type === 'error' ? '<i class="fa-solid fa-xmark text-[10px] mr-1"></i>' 
               : (type === 'warn' ? '<i class="fa-solid fa-triangle-exclamation text-[10px] mr-1"></i>' 
               : '<i class="fa-solid fa-angle-right text-[10px] mr-1"></i>'));

    const div = document.createElement('div');
    div.className = `${color} leading-relaxed`;
    div.innerHTML = `<span class="text-slate-500 font-normal">[${now}]</span> ${icon}${msg}`;
    con.appendChild(div);
    con.scrollTop = con.scrollHeight;

    logCounter++;
    const countBadge = document.getElementById('logCountBadge');
    if (countBadge) countBadge.textContent = `${logCounter} entri`;
}

function clearConsoleLog() {
    const con = document.getElementById('pingLogConsole');
    if (con) {
        con.innerHTML = '<div class="text-slate-500">[System Log Cleaned] Siap menerima log diagnostik baru...</div>';
    }
    logCounter = 0;
    const countBadge = document.getElementById('logCountBadge');
    if (countBadge) countBadge.textContent = '0 entri';
}

// ── Switch Perangkat Aktif ───────────────────────────────────────────────────
function changeActiveDevice(deviceId) {
    const dev = allDevices.find(d => d.id == deviceId);
    if (!dev) return;

    currentDeviceId = dev.id;
    const formDevId = document.getElementById('formDeviceId');
    if (formDevId) formDevId.value = dev.id;

    // Update Label Header (jika elemen tersedia di DOM)
    const elCode = document.getElementById('labelDeviceCode');
    if (elCode) elCode.textContent = dev.device_code;
    const elLoc = document.getElementById('labelDeviceLocation');
    if (elLoc) elLoc.textContent = dev.location || 'Gerbang Utama';
    const elSeen = document.getElementById('labelLastSeen');
    if (elSeen) elSeen.textContent = dev.last_seen || '-';

    // Status Badge
    const isOnline = (dev.iot_status === 'online');
    const statusBadge = document.getElementById('badgeDeviceStatus');
    const statusDot = document.getElementById('dotDeviceStatus');
    const statusText = document.getElementById('textDeviceStatus');
    if (statusBadge) {
        if (isOnline) {
            statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20';
            if (statusDot) statusDot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
            if (statusText) statusText.textContent = 'ONLINE';
        } else {
            statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600';
            if (statusDot) statusDot.className = 'w-2 h-2 rounded-full bg-slate-400';
            if (statusText) statusText.textContent = 'OFFLINE';
        }
    }

    // Input IP & Display
    const cleanIp = (dev.ip_address && dev.ip_address !== '-' && dev.ip_address !== '0.0.0.0') ? dev.ip_address : '';
    const inputEspIp = document.getElementById('inputEspIp');
    if (inputEspIp) inputEspIp.value = cleanIp;
    const formHiddenIp = document.getElementById('formHiddenIp');
    if (formHiddenIp) formHiddenIp.value = cleanIp;

    // LCD Line 2
    const lcdLine2 = document.getElementById('lcdPreviewLine2');
    if (lcdLine2) {
        lcdLine2.innerHTML = `<span>IP: ${cleanIp || '192.168.1.15'}</span><span class="text-[10px] text-slate-400"><i class="fa-solid fa-wifi text-[10px]"></i></span>`;
    }

    logToConsole(`Beralih ke perangkat: ${dev.device_code} (${dev.device_name})`);

    // Otomatis uji ping jika ada IP
    if (cleanIp) {
        pingEspDevice(true);
    }
}

// ── Tab Management (Client & AP) ─────────────────────────────────────────────
async function handleTabClick(mode) {
    if (mode === currentActiveMode) return;

    if (mode === 'ap') {
        switchIotMode('ap');
        const ip = document.getElementById('inputEspIp') ? document.getElementById('inputEspIp').value.trim() : '';
        if (ip) {
            const kirim = confirm(`Beralih ke Mode Access Point (AP)?\n\nJika Ya, perintah akan dikirim ke ESP (${ip}) agar memancarkan hotspot Wi-Fi 'ESP-ABSENSI-RFID'.\n\nKlik OK untuk mengirim perintah ke ESP sekarang, atau Batal untuk melihat informasi hotspot saja.`);
            if (kirim) {
                await requestSwitchMode('ap', false);
            }
        }
    } else {
        switchIotMode('client');
        const ip = document.getElementById('inputEspIp') ? document.getElementById('inputEspIp').value.trim() : '';
        if (ip && currentActiveMode === 'ap') {
            const kirim = confirm("Kembalikan mikrokontroler ESP ke Mode Client (Wi-Fi Lokal)?");
            if (kirim) {
                await requestSwitchMode('client', false);
            }
        }
    }
}

function switchIotMode(mode) {
    currentActiveMode = mode;
    const secClient = document.getElementById('sectionModeClient');
    const secAp     = document.getElementById('sectionModeAp');

    const tabClient = document.getElementById('tabModeClient');
    const tabAp     = document.getElementById('tabModeAp');

    const activeClasses = "flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs border border-slate-200/80 dark:border-slate-600 inline-flex items-center justify-center gap-2 cursor-pointer";
    const inactiveClasses = "flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white inline-flex items-center justify-center gap-2 cursor-pointer";

    if (secClient) secClient.classList.add('hidden');
    if (secAp) secAp.classList.add('hidden');

    if (tabClient) tabClient.className = inactiveClasses;
    if (tabAp) tabAp.className = inactiveClasses;

    if (mode === 'client') {
        if (secClient) secClient.classList.remove('hidden');
        if (tabClient) tabClient.className = activeClasses;
        stopApTimer();
        logToConsole("Mode aktif: Mode Client (Jaringan Sekolah & Ping ESP)");
    } else if (mode === 'ap') {
        if (secAp) secAp.classList.remove('hidden');
        if (tabAp) tabAp.className = activeClasses;
        startApTimer();
        logToConsole("Mode aktif: Mode Access Point (Hotspot Pengaturan)", 'warn');
    }
}

// ── Timer Countdown 5 Menit Mode AP ──────────────────────────────────────────
function startApTimer() {
    stopApTimer();
    apRemainingSeconds = 300;
    updateTimerDisplay();
    apTimerInterval = setInterval(() => {
        apRemainingSeconds--;
        updateTimerDisplay();
        if (apRemainingSeconds <= 0) {
            stopApTimer();
            logToConsole("Batas waktu 5 menit Mode AP telah selesai. ESP otomatis kembali ke Mode Client.", 'warn');
            alert("Batas waktu 5 menit Mode AP telah selesai. Mikrokontroler otomatis kembali ke Mode Client.");
            switchIotMode('client');
        }
    }, 1000);
}

function stopApTimer() {
    if (apTimerInterval) {
        clearInterval(apTimerInterval);
        apTimerInterval = null;
    }
}

function updateTimerDisplay() {
    const mins = Math.floor(apRemainingSeconds / 60);
    const secs = apRemainingSeconds % 60;
    const str = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    const el = document.getElementById('apTimerDisplay');
    if (el) el.textContent = str;
}

async function checkIfConnectedToClient() {
    const btn = document.getElementById('btnCheckApStatus');
    const icon = document.getElementById('iconCheckApStatus');
    if (icon) icon.classList.add('fa-spin');
    if (btn) btn.disabled = true;

    logToConsole("Memeriksa apakah ESP telah selesai konfigurasi AP dan terhubung ke Wi-Fi...");
    await refreshDeviceState();

    if (icon) icon.classList.remove('fa-spin');
    if (btn) btn.disabled = false;

    const dev = allDevices.find(d => d.id == currentDeviceId);
    if (dev && (dev.iot_status === 'online' || (dev.ip_address && dev.ip_address !== '-' && dev.ip_address !== '0.0.0.0' && dev.ip_address !== '192.168.4.1'))) {
        logToConsole(`ESP terdeteksi sudah berhasil masuk Mode Client dengan IP: ${dev.ip_address}!`, 'success');
        alert(`ESP Berhasil Terhubung ke Wi-Fi!\nIP Mikrokontroler: ${dev.ip_address}\n\nHalaman akan otomatis beralih ke Mode Client.`);
        switchIotMode('client');
        const inputIp = document.getElementById('inputEspIp');
        if (inputIp) inputIp.value = dev.ip_address;
        pingEspDevice(false);
    } else {
        alert("ESP belum terdeteksi online di Wi-Fi router. Jika baru saja klik Simpan di portal 192.168.4.1, mohon tunggu beberapa detik hingga ESP selesai menyambung, lalu klik tombol ini lagi.");
    }
}

// ── Fungsi Ping ESP & Sinkronisasi Live ───────────────────────────────────────
async function pingEspDevice(silent = false) {
    const inputIp = document.getElementById('inputEspIp');
    let ip = inputIp ? inputIp.value.trim() : '';
    ip = ip.replace(/^https?:\/\//i, '').replace(/\/.*$/, '').trim();
    if (inputIp) inputIp.value = ip;

    const btn = document.getElementById('btnPingEsp');
    const topIcon = document.getElementById('topRefreshIcon');
    const statusBadge = document.getElementById('clientStatusBadge');
    const statusDot = document.getElementById('clientStatusDot');
    const statusText = document.getElementById('clientStatusText');
    const latencyBadge = document.getElementById('clientLatencyBadge');
    const lcdLine1 = document.getElementById('lcdPreviewLine1');
    const lcdLine2 = document.getElementById('lcdPreviewLine2');

    if (!ip) {
        if (!silent) alert("Silakan masukkan alamat IP ESP terlebih dahulu!");
        return;
    }

    if (lcdLine1 && lcdLine2) {
        lcdLine1.innerHTML = `<span>IP ESP (Client):</span><span class="text-[10px] text-amber-500 font-bold">UJI PING</span>`;
        lcdLine2.innerHTML = `<span>IP: ${ip}</span><span class="text-[10px] text-slate-400"><i class="fa-solid fa-spinner fa-spin text-[10px]"></i></span>`;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-[11px]"></i> Memeriksa...`;
    }
    if (topIcon) topIcon.classList.add('fa-spin');

    logToConsole(`Mengirim uji ping ke http://${ip}/ping ...`);

    try {
        const formData = new FormData();
        formData.append('ip', ip);
        formData.append('device_id', currentDeviceId);

        const res = await fetch(`${BASE_URL}/admin/iot_ping`, {
            method: 'POST',
            body: formData
        });
        const json = await res.json();

        if (json.success && json.status === 'connected') {
            if (statusBadge) statusBadge.className = "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 shadow-xs";
            if (statusDot) statusDot.className = "w-2 h-2 rounded-full bg-emerald-500 animate-pulse";
            if (statusText) statusText.textContent = "CONNECTED";
            if (latencyBadge) {
                latencyBadge.textContent = `${json.latency} ms`;
                latencyBadge.className = "text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400";
            }

            // Update status di header bar
            const badgeTop = document.getElementById('badgeDeviceStatus');
            const dotTop = document.getElementById('dotDeviceStatus');
            const textTop = document.getElementById('textDeviceStatus');
            if (badgeTop) badgeTop.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20';
            if (dotTop) dotTop.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
            if (textTop) textTop.textContent = 'ONLINE';

            // LCD status
            if (lcdLine1 && lcdLine2) {
                lcdLine1.innerHTML = `<span>Connected!</span><span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">OK</span>`;
                lcdLine2.innerHTML = `<span>Web Terhubung</span><span class="text-[10px] text-slate-400"><i class="fa-solid fa-wifi"></i></span>`;
                setTimeout(() => {
                    lcdLine1.innerHTML = `<span>Sistem Absensi</span><span class="text-[10px] text-slate-400 font-bold">READY</span>`;
                    lcdLine2.innerHTML = `<span>Tempelkan Kartu</span><span class="text-[10px] text-slate-400"><i class="fa-solid fa-id-card"></i></span>`;
                }, 2000);
            }

            // Cek host mismatch
            const espHost = (json.esp_data && json.esp_data.server_host) ? json.esp_data.server_host : '';
            const hostSyncBox = document.getElementById('hostMismatchWarning');
            const mismatchEl = document.getElementById('mismatchHostEsp');
            if (hostSyncBox && mismatchEl) {
                if (espHost && espHost.replace(/\/$/, '').toLowerCase() !== SERVER_HOST.replace(/\/$/, '').toLowerCase()) {
                    hostSyncBox.classList.remove('hidden');
                    mismatchEl.textContent = espHost;
                    logToConsole(`Host di ESP (${espHost}) berbeda dengan Server (${SERVER_HOST}). Klik 'Sinkronkan ke ESP'.`, 'warn');
                } else {
                    hostSyncBox.classList.add('hidden');
                }
            }

            logToConsole(`Ping sukses: ${ip} CONNECTED (${json.latency} ms).`, 'success');
        } else {
            if (statusBadge) statusBadge.className = "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 shadow-xs";
            if (statusDot) statusDot.className = "w-2 h-2 rounded-full bg-rose-600";
            if (statusText) statusText.textContent = "DISCONNECTED";
            if (latencyBadge) {
                latencyBadge.textContent = "Timeout";
                latencyBadge.className = "text-xs font-mono font-bold text-rose-600 dark:text-rose-400";
            }

            logToConsole(`Ping gagal: ${ip} tidak merespon (${json.message || 'Host Unreachable'}).`, 'error');
        }
    } catch (err) {
        if (statusBadge) statusBadge.className = "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 shadow-xs";
        if (statusDot) statusDot.className = "w-2 h-2 rounded-full bg-rose-600";
        if (statusText) statusText.textContent = "DISCONNECTED";
        if (latencyBadge) {
            latencyBadge.textContent = "Error";
            latencyBadge.className = "text-xs font-mono font-bold text-rose-600 dark:text-rose-400";
        }
        logToConsole(`Error koneksi ping: ${err.message}`, 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-bolt text-[11px]"></i> Cek Ping`;
        }
        if (topIcon) topIcon.classList.remove('fa-spin');
    }
}

// ── Simpan Alamat IP ke Database ─────────────────────────────────────────────
async function saveEspIp() {
    const ip = document.getElementById('inputEspIp').value.trim();
    const btn = document.getElementById('btnSaveIp');
    if (!ip) {
        alert("Masukkan IP terlebih dahulu!");
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-[11px]"></i> Menyimpan...`;

    try {
        const fd = new FormData();
        fd.append('id', currentDeviceId);
        fd.append('ip', ip);

        const res = await fetch(`${BASE_URL}/admin/iot_save_ip`, {
            method: 'POST',
            body: fd
        });
        const json = await res.json();
        if (json.success) {
            document.getElementById('formHiddenIp').value = ip;
            logToConsole(`Alamat IP ${ip} tersimpan ke database`, 'success');
            alert(json.message);
        } else {
            alert(json.message || "Gagal menyimpan IP");
        }
    } catch (e) {
        alert("Terjadi kesalahan saat menyimpan IP.");
        logToConsole("Gagal menyimpan IP ke server", 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-floppy-disk text-[11px]"></i> Simpan IP`;
    }
}

// ── Sinkronkan Host Server Komputer ke ESP ──────────────────────────────────
async function syncHostToEsp() {
    const ip = document.getElementById('inputEspIp') ? document.getElementById('inputEspIp').value.trim() : '';
    const btn = document.getElementById('btnSyncHost');
    if (!ip) {
        alert("Masukkan IP ESP terlebih dahulu!");
        return;
    }

    if (!confirm(`Kirim host server '${SERVER_HOST}' langsung ke ESP (${ip})?\n\nAlat akan otomatis menyimpan host baru dan merestart dalam 1 detik.`)) {
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-[11px]"></i> Mengirim...`;
    }

    logToConsole(`Mengirim sinkronisasi Host (${SERVER_HOST}) ke ESP di ${ip}...`);

    try {
        const fd = new FormData();
        fd.append('id', currentDeviceId);
        fd.append('ip', ip);
        fd.append('host', SERVER_HOST);

        const res = await fetch(`${BASE_URL}/admin/iot_save_config`, {
            method: 'POST',
            body: fd
        });
        const json = await res.json();
        alert(json.message);
        logToConsole(json.message, json.direct_push ? 'success' : 'warn');

        const hostSyncBox = document.getElementById('hostMismatchWarning');
        if (hostSyncBox) hostSyncBox.classList.add('hidden');

        logToConsole("Menunggu ESP restart dengan Host baru...", "info");
        setTimeout(() => {
            pingEspDevice(false);
        }, 3500);
    } catch (e) {
        alert("Gagal mengirim sinkronisasi host: " + e.message);
        logToConsole("Gagal mengirim konfigurasi host: " + e.message, 'error');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-arrows-rotate text-[11px]"></i> Sinkronkan ke ESP`;
        }
    }
}

// ── Switch Mode Remote ke ESP ────────────────────────────────────────────────
async function requestSwitchMode(targetMode, needConfirm = true) {
    const ip = document.getElementById('inputEspIp') ? document.getElementById('inputEspIp').value.trim() : '';
    if (!ip) {
        alert("Masukkan IP ESP terlebih dahulu pada kolom IP di atas!");
        return;
    }

    if (needConfirm) {
        const msg = targetMode === 'ap' 
            ? `Alihkan mikrokontroler (${ip}) ke Mode Access Point?\n\nAlat akan memancarkan hotspot Wi-Fi 'ESP-ABSENSI-RFID' selama 5 menit.`
            : `Kembalikan mikrokontroler (${ip}) ke Mode Client (Wi-Fi Lokal)?`;
        if (!confirm(msg)) return;
    }

    logToConsole(`Mengirim sinyal ganti Mode ${targetMode.toUpperCase()} ke http://${ip}/switch_mode...`);

    try {
        const fd = new FormData();
        fd.append('ip', ip);
        fd.append('mode', targetMode);

        const res = await fetch(`${BASE_URL}/admin/iot_switch_mode`, {
            method: 'POST',
            body: fd
        });
        const json = await res.json();
        alert(json.message);
        if (json.success) {
            switchIotMode(targetMode);
            logToConsole(json.message, 'success');
        } else {
            logToConsole(json.message, 'error');
        }
    } catch (e) {
        alert("Gagal menghubungi ESP di " + ip + ". Pastikan ESP menyala dan berada di jaringan yang sama.");
        logToConsole("Gagal mengirim perintah ganti mode ke ESP", 'error');
    }
}

// ── Refresh Status Perangkat dari Database ───────────────────────────────────
async function refreshDeviceState() {
    try {
        const res = await fetch(`${BASE_URL}/admin/iot_get_status?id=${currentDeviceId}`);
        const json = await res.json();
        if (json.success && json.device) {
            const dev = json.device;
            const idx = allDevices.findIndex(d => d.id == dev.id);
            if (idx >= 0) allDevices[idx] = dev;

            const elSeen = document.getElementById('labelLastSeen');
            if (elSeen) elSeen.textContent = dev.last_seen || '-';

            const labelScan = document.getElementById('labelLastScannedUid');
            if (labelScan && dev.last_scanned_uid) {
                labelScan.textContent = dev.last_scanned_uid;
            }

            const cleanIp = (dev.ip_address && dev.ip_address !== '-' && dev.ip_address !== '0.0.0.0') ? dev.ip_address : '';
            if (cleanIp) {
                const inputEspIp = document.getElementById('inputEspIp');
                if (inputEspIp && (!inputEspIp.value || inputEspIp.value === '192.168.1.15')) {
                    inputEspIp.value = cleanIp;
                    const hiddenIp = document.getElementById('formHiddenIp');
                    if (hiddenIp) hiddenIp.value = cleanIp;
                }
            }

            if (currentActiveMode === 'ap' && dev.iot_status === 'online' && cleanIp && cleanIp !== '192.168.4.1') {
                logToConsole(`ESP terdeteksi online di Wi-Fi lokal (${cleanIp}). Mengalihkan ke Mode Client...`, 'success');
                switchIotMode('client');
                const inputEspIp = document.getElementById('inputEspIp');
                if (inputEspIp) inputEspIp.value = cleanIp;
                pingEspDevice(true);
            }
        }
    } catch (e) {
        console.error("Gagal refresh status background:", e);
    }
}

function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check text-emerald-600"></i>';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}

// ── Simulasi Animasi Scan Kartu pada LCD ──────────────────────────────────────
function testCardScanAnimation() {
    const lcd1 = document.getElementById('lcdPreviewLine1');
    const lcd2 = document.getElementById('lcdPreviewLine2');
    if (!lcd1 || !lcd2) return;

    logToConsole('Memulai pengujian pratinjau scan kartu pada LCD...');

    lcd1.innerHTML = `<span>Scan: A3F82C91</span><span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">READ</span>`;
    lcd2.innerHTML = `<span>Membaca Kartu...</span><span class="text-[10px] text-slate-400"><i class="fa-solid fa-spinner fa-spin text-[10px]"></i></span>`;

    setTimeout(() => {
        lcd1.innerHTML = `<span>Ahmad Fauzi</span><span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">HADIR</span>`;
        lcd2.innerHTML = `<span>06:45 WIB - Tepat</span><span class="text-[10px] text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check"></i></span>`;

        logToConsole('Simulasi presensi sukses: Ahmad Fauzi (Hadir Tepat Waktu - 06:45 WIB)', 'success');

        setTimeout(() => {
            const ip = document.getElementById('inputEspIp').value.trim() || '192.168.1.15';
            lcd1.innerHTML = `<span>Sistem Absensi</span><span class="text-[10px] text-slate-400 font-bold">READY</span>`;
            lcd2.innerHTML = `<span>IP: ${ip}</span><span class="text-[10px] text-slate-400"><i class="fa-solid fa-wifi"></i></span>`;
        }, 2500);
    }, 1200);
}

// ── Inisialisasi saat Halaman Selesai Dimuat ──────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const initIp = document.getElementById('inputEspIp') ? document.getElementById('inputEspIp').value.trim() : '';
    if (initIp) {
        pingEspDevice(true);
    }

    statusPollingInterval = setInterval(() => {
        refreshDeviceState();
    }, 5000);
});
</script>
