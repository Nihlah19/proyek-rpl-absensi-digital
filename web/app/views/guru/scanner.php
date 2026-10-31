<?php
// Halaman scanner absensi guru
// Fitur: kamera live, upload gambar, input manual, validasi geolokasi GPS
$user        = SessionHelper::get('user_id') ?? null;
$guru        = $data['guru'] ?? null;
$barcodeGuru = $guru ? $guru->barcode_id : null;
?>
<div class="scan-page">

    <!-- Header Scanner -->
    <header class="scan-header">
        <div class="scan-header-inner">
            <div class="scan-header-left">
                <a href="<?= BASEURL; ?>/dashboard" class="scan-icon-btn" title="Kembali ke Dashboard">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="scan-title">Presensi Scanner Guru</h1>
                    <p class="scan-subtitle">Kamera Live & Barcode Presensi</p>
                </div>
            </div>
            <div class="scan-header-right">
                <span id="geo-badge" class="geo-badge geo-checking">
                    <i class="fa-solid fa-location-dot"></i>
                    <span id="geo-badge-text">Memeriksa GPS...</span>
                </span>
            </div>
        </div>
    </header>

    <!-- Main -->
    <div class="scan-main">
        <div class="scan-wrap">

            <!-- ── Geo lock overlay ── -->
            <div id="geo-lock-panel" class="geo-lock-panel hidden">
                <div class="geo-lock-icon"><i class="fa-solid fa-location-crosshairs"></i></div>
                <h3 class="geo-lock-title" id="geo-lock-title">Di Luar Jangkauan Sekolah</h3>
                <p id="geo-lock-msg" class="geo-lock-msg">Anda harus berada dalam radius maksimal <strong>100 meter</strong> dari sekolah dan menggunakan GPS berakurasi tinggi untuk melakukan absensi.</p>
                <p id="geo-lock-dist" class="geo-lock-dist"></p>
                <button id="btn-retry-geo" class="btn btn-primary" style="margin-top:14px;">
                    <i class="fa-solid fa-rotate"></i> Cek Ulang Lokasi GPS
                </button>
            </div>

            <!-- ── Tab mode scan ── -->
            <div id="scan-tabs" class="scan-tabs">
                <button class="scan-tab active" data-tab="camera" id="tab-camera">
                    <i class="fa-solid fa-camera"></i> Kamera
                </button>
                <button class="scan-tab" data-tab="image" id="tab-image">
                    <i class="fa-solid fa-image"></i> Upload
                </button>
            </div>

            <div class="scan-card">

                <!-- Status bar -->
                <div class="scan-statusbar">
                    <div class="scan-status-left">
                        <span id="status-dot" class="status-dot status-idle"></span>
                        <span id="status-text" class="status-text">Menyiapkan...</span>
                    </div>
                    <span id="result-badge" class="badge badge-hidden"></span>
                </div>

                <!-- ══ Panel Kamera ══ -->
                <div id="panel-camera" class="scan-panel">
                    <div class="camera-wrap">
                        <div class="camera-box" id="camera-box">
                            <div id="qr-reader"></div>
                            <div id="camera-placeholder" class="camera-placeholder">
                                <div class="placeholder-icon"><i class="fa-solid fa-camera"></i></div>
                                <p id="placeholder-text" class="placeholder-text">Menyiapkan kamera...</p>
                            </div>
                            <div id="scan-frame" class="scan-frame hidden">
                                <span class="corner corner-tl"></span>
                                <span class="corner corner-tr"></span>
                                <span class="corner corner-bl"></span>
                                <span class="corner corner-br"></span>
                                <div class="scan-laser"></div>
                            </div>
                        </div>
                        <div id="camera-controls" class="camera-controls hidden">
                            <button id="btn-switch-camera" type="button" class="btn btn-ghost btn-sm hidden">
                                <i class="fa-solid fa-camera-rotate"></i> Ganti Kamera
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══ Panel Upload Gambar ══ -->
                <div id="panel-image" class="scan-panel hidden">
                    <div class="image-upload-wrap">
                        <label for="img-input" class="image-drop-area" id="img-drop-area">
                            <div class="image-drop-inner" id="img-drop-inner">
                                <div class="placeholder-icon" style="background:rgba(99,102,241,0.1);">
                                    <i class="fa-solid fa-file-image" style="color:#6366f1;"></i>
                                </div>
                                <p class="image-drop-title">Klik atau seret gambar barcode/QR ke sini</p>
                                <p class="image-drop-sub">JPG, PNG, WEBP — maks 5 MB</p>
                            </div>
                            <img id="img-preview" class="image-preview hidden" alt="Preview">
                        </label>
                        <input type="file" id="img-input" accept="image/*" style="display:none;">
                        <div style="display:flex;gap:8px;margin-top:10px;">
                            <button id="btn-scan-image" class="btn btn-primary" style="flex:1;" disabled>
                                <i class="fa-solid fa-magnifying-glass"></i> Scan Gambar
                            </button>
                            <button id="btn-clear-image" class="btn btn-ghost hidden">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div id="img-result" class="img-result hidden"></div>
                    </div>
                </div>



                <!-- ══ Result Panel (bersama semua mode) ══ -->
                <div id="scanner-result" class="scan-result hidden">
                    <div class="result-body">
                        <div id="result-icon" class="result-icon"></div>
                        <div class="result-content">
                            <p id="result-title" class="result-title"></p>
                            <p id="result-message" class="result-message"></p>
                            <div id="result-guru-card" class="guru-card hidden">
                                <div id="result-guru-avatar" class="guru-avatar"></div>
                                <div class="guru-info">
                                    <p id="result-guru-nama" class="guru-nama"></p>
                                    <p id="result-guru-meta" class="guru-meta"></p>
                                </div>
                            </div>
                            <div id="result-details" class="result-details"></div>
                        </div>
                    </div>
                    <div class="result-actions">
                        <button id="btn-retry" class="btn btn-secondary">
                            <i class="fa-solid fa-rotate"></i> Scan Lagi
                        </button>
                        <button id="btn-close-scanner" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> Kembali
                        </button>
                    </div>
                </div>

            </div><!-- /.scan-card -->

        </div>
    </div>
