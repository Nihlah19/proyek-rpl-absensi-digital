<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <title><?= $data['judul'] ?? 'Live Monitor Absensi Siswa' ?> - SMK NU Wargabinangun</title>
    <!-- PWA Settings -->
    <link rel="manifest" href="<?= BASEURL; ?>/manifest.json">
    <meta name="theme-color" content="#0F7A4A">
    <link rel="apple-touch-icon" href="<?= BASEURL; ?>/assets/img/apple-touch-icon.png?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASEURL; ?>/assets/img/icon-192.png?v=3">
    <link rel="shortcut icon" href="<?= BASEURL; ?>/assets/img/logo.png?v=3" type="image/png">
    
    <!-- Offline-Ready Local Assets (Precompiled Tailwind CSS + Fonts + Icons) -->
    <link rel="preload" href="<?= BASEURL; ?>/assets/css/tailwind.css?v=3.0" as="style">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/tailwind.css?v=3.0">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fontawesome/css/all.min.css?v=6.5">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fonts/plus-jakarta-sans/plus-jakarta-sans.css?v=1">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased min-h-screen flex flex-col text-slate-800">

    <!-- Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white border border-slate-200 shadow-2xs p-1.5 shrink-0">
                    <img src="<?= BASEURL; ?>/assets/img/logo.png?v=3" alt="Logo SMK NU Wargabinangun" class="h-full w-full object-contain">
                </div>
                <div>
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight leading-tight">SMK NU WARGABINANGUN</h1>
                    <p class="text-[11px] sm:text-xs text-emerald-600 font-semibold tracking-wide uppercase leading-tight">Live Monitor Absensi Siswa</p>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 flex flex-col gap-4">
        
        <!-- Header Section Info & Waktu -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-slate-200 rounded-xl p-4 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 text-sm shrink-0">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">Riwayat Absensi Siswa Hari Ini</h2>
                    <p class="text-xs text-slate-500 leading-tight">Aktivitas pemindaian kartu RFID presensi siswa real-time</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-mono font-semibold text-slate-700 tabular-nums">
                    <i class="fa-regular fa-clock text-slate-400 text-xs"></i>
                    <span id="current-time">--:--:-- WIB</span>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Scan -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden w-full flex flex-col">
            <!-- Toolbar: Search Bar & Count Badge -->
            <div class="p-3.5 border-b border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="relative flex-1 max-w-sm">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="monitor-search" placeholder="Cari nama atau kelas siswa..."
                        class="w-full pl-9 pr-8 py-2 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-2xs">
                    <button type="button" id="monitor-search-clear" onclick="clearSearch()" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 transition" title="Hapus pencarian">
                        <i class="fa-solid fa-circle-xmark text-xs"></i>
                    </button>
                </div>
                <div class="text-xs text-slate-500 flex items-center gap-2 self-start sm:self-auto font-medium">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200/80 font-semibold text-[11px] shadow-2xs">
                        <i class="fa-solid fa-user-check text-[10px]"></i>
                        <span id="total-scanned-count">0 Siswa Hadir</span>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-4 font-semibold w-16 text-center">Profil</th>
                            <th class="py-3 px-4 font-semibold">Nama Siswa & Kelas</th>
                            <th class="py-3 px-4 font-semibold">Waktu Presensi</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody id="riwayat-grid" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400 text-xs font-medium">
                                <i class="fa-solid fa-spinner fa-spin mr-2 text-slate-300"></i> Memuat data absensi...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div id="pagination-wrapper" class="p-3 bg-white border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="text-slate-500 font-medium" id="pagination-info">
                    Menampilkan 0 dari 0 data
                </div>
                <div class="flex items-center gap-1.5" id="pagination-controls">
                    <button type="button" id="btn-prev-page" onclick="changePage(-1)" disabled
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-2xs">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                        <span>Sebelumnya</span>
                    </button>
                    <span class="px-2.5 py-1 text-slate-700 font-semibold" id="page-indicator">
                        Halaman 1 / 1
                    </span>
                    <button type="button" id="btn-next-page" onclick="changePage(1)" disabled
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-2xs">
                        <span>Berikutnya</span>
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-5 text-center border-t border-slate-200 bg-white">
        <p class="text-xs font-medium text-slate-400 select-none">
            &copy; 2026 Nihlah.Auliya. All Rights Reserved
        </p>
    </footer>

    <script>
        const RIWAYAT_BASEURL = '<?= BASEURL; ?>';

        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Jakarta'
            });
            const clockEl = document.getElementById('current-time');
            if (clockEl) {
                clockEl.innerText = timeString + ' WIB';
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        let allScannedData = [];
        let searchQuery = '';
        let currentPage = 1;
        const itemsPerPage = 8;

        function fetchRiwayat() {
            fetch(RIWAYAT_BASEURL + '/riwayat/get_latest_absensi')
                .then(res => res.json())
                .then(res => {
                    if (res && res.success && Array.isArray(res.data)) {
                        allScannedData = res.data;
                        renderCurrentPage();
                    }
                })
                .catch(err => console.error('[Live Monitor] Error fetching absensi:', err));
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function clearSearch() {
            const input = document.getElementById('monitor-search');
            if (input) {
                input.value = '';
                searchQuery = '';
                const clearBtn = document.getElementById('monitor-search-clear');
                if (clearBtn) clearBtn.classList.add('hidden');
                currentPage = 1;
                renderCurrentPage();
            }
        }

        function changePage(delta) {
            currentPage += delta;
            renderCurrentPage();
        }

        function renderCurrentPage() {
            const tbody = document.getElementById('riwayat-grid');
            const totalScannedCountEl = document.getElementById('total-scanned-count');
            const paginationInfoEl = document.getElementById('pagination-info');
            const pageIndicatorEl = document.getElementById('page-indicator');
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');
            
            if (!tbody) return;

            // Update badge total (hitung yang hadir / terlambat / pulang, tidak termasuk alfa)
            if (totalScannedCountEl) {
                const hadirCount = allScannedData.filter(item => {
                    const st = (item.status || '').toLowerCase();
                    return st === 'hadir' || st === 'terlambat' || st === 'pulang_cepat' || Boolean(item.jam_pulang);
                }).length;
                totalScannedCountEl.textContent = `${hadirCount} Siswa Hadir`;
            }

            // Filter data by search query
            const filtered = allScannedData.filter(item => {
                if (!searchQuery) return true;
                const nameMatch = (item.name || '').toLowerCase().includes(searchQuery);
                const kelasMatch = (item.nama_kelas || '').toLowerCase().includes(searchQuery);
                return nameMatch || kelasMatch;
            });

            // Calculate pagination
            const totalPages = Math.max(1, Math.ceil(filtered.length / itemsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filtered.length);
            const pageItems = filtered.slice(startIndex, endIndex);

            // Update Pagination UI
            if (paginationInfoEl) {
                if (filtered.length === 0) {
                    paginationInfoEl.textContent = allScannedData.length === 0 
                        ? 'Belum ada data presensi hari ini'
                        : `Tidak ditemukan hasil untuk "${searchQuery}"`;
                } else {
                    paginationInfoEl.textContent = `Menampilkan ${startIndex + 1} - ${endIndex} dari ${filtered.length} siswa ${searchQuery ? `(difilter)` : ''}`;
                }
            }

            if (pageIndicatorEl) {
                pageIndicatorEl.textContent = `Halaman ${currentPage} / ${totalPages}`;
            }

            if (btnPrev) {
                btnPrev.disabled = (currentPage <= 1);
            }
            if (btnNext) {
                btnNext.disabled = (currentPage >= totalPages);
            }

            // Empty state
            if (filtered.length === 0) {
                if (allScannedData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-14 text-center">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-400 shadow-2xs">
                                    <i class="fa-regular fa-clipboard text-xl"></i>
                                </div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-700">Belum Ada Presensi Siswa Hari Ini</h3>
                                <p class="text-[11px] sm:text-xs text-slate-400 mt-1 max-w-sm mx-auto">Riwayat presensi akan terisi otomatis saat siswa melakukan scan kartu RFID pada gerbang masuk.</p>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-12 text-center">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                                </div>
                                <h3 class="text-xs font-semibold text-slate-700">Pencarian Tidak Ditemukan</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada siswa hadir dengan nama/kelas "${escapeHtml(searchQuery)}"</p>
                            </td>
                        </tr>
                    `;
                }
                return;
            }

            tbody.innerHTML = '';
            pageItems.forEach(item => {
                const isPulang = Boolean(item.jam_pulang && item.jam_pulang.trim() !== '');
                const rawWaktu = isPulang ? item.jam_pulang : item.jam_datang;
                const status = (item.status || '').toLowerCase();
                
                let rowHover = 'hover:bg-slate-50/60';
                let avatarBg = 'text-slate-500 bg-slate-100 border-slate-200';
                let statusBadge = '';
                let waktuHtml = '';

                // Waktu display
                if (rawWaktu && rawWaktu !== 'null') {
                    const iconClock = isPulang ? 'fa-arrow-right-from-bracket text-emerald-500' : 'fa-clock text-blue-500';
                    waktuHtml = `
                        <span class="inline-flex items-center gap-1.5 font-mono text-xs font-semibold text-slate-700 tabular-nums bg-slate-50 px-2.5 py-1 rounded-md border border-slate-200">
                            <i class="fa-regular ${iconClock} text-[11px]"></i>
                            ${escapeHtml(rawWaktu)} WIB
                        </span>
                    `;
                } else {
                    waktuHtml = `
                        <span class="inline-flex items-center gap-1 text-xs text-slate-400 italic">
                            <i class="fa-solid fa-minus text-[10px]"></i> Belum scan
                        </span>
                    `;
                }

                // Status Badge & Row Theme (Keterangan Absen hanya Hadir, Izin, Sakit, Alfa; Tepat Waktu / Terlambat sebagai Status Waktu)
                if (isPulang) {
                    rowHover = 'hover:bg-blue-50/40';
                    avatarBg = 'text-blue-600 bg-blue-50 border-blue-200';
                    const waktuStatusTag = status === 'terlambat'
                        ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">Terlambat</span>`
                        : (status === 'hadir' ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">Tepat Waktu</span>` : '');
                    statusBadge = `<div class="inline-flex flex-wrap items-center gap-1"><span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80"><i class="fa-solid fa-arrow-right-from-bracket text-[10px]"></i> Hadir (Pulang)</span>${waktuStatusTag}</div>`;
                } else if (status === 'hadir') {
                    rowHover = 'hover:bg-emerald-50/40';
                    avatarBg = 'text-emerald-600 bg-emerald-50 border-emerald-200';
                    statusBadge = `<div class="inline-flex flex-wrap items-center gap-1"><span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80"><i class="fa-solid fa-check text-[10px]"></i> Hadir</span><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">Tepat Waktu</span></div>`;
                } else if (status === 'terlambat') {
                    rowHover = 'hover:bg-amber-50/40';
                    avatarBg = 'text-amber-600 bg-amber-50 border-amber-200';
                    statusBadge = `<div class="inline-flex flex-wrap items-center gap-1"><span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80"><i class="fa-solid fa-check text-[10px]"></i> Hadir</span><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80"><i class="fa-solid fa-clock text-[9px]"></i> Terlambat</span></div>`;
                } else if (status === 'izin') {
                    rowHover = 'hover:bg-purple-50/40';
                    avatarBg = 'text-purple-600 bg-purple-50 border-purple-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200/80"><i class="fa-solid fa-file-lines text-[10px]"></i> Izin</span>`;
                } else if (status === 'sakit') {
                    rowHover = 'hover:bg-amber-50/40';
                    avatarBg = 'text-amber-600 bg-amber-50 border-amber-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/80"><i class="fa-solid fa-notes-medical text-[10px]"></i> Sakit</span>`;
                } else if (status === 'alfa') {
                    rowHover = 'hover:bg-rose-50/40';
                    avatarBg = 'text-rose-600 bg-rose-50 border-rose-200';
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200/80"><i class="fa-solid fa-circle-xmark text-[10px]"></i> Alfa</span>`;
                } else {
                    statusBadge = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">${escapeHtml(status ? status.toUpperCase() : '-')}</span>`;
                }

                const avatarSrc = item.avatar ? (item.avatar.startsWith('data:image/') ? item.avatar : `${RIWAYAT_BASEURL}/uploads/avatars/${escapeHtml(item.avatar)}`) : '';
                const profileAvatar = avatarSrc 
                    ? `<img src="${avatarSrc}" alt="${escapeHtml(item.name)}" class="w-full h-full object-cover">`
                    : `<i class="fa-solid fa-user text-xs"></i>`;

                tbody.innerHTML += `
                    <tr class="transition-colors ${rowHover}">
                        <td class="py-3 px-4 text-center align-middle">
                            <div class="w-9 h-9 mx-auto rounded-full flex items-center justify-center shrink-0 overflow-hidden border ${avatarBg}">
                                ${profileAvatar}
                            </div>
                        </td>
                        <td class="py-3 px-4 align-middle">
                            <div class="font-semibold text-slate-800 text-sm leading-snug">${escapeHtml(item.name || '-')}</div>
                            <div class="text-xs text-slate-500 leading-snug flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 font-medium text-[11px] text-slate-600">${escapeHtml(item.nama_kelas || 'Kelas -')}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 align-middle">
                            ${waktuHtml}
                        </td>
                        <td class="py-3 px-4 align-middle">
                            ${statusBadge}
                        </td>
                    </tr>
                `;
            });
        }

        // Event listener search input
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('monitor-search');
            const clearBtn = document.getElementById('monitor-search-clear');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    searchQuery = e.target.value.trim().toLowerCase();
                    if (clearBtn) {
                        if (searchQuery.length > 0) {
                            clearBtn.classList.remove('hidden');
                        } else {
                            clearBtn.classList.add('hidden');
                        }
                    }
                    currentPage = 1;
                    renderCurrentPage();
                });
            }
        });

        // Panggil pertama kali
        fetchRiwayat();

        // Polling live setiap 3 detik
        setInterval(fetchRiwayat, 3000);
    </script>
</body>
</html>
