<link href="<?= BASEURL; ?>/assets/vendor/tom-select/css/tom-select.css" rel="stylesheet">
<script src="<?= BASEURL; ?>/assets/vendor/tom-select/js/tom-select.complete.min.js"></script>
<style>
/* Customizing Tom Select to match the Tailwind theme */
.ts-control {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
    box-shadow: none;
    transition: all 0.2s;
}
.ts-control.focus {
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}
.ts-wrapper.single .ts-control {
    background-color: #fff;
}
.dark .ts-control {
    background-color: #0f172a;
    border-color: #334155;
    color: #f1f5f9;
}
.dark .ts-wrapper.single .ts-control {
    background-color: #0f172a;
    color: #f1f5f9;
}
.dark .ts-dropdown {
    background-color: #1e293b;
    border-color: #334155;
    color: #f1f5f9;
}
.dark .ts-dropdown .option {
    color: #cbd5e1;
}
.dark .ts-dropdown .active {
    background-color: #334155;
    color: #fff;
}
</style>

<?php 
$activeDev = $data['activeDevice'] ?? null;
$currentMode = $activeDev->app_mode ?? 'absen';
$isOnline = ($activeDev && $activeDev->iot_status === 'online');
$devIp = $activeDev->ip_address ?? '-';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white">Manajemen Kartu RFID</h2>
        <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Kelola dan registrasikan kartu RFID ke siswa langsung dari web tanpa utak-atik mikrokontroler</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="openModal()" class="vh-btn-primary text-xs sm:text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Registrasi Kartu Baru</span>
        </button>
    </div>
</div>

<!-- Banner Kendali Mode Scanner IoT (Sederhana & Selaras dengan Desain Utama) -->
<div class="mb-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 sm:p-5 shadow-xs">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div id="iotStatusIconBox" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 <?= $isOnline ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400 border border-slate-200 dark:border-slate-600' ?>">
                <i class="fa-solid fa-microchip text-base"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">Alat Scanner RFID IoT</span>
                    <span id="badgeIotOnline" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold <?= $isOnline ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400' ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= $isOnline ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' ?>"></span>
                        <span id="textIotOnline"><?= $isOnline ? 'Online' : 'Offline' ?></span>
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono px-2 py-0.5 bg-slate-100 dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600">
                        IP: <strong id="displayDevIp" class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($devIp) ?></strong>
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Mode Aktif:</span>
                    <span id="badgeCurrentMode" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold transition-all <?= $currentMode === 'regis' ? 'bg-amber-100 dark:bg-amber-500/15 text-amber-800 dark:text-amber-300' : 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-300' ?>">
                        <i class="fa-solid <?= $currentMode === 'regis' ? 'fa-address-card text-amber-600' : 'fa-clipboard-check text-emerald-600' ?>" id="iconCurrentMode"></i>
                        <span id="textCurrentMode"><?= $currentMode === 'regis' ? 'Mode 2: Registrasi Kartu' : 'Mode 1: Scan Absensi' ?></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tombol Pengalih Mode & Pengaturan IoT -->
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="setDeviceMode('absen', true)" id="btnModeAbsenDirect"
                    class="py-2 px-3.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer <?= $currentMode === 'absen' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600' ?>">
                <i class="fa-solid <?= $currentMode === 'absen' ? 'fa-check' : 'fa-clipboard-check' ?>"></i>
                <span>Mode Absen</span>
            </button>
            <button type="button" onclick="setDeviceMode('regis', true)" id="btnModeRegisDirect"
                    class="py-2 px-3.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer <?= $currentMode === 'regis' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600' ?>">
                <i class="fa-solid <?= $currentMode === 'regis' ? 'fa-check' : 'fa-id-card' ?>"></i>
                <span>Mode Tambah Kartu</span>
            </button>
            <a href="<?= BASEURL ?>/admin/iot" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-600" title="Pengaturan Lanjutan IoT">
                <i class="fa-solid fa-sliders text-emerald-600 dark:text-emerald-400"></i>
                <span class="hidden sm:inline">Pengaturan IoT</span>
            </a>
        </div>
    </div>
</div>

