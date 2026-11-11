</main>
    <footer class="w-full mt-auto py-6 px-4 sm:px-8 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 ">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" alt="Logo SMK" width="24" height="24" class="h-6 w-6 object-contain shrink-0 opacity-80 hover:opacity-100 transition-opacity" loading="lazy" decoding="async">
                <span class="font-bold text-[#163300] dark:text-emerald-400 font-headline uppercase tracking-tight text-[11px]">
                    SMK NU Wargabinangun
                </span>
                <span class="text-slate-300 dark:text-slate-700 hidden sm:inline">&middot;</span>
                <span class="text-slate-400 dark:text-slate-500 hidden sm:inline text-[11px]">Presensi RFID &amp; AI</span>
            </div>
            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 tracking-wider">
                &copy; 2026 <span class="font-bold text-[#163300] dark:text-[#9fe870]">Nihlah.Auliya</span>. All Rights Reserved
            </p>
        </div>
    </footer>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<!-- ═══════════════════════════════════════════════════
     CHATBOT FLOATING WIDGET
     Semua HTML + CSS + JS disatukan di sini.
     Tidak bergantung pada file eksternal chatbot.js.
     ═══════════════════════════════════════════════════ -->

<style>
/* ── Layout Utama Widget ── */
#cw-btn {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 9999;
    width: 52px;
    height: 52px;
    border-radius: 9999px;
    background: #059669;
    color: #ffffff;
    border: 1px solid #10b981;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.18);
    transition: transform 0.15s ease, background-color 0.15s ease;
}
#cw-btn:hover {
    background: #047857;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px 0 rgba(0, 0, 0, 0.22);
}
#cw-btn:active {
    transform: scale(0.96);
}

/* ── Jendela Chat ── */
#cw-win {
    position: fixed;
    bottom: 5.2rem;
    right: 1.5rem;
    z-index: 9998;
    width: 360px;
    max-width: calc(100vw - 1.5rem);
    height: 520px;
    max-height: calc(100vh - 7rem);
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid rgba(203,213,225,.8);
    box-shadow: 0 12px 40px rgba(0,0,0,.14);
    display: none;               /* JS toggle ke flex */
    flex-direction: column;
    overflow: hidden;
/* Animasi muncul */
    animation: cwSlideUp .22s cubic-bezier(.16,1,.3,1);
}
@media (max-width: 640px) {
    #cw-btn {
        bottom: 1rem;
        right: 1rem;
        width: 50px;
        height: 50px;
        border-radius: 9999px;
    }
    #cw-win {
        bottom: 4.5rem;
        left: 0.75rem;
        right: 0.75rem;
        width: auto;
        max-width: none;
        height: 75vh;
        max-height: calc(100vh - 5.5rem);
        border-radius: 16px;
    }
}
@keyframes cwSlideUp {
    from { opacity:0; transform:translateY(12px) scale(.96); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}

/* ── Header ── */
#cw-header {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .75rem 1rem;
    background: linear-gradient(135deg, #065f46, #0f766e);
    color: #fff;
}
.cw-header-info { display:flex; align-items:center; gap:.75rem; }
.cw-avatar {
    width: 36px; height: 36px;
    border-radius: 12px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.3);
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.cw-title { font-weight: 700; font-size: .875rem; line-height: 1.2; }
.cw-status {
    font-size: .625rem;
    color: rgba(167,243,208,1);
    font-weight: 600;
    display: flex; align-items: center; gap: .3rem;
    margin-top: 1px;
}
.cw-status-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #6ee7b7;
    animation: cwPulse 2s ease-in-out infinite;
}
#cw-refresh-btn, #cw-close-btn {
    width: 32px; height: 32px;
    border-radius: 10px;
    background: rgba(255,255,255,.12);
    border: none; cursor: pointer; color: rgba(255,255,255,.85);
    display: flex; align-items:center; justify-content:center;
    font-size: .9rem;
    transition: background .15s;
    flex-shrink: 0;
}
#cw-refresh-btn:hover, #cw-close-btn:hover { background: rgba(255,255,255,.22); color:#fff; }

/* ── Area Pesan ── */
#cw-msgs {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    scroll-behavior: smooth;
}
#cw-msgs::-webkit-scrollbar { width: 4px; }
#cw-msgs::-webkit-scrollbar-track { background: transparent; }
#cw-msgs::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }

