<?php $userRole = SessionHelper::get('role'); ?>
<!-- PAGE CONTENT WRAPPER -->
<div class="w-full space-y-6" id="pageContent">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                <?= ($userRole === 'kepsek') ? 'Persetujuan Izin / Sakit Guru' : 'Daftar Persetujuan Izin/Sakit' ?>
            </h1>
            <div class="text-sm text-slate-500 mt-1 flex items-center gap-2">
                <a href="<?= BASEURL; ?>/dashboard" class="hover:text-primary transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-700 font-medium"><?= ($userRole === 'kepsek') ? 'Persetujuan Izin Guru' : 'Persetujuan Izin' ?></span>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" placeholder="<?= ($userRole === 'kepsek') ? 'Cari nama guru...' : 'Cari pemohon...' ?>" class="w-full sm:w-64 pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
            </div>
            <select id="statusFilter" class="w-full sm:w-auto px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
            </select>
        </div>
    </div>


<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden mb-6">
    <?php if ($userRole !== 'kepsek'): ?>
    <!-- Tabs Header -->
    <div class="flex border-b border-slate-200">
        <button onclick="switchTab('tab-izin')" id="btn-tab-izin" class="flex-1 py-4 px-6 text-center font-medium text-sm transition-colors border-b-2 text-primary border-primary bg-primary/5">
            <i class="fa-solid fa-file-medical mr-2"></i>Izin / Sakit
        </button>
        <button onclick="switchTab('tab-pulang')" id="btn-tab-pulang" class="flex-1 py-4 px-6 text-center font-medium text-sm transition-colors border-b-2 text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-50">
            <i class="fa-solid fa-person-running mr-2"></i>Pulang Cepat
        </button>
    </div>
    <?php endif; ?>

    <!-- TAB 1: IZIN/SAKIT -->
    <div id="tab-izin" class="tab-content block">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid <?= ($userRole === 'kepsek') ? 'fa-chalkboard-user' : 'fa-clipboard-list' ?> text-primary"></i>
                <?= ($userRole === 'kepsek') ? 'Data Pengajuan Izin / Sakit Guru' : 'Data Pengajuan Izin/Sakit' ?>
            </h2>
        </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider">No</th>
                    <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider"><?= ($userRole === 'kepsek') ? 'Nama Guru' : 'Pemohon' ?></th>
                    <?php if ($userRole !== 'kepsek'): ?>
                    <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Peran</th>
                    <?php endif; ?>
                    <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Tgl Mulai</th>
                    <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Tgl Selesai</th>
                    <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Jenis</th>
                    <th class="text-left px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Alasan</th>
                    <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-5 py-3 w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($data['izin'])): ?>
                <tr>
                    <td colspan="<?= ($userRole === 'kepsek') ? '8' : '9' ?>" class="px-5 py-8 text-center text-slate-400">
                        <i class="fa-solid fa-inbox text-2xl mb-2 block"></i>
                        Belum ada pengajuan.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($data['izin'] as $izin): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors izin-row" data-status="<?= htmlspecialchars($izin->status) ?>">
                        <td class="px-5 py-4 text-slate-400 row-number"><?= $i++ ?></td>
                        <td class="px-5 py-4">
                            <span class="font-medium text-slate-800">
                                <?= htmlspecialchars(isset($izin->nama_siswa) ? $izin->nama_siswa : $izin->nama_guru) ?>
                            </span>
                            <?php if ($userRole === 'kepsek' && !empty($izin->nip)): ?>
                                <div class="text-xs text-slate-400 font-mono">NIP: <?= htmlspecialchars($izin->nip) ?></div>
                            <?php endif; ?>
                        </td>
                        <?php if ($userRole !== 'kepsek'): ?>
                        <td class="px-4 py-4 text-center text-slate-600">
                            <?php if (isset($izin->nama_siswa)): ?>
                                <span class="text-slate-500">Siswa</span>
                                <span class="text-slate-400 text-xs ml-1">(<?= htmlspecialchars($izin->nama_kelas ?? '-') ?>)</span>
                            <?php else: ?>
                                <span class="text-slate-500">Guru</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td class="px-4 py-4 text-center text-slate-600 font-mono text-sm">
                            <?= date('d/m/Y', strtotime($izin->tanggal_mulai)) ?>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600 font-mono text-sm">
                            <?= date('d/m/Y', strtotime($izin->tanggal_selesai)) ?>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                <?= $izin->jenis == 'sakit'
                                    ? 'bg-danger/10 text-danger border border-danger/20'
                                    : 'bg-info/10 text-info border border-info/20' ?>">
                                <?= strtoupper($izin->jenis) ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-600">
                            <?= htmlspecialchars($izin->alasan) ?>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <?php if ($izin->status == 'pending'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="fa-solid fa-clock text-slate-400"></i> Menunggu
                                </span>
                            <?php elseif ($izin->status == 'approved'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-check-circle text-emerald-500"></i> Disetujui
                                </span>
                            <?php elseif ($izin->status == 'rejected'): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                    <i class="fa-solid fa-times-circle text-red-500"></i> Ditolak
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="<?= BASEURL; ?>/izin/detail/<?= $izin->id; ?>"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white text-xs font-medium rounded-lg hover:bg-primary-dark transition-colors">
                                <i class="fa-solid fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination UI -->
    <?php if (!empty($data['izin'])): ?>
    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between" id="paginationControls">
        <span class="text-sm text-slate-500" id="paginationInfo">Menampilkan data</span>
        <div class="flex gap-2">
            <button id="btnPrevPage" class="px-3 py-1.5 border border-slate-200 text-slate-600 rounded-lg text-sm hover:bg-slate-50 disabled:opacity-50">Sebelumnya</button>
            <button id="btnNextPage" class="px-3 py-1.5 border border-slate-200 text-slate-600 rounded-lg text-sm hover:bg-slate-50 disabled:opacity-50">Selanjutnya</button>
        </div>
    </div>
    <?php endif; ?>
    </div>
    <!-- END TAB 1 -->

    <?php if ($userRole !== 'kepsek'): ?>
    <!-- TAB 2: PULANG CEPAT -->
    <div id="tab-pulang" class="tab-content hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-person-running text-primary"></i>Data Pengajuan Pulang Cepat
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider">Siswa</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Kelas</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Jam Tap</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Alasan</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-center px-5 py-3 w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($data['pulang_cepat'])): ?>
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-2xl mb-2 block"></i>
                            Belum ada pengajuan pulang cepat.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($data['pulang_cepat'] as $p): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors pc-row" data-status="<?= strtolower($p->status) ?>">
                            <td class="px-5 py-4 text-slate-600 font-mono text-sm"><?= date('d/m/Y', strtotime($p->tanggal)) ?></td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-slate-800"><?= htmlspecialchars($p->nama_siswa) ?></span>
                                <div class="text-xs text-slate-400"><?= htmlspecialchars($p->nis) ?></div>
                            </td>
                            <td class="px-4 py-4 text-center text-slate-600"><?= htmlspecialchars($p->nama_kelas) ?></td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-medium border border-blue-100">
                                    <i class="fa-regular fa-clock"></i><?= date('H:i', strtotime($p->jam_pengajuan)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-600 max-w-[200px] truncate" title="<?= htmlspecialchars($p->alasan) ?>">
                                <?= htmlspecialchars($p->alasan ?: '-') ?>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <?php if ($p->status == 'PENDING'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <i class="fa-solid fa-clock text-slate-400"></i> Menunggu
                                    </span>
                                <?php elseif ($p->status == 'APPROVED'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check-circle text-emerald-500"></i> Disetujui
                                    </span>
                                <?php elseif ($p->status == 'REJECTED'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                        <i class="fa-solid fa-times-circle text-red-500"></i> Ditolak
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="<?= BASEURL; ?>/pulang_cepat/detail/<?= $p->id ?>" class="text-primary hover:text-primary-dark font-medium tooltip" title="Lihat Detail">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- END TAB 2 -->
    <?php endif; ?>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentTab = 'tab-izin';
    
    // Switch Tab Logic
    window.switchTab = function(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('block'));
        
        const tabIzin = document.getElementById('tab-izin');
        const tabPulang = document.getElementById('tab-pulang');
        const targetTab = document.getElementById(tabId);
        
        if (tabIzin) tabIzin.classList.add('hidden');
        if (tabPulang) tabPulang.classList.add('hidden');
        if (targetTab) {
            targetTab.classList.remove('hidden');
            targetTab.classList.add('block');
        }
        
        // Reset styles
        const activeClass = ['text-primary', 'border-primary', 'bg-primary/5'];
        const inactiveClass = ['text-slate-500', 'border-transparent', 'hover:text-slate-700', 'hover:bg-slate-50'];
        
        const btnIzin = document.getElementById('btn-tab-izin');
        const btnPulang = document.getElementById('btn-tab-pulang');
        
        if (btnIzin) btnIzin.classList.remove(...activeClass, ...inactiveClass);
        if (btnPulang) btnPulang.classList.remove(...activeClass, ...inactiveClass);
        
        if (tabId === 'tab-izin') {
            if (btnIzin) btnIzin.classList.add(...activeClass);
            if (btnPulang) btnPulang.classList.add(...inactiveClass);
            currentTab = 'tab-izin';
        } else {
            if (btnPulang) btnPulang.classList.add(...activeClass);
            if (btnIzin) btnIzin.classList.add(...inactiveClass);
            currentTab = 'tab-pulang';
        }
        
        // Update table filtering for the active tab
        currentPage = 1;
        updateTable();
    };

    const rowsIzin = Array.from(document.querySelectorAll('tr.izin-row'));
    const rowsPC = Array.from(document.querySelectorAll('tr.pc-row'));

    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');
    const paginationInfo = document.getElementById('paginationInfo');
    
    let currentPage = 1;
    const rowsPerPage = 10;
    
    function updateTable() {
        const term = searchInput ? searchInput.value.toLowerCase() : '';
        const status = statusFilter ? statusFilter.value : '';
        
        const rows = (currentTab === 'tab-izin') ? rowsIzin : rowsPC;
        
        if(rows.length === 0) {
            if(paginationInfo) paginationInfo.textContent = 'Tidak ada data';
            if(btnPrev) btnPrev.disabled = true;
            if(btnNext) btnNext.disabled = true;
            return;
        }

        let filteredRows = rows.filter(row => {
            const text = row.innerText.toLowerCase();
            const rowStatus = row.getAttribute('data-status');
            const matchSearch = text.includes(term);
            const matchStatus = status === '' || rowStatus === status;
            return matchSearch && matchStatus;
        });
        
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
        if(currentPage > totalPages) currentPage = totalPages;
        if(currentPage < 1) currentPage = 1;
        
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        rows.forEach(row => row.style.display = 'none');
        
        filteredRows.slice(start, end).forEach((row, idx) => {
            row.style.display = '';
            const tdNo = row.querySelector('.row-number');
            if(tdNo) tdNo.textContent = start + idx + 1;
        });
        
        if (totalRows === 0) {
            if(paginationInfo) paginationInfo.textContent = 'Tidak ada data ditemukan';
        } else {
            if(paginationInfo) paginationInfo.textContent = `Menampilkan ${start + 1}-${Math.min(end, totalRows)} dari ${totalRows} data`;
        }
        
        if(btnPrev) btnPrev.disabled = currentPage === 1;
        if(btnNext) btnNext.disabled = currentPage === totalPages;
    }
    
    if (searchInput) searchInput.addEventListener('input', () => { currentPage = 1; updateTable(); });
    if (statusFilter) statusFilter.addEventListener('change', () => { currentPage = 1; updateTable(); });
    
    if (btnPrev) btnPrev.addEventListener('click', () => { if(currentPage > 1) { currentPage--; updateTable(); } });
    if (btnNext) btnNext.addEventListener('click', () => { currentPage++; updateTable(); });
    
    updateTable();
});

// Pulang Cepat AJAX actions
async function approvePengajuanPC(id) {
    if (!confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')) return;
    try {
        const res = await fetch(`<?= BASEURL ?>/pulangcepat/approve/${id}`, { method: 'POST' });
        const json = await res.json();
        alert(json.message);
        if (json.ok) location.reload();
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}

async function rejectPengajuanPC(id) {
    const reason = prompt('Masukkan alasan penolakan:');
    if (reason === null) return;
    if (reason.trim() === '') {
        alert('Alasan penolakan wajib diisi!');
        return;
    }
    try {
        const res = await fetch(`<?= BASEURL ?>/pulangcepat/reject/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason: reason })
        });
        const json = await res.json();
        alert(json.message);
        if (json.ok) location.reload();
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}
</script>
