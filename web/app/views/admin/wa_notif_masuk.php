<div class="w-full space-y-6">

    <!-- Top Breadcrumb & Action Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 dark:text-blue-400 mb-1">
                <i class="fa-brands fa-whatsapp text-base"></i>
                <span>Fitur Otomatisasi Absensi</span>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <span class="text-slate-500 dark:text-slate-400 font-normal">WhatsApp Orang Tua &mdash; Kehadiran</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight flex items-center gap-2 flex-wrap">
                <span>Notifikasi WA Siswa Hadir (Masuk)</span>
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60">
                    Fitur Baru
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kirim pesan WhatsApp otomatis ke nomor HP orang tua / wali seketika siswa berhasil melakukan check-in absen masuk sekolah (hadir).
            </p>
        </div>

        <!-- Quick Switch Tabs -->
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 bg-slate-100 dark:bg-slate-900/60 p-1.5 rounded-xl self-start md:self-auto border border-slate-200 dark:border-slate-700">
            <a href="<?= BASEURL ?>/wa/notif_masuk" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-blue-800 dark:text-blue-400 shadow-xs border border-blue-200/60 dark:border-blue-800/60 flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-right-to-bracket text-blue-600 dark:text-blue-400"></i> Notif Masuk
            </a>
            <a href="<?= BASEURL ?>/wa/notif_pulang" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 flex items-center gap-1.5 transition-all">
                <i class="fa-brands fa-whatsapp"></i> Notif Pulang
            </a>
            <a href="<?= BASEURL ?>/wa/config" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 flex items-center gap-1.5 transition-all">
                <i class="fa-solid fa-sliders"></i> Konfigurasi
            </a>
        </div>
    </div>

    <!-- Quick Stats Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Status Otomatisasi -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status Fitur</p>
                <div class="flex items-center gap-2 mt-1">
                    <span id="statusBadgeIndicator" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold <?= $data['isEnabled'] ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600' ?>">
                        <span class="w-2 h-2 rounded-full <?= $data['isEnabled'] ? 'bg-blue-500 animate-pulse' : 'bg-slate-400' ?>"></span>
                        <?= $data['isEnabled'] ? 'AKTIF OTOMATIS' : 'NONAKTIF' ?>
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Saat scan masuk sukses</p>
            </div>
            <form action="<?= BASEURL ?>/wa/toggle_masuk" method="POST" id="toggleMasukForm">
                <input type="hidden" name="is_ajax" value="1">
                <label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan / menonaktifkan">
                    <input type="checkbox" id="toggleMasukSwitch" class="sr-only peer" <?= $data['isEnabled'] ? 'checked' : '' ?> onchange="handleToggleMasuk(this)">
                    <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </form>
        </div>

        <!-- 2. Terkirim Hari Ini -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Terkirim Hari Ini</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white mt-1"><?= number_format($data['stats']['sent_today']) ?></h3>
                <p class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold mt-0.5">
                    <i class="fa-solid fa-check-double mr-1"></i>Pesan kehadiran siswa
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg border border-blue-100 dark:border-blue-900/50">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
        </div>

        <!-- 3. Gateway Terhubung -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Device Pengirim</p>
                <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-white mt-1 truncate">
                    <?= htmlspecialchars($data['mainDevice']->label ?? 'Device Utama') ?>
                </h3>
                <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                    <?= !empty($data['mainDevice']->device_number) ? htmlspecialchars($data['mainDevice']->device_number) : 'Nomor belum diisi' ?>
                </p>
            </div>
            <a href="<?= BASEURL ?>/wa/config" class="w-11 h-11 rounded-xl bg-slate-50 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-300 hover:text-blue-700 flex items-center justify-center text-base border border-slate-200 dark:border-slate-600 transition-colors shrink-0" title="Atur Gateway">
                <i class="fa-solid fa-gear"></i>
            </a>
        </div>

        <!-- 4. Success Rate -->
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tingkat Keberhasilan</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white mt-1"><?= $data['stats']['success_rate'] ?>%</h3>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                    Gagal: <span class="font-bold text-rose-600 dark:text-rose-400"><?= $data['stats']['failed_today'] ?></span> | Pending: <span class="font-bold text-amber-600 dark:text-amber-400"><?= $data['stats']['pending'] ?></span>
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-100 dark:border-indigo-900/50">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
        </div>
    </div>

    <!-- Alert Status Pengaturan -->
    <div id="ajaxAlertBox" class="hidden p-4 rounded-xl text-xs font-semibold flex items-center justify-between">
        <span id="ajaxAlertText"></span>
        <button onclick="document.getElementById('ajaxAlertBox').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- MAIN TWO-COLUMN CONTENT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: LIVE WHATSAPP PHONE SIMULATOR & TEMPLATE EDITOR (col 5) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Card Phone Simulator -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="p-3.5 sm:p-4 bg-blue-50 dark:bg-blue-950/40 border-b border-blue-200/80 dark:border-blue-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-brands fa-whatsapp text-blue-600 dark:text-blue-400 text-base"></i>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Live Preview WA Masuk</span>
                    </div>
                    <span class="text-[10px] bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300 font-bold px-2 py-0.5 rounded-full border border-blue-200 dark:border-blue-800/60">
                        Realtime Simulation
                    </span>
                </div>

                <!-- Simulation Settings Bar -->
                <div class="p-3 bg-white dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700/60 flex flex-col gap-1.5">
                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Simulasikan dengan data Siswa:</label>
                    <select id="simSiswaSelect" onchange="updateLivePreviewFromSelect()" class="w-full text-xs border border-slate-200 dark:border-slate-600 rounded-lg p-2 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 outline-none">
                        <?php if (!empty($data['siswaList'])): ?>
                            <?php foreach ($data['siswaList'] as $s): ?>
                                <option value="<?= htmlspecialchars(json_encode([
                                    'nama'  => $s->name,
                                    'kelas' => $s->nama_kelas ?? '10 TKJ',
                                    'nis'   => $s->nis ?? '-',
                                    'hp'    => $s->no_hp_ortu ?? '',
                                    'id'    => $s->id
                                ])) ?>" <?= ($s->id == 12) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s->name) ?> (<?= htmlspecialchars($s->nama_kelas ?? 'Kelas') ?>) <?= !empty($s->no_hp_ortu) ? '📱 ' . htmlspecialchars($s->no_hp_ortu) : '⚠️ (No HP belum ada)' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value='{"nama":"Nihlah Auliya","kelas":"10 TKJ","nis":"20000","hp":"","id":12}'>
                                Nihlah Auliya (10 TKJ)
                            </option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- SMARTPHONE FRAME MOCKUP -->
                <div class="p-3 sm:p-4 bg-slate-100 dark:bg-slate-900/50 flex justify-center overflow-x-auto">
                    <div class="w-full max-w-[320px] sm:max-w-[340px] bg-slate-900 rounded-[38px] p-3 shadow-2xl border-4 border-slate-800 relative mx-auto shrink-0">
                        <!-- Speaker & Notch -->
                        <div class="absolute top-4 left-1/2 -translate-x-1/2 w-20 h-4 bg-slate-900 rounded-full flex items-center justify-center gap-2 z-10">
                            <span class="w-8 h-1 bg-slate-700 rounded-full"></span>
                            <span class="w-2 h-2 rounded-full bg-slate-800 border border-slate-700"></span>
                        </div>

                        <!-- Inner Screen -->
                        <div class="rounded-[28px] overflow-hidden bg-[#EFEAE2] flex flex-col h-[460px] sm:h-[480px] shadow-inner relative border border-slate-700/50">

                            <!-- WA Top App Bar -->
                            <div class="bg-[#075E54] text-white px-3 py-2.5 flex items-center justify-between pt-4">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-arrow-left text-xs opacity-80"></i>
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 border border-white/40 overflow-hidden flex items-center justify-center text-emerald-800 font-bold text-xs shrink-0">
                                        <img src="<?= BASEURL ?>/assets/img/logo.png?v=3" alt="Logo" class="w-6 h-6 object-contain" onerror="this.onerror=null; this.src='<?= BASEURL ?>/assets/img/icon-192.png'">
                                    </div>
                                    <div class="leading-tight min-w-0">
                                        <h4 class="text-xs font-bold truncate max-w-[140px]">SMK NU Wargabinangun</h4>
                                        <p class="text-[9.5px] text-emerald-200 truncate">Official Presensi Bot • Online</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5 text-xs opacity-90 pr-1 shrink-0">
                                    <i class="fa-solid fa-phone"></i>
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </div>
                            </div>

                            <!-- Chat Wallpaper Area -->
                            <div class="flex-1 p-3 overflow-y-auto space-y-3 bg-[#EFEAE2]" style="background-image: radial-gradient(#d1c7b7 0.8px, transparent 0.8px); background-size: 14px 14px;">
                                <!-- Date Pill -->
                                <div class="flex justify-center">
                                    <span class="text-[10px] font-bold bg-white/80 text-slate-600 px-3 py-0.5 rounded-full shadow-2xs border border-slate-200/60 uppercase tracking-wider">
                                        Hari Ini
                                    </span>
                                </div>

                                <!-- Security Notice -->
                                <div class="p-2 bg-[#FFF3C4] border border-[#FFE082] rounded-lg text-center shadow-2xs">
                                    <p class="text-[9.5px] text-amber-900 leading-tight">
                                        <i class="fa-solid fa-lock text-[8.5px] mr-1 text-amber-700"></i>
                                        Pesan ini terenkripsi secara otomatis melalui WhatsApp Gateway SMK NU Wargabinangun.
                                    </p>
                                </div>

                                <!-- INCOMING WHATSAPP BUBBLE -->
                                <div class="flex justify-start">
                                    <div class="bg-white rounded-xl rounded-tl-xs p-3 shadow-sm border border-slate-200/60 max-w-[92%] relative">
                                        <!-- Message Content -->
                                        <div id="liveWaBubbleText" class="text-[11.5px] text-slate-800 leading-relaxed font-sans whitespace-pre-line">
                                            <!-- Diisi oleh Javascript secara realtime -->
                                        </div>

                                        <!-- Message Timestamp & Read Status -->
                                        <div class="flex items-center justify-end gap-1 mt-1.5 pt-1 text-[9.5px] text-slate-400">
                                            <span id="liveWaTimestamp"><?= date('H:i') ?></span>
                                            <span class="text-sky-500 font-bold" title="Terkirim & Dibaca">✓✓</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- WA Bottom Input Bar (Dummy) -->
                            <div class="bg-[#F0F2F5] px-2 py-1.5 flex items-center gap-1.5 border-t border-slate-200/80">
                                <i class="fa-regular fa-face-smile text-slate-400 text-sm ml-1"></i>
                                <div class="flex-1 bg-white rounded-full px-3 py-1 text-[11px] text-slate-400 border border-slate-200 truncate">
                                    Balas pesan ini...
                                </div>
                                <div class="w-7 h-7 rounded-full bg-[#00A884] text-white flex items-center justify-center text-xs shadow-xs shrink-0">
                                    <i class="fa-solid fa-microphone"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-100 dark:border-slate-700/60 text-center">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 mr-1"></i>
                        Pesan di atas adalah persis apa yang akan dilihat oleh orang tua saat ananda tiba di sekolah.
                    </p>
                </div>
            </div>

            <!-- Card Template Editor -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-pen-to-square text-blue-600 dark:text-blue-400"></i>
                            Kustomisasi Format Pesan WA Masuk
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Edit susunan kata pesan kehadiran sesuai kebutuhan sekolah</p>
                    </div>
                </div>

                <!-- Placeholder Pills -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Klik tag di bawah untuk menyisipkan variabel otomatis:</label>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="insertTag('{nama_siswa}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 hover:text-blue-700 dark:hover:text-blue-300 rounded-md text-[11px] font-mono font-bold transition-colors">
                            {nama_siswa}
                        </button>
                        <button type="button" onclick="insertTag('{kelas}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 hover:text-blue-700 dark:hover:text-blue-300 rounded-md text-[11px] font-mono font-bold transition-colors">
                            {kelas}
                        </button>
                        <button type="button" onclick="insertTag('{nis}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 hover:text-blue-700 dark:hover:text-blue-300 rounded-md text-[11px] font-mono font-bold transition-colors">
                            {nis}
                        </button>
                        <button type="button" onclick="insertTag('{jam_masuk}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 hover:text-blue-700 dark:hover:text-blue-300 rounded-md text-[11px] font-mono font-bold transition-colors">
                            {jam_masuk}
                        </button>
                        <button type="button" onclick="insertTag('{status}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 hover:text-blue-700 dark:hover:text-blue-300 rounded-md text-[11px] font-mono font-bold transition-colors">
                            {status}
                        </button>
                        <button type="button" onclick="insertTag('{tanggal}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 hover:text-blue-700 dark:hover:text-blue-300 rounded-md text-[11px] font-mono font-bold transition-colors">
                            {tanggal}
                        </button>
                        <button type="button" onclick="insertTag('{nama_sekolah}')" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/40 text-slate-700 dark:text-slate-200 hover:text-blue-700 dark:hover:text-blue-300 rounded-md text-[11px] font-mono font-bold transition-colors">
                            {nama_sekolah}
                        </button>
                    </div>
                </div>

                <!-- Form Template -->
                <form action="<?= BASEURL ?>/wa/save_template_masuk" method="POST" class="space-y-3">
                    <textarea id="templateTextarea" name="template" rows="9" oninput="handleTemplateInput(this.value)"
                              class="w-full text-xs font-mono p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-slate-900 outline-none transition-all leading-relaxed"><?= htmlspecialchars($data['template']) ?></textarea>

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Format Pesan
                        </button>
                        <button type="button" onclick="if(confirm('Kembalikan format pesan ke template bawaan sistem?')) document.getElementById('formResetTemplateMasuk').submit();" class="text-xs text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:underline cursor-pointer">
                            Reset ke Standar
                        </button>
                    </div>
                </form>

                <!-- Hidden Reset Form (Valid HTML5 Standalone Form) -->
                <form id="formResetTemplateMasuk" action="<?= BASEURL ?>/wa/reset_template_masuk" method="POST" class="hidden"></form>
            </div>

        </div>

        <!-- RIGHT COLUMN: TESTING FORM & REALTIME QUEUE LOGS (col 7) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Card Quick Test Send WA -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs p-5 sm:p-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-3 mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane text-blue-600 dark:text-blue-400"></i>
                            Uji Coba Kirim Notifikasi WA Masuk Sekarang
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kirim pesan uji coba ke nomor WhatsApp Anda atau nomor orang tua</p>
                    </div>
                    <span class="text-[11px] bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-2 py-0.5 rounded-md font-mono font-bold">
                        Fonnte API
                    </span>
                </div>

                <form id="formTestSend" onsubmit="handleTestSend(event)" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Nomor WhatsApp Tujuan *
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-mono text-xs">
                                    <i class="fa-brands fa-whatsapp text-blue-600 dark:text-blue-400 text-sm"></i>
                                </span>
                                <input type="text" id="testPhoneInput" required
                                       class="w-full pl-9 pr-3 py-2 text-xs font-mono bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                                       placeholder="Contoh: 08123456789 atau 628123456789"
                                       value="">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Simulasi Jam Masuk
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-xs">
                                    <i class="fa-regular fa-clock text-slate-500"></i>
                                </span>
                                <input type="time" id="testJamInput" value="<?= date('H:i') ?>"
                                       class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none font-mono">
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-blue-50/50 dark:bg-blue-950/30 rounded-xl border border-blue-100 dark:border-blue-900/40 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap text-blue-700 dark:text-blue-400"></i>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Siswa: <strong id="testNamaLabel" class="text-slate-900 dark:text-white">Nihlah Auliya</strong> (<span id="testKelasLabel">10 TKJ</span>)</span>
                        </div>
                        <span class="text-[10px] text-blue-700 dark:text-blue-300 font-bold bg-blue-100/70 dark:bg-blue-900/50 px-2 py-0.5 rounded-full">Siap Dikirim</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" id="btnTestSend" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-xs rounded-lg shadow-xs transition-all flex items-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-paper-plane" id="btnTestSendIcon"></i>
                            <span id="btnTestSendText">Kirim Pesan Tes WhatsApp</span>
                        </button>
                        <span id="testSendResultMsg" class="text-xs font-semibold"></span>
                    </div>
                </form>

                <!-- Feedback Box -->
                <div id="testFeedbackBox" class="hidden mt-4 p-3.5 rounded-xl text-xs space-y-1 transition-all"></div>
            </div>

            <!-- Card Realtime Queue & History -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600 dark:text-blue-400"></i>
                            Log Notifikasi WA Absen Masuk Terbaru
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar riwayat notifikasi kehadiran siswa yang dikirimkan ke nomor WhatsApp orang tua</p>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <button onclick="window.location.reload()" class="p-2 rounded-lg bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 text-xs transition-colors cursor-pointer" title="Muat Ulang Log">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                        <a href="<?= BASEURL ?>/wa/config" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Lihat Semua Antrean <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Table Logs (Responsive min-w-[650px]) -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs min-w-[650px]">
                        <thead class="bg-slate-50 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200/80 dark:border-slate-700 uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Siswa</th>
                                <th class="px-4 py-3">No. WhatsApp Ortu</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-slate-700 dark:text-slate-200 font-medium">
                            <?php if (!empty($data['recentLogs'])): ?>
                                <?php foreach ($data['recentLogs'] as $log): ?>
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-colors">
                                        <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap text-slate-500 dark:text-slate-400">
                                            <?= !empty($log->created_at) ? date('d/m H:i', strtotime($log->created_at)) : '-' ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-800 dark:text-slate-100"><?= htmlspecialchars($log->nama_siswa ?? ('Siswa #' . $log->siswa_id)) ?></div>
                                            <div class="text-[10px] text-slate-400"><?= htmlspecialchars($log->nama_kelas ?? '-') ?> &bull; NIS: <?= htmlspecialchars($log->nis ?? '-') ?></div>
                                        </td>
                                        <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap">
                                            <a href="https://wa.me/<?= htmlspecialchars($log->target_phone) ?>" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                                <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                                <?= htmlspecialchars($log->target_phone) ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <?php if ($log->status === 'sent'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                                    <i class="fa-solid fa-check text-[9px]"></i> Terkirim
                                                </span>
                                            <?php elseif ($log->status === 'pending'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                                                    <i class="fa-solid fa-hourglass-half text-[9px]"></i> Menunggu
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60" title="<?= htmlspecialchars($log->response ?? 'Gagal kirim') ?>">
                                                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> Gagal
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <button type="button" onclick="showLogDetailModal(<?= htmlspecialchars(json_encode($log)) ?>)" class="px-2.5 py-1 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-slate-600 rounded-lg text-xs font-semibold transition-colors cursor-pointer" title="Lihat Isi Pesan">
                                                Detail
                                            </button>
                                            <?php if ($log->status !== 'sent'): ?>
                                                <button type="button" onclick="resendLogQueue(<?= $log->id ?>, this)" class="px-2.5 py-1 text-blue-700 dark:text-blue-300 hover:text-blue-800 bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors ml-1 cursor-pointer" title="Kirim Ulang Pesan">
                                                    <i class="fa-solid fa-rotate-right"></i> Kirim Ulang
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500">
                                        <i class="fa-solid fa-right-to-bracket text-3xl mb-2 text-slate-300 dark:text-slate-600 block"></i>
                                        <p class="text-xs font-medium">Belum ada riwayat pengiriman notifikasi WhatsApp kehadiran (masuk) siswa.</p>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Notifikasi akan otomatis masuk ke tabel ini saat siswa men-tap kartu RFID saat jam masuk.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- MODAL DETAIL LOG PESAN WHATSAPP -->
<div id="modalLogDetail" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-3 sm:p-4" onclick="if(event.target === this) closeModalLogDetail()">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-lg p-5 sm:p-6 space-y-4 animate-slide-up border border-slate-200 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fa-brands fa-whatsapp text-blue-600 dark:text-blue-400 text-lg"></i>
                Detail Pesan WhatsApp Masuk (Hadir)
            </h3>
            <button onclick="closeModalLogDetail()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <div class="space-y-3 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-bold block">Penerima (Orang Tua):</span>
                    <span id="modalTargetPhone" class="font-mono font-bold text-slate-800 dark:text-slate-100"></span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-bold block">Nama Siswa:</span>
                    <span id="modalSiswaName" class="font-bold text-slate-800 dark:text-slate-100"></span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-bold block">Status:</span>
                    <span id="modalStatusBadge"></span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 text-[10px] uppercase font-bold block">Waktu Tercatat:</span>
                    <span id="modalSentAt" class="font-mono text-slate-700 dark:text-slate-300"></span>
                </div>
            </div>

            <div>
                <span class="text-slate-500 dark:text-slate-400 text-[11px] font-bold block mb-1">Isi Pesan:</span>
                <div id="modalMessageContent" class="p-3 bg-slate-900 text-slate-100 rounded-xl font-mono text-[11px] whitespace-pre-line max-h-56 overflow-y-auto leading-relaxed border border-slate-800"></div>
            </div>

            <div>
                <span class="text-slate-500 dark:text-slate-400 text-[11px] font-bold block mb-1">Respon Gateway / Fonnte:</span>
                <div id="modalResponseContent" class="p-2.5 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-xl font-mono text-[10.5px] truncate max-h-20 overflow-y-auto border border-slate-200 dark:border-slate-700"></div>
            </div>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="button" onclick="closeModalLogDetail()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition-colors cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- SCRIPTS INTERAKTIF SIMULATOR & AJAX -->
<script>
var currentSimData = {
    nama: "Nihlah Auliya",
    kelas: "10 TKJ",
    nis: "20000",
    hp: "",
    id: 12
};

document.addEventListener('DOMContentLoaded', function() {
    updateLivePreviewFromSelect();
});

function updateLivePreviewFromSelect() {
    var select = document.getElementById('simSiswaSelect');
    if (!select) return;
    try {
        var parsed = JSON.parse(select.value);
        currentSimData = parsed;
        
        var phoneInput = document.getElementById('testPhoneInput');
        if (phoneInput && currentSimData.hp) {
            phoneInput.value = currentSimData.hp;
        }
        
        var namaLabel = document.getElementById('testNamaLabel');
        if (namaLabel) namaLabel.textContent = currentSimData.nama;
        
        var kelasLabel = document.getElementById('testKelasLabel');
        if (kelasLabel) kelasLabel.textContent = currentSimData.kelas;

        renderLiveBubble();
    } catch(e) {
        console.error(e);
    }
}

function renderLiveBubble() {
    var textarea = document.getElementById('templateTextarea');
    var bubbleText = document.getElementById('liveWaBubbleText');
    if (!textarea || !bubbleText) return;

    var rawTpl = textarea.value;
    var jam = (document.getElementById('testJamInput')?.value) || '07:00';

    var rendered = rawTpl
        .replace(/{nama_siswa}/g, currentSimData.nama || 'Nihlah Auliya')
        .replace(/{nama}/g, currentSimData.nama || 'Nihlah Auliya')
        .replace(/{kelas}/g, currentSimData.kelas || '10 TKJ')
        .replace(/{nis}/g, currentSimData.nis || '-')
        .replace(/{jam_masuk}/g, jam)
        .replace(/{jam}/g, jam)
        .replace(/{tanggal}/g, formatTodayIndo())
        .replace(/{nama_sekolah}/g, 'SMK NU Wargabinangun')
        .replace(/{sekolah}/g, 'SMK NU Wargabinangun')
        .replace(/{status}/g, '✅ Hadir Tepat Waktu')
        .replace(/{status_masuk}/g, '✅ Hadir Tepat Waktu');

    var htmlFormatted = rendered
        .replace(/\*(.*?)\*/g, '<strong>$1</strong>')
        .replace(/_(.*?)_/g, '<em>$1</em>');

    bubbleText.innerHTML = htmlFormatted;

    var tsElem = document.getElementById('liveWaTimestamp');
    if (tsElem) tsElem.textContent = jam;
}

function handleTemplateInput(val) {
    renderLiveBubble();
}

function insertTag(tag) {
    var textarea = document.getElementById('templateTextarea');
    if (!textarea) return;
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    var val = textarea.value;
    textarea.value = val.substring(0, start) + tag + val.substring(end);
    textarea.focus();
    textarea.selectionStart = textarea.selectionEnd = start + tag.length;
    renderLiveBubble();
}

function formatTodayIndo() {
    var bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    var d = new Date();
    return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
}

function handleToggleMasuk(checkbox) {
    var form = document.getElementById('toggleMasukForm');
    var badge = document.getElementById('statusBadgeIndicator');
    var alertBox = document.getElementById('ajaxAlertBox');
    var alertText = document.getElementById('ajaxAlertText');

    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'is_ajax=1'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.enabled) {
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-blue-800 border border-blue-200';
                badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> AKTIF OTOMATIS';
            } else {
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600 border border-slate-200';
                badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-slate-400"></span> NONAKTIF';
            }

            alertBox.className = 'p-4 rounded-xl text-xs font-semibold flex items-center justify-between ' + 
                                 (data.enabled ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-amber-50 text-amber-800 border border-amber-200');
            alertText.textContent = data.message;
            alertBox.classList.remove('hidden');
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan koneksi.');
    });
}