<!-- Tabel Data Kartu -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden">
    <!-- Card Header with Search & Per-Page Controls -->
    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/70 dark:bg-slate-800/80">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-id-card text-emerald-600 dark:text-emerald-400"></i>
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">Daftar Kartu RFID Terdaftar</h3>
            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold" id="totalCardsCount"><?= count($data['cards'] ?? []) ?></span>
        </div>
        
        <!-- Controls: Search & Rows per page -->
        <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                <span>Baris:</span>
                <select id="rfidRowsPerPage" onchange="changeRfidRowsPerPage(this.value)" class="text-xs py-1.5 px-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 cursor-pointer">
                    <option value="10" selected>10 baris</option>
                    <option value="20">20 baris</option>
                </select>
            </div>
            <div class="relative">
                <input type="text" id="rfidSearchInput" onkeyup="filterRfidTable()" placeholder="Cari nama / UID / NIS..." class="text-xs py-1.5 pl-8 pr-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-1 focus:ring-emerald-500">
                <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="rfidTable" class="w-full min-w-[640px] text-left border-collapse text-xs sm:text-sm">
            <thead>
                <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    <th class="px-5 py-3.5">No</th>
                    <th class="px-5 py-3.5">UID RFID</th>
                    <th class="px-5 py-3.5">Nama Siswa</th>
                    <th class="px-5 py-3.5">NIS</th>
                    <th class="px-5 py-3.5">Kelas</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs sm:text-sm">
                <?php if (empty($data['cards'])): ?>
                <tr id="rfidInitialEmptyRow">
                    <td colspan="7" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">Belum ada kartu RFID yang terdaftar.</td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($data['cards'] as $card): ?>
                    <tr class="rfid-data-row hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono"><?= $no++ ?></td>
                        <td class="px-5 py-3.5 font-mono font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($card->uid) ?></td>
                        <td class="px-5 py-3.5 font-semibold text-slate-800 dark:text-slate-100"><?= htmlspecialchars($card->student_name) ?></td>
                        <td class="px-5 py-3.5 font-mono text-slate-600 dark:text-slate-400"><?= htmlspecialchars($card->nis) ?></td>
                        <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($card->nama_kelas) ?></td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $card->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40' : 'bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400 border border-red-200/60 dark:border-red-800/40' ?>">
                                <?= ucfirst($card->status) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <button onclick="deleteCard(<?= $card->id ?>)" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600 dark:text-slate-400" id="rfidPaginationContainer">
        <div id="rfidPaginationInfo">
            Menampilkan <span class="font-semibold text-slate-800 dark:text-white" id="rfidShowingStart">1</span> - <span class="font-semibold text-slate-800 dark:text-white" id="rfidShowingEnd">10</span> dari <span class="font-semibold text-slate-800 dark:text-white" id="rfidTotalItems">0</span> data
        </div>
        <div class="flex items-center gap-1" id="rfidPaginationButtons">
            <!-- Dynamic pagination buttons -->
        </div>
    </div>
</div>

