<?php $this->view('templates/header', $data); ?>
<div id="pageContent" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pilih Guru</h1>
            <p class="text-slate-500 text-sm mt-1">Pilih guru untuk kelola jadwal mengajar</p>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Search and Filter -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full sm:w-96 group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-secondary transition-colors">
                <i class="fa-solid fa-search"></i>
            </div>
            <input type="text" id="searchGuru" placeholder="Cari NIP atau Nama Guru..."
                   onkeyup="filterTable('tblJadwalGuru', this.value)"
                   class="w-full pl-10 pr-4 py-3 bg-white/80 backdrop-blur-sm border border-slate-200/80 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm group-hover:shadow-md">
        </div>
    </div>

    <!-- Guru List -->
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-700 flex items-center gap-2">
                <i class="fa-solid fa-users text-primary"></i>Daftar Guru
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="tblJadwalGuru">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider w-12">No</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider">NIP</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-500 uppercase tracking-wider">Nama Guru</th>
                        <th class="text-center px-5 py-3 w-28"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $no = 1; foreach ($data['guru'] as $g): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4 text-slate-400 text-xs"><?= $no++ ?></td>
                        <td class="px-5 py-4">
                            <span class="font-mono text-slate-600 text-sm"><?= htmlspecialchars($g->nip) ?></span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-medium text-slate-700"><?= htmlspecialchars($g->nama_guru) ?></span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="<?= BASEURL; ?>/jadwal/detail_guru/<?= $g->id; ?>"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary text-white text-xs font-medium rounded-lg hover:bg-primary-dark transition-colors">
                                <i class="fa-solid fa-calendar-days"></i> Kelola Jadwal
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Pagination Controls for Jadwal Guru -->
            <div id="paginationGuru" class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 bg-slate-50 border-t border-slate-200 gap-4 hidden">
                <span id="pageInfoGuru" class="text-sm font-medium text-slate-500">Menampilkan 0-0 dari 0 data</span>
                <div class="flex items-center gap-2">
                    <button onclick="prevPageGuru()" id="btnPrevGuru" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </button>
                    <div id="pageNumbersGuru" class="flex gap-1"></div>
                    <button onclick="nextPageGuru()" id="btnNextGuru" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// CLIENT-SIDE PAGINATION FOR GURU LIST
// ==========================================
let currentGuruPage = 1;
const guruRowsPerPage = 10;
let guruFilteredRows = [];

function initPaginationGuru() {
    const table = document.getElementById("tblJadwalGuru");
    const tr = table.getElementsByTagName("tr");
    
    guruFilteredRows = [];
    for (let i = 1; i < tr.length; i++) {
        if (tr[i].getElementsByTagName("td").length > 1 && tr[i].style.display !== "none") {
            guruFilteredRows.push(tr[i]);
        }
    }
    
    currentGuruPage = 1;
    
    const pagContainer = document.getElementById("paginationGuru");
    if (guruFilteredRows.length > 0) {
        pagContainer.classList.remove("hidden");
        renderGuruPage();
    } else {
        pagContainer.classList.add("hidden");
    }
}

function renderGuruPage() {
    const totalPages = Math.ceil(guruFilteredRows.length / guruRowsPerPage);
    if(currentGuruPage > totalPages) currentGuruPage = totalPages || 1;
    
    const start = (currentGuruPage - 1) * guruRowsPerPage;
    const end = start + guruRowsPerPage;
    
    guruFilteredRows.forEach((row, index) => {
        if (index >= start && index < end) {
            row.style.display = ""; 
        } else {
            row.style.display = "none";
        }
    });
    
    const info = document.getElementById("pageInfoGuru");
    if (guruFilteredRows.length > 0) {
        info.innerText = `Menampilkan ${start + 1}-${Math.min(end, guruFilteredRows.length)} dari ${guruFilteredRows.length} data`;
    } else {
        info.innerText = `Menampilkan 0-0 dari 0 data`;
    }
    
    document.getElementById("btnPrevGuru").disabled = (currentGuruPage === 1);
    document.getElementById("btnNextGuru").disabled = (currentGuruPage === totalPages || totalPages === 0);
    
    const pageNumbers = document.getElementById("pageNumbersGuru");
    pageNumbers.innerHTML = "";
    
    let startPage = Math.max(1, currentGuruPage - 2);
    let endPage = Math.min(totalPages, currentGuruPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement("button");
        btn.innerText = i;
        btn.onclick = () => goToPageGuru(i);
        if (i === currentGuruPage) {
            btn.className = "px-3 py-1 rounded-lg text-sm font-bold bg-primary text-white shadow-sm";
        } else {
            btn.className = "px-3 py-1 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-200 transition-colors";
        }
        pageNumbers.appendChild(btn);
    }
}

function prevPageGuru() {
    if (currentGuruPage > 1) {
        currentGuruPage--;
        renderGuruPage();
    }
}

function nextPageGuru() {
    const totalPages = Math.ceil(guruFilteredRows.length / guruRowsPerPage);
    if (currentGuruPage < totalPages) {
        currentGuruPage++;
        renderGuruPage();
    }
}

function goToPageGuru(page) {
    currentGuruPage = page;
    renderGuruPage();
}

function filterTable(tableId, filterValue) {
    let filter = filterValue.toLowerCase();
    let table = document.getElementById(tableId);
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        if (tr[i].getElementsByTagName("td").length > 1) {
            let textValue = tr[i].textContent || tr[i].innerText;
            if (textValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
    initPaginationGuru();
}

document.addEventListener("DOMContentLoaded", function() {
    setTimeout(() => {
        initPaginationGuru();
    }, 100);
});
</script>
<?php $this->view('templates/footer'); ?>