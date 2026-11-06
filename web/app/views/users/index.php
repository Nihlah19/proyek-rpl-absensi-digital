<!-- Halaman Kelola Pengguna - Admin -->
<link href="<?= BASEURL; ?>/assets/vendor/tom-select/css/tom-select.css" rel="stylesheet">
<script src="<?= BASEURL; ?>/assets/vendor/tom-select/js/tom-select.complete.min.js"></script>
<style>
.ts-control {
    border: 1px solid #e2e8f0 !important;
    border-radius: 0.75rem !important;
    padding: 0.625rem 1rem !important;
    font-size: 0.875rem !important;
    box-shadow: none !important;
    background-color: #fff !important;
    transition: all 0.2s;
}
.ts-control.focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
}
.ts-dropdown .option.selected {
    background-color: #d1fae5;
    color: #065f46;
}
.ts-dropdown .option:hover {
    background-color: #ecfdf5;
    color: #065f46;
}
</style>
<div class="w-full space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-700">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Kelola Pengguna</h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-0.5">Manajemen data Guru dan Siswa</p>
        </div>
    </div>

    <!-- Modern Tabs with Pills -->
    <div class="flex gap-4 mb-6 border-b border-slate-200/60 pb-2">
        <button onclick="showTab('tab-guru')"
                id="btn-guru"
                class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-md flex items-center gap-2 tab-btn active-tab bg-primary text-white shadow-primary/30 shadow-lg">
            <i class="fa-solid fa-chalkboard-user"></i> Data Guru
            <span class="ml-1 bg-white/20 text-white text-xs px-2 py-0.5 rounded-full font-bold backdrop-blur-sm"><?= count($data['guru'] ?? []) ?></span>
        </button>
        <button onclick="showTab('tab-siswa')"
                id="btn-siswa"
                class="relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:bg-slate-100 flex items-center gap-2 text-slate-500 tab-btn bg-white border border-slate-200">
            <i class="fa-solid fa-graduation-cap"></i> Data Siswa
            <span class="ml-1 bg-slate-200 text-slate-600 text-xs px-2 py-0.5 rounded-full font-bold"><?= count($data['siswa'] ?? []) ?></span>
        </button>
    </div>

    <!-- ===== TAB GURU ===== -->
    <div id="tab-guru" class="animate-slide-up">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="relative w-full sm:w-96 group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                    <i class="fa-solid fa-search"></i>
                </div>
                <input type="text" id="searchGuru" placeholder="Cari nama, NIP, atau username..."
                       onkeyup="filterTable('tblGuru', this.value)"
                       class="w-full pl-10 pr-4 py-3 bg-white/80 backdrop-blur-sm border border-slate-200/80 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all shadow-sm group-hover:shadow-md">
            </div>
            <button onclick="document.getElementById('modalTambahGuru').classList.remove('hidden')"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-accent text-white px-6 py-3 rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-primary/40 transform hover:-translate-y-1 transition-all duration-300">
                <i class="fa-solid fa-plus-circle"></i> Tambah Guru
            </button>
        </div>

        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary to-accent"></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm" id="tblGuru">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80">
                        <tr>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">No</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Profil Guru</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Username</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">NIP / Subject</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Barcode ID</th>
                            <th class="text-center px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80">
                        <?php if (empty($data['guru'])): ?>
                        <tr><td colspan="6" class="text-center py-16">
                            <div class="flex flex-col items-center justify-center animate-pulse-slow">
                                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-chalkboard-user text-4xl text-slate-300"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-600">Belum ada data guru</h3>
                                <p class="text-slate-400 text-sm mt-1">Silakan tambahkan data guru baru.</p>
                            </div>
                        </td></tr>
                        <?php else: ?>
                        <?php $no = 1; foreach ($data['guru'] as $g): ?>
                        <tr class="hover:bg-primary/5 transition-colors duration-200 group">
                            <td class="px-6 py-4 text-slate-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-500 dark:text-slate-400 text-sm flex-shrink-0">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 flex items-center gap-2 flex-wrap">
                                            <?= htmlspecialchars($g->name) ?>
                                            <?php if($g->role === 'wali_kelas' || $g->role === 'walikelas'): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">Wali Kelas</span>
                                            <?php endif; ?>
                                            <?php if(!empty($g->is_pembimbing_pkl)): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 flex items-center gap-1">
                                                    <i class="fa-solid fa-user-tie text-[9px]"></i> Pembimbing PKL
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                        <?php if(!empty($g->pembimbing_kelas_names)): ?>
                                            <span class="text-[11px] text-indigo-600 font-semibold flex items-center gap-1 mt-0.5">
                                                <i class="fa-solid fa-briefcase text-[10px]"></i> Bimbingan: <?= htmlspecialchars($g->pembimbing_kelas_names) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-xs text-slate-400 font-medium">Terdaftar: <?= date('d M Y', strtotime($g->created_at)) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-mono text-xs font-semibold border border-slate-200 group-hover:border-primary/30 transition-colors">
                                    <i class="fa-solid fa-at text-slate-400"></i> <?= htmlspecialchars($g->username) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-slate-700 font-medium"><i class="fa-regular fa-id-card text-slate-400 mr-1"></i> <?= htmlspecialchars($g->nip) ?></span>
                                    <?php if($g->subject): ?>
                                    <span class="text-xs text-slate-500"><i class="fa-solid fa-book text-slate-400 mr-1"></i> <?= htmlspecialchars($g->subject) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100">
                                    <i class="fa-solid fa-qrcode"></i> <?= htmlspecialchars($g->barcode_id ?? '-') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= BASEURL ?>/users/barcodeGuru/<?= $g->guru_id ?>" title="Cetak Barcode" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 bg-slate-50 border border-slate-200 hover:bg-accent/10 hover:text-accent hover:border-accent/30 transition-all">
                                        <i class="fa-solid fa-barcode"></i>
                                    </a>
                                    <a href="<?= BASEURL ?>/users/editGuru/<?= $g->guru_id ?>" title="Edit Guru" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 bg-slate-50 border border-slate-200 hover:bg-primary/10 hover:text-primary hover:border-primary/30 transition-all">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="<?= BASEURL ?>/users/hapusGuru/<?= $g->guru_id ?>" method="POST" class="inline-block m-0" onsubmit="return confirm('Hapus guru <?= htmlspecialchars(addslashes($g->name)) ?>?')">
                                        <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate() ?>">
                                        <button type="submit" title="Hapus Guru" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 bg-slate-50 border border-slate-200 hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-all">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
                <!-- Pagination Controls for Guru -->
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

    <!-- ===== TAB SISWA ===== -->
    <div id="tab-siswa" class="hidden animate-slide-up">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="relative w-full sm:w-96 group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-secondary transition-colors">
                    <i class="fa-solid fa-search"></i>
                </div>
                <input type="text" id="searchSiswa" placeholder="Cari nama, NIS, atau kelas..."
                       onkeyup="filterTable('tblSiswa', this.value)"
                       class="w-full pl-10 pr-4 py-3 bg-white/80 backdrop-blur-sm border border-slate-200/80 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm group-hover:shadow-md">
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-wrap justify-end">
                
                <!-- Dropdown Aksi Lainnya -->
                <div class="relative group inline-block">
                    <button class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm">
                        <i class="fa-solid fa-list-check text-slate-400"></i> Kelola Kelas <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
                    </button>
                    <!-- Dropdown Content (Hover or Focus-within) -->
                    <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 flex flex-col overflow-hidden">
                        <button onclick="openModal('modalPindahKelasMasal')" class="text-left px-4 py-3 text-sm font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-people-arrows w-4"></i> Pindah Kelas Masal
                        </button>
                        <button onclick="openModal('modalHapusSekelas')" class="text-left px-4 py-3 text-sm font-medium text-slate-700 hover:bg-red-50 hover:text-red-600 transition-colors flex items-center gap-2 border-t border-slate-50">
                            <i class="fa-solid fa-users-slash w-4"></i> Hapus Sekelas
                        </button>
                    </div>
                </div>

                <button onclick="openModalRFID()"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-emerald-100 transition-all duration-300">
                    <i class="fa-brands fa-nfc-symbol"></i> Registrasi RFID
                </button>
                <button onclick="openModal('modalTambahSiswa')"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-secondary to-accent text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-secondary/30 transform hover:-translate-y-0.5 transition-all duration-300">
                    <i class="fa-solid fa-plus"></i> Tambah Siswa
                </button>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-secondary to-accent"></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm" id="tblSiswa">
                    <thead class="bg-slate-50/80 border-b border-slate-200/80">
                        <tr>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">No</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Profil Siswa</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Username</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">NIS / Kelas</th>
                            <th class="text-left px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Kartu RFID</th>
                            <th class="text-center px-6 py-4 font-bold text-slate-700 uppercase tracking-wider text-xs">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/80">
                        <?php if (empty($data['siswa'])): ?>
                        <tr><td colspan="6" class="text-center py-16">
                            <div class="flex flex-col items-center justify-center animate-pulse-slow">
                                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-user-graduate text-4xl text-slate-300"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-600">Belum ada data siswa</h3>
                                <p class="text-slate-400 text-sm mt-1">Silakan tambahkan data siswa baru.</p>
                            </div>
                        </td></tr>
                        <?php else: ?>
                        <?php $no = 1; foreach ($data['siswa'] as $s): ?>
                        <tr class="hover:bg-secondary/5 transition-colors duration-200 group">
                            <td class="px-6 py-4 text-slate-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-slate-500 dark:text-slate-400 text-sm flex-shrink-0">
                                        <i class="fa-solid fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800 block text-base"><?= htmlspecialchars($s->name) ?></span>
                                        <span class="text-xs text-slate-400 font-medium">Terdaftar: <?= date('d M Y', strtotime($s->created_at ?? date('Y-m-d'))) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-mono text-xs font-semibold border border-slate-200 group-hover:border-secondary/30 transition-colors">
                                    <i class="fa-solid fa-at text-slate-400"></i> <?= htmlspecialchars($s->username) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-slate-700 font-medium"><i class="fa-regular fa-id-badge text-slate-400 mr-1"></i> <?= htmlspecialchars($s->nis) ?></span>
                                    <span class="inline-flex items-center w-fit px-2 py-0.5 rounded text-xs font-bold <?= $s->nama_kelas ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-500' ?>">
                                        <i class="fa-solid fa-chalkboard text-xs mr-1"></i> <?= htmlspecialchars($s->nama_kelas ?? 'Belum diatur') ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($s->rfid_uid): ?>
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-100">
                                    <i class="fa-brands fa-nfc-symbol"></i> <?= htmlspecialchars($s->rfid_uid) ?>
                                </div>
                                <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 text-xs font-medium border border-rose-100/50">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Belum terdaftar
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?= BASEURL ?>/users/editSiswa/<?= $s->siswa_id ?>" title="Edit Siswa" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 bg-slate-50 border border-slate-200 hover:bg-secondary/10 hover:text-secondary hover:border-secondary/30 transition-all">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="<?= BASEURL ?>/users/hapusSiswa/<?= $s->siswa_id ?>" method="POST" class="inline-block m-0" onsubmit="return confirm('Hapus siswa <?= htmlspecialchars(addslashes($s->name)) ?>?')">
                                        <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate() ?>">
                                        <button type="submit" title="Hapus Siswa" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 bg-slate-50 border border-slate-200 hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-all">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
                <!-- Pagination Controls for Siswa -->
                <div id="paginationSiswa" class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 bg-slate-50 border-t border-slate-200 gap-4 hidden">
                    <span id="pageInfoSiswa" class="text-sm font-medium text-slate-500">Menampilkan 0-0 dari 0 data</span>
                    <div class="flex items-center gap-2">
                        <button onclick="prevPageSiswa()" id="btnPrevSiswa" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <div id="pageNumbersSiswa" class="flex gap-1"></div>
                        <button onclick="nextPageSiswa()" id="btnNextSiswa" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL TAMBAH GURU ===== -->