/* Bubble animasi masuk */
.cw-row {
    display: flex;
    animation: cwBubble .25s cubic-bezier(.16,1,.3,1) forwards;
}
@keyframes cwBubble {
    from { opacity:0; transform:translateY(5px); }
    to   { opacity:1; transform:translateY(0); }
}
.cw-row.user { justify-content: flex-end; }
.cw-row.bot  { align-items: flex-start; gap: .5rem; }

/* Bubble user */
.cw-bubble-user {
    max-width: 78%;
    padding: .6rem .9rem;
    border-radius: 18px 18px 4px 18px;
    background: linear-gradient(135deg,#059669,#0f766e);
    color: #fff;
    font-size: .8125rem;
    line-height: 1.5;
    word-break: break-word;
    font-weight: 500;
}

/* Avatar bot */
.cw-bot-av {
    width: 28px; height: 28px;
    border-radius: 9px;
    background: linear-gradient(135deg,#059669,#0f766e);
    display: flex; align-items:center; justify-content:center;
    font-size: .7rem;
    color: #fff;
    flex-shrink: 0;
    margin-top: 2px;
}

/* Bubble bot */
.cw-bubble-bot-wrap { display:flex; flex-direction:column; gap:3px; max-width:87%; }
.cw-bot-label { font-size:.6rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em; }
.cw-bubble-bot {
    padding: .6rem .9rem;
    border-radius: 4px 18px 18px 18px;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: .8125rem;
    line-height: 1.55;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.cw-bubble-bot.is-err {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

/* Markdown sederhana dalam bubble bot */
.cw-bubble-bot ul  { list-style:none; padding-left:0; margin:.25rem 0; }
.cw-bubble-bot ol  { list-style:decimal; padding-left:1.2rem; margin:.3rem 0; }
.cw-bubble-bot li  { margin-bottom:.15rem; line-height:1.45; }
.cw-bubble-bot strong { font-weight:700; color:#0f766e; }
.cw-bubble-bot code {
    background:#f1f5f9; color:#0f766e;
    padding:.1rem .3rem; border-radius:4px;
    font-size:.75em; font-family:monospace;
}

/* ── Typing Indicator ── */
#cw-typing {
    flex-shrink: 0;
    display: none;   /* JS toggle */
    align-items: flex-start;
    gap: .5rem;
    padding: 0 1rem .75rem;
}
.cw-typing-dots {
    display: flex; align-items: center; gap: .3rem;
    padding: .5rem .75rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 4px 14px 14px 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.cw-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #10b981;
    animation: cwDot 1.1s ease-in-out infinite;
}
.cw-dot:nth-child(2) { animation-delay: .18s; }
.cw-dot:nth-child(3) { animation-delay: .36s; }
@keyframes cwDot {
    0%,60%,100% { transform:translateY(0); }
    30%          { transform:translateY(-5px); }
}

/* ── Input Bar ── */
#cw-input-bar {
    flex-shrink: 0;
    padding: .6rem .75rem .75rem;
    background: #fff;
    border-top: 1px solid #e2e8f0;
}
.cw-input-row { display:flex; align-items:center; gap:.5rem; }
#cw-input {
    flex: 1;
    min-width: 0;
    padding: .6rem 1rem;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: .8125rem;
    color: #1e293b;
    font-family: inherit;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
#cw-input::placeholder { color:#94a3b8; }
#cw-input:focus {
    border-color: #059669;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(5,150,105,.15);
}
#cw-send {
    flex-shrink: 0;
    width: 38px; height: 38px;
    border-radius: 11px;
    background: linear-gradient(135deg,#059669,#0f766e);
    border: none; cursor: pointer;
    color: #fff;
    display: flex; align-items:center; justify-content:center;
    font-size: .75rem;
    transition: transform .15s, opacity .15s;
}
#cw-send:hover { transform: scale(1.07); }
#cw-send:disabled { opacity:.4; cursor:not-allowed; transform:none; }
.cw-footer-note { text-align:center; font-size:.6rem; color:#94a3b8; margin-top:.4rem; }

/* ── Dark Mode Overrides for Chatbot ── */
.dark #cw-win {
    background: #0f172a;
    border-color: #334155;
    box-shadow: 0 16px 48px rgba(0,0,0,.5);
}
.dark #cw-msgs::-webkit-scrollbar-thumb {
    background: #334155;
}
.dark .cw-bubble-bot {
    background: #0f172a;
    border-color: #334155;
    color: #f1f5f9;
    box-shadow: 0 1px 4px rgba(0,0,0,.25);
}
.dark .cw-bubble-bot strong {
    color: #34d399;
}
.dark .cw-bubble-bot code {
    background: #0f172a;
    color: #34d399;
}
.dark .cw-typing-dots {
    background: #0f172a;
    border-color: #334155;
}
.dark #cw-input-bar {
    background: #0f172a;
    border-color: #334155;
}
.dark #cw-input {
    background: #0f172a;
    border-color: #334155;
    color: #f8fafc;
}
.dark #cw-input::placeholder {
    color: #64748b;
}
.dark #cw-input:focus {
    border-color: #10b981;
    background: #0f172a;
    box-shadow: 0 0 0 3px rgba(16,185,129,.2);
}
.dark .cw-footer-note {
    color: #64748b;
}
</style>

