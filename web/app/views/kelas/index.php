<div class="w-full space-y-6">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xs border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-slate-50/70 dark:bg-slate-800/80">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white">Konfigurasi Telegram</h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Isi Chat ID grup kelas agar bot Telegram dapat mengirimkan notifikasi absensi ke grup masing-masing kelas.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="document.getElementById('modalPanduan').classList.remove('hidden')" class="vh-btn-secondary text-xs sm:text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-emerald-600 dark:text-emerald-400"></i>
                    <span>Panduan Chat ID</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-xs sm:text-sm text-left text-slate-600 dark:text-slate-300">
                <thead class="text-xs text-slate-500 dark:text-slate-400 uppercase bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-center w-12">No</th>
                        <th class="px-5 py-3.5 font-semibold">Kode Kelas</th>
                        <th class="px-5 py-3.5 font-semibold">Nama Kelas</th>
                        <th class="px-5 py-3.5 font-semibold">Telegram Chat ID</th>
                        <th class="px-5 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php $no = 1; foreach ($data['kelas'] as $k): ?>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                        <td class="px-5 py-3.5 text-center font-mono text-slate-400 dark:text-slate-500"><?= $no++; ?></td>
                        <td class="px-5 py-3.5 font-mono font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($k->class_code); ?></td>
                        <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($k->nama_kelas); ?></td>
                        <td class="px-5 py-3.5 font-mono">
                            <?php if($k->telegram_chat_id): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                    <?= htmlspecialchars($k->telegram_chat_id); ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
                                    Belum Diatur
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <?php if(isset($k->status) && $k->status === 'pkl'): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50">
                                    PKL
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                                    Aktif
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <button onclick="editKelas(<?= $k->id; ?>, '<?= htmlspecialchars(addslashes($k->nama_kelas)); ?>', '<?= htmlspecialchars(addslashes($k->telegram_chat_id ?? '')); ?>', '<?= htmlspecialchars(addslashes($k->status ?? 'aktif')); ?>')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white transition-all shadow-xs cursor-pointer" title="Edit">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col border border-slate-200 dark:border-slate-700 animate-fade-in">
        <form action="<?= BASEURL; ?>/kelas/update" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 flex items-center justify-between shrink-0">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Edit Kelas <span id="nama_kelas_label" class="text-emerald-600 dark:text-emerald-400"></span></h3>
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div class="p-5 space-y-4 overflow-y-auto flex-1 text-xs sm:text-sm">
                <input type="hidden" name="id" id="kelas_id">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" id="telegram_chat_id" class="vh-input w-full px-3 py-2 text-xs sm:text-sm font-mono" placeholder="-100123456789">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Awali dengan tanda minus (-) jika berupa grup atau channel.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Status Kelas</label>
                    <select name="status" id="status_kelas" class="vh-input w-full px-3 py-2 text-xs sm:text-sm">
                        <option value="aktif">Aktif (Wajib Absen)</option>
                        <option value="pkl">PKL (Praktik Kerja Lapangan)</option>
                    </select>
                </div>
            </div>
            <div class="p-4 sm:p-5 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 flex justify-end gap-2.5 shrink-0">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="vh-btn-secondary text-xs sm:text-sm">Batal</button>
                <button type="submit" class="vh-btn-primary text-xs sm:text-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Panduan -->
<div id="modalPanduan" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col border border-slate-200 dark:border-slate-700 animate-fade-in">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 flex justify-between items-center shrink-0">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Panduan Mendapatkan Chat ID Grup Telegram</h3>
            <button onclick="document.getElementById('modalPanduan').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        <div class="p-5 sm:p-6 space-y-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed overflow-y-auto flex-1">
            <p>Untuk dapat mengirimkan notifikasi absensi ke grup Telegram kelas masing-masing, ikuti langkah berikut:</p>
            <ol class="list-decimal pl-5 space-y-2">
                <li>Buka aplikasi Telegram dan buat/buka Grup Kelas yang bersangkutan.</li>
                <li>Tambahkan Bot yang sesuai (contoh: <b>@walisiswatsm10bot</b>) ke dalam Grup Kelas tersebut.</li>
                <li>Jadikan Bot tersebut sebagai <b>Admin</b> di dalam grup agar dapat mengirim pesan.</li>
                <li>Kirim pesan sembarang (misalnya: <code>/start</code> atau teks lainnya) di dalam grup.</li>
                <li>Kunjungi tautan berikut di browser Anda dengan memasukkan Token Bot kelas bersangkutan:<br>
                <code class="block bg-slate-100 dark:bg-slate-900 p-2 rounded mt-1 break-all text-emerald-700 dark:text-emerald-400 font-mono text-xs">https://api.telegram.org/bot&lt;TOKEN_BOT&gt;/getUpdates</code>
                </li>
                <li>Cari teks yang menampilkan <code>"chat":{"id":-100XXXXXXXXXX}</code>. Angka yang diawali dengan tanda minus tersebut adalah <b>Chat ID</b> grup Anda.</li>
                <li>Salin Chat ID tersebut dan masukkan ke dalam form Edit Chat ID di sebelah.</li>
            </ol>
            <div class="mt-4 p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/70 dark:border-amber-800/40 rounded-xl flex gap-3 text-amber-800 dark:text-amber-300 text-xs">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0 text-sm"></i>
                <p><b>Catatan Penting:</b> Pastikan Anda mengisi Chat ID untuk grup yang benar dengan bot yang benar pula (masing-masing kelas memiliki bot dan Chat ID yang berbeda).</p>
            </div>
        </div>
        <div class="p-4 sm:p-5 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 flex justify-end shrink-0">
            <button type="button" onclick="document.getElementById('modalPanduan').classList.add('hidden')" class="vh-btn-secondary text-xs sm:text-sm">Tutup</button>
        </div>
    </div>
</div>

<script>
function editKelas(id, namaKelas, currentChatId, status) {
    document.getElementById('kelas_id').value = id;
    document.getElementById('nama_kelas_label').innerText = namaKelas;
    document.getElementById('telegram_chat_id').value = currentChatId;
    document.getElementById('status_kelas').value = status;
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>
