<!-- ── MODAL SCANNER QR (Powered by mebjas/html5-qrcode) ── -->
<div id="modalScanQr" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-all duration-300">
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 max-w-md w-full overflow-hidden flex flex-col max-h-[92vh]">
        
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i class="fa-solid fa-qrcode text-base"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base leading-tight">Scan QR Absensi</h3>
                    <p class="text-xs text-slate-500">Arahkan kartu QR / Barcode Anda ke kamera</p>
                </div>
            </div>
            <button type="button" onclick="closeScanModal()" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Tabs: Kamera Live vs Upload File -->
        <div class="flex border-b border-slate-100 bg-white px-5 pt-2 gap-4">
            <button type="button" id="tabModalCamera" onclick="switchModalScanMode('camera')"
                    class="pb-2.5 text-xs font-bold text-emerald-700 border-b-2 border-emerald-600 flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-camera"></i> Kamera Scanner
            </button>
            <button type="button" id="tabModalFile" onclick="switchModalScanMode('file')"
                    class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 border-b-2 border-transparent flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-file-image"></i> Upload Gambar QR
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 overflow-y-auto flex-1">
            
            <!-- Mode 1: Kamera Scanner -->
            <div id="modalCameraSection" class="flex flex-col items-center">
                <!-- Viewfinder Box -->
                <div class="relative w-full max-w-[280px] aspect-square rounded-2xl overflow-hidden bg-slate-900 border-2 border-slate-200 shadow-inner flex items-center justify-center" id="modalCameraContainer">
                    <div id="modal-qr-reader" class="w-full h-full"></div>
                    
                    <!-- Animasi Scanner Laser & Corner Frame -->
                    <div id="modalScanFrame" class="absolute inset-4 pointer-events-none z-10 hidden">
                        <span class="absolute top-0 left-0 w-6 h-6 border-t-3 border-l-3 border-emerald-400 rounded-tl-lg"></span>
                        <span class="absolute top-0 right-0 w-6 h-6 border-t-3 border-r-3 border-emerald-400 rounded-tr-lg"></span>
                        <span class="absolute bottom-0 left-0 w-6 h-6 border-b-3 border-l-3 border-emerald-400 rounded-bl-lg"></span>
                        <span class="absolute bottom-0 right-0 w-6 h-6 border-b-3 border-r-3 border-emerald-400 rounded-br-lg"></span>
                        <div class="absolute left-0 right-0 h-0.5 bg-emerald-400 shadow-[0_0_10px_2px_rgba(52,211,153,0.8)] animate-pulse" style="animation: scanLaserMove 2s ease-in-out infinite;"></div>
                    </div>

                    <!-- Placeholder Loading -->
                    <div id="modalCameraLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-white z-20">
                        <i class="fa-solid fa-spinner fa-spin text-3xl mb-2 text-emerald-400"></i>
                        <p class="text-xs font-medium text-slate-300">Menyiapkan kamera scanner...</p>
                    </div>
                </div>

                <!-- Kontrol Kamera: Switch & Select -->
                <div class="w-full mt-3 flex flex-col gap-2">
                    <div id="modalCameraSelectWrap" class="hidden">
                        <select id="modalCameraSelect" class="w-full text-xs font-medium bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        </select>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-500 px-1">
                        <span id="modalCameraStatus"><i class="fa-solid fa-circle text-[8px] text-emerald-500 mr-1 animate-pulse"></i> Scanner Siap</span>
                        <button type="button" id="modalBtnFlip" onclick="flipModalCamera()" class="text-emerald-700 hover:text-emerald-800 font-semibold flex items-center gap-1 hidden">
                            <i class="fa-solid fa-camera-rotate"></i> Ganti Kamera
                        </button>
                    </div>
                    <div class="flex items-center justify-between text-xs px-1">
                        <span id="modalGpsStatus" class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                            <i class="fa-solid fa-spinner fa-spin text-[10px]"></i> Memeriksa GPS...
                        </span>
                        <button type="button" onclick="requestModalGps(true)" class="text-[11px] font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Refresh GPS
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mode 2: Upload Gambar QR -->
            <div id="modalFileSection" class="hidden flex flex-col items-center">
                <label for="modalFileInput" class="w-full border-2 border-dashed border-slate-200 hover:border-emerald-400 bg-slate-50 hover:bg-emerald-50/30 rounded-2xl p-6 text-center cursor-pointer transition-all flex flex-col items-center justify-center min-h-[220px]">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 mb-1">Klik untuk pilih gambar QR</p>
                    <p class="text-xs text-slate-400">Format JPG, PNG, WEBP</p>
                    <img id="modalFilePreview" class="mt-3 max-h-36 rounded-lg shadow-sm border border-slate-200 hidden object-contain">
                </label>
                <input type="file" id="modalFileInput" accept="image/*" class="hidden" onchange="handleModalFileSelect(this)">
                <button type="button" id="modalBtnScanFile" onclick="scanModalSelectedFile()" disabled
                        class="w-full mt-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:pointer-events-none text-white rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-magnifying-glass"></i> Pindai Gambar
                </button>
            </div>

            <!-- Pesan Feedback / Status Absensi -->
            <div id="modalFeedbackBox" class="mt-4 p-3 rounded-xl text-xs font-semibold text-center hidden"></div>

        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
            <a href="<?= BASEURL; ?>/guru/scanner" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                <i class="fa-solid fa-expand text-slate-400"></i> Buka Layar Penuh
            </a>
            <button type="button" onclick="closeScanModal()" class="px-4 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-200/60 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<style>