function handleTestSend(e) {
    e.preventDefault();
    var btn = document.getElementById('btnTestSend');
    var icon = document.getElementById('btnTestSendIcon');
    var text = document.getElementById('btnTestSendText');
    var phone = document.getElementById('testPhoneInput').value;
    var jam = document.getElementById('testJamInput').value;
    var feedbackBox = document.getElementById('testFeedbackBox');

    btn.disabled = true;
    icon.className = 'fa-solid fa-spinner fa-spin';
    text.textContent = 'Menghubungkan ke Fonnte...';
    feedbackBox.classList.add('hidden');

    var params = new URLSearchParams();
    params.append('phone', phone);
    params.append('nama_siswa', currentSimData.nama);
    params.append('kelas', currentSimData.kelas);
    params.append('nis', currentSimData.nis);
    params.append('jam_masuk', jam);
    params.append('siswa_id', currentSimData.id);

    fetch('<?= BASEURL ?>/wa/test_send_masuk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        icon.className = 'fa-solid fa-paper-plane';
        text.textContent = 'Kirim Pesan Tes WhatsApp';

        feedbackBox.classList.remove('hidden');
        if (res.success) {
            feedbackBox.className = 'mt-4 p-3.5 rounded-xl text-xs space-y-1 bg-emerald-50 text-emerald-900 border border-emerald-200';
            feedbackBox.innerHTML = '<strong><i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Berhasil Terkirim!</strong><p>' + res.message + '</p>';
        } else {
            feedbackBox.className = 'mt-4 p-3.5 rounded-xl text-xs space-y-1 bg-red-50 text-red-900 border border-red-200';
            feedbackBox.innerHTML = '<strong><i class="fa-solid fa-circle-exclamation text-red-600 mr-1"></i> Pengiriman Gagal:</strong><p>' + res.message + '</p>' +
                                    (res.http_code ? '<p class="text-[10px] text-red-500 font-mono">Kode HTTP: ' + res.http_code + '</p>' : '');
        }
    })
    .catch(err => {
        btn.disabled = false;
        icon.className = 'fa-solid fa-paper-plane';
        text.textContent = 'Kirim Pesan Tes WhatsApp';
        feedbackBox.classList.remove('hidden');
        feedbackBox.className = 'mt-4 p-3.5 rounded-xl text-xs bg-red-50 text-red-900 border border-red-200';
        feedbackBox.innerHTML = '<strong>Gagal melakukan request ke server lokal:</strong> ' + err.message;
    });
}