<div id="modalTambahGuru" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeModal('modalTambahGuru')"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden relative transform scale-95 opacity-0 transition-all duration-300 modal-content">
        <div class="bg-gradient-to-r from-primary to-accent p-5 sm:p-6 text-white relative shrink-0">
            <button onclick="closeModal('modalTambahGuru')" class="absolute top-5 right-5 text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h2 class="text-xl sm:text-2xl font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-inner">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                Tambah Guru Baru
            </h2>
            <p class="text-white/80 mt-1 text-xs sm:text-sm">Isi formulir di bawah untuk mendaftarkan guru baru ke sistem.</p>
        </div>
        
        <form action="<?= BASEURL ?>/users/tambahGuru" method="POST" class="p-5 sm:p-8 space-y-4 sm:space-y-5 bg-slate-50 overflow-y-auto flex-1">
            <?= CsrfHelper::input() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </div>
                        <input type="text" name="name" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Nama lengkap beserta gelar">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Username <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-at"></i>
                        </div>
                        <input type="text" name="username" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Untuk login sistem">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Minimal 6 karakter">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">NIP <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-id-card"></i>
                        </div>
                        <input type="text" name="nip" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Nomor Induk Pegawai">
                    </div>
                </div>
            </div>
            
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700 ml-1">Mata Pelajaran <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <input type="text" name="subject" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all shadow-sm" placeholder="Contoh: Matematika, Bahasa Inggris">
                </div>
            </div>

            <div class="space-y-1.5 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mt-2">
                <label class="text-sm font-semibold text-slate-700 flex justify-between items-center">
                    Barcode ID 
                    <span class="bg-blue-50 text-blue-600 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Otomatis Dibuat jika Kosong</span>
                </label>
                <div class="relative mt-2">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <input type="text" name="barcode_id" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all font-mono" placeholder="Kosongkan untuk generate otomatis">
                </div>
                <p class="text-xs text-slate-500 mt-2 flex items-start gap-1.5">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                    ID ini akan digunakan untuk cetak kartu barcode absensi guru.
                </p>
            </div>
            <!-- Penugasan Pembimbing PKL di Modal Tambah Guru -->
            <div class="space-y-3 bg-indigo-50/60 p-4 rounded-2xl border border-indigo-100 mt-2">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Tugaskan sebagai Pembimbing PKL</span>
                        <span class="text-xs text-slate-500">Guru ini akan berwenang mengelola kelompok & lokasi PKL</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_pembimbing_pkl" value="1" class="sr-only peer" onchange="document.getElementById('modalTambahPklKelas').classList.toggle('hidden', !this.checked)">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                <div id="modalTambahPklKelas" class="hidden pt-2 border-t border-indigo-100 space-y-2">
                    <label class="text-xs font-bold uppercase text-slate-600 block">Pilih Kelas PKL Tanggung Jawab:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-36 overflow-y-auto">
                        <?php foreach (($data['allKelasPkl'] ?? []) as $kp): ?>
                        <label class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 text-xs cursor-pointer hover:border-indigo-300">
                            <input type="checkbox" name="pembimbing_kelas_ids[]" value="<?= $kp->id ?>" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="font-medium text-slate-700 truncate"><?= htmlspecialchars($kp->nama_kelas) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 pt-4 mt-2 border-t border-slate-200">
                <button type="button" onclick="closeModal('modalTambahGuru')"
                        class="flex-1 px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 hover:text-slate-800 transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-gradient-to-r from-primary to-accent text-white py-3 rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-primary/40 transform hover:-translate-y-0.5 transition-all">
                    Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL TAMBAH SISWA ===== -->