@keyframes scanLaserMove {
    0%   { top: 5%; opacity: 0.8; }
    50%  { top: 92%; opacity: 0.95; }
    100% { top: 5%; opacity: 0.8; }
}
#modal-qr-reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 14px;
}
#modal-qr-reader__scan_region {
    width: 100% !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
#modal-qr-reader__dashboard,
#modal-qr-reader__header_message {
    display: none !important;
}
</style>

<script>
let modalHtml5Qrcode = null;
let modalDevices = [];
let modalCurrentDeviceIndex = 0;
let modalIsScanning = false;
let modalIsProcessing = false;
let modalLastScan = null;
let modalLastScanTime = 0;
let modalAudioCtx = null;
let modalSelectedFile = null;

// Sound Beep Generator (Web Audio API)
function playModalBeep(type = 'success') {
    try {
        if (!modalAudioCtx) {
            modalAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (modalAudioCtx.state === 'suspended') {
            modalAudioCtx.resume();
        }
        const osc = modalAudioCtx.createOscillator();
        const gain = modalAudioCtx.createGain();
        osc.connect(gain);
        gain.connect(modalAudioCtx.destination);
        
        if (type === 'success') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, modalAudioCtx.currentTime);
            osc.frequency.setValueAtTime(1320, modalAudioCtx.currentTime + 0.08);
            gain.gain.setValueAtTime(0.25, modalAudioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, modalAudioCtx.currentTime + 0.2);
            osc.start();
            osc.stop(modalAudioCtx.currentTime + 0.2);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(260, modalAudioCtx.currentTime);
            gain.gain.setValueAtTime(0.2, modalAudioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, modalAudioCtx.currentTime + 0.25);
            osc.start();
            osc.stop(modalAudioCtx.currentTime + 0.25);
        }
    } catch (_) {}
}

