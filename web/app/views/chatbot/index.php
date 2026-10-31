<?php $this->view('templates/header', ['judul' => 'AI Assistant | Absensi SMK']); ?>

<style>
/* ── Chatbot Full-Page Layout ── */
#cb-page-messages::-webkit-scrollbar       { width: 5px; }
#cb-page-messages::-webkit-scrollbar-track { background: transparent; }
#cb-page-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }
#cb-page-messages::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

/* Bubble fade-in */
.cb-page-row { animation: cbPageFade 0.3s cubic-bezier(0.16,1,0.3,1) forwards; }
@keyframes cbPageFade {
    from { opacity:0; transform:translateY(7px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Typing dots */
.cb-dot { animation: cbBounce 1.2s infinite; }
.cb-dot:nth-child(2) { animation-delay: 0.2s; }
.cb-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes cbBounce {
    0%, 80%, 100% { transform: translateY(0); }
    40%           { transform: translateY(-5px); }
}

/* Markdown rendered in bot bubbles */
.cb-bot-text ul     { list-style: none; padding-left: 0; margin-top: 0.35rem; margin-bottom: 0.35rem; }
.cb-bot-text ol     { list-style: decimal; padding-left: 1.25rem; margin-top: 0.3rem; }
.cb-bot-text li     { margin-bottom: 0.2rem; line-height: 1.45; }
.cb-bot-text strong { font-weight: 700; color: #0f766e; }
.cb-bot-text code   { background:#f0fdf4; color:#065f46; padding:0.1rem 0.35rem; border-radius:5px; font-size:0.8em; font-family:monospace; }
.cb-bot-text p      { margin-bottom: 0.4rem; }
.cb-bot-text a      { color:#059669; text-decoration:underline; }

/* Input focus ring */
#cb-page-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,0.18); border-color: #059669; }
</style>

<!-- Page Wrapper -->
<div class="flex flex-col" style="height: calc(100vh - 4rem - 2rem);">

    <!-- ── Header Bar ── -->
    <div class="flex items-center justify-between px-1 pb-4 border-b border-slate-200/80 mb-1 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                 style="background:linear-gradient(135deg,#065f46,#0f766e);">
                <i class="fa-solid fa-robot text-white"></i>
            </div>
            <div>
                <h1 class="text-sm font-extrabold text-slate-800 tracking-tight leading-tight">AI Assistant SMK</h1>
                <p class="text-[11px] font-semibold text-emerald-600 leading-tight flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    Online &bull; Siap Membantu Anda
                </p>
            </div>
        </div>
        <a href="<?= BASEURL; ?>/dashboard"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-800 text-xs font-semibold rounded-xl transition-all">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- ── Chat Messages Stream ── -->
    <div id="cb-page-messages" class="flex-1 overflow-y-auto py-5 space-y-5 px-1">

        <!-- Pesan Selamat Datang -->
        <div class="cb-page-row flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center mt-0.5"
                 style="background:linear-gradient(135deg,#059669,#0f766e);">
                <i class="fa-solid fa-robot text-white text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wide">AI Assistant</p>
                <div class="cb-bot-text bg-white rounded-2xl rounded-tl-sm px-4 py-3 text-slate-700 text-sm leading-relaxed shadow-sm border border-slate-100 max-w-[75%]">
                    Halo! Saya adalah <strong>AI Assistant</strong> sistem absensi RFID SMK NU Wargabinangun.<br><br>
                    Saya dapat membantu Anda dengan:
                    <ul>
                        <li>Pertanyaan seputar <strong>sistem absensi & presensi</strong></li>
                        <li>Troubleshooting <strong>RFID & ESP32</strong></li>
                        <li>Panduan penggunaan <strong>fitur-fitur portal</strong></li>
                        <li>Informasi jadwal & izin</li>
                    </ul>
                    <br>Ada yang ingin Anda tanyakan?
                </div>
            </div>
        </div>

    </div>

    <!-- ── Typing Indicator ── -->
    <div id="cb-page-typing" class="hidden shrink-0 px-1 pb-3">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center"
                 style="background:linear-gradient(135deg,#059669,#0f766e);">
                <i class="fa-solid fa-robot text-white text-xs"></i>
            </div>
            <div class="flex items-center">
                <div class="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm border border-slate-100 flex items-center gap-2">
                    <span class="text-slate-400 text-xs font-medium mr-1">Memproses</span>
                    <span class="cb-dot w-2 h-2 rounded-full bg-emerald-500 block"></span>
                    <span class="cb-dot w-2 h-2 rounded-full bg-emerald-500 block"></span>
                    <span class="cb-dot w-2 h-2 rounded-full bg-emerald-500 block"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Input Bar ── -->
    <div class="shrink-0 pt-3 pb-1">
        <!-- Suggestion Chips (Sesuai Peran / Role) -->
        <?php
        $currentRole = SessionHelper::get('role') ?? 'siswa';
        $chips = match($currentRole) {
            'siswa' => [
                'Jadwal absensi saya hari ini?',
                'Cek riwayat kehadiran saya',
                'Status kartu RFID saya',
                'Status pengajuan izin saya?',
            ],
            'guru' => [
                'Jadwal mengajar saya hari ini',
                'Rekap kehadiran saya bulan ini',
                'Cek barcode & profil saya',
                'Solusi scan QR gagal?',
            ],
            'wali_kelas', 'walikelas' => [
                'Siapa saja siswa kelasku yang hadir?',
                'Daftar siswa di kelas binaan',
                'Ada izin siswa yang pending?',
                'Jadwal mengajar saya hari ini',
            ],
            'admin', 'kepsek' => [
                'Ringkasan statistik sistem hari ini',
                'Siapa saja guru yang hadir hari ini?',
                'Cek status perangkat IoT scanner',
                'Rekap kehadiran siswa kelas',
            ],
            default => [
                'Panduan sistem absensi?',
                'Jadwal absensi sekolah hari ini?',
                'Kendala scan RFID/QR?',
                'Informasi izin absensi?',
            ]
        };
        ?>
        <div class="flex flex-wrap gap-2 mb-3" id="cb-chips">
            <?php foreach ($chips as $chip): ?>
                <button onclick="cbChip(this)" class="px-3 py-1.5 bg-white border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 text-xs font-semibold rounded-full transition-all shadow-xs"><?= htmlspecialchars($chip); ?></button>
            <?php endforeach; ?>
        </div>

        <form id="cb-page-form" onsubmit="event.preventDefault(); cbPageSend();" class="relative flex items-center gap-2">
            <div class="flex-1 relative">
                <input
                    type="text"
                    id="cb-page-input"
                    placeholder="Tanyakan sesuatu ke AI Assistant..."
                    autocomplete="off"
                    class="w-full bg-white border border-slate-200 rounded-2xl pl-5 pr-5 py-3.5 text-sm text-slate-800 placeholder-slate-400 transition-all"
                    style="box-shadow: 0 1px 4px rgba(0,0,0,0.06);"
                >
            </div>
            <button
                type="submit"
                id="cb-page-send"
                class="shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center text-white font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:scale-105 hover:shadow-md"
                style="background:linear-gradient(135deg,#059669,#0f766e);"
                aria-label="Kirim"
            >
                <i class="fa-solid fa-paper-plane text-sm"></i>
            </button>
        </form>

        <p class="text-[10px] text-center text-slate-400 mt-2.5">
            <i class="fa-solid fa-circle-info mr-0.5"></i>
            AI dapat membuat kekeliruan &bull; Max 20 pertanyaan AI/hari &bull; Verifikasi info penting dengan admin
        </p>
    </div>

</div>

<script>
var cbBase    = "<?= BASEURL; ?>";
var cbMsgArea = document.getElementById('cb-page-messages');
var cbTyping  = document.getElementById('cb-page-typing');
var cbInput   = document.getElementById('cb-page-input');
var cbSend    = document.getElementById('cb-page-send');

// Quick chip
function cbChip(btn) {
    if (!cbInput) return;
    cbInput.value = btn.textContent.replace(/^[^\w]+/, '').trim();
    cbInput.focus();
    // Sembunyikan chips setelah pakai
    var chips = document.getElementById('cb-chips');
    if (chips) chips.style.display = 'none';
}

function cbPageSend() {
    if (!cbInput || !cbSend) return;
    var msg = cbInput.value.trim();
    if (!msg) return;

    cbAppend('user', msg);
    cbInput.value    = '';
    cbInput.disabled = true;
    cbSend.disabled  = true;

    // Sembunyikan chips permanen setelah kirim pertama
    var chips = document.getElementById('cb-chips');
    if (chips) chips.style.display = 'none';

    cbShowTyping();

    fetch(cbBase + '/chatbot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ message: msg })
    })
    .then(function(res) {
        if (!res.ok) {
            if (res.status === 401) throw new Error('Sesi berakhir. Silakan login kembali.');
            throw new Error('Kesalahan server (HTTP ' + res.status + ').');
        }
        return res.json();
    })
    .then(function(data) {
        cbHideTyping();
        cbAppend('bot', data.error || data.response || '(tidak ada respons)', !!data.error);
    })
    .catch(function(err) {
        cbHideTyping();
        cbAppend('bot', 'Gagal menghubungi AI. ' + err.message, true);
    })
    .finally(function() {
        cbInput.disabled = false;
        cbSend.disabled  = false;
        cbInput.focus();
    });
}

function cbShowTyping() {
    if (cbTyping) { cbTyping.classList.remove('hidden'); cbScrollBottom(); }
}
function cbHideTyping() {
    if (cbTyping) cbTyping.classList.add('hidden');
}
function cbScrollBottom() {
    if (cbMsgArea) cbMsgArea.scrollTop = cbMsgArea.scrollHeight;
}

function cbRenderMd(text) {
    var html = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/^[\-\*] (.+)$/gm, '<li class="list-disc ml-4">$1</li>')
        .replace(/^(\d+)\.\s+(.+)$/gm, '<li class="list-none"><span class="font-bold text-emerald-800">$1.</span> $2</li>');

    html = html.replace(/(<li[\s\S]*?<\/li>)/g, function(m){
        return '<ul class="my-1.5 space-y-1">' + m.replace(/\n+/g, '') + '</ul>';
    });

    html = html.replace(/\n{2,}/g, '<br><br>').replace(/\n/g, '<br>');
    html = html.replace(/<br>\s*<(ul|ol)/gi, '<$1').replace(/<\/(ul|ol)>\s*<br>/gi, '</$1>');

    return html;
}

function cbEscape(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function cbAppend(sender, text, isErr) {
    var row = document.createElement('div');
    row.className = 'cb-page-row';

    if (sender === 'user') {
        row.className += ' flex justify-end';
        var bub = document.createElement('div');
        bub.className = 'max-w-[72%] px-4 py-3 rounded-2xl rounded-tr-sm text-sm leading-relaxed break-words text-white font-medium shadow-sm';
        bub.style.background = 'linear-gradient(135deg,#059669,#0f766e)';
        bub.textContent = text;
        row.appendChild(bub);
    } else {
        row.className += ' flex items-start gap-3';

        var av = document.createElement('div');
        av.className = 'w-8 h-8 rounded-xl shrink-0 flex items-center justify-center mt-0.5';
        av.style.background = 'linear-gradient(135deg,#059669,#0f766e)';
        av.innerHTML = '<i class="fa-solid fa-robot text-white text-xs"></i>';

        var wrap = document.createElement('div');
        wrap.className = 'flex-1 min-w-0';

        var lbl = document.createElement('p');
        lbl.className = 'text-[10px] font-bold text-slate-400 mb-1.5 uppercase tracking-wide';
        lbl.textContent = 'AI Assistant';

        var bub = document.createElement('div');
        bub.className = 'max-w-[75%]';
        if (isErr) {
            bub.innerHTML = '<div class="bg-red-50 border border-red-100 text-red-600 rounded-2xl rounded-tl-sm px-4 py-3 text-sm leading-relaxed shadow-sm">'
                + cbEscape(text) + '</div>';
        } else {
            bub.innerHTML = '<div class="cb-bot-text bg-white rounded-2xl rounded-tl-sm px-4 py-3 text-slate-700 text-sm leading-relaxed shadow-sm border border-slate-100">'
                + cbRenderMd(text) + '</div>';
        }

        wrap.appendChild(lbl);
        wrap.appendChild(bub);
        row.appendChild(av);
        row.appendChild(wrap);
    }

    cbMsgArea.appendChild(row);
    cbScrollBottom();
}
</script>

<?php $this->view('templates/footer'); ?>