<div id="modalTambahSiswa" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeModal('modalTambahSiswa')"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden relative transform scale-95 opacity-0 transition-all duration-300 modal-content">
        <div class="bg-gradient-to-r from-secondary to-accent p-5 sm:p-6 text-white relative shrink-0">
            <button onclick="closeModal('modalTambahSiswa')" class="absolute top-5 right-5 text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h2 class="text-xl sm:text-2xl font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-inner">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                Tambah Siswa Baru
            </h2>
            <p class="text-white/80 mt-1 text-xs sm:text-sm">Isi formulir di bawah untuk mendaftarkan siswa baru ke sistem.</p>
        </div>
        
        <form action="<?= BASEURL ?>/users/tambahSiswa" method="POST" class="p-5 sm:p-8 space-y-4 sm:space-y-5 bg-slate-50 overflow-y-auto flex-1">
            <?= CsrfHelper::input() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </div>
                        <input type="text" name="name" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm" placeholder="Nama lengkap siswa">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Username <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-at"></i>
                        </div>
                        <input type="text" name="username" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm" placeholder="Untuk login sistem">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Password <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm" placeholder="Minimal 6 karakter">
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">NIS <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-id-badge"></i>
                        </div>
                        <input type="text" name="nis" required class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm" placeholder="Nomor Induk Siswa">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5">
                    <label class="text-sm font-semibold text-slate-700 ml-1">Kelas Asal</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-chalkboard"></i>
                        </div>
                        <select name="kelas_id" class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm appearance-none">
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($data['kelas'] as $k): ?>
                            <option value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_kelas) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between ml-1">
                        <label class="text-sm font-semibold text-slate-700">RFID UID <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                        <button type="button" id="btnScanIotTambahSiswa" onclick="startIotScanForInput('inputRfidTambahSiswa', 'statusScanTambahSiswa', this)"
                                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-0.5 rounded-lg border border-emerald-200 transition-all flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-wifi animate-pulse"></i>
                            <span>Scan dari Alat IoT</span>
                        </button>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-500">
                            <i class="fa-brands fa-nfc-symbol"></i>
                        </div>
                        <input type="text" id="inputRfidTambahSiswa" name="rfid_uid" class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-secondary/20 focus:border-secondary transition-all shadow-sm font-mono uppercase" placeholder="Misal: A1B2C3D4">
                    </div>
                    <div id="statusScanTambahSiswa" class="hidden text-xs py-1.5 px-2.5 rounded-lg font-medium"></div>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2 flex items-start gap-1.5 bg-blue-50/50 p-2.5 rounded-lg border border-blue-100">
                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                Klik tombol <strong>Scan dari Alat IoT</strong> untuk membaca kartu langsung dari sensor mikrokontroler tanpa kabel / Serial Monitor. Kosongkan jika siswa belum memiliki kartu.
            </p>
            
            <div class="flex gap-4 pt-4 mt-2 border-t border-slate-200">
                <button type="button" onclick="closeModal('modalTambahSiswa')"
                        class="flex-1 px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 hover:text-slate-800 transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-gradient-to-r from-secondary to-accent text-white py-3 rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-secondary/40 transform hover:-translate-y-0.5 transition-all">
                    Simpan Data Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrasi RFID -->