<!-- ── FAB Button ── -->
<button id="cw-btn" onclick="cwOpen()" aria-label="Buka Asisten Layanan" title="Tanya Informasi Presensi">
    <i class="fa-solid fa-headset text-2xl text-white"></i>
</button>

<!-- ── Jendela Chat ── -->
<div id="cw-win">

    <!-- Header -->
    <div id="cw-header">
        <div class="cw-header-info">
            <div class="cw-avatar" style="background:#047857; color:#fff; display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-headset text-sm"></i>
            </div>
            <div>
                <div class="cw-title">Layanan Informasi Presensi</div>
                <div class="cw-status">
                    <span class="cw-status-dot"></span>
                    Online • Database Terhubung
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 0.35rem;">
            <button id="cw-refresh-btn" onclick="cwRefresh()" aria-label="Refresh Chat" title="Hapus Riwayat Chat">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
            <button id="cw-close-btn" onclick="cwClose()" aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- Pesan -->
    <div id="cw-msgs">
        <!-- Pesan awal dari bot -->
        <div class="cw-row bot">
            <div class="cw-bot-av"><i class="fa-solid fa-comments text-[11px]"></i></div>
            <div class="cw-bubble-bot-wrap">
                <div class="cw-bot-label">Asisten Presensi</div>
                <div class="cw-bubble-bot">
                    Halo! Saya siap membantu memberikan informasi data <strong>siswa, guru, kelas, dan presensi</strong> SMK NU Wargabinangun.<br>
                    Silakan ketik pertanyaan Anda seputar database sekolah!
                </div>
            </div>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div id="cw-typing">
        <div class="cw-bot-av"><i class="fa-solid fa-comments text-[11px]"></i></div>
        <div class="cw-typing-dots">
            <span class="cw-dot"></span>
            <span class="cw-dot"></span>
            <span class="cw-dot"></span>
        </div>
    </div>

    <!-- Input Bar -->
    <div id="cw-input-bar">
        <div class="cw-input-row">
            <input id="cw-input" type="text" placeholder="Tanyakan sesuatu..." autocomplete="off">
            <button id="cw-send" onclick="cwSend()" aria-label="Kirim">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
        <p class="cw-footer-note">Layanan Presensi • SMK NU Wargabinangun</p>
    </div>
</div>

