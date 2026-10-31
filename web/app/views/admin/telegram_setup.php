<?php
// $data dari TelegramController::index()
// $bots, $kelasData, $results, $totalSet, $totalOk, $totalErr
?>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- SETUP TELEGRAM BOT — desain Tailwind, seragam dengan dashboard admin   -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->

<div class="space-y-6">

    <!-- ── PAGE HEADER ──────────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    9 Bot Aktif
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Setup Telegram Bot</h1>
            <p class="text-sm text-slate-500 mt-0.5">Atur User ID Telegram wali kelas per kelas. Setiap kelas memiliki bot sendiri.</p>
        </div>
        <div class="text-xs text-slate-400 text-right">
            <div class="font-semibold text-slate-600"><?= date('d F Y') ?></div>
            <div><?= date('H:i:s') ?></div>
        </div>
    </div>

    <!-- ── STATS ─────────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-4 text-center hover:border-emerald-200 transition-colors">
            <div class="text-3xl font-extrabold text-emerald-600"><?= $data['totalSet'] ?><span class="text-lg text-slate-400">/9</span></div>
            <div class="text-xs text-slate-500 font-semibold mt-1">👤 User ID Terisi</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-4 text-center hover:border-emerald-200 transition-colors">
            <div class="text-3xl font-extrabold text-green-500"><?= $data['totalOk'] ?></div>
            <div class="text-xs text-slate-500 font-semibold mt-1">✅ Test Berhasil</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-4 text-center hover:border-emerald-200 transition-colors">
            <div class="text-3xl font-extrabold text-red-500"><?= $data['totalErr'] ?></div>
            <div class="text-xs text-slate-500 font-semibold mt-1">⚠️ Test Gagal</div>
        </div>
    </div>

    <!-- ── ACTION BAR (Simpan & Test Semua) ─────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-5 relative overflow-hidden">
        <!-- Accent line atas -->
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-400 via-teal-400 to-emerald-600 rounded-t-2xl"></div>
        <form method="POST" id="formSaveAll">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-slate-800">💾 Simpan &amp; Uji Semua Kelas Sekaligus</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Simpan semua User ID atau kirim test notifikasi ke semua bot sekaligus.</p>
                </div>
                <div class="flex gap-2 overflow-x-auto pb-2 flex-nowrap hide-scrollbar">
                    <button type="submit" name="save_all"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold shadow-sm hover:from-emerald-600 hover:to-emerald-700 hover:shadow-emerald-200 hover:shadow-md transition-all whitespace-nowrap">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Semua
                    </button>
                    <button type="submit" name="test_all"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white text-xs font-bold shadow-sm hover:from-teal-600 hover:to-emerald-600 hover:shadow-teal-200 hover:shadow-md transition-all whitespace-nowrap">
                        <i class="fa-solid fa-paper-plane"></i> Test Semua Bot
                    </button>
                </div>
            </div>
            <!-- Hidden fields untuk nilai dari kartu -->
            <?php foreach ($data['bots'] as $bot):
                $key = 'uid_' . preg_replace('/\s+/', '_', $bot['nama_kelas']);
            ?>
            <input type="hidden" name="<?= $key ?>" id="hidden_<?= $key ?>">
            <?php endforeach; ?>
        </form>
    </div>

    <!-- ── BOT CARDS ─────────────────────────────────────────────────────── -->
    <div>
        <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Konfigurasi Per Kelas — User ID Wali Kelas</p>
        <div class="flex md:grid flex-nowrap md:flex-wrap md:grid-cols-2 xl:grid-cols-3 gap-4 overflow-x-auto pb-4 snap-x hide-scrollbar">
        <?php
        $jurusanColors = [
            'tsm' => ['bar' => 'from-orange-400 to-orange-500', 'tag' => 'bg-orange-50 text-orange-600 border-orange-200', 'dot' => 'bg-orange-400'],
            'tkr' => ['bar' => 'from-purple-400 to-purple-500',  'tag' => 'bg-purple-50 text-purple-600 border-purple-200',   'dot' => 'bg-purple-400'],
            'tkj' => ['bar' => 'from-teal-400 to-teal-500',     'tag' => 'bg-teal-50 text-teal-600 border-teal-200',         'dot' => 'bg-teal-400'],
        ];
        foreach ($data['bots'] as $bot):
            $nama    = $bot['nama_kelas'];
            $parts   = explode(' ', $nama);
            $tingkat = $parts[0];
            $jurusan = $parts[1] ?? '';
            $jLow    = strtolower($jurusan);
            $key     = 'uid_' . preg_replace('/\s+/', '_', $nama);
            $chatId  = $data['kelasData'][$nama]['chat_id'] ?? '';
            $res     = $data['results'][$nama] ?? null;
            $colors  = $jurusanColors[$jLow] ?? $jurusanColors['tkj'];

            // Border glow berdasarkan status
            $cardBorder = 'border-slate-100';
            if ($res && $res['ok'])       $cardBorder = 'border-green-200 shadow-green-50';
            elseif ($res && !$res['ok'])  $cardBorder = 'border-red-200 shadow-red-50';
            elseif (!empty($chatId))      $cardBorder = 'border-emerald-200';
        ?>
        <div class="bg-white rounded-2xl border <?= $cardBorder ?> shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden flex flex-col min-w-[85vw] sm:min-w-[320px] md:min-w-0 snap-center shrink-0">
            <!-- Color bar jurusan -->
            <div class="h-1 bg-gradient-to-r <?= $colors['bar'] ?>"></div>

            <div class="p-5 flex-1 flex flex-col">
                <!-- Head -->
                <div class="flex items-start justify-between mb-1">
                    <div class="text-lg font-extrabold text-slate-800 tracking-tight">Kelas <?= $nama ?></div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border <?= $colors['tag'] ?>">
                        <?= $jurusan ?>
                    </span>
                </div>
                <div class="text-xs text-slate-400 mb-3">🤖 <span class="text-emerald-600 font-semibold">@<?= $bot['username'] ?></span></div>

                <!-- Status badge -->
                <?php if ($res && $res['ok']): ?>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-green-600 bg-green-50 border border-green-200 rounded-full px-2.5 py-0.5 mb-3 self-start">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Notifikasi Terkirim
                    </span>
                <?php elseif ($res && !$res['ok']): ?>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-red-500 bg-red-50 border border-red-200 rounded-full px-2.5 py-0.5 mb-1 self-start">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span> Gagal Terkirim
                    </span>
                    <div class="text-[11px] font-mono text-red-400 bg-red-50 rounded-lg px-3 py-2 mb-3"><?= htmlspecialchars($res['description'] ?? '') ?></div>
                <?php elseif (!empty($chatId)): ?>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-0.5 mb-3 self-start">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> User ID Tersimpan
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-400 bg-slate-50 border border-slate-200 rounded-full px-2.5 py-0.5 mb-3 self-start">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 inline-block"></span> Belum Dikonfigurasi
                    </span>
                <?php endif; ?>

                <!-- UID Input + Save -->
                <div class="flex gap-2 items-stretch mb-3">
                    <div class="flex-1 flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100 transition-all">
                        <span class="px-3 text-base text-slate-400 shrink-0"><i class="fa-solid fa-user"></i></span>
                        <input
                            type="text"
                            id="inp_<?= $key ?>"
                            placeholder="Masukkan User ID Telegram..."
                            value="<?= htmlspecialchars($chatId) ?>"
                            pattern="[0-9]+"
                            oninput="syncHidden('<?= $key ?>', this.value)"
                            class="flex-1 bg-transparent border-none outline-none py-2.5 pr-3 text-sm font-semibold text-slate-700 placeholder-slate-300"
                        >
                    </div>
                    <form method="POST" class="flex">
                        <input type="hidden" name="kelas_name" value="<?= $nama ?>">
                        <input type="hidden" name="single_uid" id="submit_uid_<?= $key ?>">
                        <button
                            type="submit"
                            name="save_single"
                            onclick="document.getElementById('submit_uid_<?= $key ?>').value = document.getElementById('inp_<?= $key ?>').value"
                            class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold shadow-sm hover:from-emerald-600 hover:to-emerald-700 transition-all whitespace-nowrap"
                            title="Simpan User ID kelas ini">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
                        </button>
                    </form>
                </div>

                <!-- Aksi: Buka Bot & Test -->
                <div class="grid grid-cols-2 gap-2 mt-auto">
                    <a href="https://t.me/<?= $bot['username'] ?>?start=aktif" target="_blank"
                        class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold hover:bg-emerald-100 hover:border-emerald-300 transition-all">
                        📱 Buka &amp; /Start
                    </a>
                    <form method="POST">
                        <input type="hidden" name="kelas_name" value="<?= $nama ?>">
                        <button type="submit" name="test_single"
                            onclick="this.textContent='⏳...'"
                            class="w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-teal-50 border border-teal-200 text-teal-700 text-xs font-bold hover:bg-teal-100 hover:border-teal-300 transition-all cursor-pointer">
                            📤 Test Kirim
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer kartu -->
            <div class="px-5 py-3 border-t border-slate-50 bg-slate-50/50 flex items-center justify-between text-xs text-slate-400">
                <span>Chat ID:</span>
                <?php if (!empty($chatId)): ?>
                    <span class="font-mono font-semibold text-teal-600"><?= htmlspecialchars($chatId) ?></span>
                <?php else: ?>
                    <span class="italic">— belum diisi</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── STATUS DATABASE TABLE ─────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-50">
            <h3 class="text-sm font-bold text-slate-800">📋 Status Database Semua Kelas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kelas</th>
                        <th class="text-left px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Token Bot</th>
                        <th class="text-left px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">User ID</th>
                        <th class="text-left px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                <?php
                $dotColors = ['tsm' => 'bg-orange-400', 'tkr' => 'bg-purple-400', 'tkj' => 'bg-teal-400'];
                foreach ($data['bots'] as $bot):
                    $nama   = $bot['nama_kelas'];
                    $chatId = $data['kelasData'][$nama]['chat_id'] ?? '';
                    $dbId   = $data['kelasData'][$nama]['db_id'] ?? null;
                    $res    = $data['results'][$nama] ?? null;
                    $parts  = explode(' ', $nama);
                    $jLow   = strtolower($parts[1] ?? 'tkj');
                    $dotCls = $dotColors[$jLow] ?? 'bg-slate-400';
                ?>
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-3 font-bold text-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full <?= $dotCls ?> inline-block"></span>
                            <?= $nama ?>
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <?php if ($dbId): ?>
                            <span class="text-green-600 font-semibold text-xs">✅ Terpasang</span>
                        <?php else: ?>
                            <span class="text-red-500 font-semibold text-xs">❌ Tidak Ada</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-3 font-mono text-xs">
                        <?php if (!empty($chatId)): ?>
                            <span class="text-teal-600 font-semibold"><?= htmlspecialchars($chatId) ?></span>
                        <?php else: ?>
                            <span class="italic text-slate-300">— belum diisi</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-3">
                        <?php if ($res && $res['ok']): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 border border-green-200 rounded-full px-2.5 py-0.5">✅ Aktif</span>
                        <?php elseif ($res && !$res['ok']): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-red-500 bg-red-50 border border-red-200 rounded-full px-2.5 py-0.5">❌ Gagal</span>
                        <?php elseif (!empty($chatId)): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 rounded-full px-2.5 py-0.5">⏳ Belum Diuji</span>
                        <?php else: ?>
                            <span class="text-xs text-slate-300 italic">— Belum diset</span>
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