<div id="regModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeModalRFID()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] mx-auto overflow-hidden flex flex-col modal-content transition-all transform opacity-0 scale-95 duration-300">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-emerald-500 to-teal-500 shrink-0">
            <h3 class="font-bold text-white flex items-center gap-2"><i class="fa-brands fa-nfc-symbol"></i> Registrasi Kartu RFID</h3>
            <button onclick="closeModalRFID()" class="text-white/80 hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
            <!-- Pilihan Siswa -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Siswa</label>
                <select id="studentSelect" class="w-full">
                    <option value="">-- Cari & Pilih Siswa --</option>
                    <?php foreach ($data['siswa'] as $s): ?>
                        <?php if (empty($s->rfid_uid)): ?>
                        <option value="<?= $s->siswa_id ?>"><?= htmlspecialchars($s->name) ?> - <?= htmlspecialchars($s->nis) ?> (<?= htmlspecialchars($s->nama_kelas ?? 'Belum diatur') ?>)</option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Scan Area -->
            <div class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/80 p-6 text-center transition-all duration-300" id="scanArea">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-4 border border-slate-100" id="scanIcon">
                    <i class="fa-solid fa-wifi text-2xl text-slate-400 animate-pulse"></i>
                </div>
                <h4 class="font-bold text-slate-700 mb-1" id="scanTitle">Menunggu kartu RFID...</h4>
                <p class="text-xs text-slate-500" id="scanDesc">Silakan tempelkan kartu pada scanner ESP8266</p>
                <input type="hidden" id="detectedUid" value="">
            </div>

            <!-- Pesan Error / Peringatan -->
            <div id="scanAlert" class="hidden rounded-lg p-3 text-sm text-center font-medium"></div>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
            <button onclick="closeModalRFID()" class="px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
            <button id="btnSimpan" onclick="saveRegistration()" disabled class="px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl opacity-50 cursor-not-allowed transition-all">Simpan Registrasi</button>
        </div>
    </div>
