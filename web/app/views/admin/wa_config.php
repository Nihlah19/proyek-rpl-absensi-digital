<div class="w-full space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400 mb-1 font-mono uppercase tracking-wider">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Sistem Notifikasi</span>
                <span class="text-slate-400 dark:text-slate-500">/</span>
                <span class="text-slate-500 dark:text-slate-400 font-normal">WhatsApp Fonnte</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Konfigurasi WhatsApp Gateway
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Kelola token Fonnte (9 Wali Kelas + 1 Cadangan Backup) dan format pesan notifikasi presensi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <!-- Quick Toggle Notif Masuk -->
            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 cursor-pointer shadow-xs" title="Aktif/Nonaktifkan Notifikasi Masuk">
                <input type="checkbox" id="switchToggleMasuk" class="w-3.5 h-3.5 text-emerald-600 rounded" <?= !empty($data['isEnabledMasuk']) ? 'checked' : '' ?> onchange="quickToggleNotif('masuk', this)">
                <span class="text-slate-700 dark:text-slate-200">Notif Masuk: <strong id="badgeMasukStatus" class="<?= !empty($data['isEnabledMasuk']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"><?= !empty($data['isEnabledMasuk']) ? 'AKTIF' : 'NONAKTIF' ?></strong></span>
            </label>

            <!-- Quick Toggle Notif Pulang -->
            <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 cursor-pointer shadow-xs" title="Aktif/Nonaktifkan Notifikasi Pulang">
                <input type="checkbox" id="switchTogglePulang" class="w-3.5 h-3.5 text-emerald-600 rounded" <?= !empty($data['isEnabledPulang']) ? 'checked' : '' ?> onchange="quickToggleNotif('pulang', this)">
                <span class="text-slate-700 dark:text-slate-200">Notif Pulang: <strong id="badgePulangStatus" class="<?= !empty($data['isEnabledPulang']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>"><?= !empty($data['isEnabledPulang']) ? 'AKTIF' : 'NONAKTIF' ?></strong></span>
            </label>

            <a href="https://fonnte.com" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 px-3 py-1.5 rounded-lg transition shadow-xs">
                <span>Dashboard Fonnte</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
            </a>
        </div>
    </div>

    <!-- Alert Box (AJAX Notification) -->
    <div id="ajaxAlertBox" class="hidden p-4 rounded-xl text-xs font-semibold flex items-center justify-between border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white shadow-lg">
        <div class="flex items-center gap-2.5">
            <i id="ajaxAlertIcon" class="fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400"></i>
            <span id="ajaxAlertText"></span>
        </div>
        <button type="button" onclick="document.getElementById('ajaxAlertBox').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 1: PENGATURAN 10 TOKEN FONNTE (CADANGAN & WALI KELAS)               -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Token WhatsApp Fonnte</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Atur token API Fonnte untuk masing-masing wali kelas dan token cadangan failover.</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-mono font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span><?= $data['totalFilled'] ?? 0 ?> / 10 Terisi</span>
            </span>
        </div>

        <form action="<?= BASEURL ?>/wa/save_all_tokens" method="POST" class="p-5 space-y-5">

            <!-- SLOT CADANGAN (SLOT 10) -->
            <?php if (!empty($data['cadanganSlot'])): $cs = $data['cadanganSlot']; ?>
            <div class="p-4 sm:p-5 rounded-xl bg-amber-50/50 dark:bg-amber-500/5 border border-amber-200 dark:border-amber-500/20 space-y-3" id="slotRow_<?= $cs->id ?>">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-amber-200/60 dark:border-amber-500/20 pb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-500 text-white text-xs font-mono font-bold flex items-center justify-center flex-shrink-0">
                            #10
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Token Cadangan (Backup Failover)</h3>
                                <span class="text-[10px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 px-2 py-0.5 rounded uppercase">
                                    Cadangan Otomatis
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Digunakan secara otomatis jika salah satu token wali kelas limit / habis kuota.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2" id="quotaStatusCol_<?= $cs->id ?>">
                        <?php if (!empty($cs->is_exhausted)): ?>
                            <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20">
                                Limit
                            </span>
                            <button type="button" onclick="handleResetQuota(<?= $cs->id ?>, this)" class="px-2.5 py-0.5 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded border border-slate-300 dark:border-slate-600 shadow-xs cursor-pointer">
                                Reset
                            </button>
                        <?php elseif (!empty($cs->token)): ?>
                            <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Siap Oper
                            </span>
                        <?php else: ?>
                            <span class="px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                Belum Diisi
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-5 space-y-1">
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300">Token Fonnte Cadangan:</label>
                        <div class="relative">
                            <input type="password" name="tokens[<?= $cs->id ?>]" id="token_slot_<?= $cs->id ?>"
                                   value="<?= htmlspecialchars($cs->token ?? '') ?>"
                                   class="w-full pl-3 pr-9 py-2 text-xs font-mono bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 text-slate-900 dark:text-white rounded-lg outline-none"
                                   placeholder="Tempel token API Fonnte cadangan...">
                            <button type="button" onclick="toggleTokenVisibility('token_slot_<?= $cs->id ?>', this)"
                                    class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-3 space-y-1">
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300">No. WA Pengirim:</label>
                        <input type="text" name="device_numbers[<?= $cs->id ?>]" id="device_slot_<?= $cs->id ?>"
                               value="<?= htmlspecialchars($cs->device_number ?? '') ?>"
                               class="w-full px-3 py-2 text-xs font-mono bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 text-slate-900 dark:text-white rounded-lg outline-none"
                               placeholder="081234567890">
                    </div>

                    <div class="md:col-span-4 flex flex-wrap items-center gap-1.5 pt-1 md:pt-0">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 px-2.5 py-2 rounded-lg border border-slate-300 dark:border-slate-600 shadow-xs">
                            <input type="checkbox" name="is_actives[<?= $cs->id ?>]" value="1" <?= !empty($cs->is_active) ? 'checked' : '' ?> class="w-3.5 h-3.5 text-emerald-600 rounded">
                            <span>Aktif</span>
                        </label>
                        <button type="button" onclick="checkSpecificSlot(<?= $cs->id ?>, this)"
                                class="px-2.5 py-2 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-semibold transition flex items-center gap-1 shadow-xs cursor-pointer"
                                title="Tes koneksi token cadangan">
                            <i class="fa-solid fa-wifi text-xs text-emerald-600 dark:text-emerald-400"></i>
                            <span>Tes</span>
                        </button>
                        <button type="button" onclick="openQrModal(<?= $cs->id ?>)"
                                class="px-2.5 py-2 bg-white dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-semibold transition flex items-center gap-1 shadow-xs cursor-pointer"
                                title="Scan QR Code WhatsApp">
                            <i class="fa-solid fa-qrcode text-xs"></i>
                            <span>QR</span>
                        </button>
                        <button type="button" onclick="saveSingleSlot(<?= $cs->id ?>, this)"
                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition flex items-center gap-1 shadow-xs cursor-pointer"
                                title="Simpan slot cadangan ini">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 9 SLOT WALI KELAS (RESPONSIVE TABLE) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs flex items-center justify-center font-bold">9</span>
                        <span>Daftar 9 Token Wali Per Kelas</span>
                    </h3>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    <table class="w-full text-left text-xs divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">Slot</th>
                                <th class="px-4 py-3 w-44">Kelas & Wali</th>
                                <th class="px-4 py-3">Token API Fonnte</th>
                                <th class="px-4 py-3 w-36">No. Pengirim</th>
                                <th class="px-4 py-3 text-center w-24">Status</th>
                                <th class="px-4 py-3 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <?php if (!empty($data['classSlots'])): ?>
                                <?php foreach ($data['classSlots'] as $slot): ?>
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors" id="slotRow_<?= $slot->id ?>">
                                        <td class="px-4 py-2.5 text-center font-mono font-bold text-slate-400 dark:text-slate-500">
                                            #<?= $slot->id ?>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-[11px]">
                                                <?= htmlspecialchars($slot->nama_kelas ?? $slot->label) ?>
                                            </span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5 truncate max-w-[160px]">
                                                <?= !empty($slot->wali_nama) ? htmlspecialchars($slot->wali_nama) : 'Wali belum diatur' ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <div class="relative min-w-[200px]">
                                                <input type="password" name="tokens[<?= $slot->id ?>]" id="token_slot_<?= $slot->id ?>"
                                                       value="<?= htmlspecialchars($slot->token ?? '') ?>"
                                                       class="w-full pl-3 pr-8 py-1.5 text-xs font-mono bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 text-slate-900 dark:text-white rounded-lg outline-none"
                                                       placeholder="Token Fonnte <?= htmlspecialchars($slot->nama_kelas ?? '') ?>...">
                                                <button type="button" onclick="toggleTokenVisibility('token_slot_<?= $slot->id ?>', this)"
                                                        class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="device_numbers[<?= $slot->id ?>]" id="device_slot_<?= $slot->id ?>"
                                                   value="<?= htmlspecialchars($slot->device_number ?? '') ?>"
                                                   class="w-full px-3 py-1.5 text-xs font-mono bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 text-slate-900 dark:text-white rounded-lg outline-none"
                                                   placeholder="081234567890">
                                        </td>
                                        <td class="px-4 py-2.5 text-center whitespace-nowrap" id="quotaStatusCol_<?= $slot->id ?>">
                                            <?php if (!empty($slot->is_exhausted)): ?>
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20">
                                                        Limit
                                                    </span>
                                                    <button type="button" onclick="handleResetQuota(<?= $slot->id ?>, this)" class="text-[9px] text-sky-600 dark:text-sky-400 hover:underline font-bold">
                                                        Reset
                                                    </button>
                                                </div>
                                            <?php elseif (!empty($slot->token)): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Siap
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                                    Kosong
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1">
                                                <label class="inline-flex items-center gap-1 cursor-pointer mr-1" title="Aktifkan / Nonaktifkan Slot">
                                                    <input type="checkbox" name="is_actives[<?= $slot->id ?>]" value="1" <?= !empty($slot->is_active) ? 'checked' : '' ?> class="w-3.5 h-3.5 text-emerald-600 rounded">
                                                </label>
                                                <button type="button" onclick="checkSpecificSlot(<?= $slot->id ?>, this)"
                                                        class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded text-xs font-semibold transition shadow-xs cursor-pointer"
                                                        title="Tes koneksi Fonnte">
                                                    <i class="fa-solid fa-wifi text-[11px] text-emerald-600 dark:text-emerald-400"></i>
                                                </button>
                                                <button type="button" onclick="openQrModal(<?= $slot->id ?>)"
                                                        class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600 rounded text-xs font-semibold transition shadow-xs cursor-pointer"
                                                        title="Scan QR Code WhatsApp">
                                                    <i class="fa-solid fa-qrcode text-[11px]"></i>
                                                </button>
                                                <button type="button" onclick="saveSingleSlot(<?= $slot->id ?>, this)"
                                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold transition shadow-xs cursor-pointer"
                                                        title="Simpan slot ini saja">
                                                    <i class="fa-solid fa-floppy-disk text-[11px]"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tombol Simpan Semua 10 Token -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 mr-1"></i> Perubahan konfigurasi token akan langsung aktif secara realtime.
                </span>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Seluruh 10 Token</span>
                </button>
            </div>
        </form>

        <!-- Hasil Pengecekan Koneksi Realtime Fonnte -->
        <div id="fonnteStatusResult" class="hidden m-5 mt-0 p-4 rounded-xl text-xs space-y-2 border"></div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 2: FORMAT PESAN NOTIFIKASI OTOMATIS (MASUK & PULANG)               -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs flex items-center justify-center font-bold">2</span>
                    <span>Format Template Pesan WhatsApp</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Template pesan yang otomatis dikirimkan ke orang tua saat siswa melakukan presensi masuk & pulang.
                </p>
            </div>
            <span class="text-xs font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1 rounded-lg self-start sm:self-auto shadow-xs">
                <i class="fa-solid fa-circle-info text-emerald-600 dark:text-emerald-400 mr-1"></i> Klik tag variabel untuk menyisipkan
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            <!-- CARD A: FORMAT PESAN MASUK -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-right-to-bracket text-sky-600 dark:text-sky-400 text-base"></i>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Format Presensi Masuk (Hadir)</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Notifikasi saat siswa scan absen masuk</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-400 px-2 py-0.5 rounded uppercase">
                            Masuk
                        </span>
                    </div>

                    <form action="<?= BASEURL ?>/wa/save_template_masuk" method="POST" id="formTemplateMasuk" class="p-4 sm:p-5 space-y-3.5">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Format Teks Pesan:
                                </label>
                                <a href="<?= BASEURL ?>/wa/reset_template_masuk" onclick="return confirm('Kembalikan format pesan masuk ke bawaan default?')" class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:underline flex items-center gap-1 font-semibold">
                                    <i class="fa-solid fa-rotate-left text-[9px]"></i> Reset ke Default
                                </a>
                            </div>

                            <textarea name="template" id="templateMasukInput" rows="5" required
                                      oninput="updatePreviewMasuk()"
                                      class="w-full p-3 text-xs font-mono leading-relaxed bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-900 text-slate-900 dark:text-white rounded-lg outline-none"
                                      placeholder="Ketik format template pesan masuk..."><?= htmlspecialchars($data['templateMasuk'] ?? '') ?></textarea>
                        </div>

                        <!-- Tag Variable Chips -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                Sisipkan Tag:
                            </label>
                            <div class="flex flex-wrap gap-1">
                                <button type="button" onclick="insertVariable('templateMasukInput', '{nama_siswa}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {nama_siswa}</button>
                                <button type="button" onclick="insertVariable('templateMasukInput', '{kelas}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {kelas}</button>
                                <button type="button" onclick="insertVariable('templateMasukInput', '{nis}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {nis}</button>
                                <button type="button" onclick="insertVariable('templateMasukInput', '{jam_masuk}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {jam_masuk}</button>
                                <button type="button" onclick="insertVariable('templateMasukInput', '{status}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {status}</button>
                                <button type="button" onclick="insertVariable('templateMasukInput', '{tanggal}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {tanggal}</button>
                                <button type="button" onclick="insertVariable('templateMasukInput', '{nama_sekolah}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {nama_sekolah}</button>
                            </div>
                        </div>

                        <!-- Pratinjau Tampilan Pesan -->
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="fa-brands fa-whatsapp text-emerald-600 dark:text-emerald-400"></i> Pratinjau Pesan Masuk:
                            </span>
                            <div class="p-3 bg-slate-100 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700">
                                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-lg p-3 text-xs text-slate-800 dark:text-slate-100 space-y-1">
                                    <p id="previewMasukText" class="whitespace-pre-line leading-relaxed text-[11px]"></p>
                                    <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400 pt-1">
                                        <span id="previewMasukTime">07:15</span>
                                        <i class="fa-solid fa-check-double text-sky-500"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-lg shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Simpan Format Pesan Masuk</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- CARD B: FORMAT PESAN PULANG -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <i class="fa-solid fa-right-from-bracket text-emerald-600 dark:text-emerald-400 text-base"></i>
                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Format Presensi Pulang</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Notifikasi saat siswa scan checkout absen pulang</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded uppercase">
                            Pulang
                        </span>
                    </div>

                    <form action="<?= BASEURL ?>/wa/save_template" method="POST" id="formTemplatePulang" class="p-4 sm:p-5 space-y-3.5">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Format Teks Pesan:
                                </label>
                                <a href="<?= BASEURL ?>/wa/reset_template" onclick="return confirm('Kembalikan format pesan kepulangan ke bawaan default?')" class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:underline flex items-center gap-1 font-semibold">
                                    <i class="fa-solid fa-rotate-left text-[9px]"></i> Reset ke Default
                                </a>
                            </div>

                            <textarea name="template" id="templatePulangInput" rows="5" required
                                      oninput="updatePreviewPulang()"
                                      class="w-full p-3 text-xs font-mono leading-relaxed bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 focus:bg-white dark:focus:bg-slate-900 text-slate-900 dark:text-white rounded-lg outline-none"
                                      placeholder="Ketik format template pesan pulang..."><?= htmlspecialchars($data['templatePulang'] ?? '') ?></textarea>
                        </div>

                        <!-- Tag Variable Chips -->
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                Sisipkan Tag:
                            </label>
                            <div class="flex flex-wrap gap-1">
                                <button type="button" onclick="insertVariable('templatePulangInput', '{nama_siswa}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {nama_siswa}</button>
                                <button type="button" onclick="insertVariable('templatePulangInput', '{kelas}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {kelas}</button>
                                <button type="button" onclick="insertVariable('templatePulangInput', '{nis}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {nis}</button>
                                <button type="button" onclick="insertVariable('templatePulangInput', '{jam_pulang}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {jam_pulang}</button>
                                <button type="button" onclick="insertVariable('templatePulangInput', '{tanggal}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {tanggal}</button>
                                <button type="button" onclick="insertVariable('templatePulangInput', '{nama_sekolah}')" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-[10px] font-mono font-semibold border border-slate-200 dark:border-slate-600 cursor-pointer">+ {nama_sekolah}</button>
                            </div>
                        </div>

                        <!-- Live WhatsApp Bubble Preview -->
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="fa-brands fa-whatsapp text-emerald-600 dark:text-emerald-400"></i> Pratinjau Pesan Pulang:
                            </span>
                            <div class="p-3 bg-slate-100 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700">
                                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-lg p-3 text-xs text-slate-800 dark:text-slate-100 space-y-1">
                                    <p id="previewPulangText" class="whitespace-pre-line leading-relaxed text-[11px]"></p>
                                    <div class="flex items-center justify-end gap-1 text-[9px] text-slate-400 pt-1">
                                        <span id="previewPulangTime">14:30</span>
                                        <i class="fa-solid fa-check-double text-sky-500"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Simpan Format Pesan Pulang</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN 3: UJI COBA PENGIRIMAN NOTIFIKASI WHATSAPP (TEST SEND)              -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">3. Uji Coba Pengiriman Notifikasi (Test Send)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kirim pesan uji coba ke nomor WhatsApp Anda untuk memastikan token gateway berjalan normal.</p>
                </div>
            </div>
        </div>

        <form id="formTestSend" onsubmit="handleTestSendSubmit(event)" class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Nomor HP Tujuan -->
                <div class="space-y-1 sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        No. WhatsApp Tujuan: <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="testPhoneInput" required
                               class="w-full pl-8 pr-3 py-2 text-xs font-mono font-bold bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 rounded-lg outline-none text-slate-900 dark:text-white"
                               placeholder="Contoh: 081234567890">
                        <i class="fa-brands fa-whatsapp absolute left-2.5 top-2.5 text-xs text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                </div>

                <!-- 2. Tipe Notifikasi -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Tipe Pesan Presensi:
                    </label>
                    <select id="testNotifType" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 rounded-lg outline-none text-slate-900 dark:text-white cursor-pointer">
                        <option value="masuk">Presensi Masuk (Hadir)</option>
                        <option value="pulang" selected>Presensi Pulang</option>
                    </select>
                </div>

                <!-- 3. Gateway Pengirim -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Token Gateway Pengirim:
                    </label>
                    <select id="testSlotSelect" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 rounded-lg outline-none text-slate-900 dark:text-white cursor-pointer">
                        <option value="">Otomatis (Failover ke Cadangan)</option>
                        <option value="10">Slot #10 (Token Cadangan Failover)</option>
                        <?php if (!empty($data['classSlots'])): ?>
                            <?php foreach ($data['classSlots'] as $cs): ?>
                                <option value="<?= $cs->id ?>">Slot #<?= $cs->id ?> (<?= htmlspecialchars($cs->nama_kelas ?? $cs->label) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- 4. Nama Siswa Simulasi -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Nama Siswa (Simulasi):
                    </label>
                    <input type="text" id="testNamaSiswa" value="Nihlah Auliya"
                           class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-emerald-500 rounded-lg outline-none text-slate-900 dark:text-white"
                           placeholder="Nama Siswa">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                <span class="text-xs text-slate-500 dark:text-slate-400">
                    <i class="fa-solid fa-circle-info text-emerald-600 dark:text-emerald-400 mr-1"></i> Pesan uji coba akan diformat menggunakan template pesan aktif di atas.
                </span>
                <button type="submit" id="btnTestSendSubmit" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-paper-plane" id="btnTestSendIcon"></i>
                    <span id="btnTestSendText">Kirim Pesan Uji Coba</span>
                </button>
            </div>
        </form>

        <!-- Hasil Pengujian Pesan -->
        <div id="testSendResult" class="hidden m-5 mt-0 p-4 rounded-xl text-xs space-y-2 border"></div>
    </div>

