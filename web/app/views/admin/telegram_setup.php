<?php
// $data dari TelegramController::index()
// $bots, $kelasData, $results, $totalSet, $totalSetPkl, $totalOk, $totalErr
?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- SETUP TELEGRAM BOT — desain Tailwind, seragam dengan dashboard admin   -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->

<div class="space-y-6">

    <!-- ── PAGE HEADER ──────────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    9 Bot Kelas Aktif
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-[11px] font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-briefcase text-[10px]"></i> Jalur Notifikasi PKL
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Setup Telegram Bot</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Atur ID penerima notifikasi Telegram untuk <strong>Wali Kelas</strong> (absensi harian) dan <strong>Guru Pembimbing PKL</strong> (absensi datang/pulang PKL). Notifikasi PKL dikirim otomatis dari bot kelas masing-masing ke ID Pembimbing PKL.
            </p>
        </div>
        <div class="text-xs text-slate-400 text-right">
            <div class="font-semibold text-slate-600"><?= date('d F Y') ?></div>
            <div><?= date('H:i:s') ?> WIB</div>
        </div>
    </div>

    <!-- ── STATS ─────────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-4 text-center hover:border-emerald-200 transition-colors">
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600"><?= $data['totalSet'] ?? 0 ?><span class="text-base text-slate-400">/9</span></div>
            <div class="text-[11px] text-slate-500 font-semibold mt-1">👤 ID Wali Kelas Terisi</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-4 text-center hover:border-indigo-200 transition-colors">
            <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600"><?= $data['totalSetPkl'] ?? 0 ?><span class="text-base text-slate-400">/9</span></div>
            <div class="text-[11px] text-slate-500 font-semibold mt-1">💼 ID Pembimbing PKL</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-4 text-center hover:border-green-200 transition-colors">
            <div class="text-2xl sm:text-3xl font-extrabold text-green-500"><?= $data['totalOk'] ?? 0 ?></div>
            <div class="text-[11px] text-slate-500 font-semibold mt-1">✅ Test Berhasil</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-4 text-center hover:border-red-200 transition-colors">
            <div class="text-2xl sm:text-3xl font-extrabold text-red-500"><?= $data['totalErr'] ?? 0 ?></div>
            <div class="text-[11px] text-slate-500 font-semibold mt-1">⚠️ Test Gagal</div>
        </div>
    </div>

    <!-- ── ACTION BAR (Simpan & Test Semua) ─────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5 relative overflow-hidden">
        <!-- Accent line atas -->
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-400 via-indigo-400 to-emerald-600 rounded-t-2xl"></div>
        <form method="POST" id="formSaveAll">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-slate-800">💾 Simpan Semua ID Penerima Kelas Sekaligus</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Simpan seluruh User ID Wali Kelas dan ID Pembimbing PKL ke database dalam satu klik.</p>
                </div>
                <div class="flex gap-2 overflow-x-auto pb-2 flex-nowrap hide-scrollbar">
                    <button type="submit" name="save_all"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold shadow-sm hover:from-emerald-600 hover:to-emerald-700 hover:shadow-emerald-200 hover:shadow-md transition-all whitespace-nowrap cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Semua ID
                    </button>
                    <button type="submit" name="test_all"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-xs font-bold shadow-sm hover:from-teal-600 hover:to-emerald-600 hover:shadow-teal-200 hover:shadow-md transition-all whitespace-nowrap cursor-pointer">
                        <i class="fa-solid fa-paper-plane"></i> Test Semua Bot
                    </button>
                </div>
            </div>
            <!-- Hidden fields untuk nilai dari kartu -->
            <?php foreach ($data['bots'] as $bot):
                $keyWali = 'uid_' . preg_replace('/\s+/', '_', $bot['nama_kelas']);
                $keyPkl  = 'uid_pkl_' . preg_replace('/\s+/', '_', $bot['nama_kelas']);
            ?>
            <input type="hidden" name="<?= $keyWali ?>" id="hidden_<?= $keyWali ?>">
            <input type="hidden" name="<?= $keyPkl ?>" id="hidden_<?= $keyPkl ?>">
            <?php endforeach; ?>
        </form>
    </div>

    <!-- ── BOT CARDS ─────────────────────────────────────────────────────── -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                Konfigurasi ID Penerima Per Kelas (Wali Kelas &amp; Pembimbing PKL)
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php
        $jurusanColors = [
            'tsm' => ['bar' => 'from-orange-400 to-orange-500', 'tag' => 'bg-orange-50 text-orange-600 border-orange-200', 'dot' => 'bg-orange-400'],
            'tkr' => ['bar' => 'from-purple-400 to-purple-500',  'tag' => 'bg-purple-50 text-purple-600 border-purple-200',   'dot' => 'bg-purple-400'],
            'tkj' => ['bar' => 'from-teal-400 to-teal-500',     'tag' => 'bg-teal-50 text-teal-600 border-teal-200',         'dot' => 'bg-teal-400'],
        ];
        foreach ($data['bots'] as $bot):
            $nama       = $bot['nama_kelas'];
            $parts      = explode(' ', $nama);
            $tingkat    = $parts[0];
            $jurusan    = $parts[1] ?? '';
            $jLow       = strtolower($jurusan);
            $keyWali    = 'uid_' . preg_replace('/\s+/', '_', $nama);
            $keyPkl     = 'uid_pkl_' . preg_replace('/\s+/', '_', $nama);
            $chatId     = $data['kelasData'][$nama]['chat_id'] ?? '';
            $chatIdPkl  = $data['kelasData'][$nama]['chat_id_pkl'] ?? '';
            $statusKelas = $data['kelasData'][$nama]['status'] ?? 'aktif';
            $res        = $data['results'][$nama] ?? null;
            $resPkl     = $data['results'][$nama . '_pkl'] ?? null;
            $colors     = $jurusanColors[$jLow] ?? $jurusanColors['tkj'];

            $isPkl = ($statusKelas === 'pkl');
        ?>
        <div class="bg-white rounded-2xl border <?= $isPkl ? 'border-indigo-200 shadow-indigo-50/50' : 'border-slate-100' ?> shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden flex flex-col">
            <!-- Color bar jurusan -->
            <div class="h-1 bg-gradient-to-r <?= $colors['bar'] ?>"></div>

            <div class="p-5 flex-1 flex flex-col space-y-3.5">
                <!-- Head -->
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base sm:text-lg font-extrabold text-slate-800 tracking-tight">Kelas <?= $nama ?></span>
                            <?php if ($isPkl): ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-indigo-100 text-indigo-700 border border-indigo-200">
                                    PKL
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5">
                            <span>🤖 Bot:</span>
                            <a href="https://t.me/<?= $bot['username'] ?>" target="_blank" class="text-emerald-600 font-semibold hover:underline">
                                @<?= $bot['username'] ?>
                            </a>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border <?= $colors['tag'] ?>">
                        <?= $jurusan ?>
                    </span>
                </div>

                <!-- FORM INPUT PER KELAS (DUA ID: WALI & PEMBIMBING PKL) -->
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="kelas_name" value="<?= $nama ?>">
                    <input type="hidden" name="single_uid" id="submit_uid_<?= $keyWali ?>">
                    <input type="hidden" name="single_uid_pkl" id="submit_uid_pkl_<?= $keyPkl ?>">

                    <!-- Field 1: User ID Wali Kelas -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center justify-between">
                            <span><i class="fa-solid fa-chalkboard-user text-slate-400 mr-1"></i> ID Telegram Wali Kelas</span>
                            <span class="text-[10px] font-normal text-slate-400">Absensi Sekolah</span>
                        </label>
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500 transition-all">
                            <input
                                type="text"
                                id="inp_<?= $keyWali ?>"
                                placeholder="ID Chat Wali Kelas..."
                                value="<?= htmlspecialchars($chatId) ?>"
                                oninput="syncHidden('<?= $keyWali ?>', this.value)"
                                class="w-full bg-transparent border-none outline-none py-2 px-3 text-xs font-semibold text-slate-700 placeholder-slate-300 font-mono"
                            >
                        </div>
                    </div>

                    <!-- Field 2: User ID Pembimbing PKL -->
                    <div>
                        <label class="block text-[11px] font-bold text-indigo-900 mb-1 flex items-center justify-between">
                            <span><i class="fa-solid fa-user-tie text-indigo-500 mr-1"></i> ID Telegram Pembimbing PKL</span>
                            <span class="text-[10px] font-bold text-indigo-600">Presensi PKL</span>
                        </label>
                        <div class="flex items-center bg-indigo-50/40 border border-indigo-200 rounded-xl overflow-hidden focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all">
                            <input
                                type="text"
                                id="inp_<?= $keyPkl ?>"
                                placeholder="ID Chat Pembimbing PKL..."
                                value="<?= htmlspecialchars($chatIdPkl) ?>"
                                oninput="syncHidden('<?= $keyPkl ?>', this.value)"
                                class="w-full bg-transparent border-none outline-none py-2 px-3 text-xs font-semibold text-indigo-900 placeholder-indigo-300 font-mono"
                            >
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5">Notif Datang & Pulang siswa PKL kelas ini dikirim dari <strong>@<?= $bot['username'] ?></strong> ke ID ini.</p>
                    </div>

                    <!-- Tombol Simpan Kelas Ini -->
                    <div class="pt-1">
                        <button
                            type="submit"
                            name="save_single"
                            onclick="
                                document.getElementById('submit_uid_<?= $keyWali ?>').value = document.getElementById('inp_<?= $keyWali ?>').value;
                                document.getElementById('submit_uid_pkl_<?= $keyPkl ?>').value = document.getElementById('inp_<?= $keyPkl ?>').value;
                            "
                            class="w-full py-1.5 px-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fa-solid fa-floppy-disk text-[11px]"></i> Simpan ID Kelas <?= $nama ?>
                        </button>
                    </div>
                </form>

                <!-- Tombol Action Test -->
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <form method="POST">
                        <input type="hidden" name="kelas_name" value="<?= $nama ?>">
                        <button type="submit" name="test_single"
                            onclick="this.textContent='⏳ Menguji...'"
                            class="w-full py-1.5 px-2 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-[11px] font-semibold transition-colors cursor-pointer text-center truncate"
                            title="Test notifikasi ke ID Wali Kelas">
                            <i class="fa-solid fa-paper-plane mr-1 text-[10px]"></i> Test Wali
                        </button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="kelas_name" value="<?= $nama ?>">
                        <button type="submit" name="test_single_pkl"
                            onclick="this.textContent='⏳ Menguji...'"
                            class="w-full py-1.5 px-2 rounded-lg bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-800 text-[11px] font-semibold transition-colors cursor-pointer text-center truncate"
                            title="Test notifikasi ke ID Pembimbing PKL">
                            <i class="fa-solid fa-briefcase mr-1 text-[10px]"></i> Test PKL
                        </button>
                    </form>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── TABEL STATUS REKAP BOT ────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">📋 Ringkasan Bot &amp; Jalur ID Penerima Telegram</h3>
            <span class="text-xs text-slate-400">9 Kelas Terdaftar</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-100 uppercase tracking-wider text-[10px] font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Bot Telegram</th>
                        <th class="px-6 py-3">ID Wali Kelas (Reguler)</th>
                        <th class="px-6 py-3">ID Pembimbing PKL</th>
                        <th class="px-6 py-3">Status Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php foreach ($data['bots'] as $bot):
                    $nama   = $bot['nama_kelas'];
                    $chatId = $data['kelasData'][$nama]['chat_id'] ?? '';
                    $chatIdPkl = $data['kelasData'][$nama]['chat_id_pkl'] ?? '';
                    $st     = $data['kelasData'][$nama]['status'] ?? 'aktif';
                ?>
                <tr class="hover:bg-slate-50/50">
                    <td class="px-6 py-3 font-bold text-slate-800"><?= $nama ?></td>
                    <td class="px-6 py-3 font-semibold text-emerald-600">@<?= $bot['username'] ?></td>
                    <td class="px-6 py-3 font-mono">
                        <?= !empty($chatId) ? htmlspecialchars($chatId) : '<span class="text-slate-300 italic">— belum diset</span>' ?>
                    </td>
                    <td class="px-6 py-3 font-mono text-indigo-700 font-semibold">
                        <?= !empty($chatIdPkl) ? htmlspecialchars($chatIdPkl) : '<span class="text-slate-300 italic">— belum diset</span>' ?>
                    </td>
                    <td class="px-6 py-3">
                        <?php if ($st === 'pkl'): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-indigo-100 text-indigo-700 border border-indigo-200">
                                PKL
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium text-slate-500 bg-slate-100">
                                Aktif Normal
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /space-y-6 -->

<script>
function syncHidden(key, val) {
    const el = document.getElementById('hidden_' + key);
    if (el) el.value = val;
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[id^="inp_"]').forEach(inp => {
        const id = inp.id.replace('inp_', '');
        syncHidden(id, inp.value);
    });
});
</script>
<style>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>