</div>

<!-- ===== MODAL PINDAH KELAS MASAL ===== -->
<div id="modalPindahKelasMasal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeModal('modalPindahKelasMasal')"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden relative transform scale-95 opacity-0 transition-all duration-300 modal-content">
        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-5 sm:p-6 text-white relative shrink-0">
            <button onclick="closeModal('modalPindahKelasMasal')" class="absolute top-5 right-5 text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h2 class="text-xl font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-inner">
                    <i class="fa-solid fa-people-arrows"></i>
                </div>
                Pindah Kelas Masal
            </h2>
            <p class="text-white/80 mt-1 text-xs sm:text-sm">Pindahkan seluruh siswa dari satu kelas ke kelas lainnya.</p>
        </div>
        
        <form action="<?= BASEURL ?>/users/pindahKelasMasal" method="POST" class="p-5 sm:p-8 space-y-4 sm:space-y-5 bg-slate-50 overflow-y-auto flex-1" onsubmit="return confirm('Apakah Anda yakin ingin memindahkan seluruh siswa ke kelas baru? Tindakan ini tidak dapat dibatalkan secara massal.')">
            <?= CsrfHelper::input() ?>
            
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700 ml-1">Dari Kelas (Asal) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chalkboard"></i>
                    </div>
                    <select name="kelas_asal_id" required class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-sm appearance-none">
                        <option value="">-- Pilih Kelas Asal --</option>
                        <?php foreach ($data['kelas'] as $k): ?>
                        <option value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_kelas) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="flex justify-center text-emerald-500 py-1">
                <i class="fa-solid fa-arrow-down text-xl"></i>
            </div>
            
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700 ml-1">Ke Kelas (Tujuan) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chalkboard"></i>
                    </div>
                    <select name="kelas_tujuan_id" required class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all shadow-sm appearance-none">
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        <?php foreach ($data['kelas'] as $k): ?>
                        <option value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_kelas) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-4 pt-4 mt-2 border-t border-slate-200">
                <button type="button" onclick="closeModal('modalPindahKelasMasal')"
                        class="flex-1 px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 hover:text-slate-800 transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-500 to-teal-500 text-white py-3 rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-emerald-500/40 transform hover:-translate-y-0.5 transition-all">
                    Pindahkan Siswa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL HAPUS SEKELAS ===== -->