function showModalFeedback(type, message, persistent = false) {
    const fb = document.getElementById('modalFeedbackBox');
    if (!fb) return;
    fb.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200',
                          'bg-red-50', 'text-red-700', 'border-red-200', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
    
    if (type === 'success') {
        fb.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        fb.innerHTML = `<i class="fa-solid fa-circle-check mr-1.5"></i> ${message}`;
    } else if (type === 'error') {
        fb.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
        fb.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1.5"></i> ${message}`;
    } else {
        fb.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
        fb.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> ${message}`;
    }

    if (!persistent && type !== 'loading') {
        setTimeout(() => {
            fb.classList.add('hidden');
        }, 4000);
    }
}

// Buka Modal dan Inisialisasi Kamera Mebjas
async function openScanModal() {
    const modal = document.getElementById('modalScanQr');
    if (!modal) return;
    modal.classList.remove('hidden');
    switchModalScanMode('camera');
    requestModalGps();

    const loadingEl = document.getElementById('modalCameraLoading');
    const scanFrame = document.getElementById('modalScanFrame');
    if (loadingEl) loadingEl.classList.remove('hidden');
    if (scanFrame) scanFrame.classList.add('hidden');

    // Pastikan library html5-qrcode tersedia
    if (typeof Html5Qrcode === 'undefined') {
        showModalFeedback('error', 'Library Html5Qrcode belum selesai dimuat. Silakan refresh halaman.');
        if (loadingEl) loadingEl.classList.add('hidden');
        return;
    }

    // Buat instance Html5Qrcode baru jika belum ada
    if (!modalHtml5Qrcode) {
        modalHtml5Qrcode = new Html5Qrcode("modal-qr-reader", {
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.UPC_A
            ],
            verbose: false
        });
    }

    // Dapatkan daftar kamera
    try {
        const devices = await Html5Qrcode.getCameras();
        modalDevices = devices || [];
        const select = document.getElementById('modalCameraSelect');
        const selectWrap = document.getElementById('modalCameraSelectWrap');
        const btnFlip = document.getElementById('modalBtnFlip');
        
        if (modalDevices.length > 0) {
            select.innerHTML = '';
            modalDevices.forEach((dev, idx) => {
                const opt = document.createElement('option');
                opt.value = dev.id;
                opt.text = dev.label || `Kamera ${idx + 1}`;
                select.appendChild(opt);
            });
            if (modalDevices.length > 1) {
                selectWrap.classList.remove('hidden');
                btnFlip.classList.remove('hidden');
            }
            // Pilih kamera belakang jika ada, atau pertama
            const backIdx = modalDevices.findIndex(d => /back|belakang|rear|environment/i.test(d.label || ''));
            modalCurrentDeviceIndex = backIdx >= 0 ? backIdx : 0;
            select.value = modalDevices[modalCurrentDeviceIndex].id;
            
            select.onchange = function() {
                startModalCameraTarget(this.value);
            };
            
            await startModalCameraTarget(modalDevices[modalCurrentDeviceIndex].id);
        } else {
            // Coba dengan facingMode default
            await startModalCameraTarget({ facingMode: "environment" });
        }
    } catch (err) {
        // Fallback langsung ke facingMode
        try {
            await startModalCameraTarget({ facingMode: "environment" });
        } catch (err2) {
            try {
                await startModalCameraTarget({ facingMode: "user" });
            } catch (err3) {
                console.error("Camera access failed:", err3);
                showModalFeedback('error', 'Kamera tidak dapat diakses. Pastikan izin kamera aktif.');
                if (loadingEl) loadingEl.classList.add('hidden');
            }
        }
    }
}

async function startModalCameraTarget(target) {
    if (!modalHtml5Qrcode) return;
    const loadingEl = document.getElementById('modalCameraLoading');
    const scanFrame = document.getElementById('modalScanFrame');
    
    if (modalIsScanning) {
        try { await modalHtml5Qrcode.stop(); } catch (_) {}
        modalIsScanning = false;
    }

    if (loadingEl) loadingEl.classList.remove('hidden');
    if (scanFrame) scanFrame.classList.add('hidden');

    const config = {
        fps: 10,
        qrbox: function(viewfinderWidth, viewfinderHeight) {
            const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
            const size = Math.max(160, Math.floor(minEdge * 0.75));
            return { width: size, height: size };
        },
        aspectRatio: 1.0,
        disableFlip: false
    };

    try {
        await modalHtml5Qrcode.start(
            target,
            config,
            onModalScanSuccess,
            onModalScanFailure
        );
        modalIsScanning = true;
        if (loadingEl) loadingEl.classList.add('hidden');
        if (scanFrame) scanFrame.classList.remove('hidden');
        document.getElementById('modalCameraStatus').innerHTML = '<i class="fa-solid fa-circle text-[8px] text-emerald-500 mr-1 animate-pulse"></i> Kamera Aktif';
    } catch (e) {
        console.warn("Failed to start camera target:", target, e);
        // Jika target ID gagal, coba fallback user facingMode
        if (typeof target === 'string') {
            await modalHtml5Qrcode.start({ facingMode: "user" }, config, onModalScanSuccess, onModalScanFailure);
            modalIsScanning = true;
            if (loadingEl) loadingEl.classList.add('hidden');
            if (scanFrame) scanFrame.classList.remove('hidden');
        } else {
            throw e;
        }
    }
}

async function flipModalCamera() {
    if (modalDevices.length < 2) return;
    modalCurrentDeviceIndex = (modalCurrentDeviceIndex + 1) % modalDevices.length;
    const select = document.getElementById('modalCameraSelect');
    if (select) select.value = modalDevices[modalCurrentDeviceIndex].id;
    await startModalCameraTarget(modalDevices[modalCurrentDeviceIndex].id);
}

async function closeScanModal() {
    const modal = document.getElementById('modalScanQr');
    if (!modal) return;
    
    if (modalHtml5Qrcode && modalIsScanning) {
        try {
            await modalHtml5Qrcode.stop();
        } catch (_) {}
        modalIsScanning = false;
    }
    modal.classList.add('hidden');
}

const MODAL_SCHOOL_LAT = <?= json_encode((float)SCHOOL_LAT); ?>;
const MODAL_SCHOOL_LNG = <?= json_encode((float)SCHOOL_LNG); ?>;
const MODAL_MAX_RADIUS = <?= json_encode((int)(defined('SCHOOL_RADIUS_METER') ? SCHOOL_RADIUS_METER : 100)); ?>; // Maksimal 100m
const MODAL_MAX_ACCURACY = 80;

let modalGps = { lat: null, lng: null, accuracy: null, dist: null, ok: false, fetching: false };

function modalHaversine(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2)*Math.sin(dLat/2)
            + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)
            * Math.sin(dLng/2)*Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function updateModalGpsUI() {
    const statusEl = document.getElementById('modalGpsStatus');
    if (!statusEl) return;

    if (modalGps.fetching) {
        statusEl.className = 'inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full';
        statusEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i> Memeriksa GPS...';
        return;
    }

    if (modalGps.ok) {
        const d = Math.round(modalGps.dist);
        const a = Math.round(modalGps.accuracy);
        statusEl.className = 'inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full';
        statusEl.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-600 text-[10px]"></i> Lokasi OK: ±${d}m (Akurasi: ±${a}m)`;
    } else {
        if (modalGps.dist !== null && modalGps.dist > MODAL_MAX_RADIUS) {
            const d = Math.round(modalGps.dist);
            statusEl.className = 'inline-flex items-center gap-1 text-[11px] font-medium text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full';
            statusEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-rose-600 text-[10px]"></i> Di luar area: ±${d}m (Maks 100m)`;
        } else if (modalGps.accuracy !== null && modalGps.accuracy > MODAL_MAX_ACCURACY) {
            const a = Math.round(modalGps.accuracy);
            statusEl.className = 'inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full';
            statusEl.innerHTML = `<i class="fa-solid fa-satellite-dish text-amber-600 text-[10px]"></i> Akurasi GPS rendah (±${a}m)`;
        } else {
            statusEl.className = 'inline-flex items-center gap-1 text-[11px] font-medium text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full';
            statusEl.innerHTML = '<i class="fa-solid fa-location-crosshairs text-rose-600 text-[10px]"></i> GPS belum aktif / ditolak';
        }
    }
}

