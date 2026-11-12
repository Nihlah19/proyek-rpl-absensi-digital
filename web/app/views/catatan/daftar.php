<?php
$role            = $data['role'] ?? SessionHelper::get('role');
$isWriter        = (bool)($data['isWriter'] ?? in_array($role, ['wali_kelas', 'walikelas', 'kepsek']));
$isWaliKelas     = in_array($role, ['wali_kelas', 'walikelas']);
$isKepsek        = ($role === 'kepsek');
$catatanList     = $data['catatanList'] ?? [];
$catatanTerkirim = $data['catatanTerkirim'] ?? [];
$editTargetRole  = $isKepsek ? 'guru' : 'siswa';
?>

<div id="pageContent" class="w-full space-y-6">
    <!-- ── HEADER SECTION ── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <a href="<?= BASEURL; ?>/catatan" class="hover:text-emerald-600 font-medium">Catatan</a>
                <span>/</span>
                <span class="text-emerald-700 dark:text-emerald-400 font-semibold">Daftar Catatan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Daftar Semua Catatan
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm">
                <?= $isWriter ? 'Kelola, cari, edit, atau hapus catatan yang telah dibuat serta pantau catatan masuk.' : 'Lihat dan cari seluruh catatan bimbingan yang Anda terima.' ?>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-stretch sm:self-center">
            <?php if ($isWriter): ?>
            <a href="<?= BASEURL; ?>/catatan" class="vh-btn-primary flex-1 sm:flex-initial justify-center text-xs sm:text-sm inline-flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-pen-nib"></i> Tulis Catatan Baru
            </a>
            <?php else: ?>
            <a href="<?= BASEURL; ?>/catatan" class="vh-btn-secondary flex-1 sm:flex-initial justify-center text-xs sm:text-sm inline-flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-arrow-left text-slate-400"></i> Ringkasan Catatan
            </a>
            <?php endif; ?>
            <a href="<?= BASEURL; ?>/dashboard" class="vh-btn-secondary flex-1 sm:flex-initial justify-center text-xs sm:text-sm inline-flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-house text-slate-400"></i> Dashboard
            </a>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <!-- ── RINGKASAN & FILTER BAR ── -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <?php if ($isWriter): ?>
            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" onclick="switchDaftarTab('terkirim')" id="btnDaftarTerkirim"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-2xs transition cursor-pointer inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane text-[11px]"></i>
                    Terkirim (<?= count($catatanTerkirim) ?>)
                </button>
                <?php if ($isWaliKelas): ?>
                <button type="button" onclick="switchDaftarTab('diterima')" id="btnDaftarDiterima"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-envelope-open-text text-[11px]"></i>
                    Dari Kepsek (<?= count($catatanList) ?>)
                </button>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Total Catatan Diterima: <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400"><?= count($catatanList) ?></span>
            </div>
            <?php endif; ?>

            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" id="searchDaftarCatatan" oninput="filterDaftarCatatan()"
                       placeholder="Cari judul, nama, atau isi catatan..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>
    </div>

    <?php if ($isWriter): ?>
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- PANEL TERKIRIM (BISA EDIT & HAPUS)                                     -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div id="daftarPanelTerkirim" class="space-y-4">
        <?php if (empty($catatanTerkirim)): ?>
            <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-10 text-center space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center text-2xl mx-auto">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Belum Ada Catatan Terkirim</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Semua catatan yang Anda buat dan kirimkan akan tampil lengkap di daftar ini untuk diedit atau dikelola kapan saja.
                </p>
                <a href="<?= BASEURL; ?>/catatan" class="vh-btn-primary inline-flex items-center gap-2 text-xs">
                    <i class="fa-solid fa-plus"></i> Buat Catatan Pertama
                </a>
            </div>
        <?php else: ?>
            <!-- Desktop Table -->
            <div class="hidden md:block bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400 bg-slate-50/90 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3.5 text-center w-12">No</th>
                                <th class="px-4 py-3.5">Penerima</th>
                                <th class="px-4 py-3.5">Judul &amp; Isi Catatan</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Waktu Kirim</th>
                                <th class="px-4 py-3.5 text-center whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <?php $no = 1; foreach ($catatanTerkirim as $item):
                                $searchStr = strtolower(($item->target_name ?? '') . ' ' . ($item->title ?? '') . ' ' . ($item->content ?? ''));
                            ?>
                            <tr class="daftar-catatan-row hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition-colors"
                                data-search="<?= htmlspecialchars($searchStr) ?>">
                                <td class="px-4 py-3.5 text-center font-mono text-slate-400"><?= $no++ ?></td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="font-bold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($item->target_name ?? '-') ?></div>
                                    <?php if (!empty($item->nis)): ?>
                                        <div class="text-[11px] font-mono text-slate-400">NIS: <?= htmlspecialchars($item->nis) ?></div>
                                    <?php elseif (!empty($item->target_username)): ?>
                                        <div class="text-[11px] font-mono text-slate-400">@<?= htmlspecialchars($item->target_username) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5 align-top max-w-md">
                                    <div class="font-bold text-emerald-700 dark:text-emerald-400 mb-1"><?= htmlspecialchars($item->title ?? 'Catatan') ?></div>
                                    <div class="text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap break-words"><?= nl2br(htmlspecialchars($item->content ?? '')) ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    <?= !empty($item->created_at) ? date('d M Y, H:i', strtotime($item->created_at)) : '-' ?>
                                </td>
                                <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                onclick='openDaftarEditModal(<?= (int)$item->id ?>, <?= json_encode($editTargetRole) ?>, <?= json_encode($item->target_name ?? "-") ?>, <?= json_encode($item->title ?? "Catatan") ?>, <?= json_encode($item->content ?? "") ?>)'
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition cursor-pointer">
                                            <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                                        </button>
                                        <button type="button"
                                                onclick='openDaftarDeleteModal(<?= (int)$item->id ?>, <?= json_encode($editTargetRole) ?>, <?= json_encode($item->target_name ?? "-") ?>, <?= json_encode($item->title ?? "Catatan") ?>)'
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 border border-slate-200 dark:border-slate-700 hover:border-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can text-[10px]"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile / Android Cards -->
            <div class="block md:hidden space-y-3">
                <?php foreach ($catatanTerkirim as $item):
                    $searchStr = strtolower(($item->target_name ?? '') . ' ' . ($item->title ?? '') . ' ' . ($item->content ?? ''));
                ?>
                <div class="daftar-catatan-row bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs space-y-3"
                     data-search="<?= htmlspecialchars($searchStr) ?>">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white break-words"><?= htmlspecialchars($item->target_name ?? '-') ?></h3>
                            <p class="text-[11px] font-mono text-slate-400">
                                <?= !empty($item->created_at) ? date('d M Y, H:i', strtotime($item->created_at)) : '-' ?>
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                            Terkirim
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60 space-y-1">
                        <div class="text-xs font-bold text-emerald-700 dark:text-emerald-400"><?= htmlspecialchars($item->title ?? 'Catatan') ?></div>
                        <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap break-words"><?= nl2br(htmlspecialchars($item->content ?? '')) ?></div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button"
                                onclick='openDaftarEditModal(<?= (int)$item->id ?>, <?= json_encode($editTargetRole) ?>, <?= json_encode($item->target_name ?? "-") ?>, <?= json_encode($item->title ?? "Catatan") ?>, <?= json_encode($item->content ?? "") ?>)'
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-emerald-400 cursor-pointer">
                            <i class="fa-solid fa-pen-to-square text-[11px]"></i> Edit
                        </button>
                        <button type="button"
                                onclick='openDaftarDeleteModal(<?= (int)$item->id ?>, <?= json_encode($editTargetRole) ?>, <?= json_encode($item->target_name ?? "-") ?>, <?= json_encode($item->title ?? "Catatan") ?>)'
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-rose-50/70 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 cursor-pointer">
                            <i class="fa-solid fa-trash-can text-[11px]"></i> Hapus
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <!-- PANEL DITERIMA (SISWA / GURU / WALI KELAS DARI KEPSEK)                 -->
    <!-- ══════════════════════════════════════════════════════════════════════ -->
    <div id="daftarPanelDiterima" class="<?= ($isWriter && !$isWaliKelas) ? 'hidden' : ($isWriter ? 'hidden' : '') ?> space-y-3">
        <?php if (empty($catatanList)): ?>
            <div class="bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-10 text-center space-y-2">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center text-2xl mx-auto">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Belum Ada Catatan Masuk</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Catatan dari pimpinan atau wali kelas akan tampil di halaman ini.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($catatanList as $item):
                    $searchStr = strtolower(($item->author_name ?? '') . ' ' . ($item->title ?? '') . ' ' . ($item->content ?? ''));
                ?>
                <div class="daftar-catatan-row bg-white dark:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3"
                     data-search="<?= htmlspecialchars($searchStr) ?>">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate"><?= htmlspecialchars($item->author_name ?? 'Pengirim') ?></h3>
                                <p class="text-[11px] font-mono text-slate-400"><?= !empty($item->created_at) ? date('d M Y, H:i', strtotime($item->created_at)) : '-' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700/60 space-y-1">
                        <div class="text-xs font-bold text-emerald-700 dark:text-emerald-400"><?= htmlspecialchars($item->title ?? 'Catatan') ?></div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap break-words"><?= nl2br(htmlspecialchars($item->content ?? '')) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($isWriter): ?>
