<!-- PAGE CONTENT WRAPPER -->
<div class="px-4 sm:px-6 lg:px-12 py-8 w-full max-w-9xl mx-auto space-y-8" id="pageContent">

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Detail Pengajuan</h1>
    <a href="<?= BASEURL; ?>/izin" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

<?php Flasher::flash(); ?>
<?php $izin = $data['izin']; ?>

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <!-- Header Card -->
        <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-primary/5 to-secondary/5">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-file-lines text-primary"></i>
                        <?= ucfirst($izin->jenis) ?> - <?= $izin->name ?>
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            <?= ucfirst($izin->role) ?>
                            <?php if (isset($izin->nama_kelas)): ?>
                                &middot; <?= htmlspecialchars($izin->nama_kelas) ?>
                            <?php endif; ?>
                        </span>
                    </p>
                </div>
                <?php if ($izin->status == 'pending'): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-200">
                        <i class="fa-solid fa-clock me-1"></i> Menunggu
                    </span>
                <?php elseif ($izin->status == 'approved'): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-check-circle me-1"></i> Disetujui
                    </span>
                <?php elseif ($izin->status == 'rejected'): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                        <i class="fa-solid fa-times-circle me-1"></i> Ditolak
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6 space-y-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="p-4 bg-slate-50 rounded-xl">
                    <dt class="text-slate-400 font-medium mb-1">Tanggal Mulai</dt>
                    <dd class="font-semibold text-slate-800 font-mono"><?= date('d/m/Y', strtotime($izin->tanggal_mulai)) ?></dd>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl">
                    <dt class="text-slate-400 font-medium mb-1">Tanggal Selesai</dt>
                    <dd class="font-semibold text-slate-800 font-mono"><?= date('d/m/Y', strtotime($izin->tanggal_selesai)) ?></dd>
                </div>
                <div class="sm:col-span-2 p-4 bg-slate-50 rounded-xl">
                    <dt class="text-slate-400 font-medium mb-1">Alasan</dt>
                    <dd class="text-slate-700"><?= nl2br(htmlspecialchars($izin->alasan)) ?></dd>
                </div>
                <?php if (!empty($izin->keterangan)): ?>
                <div class="sm:col-span-2 p-4 bg-slate-50 rounded-xl">
                    <dt class="text-slate-400 font-medium mb-1">Keterangan</dt>
                    <dd class="text-slate-600 whitespace-pre-wrap"><?= nl2br(htmlspecialchars($izin->keterangan)) ?></dd>
                </div>
                <?php endif; ?>
                <?php if (!empty($izin->file_bukti)): ?>
                <div class="sm:col-span-2 p-4 bg-slate-50 rounded-xl">
                    <dt class="text-slate-400 font-medium mb-2">Lampiran</dt>
                    <a href="<?= BASEURL; ?>/izin/file/<?= $izin->file_bukti; ?>"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-sky-50 text-sky-700 text-sm font-medium rounded-lg hover:bg-sky-100 transition-colors border border-sky-200">
                        <i class="fa-solid fa-download"></i>
                        Lihat Lampiran
                    </a>
                </div>
                <?php endif; ?>
                <?php if (!empty($izin->catatan_approval)): ?>
                <div class="sm:col-span-2 p-4 bg-sky-50 rounded-xl border border-sky-100">
                    <dt class="text-sky-600 font-medium mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-message"></i> Catatan Penilai
                    </dt>
                    <dd class="text-sky-700 text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($izin->catatan_approval)) ?></dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <!-- Form Tindak Lanjut (wali_kelas/kepsek, pending) -->
    <?php
    $userRole = SessionHelper::get('role');
    if (in_array($userRole, ['wali_kelas', 'walikelas', 'kepsek', 'admin']) && $izin->status == 'pending'):
    ?>
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-amber-50/50">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-amber-500"></i>
                <h2 class="text-lg font-semibold text-slate-800">Tindak Lanjut Pengajuan</h2>
            </div>
        </div>
        <div class="p-6">
            <form action="<?= BASEURL; ?>/izin/action" method="post" class="space-y-5 max-w-xl">
                <input type="hidden" name="id" value="<?= $izin->id; ?>">
                <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate(); ?>">

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700 mb-2">Keputusan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="approved" class="peer hidden" required>
                            <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-slate-300 transition-all">
                                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center peer-checked:bg-emerald-100 transition-colors">
                                    <i class="fa-solid fa-check-circle text-emerald-500"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-700 peer-checked:text-emerald-700">Setujui</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="rejected" class="peer hidden">
                            <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-slate-300 transition-all">
                                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center peer-checked:bg-red-100 transition-colors">
                                    <i class="fa-solid fa-times-circle text-red-500"></i>
                                </div>
                                <span class="text-sm font-medium text-slate-700 peer-checked:text-red-700">Tolak</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="catatan" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <textarea class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-400 resize-y"
                              id="catatan" name="catatan" rows="3"
                              placeholder="Tambahkan pesan untuk pemohon..."></textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-colors shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Simpan Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
</div>