<div id="modalHapusSekelas" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 modal-backdrop" onclick="closeModal('modalHapusSekelas')"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden relative transform scale-95 opacity-0 transition-all duration-300 modal-content">
        <div class="bg-gradient-to-r from-red-500 to-rose-500 p-5 sm:p-6 text-white relative shrink-0">
            <button onclick="closeModal('modalHapusSekelas')" class="absolute top-5 right-5 text-white/70 hover:text-white transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-full backdrop-blur-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h2 class="text-xl font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-inner">
                    <i class="fa-solid fa-users-slash"></i>
                </div>
                Hapus Sekelas
            </h2>
            <p class="text-white/80 mt-1 text-xs sm:text-sm">Hapus semua data siswa dalam satu kelas.</p>
        </div>
        
        <form action="<?= BASEURL ?>/users/hapusSiswaMasal" method="POST" class="p-5 sm:p-8 space-y-4 sm:space-y-5 bg-slate-50 overflow-y-auto flex-1" onsubmit="return confirm('PERINGATAN!\n\nApakah Anda benar-benar yakin ingin menghapus SELURUH SISWA di kelas ini?\nData absensi mereka juga mungkin akan terdampak atau hilang!\n\nTindakan ini TIDAK DAPAT DIBATALKAN!')">
            <?= CsrfHelper::input() ?>
            
            <div class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-200 text-sm mb-4">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i><strong>Peringatan:</strong> Tindakan ini akan menghapus akun dan data RFID seluruh siswa pada kelas yang dipilih.
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-slate-700 ml-1">Pilih Kelas <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-chalkboard"></i>
                    </div>
                    <select name="kelas_id" required class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all shadow-sm appearance-none">
                        <option value="">-- Pilih Kelas yang Ingin Dihapus --</option>
                        <?php foreach ($data['kelas'] as $k): ?>
                        <option value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_kelas) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-4 pt-4 mt-2 border-t border-slate-200">
                <button type="button" onclick="closeModal('modalHapusSekelas')"
                        class="flex-1 px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 hover:text-slate-800 transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-gradient-to-r from-red-500 to-rose-500 text-white py-3 rounded-xl text-sm font-bold hover:shadow-lg hover:shadow-red-500/40 transform hover:-translate-y-0.5 transition-all">
                    Hapus Semua
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }
    .animate-slide-up { animation: slideUp 0.5s ease-out; }
    .animate-pulse-slow { animation: pulse 3s infinite; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .tab-btn { position: relative; overflow: hidden; }
    .active-tab::after {
        content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 40%; height: 3px; background-color: white; border-radius: 4px 4px 0 0;
    }
</style>

<script>
function openModal(id) {
    const modal = document.getElementById(id);
    const backdrop = modal.querySelector('.modal-backdrop');
    const content = modal.querySelector('.modal-content');
    
    modal.classList.remove('hidden');
    // Trigger reflow
    void modal.offsetWidth;
    
    backdrop.classList.add('opacity-100');
    backdrop.classList.remove('opacity-0');
    
    content.classList.add('opacity-100', 'scale-100');
    content.classList.remove('opacity-0', 'scale-95');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    const backdrop = modal.querySelector('.modal-backdrop');
    const content = modal.querySelector('.modal-content');
    
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    
    content.classList.remove('opacity-100', 'scale-100');
    content.classList.add('opacity-0', 'scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300); // Wait for transition
}

// Update existing inline onclicks to use openModal
document.querySelectorAll('[onclick="document.getElementById(\'modalTambahGuru\').classList.remove(\'hidden\')"]')
    .forEach(el => el.setAttribute('onclick', "openModal('modalTambahGuru')"));
document.querySelectorAll('[onclick="document.getElementById(\'modalTambahSiswa\').classList.remove(\'hidden\')"]')
    .forEach(el => el.setAttribute('onclick', "openModal('modalTambahSiswa')"));

function showTab(tabId) {
    const isGuru = tabId === 'tab-guru';
    
    // Hide/Show Content with animation reset
    ['tab-guru','tab-siswa'].forEach(function(id) {
        const el = document.getElementById(id);
        if (id === tabId) {
            el.classList.remove('hidden');
            // Retrigger animation
            el.style.animation = 'none';
            void el.offsetHeight; /* trigger reflow */
            el.style.animation = null; 
        } else {
            el.classList.add('hidden');
        }
    });
    
    // Update Button Styling
    const btnGuru = document.getElementById('btn-guru');
    const btnSiswa = document.getElementById('btn-siswa');
    
    if (isGuru) {
        btnGuru.className = 'relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-md flex items-center gap-2 tab-btn active-tab bg-primary text-white shadow-primary/30 shadow-lg';
        btnGuru.querySelector('span').className = 'ml-1 bg-white/20 text-white text-xs px-2 py-0.5 rounded-full font-bold backdrop-blur-sm';
        
        btnSiswa.className = 'relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:bg-slate-100 flex items-center gap-2 text-slate-500 tab-btn bg-white border border-slate-200';
        btnSiswa.querySelector('span').className = 'ml-1 bg-slate-200 text-slate-600 text-xs px-2 py-0.5 rounded-full font-bold';
    } else {
        btnSiswa.className = 'relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-md flex items-center gap-2 tab-btn active-tab bg-secondary text-white shadow-secondary/30 shadow-lg';
        btnSiswa.querySelector('span').className = 'ml-1 bg-white/20 text-white text-xs px-2 py-0.5 rounded-full font-bold backdrop-blur-sm';
        
        btnGuru.className = 'relative px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 hover:bg-slate-100 flex items-center gap-2 text-slate-500 tab-btn bg-white border border-slate-200';
        btnGuru.querySelector('span').className = 'ml-1 bg-slate-200 text-slate-600 text-xs px-2 py-0.5 rounded-full font-bold';
    }
}

function filterTable(tableId, value) {
    var rows = document.getElementById(tableId).querySelectorAll('tbody tr');
    var q = value.toLowerCase();
    rows.forEach(function(row) {
        if(row.querySelector('td[colspan]')) return; // skip empty state
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// === RFID LOGIC ===
let pollInterval;
let isPolling = false;
let forceMove = false;

let rfidStudentTS = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('studentSelect')) {
        rfidStudentTS = new TomSelect('#studentSelect', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: '-- Cari & Pilih Siswa --',
            maxOptions: 500
        });
    }
});

function openModalRFID() {
    const modal = document.getElementById('regModal');
    const backdrop = modal.querySelector('.modal-backdrop');
    const content = modal.querySelector('.modal-content');
    
    modal.classList.remove('hidden');
    void modal.offsetWidth;
    backdrop.classList.add('opacity-100');
    backdrop.classList.remove('opacity-0');
    content.classList.add('opacity-100', 'scale-100');
    content.classList.remove('opacity-0', 'scale-95');
    
    if (rfidStudentTS) { rfidStudentTS.clear(); } else { document.getElementById('studentSelect').value = ''; }
    resetScanArea();
    startPolling();
}

function closeModalRFID() {
    const modal = document.getElementById('regModal');
    const backdrop = modal.querySelector('.modal-backdrop');
    const content = modal.querySelector('.modal-content');
    
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    content.classList.remove('opacity-100', 'scale-100');
    content.classList.add('opacity-0', 'scale-95');
    
    setTimeout(() => { modal.classList.add('hidden'); }, 300);
    stopPolling();
    if (rfidStudentTS) { rfidStudentTS.clear(); }
}

function resetScanArea() {
    document.getElementById('scanArea').className = 'rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/80 p-6 text-center transition-all duration-300';
    document.getElementById('scanIcon').innerHTML = '<i class="fa-solid fa-wifi text-2xl text-slate-400 animate-pulse"></i>';
    document.getElementById('scanTitle').innerText = 'Menunggu kartu RFID...';
    document.getElementById('scanTitle').className = 'font-bold text-slate-700 mb-1';
    document.getElementById('scanDesc').innerText = 'Silakan tempelkan kartu pada scanner ESP8266';
    document.getElementById('detectedUid').value = '';
    document.getElementById('scanAlert').classList.add('hidden');
    
    let btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    btn.classList.remove('shadow-lg', 'shadow-emerald-500/40', 'transform', 'hover:-translate-y-0.5');
}

function startPolling() {
    if (isPolling) return;
    isPolling = true;
    pollInterval = setInterval(checkLatestScan, 1500);
}

function stopPolling() {
    isPolling = false;
    clearInterval(pollInterval);
}

function checkLatestScan() {
    fetch('<?= BASEURL ?>/rfid/get_latest_scan')
    .then(res => res.json())
    .then(res => {
        if (res.success && res.data.uid) {
            let uid = res.data.uid;
            
            if(document.getElementById('detectedUid').value === uid) return;

            document.getElementById('detectedUid').value = uid;
            document.getElementById('scanArea').className = 'rounded-xl border-2 border-solid border-emerald-200 bg-emerald-50 p-6 text-center transition-all duration-300 shadow-inner';
            document.getElementById('scanIcon').innerHTML = '<i class="fa-regular fa-address-card text-2xl text-emerald-500"></i>';
            document.getElementById('scanTitle').innerText = 'Kartu Terdeteksi!';
            document.getElementById('scanTitle').className = 'font-bold text-emerald-700 mb-1';
            document.getElementById('scanDesc').innerHTML = 'UID: <span class="font-mono font-bold text-slate-800">' + uid + '</span>';
            
            let btn = document.getElementById('btnSimpan');
            let alertBox = document.getElementById('scanAlert');

            if (res.data.used) {
                alertBox.className = 'rounded-lg p-3 text-sm text-center font-medium bg-rose-50 text-rose-700 border border-rose-200 mt-4';
                alertBox.innerText = 'UID ini sudah terdaftar milik: ' + res.data.owner + '. Klik Simpan jika ingin memindahkannya.';
                alertBox.classList.remove('hidden');
                
                forceMove = true;
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('shadow-lg', 'shadow-emerald-500/40', 'transform', 'hover:-translate-y-0.5');
            } else {
                alertBox.classList.add('hidden');
                forceMove = false;
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('shadow-lg', 'shadow-emerald-500/40', 'transform', 'hover:-translate-y-0.5');
            }
        }
    })
    .catch(err => console.error(err));
}

function saveRegistration() {
    let studentId = rfidStudentTS ? rfidStudentTS.getValue() : document.getElementById('studentSelect').value;
    let uid = document.getElementById('detectedUid').value;
    
    if (!studentId) { alert("Silakan pilih siswa terlebih dahulu."); return; }
    if (!uid) { alert("UID belum terdeteksi. Silakan tempelkan kartu."); return; }
    
    if (forceMove) {
        if (!confirm("Kartu ini sudah dipakai siswa lain. Anda yakin ingin memindahkannya?")) return;
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
    .then(res => {
        if (res.success) {
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

// ==========================================
// CLIENT-SIDE PAGINATION FOR SISWA
// ==========================================
let currentSiswaPage = 1;
const siswaRowsPerPage = 10;
let siswaFilteredRows = [];

function initPaginationSiswa() {
    const table = document.getElementById("tblSiswa");
    const tr = table.getElementsByTagName("tr");
    
    siswaFilteredRows = [];
    // Collect all rows in tbody that are not the "No data" message
    for (let i = 1; i < tr.length; i++) {
        if (tr[i].getElementsByTagName("td").length > 1 && tr[i].style.display !== "none") {
            siswaFilteredRows.push(tr[i]);
        }
    }
    
    currentSiswaPage = 1;
    
    const pagContainer = document.getElementById("paginationSiswa");
    if (siswaFilteredRows.length > 0) {
        pagContainer.classList.remove("hidden");
        renderSiswaPage();
    } else {
        pagContainer.classList.add("hidden");
    }
}

function renderSiswaPage() {
    const totalPages = Math.ceil(siswaFilteredRows.length / siswaRowsPerPage);
    if(currentSiswaPage > totalPages) currentSiswaPage = totalPages || 1;
    
    const start = (currentSiswaPage - 1) * siswaRowsPerPage;
    const end = start + siswaRowsPerPage;
    
    // Hide all filtered rows, then only show the ones on the current page
    siswaFilteredRows.forEach((row, index) => {
        if (index >= start && index < end) {
            row.style.display = ""; // default row display
        } else {
            row.style.display = "none";
        }
    });
    
    // Update Info Text
    const info = document.getElementById("pageInfoSiswa");
    if (siswaFilteredRows.length > 0) {
        info.innerText = `Menampilkan ${start + 1}-${Math.min(end, siswaFilteredRows.length)} dari ${siswaFilteredRows.length} data`;
    } else {
        info.innerText = `Menampilkan 0-0 dari 0 data`;
    }
    
    // Update Buttons
    document.getElementById("btnPrevSiswa").disabled = (currentSiswaPage === 1);
    document.getElementById("btnNextSiswa").disabled = (currentSiswaPage === totalPages || totalPages === 0);
    
    // Update Page Numbers
    const pageNumbers = document.getElementById("pageNumbersSiswa");
    pageNumbers.innerHTML = "";
    
    let startPage = Math.max(1, currentSiswaPage - 2);
    let endPage = Math.min(totalPages, currentSiswaPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement("button");
        btn.innerText = i;
        btn.onclick = () => goToPageSiswa(i);
        if (i === currentSiswaPage) {
            btn.className = "px-3 py-1 rounded-lg text-sm font-bold bg-secondary text-white shadow-sm";
        } else {
            btn.className = "px-3 py-1 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-200 transition-colors";
        }
        pageNumbers.appendChild(btn);
    }
}

function prevPageSiswa() {
    if (currentSiswaPage > 1) {
        currentSiswaPage--;
        renderSiswaPage();
    }
}

function nextPageSiswa() {
    const totalPages = Math.ceil(siswaFilteredRows.length / siswaRowsPerPage);
    if (currentSiswaPage < totalPages) {
        currentSiswaPage++;
        renderSiswaPage();
    }
}

function goToPageSiswa(page) {
    currentSiswaPage = page;
    renderSiswaPage();
}

// Modify existing filterTable to re-init pagination for Siswa
const originalFilterTable = window.filterTable;
window.filterTable = function(tableId, query) {
    // Basic search functionality
    let input = document.getElementById(tableId === 'tblGuru' ? 'searchGuru' : 'searchSiswa');
    let filter = input.value.toLowerCase();
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
    
    if(tableId === 'tblSiswa') {
        initPaginationSiswa();
    } else if (tableId === 'tblGuru') {
        initPaginationGuru();
    }
};

// ==========================================
// CLIENT-SIDE PAGINATION FOR GURU
// ==========================================
let currentGuruPage = 1;
const guruRowsPerPage = 10;
let guruFilteredRows = [];

function initPaginationGuru() {
    const table = document.getElementById("tblGuru");
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

// Initialize pagination on DOM ready
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(() => {
        initPaginationSiswa();
        initPaginationGuru();
    }, 100);
});

// ── Penanganan Pindaian RFID Langsung dari IoT untuk Modal Tambah Siswa ──────
let iotInputScanInterval = null;

async function startIotScanForInput(targetInputId, statusBoxId, btnEl) {
    const input = document.getElementById(targetInputId);
    const statusBox = document.getElementById(statusBoxId);
    if (!input || !statusBox) return;

    statusBox.className = "text-xs py-1.5 px-2.5 rounded-lg font-medium bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-between";
    statusBox.innerHTML = `<span><i class="fa-solid fa-spinner fa-spin mr-1"></i> Mode Tambah Kartu Aktif! Tempelkan kartu pada scanner mikrokontroler...</span> <button type="button" onclick="cancelIotScan('${targetInputId}','${statusBoxId}')" class="text-slate-400 hover:text-slate-600 text-xs font-bold ml-2">Batal</button>`;
    statusBox.classList.remove('hidden');

    if (btnEl) btnEl.disabled = true;

    try {
        await fetch('<?= BASEURL ?>/rfid/set_device_mode', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mode: 'regis' })
        });
    } catch (e) {
        console.error(e);
    }

    if (iotInputScanInterval) clearInterval(iotInputScanInterval);

    let baselineUid = input.value.trim();

    iotInputScanInterval = setInterval(async () => {
        try {
            const res = await fetch('<?= BASEURL ?>/rfid/get_latest_scan');
            const json = await res.json();
            if (json.success && json.data && json.data.uid) {
                const uid = json.data.uid;
                if (uid !== baselineUid) {
                    input.value = uid;
                    input.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50');
                    setTimeout(() => input.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50'), 2500);

                    statusBox.className = "text-xs py-1.5 px-2.5 rounded-lg font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1.5";
                    statusBox.innerHTML = `<i class="fa-solid fa-check text-emerald-600"></i> Kartu terdeteksi: <strong class="font-mono text-emerald-700">${uid}</strong>`;

                    clearInterval(iotInputScanInterval);
                    iotInputScanInterval = null;

                    if (btnEl) btnEl.disabled = false;

                    // Kembalikan alat ke mode absensi
                    await fetch('<?= BASEURL ?>/rfid/set_device_mode', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ mode: 'absen' })
                    });
                }
            }
        } catch (e) {}
    }, 1200);
}

async function cancelIotScan(targetInputId, statusBoxId) {
    if (iotInputScanInterval) {
        clearInterval(iotInputScanInterval);
        iotInputScanInterval = null;
    }
    const statusBox = document.getElementById(statusBoxId);
    if (statusBox) statusBox.classList.add('hidden');
    const btn = document.getElementById('btnScanIotTambahSiswa');
    if (btn) btn.disabled = false;
    
    // Kembalikan ke mode absen
    await fetch('<?= BASEURL ?>/rfid/set_device_mode', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode: 'absen' })
    });
}
</script>