</div>

<!-- ==================== MODAL SCAN QR CODE WHATSAPP ==================== -->
<div id="modalQrWhatsapp" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden my-auto shadow-xl">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-qrcode text-emerald-600 dark:text-emerald-400 text-base"></i>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white" id="modalQrTitle">Sambungkan WhatsApp (Scan QR)</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400" id="modalQrSubtitle">Slot Token Fonnte</p>
                </div>
            </div>
            <button type="button" onclick="closeQrModal()" class="w-6 h-6 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 hover:text-slate-700 dark:hover:text-white flex items-center justify-center text-xs transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-4 space-y-3 text-center">
            <!-- Loading State -->
            <div id="qrLoadingState" class="py-6 space-y-2">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-emerald-600 dark:text-emerald-400"></i>
                <p class="text-xs text-slate-500 dark:text-slate-400">Menghubungkan ke server Fonnte...</p>
            </div>

            <!-- Connected State -->
            <div id="qrConnectedState" class="hidden py-3 space-y-3">
                <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto text-lg border border-emerald-200 dark:border-emerald-500/20">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">WhatsApp Sudah Terhubung!</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto">
                    Perangkat aktif tersambung ke WhatsApp.
                </p>
                <div class="p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-left text-xs space-y-1">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Nomor Pengirim:</span> <strong id="qrDeviceNum" class="text-slate-900 dark:text-white font-mono">-</strong></div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400"><span>Status:</span> <span class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">CONNECT</span></div>
                </div>
                <div class="pt-1">
                    <button type="button" onclick="handleReconnectDevice()" id="btnReconnectDevice" class="w-full px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-lg border border-slate-200 dark:border-slate-600 transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-rotate text-amber-500"></i> Putuskan & Minta QR Baru
                    </button>
                </div>
            </div>

            <!-- QR Code State -->
            <div id="qrDisplayState" class="hidden space-y-2.5">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Arahkan WhatsApp di HP ke QR Code berikut:
                </p>
                <div class="p-2.5 bg-white rounded-lg inline-block border border-slate-200">
                    <img id="qrImageElement" src="" alt="WhatsApp QR Code" class="w-44 h-44 mx-auto object-contain">
                </div>
                
                <div class="p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-left text-[11px] space-y-0.5 text-slate-600 dark:text-slate-400">
                    <div>1. Buka <strong>WhatsApp</strong> di HP.</div>
                    <div>2. Buka <strong>Perangkat Tertaut (Linked Devices)</strong>.</div>
                    <div>3. Ketuk <strong>Tautkan Perangkat</strong> lalu scan QR di atas.</div>
                </div>

                <div class="flex items-center justify-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold animate-pulse">
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    <span>Menunggu scan QR...</span>
                </div>
            </div>

            <!-- Error State -->
            <div id="qrErrorState" class="hidden py-3 space-y-2">
                <div class="w-10 h-10 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto text-lg border border-rose-200 dark:border-rose-500/20">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Gagal Memuat QR Code</h4>
                <p class="text-xs text-rose-600 dark:text-rose-400" id="qrErrorMessage">-</p>
                <button type="button" onclick="loadSlotQr(currentQrSlotId)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-lg border border-slate-200 dark:border-slate-600 transition shadow-xs cursor-pointer">
                    Coba Lagi
                </button>
            </div>
        </div>

        <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end text-xs">
            <button type="button" onclick="closeQrModal()" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded text-xs transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ==================== JAVASCRIPT ==================== -->
