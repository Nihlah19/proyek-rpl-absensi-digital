<?php
$p        = $data['pengajuan'];
$userRole = SessionHelper::get('role');

$statusConfig = [
    'PENDING'  => ['label' => 'Menunggu',  'icon' => 'fa-clock',        'cls' => 'bg-amber-100 text-amber-700 border-amber-200'],
    'APPROVED' => ['label' => 'Disetujui', 'icon' => 'fa-circle-check', 'cls' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
    'REJECTED' => ['label' => 'Ditolak',   'icon' => 'fa-circle-xmark', 'cls' => 'bg-red-100 text-red-700 border-red-200'],
    'DRAFT'    => ['label' => 'Draft',     'icon' => 'fa-pen',          'cls' => 'bg-slate-100 text-slate-600 border-slate-200'],
];
$sc = $statusConfig[$p->status] ?? $statusConfig['DRAFT'];
$canAct = in_array($userRole, ['wali_kelas', 'admin']) && $p->status === 'PENDING';
?>

<div id="pageContent" class="w-full max-w-3xl mx-auto px-4 sm:px-6 pt-24 pb-16">

    <!-- Breadcrumb / Back -->
    <div class="flex items-center gap-3 mb-8">
        <a href="<?= BASEURL ?>/izin/approval"
           class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors group">
            <span class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:border-slate-300 transition-colors">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </span>
            Kembali ke Daftar Persetujuan
        </a>
    </div>

    <?php Flasher::flash(); ?>

    <!-- ── CARD UTAMA ───────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden mb-6">

        <!-- Header -->
        <div class="px-6 pt-6 pb-5 bg-gradient-to-br from-orange-50 via-white to-amber-50/30 border-b border-slate-100">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-orange-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i class="fa-solid fa-person-running text-orange-500 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($p->nama_siswa) ?></h1>
                        <p class="text-sm text-slate-500 mt-0.5 flex items-center gap-1.5">
                            <i class="fa-solid fa-graduation-cap text-xs"></i>
                            <?= htmlspecialchars($p->nama_kelas) ?>
                            <?php if (!empty($p->nis)): ?>
                                <span class="text-slate-300">·</span>
                                <span class="font-mono text-xs"><?= htmlspecialchars($p->nis) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border <?= $sc['cls'] ?>">
                    <i class="fa-solid <?= $sc['icon'] ?>"></i>
                    <?= $sc['label'] ?>
                </span>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1.5">Tanggal</p>
                <p class="text-base font-bold text-slate-800 font-mono">
                    <?= date('d/m/Y', strtotime($p->tanggal)) ?>
                </p>
            </div>

            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1.5">Jam Tap RFID</p>
                <p class="text-base font-bold text-slate-800 font-mono">
                    <?= date('H:i', strtotime($p->jam_pengajuan)) ?> WIB
                </p>
            </div>

            <div class="sm:col-span-2 bg-orange-50 border border-orange-100 rounded-xl p-4">
                <p class="text-xs text-orange-500 font-semibold uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-comment-dots"></i> Alasan Pengajuan
                </p>
                <p class="text-sm font-semibold text-slate-800 leading-relaxed">
                    <?= $p->alasan ? nl2br(htmlspecialchars($p->alasan)) : '<span class="text-slate-400 italic">Belum diisi</span>' ?>
                </p>
            </div>

            <?php if (!empty($p->jam_pulang_jadwal)): ?>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1.5">Jam Pulang Jadwal</p>
                <p class="text-base font-bold text-slate-800 font-mono">
                    <?= date('H:i', strtotime($p->jam_pulang_jadwal)) ?> WIB
                </p>
            </div>
            <?php endif; ?>

            <?php if (!empty($p->jam_masuk)): ?>
            <div class="bg-slate-50 rounded-xl p-4">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide mb-1.5">Jam Masuk</p>
                <p class="text-base font-bold text-slate-800 font-mono">
                    <?= date('H:i', strtotime($p->jam_masuk)) ?> WIB
                </p>
            </div>
            <?php endif; ?>

            <?php if ($p->status === 'APPROVED' && !empty($p->approved_at)): ?>
            <div class="sm:col-span-2 bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                <p class="text-xs text-emerald-600 font-semibold uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check"></i> Persetujuan
                </p>
                <p class="text-sm text-emerald-700">
                    Disetujui pada <strong><?= date('d/m/Y H:i', strtotime($p->approved_at)) ?></strong>
                </p>
            </div>
            <?php endif; ?>

            <?php if ($p->status === 'REJECTED' && !empty($p->rejected_reason)): ?>
            <div class="sm:col-span-2 bg-red-50 border border-red-100 rounded-xl p-4">
                <p class="text-xs text-red-500 font-semibold uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-xmark"></i> Alasan Penolakan
                </p>
                <p class="text-sm text-red-700 leading-relaxed"><?= nl2br(htmlspecialchars($p->rejected_reason)) ?></p>
                <?php if (!empty($p->rejected_at)): ?>
                    <p class="text-xs text-red-400 mt-2">Ditolak pada <?= date('d/m/Y H:i', strtotime($p->rejected_at)) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ── FORM KEPUTUSAN (hanya jika PENDING dan punya hak) ──────────── -->
    <?php if ($canAct): ?>
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 overflow-hidden" id="formKeputusan">
        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                <i class="fa-solid fa-gavel text-amber-600 text-sm"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-800">Beri Keputusan</h2>
                <p class="text-xs text-slate-400">Pilih aksi lalu simpan perubahan</p>
            </div>
        </div>

        <div class="p-6">
            <form action="<?= BASEURL ?>/pulang_cepat/action" method="post" id="formDecision">
                <input type="hidden" name="id" value="<?= $p->id ?>">
                <input type="hidden" name="status" id="hiddenStatus" value="">

                <!-- Tombol Pilihan Besar -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <button type="button" id="btnSetujui"
                        onclick="setDecision('APPROVED')"
                        class="decision-btn relative flex flex-col items-center justify-center gap-3 p-6 rounded-2xl border-2 border-slate-200 hover:border-emerald-400 hover:bg-emerald-50 transition-all duration-200 cursor-pointer group">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-2xl"></i>
                        </div>
                        <div class="text-center">
                            <p class="font-bold text-slate-700 group-hover:text-emerald-700 transition-colors">Setujui</p>
                            <p class="text-xs text-slate-400 mt-0.5">Izinkan pulang cepat</p>
                        </div>
                        <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center" id="dotSetujui">
                        </div>
                    </button>

                    <button type="button" id="btnTolak"
                        onclick="setDecision('REJECTED')"
                        class="decision-btn relative flex flex-col items-center justify-center gap-3 p-6 rounded-2xl border-2 border-slate-200 hover:border-red-400 hover:bg-red-50 transition-all duration-200 cursor-pointer group">
                        <div class="w-14 h-14 rounded-full bg-red-50 group-hover:bg-red-100 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-circle-xmark text-red-500 text-2xl"></i>
                        </div>
                        <div class="text-center">
                            <p class="font-bold text-slate-700 group-hover:text-red-700 transition-colors">Tolak</p>
                            <p class="text-xs text-slate-400 mt-0.5">Tolak permohonan ini</p>
                        </div>
                        <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center" id="dotTolak">
                        </div>
                    </button>
                </div>

                <!-- Catatan -->
                <div class="mb-6" id="catatanWrap">
                    <label for="catatan" class="block text-sm font-semibold text-slate-700 mb-2">
                        Catatan
                        <span class="text-slate-400 font-normal" id="catatanLabel">(Opsional)</span>
                    </label>
                    <textarea id="catatan" name="catatan" rows="3"
                        placeholder="Tambahkan catatan untuk siswa..."
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700
                               focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-400
                               resize-none transition-all"></textarea>
                </div>

                <!-- Error msg -->
                <div id="errMsg" class="hidden mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-xl border border-red-100 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span id="errText"></span>
                </div>

                <!-- Submit -->
                <div class="flex justify-end gap-3">
                    <a href="<?= BASEURL ?>/izin/approval"
                       class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" id="btnSimpan" disabled
                        class="px-6 py-2.5 bg-slate-300 text-slate-500 text-sm font-bold rounded-xl transition-all duration-200
                               disabled:cursor-not-allowed flex items-center gap-2"
                        id="btnSimpan">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .decision-btn.active-approve {
            border-color: #10b981 !important;
            background-color: #ecfdf5 !important;
        }
        .decision-btn.active-approve .dot-inner { background: #10b981; }

        .decision-btn.active-reject {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
        .decision-btn.active-reject .dot-inner { background: #ef4444; }
    </style>

    <script>
    let currentDecision = null;

    function setDecision(val) {
        currentDecision = val;
        document.getElementById('hiddenStatus').value = val;

        const btnSetujui = document.getElementById('btnSetujui');
        const btnTolak   = document.getElementById('btnTolak');
        const dotS       = document.getElementById('dotSetujui');
        const dotT       = document.getElementById('dotTolak');
        const btnSimpan  = document.getElementById('btnSimpan');
        const label      = document.getElementById('catatanLabel');

        // Reset both
        btnSetujui.classList.remove('active-approve', 'active-reject');
        btnTolak.classList.remove('active-approve', 'active-reject');
        dotS.innerHTML = '';
        dotT.innerHTML = '';

        if (val === 'APPROVED') {
            btnSetujui.classList.add('active-approve');
            dotS.innerHTML = '<div class="dot-inner w-3 h-3 rounded-full bg-emerald-500"></div>';
            label.textContent = '(Opsional)';
            btnSimpan.disabled = false;
            btnSimpan.className = 'px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl transition-all duration-200 flex items-center gap-2';
        } else {
            btnTolak.classList.add('active-reject');
            dotT.innerHTML = '<div class="dot-inner w-3 h-3 rounded-full bg-red-500"></div>';
            label.textContent = '(Wajib diisi)';
            btnSimpan.disabled = false;
            btnSimpan.className = 'px-6 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition-all duration-200 flex items-center gap-2';
        }
    }

    document.getElementById('formDecision').addEventListener('submit', function(e) {
        const errMsg  = document.getElementById('errMsg');
        const errText = document.getElementById('errText');
        errMsg.classList.add('hidden');

        if (!currentDecision) {
            e.preventDefault();
            errText.textContent = 'Harap pilih keputusan terlebih dahulu (Setujui atau Tolak).';
            errMsg.classList.remove('hidden');
            return;
        }

        if (currentDecision === 'REJECTED') {
            const catatan = document.getElementById('catatan').value.trim();
            if (!catatan) {
                e.preventDefault();
                errText.textContent = 'Catatan/alasan wajib diisi jika menolak pengajuan.';
                errMsg.classList.remove('hidden');
                document.getElementById('catatan').focus();
                return;
            }
        }

        // Show loading on submit button
        const btn = document.getElementById('btnSimpan');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
        btn.disabled = true;
    });
    </script>
    <?php endif; ?>

    <!-- Status sudah diproses -->
    <?php if (!$canAct && $p->status !== 'DRAFT'): ?>
    <div class="bg-white rounded-2xl shadow-soft border border-slate-100 p-6 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center
            <?= $p->status === 'APPROVED' ? 'bg-emerald-100' : 'bg-red-100' ?>">
            <i class="fa-solid <?= $p->status === 'APPROVED' ? 'fa-circle-check text-emerald-500' : 'fa-circle-xmark text-red-500' ?>"></i>
        </div>
        <div>
            <p class="font-semibold text-slate-700">
                Pengajuan ini sudah
                <?= $p->status === 'APPROVED' ? '<span class="text-emerald-600">disetujui</span>' : '<span class="text-red-600">ditolak</span>' ?>.
            </p>
            <p class="text-xs text-slate-400 mt-0.5">Tidak ada aksi lebih lanjut yang diperlukan.</p>
        </div>
    </div>
    <?php endif; ?>

</div>