<!-- Modal Edit Catatan -->
<div id="modalDaftarEdit" class="fixed inset-0 z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-t-2xl sm:rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-lg overflow-hidden">
        <form action="<?= BASEURL; ?>/catatan/update" method="POST">
            <?= CsrfHelper::input() ?>
            <input type="hidden" name="id" id="daftarEditId" value="">
            <input type="hidden" name="target_role" id="daftarEditRole" value="">
            <input type="hidden" name="redirect" value="/catatan/daftar">

            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Catatan</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Penerima: <strong id="daftarEditTarget" class="text-emerald-700 dark:text-emerald-400">-</strong></p>
                </div>
                <button type="button" onclick="closeDaftarEditModal()" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul / Perihal</label>
                    <input type="text" name="title" id="daftarEditTitle" required maxlength="150"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Isi Catatan <span class="text-rose-500">*</span></label>
                    <textarea name="content" id="daftarEditContent" rows="5" required
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
            </div>

            <div class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button" onclick="closeDaftarEditModal()" class="vh-btn-secondary justify-center text-xs">Batal</button>
                <button type="submit" class="vh-btn-primary justify-center text-xs px-5">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Catatan -->
<div id="modalDaftarDelete" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 text-center space-y-4">
        <form action="<?= BASEURL; ?>/catatan/delete" method="POST">
            <?= CsrfHelper::input() ?>
            <input type="hidden" name="id" id="daftarDeleteId" value="">
            <input type="hidden" name="target_role" id="daftarDeleteRole" value="">
            <input type="hidden" name="redirect" value="/catatan/daftar">

            <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mx-auto">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Hapus Catatan Ini?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Catatan <strong id="daftarDeleteSummary" class="text-slate-800 dark:text-slate-200"></strong> akan dihapus secara permanen.
            </p>
            <div class="flex flex-col-reverse sm:flex-row items-center justify-center gap-2 pt-2">
                <button type="button" onclick="closeDaftarDeleteModal()" class="vh-btn-secondary w-full sm:w-auto justify-center text-xs">Batal</button>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition-colors shadow-xs cursor-pointer">
                    <i class="fa-solid fa-trash-can"></i> Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function switchDaftarTab(tab) {
    const pTerkirim = document.getElementById('daftarPanelTerkirim');
    const pDiterima = document.getElementById('daftarPanelDiterima');
    const bTerkirim = document.getElementById('btnDaftarTerkirim');
    const bDiterima = document.getElementById('btnDaftarDiterima');

    if (tab === 'terkirim') {
        if (pTerkirim) pTerkirim.classList.remove('hidden');
        if (pDiterima) pDiterima.classList.add('hidden');
        if (bTerkirim) bTerkirim.className = 'px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-2xs transition cursor-pointer inline-flex items-center gap-1.5';
        if (bDiterima) bDiterima.className = 'px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer inline-flex items-center gap-1.5';
    } else {
        if (pTerkirim) pTerkirim.classList.add('hidden');
        if (pDiterima) pDiterima.classList.remove('hidden');
        if (bDiterima) bDiterima.className = 'px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-2xs transition cursor-pointer inline-flex items-center gap-1.5';
        if (bTerkirim) bTerkirim.className = 'px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition cursor-pointer inline-flex items-center gap-1.5';
    }
}