<script>
    // 1. Toggle Show / Hide Password
    function toggleTokenVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    // 2. Sisipkan Tag Variabel ke Textarea
    function insertVariable(textareaId, tag) {
        const textarea = document.getElementById(textareaId);
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();

        if (textareaId === 'templateMasukInput') {
            updatePreviewMasuk();
        } else if (textareaId === 'templatePulangInput') {
            updatePreviewPulang();
        }
    }

    // 3. Live Preview - Pesan Masuk
    function updatePreviewMasuk() {
        const input = document.getElementById('templateMasukInput');
        const preview = document.getElementById('previewMasukText');
        if (!input || !preview) return;

        let text = input.value;
        const d = new Date();
        const jam = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const tgl = d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();

        text = text.replace(/\{nama_siswa\}|\{nama\}/g, 'Nihlah Auliya')
                   .replace(/\{kelas\}/g, '10 TKJ')
                   .replace(/\{nis\}/g, '20000')
                   .replace(/\{jam_masuk\}|\{jam\}/g, jam)
                   .replace(/\{status\}|\{status_masuk\}/g, '✅ Hadir Tepat Waktu')
                   .replace(/\{tanggal\}/g, tgl)
                   .replace(/\{nama_sekolah\}|\{sekolah\}/g, 'SMK NU Wargabinangun');

        preview.textContent = text;
        const timeEl = document.getElementById('previewMasukTime');
        if (timeEl) timeEl.textContent = jam;
    }

    // 4. Live Preview - Pesan Pulang
    function updatePreviewPulang() {
        const input = document.getElementById('templatePulangInput');
        const preview = document.getElementById('previewPulangText');
        if (!input || !preview) return;

        let text = input.value;
        const d = new Date();
        const jam = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const tgl = d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();

        text = text.replace(/\{nama_siswa\}|\{nama\}/g, 'Nihlah Auliya')
                   .replace(/\{kelas\}/g, '10 TKJ')
                   .replace(/\{nis\}/g, '20000')
                   .replace(/\{jam_pulang\}|\{jam\}/g, jam)
                   .replace(/\{tanggal\}/g, tgl)
                   .replace(/\{nama_sekolah\}|\{sekolah\}/g, 'SMK NU Wargabinangun');

        preview.textContent = text;
        const timeEl = document.getElementById('previewPulangTime');
        if (timeEl) timeEl.textContent = jam;
    }

    // 5. Quick Toggle Switch Notifikasi (Masuk & Pulang)
    function quickToggleNotif(type, checkbox) {
        const isChecked = checkbox.checked;
        const url = type === 'masuk' ? '<?= BASEURL ?>/wa/toggle_masuk' : '<?= BASEURL ?>/wa/toggle_pulang';

        const formData = new FormData();
        formData.append('is_ajax', '1');

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById(type === 'masuk' ? 'badgeMasukStatus' : 'badgePulangStatus');
            if (badge) {
                if (data.enabled) {
                    badge.className = 'text-emerald-600 dark:text-emerald-400';
                    badge.textContent = 'AKTIF';
                } else {
                    badge.className = 'text-slate-400';
                    badge.textContent = 'NONAKTIF';
                }
            }
            showAjaxAlert(data.message || 'Status notifikasi berhasil diubah.', true);
        })
        .catch(err => {
            checkbox.checked = !isChecked;
            showAjaxAlert('Gagal mengubah status notifikasi.', false);
        });
    }

    // 6. Simpan Baris Slot Tertentu (Single Slot Save via AJAX)
    function saveSingleSlot(slotId, btn) {
        const tokenInput = document.getElementById('token_slot_' + slotId);
        const deviceInput = document.getElementById('device_slot_' + slotId);
        const row = document.getElementById('slotRow_' + slotId);
        const activeCheck = row ? row.querySelector('input[type="checkbox"]') : null;

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i>';

        const formData = new FormData();
        formData.append('slot_id', slotId);
        formData.append('token', tokenInput ? tokenInput.value : '');
        formData.append('device_number', deviceInput ? deviceInput.value : '');
        if (activeCheck && activeCheck.checked) {
            formData.append('is_active', '1');
        }
        formData.append('is_ajax', '1');

        fetch('<?= BASEURL ?>/wa/save_slot', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            showAjaxAlert(data.message || `Slot #${slotId} berhasil disimpan!`, true);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            showAjaxAlert(`Slot #${slotId} berhasil diperbarui.`, true);
        });
    }

    // 7. Cek Koneksi Token Slot Tertentu
    function checkSpecificSlot(slotId, btn) {
        const tokenInput = document.getElementById('token_slot_' + slotId);
        const box = document.getElementById('fonnteStatusResult');
        if (!box) return;

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
        box.classList.add('hidden');

        const formData = new FormData();
        formData.append('slot_id', slotId);
        formData.append('token', tokenInput ? tokenInput.value.trim() : '');

        fetch('<?= BASEURL ?>/wa/check_device', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            box.classList.remove('hidden');

            const isOk = Boolean(data.success && (
                data.connected === true ||
                data.status === true ||
                data.status === 'connect' ||
                data.status === 'connected' ||
                data.device_status === 'connect' ||
                data.device_status === 'connected'
            ));

            if (isOk) {
                const info = data.device_info || data || {};
                const devName = info.name || data.name || 'Absensi Siswa';
                const devNum = info.device || data.device || '-';
                const devStat = (info.device_status || data.device_status || 'CONNECT').toUpperCase();
                const devPkg = info.package || data.package || 'Free / Reguler';
                const devQuota = info.quota || data.quota || '-';
                const devExp = info.expired || data.expired || '-';

                const devInput = document.getElementById('device_slot_' + slotId);
                if (devInput && (!devInput.value || devInput.value.trim() === '') && devNum !== '-') {
                    devInput.value = devNum;
                }

                const statusCol = document.getElementById('quotaStatusCol_' + slotId);
                const htmlBadge = (slotId === 10) 
                    ? '<span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1"><i class="fa-solid fa-check"></i> Siap Oper</span>'
                    : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Siap</span>';

                if (statusCol) statusCol.innerHTML = htmlBadge;

                box.className = 'm-5 mt-0 p-4 rounded-xl text-xs space-y-2 border bg-emerald-50/50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-slate-900 dark:text-white shadow-xs';
                box.innerHTML = `
                    <div class="flex items-center justify-between border-b border-emerald-200 dark:border-emerald-500/20 pb-2">
                        <div class="flex items-center gap-2 font-bold text-sm text-emerald-700 dark:text-emerald-400">
                            <i class="fa-solid fa-circle-check text-base"></i>
                            <span>Slot #${slotId} WhatsApp Terhubung!</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 uppercase font-mono">
                            ${devStat}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1 font-mono text-[11px]">
                        <div><span class="text-slate-500 dark:text-slate-400 block text-[10px]">DEVICE</span><strong class="text-slate-900 dark:text-white">${devName}</strong></div>
                        <div><span class="text-slate-500 dark:text-slate-400 block text-[10px]">NOMOR</span><strong class="text-emerald-700 dark:text-emerald-400">${devNum}</strong></div>
                        <div><span class="text-slate-500 dark:text-slate-400 block text-[10px]">PAKET</span><strong class="text-slate-900 dark:text-white">${devPkg}</strong></div>
                        <div><span class="text-slate-500 dark:text-slate-400 block text-[10px]">SISA KUOTA</span><strong class="text-slate-900 dark:text-white">${devQuota} (${devExp})</strong></div>
                    </div>
                `;
            } else {
                box.className = 'm-5 mt-0 p-4 rounded-xl text-xs space-y-2 border bg-rose-50/50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 text-slate-900 dark:text-white shadow-xs';
                box.innerHTML = `
                    <div class="flex items-center gap-2 font-bold text-sm text-rose-700 dark:text-rose-400">
                        <i class="fa-solid fa-circle-xmark text-base"></i>
                        <span>Slot #${slotId} Gagal Terhubung</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-300">${data.message || 'Token tidak valid atau belum dihubungkan ke WhatsApp.'}</p>
                `;
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            box.classList.remove('hidden');
            box.className = 'm-5 mt-0 p-4 rounded-xl text-xs space-y-2 border bg-rose-50/50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 text-slate-900 dark:text-white shadow-xs';
            box.innerHTML = `<p class="text-[11px] text-rose-700 dark:text-rose-400 font-bold">Terjadi kesalahan jaringan saat mengecek perangkat.</p>`;
        });
    }

    // 8. Reset Batas Kuota Suatu Slot
    function handleResetQuota(slotId, btn) {
        if (!confirm(`Reset status batas kuota limit untuk Slot #${slotId}? Token akan kembali aktif untuk pengiriman.`)) return;

        const formData = new FormData();
        formData.append('slot_id', slotId);
        formData.append('is_ajax', '1');

        fetch('<?= BASEURL ?>/wa/reset_quota', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            showAjaxAlert(`Status batas limit Slot #${slotId} berhasil direset!`, true);
            const statusCol = document.getElementById('quotaStatusCol_' + slotId);
            const badgeHtml = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Siap</span>';
            if (statusCol) statusCol.innerHTML = badgeHtml;
        })
        .catch(err => {
            location.reload();
        });
    }

    // 9. Modal Scan QR Code WhatsApp Fonnte
    let currentQrSlotId = null;
    let qrPollInterval = null;

    function openQrModal(slotId) {
        currentQrSlotId = slotId;
        const modal = document.getElementById('modalQrWhatsapp');
        const title = document.getElementById('modalQrTitle');
        const subtitle = document.getElementById('modalQrSubtitle');
        if (!modal) return;

        modal.classList.remove('hidden');
        if (title) title.textContent = `Sambungkan WhatsApp (Slot #${slotId})`;
        if (subtitle) subtitle.textContent = (slotId === 10) ? 'Slot #10 Token Cadangan Backup' : `Slot #${slotId} Wali Kelas`;

        loadSlotQr(slotId);
    }

    function closeQrModal() {
        const modal = document.getElementById('modalQrWhatsapp');
        if (modal) modal.classList.add('hidden');
        if (qrPollInterval) {
            clearInterval(qrPollInterval);
            qrPollInterval = null;
        }
        currentQrSlotId = null;
    }

    function setQrState(state) {
        document.getElementById('qrLoadingState').classList.toggle('hidden', state !== 'loading');
        document.getElementById('qrConnectedState').classList.toggle('hidden', state !== 'connected');
        document.getElementById('qrDisplayState').classList.toggle('hidden', state !== 'qr');
        document.getElementById('qrErrorState').classList.toggle('hidden', state !== 'error');
    }

    function loadSlotQr(slotId) {
        setQrState('loading');
        if (qrPollInterval) clearInterval(qrPollInterval);

        const tokenInput = document.getElementById('token_slot_' + slotId);
        const tokenVal = tokenInput ? tokenInput.value.trim() : '';

        if (!tokenVal) {
            setQrState('error');
            document.getElementById('qrErrorMessage').textContent = 'Token API Fonnte untuk slot ini masih kosong. Silakan isi token terlebih dahulu.';
            return;
        }

        const formData = new FormData();
        formData.append('slot_id', slotId);
        formData.append('token', tokenVal);

        fetch('<?= BASEURL ?>/wa/get_qr', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === true && data.url) {
                setQrState('qr');
                const img = document.getElementById('qrImageElement');
                if (img) img.src = data.url;

                startQrPolling(slotId);
            } else if (data.status === 'connected' || data.connected === true || (data.message && data.message.includes('already connected'))) {
                setQrState('connected');
                const devNum = document.getElementById('qrDeviceNum');
                if (devNum) devNum.textContent = data.device || '-';
            } else {
                setQrState('error');
                document.getElementById('qrErrorMessage').textContent = data.message || 'Gagal memuat QR dari server Fonnte.';
            }
        })
        .catch(err => {
            setQrState('error');
            document.getElementById('qrErrorMessage').textContent = 'Terjadi kesalahan jaringan saat meminta QR Code.';
        });
    }

    function startQrPolling(slotId) {
        if (qrPollInterval) clearInterval(qrPollInterval);
        qrPollInterval = setInterval(() => {
            const tokenInput = document.getElementById('token_slot_' + slotId);
            const formData = new FormData();
            formData.append('slot_id', slotId);
            formData.append('token', tokenInput ? tokenInput.value.trim() : '');

            fetch('<?= BASEURL ?>/wa/check_device', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === true || data.status === 'connect' || data.status === 'connected') {
                    clearInterval(qrPollInterval);
                    setQrState('connected');
                    const devNum = document.getElementById('qrDeviceNum');
                    if (devNum) devNum.textContent = data.device || '-';
                    const statusCol = document.getElementById('quotaStatusCol_' + slotId);
                    if (statusCol) {
                        statusCol.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Siap</span>';
                    }
                }
            })
            .catch(() => {});
        }, 4000);
    }

    function handleReconnectDevice() {
        if (!currentQrSlotId) return;
        if (!confirm('Putuskan sesi WhatsApp pada perangkat ini dan minta QR code baru?')) return;

        const btn = document.getElementById('btnReconnectDevice');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memutuskan sesi...';

        const tokenInput = document.getElementById('token_slot_' + currentQrSlotId);
        const formData = new FormData();
        formData.append('slot_id', currentQrSlotId);
        formData.append('token', tokenInput ? tokenInput.value.trim() : '');

        fetch('<?= BASEURL ?>/wa/disconnect_device', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            loadSlotQr(currentQrSlotId);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            loadSlotQr(currentQrSlotId);
        });
    }

    // 10. Helper Toast Alert AJAX
    function showAjaxAlert(msg, isSuccess) {
        const box = document.getElementById('ajaxAlertBox');
        const icon = document.getElementById('ajaxAlertIcon');
        const text = document.getElementById('ajaxAlertText');
        if (!box) return;

        box.classList.remove('hidden');
        if (isSuccess) {
            box.className = 'p-4 rounded-xl text-xs font-semibold flex items-center justify-between border bg-white dark:bg-slate-800 border-emerald-500/40 text-slate-900 dark:text-white shadow-xl';
            if (icon) icon.className = 'fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400';
        } else {
            box.className = 'p-4 rounded-xl text-xs font-semibold flex items-center justify-between border bg-white dark:bg-slate-800 border-rose-500/40 text-slate-900 dark:text-white shadow-xl';
            if (icon) icon.className = 'fa-solid fa-circle-exclamation text-base text-rose-600 dark:text-rose-400';
        }
        if (text) text.textContent = msg;

        setTimeout(() => {
            if (box) box.classList.add('hidden');
        }, 5000);
    }

    // 11. Uji Coba Pengiriman Pesan (Test Send)
    function handleTestSendSubmit(e) {
        e.preventDefault();

        const btn = document.getElementById('btnTestSendSubmit');
        const icon = document.getElementById('btnTestSendIcon');
        const text = document.getElementById('btnTestSendText');
        const resultBox = document.getElementById('testSendResult');
        const phone = document.getElementById('testPhoneInput').value.trim();
        const notifType = document.getElementById('testNotifType').value;
        const slotSelect = document.getElementById('testSlotSelect').value;
        const namaSiswa = document.getElementById('testNamaSiswa')?.value.trim() || 'Nihlah Auliya';

        if (!phone) {
            alert('Nomor WhatsApp tujuan wajib diisi.');
            return;
        }

        btn.disabled = true;
        if (icon) icon.className = 'fa-solid fa-spinner fa-spin';
        if (text) text.textContent = 'Mengirim Pesan...';
        resultBox.classList.add('hidden');

        const formData = new FormData();
        formData.append('phone', phone);
        formData.append('nama_siswa', namaSiswa);
        formData.append('kelas', '10 TKJ');
        formData.append('nis', '20000');
        formData.append('notif_type', notifType);
        if (slotSelect) {
            formData.append('slot_id', slotSelect);
        }

        fetch('<?= BASEURL ?>/wa/test_send', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(async res => {
            const resText = await res.text();
            try {
                return JSON.parse(resText);
            } catch (err) {
                throw new Error("Respon server: " + resText);
            }
        })
        .then(data => {
            btn.disabled = false;
            if (icon) icon.className = 'fa-solid fa-paper-plane';
            if (text) text.textContent = 'Kirim Pesan Uji Coba';

            resultBox.classList.remove('hidden');
            if (data.success && !data.is_pending) {
                resultBox.className = 'm-5 mt-0 p-4 rounded-xl text-xs space-y-1.5 border bg-emerald-50/50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-slate-900 dark:text-white shadow-xs';
                resultBox.innerHTML = `
                    <div class="flex items-center gap-2 font-bold text-sm text-emerald-700 dark:text-emerald-400">
                        <i class="fa-solid fa-circle-check text-base"></i>
                        <span>Pesan WhatsApp Berhasil Terkirim!</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-300">${data.message || 'Pesan notifikasi telah diterima oleh server Fonnte.'}</p>
                    <div class="pt-2 flex flex-wrap items-center gap-3 font-mono text-[11px]">
                        <div><span class="text-slate-500 dark:text-slate-400">TARGET:</span> <strong class="text-slate-900 dark:text-white">${phone}</strong></div>
                        <div><span class="text-slate-500 dark:text-slate-400">GATEWAY:</span> <strong class="text-emerald-700 dark:text-emerald-400">Slot #${data.slot_used || 'Auto'}</strong></div>
                        <div><span class="text-slate-500 dark:text-slate-400">STATUS:</span> <strong class="text-slate-900 dark:text-white">${data.fonnte_status || 'SENT'}</strong></div>
                    </div>
                `;
            } else if (data.success && data.is_pending) {
                resultBox.className = 'm-5 mt-0 p-4 rounded-xl text-xs space-y-1.5 border bg-amber-50/50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30 text-slate-900 dark:text-white shadow-xs';
                resultBox.innerHTML = `
                    <div class="flex items-center gap-2 font-bold text-sm text-amber-700 dark:text-amber-400">
                        <i class="fa-solid fa-clock text-base"></i>
                        <span>Pesan Masuk Antrean (Pending)</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-300">${data.message || 'Pesan telah masuk antrean Fonnte.'}</p>
                `;
            } else {
                resultBox.className = 'm-5 mt-0 p-4 rounded-xl text-xs space-y-1.5 border bg-rose-50/50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 text-slate-900 dark:text-white shadow-xs';
                resultBox.innerHTML = `
                    <div class="flex items-center gap-2 font-bold text-sm text-rose-700 dark:text-rose-400">
                        <i class="fa-solid fa-triangle-exclamation text-base"></i>
                        <span>Gagal Mengirimkan Pesan WhatsApp</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-300">${data.message || 'Terjadi kesalahan teknis.'}</p>
                `;
            }
        })
        .catch(err => {
            btn.disabled = false;
            if (icon) icon.className = 'fa-solid fa-paper-plane';
            if (text) text.textContent = 'Kirim Pesan Uji Coba';

            resultBox.classList.remove('hidden');
            resultBox.className = 'm-5 mt-0 p-4 rounded-xl text-xs space-y-1.5 border bg-rose-50/50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 text-slate-900 dark:text-white shadow-xs';
            resultBox.innerHTML = `
                <div class="flex items-center gap-2 font-bold text-sm text-rose-700 dark:text-rose-400">
                    <i class="fa-solid fa-circle-xmark text-base"></i>
                    <span>Terjadi Kesalahan Jaringan</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-300">${err.message}</p>
            `;
        });
    }

    // Inisialisasi awal saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', function() {
        updatePreviewMasuk();
        updatePreviewPulang();
    });
</script>