<script>
(function () {
    var BASE = '<?= BASEURL; ?>';

    var btn    = document.getElementById('cw-btn');
    var win    = document.getElementById('cw-win');
    var input  = document.getElementById('cw-input');
    var send   = document.getElementById('cw-send');
    var msgs   = document.getElementById('cw-msgs');
    var typing = document.getElementById('cw-typing');

    /* ── Buka / Tutup ── */
    window.cwOpen = function () {
        win.style.display = 'flex';
        btn.style.opacity = '0';
        btn.style.pointerEvents = 'none';
        setTimeout(function () { if (input) input.focus(); }, 80);
        cwScroll();
    };

    window.cwClose = function () {
        win.style.display = 'none';
        btn.style.opacity = '1';
        btn.style.pointerEvents = '';
    };

    /* Tutup dengan tombol Escape */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && win.style.display === 'flex') cwClose();
    });

    /* ── Refresh Chat ── */
    window.cwRefresh = function () {
        if (!confirm('Bersihkan layar chat ini?')) return;
        
        // Kosongkan msgs container tapi simpan pesan awal
        if (msgs) {
            msgs.innerHTML = `
            <!-- Pesan awal dari bot -->
            <div class="cw-row bot">
                <div class="cw-bot-av"><i class="fa-solid fa-robot"></i></div>
                <div class="cw-bubble-bot-wrap">
                    <div class="cw-bot-label">Asisten Presensi</div>
                    <div class="cw-bubble-bot">
                        Halo! Saya siap membantu seputar <strong>sistem absensi RFID</strong>.<br>
                        Ada yang ingin ditanyakan?
                    </div>
                </div>
            </div>`;
        }
    };

    /* ── Kirim pesan ── */
    window.cwSend = function () {
        if (!input) return;
        var msg = input.value.trim();
        if (!msg) return;

        cwAppend('user', msg);
        input.value = '';
        input.disabled = true;
        send.disabled  = true;
        cwShowTyping();

        fetch(BASE + '/chatbot', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ message: msg })
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (d) {
            cwHideTyping();
            cwAppend('bot', d.error || d.response || '(tidak ada respons)', !!d.error);
        })
        .catch(function (e) {
            cwHideTyping();
            cwAppend('bot', 'Gagal menghubungi AI: ' + e.message, true);
        })
        .finally(function () {
            input.disabled = false;
            send.disabled  = false;
            if (win.style.display === 'flex') input.focus();
        });
    };

    /* Enter untuk kirim */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && win && win.style.display === 'flex'
            && document.activeElement === input && !e.shiftKey) {
            e.preventDefault();
            cwSend();
        }
    });

    /* ── Append bubble ── */
    function cwAppend(sender, text, isErr) {
        var row = document.createElement('div');
        row.className = 'cw-row ' + sender;

        if (sender === 'user') {
            var b = document.createElement('div');
            b.className = 'cw-bubble-user';
            b.textContent = text;
            row.appendChild(b);
        } else {
            var av = document.createElement('div');
            av.className = 'cw-bot-av';
            av.innerHTML = '<i class="fa-solid fa-comments text-[11px]"></i>';

            var wrap = document.createElement('div');
            wrap.className = 'cw-bubble-bot-wrap';

            var lbl = document.createElement('div');
            lbl.className = 'cw-bot-label';
            lbl.textContent = 'Asisten Presensi';

            var bub = document.createElement('div');
            bub.className = 'cw-bubble-bot' + (isErr ? ' is-err' : '');

            if (isErr) {
                bub.textContent = text;
            } else {
                bub.innerHTML = cwRender(text);
            }

            wrap.appendChild(lbl);
            wrap.appendChild(bub);
            row.appendChild(av);
            row.appendChild(wrap);
        }

        msgs.appendChild(row);
        cwScroll();
    }

    /* ── Render markdown sederhana ── */
    function cwRender(t) {
        // escape dulu agar aman
        t = t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        // bold **...**
        t = t.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // inline code `...`
        t = t.replace(/`([^`]+)`/g, '<code>$1</code>');
        // bullet - item
        t = t.replace(/^[-*] (.+)$/gm, '<li class="list-disc ml-3">$1</li>');
        // numbered 1. item
        t = t.replace(/^(\d+)\. (.+)$/gm, '<li class="list-none"><span class="font-bold text-emerald-700">$1.</span> $2</li>');
        // wrap <li> berurutan jadi <ul>
        t = t.replace(/(<li.*?<\/li>(\n|<br>)?)+/gs, function(m){
            return '<ul style="list-style:none;padding-left:0;margin:0.25rem 0;">' + m.replace(/<br>/g,'').replace(/\n+/g,'') + '</ul>';
        });
        // newline → <br>
        t = t.replace(/\n{2,}/g, '<br><br>').replace(/\n/g, '<br>');
        return t;
    }

    /* ── Typing indicator ── */
    function cwShowTyping() {
        if (!typing) return;
        typing.style.display = 'flex';
        cwScroll();
    }
    function cwHideTyping() {
        if (typing) typing.style.display = 'none';
    }

    function cwScroll() {
        if (msgs) msgs.scrollTop = msgs.scrollHeight;
    }
})();
</script>

<?php endif; ?>

<!-- ── Sidebar Toggle (Mobile & Desktop) ── -->
<script>
function isSidebarOpen() {
    var sidebar = document.getElementById('sidebarMenu');
    if (!sidebar) return false;
    var isDesktop = window.innerWidth >= 768;
    if (isDesktop) {
        return !sidebar.classList.contains('sidebar-closed');
    } else {
        return sidebar.classList.contains('sidebar-open');
    }
}

function updateSidebarUI(isOpen) {
    var sidebar = document.getElementById('sidebarMenu');
    var overlay = document.getElementById('sidebarOverlay');
    var main = document.getElementById('mainContent');
    var icon = document.getElementById('sidebarToggleIcon');
    var isDesktop = window.innerWidth >= 768;

    if (isOpen) {
        // Menu Terbuka (Kelihatan menu fiturnya) -> Tampilkan tanda X
        if (sidebar) {
            sidebar.classList.remove('sidebar-closed');
            sidebar.classList.add('sidebar-open');
        }
        if (main && isDesktop) {
            main.classList.remove('content-expanded');
        }
        if (overlay && !isDesktop) {
            overlay.classList.remove('hidden');
        }
        if (icon) {
            icon.className = 'fa-solid fa-xmark text-base';
        }
    } else {
        // Menu Tertutup (Nga ditampilin) -> Tampilkan icon titik tiga vertikal
        if (sidebar) {
            sidebar.classList.remove('sidebar-open');
            sidebar.classList.add('sidebar-closed');
        }
        if (main && isDesktop) {
            main.classList.add('content-expanded');
        }
        if (overlay) {
            overlay.classList.add('hidden');
        }
        if (icon) {
            icon.className = 'fa-solid fa-ellipsis-vertical text-base';
        }
    }
}

function toggleSidebar() {
    var currentlyOpen = isSidebarOpen();
    var newState = !currentlyOpen;
    updateSidebarUI(newState);
    try {
        localStorage.setItem('sidebar_pref', newState ? 'open' : 'closed');
    } catch(e){}
}

// Inisialisasi status menu saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    var isDesktop = window.innerWidth >= 768;
    var pref = null;
    try { pref = localStorage.getItem('sidebar_pref'); } catch(e){}

    if (isDesktop) {
        // Pada layar desktop, default terbuka kecuali jika user pernah menutupnya
        if (pref === 'closed') {
            updateSidebarUI(false);
        } else {
            updateSidebarUI(true);
        }
    } else {
        // Pada layar mobile, default tertutup
        updateSidebarUI(false);
    }
});

// Tutup sidebar otomatis di mobile saat salah satu link diklik
document.querySelectorAll('#sidebarMenu a').forEach(function (link) {
    link.addEventListener('click', function () {
        if (window.innerWidth < 768) {
            updateSidebarUI(false);
        }
    });
});
</script>

<script defer src="<?= BASEURL; ?>/assets/js/script.js"></script>


<!-- PWA Service Worker & Install Flow -->
<script>
    const BASE_URL = '<?= BASEURL; ?>';
</script>
<script defer src="<?= BASEURL; ?>/assets/js/pwa.js?v=3.0"></script>
<?php if (isset($_SESSION['user_id'])): ?>
<script defer src="<?= BASEURL; ?>/assets/js/notification.js?v=2.1"></script>
<?php endif; ?>
<!-- Offline Status Indicator -->
<div id="offlineIndicator" class="hidden fixed bottom-4 right-4 z-50 bg-amber-600/95 dark:bg-amber-700/95 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl shadow-lg backdrop-blur-xs flex items-center gap-2 border border-amber-400/40 transition-all">
    <span class="w-2 h-2 rounded-full bg-amber-200 animate-pulse"></span>
    <span>Mode Offline Aktif &middot; Akses Data Cache</span>
</div>
<script>
(function() {
    function updateOnlineStatus() {
        const el = document.getElementById('offlineIndicator');
        if (!el) return;
        if (!navigator.onLine) {
            el.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-200 animate-pulse"></span><span>Mode Offline Aktif &middot; Akses Data Cache</span>';
            el.className = 'fixed bottom-4 right-4 z-50 bg-amber-600/95 dark:bg-amber-700/95 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl shadow-lg backdrop-blur-xs flex items-center gap-2 border border-amber-400/40 transition-all';
            el.classList.remove('hidden');
        } else {
            if (!el.classList.contains('hidden')) {
                el.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-200"></span><span>Koneksi Kembali Online</span>';
                el.className = 'fixed bottom-4 right-4 z-50 bg-emerald-600/95 dark:bg-emerald-700/95 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl shadow-lg backdrop-blur-xs flex items-center gap-2 border border-emerald-400/40 transition-all';
                setTimeout(() => el.classList.add('hidden'), 2500);
            }
        }
    }
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    if (!navigator.onLine) updateOnlineStatus();
})();
</script>
</body>
</html>