</div>

<style>
/* ── Layout dasar ── */
.scan-page { width:100%;min-height:100vh;background:#f8fafc;display:flex;flex-direction:column;font-family:inherit; }

.scan-header { background:#fff;border-bottom:1px solid #e2e8f0; }
.scan-header-inner { max-width:56rem;margin:0 auto;padding:16px;display:flex;align-items:center;justify-content:space-between; }
.scan-header-left { display:flex;align-items:center;gap:12px; }
.scan-icon-btn { color:#94a3b8;font-size:20px;text-decoration:none;transition:color .15s; }
.scan-icon-btn:hover { color:#475569; }
.scan-title { font-size:17px;font-weight:600;color:#1e293b;margin:0; }
.scan-subtitle { font-size:12px;color:#94a3b8;margin:2px 0 0; }
.scan-close-btn { padding:6px 12px;background:#f1f5f9;border-radius:8px;font-size:13px;color:#475569;text-decoration:none;transition:background .15s; }
.scan-close-btn:hover { background:#e2e8f0; }

/* ── Geo badge ── */
.geo-badge { display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;font-size:12px;font-weight:600; }
.geo-checking { background:#f1f5f9;color:#64748b; }
.geo-ok      { background:#d1fae5;color:#065f46; }
.geo-fail    { background:#fee2e2;color:#991b1b; }

/* ── Geo lock overlay ── */
.geo-lock-panel { background:#fff;border-radius:18px;border:1px solid #fee2e2;box-shadow:0 1px 3px rgba(0,0,0,0.04);padding:32px 24px;text-align:center;margin-bottom:12px; }
.geo-lock-icon { width:60px;height:60px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#dc2626; }
.geo-lock-title { font-size:17px;font-weight:700;color:#1e293b;margin:0 0 8px; }
.geo-lock-msg { font-size:13px;color:#64748b;margin:0; }
.geo-lock-dist { font-size:13px;color:#94a3b8;margin:6px 0 0;font-style:italic; }

/* ── Tabs ── */
.scan-tabs { display:flex;gap:4px;background:#f1f5f9;border-radius:12px;padding:4px;margin-bottom:10px; }
.scan-tab { flex:1;padding:8px;border:none;border-radius:9px;font-size:13px;font-weight:500;color:#64748b;background:transparent;cursor:pointer;transition:all .15s;display:flex;align-items:center;justify-content:center;gap:6px; }
.scan-tab.active { background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.scan-tab:hover:not(.active) { background:rgba(255,255,255,0.6); }

/* ── Card + Panels ── */
.scan-main { flex:1;width:100%;display:flex;align-items:flex-start;justify-content:center;padding:16px; }
.scan-wrap { width:100%;max-width:600px;margin:0 auto; }
.scan-card { background:#fff;border-radius:18px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden; }
.scan-panel { /* each tab panel */ }

/* ── Status bar ── */
.scan-statusbar { padding:12px 18px;border-bottom:1px solid #f1f5f9;background:#f8fafc;display:flex;align-items:center;justify-content:space-between; }
.scan-status-left { display:flex;align-items:center;gap:8px; }
.status-dot { width:10px;height:10px;border-radius:50%;display:inline-block;transition:background .2s; }
.status-idle       { background:#cbd5e1; }
.status-processing { background:#facc15; }
.status-active     { background:#4ade80; }
.status-success    { background:#10b981; }
.status-error      { background:#f87171; }
.status-text { font-size:13px;color:#64748b; }
.badge { padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600; }
.badge-hidden { display:none; }
.badge-success { background:#d1fae5;color:#059669; }
.badge-danger  { background:#fee2e2;color:#dc2626; }
.badge-info    { background:#dbeafe;color:#2563eb; }

/* ── Kotak kamera ── */
.camera-wrap { padding:18px; }
.camera-box { position:relative;width:100%;max-width:100%;aspect-ratio:4/3;margin:0 auto;border-radius:16px;overflow:hidden;background:#0f172a; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
.camera-box.flash-success { border: 3px solid #10b981 !important; box-shadow: 0 0 30px rgba(16, 185, 129, 0.7) !important; }
.camera-box.flash-error { border: 3px solid #ef4444 !important; box-shadow: 0 0 30px rgba(239, 68, 68, 0.7) !important; }
#qr-reader { position:absolute;inset:0;width:100% !important;height:100% !important; }
#qr-reader video { width:100% !important;height:100% !important;object-fit:cover !important; transform: none !important; }
#qr-reader__dashboard,#qr-reader__header_message,#qr-reader__camera_selection,#qr-reader__camera_permission_button { display:none !important; }
.camera-placeholder { position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;background:#0f172a;color:rgba(255,255,255,0.65);text-align:center;padding:24px;z-index:5; }
.placeholder-icon { width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;font-size:20px; }
.placeholder-text { font-size:13px;margin:0;line-height:1.5; }
.scan-frame { position:absolute;inset:30px;pointer-events:none;z-index:4; }
.corner { position:absolute;width:28px;height:28px;border:3px solid #34d399; }
.corner-tl { top:0;left:0;border-right:none;border-bottom:none;border-top-left-radius:8px; }
.corner-tr { top:0;right:0;border-left:none;border-bottom:none;border-top-right-radius:8px; }
.corner-bl { bottom:0;left:0;border-right:none;border-top:none;border-bottom-left-radius:8px; }
.corner-br { bottom:0;right:0;border-left:none;border-top:none;border-bottom-right-radius:8px; }
.scan-laser { position:absolute;left:0;right:0;top:0;height:2px;background:#34d399;box-shadow:0 0 8px 2px rgba(52,211,153,0.6);animation:scan-laser-move 2.2s ease-in-out infinite; }
@keyframes scan-laser-move { 0%{top:0%;opacity:.9}48%{top:100%;opacity:.9}50%{opacity:0}52%{top:0%;opacity:0}55%{opacity:.9}100%{top:0%;opacity:.9} }
.hidden { display:none !important; }
.camera-controls { display:flex;justify-content:center;margin-top:12px; }

/* ── Upload gambar ── */
.image-upload-wrap { padding:18px; }
.image-drop-area { display:block;border:2px dashed #e2e8f0;border-radius:14px;cursor:pointer;background:#f8fafc;transition:border-color .15s,background .15s;overflow:hidden;position:relative; }
.image-drop-area:hover,.image-drop-area.drag-over { border-color:#6366f1;background:#eef2ff; }
.image-drop-inner { display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:28px 16px;text-align:center; }
.image-drop-title { font-size:13px;font-weight:500;color:#475569;margin:0; }
.image-drop-sub   { font-size:12px;color:#94a3b8;margin:0; }
.image-preview { width:100%;max-height:260px;object-fit:contain;display:block;border-radius:12px; }
.img-result { margin-top:10px;padding:12px;border-radius:10px;font-size:13px; }

/* ── Panel manual ── */
.manual-section-inner { padding:18px; }
.manual-alert { background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:14px;display:flex;gap:10px;margin-bottom:14px;align-items:flex-start; }
.manual-alert-icon { color:#f59e0b;margin-top:2px; }
.manual-alert-title { font-size:13px;font-weight:600;color:#92400e;margin:0; }
.manual-alert-subtitle { font-size:12px;color:#b45309;margin:2px 0 0; }
.manual-form { display:flex;flex-direction:column;gap:10px; }
@media(min-width:400px){ .manual-form { flex-direction:row; } }
.manual-input { flex:1;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none; }
.manual-input:focus { border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,0.15); }
.manual-result { margin-top:10px; }

/* ── Buttons ── */
.btn { display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:500;border:none;cursor:pointer;transition:background .15s;text-decoration:none; }
.btn:disabled { opacity:.5;cursor:not-allowed; }
.btn-sm { padding:7px 12px;font-size:12px; }
.btn-primary   { background:#6366f1;color:#fff; }
.btn-primary:hover:not(:disabled)   { background:#4f46e5; }
.btn-secondary { background:#f1f5f9;color:#475569;flex:1; }
.btn-secondary:hover { background:#e2e8f0; }
.btn-ghost   { background:#f1f5f9;color:#475569; }
.btn-ghost:hover { background:#e2e8f0; }
.btn-amber   { background:#f59e0b;color:#fff; }
.btn-amber:hover { background:#d97706; }

/* ── Result panel ── */
.scan-result { border-top:1px solid #f1f5f9;padding:18px; }
.result-body { display:flex;align-items:flex-start;gap:14px; }
.result-icon { width:48px;height:48px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:20px; }
.result-content { flex:1;min-width:0; }
.result-title   { font-size:15px;font-weight:600;color:#1e293b;margin:0; }
.result-message { font-size:13px;color:#64748b;margin:4px 0 0; }
.result-details { margin-top:10px;font-size:13px;color:#94a3b8; }
.result-actions { display:flex;gap:8px;margin-top:16px; }
.guru-card { margin-top:12px;padding:10px 12px;border-radius:10px;background:#f8fafc;border:1px solid #f1f5f9;display:flex;align-items:center;gap:12px; }
.guru-avatar { width:36px;height:36px;border-radius:50%;background:#e2e8f0;color:#64748b;font-weight:600;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.guru-info { min-width:0; }
.guru-nama { font-size:13px;font-weight:600;color:#1e293b;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.guru-meta { font-size:12px;color:#94a3b8;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }

/* ── Info box ── */
.info-box { margin-top:16px;padding:14px;border-radius:12px;background:#f8fafc;border:1px solid #f1f5f9;display:flex;gap:12px;font-size:13px;color:#64748b; }
.info-box-icon { color:#94a3b8;margin-top:2px; }
.info-box-title { font-weight:600;color:#334155;margin:0; }
.info-box-list { margin:6px 0 0;padding-left:18px;color:#94a3b8; }
.info-box-list li { margin-bottom:4px; }
.info-box-list code { background:#f1f5f9;padding:1px 5px;border-radius:4px;font-size:12px;color:#475569; }
</style>

<!-- Libraries -->
<script src="<?= BASEURL; ?>/assets/vendor/html5-qrcode/html5-qrcode.min.js" type="text/javascript"></script>

<script>
(function () {
    'use strict';

    // ── Konfigurasi geolokasi (dimuat dari server) ──
    var geoConfig = { lat: null, lng: null, radius: 10, loaded: false };
    var geoState  = { ok: false, checked: false, dist: null, bypassed: false };

    // ── State kamera ──
    var state = {
        html5Qrcode: null, cameras: [], cameraIndex: 0,
        activeCameraId: null, isRunning: false, isPaused: false,
        isStopped: false, lastResult: null, lastResultTime: 0,
        debounceMs: 2000, scanCooldownMs: 800
    };

    // ── DOM ──
    var statusDot    = document.getElementById('status-dot');
    var statusText   = document.getElementById('status-text');
    var resultBadge  = document.getElementById('result-badge');
    var cameraPlaceholder = document.getElementById('camera-placeholder');
    var placeholderText   = document.getElementById('placeholder-text');
    var scanFrame         = document.getElementById('scan-frame');
    var cameraControls    = document.getElementById('camera-controls');
    var btnSwitchCamera   = document.getElementById('btn-switch-camera');
    var resultPanel  = document.getElementById('scanner-result');
    var resultIcon   = document.getElementById('result-icon');
    var resultTitle  = document.getElementById('result-title');
    var resultMsg    = document.getElementById('result-message');
    var resultDetail = document.getElementById('result-details');
    var guruCard     = document.getElementById('result-guru-card');
    var guruAvatar   = document.getElementById('result-guru-avatar');
    var guruNamaEl   = document.getElementById('result-guru-nama');
    var guruMetaEl   = document.getElementById('result-guru-meta');
    var btnRetry     = document.getElementById('btn-retry');
    var btnClose     = document.getElementById('btn-close-scanner');
    var geoBadge     = document.getElementById('geo-badge');
    var geoBadgeText = document.getElementById('geo-badge-text');
    var geoLockPanel = document.getElementById('geo-lock-panel');
    var geoLockDist  = document.getElementById('geo-lock-dist');
    var btnRetryGeo  = document.getElementById('btn-retry-geo');
    var btnBypassGeo = document.getElementById('btn-bypass-geo');
    // image
    var imgInput     = document.getElementById('img-input');
    var imgPreview   = document.getElementById('img-preview');
    var imgDropArea  = document.getElementById('img-drop-area');
    var imgDropInner = document.getElementById('img-drop-inner');
    var btnScanImg   = document.getElementById('btn-scan-image');
    var btnClearImg  = document.getElementById('btn-clear-image');
    var imgResult    = document.getElementById('img-result');
    // panels
    var panelCamera = document.getElementById('panel-camera');
    var panelImage  = document.getElementById('panel-image');

    // ═══════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════
    function esc(str) {
        var d = document.createElement('div');
        d.textContent = (str == null) ? '' : String(str);
        return d.innerHTML;
    }
    function pick(obj) {
        if (!obj) return null;
        for (var i = 1; i < arguments.length; i++) {
            var k = arguments[i];
            if (obj[k] !== undefined && obj[k] !== null && obj[k] !== '') return obj[k];
        }
        return null;
    }
    function initials(name) {
        if (!name) return '?';
        var p = String(name).trim().split(/\s+/);
        return ((p[0] ? p[0][0] : '') + (p.length > 1 ? p[p.length-1][0] : '')).toUpperCase() || '?';
    }
    function setStatus(cls, text) {
        statusDot.className = 'status-dot ' + cls;
        statusText.textContent = text;
    }

    // Audio Beep Synthesizer menggunakan Web Audio API
    var audioCtx = null;
    function playScanBeep(type) {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            if (type === 'success') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                osc.frequency.setValueAtTime(1320, audioCtx.currentTime + 0.07);
                gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.16);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.16);
            } else {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(260, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.2);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.2);
            }
        } catch(e) {}
    }

    var cameraBoxEl = document.getElementById('camera-box');
    function triggerBoxFlash(type) {
        if (!cameraBoxEl) return;
        cameraBoxEl.classList.remove('flash-success', 'flash-error');
        if (type === 'success') cameraBoxEl.classList.add('flash-success');
        else if (type === 'error') cameraBoxEl.classList.add('flash-error');
        setTimeout(function() { cameraBoxEl.classList.remove('flash-success', 'flash-error'); }, 450);
    }

    // ═══════════════════════════════════════
    // GEOLOKASI
    // ═══════════════════════════════════════
    // Haversine distance in meters
    function haversine(lat1, lng1, lat2, lng2) {
        var R = 6371000;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLng = (lng2 - lng1) * Math.PI / 180;
        var a = Math.sin(dLat/2)*Math.sin(dLat/2)
              + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)
              * Math.sin(dLng/2)*Math.sin(dLng/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function setGeoBadge(status, text) {
        if (geoBadge && geoBadgeText) {
            geoBadge.className = 'geo-badge ' + status; // geo-checking | geo-ok | geo-fail
            geoBadgeText.textContent = text;
        }
    }

    function showGeoLock(dist, customTitle, customMsg) {
        geoLockPanel.classList.remove('hidden');
        if (customTitle && geoLockTitle) geoLockTitle.textContent = customTitle;
        if (customMsg && geoLockMsg) geoLockMsg.textContent = customMsg;
        if (dist !== null && geoLockDist) {
            geoLockDist.textContent = 'Jarak terdeteksi: ±' + Math.round(dist) + ' meter dari sekolah (Maksimal 100m)';
        }
        setStatus('status-error', 'Lokasi di luar batas 100m / GPS tidak valid');
    }
    function hideGeoLock() { geoLockPanel.classList.add('hidden'); }

    function doGeoCheck() {
        if (!navigator.geolocation) {
            geoState.ok = false;
            geoState.checked = true;
            setGeoBadge('geo-fail', 'Browser tidak mendukung GPS');
            showGeoLock(null, 'GPS Tidak Didukung', 'Browser Anda tidak memiliki dukungan sensor geolokasi GPS.');
            return;
        }

        setGeoBadge('geo-checking', 'Memeriksa sinyal & akurasi GPS...');
        if (!state.isRunning) {
            setStatus('status-processing', 'Memverifikasi posisi & akurasi GPS...');
        }

        navigator.geolocation.getCurrentPosition(function(pos) {
            var lat      = pos.coords.latitude;
            var lng      = pos.coords.longitude;
            var accuracy = pos.coords.accuracy || 0;
            var dist     = haversine(lat, lng, geoConfig.lat, geoConfig.lng);

            geoState.lat      = lat;
            geoState.lng      = lng;
            geoState.accuracy = accuracy;
            geoState.dist     = dist;
            geoState.checked  = true;

            var maxRadius = 100; // Maksimal 100 meter
            var maxAccuracy = 80; // Toleransi akurasi sinyal GPS maksimal 80m

            if (accuracy > maxAccuracy) {
                geoState.ok = false;
                setGeoBadge('geo-fail', 'Akurasi GPS rendah (±' + Math.round(accuracy) + 'm)');
                showGeoLock(dist, 'Sinyal GPS Kurang Akurat', 'Keakuratan GPS Anda ±' + Math.round(accuracy) + ' meter (maksimal toleransi ' + maxAccuracy + 'm). Mohon pindah ke area terbuka dan aktifkan GPS Berakurasi Tinggi.');
                if (state.html5Qrcode && state.isRunning) {
                    state.html5Qrcode.stop().catch(function(){});
                    state.isRunning = false;
                }
                return;
            }

            if (dist <= maxRadius) {
                geoState.ok = true;
                setGeoBadge('geo-ok', '±' + Math.round(dist) + 'm dari sekolah (Akurasi: ±' + Math.round(accuracy) + 'm)');
                hideGeoLock();
                if (!state.isRunning && !state.isStopped) {
                    initCamera();
                } else {
                    setStatus('status-active', 'Kamera aktif — arahkan barcode');
                }
            } else {
                geoState.ok = false;
                setGeoBadge('geo-fail', '±' + Math.round(dist) + 'm (Di luar batas 100m)');
                showGeoLock(dist, 'Di Luar Jangkauan Sekolah', 'Anda berada lebih dari 100 meter dari titik lokasi sekolah. Absensi hanya dapat dilakukan dalam radius maksimal 100 meter.');
                if (state.html5Qrcode && state.isRunning) {
                    state.html5Qrcode.stop().catch(function(){});
                    state.isRunning = false;
                }
            }
        }, function(err) {
            geoState.ok = false;
            geoState.checked = true;
            var errText = 'Akses GPS ditolak / gagal';
            if (err.code === 1) errText = 'Izin akses GPS ditolak';
            else if (err.code === 2) errText = 'Posisi GPS tidak dapat ditentukan';
            else if (err.code === 3) errText = 'Waktu permintaan GPS habis (timeout)';
            
            setGeoBadge('geo-fail', errText);
            showGeoLock(null, 'Izin Lokasi GPS Diperlukan', 'Aktifkan GPS perangkat Anda dan izinkan browser mengakses lokasi presisi tinggi untuk absensi.');
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0 // Pastikan selalu mengambil posisi satelit terbaru
        });
    }

    function loadGeoConfigThenCheck() {
        setGeoBadge('geo-checking', 'Memeriksa konfigurasi & GPS...');
        fetch('<?= BASEURL ?>/api/geo-config.php')
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success && res.data) {
                    geoConfig.lat    = res.data.lat;
                    geoConfig.lng    = res.data.lng;
                    geoConfig.radius = 100; // Selalu 100 meter
                    geoConfig.loaded = true;
                }
                doGeoCheck();
            })
            .catch(function(){
                geoConfig.radius = 100;
                doGeoCheck();
            });
    }

    btnRetryGeo && btnRetryGeo.addEventListener('click', function() {
        doGeoCheck();
    });

    // ═══════════════════════════════════════
    // BACKEND
    // ═══════════════════════════════════════
    function kirimKeBackend(identifier) {
        if (!geoState.ok) {
            var msg = geoState.dist !== null
                ? ('Jarak Anda ±' + Math.round(geoState.dist) + 'm dari sekolah (maksimal 100m).')
                : 'Lokasi GPS belum tervalidasi atau tidak akurat.';
            return Promise.resolve({
                status: 403,
                data: { success: false, message: 'Presensi ditolak: ' + msg }
            });
        }

        var url = '<?= BASEURL; ?>/api/attendance.php';
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                barcode: identifier,
                lat: geoState.lat,
                lng: geoState.lng,
                accuracy: geoState.accuracy,
                source: 'guru_scanner'
            })
        }).then(function(res) {
            var status = res.status;
            return res.text().then(function(text) {
                try { return { data: JSON.parse(text), status: status }; }
                catch(e) { return { data: { success: false, message: 'Server error (HTTP ' + status + ')' }, status: status }; }
            });
        });
    }

    // ═══════════════════════════════════════
    // UI: RENDER RESULT
    // ═══════════════════════════════════════
    function renderGuruInfo(guru) {
        if (!guru) { guruCard.classList.add('hidden'); return; }
        var nama   = pick(guru, 'nama', 'name', 'nama_guru');
        var idGuru = pick(guru, 'nip', 'barcode_id', 'id');
        var mapel  = pick(guru, 'mapel', 'mata_pelajaran', 'subject');
        if (!nama && !idGuru && !mapel) { guruCard.classList.add('hidden'); return; }
        guruAvatar.textContent = initials(nama);
        guruNamaEl.textContent = nama || 'Guru';
        var meta = [];
        if (idGuru) meta.push('ID: ' + idGuru);
        if (mapel)  meta.push(mapel);
        guruMetaEl.textContent = meta.join(' • ');
        guruCard.classList.remove('hidden');
    }

    function showResult(iconBg, iconColor, iconClass, title, message, detailsHtml, guru) {
        resultIcon.style.background = iconBg;
        resultIcon.innerHTML = '<i class="fa-solid ' + iconClass + '" style="color:' + iconColor + '"></i>';
        resultTitle.textContent  = title;
        resultMsg.textContent    = message;
        resultDetail.innerHTML   = detailsHtml || '';
        renderGuruInfo(guru);
        resultPanel.classList.remove('hidden');
    }

    function resetScannerUI() {
        resultPanel.classList.add('hidden');
        resultBadge.className = 'badge badge-hidden';
        guruCard.classList.add('hidden');
    }

    function interpretResponse(payload) {
        var data = payload.data;
        var guru = data && data.data ? (data.data.guru || null) : null;
        if (!data) return { success:false, stop:false, pauseMs:state.scanCooldownMs, iconBg:'#fee2e2', iconColor:'#ef4444', iconClass:'fa-triangle-exclamation', title:'Kesalahan Server', message:'Respons tidak valid.', detailsHtml:'', badgeText:'Error', badgeClass:'badge-danger', guru:null };

        if (data.success) {
            if (data.data && data.data.has_schedule === false) return { success:true, stop:true, pauseMs:0, iconBg:'#dbeafe', iconColor:'#0ea5e9', iconClass:'fa-circle-info', title:'Tidak Ada Jadwal', message:data.message||'Tidak perlu absen hari ini.', detailsHtml:'<span>Absensi tidak dicatat.</span>', badgeText:'Tidak perlu absen', badgeClass:'badge-info', guru:guru };
            if (data.data && data.data.type === 'check_in') {
                var t = data.data.jam_datang ? data.data.jam_datang.substring(0,5) : '-';
                return { success:true, stop:false, pauseMs:0, resumeAfterMs:800, iconBg:'#d1fae5', iconColor:'#10b981', iconClass:'fa-circle-check', title:'Absensi Masuk Berhasil', message:data.message||'Absensi masuk berhasil!', detailsHtml:'Jam: <strong>'+esc(t)+'</strong> &nbsp;|&nbsp; Status: <span class="badge badge-success">'+(data.data.status?data.data.status.toUpperCase():'HADIR')+'</span>', badgeText:'Hadir', badgeClass:'badge-success', guru:guru };
            }
            if (data.data && data.data.type === 'check_out') {
                var tp = data.data.jam_pulang ? data.data.jam_pulang.substring(0,5) : '-';
                return { success:true, stop:true, pauseMs:0, iconBg:'#d1fae5', iconColor:'#10b981', iconClass:'fa-flag-checkered', title:'Absensi Pulang Berhasil', message:data.message||'Absensi pulang berhasil!', detailsHtml:'Jam: <strong>'+esc(tp)+'</strong>', badgeText:'Pulang', badgeClass:'badge-success', guru:guru };
            }
            return { success:true, stop:true, pauseMs:0, iconBg:'#d1fae5', iconColor:'#10b981', iconClass:'fa-check', title:'Berhasil', message:data.message||'Berhasil.', detailsHtml:'', badgeText:null, badgeClass:null, guru:guru };
        }

        var alreadyScanned = data.data && data.data.type === 'already_scanned';
        var isLeave = data.data && data.data.type === 'leave_active';
        if (isLeave) {
            var st = data.data.status ? data.data.status.toUpperCase() : 'IZIN/SAKIT';
            return {
                success: false,
                stop: true,
                pauseMs: 0,
                iconBg: '#eff6ff',
                iconColor: '#3b82f6',
                iconClass: 'fa-envelope-open-text',
                title: 'Tercatat ' + st,
                message: data.message || 'Anda telah tercatat izin/sakit untuk hari ini.',
                detailsHtml: data.data.keterangan ? '<span>Keterangan: <strong>' + esc(data.data.keterangan) + '</strong></span>' : '<span>Tidak perlu melakukan presensi lagi.</span>',
                badgeText: st,
                badgeClass: 'badge-info',
                guru: guru
            };
        }
        return { success:false, stop:!!alreadyScanned, pauseMs:state.scanCooldownMs, iconBg:'#fee2e2', iconColor:'#ef4444', iconClass:'fa-times-circle', title:'Gagal Absensi', message:data.message||'Gagal absensi.', detailsHtml:'<span>Coba lagi beberapa saat.</span>', badgeText:'Gagal', badgeClass:'badge-danger', guru:guru };
    }

    function handleResult(payload) {
        var r = interpretResponse(payload);
        showResult(r.iconBg, r.iconColor, r.iconClass, r.title, r.message, r.detailsHtml, r.guru);
        if (r.badgeText) { resultBadge.className = 'badge ' + r.badgeClass; resultBadge.textContent = r.badgeText; }
        else { resultBadge.className = 'badge badge-hidden'; }
        setStatus(r.success ? 'status-success' : 'status-error', r.success ? (r.stop ? 'Selesai' : 'Absensi tercatat') : 'Gagal');
        state.isStopped = r.stop;
        if (r.resumeAfterMs && !r.stop) {
            setTimeout(function() { if (!state.isStopped) { state.isPaused = false; state.lastResult = null; setStatus('status-active', 'Kamera aktif — siap scan pulang'); } }, r.resumeAfterMs);
        } else if (r.pauseMs && !r.stop) {
            setTimeout(function() { if (!state.isStopped) { state.isPaused = false; state.lastResult = null; } }, r.pauseMs);
        } else if (!r.stop) {
            state.isPaused = false;
        }
        return r;
    }

    // ═══════════════════════════════════════
    // GEO GUARD — cek sebelum submit ke backend
    // ═══════════════════════════════════════
    function geoGuardThenSubmit(identifier, onDone) {
        if (geoState.ok) {
            onDone(identifier);
            return;
        }

        var errMsg = 'Lokasi GPS belum tervalidasi atau tidak akurat.';
        var errDetail = '';
        if (geoState.dist !== null && geoState.dist > 100) {
            errMsg = 'Anda berada di luar batas radius 100 meter dari sekolah (±' + Math.round(geoState.dist) + 'm).';
            errDetail = 'Batas maksimal jarak presensi adalah 100 meter.';
        } else if (geoState.accuracy !== null && geoState.accuracy > 80) {
            errMsg = 'Keakuratan GPS Anda ±' + Math.round(geoState.accuracy) + 'm (batas toleransi maksimal 80m).';
            errDetail = 'Gunakan mode GPS Berakurasi Tinggi (High Accuracy) di area terbuka.';
        }

        setStatus('status-error', 'Lokasi ditolak');
        showResult('#fee2e2','#ef4444','fa-location-dot',
            'Presensi Ditolak',
            errMsg,
            errDetail ? '<span>' + errDetail + '</span>' : '',
            null);
        resultBadge.className = 'badge badge-danger';
        resultBadge.textContent = 'Ditolak';

        setTimeout(function() {
            if (!state.isStopped) {
                state.isPaused = false;
                state.lastResult = null;
            }
        }, state.scanCooldownMs);
    }

    // ═══════════════════════════════════════
    // KAMERA
    // ═══════════════════════════════════════
    var qrConfig = {
        fps: 10,
        qrbox: function(viewfinderWidth, viewfinderHeight) {
            var minEdge = Math.min(viewfinderWidth, viewfinderHeight);
            return {
                width: Math.floor(minEdge * 0.8),
                height: Math.floor(minEdge * 0.8)
            };
        },
        aspectRatio: 1.0,
        disableFlip: false,
        videoConstraints: {
            width: { ideal: 1280 },
            height: { ideal: 720 }
        }
    };
    if (window.Html5QrcodeSupportedFormats) {
        qrConfig.formatsToSupport = [
            Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.CODE_128
        ];
    }

    function isSecureContextOk() { return window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1'; }

    function onCameraReady() {
        state.isRunning = true;
        cameraPlaceholder.classList.add('hidden');
        scanFrame.classList.remove('hidden');
        cameraControls.classList.remove('hidden');
        setStatus('status-active', 'Kamera aktif — arahkan barcode/QR');
        Html5Qrcode.getCameras().then(function(devices) {
            state.cameras = devices || [];
            btnSwitchCamera.classList.toggle('hidden', state.cameras.length < 2);
        }).catch(function(){});
    }

    function showCameraError(title, detail) {
        placeholderText.textContent = title;
        setStatus('status-error', 'Kamera tidak tersedia');
    }

    function describeError(err) {
        var name = err && err.name ? err.name : '';
        var msg  = err && err.message ? String(err.message) : String(err || '');
        if (name === 'NotAllowedError' || /Permission/i.test(msg)) return { title: 'Akses kamera ditolak', detail: 'Izinkan akses kamera di pengaturan browser.' };
        if (name === 'NotFoundError' || name === 'OverconstrainedError') return { title: 'Kamera tidak ditemukan', detail: 'Perangkat tidak memiliki kamera.' };
        if (name === 'NotReadableError') return { title: 'Kamera sedang dipakai aplikasi lain', detail: 'Tutup aplikasi lain yang memakai kamera.' };
        return { title: 'Gagal mengakses kamera', detail: msg || 'Terjadi kesalahan.' };
    }

    function onScanSuccess(identifier) {
        var now = Date.now();
        if (identifier === state.lastResult && (now - state.lastResultTime) < state.debounceMs) return;
        if (state.isPaused || state.isStopped) return;
        state.isPaused = true;
        state.lastResult = identifier;
        state.lastResultTime = now;
        playScanBeep('success');
        triggerBoxFlash('success');
        setStatus('status-processing', 'Memproses...');

        geoGuardThenSubmit(identifier, function(id) {
            kirimKeBackend(id)
                .then(function(payload) {
                    var r = handleResult(payload);
                    if (r && !r.success) {
                        playScanBeep('error');
                        triggerBoxFlash('error');
                    }
                })
                .catch(function() {
                    playScanBeep('error');
                    triggerBoxFlash('error');
                    showResult('#fee2e2','#ef4444','fa-triangle-exclamation','Kesalahan Koneksi','Tidak dapat terhubung ke server.','',null);
                    resultBadge.className = 'badge badge-danger'; resultBadge.textContent = 'Error';
                    setStatus('status-error','Error koneksi');
                    setTimeout(function(){ if (!state.isStopped){ state.isPaused=false; state.lastResult=null; } }, state.scanCooldownMs);
                });
        });
    }

    function initCamera() {
        if (typeof Html5Qrcode === 'undefined') { showCameraError('Library scanner gagal dimuat.', ''); return; }
        if (!isSecureContextOk()) { showCameraError('Kamera hanya bisa diakses lewat HTTPS atau localhost.', ''); return; }
        if (state.isRunning) return;

        state.html5Qrcode = new Html5Qrcode('qr-reader', {
            verbose: false
        });
        setStatus('status-processing', 'Memulai kamera...');

        function startWithTarget(target) {
            return state.html5Qrcode.start(
                target, qrConfig,
                function(decodedText) { if (!state.isStopped) onScanSuccess(decodedText); },
                function() {}
            );
        }

        // Strategi Kilat: Gunakan ID kamera langsung (bebas delay negosiasi WebRTC)
        if (Html5Qrcode.getCameras) {
            Html5Qrcode.getCameras().then(function(devices) {
                if (devices && devices.length > 0) {
                    state.cameras = devices;
                    // Pilih kamera belakang jika ada, atau kamera pertama (webcam laptop/PC)
                    var backIndex = devices.findIndex(function(d){ return /back|belakang|rear|environment/i.test(d.label || ''); });
                    if (backIndex === -1 && devices.length > 1) {
                        backIndex = devices.length - 1; // Kamera belakang di akhir pada mobile
                    } else if (backIndex === -1) {
                        backIndex = 0;
                    }
                    state.cameraIndex = backIndex;
                    var chosenCam = devices[state.cameraIndex];
                    
                    startWithTarget(chosenCam.id)
                        .then(onCameraReady)
                        .catch(function() {
                            startWithTarget({ facingMode: 'environment' })
                                .then(onCameraReady)
                                .catch(function() {
                                    startWithTarget({ facingMode: 'user' })
                                        .then(onCameraReady)
                                        .catch(function(err3) {
                                            var d = describeError(err3);
                                            showCameraError(d.title, d.detail);
                                        });
                                });
                        });
                } else {
                    startWithTarget({ facingMode: 'environment' })
                        .then(onCameraReady)
                        .catch(function() {
                            startWithTarget({ facingMode: 'user' })
                                .then(onCameraReady)
                                .catch(function(err3) {
                                    var d = describeError(err3);
                                    showCameraError(d.title, d.detail);
                                });
                        });
                }
            }).catch(function() {
                startWithTarget({ facingMode: 'environment' })
                    .then(onCameraReady)
                    .catch(function() {
                        startWithTarget({ facingMode: 'user' })
                            .then(onCameraReady)
                            .catch(function(err3) {
                                var d = describeError(err3);
                                showCameraError(d.title, d.detail);
                            });
                    });
            });
        } else {
            startWithTarget({ facingMode: 'environment' })
                .then(onCameraReady)
                .catch(function() {
                    startWithTarget({ facingMode: 'user' })
                        .then(onCameraReady)
                        .catch(function(err3) {
                            var d = describeError(err3);
                            showCameraError(d.title, d.detail);
                        });
                });
        }
    }

    function stopCamera() {
        if (state.html5Qrcode && state.isRunning) {
            return state.html5Qrcode.stop().then(function(){ state.isRunning=false; }).catch(function(){ state.isRunning=false; });
        }
        return Promise.resolve();
    }

    // ═══════════════════════════════════════
    // UPLOAD GAMBAR
    // ═══════════════════════════════════════
    function loadImageFile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        if (file.size > 5 * 1024 * 1024) { alert('Ukuran gambar maksimal 5 MB.'); return; }
        var reader = new FileReader();
        reader.onload = function(e) {
            imgPreview.src = e.target.result;
            imgPreview.classList.remove('hidden');
            imgDropInner.classList.add('hidden');
            btnScanImg.disabled = false;
            btnClearImg.classList.remove('hidden');
            imgResult.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    imgInput.addEventListener('change', function() { if (this.files[0]) loadImageFile(this.files[0]); });

    // Drag & drop
    imgDropArea.addEventListener('dragover', function(e){ e.preventDefault(); this.classList.add('drag-over'); });
    imgDropArea.addEventListener('dragleave', function(){ this.classList.remove('drag-over'); });
    imgDropArea.addEventListener('drop', function(e){
        e.preventDefault(); this.classList.remove('drag-over');
        var f = e.dataTransfer.files[0]; if(f) loadImageFile(f);
    });

    btnClearImg.addEventListener('click', function(e) {
        e.preventDefault();
        imgPreview.src = ''; imgPreview.classList.add('hidden');
        imgDropInner.classList.remove('hidden');
        btnScanImg.disabled = true; btnClearImg.classList.add('hidden');
        imgResult.classList.add('hidden'); imgInput.value = '';
    });

    btnScanImg.addEventListener('click', function() {
        if (!imgPreview.src || btnScanImg.disabled) return;
        if (typeof Html5Qrcode === 'undefined') { alert('Library scanner belum dimuat.'); return; }

        btnScanImg.disabled = true;
        btnScanImg.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memindai...';
        imgResult.classList.add('hidden');

        // Buat elemen hidden untuk scan file
        Html5Qrcode.scanFile(imgInput.files[0] || dataURItoBlob(imgPreview.src), true)
            .then(function(result) {
                btnScanImg.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Scan Gambar';
                imgResult.classList.remove('hidden');
                imgResult.style.background = '#f0fdf4'; imgResult.style.border = '1px solid #bbf7d0'; imgResult.style.color = '#166534';
                imgResult.innerHTML = '<i class="fa-solid fa-qrcode"></i> Barcode terbaca: <strong>' + esc(result) + '</strong>';
                setStatus('status-processing', 'Memproses barcode dari gambar...');

                geoGuardThenSubmit(result, function(id) {
                    kirimKeBackend(id).then(function(payload) {
                        var r = interpretResponse(payload);
                        showResult(r.iconBg, r.iconColor, r.iconClass, r.title, r.message, r.detailsHtml, r.guru);
                        if (r.badgeText) { resultBadge.className='badge '+r.badgeClass; resultBadge.textContent=r.badgeText; }
                        setStatus(r.success?'status-success':'status-error', r.success?'Berhasil':'Gagal');
                        btnScanImg.disabled = false;
                    }).catch(function() {
                        showResult('#fee2e2','#ef4444','fa-triangle-exclamation','Kesalahan Koneksi','Tidak dapat terhubung ke server.','',null);
                        btnScanImg.disabled = false;
                    });
                });
            })
            .catch(function() {
                btnScanImg.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Scan Gambar';
                btnScanImg.disabled = false;
                imgResult.classList.remove('hidden');
                imgResult.style.background = '#fef2f2'; imgResult.style.border = '1px solid #fecaca'; imgResult.style.color = '#991b1b';
                imgResult.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Barcode/QR tidak terdeteksi. Pastikan gambar jelas dan tidak buram.';
            });
    });

    function dataURItoBlob(dataURI) {
        var byteString = atob(dataURI.split(',')[1]);
        var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
        var ab = new ArrayBuffer(byteString.length);
        var ia = new Uint8Array(ab);
        for (var i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
        return new Blob([ab], { type: mimeString });
    }

    // ═══════════════════════════════════════
    // TAB SWITCHING
    // ═══════════════════════════════════════
    document.querySelectorAll('.scan-tab').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tabId = this.getAttribute('data-tab');
            document.querySelectorAll('.scan-tab').forEach(function(b){ b.classList.remove('active'); });
            this.classList.add('active');
            panelCamera.classList.add('hidden');
            panelImage.classList.add('hidden');
            resetScannerUI();
            if (tabId === 'camera') {
                panelCamera.classList.remove('hidden');
                if (!state.isRunning && !state.isStopped && geoState.ok) initCamera();
                else if (state.isRunning) setStatus('status-active', 'Kamera aktif — arahkan barcode/QR');
            } else if (tabId === 'image') {
                panelImage.classList.remove('hidden');
                setStatus('status-idle', 'Upload gambar barcode/QR untuk di-scan');
                stopCamera();
            }
        });
    });

    // ═══════════════════════════════════════
    // TOMBOL
    // ═══════════════════════════════════════
    btnSwitchCamera.addEventListener('click', function() {
        if (!state.cameras.length) return;
        state.cameraIndex = (state.cameraIndex + 1) % state.cameras.length;
        var nextId = state.cameras[state.cameraIndex].id;
        stopCamera().then(function() {
            cameraPlaceholder.classList.remove('hidden');
            placeholderText.textContent = 'Mengganti kamera...';
            scanFrame.classList.add('hidden');
            state.html5Qrcode.start(nextId, qrConfig,
                function(dt){ if (!state.isStopped) onScanSuccess(dt); }, function(){}
            ).then(onCameraReady).catch(function(err){ var d=describeError(err); showCameraError(d.title, d.detail); });
        });
    });

    btnRetry.addEventListener('click', function() {
        state.isStopped = false; state.isPaused = false; state.lastResult = null;
        resetScannerUI();
        var activeTab = document.querySelector('.scan-tab.active');
        if (activeTab && activeTab.getAttribute('data-tab') === 'camera') {
            if (!state.isRunning) initCamera();
            else setStatus('status-active', 'Kamera aktif — arahkan barcode');
        } else {
            setStatus('status-idle', 'Siap');
        }
    });

    btnClose.addEventListener('click', function() {
        stopCamera().then(function(){ window.location.href = '<?= BASEURL; ?>/dashboard'; });
    });


    // ═══════════════════════════════════════
    // INIT
    // ═══════════════════════════════════════
    // 1. Muat geo config dari server, lalu cek GPS, baru start kamera
    loadGeoConfigThenCheck();

})();
</script>