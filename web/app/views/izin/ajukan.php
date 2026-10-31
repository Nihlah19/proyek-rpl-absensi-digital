<div id="pageContent" class="w-full max-w-7xl mx-auto px-6 sm:px-8 lg:px-12 py-10">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Buat Pengajuan Baru</h1>
        <a href="<?= BASEURL; ?>/izin" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php Flasher::flash(); ?>

    <!-- Form Container Center -->
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-100 bg-gradient-to-r from-primary/5 to-secondary/5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <i class="fa-solid fa-paste text-primary"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Form Pengajuan</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Isi data berikut untuk mengajukan izin atau sakit</p>
                    </div>
                </div>
            </div>
            <div class="p-8 space-y-8">
                <form action="<?= BASEURL; ?>/izin/ajukan" method="post" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= CsrfHelper::generate(); ?>">

                    <!-- Tanggal -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-3">Rentang Tanggal</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="tanggal_mulai" class="block text-xs text-slate-500 mb-1.5">Mulai</label>
                                <input type="date" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                       id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                            <div>
                                <label for="tanggal_selesai" class="block text-xs text-slate-500 mb-1.5">Selesai</label>
                                <input type="date" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                       id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>

                    <!-- Jenis -->
                    <div>
                        <label for="jenis" class="block text-sm font-medium text-slate-700 mb-1.5">Jenis <span class="text-danger">*</span></label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="jenis" value="sakit" class="peer hidden" required>
                                <div class="flex items-center gap-3 p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-danger peer-checked:bg-danger/5 hover:border-slate-300 transition-all">
                                    <div class="w-10 h-10 rounded-lg bg-danger/10 flex items-center justify-center peer-checked:bg-danger/20 transition-colors">
                                        <i class="fa-solid fa-notes-medical text-danger"></i>
                                    </div>
                                    <span class="text-sm font-medium text-slate-700 peer-checked:text-danger">Sakit</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="jenis" value="izin" class="peer hidden">
                                <div class="flex items-center gap-3 p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-info peer-checked:bg-info/5 hover:border-slate-300 transition-all">
                                    <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center peer-checked:bg-info/20 transition-colors">
                                        <i class="fa-solid fa-passport text-info"></i>
                                    </div>
                                    <span class="text-sm font-medium text-slate-700 peer-checked:text-info">Izin</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Alasan -->
                    <div>
                        <label for="alasan" class="block text-sm font-medium text-slate-700 mb-1.5">Alasan Singkat <span class="text-danger">*</span></label>
                        <input type="text" class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                               id="alasan" name="alasan" placeholder="Misal: Sakit Demam, Acara Keluarga" required>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan Tambahan</label>
                        <textarea class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary resize-y"
                                  id="keterangan" name="keterangan" rows="3"
                                  placeholder="Penjelasan lebih detail..."></textarea>
                    </div>

                    <!-- File Bukti -->
                    <div>
                        <label for="file_bukti" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Upload Surat/Bukti <span class="text-rose-500 font-normal">(Wajib disertakan)</span>
                        </label>
                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center hover:border-primary/40 transition-colors cursor-pointer" onclick="document.getElementById('file_bukti').click()">
                            <i class="fa-solid fa-cloud-upload-alt text-4xl text-slate-300 mb-3"></i>
                            <p class="text-sm text-slate-500 font-medium">Klik untuk memilih file</p>
                            <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, PDF. Maksimal 2MB</p>
                            <input type="file" class="hidden" id="file_bukti" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf" onchange="updateFileName(this)" required>
                        </div>
                        <p id="file-name" class="mt-2 text-xs text-primary font-medium hidden"></p>
                    </div>

                    <div class="flex justify-end pt-6 border-t border-slate-100">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary to-primary-dark text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    var fileName = document.getElementById('file-name');
    if (input.files && input.files.length > 0) {
        fileName.textContent = 'File dipilih: ' + input.files[0].name;
        fileName.classList.remove('hidden');
    } else {
        fileName.classList.add('hidden');
    }
}
</script>