function requestModalGps(force = false) {
    if (!navigator.geolocation) {
        modalGps.ok = false;
        modalGps.fetching = false;
        updateModalGpsUI();
        return;
    }

    modalGps.fetching = true;
    updateModalGpsUI();

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            modalGps.lat = pos.coords.latitude;
            modalGps.lng = pos.coords.longitude;
            modalGps.accuracy = pos.coords.accuracy || 0;
            modalGps.dist = modalHaversine(modalGps.lat, modalGps.lng, MODAL_SCHOOL_LAT, MODAL_SCHOOL_LNG);
            modalGps.fetching = false;

            if (modalGps.accuracy > MODAL_MAX_ACCURACY) {
                modalGps.ok = false;
            } else if (modalGps.dist <= MODAL_MAX_RADIUS) {
                modalGps.ok = true;
            } else {
                modalGps.ok = false;
            }

            updateModalGpsUI();
        },
        function(err) {
            modalGps.ok = false;
            modalGps.fetching = false;
            updateModalGpsUI();
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Callback Scan Berhasil dari mebjas/html5-qrcode
function onModalScanSuccess(decodedText, decodedResult) {
    const now = Date.now();
    if (modalIsProcessing) return;
    if (decodedText === modalLastScan && (now - modalLastScanTime) < 2500) {
        return; // Debounce kartu sama
    }

    modalIsProcessing = true;
    modalLastScan = decodedText;
    modalLastScanTime = now;

    playModalBeep('success');
    showModalFeedback('loading', 'Memeriksa lokasi GPS & memproses absensi...', true);

    const executeAttendanceSend = () => {
        const payload = {
            barcode: decodedText,
            source: 'dashboard_modal',
            lat: modalGps.lat,
            lng: modalGps.lng,
            accuracy: modalGps.accuracy
        };

        fetch('<?= BASEURL; ?>/api/attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                playModalBeep('success');
                const namaGuru = (res.data && res.data.guru && res.data.guru.nama) ? res.data.guru.nama : 'Guru';
                const statusMsg = res.message || 'Absensi Berhasil';
                showModalFeedback('success', `${statusMsg} (${namaGuru})`, true);
                
                // Reload halaman secara otomatis dalam 1.5 detik agar status dashboard terupdate
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                playModalBeep('error');
                showModalFeedback('error', res.message || 'Absensi Gagal Dicatat.');
            }
        })
        .catch(err => {
            playModalBeep('error');
            showModalFeedback('error', 'Terjadi kesalahan koneksi ke server.');
        })
        .finally(() => {
            setTimeout(() => {
                modalIsProcessing = false;
            }, 1000);
        });
    };

    // Jika GPS belum didapat, coba ambil secara cepat
    if (modalGps.lat === null && navigator.geolocation) {
        modalGps.fetching = true;
        updateModalGpsUI();
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                modalGps.lat = pos.coords.latitude;
                modalGps.lng = pos.coords.longitude;
                modalGps.accuracy = pos.coords.accuracy || 0;
                modalGps.dist = modalHaversine(modalGps.lat, modalGps.lng, MODAL_SCHOOL_LAT, MODAL_SCHOOL_LNG);
                modalGps.fetching = false;
                modalGps.ok = (modalGps.dist <= MODAL_MAX_RADIUS && modalGps.accuracy <= MODAL_MAX_ACCURACY);
                updateModalGpsUI();

                if (!modalGps.ok) {
                    playModalBeep('error');
                    let errMsg = 'Presensi ditolak. Izin lokasi GPS diperlukan untuk absensi guru.';
                    if (modalGps.dist !== null && modalGps.dist > MODAL_MAX_RADIUS) {
                        errMsg = `Presensi ditolak. Lokasi Anda berada di luar area sekolah (±${Math.round(modalGps.dist)}m dari sekolah, batas maksimal 100m).`;
                    } else if (modalGps.accuracy > MODAL_MAX_ACCURACY) {
                        errMsg = `Presensi ditolak. Keakuratan GPS rendah (±${Math.round(modalGps.accuracy)}m, batas maksimal 80m).`;
                    }
                    showModalFeedback('error', errMsg);
                    setTimeout(() => { modalIsProcessing = false; }, 1000);
                    return;
                }

                executeAttendanceSend();
            },
            function(err) {
                modalGps.fetching = false;
                modalGps.ok = false;
                updateModalGpsUI();
                playModalBeep('error');
                showModalFeedback('error', 'Presensi ditolak. Lokasi GPS diperlukan untuk presensi guru. Pastikan GPS aktif dan izinkan browser mengakses lokasi.');
                setTimeout(() => { modalIsProcessing = false; }, 1000);
            },
            { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
        );
        return;
    }

    if (!modalGps.ok) {
        playModalBeep('error');
        let errMsg = 'Presensi ditolak. Lokasi GPS diperlukan untuk presensi guru.';
        if (modalGps.dist !== null && modalGps.dist > MODAL_MAX_RADIUS) {
            errMsg = `Presensi ditolak. Lokasi Anda berada di luar area sekolah (±${Math.round(modalGps.dist)}m dari sekolah, batas maksimal 100m).`;
        } else if (modalGps.accuracy !== null && modalGps.accuracy > MODAL_MAX_ACCURACY) {
            errMsg = `Presensi ditolak. Keakuratan GPS rendah (±${Math.round(modalGps.accuracy)}m, batas toleransi 80m).`;
        }
        showModalFeedback('error', errMsg);
        setTimeout(() => { modalIsProcessing = false; }, 1000);
        return;
    }

    executeAttendanceSend();
}