function showLogDetailModal(log) {
    document.getElementById('modalTargetPhone').textContent = log.target_phone || '-';
    document.getElementById('modalSiswaName').textContent = (log.nama_siswa || 'Siswa #' + log.siswa_id) + ' (' + (log.nama_kelas || '-') + ')';
    document.getElementById('modalSentAt').textContent = log.created_at || '-';
    document.getElementById('modalMessageContent').textContent = log.message || '-';
    document.getElementById('modalResponseContent').textContent = log.response || '-';

    var statusElem = document.getElementById('modalStatusBadge');
    if (log.status === 'sent') {
        statusElem.className = 'text-emerald-700 font-bold';
        statusElem.textContent = '✅ Berhasil Terkirim';
    } else if (log.status === 'pending') {
        statusElem.className = 'text-amber-700 font-bold';
        statusElem.textContent = '⏳ Menunggu Antrean';
    } else {
        statusElem.className = 'text-red-700 font-bold';
        statusElem.textContent = '❌ Gagal Terkirim';
    }

    document.getElementById('modalLogDetail').classList.remove('hidden');
}

function closeModalLogDetail() {
    document.getElementById('modalLogDetail').classList.add('hidden');
}

function resendLogQueue(queueId, btn) {
    if (!confirm('Kirim ulang pesan WhatsApp ini sekarang?')) return;
    var origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    var params = new URLSearchParams();
    params.append('queue_id', queueId);

    fetch('<?= BASEURL ?>/wa/resend_queue', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(r => r.json())
    .then(res => {
        btn.innerHTML = origHtml;
        btn.disabled = false;
        alert(res.message);
        window.location.reload();
    })
    .catch(err => {
        btn.innerHTML = origHtml;
        btn.disabled = false;
        alert('Gagal mengirim ulang: ' + err.message);
    });
}
</script>