<!-- Modal Registrasi -->
<div id="regModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModal()"></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col border border-slate-200 dark:border-slate-700 animate-fade-in">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50/70 dark:bg-slate-800/80">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">Registrasi Kartu Baru</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Alat IoT otomatis dialihkan ke Mode Tambah Kartu</p>
            </div>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Tutup">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        
        <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs">
            <!-- Banner Mode IoT pada Modal -->
            <div id="modalModeBanner" class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/40 rounded-xl p-3 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="font-semibold">Alat IoT Siap Menerima Kartu</span>
                </div>
                <span class="font-mono text-[11px] text-amber-700 dark:text-amber-300 bg-white/70 dark:bg-slate-800/70 px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800">Mode: REGIS</span>
            </div>

            <!-- Pilihan Siswa -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Pilih Siswa</label>
                <select id="studentSelect" class="w-full text-xs">
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($data['students'] as $s): ?>
                        <option value="<?= $s->id ?>"><?= htmlspecialchars($s->name) ?> - <?= htmlspecialchars($s->nis) ?> (<?= htmlspecialchars($s->nama_kelas) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Scan Area -->
            <div class="rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50 p-6 text-center transition-all duration-300" id="scanArea">
                <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto shadow-xs mb-3 border border-slate-200 dark:border-slate-700" id="scanIcon">
                    <i class="fa-solid fa-wifi text-xl text-slate-400 animate-pulse"></i>
                </div>
                <h4 class="font-bold text-slate-800 dark:text-slate-200 mb-1 text-sm" id="scanTitle">Menunggu pindaian kartu RFID...</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400" id="scanDesc">Tempelkan kartu RFID pada scanner mikrokontroler</p>
                <input type="hidden" id="detectedUid" value="">
            </div>

            <!-- Pesan Error / Peringatan -->
            <div id="scanAlert" class="hidden rounded-lg p-3 text-xs text-center font-medium"></div>
        </div>
        
        <div class="px-5 py-3.5 border-t border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/80 flex items-center justify-between">
            <span class="text-[11px] text-slate-400 dark:text-slate-500">Tutup = Kembali ke Mode Absen</span>
            <div class="flex gap-2">
                <button onclick="closeModal()" class="vh-btn-secondary text-xs">Batal</button>
                <button id="btnSimpan" onclick="saveRegistration()" disabled class="vh-btn-primary text-xs opacity-50 cursor-not-allowed">Simpan Registrasi</button>
            </div>
        </div>
    </div>
</div>

<script>
let pollInterval;
let isPolling = false;
let studentSelectTS;
let currentIotMode = '<?= $currentMode ?>';

document.addEventListener('DOMContentLoaded', function() {
    studentSelectTS = new TomSelect('#studentSelect', {
        create: false,
        sortField: { field: "text", direction: "asc" },
        placeholder: "-- Pilih Siswa --"
    });

    // Inisialisasi Pagination Tabel Kartu
    initRfidPagination();

    // Polling status IoT background setiap 7 detik
    setInterval(fetchIotStatus, 7000);
});

// ── Pagination & Filter Tabel Kartu RFID ──────────────────────────────────────
let rfidCurrentPage = 1;
let rfidRowsPerPage = 10;
let rfidFilteredRows = [];

function initRfidPagination() {
    const table = document.getElementById('rfidTable');
    if (!table) return;
    const rows = Array.from(table.querySelectorAll('tbody tr.rfid-data-row'));
    rfidFilteredRows = rows;
    renderRfidPage();
}

function filterRfidTable() {
    const q = (document.getElementById('rfidSearchInput')?.value || '').toLowerCase().trim();
    const table = document.getElementById('rfidTable');
    if (!table) return;
    const rows = Array.from(table.querySelectorAll('tbody tr.rfid-data-row'));
    
    rfidFilteredRows = rows.filter(row => {
        const text = row.innerText.toLowerCase();
        return text.includes(q);
    });
    
    rfidCurrentPage = 1;
    renderRfidPage();
}

function changeRfidRowsPerPage(val) {
    rfidRowsPerPage = parseInt(val, 10) || 10;
    rfidCurrentPage = 1;
    renderRfidPage();
}

function renderRfidPage() {
    const table = document.getElementById('rfidTable');
    if (!table) return;
    const allRows = Array.from(table.querySelectorAll('tbody tr.rfid-data-row'));
    const total = rfidFilteredRows.length;
    const totalPages = Math.ceil(total / rfidRowsPerPage) || 1;
    
    if (rfidCurrentPage > totalPages) rfidCurrentPage = totalPages;
    if (rfidCurrentPage < 1) rfidCurrentPage = 1;
    
    const startIndex = (rfidCurrentPage - 1) * rfidRowsPerPage;
    const endIndex = Math.min(startIndex + rfidRowsPerPage, total);
    
    allRows.forEach(row => row.style.display = 'none');
    
    for (let i = startIndex; i < endIndex; i++) {
        if (rfidFilteredRows[i]) {
            rfidFilteredRows[i].style.display = '';
        }
    }
    
    const startSpan = document.getElementById('rfidShowingStart');
    const endSpan = document.getElementById('rfidShowingEnd');
    const totalSpan = document.getElementById('rfidTotalItems');
    if (startSpan) startSpan.textContent = total === 0 ? 0 : (startIndex + 1);
    if (endSpan) endSpan.textContent = endIndex;
    if (totalSpan) totalSpan.textContent = total;
    
    let emptyRow = document.getElementById('rfidEmptySearchRow');
    if (total === 0 && allRows.length > 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'rfidEmptySearchRow';
            emptyRow.innerHTML = '<td colspan="7" class="px-6 py-8 text-center text-slate-400">Tidak ada kartu yang cocok dengan pencarian.</td>';
            table.querySelector('tbody').appendChild(emptyRow);
        }
        emptyRow.style.display = '';
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
    
    renderRfidPaginationButtons(totalPages);
}

function renderRfidPaginationButtons(totalPages) {
    const container = document.getElementById('rfidPaginationButtons');
    if (!container) return;
    container.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    // Prev button
    const prevBtn = document.createElement('button');
    prevBtn.className = `px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 dark:border-slate-700 ${rfidCurrentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-50 dark:bg-slate-800 text-slate-400' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 cursor-pointer'}`;
    prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
    prevBtn.disabled = (rfidCurrentPage === 1);
    prevBtn.onclick = () => { if (rfidCurrentPage > 1) { rfidCurrentPage--; renderRfidPage(); } };
    container.appendChild(prevBtn);
    
    // Page numbers
    for (let p = 1; p <= totalPages; p++) {
        if (totalPages > 7 && Math.abs(p - rfidCurrentPage) > 2 && p !== 1 && p !== totalPages) {
            if (p === 2 || p === totalPages - 1) {
                const dots = document.createElement('span');
                dots.className = 'px-1 text-slate-400 text-xs';
                dots.textContent = '...';
                container.appendChild(dots);
            }
            continue;
        }
        const pageBtn = document.createElement('button');
        pageBtn.className = `px-2.5 py-1 rounded-md text-xs font-semibold cursor-pointer ${p === rfidCurrentPage ? 'bg-emerald-600 text-white' : 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100'}`;
        pageBtn.textContent = p;
        pageBtn.onclick = () => { rfidCurrentPage = p; renderRfidPage(); };
        container.appendChild(pageBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = `px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 dark:border-slate-700 ${rfidCurrentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-50 dark:bg-slate-800 text-slate-400' : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 cursor-pointer'}`;
    nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
    nextBtn.disabled = (rfidCurrentPage === totalPages);
    nextBtn.onclick = () => { if (rfidCurrentPage < totalPages) { rfidCurrentPage++; renderRfidPage(); } };
    container.appendChild(nextBtn);
}

// ── Pengendali 2 Mode IoT dari Web ───────────────────────────────────────────
async function setDeviceMode(targetMode, showNotice = false) {
    try {
        const res = await fetch('<?= BASEURL ?>/rfid/set_device_mode', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mode=' + encodeURIComponent(targetMode)
        });
        const json = await res.json();
        if (json.status === 'success' || json.success) {
            currentIotMode = targetMode;
            updateModeUI(targetMode);
            if (showNotice && typeof showQuickToast === 'function') {
                showQuickToast(
                    targetMode === 'regis' 
                        ? 'Alat IoT beralih ke Mode Registrasi Kartu!' 
                        : 'Alat IoT beralih ke Mode Scan Absensi Siswa!',
                    targetMode === 'regis' ? 'warning' : 'success'
                );
            }
        }
    } catch (e) {
        console.error("Gagal mengganti mode IoT:", e);
    }
}

function updateModeUI(mode) {
    const badge = document.getElementById('badgeCurrentMode');
    const icon = document.getElementById('iconCurrentMode');
    const text = document.getElementById('textCurrentMode');
    const btn1 = document.getElementById('btnModeAbsenDirect');
    const btn2 = document.getElementById('btnModeRegisDirect');

    currentIotMode = mode;

    if (mode === 'regis') {
        if (badge) {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold transition-all bg-amber-100 dark:bg-amber-500/15 text-amber-800 dark:text-amber-300';
            if (icon) icon.className = 'fa-solid fa-address-card text-amber-600';
            if (text) text.textContent = 'Mode 2: Registrasi Kartu';
        }
        if (btn1) {
            btn1.className = 'py-2 px-3.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600';
            btn1.innerHTML = '<i class="fa-solid fa-clipboard-check"></i><span>Mode Absen</span>';
        }
        if (btn2) {
            btn2.className = 'py-2 px-3.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer bg-amber-500 text-white shadow-xs';
            btn2.innerHTML = '<i class="fa-solid fa-check"></i><span>Mode Tambah Kartu</span>';
        }
    } else {
        if (badge) {
            badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold transition-all bg-emerald-100 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-300';
            if (icon) icon.className = 'fa-solid fa-clipboard-check text-emerald-600';
            if (text) text.textContent = 'Mode 1: Scan Absensi';
        }
        if (btn1) {
            btn1.className = 'py-2 px-3.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer bg-emerald-600 text-white shadow-xs';
            btn1.innerHTML = '<i class="fa-solid fa-check"></i><span>Mode Absen</span>';
        }
        if (btn2) {
            btn2.className = 'py-2 px-3.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600';
            btn2.innerHTML = '<i class="fa-solid fa-id-card"></i><span>Mode Tambah Kartu</span>';
        }
    }

    if (typeof fetchNavbarIotStatus === 'function') {
        fetchNavbarIotStatus();
    }
}

async function fetchIotStatus() {
    try {
        const res = await fetch('<?= BASEURL ?>/rfid/get_device_mode');
        const json = await res.json();
        if (json.success && json.data) {
            const d = json.data;
            const isOnline = d.iot_status === 'online';
            
            const badgeOnline = document.getElementById('badgeIotOnline');
            const textOnline = document.getElementById('textIotOnline');
            const iconBox = document.getElementById('iotStatusIconBox');
            const ipDisplay = document.getElementById('displayDevIp');

            if (badgeOnline && textOnline) {
                badgeOnline.className = `inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold ${isOnline ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'}`;
                badgeOnline.innerHTML = `<span class="w-1.5 h-1.5 rounded-full ${isOnline ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'}"></span><span>${isOnline ? 'Online' : 'Offline'}</span>`;
            }
            if (iconBox) {
                iconBox.className = `w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ${isOnline ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/60' : 'bg-slate-100 text-slate-400 border border-slate-200'}`;
            }
            if (ipDisplay && d.ip_address) {
                ipDisplay.textContent = d.ip_address;
            }

            // Sinkronkan mode jika berubah
            if (d.app_mode && d.app_mode !== currentIotMode && !document.getElementById('regModal').classList.contains('hidden')) {
                currentIotMode = d.app_mode;
                updateModeUI(d.app_mode);
            }
        }
    } catch (e) {
        console.error("Gagal sinkron status IoT:", e);
    }
}

// ── Modal Handling (Otomatis Switch ke Mode Regis saat Buka Modal) ───────────
function openModal() {
    document.getElementById('regModal').classList.remove('hidden');
    if (studentSelectTS) {
        studentSelectTS.clear();
    } else {
        document.getElementById('studentSelect').value = '';
    }
    resetScanArea();

    // Otomatis ubah mode alat ke Mode Registrasi
    setDeviceMode('regis', false);

    startPolling();
}

function closeModal() {
    document.getElementById('regModal').classList.add('hidden');
    stopPolling();

    // Otomatis kembalikan mode alat ke Mode Absensi
    setDeviceMode('absen', false);
}

function resetScanArea() {
    document.getElementById('scanArea').className = 'rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50 p-6 text-center transition-all duration-300';
    document.getElementById('scanIcon').innerHTML = '<i class="fa-solid fa-wifi text-xl text-slate-400 animate-pulse"></i>';
    document.getElementById('scanTitle').innerText = 'Menunggu pindaian kartu RFID...';
    document.getElementById('scanTitle').className = 'font-bold text-slate-800 dark:text-slate-200 mb-1 text-sm';
    document.getElementById('scanDesc').innerText = 'Tempelkan kartu RFID pada scanner mikrokontroler';
    document.getElementById('scanDesc').className = 'text-xs text-slate-500 dark:text-slate-400';
    document.getElementById('detectedUid').value = '';
    document.getElementById('scanAlert').classList.add('hidden');
    
    let btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.className = 'vh-btn-primary text-xs opacity-50 cursor-not-allowed';
}

function startPolling() {
    if (isPolling) return;
    isPolling = true;
    pollInterval = setInterval(checkLatestScan, 1500); // Tiap 1.5 detik
}

function stopPolling() {
    isPolling = false;
    clearInterval(pollInterval);
}

let forceMove = false;

function checkLatestScan() {
    fetch('<?= BASEURL ?>/rfid/get_latest_scan')
    .then(res => res.json())
    .then(res => {
        if (res.success && res.data.uid) {
            let uid = res.data.uid;
            
            if(document.getElementById('detectedUid').value === uid) return;

            document.getElementById('detectedUid').value = uid;
            document.getElementById('scanArea').className = 'rounded-xl border-2 border-solid border-emerald-400 dark:border-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 p-6 text-center transition-all duration-300 shadow-inner';
            document.getElementById('scanIcon').innerHTML = '<i class="fa-regular fa-address-card text-2xl text-emerald-600 dark:text-emerald-400 animate-bounce"></i>';
            document.getElementById('scanTitle').innerText = 'Kartu RFID Berhasil Terdeteksi!';
            document.getElementById('scanTitle').className = 'font-bold text-emerald-800 dark:text-emerald-300 mb-1 text-sm';
            document.getElementById('scanDesc').innerHTML = 'UID Kartu: <span class="font-mono font-bold text-slate-900 dark:text-white bg-white dark:bg-slate-800 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-700">' + uid + '</span>';
            
            let btn = document.getElementById('btnSimpan');
            let alertBox = document.getElementById('scanAlert');

            if (res.data.used) {
                alertBox.className = 'rounded-lg p-3 text-xs text-center font-medium bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40';
                alertBox.innerText = 'UID ini sudah terdaftar milik: ' + (res.data.owner || 'Siswa Lain') + '. Klik Simpan jika ingin memindahkannya.';
                alertBox.classList.remove('hidden');
                
                forceMove = true;
                btn.disabled = false;
                btn.className = 'vh-btn-primary text-xs cursor-pointer';
            } else {
                alertBox.className = 'rounded-lg p-2.5 text-xs text-center font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40';
                alertBox.innerText = 'Kartu baru siap dihubungkan ke siswa pilihan Anda.';
                alertBox.classList.remove('hidden');
                forceMove = false;
                btn.disabled = false;
                btn.className = 'vh-btn-primary text-xs cursor-pointer';
            }
        }
    })
    .catch(err => console.error(err));
}

function saveRegistration() {
    let studentId = document.getElementById('studentSelect').value;
    let uid = document.getElementById('detectedUid').value;
    
    if (!studentId) {
        alert("Silakan pilih siswa terlebih dahulu.");
        return;
    }
    
    if (!uid) {
        alert("UID belum terdeteksi. Silakan tempelkan kartu.");
        return;
    }
    
    if (forceMove) {
        if (!confirm("Kartu ini sudah dipakai siswa lain. Anda yakin ingin memindahkannya?")) {
            return;
        }
    }

    let btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    fetch('<?= BASEURL ?>/rfid/assign', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: studentId, uid: uid, force_move: forceMove })
    })
    .then(res => res.json())
    .then(async res => {
        if (res.success) {
            // Kembalikan alat ke Mode Absensi
            await setDeviceMode('absen', false);
            window.location.reload();
        } else {
            alert("Gagal: " + res.message);
            btn.disabled = false;
            btn.innerText = 'Simpan Registrasi';
        }
    })
    .catch(err => {
        alert("Terjadi kesalahan jaringan.");
        btn.disabled = false;
        btn.innerText = 'Simpan Registrasi';
    });
}

function deleteCard(id) {
    if (confirm("Apakah Anda yakin ingin menghapus kartu ini? Data absensi siswa tidak akan terhapus.")) {
        fetch('<?= BASEURL ?>/rfid/delete/' + id, {
            method: 'DELETE'
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) window.location.reload();
            else alert(res.message);
        });
    }
}
</script>