function filterDaftarCatatan() {
    const q = (document.getElementById('searchDaftarCatatan')?.value || '').toLowerCase().trim();
    document.querySelectorAll('.daftar-catatan-row').forEach(el => {
        const hay = el.getAttribute('data-search') || '';
        el.style.display = (!q || hay.includes(q)) ? '' : 'none';
    });
}

function openDaftarEditModal(id, role, target, title, content) {
    document.getElementById('daftarEditId').value = id;
    document.getElementById('daftarEditRole').value = role;
    document.getElementById('daftarEditTarget').textContent = target || '-';
    document.getElementById('daftarEditTitle').value = title || '';
    document.getElementById('daftarEditContent').value = content || '';
    document.getElementById('modalDaftarEdit').classList.remove('hidden');
}

function closeDaftarEditModal() {
    document.getElementById('modalDaftarEdit').classList.add('hidden');
}

function openDaftarDeleteModal(id, role, target, title) {
    document.getElementById('daftarDeleteId').value = id;
    document.getElementById('daftarDeleteRole').value = role;
    document.getElementById('daftarDeleteSummary').textContent = (title || 'Catatan') + ' (' + (target || '-') + ')';
    document.getElementById('modalDaftarDelete').classList.remove('hidden');
}

function closeDaftarDeleteModal() {
    document.getElementById('modalDaftarDelete').classList.add('hidden');
}
</script>
