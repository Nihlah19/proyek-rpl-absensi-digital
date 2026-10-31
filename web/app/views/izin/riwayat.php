<!-- PAGE CONTENT WRAPPER -->
<div class="px-4 sm:px-6 lg:px-12 py-8 w-full max-w-9xl mx-auto space-y-8" id="pageContent">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pengajuan Izin/Sakit Saya</h1>
            <p class="text-slate-500 text-sm mt-1">Riwayat semua pengajuan yang pernah kamu ajukan</p>
        </div>
        <div class="flex gap-3">
            <a href="<?= BASEURL; ?>/izin/ajukan" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200">
                <i class="fa-solid fa-plus"></i> Buat Pengajuan Baru
            </a>
        </div>
    </div>

<?php Flasher::flash(); ?>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
        <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-history text-primary"></i>Riwayat Pengajuan
        </h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left px-6 py-3.5 font-medium text-slate-500 uppercase tracking-wider">No</th>
                    <th class="text-center px-4 py-3.5 font-medium text-slate-500 uppercase tracking-wider">Tgl Mulai</th>
                    <th class="text-center px-4 py-3.5 font-medium text-slate-500 uppercase tracking-wider">Tgl Selesai</th>
                    <th class="text-center px-4 py-3.5 font-medium text-slate-500 uppercase tracking-wider">Jenis</th>
                    <th class="text-left px-4 py-3.5 font-medium text-slate-500 uppercase tracking-wider">Alasan</th>
                    <th class="text-center px-4 py-3.5 font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-6 py-3.5 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($data['izin'])): ?>
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                <i class="fa-solid fa-receipt text-slate-300 text-2xl"></i>
                            </div>
                            <p class="text-slate-400">Belum ada riwayat pengajuan.</p>
                            <a href="<?= BASEURL; ?>/izin/ajukan" class="mt-3 text-sm text-primary hover:text-primary-dark transition-colors">
                                <i class="fa-solid fa-plus me-1"></i> Buat pengajuan pertama
                            </a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($data['izin'] as $izin): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors izin-row">
                        <td class="px-6 py-4 text-slate-400 text-sm row-number"><?= $i++ ?></td>
                        <td class="px-4 py-4 text-center text-slate-600 font-mono text-sm">
                            <?= date('d/m/Y', strtotime($izin->tanggal_mulai)) ?>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600 font-mono text-sm">
                            <?= date('d/m/Y', strtotime($izin->tanggal_selesai)) ?>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <?= $izin->jenis == 'sakit'
                                ? '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-danger/10 text-danger border border-danger/20"><i class="fa-solid fa-notes-medical"></i> Sakit</span>'
                                : '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-info/10 text-info border border-info/20"><i class="fa-solid fa-passport"></i> Izin</span>' ?>
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
                        <td class="px-6 py-4 text-center">
                            <a href="<?= BASEURL; ?>/izin/detail/<?= $izin->id; ?>"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-xs font-medium rounded-lg hover:bg-primary-dark transition-colors shadow-sm">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = Array.from(document.querySelectorAll('tr.izin-row'));
    if(rows.length === 0) return;

    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');
    const paginationInfo = document.getElementById('paginationInfo');
    
    let currentPage = 1;
    const rowsPerPage = 10;
    
    function updateTable() {
        const totalRows = rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
        if(currentPage > totalPages) currentPage = totalPages;
        
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        rows.forEach(row => row.style.display = 'none');
        
        rows.slice(start, end).forEach((row, idx) => {
            row.style.display = '';
            const tdNo = row.querySelector('.row-number');
            if(tdNo) tdNo.textContent = start + idx + 1;
        });
        
        if (totalRows === 0) {
            if(paginationInfo) paginationInfo.textContent = 'Tidak ada riwayat';
        } else {
            if(paginationInfo) paginationInfo.textContent = `Menampilkan ${start + 1}-${Math.min(end, totalRows)} dari ${totalRows} data`;
        }
        
        if(btnPrev) btnPrev.disabled = currentPage === 1;
        if(btnNext) btnNext.disabled = currentPage === totalPages;
    }
    
    if (btnPrev) btnPrev.addEventListener('click', () => { if(currentPage > 1) { currentPage--; updateTable(); } });
    if (btnNext) btnNext.addEventListener('click', () => { currentPage++; updateTable(); });
    
    updateTable();
});
</script>