function onModalScanFailure(error) {
    // Abaikan kegagalan frame rutin sesuai anjuran resmi mebjas
}

// Mode Switch (Kamera vs File)
async function switchModalScanMode(mode) {
    const camSec = document.getElementById('modalCameraSection');
    const fileSec = document.getElementById('modalFileSection');
    const tabCam = document.getElementById('tabModalCamera');
    const tabFile = document.getElementById('tabModalFile');

    if (mode === 'camera') {
        camSec.classList.remove('hidden');
        fileSec.classList.add('hidden');
        tabCam.className = "pb-2.5 text-xs font-bold text-emerald-700 border-b-2 border-emerald-600 flex items-center gap-1.5 transition-all";
        tabFile.className = "pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 border-b-2 border-transparent flex items-center gap-1.5 transition-all";
        
        if (modalHtml5Qrcode && !modalIsScanning && !document.getElementById('modalScanQr').classList.contains('hidden')) {
            const target = modalDevices.length > 0 ? modalDevices[modalCurrentDeviceIndex].id : { facingMode: "environment" };
            startModalCameraTarget(target);
        }
    } else {
        camSec.classList.add('hidden');
        fileSec.classList.remove('hidden');
        tabFile.className = "pb-2.5 text-xs font-bold text-emerald-700 border-b-2 border-emerald-600 flex items-center gap-1.5 transition-all";
        tabCam.className = "pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 border-b-2 border-transparent flex items-center gap-1.5 transition-all";

        if (modalHtml5Qrcode && modalIsScanning) {
            try { await modalHtml5Qrcode.stop(); } catch (_) {}
            modalIsScanning = false;
        }
    }
}

// File QR Image Upload Handler menggunakan scanFileV2
function handleModalFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    modalSelectedFile = input.files[0];
    const preview = document.getElementById('modalFilePreview');
    const btn = document.getElementById('modalBtnScanFile');
    
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        btn.disabled = false;
    };
    reader.readAsDataURL(modalSelectedFile);
}

async function scanModalSelectedFile() {
    if (!modalSelectedFile || !modalHtml5Qrcode) return;
    showModalFeedback('loading', 'Membaca gambar barcode/QR...', true);
    
    try {
        const decodedResult = await modalHtml5Qrcode.scanFileV2(modalSelectedFile, false);
        if (decodedResult && decodedResult.decodedText) {
            onModalScanSuccess(decodedResult.decodedText, decodedResult);
        } else {
            showModalFeedback('error', 'Barcode tidak terdeteksi pada gambar. Pastikan gambar jelas.');
        }
    } catch (err) {
        console.error("File scan failed:", err);
        showModalFeedback('error', 'Barcode/QR tidak terdeteksi pada gambar. Pastikan gambar jelas dan tidak buram.');
    }
}
</script>
