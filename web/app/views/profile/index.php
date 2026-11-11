<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6">
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Profil Saya</h2>
        <p class="text-slate-500 text-sm mt-1">Kelola informasi profil dan keamanan akun Anda.</p>
    </div>

    <?php Flasher::flash(); ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <form action="<?= BASEURL; ?>/profile/update" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            <?= CsrfHelper::input() ?>
            
            <div class="flex flex-col sm:flex-row gap-8">
                <!-- Kiri: Foto Profil -->
                <div class="flex-shrink-0 flex flex-col items-center">
                    <div class="w-32 h-32 rounded-full bg-slate-100 border-4 border-white shadow-md overflow-hidden relative group">
                        <?php 
                        $uAv = $data['user']->avatar ?? '';
                        $isUAvDataUri = (!empty($uAv) && strpos($uAv, 'data:image/') === 0);
                        $uAvSrc = $isUAvDataUri ? $uAv : (BASEURL . '/uploads/avatars/' . htmlspecialchars($uAv));
                        ?>
                        <?php if(!empty($uAv)): ?>
                            <img src="<?= $uAvSrc ?>" alt="Avatar" id="preview-avatar" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div id="preview-avatar-placeholder" class="w-full h-full flex items-center justify-center bg-emerald-100 text-emerald-600 font-bold text-4xl">
                                <?= strtoupper(substr($data['user']->name, 0, 1)) ?>
                            </div>
                            <img src="" alt="Avatar" id="preview-avatar" class="w-full h-full object-cover hidden">
                        <?php endif; ?>
                        
                        <label for="avatar-upload" class="absolute inset-0 bg-slate-900/50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-white">
                            <i class="fa-solid fa-camera text-xl mb-1"></i>
                            <span class="text-xs font-medium">Ubah Foto</span>
                        </label>
                        <input type="file" id="avatar-upload" name="avatar" accept="image/jpeg, image/png, image/webp" class="hidden" onchange="previewImage(this)">
                    </div>
                    <p class="text-xs text-slate-400 mt-3 text-center w-32">Maksimal 2MB<br>(JPG, PNG, WEBP)</p>
                </div>

                <!-- Kanan: Form Data -->
                <div class="flex-1 space-y-5">
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($data['user']->name) ?>" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
                        <input type="text" value="<?= htmlspecialchars($data['user']->username) ?>" disabled
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed">
                        <p class="text-xs text-slate-400 mt-1">Username digunakan untuk login dan tidak dapat diubah.</p>
                    </div>

                    <?php if (in_array($data['user']->role, ['guru', 'wali_kelas', 'walikelas', 'kepsek']) && $data['profileData']): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">NIP</label>
                                <input type="text" name="nip" value="<?= htmlspecialchars($data['profileData']->nip ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Barcode ID</label>
                                <div class="relative">
                                    <input type="text" id="profileBarcodeIdInput" value="<?= htmlspecialchars($data['profileData']->barcode_id ?? '') ?>" disabled class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-sm cursor-not-allowed font-mono font-bold pr-10">
                                    <button type="button" onclick="copyBarcodeId()" class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-emerald-600 transition-colors" title="Salin Barcode ID">
                                        <i class="fa-regular fa-copy text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($data['user']->role === 'siswa' && $data['profileData']): ?>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">NIS</label>
                                <input type="text" value="<?= htmlspecialchars($data['profileData']->nis ?? '') ?>" disabled class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Kelas</label>
                                <input type="text" value="<?= htmlspecialchars($data['profileData']->nama_kelas ?? '') ?>" disabled class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed">
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr class="border-slate-100 my-6">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Ubah Password</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm">
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan Perubahan
                        </button>
                    </div>

                </div>
            </div>

        </form>
    </div>

    <?php if (in_array($data['user']->role, ['guru', 'wali_kelas', 'walikelas', 'kepsek']) && !empty($data['profileData']->barcode_id)): ?>
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <!-- KARTU QR CODE & BARCODE PRESENSI GURU (100% SINKRON DENGAN SISTEM) -->
    <!-- ═══════════════════════════════════════════════════════════════════════ -->
    <div class="mt-8 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 mb-2">
                        <i class="fa-solid fa-circle-check text-[10px]"></i> Barcode Presensi Aktif
                    </span>
                    <h3 class="text-lg font-bold text-slate-800">Kartu QR Code Presensi Guru</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Gunakan QR Code ini saat presensi masuk & pulang di kamera scanner atau monitor guru.</p>
                </div>
                <div class="flex items-center gap-2">
                    <form action="<?= BASEURL; ?>/profile/syncBarcode" method="POST" onsubmit="return confirm('Sinkronkan ulang Barcode ID Anda dengan sistem?')">
                        <?= CsrfHelper::input() ?>
                        <button type="submit" class="px-3.5 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all flex items-center gap-1.5" title="Sinkronkan Barcode ID jika terjadi kendala">
                            <i class="fa-solid fa-arrows-rotate text-emerald-600"></i> Sinkronkan Barcode
                        </button>
                    </form>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="mt-6 flex flex-col md:flex-row items-center gap-8 bg-gradient-to-br from-slate-50 to-emerald-50/30 p-6 rounded-2xl border border-slate-200/80" id="teacher-id-card">
                <!-- Sisi QR Code -->
                <div class="flex flex-col items-center flex-shrink-0 bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
                    <div id="profile-qrcode" class="flex items-center justify-center"></div>
                    <div class="mt-3 flex items-center gap-1.5 bg-slate-50 px-3 py-1 rounded-lg border border-slate-200">
                        <span class="font-mono text-xs font-bold text-slate-800" id="barcode-text"><?= htmlspecialchars($data['profileData']->barcode_id) ?></span>
                        <button type="button" onclick="copyBarcodeId()" class="text-slate-400 hover:text-emerald-600 transition-colors ml-1" title="Salin Barcode ID">
                            <i class="fa-regular fa-copy text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Sisi Informasi Identitas Guru -->
                <div class="flex-1 text-center md:text-left space-y-3">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700">SMK NU WARGABINANGUN</span>
                        <h4 class="text-xl font-extrabold text-slate-800 mt-0.5 leading-snug"><?= htmlspecialchars($data['user']->name) ?></h4>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs pt-1">
                        <div class="bg-white/80 p-2.5 rounded-xl border border-slate-200/60">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">NIP</span>
                            <span class="font-semibold text-slate-800 font-mono"><?= htmlspecialchars($data['profileData']->nip ?? '-') ?></span>
                        </div>
                        <div class="bg-white/80 p-2.5 rounded-xl border border-slate-200/60">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Peran / Jabatan</span>
                            <span class="font-semibold text-slate-800"><?= ucfirst(str_replace('_', ' ', $data['user']->role)) ?></span>
                        </div>
                        <?php if (!empty($data['profileData']->subject)): ?>
                        <div class="bg-white/80 p-2.5 rounded-xl border border-slate-200/60 sm:col-span-2">
                            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Mata Pelajaran</span>
                            <span class="font-semibold text-slate-800"><?= htmlspecialchars($data['profileData']->subject) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tombol Aksi Cetak & Download -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5 pt-2">
                        <button type="button" onclick="printTeacherQrCard()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-2">
                            <i class="fa-solid fa-print"></i> Cetak Kartu QR
                        </button>
                        <button type="button" onclick="downloadTeacherQrImage()" class="px-4 py-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-all flex items-center gap-2">
                            <i class="fa-solid fa-download text-emerald-600"></i> Simpan Gambar QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        if(input.files[0].size > 2 * 1024 * 1024){
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            input.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('preview-avatar');
            var placeholder = document.getElementById('preview-avatar-placeholder');
            img.src = e.target.result;
            img.classList.remove('hidden');
            if(placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function copyBarcodeId() {
    var barcode = "<?= htmlspecialchars($data['profileData']->barcode_id ?? '', ENT_QUOTES) ?>";
    if (!barcode) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(barcode).then(function() {
            alert('Barcode ID disalin: ' + barcode);
        });
    } else {
        var tempInput = document.createElement('input');
        tempInput.value = barcode;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alert('Barcode ID disalin: ' + barcode);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var qrContainer = document.getElementById('profile-qrcode');
    var barcodeValue = "<?= htmlspecialchars($data['profileData']->barcode_id ?? '', ENT_QUOTES) ?>";
    if (qrContainer && barcodeValue && typeof QRCode !== 'undefined') {
        qrContainer.innerHTML = '';
        new QRCode(qrContainer, {
            text: barcodeValue,
            width: 150,
            height: 150,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }
});

function downloadTeacherQrImage() {
    var container = document.getElementById('profile-qrcode');
    if (!container) return;
    var img = container.querySelector('img');
    var canvas = container.querySelector('canvas');
    var dataUrl = null;
    if (img && img.src && img.src.indexOf('data:image') === 0) {
        dataUrl = img.src;
    } else if (canvas) {
        dataUrl = canvas.toDataURL('image/png');
    }
    if (dataUrl) {
        var a = document.createElement('a');
        a.href = dataUrl;
        a.download = "QR-Presensi-<?= htmlspecialchars($data['profileData']->barcode_id ?? 'Guru', ENT_QUOTES) ?>.png";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    } else {
        alert('Gambar QR belum siap diunduh.');
    }
}

function printTeacherQrCard() {
    var name = "<?= htmlspecialchars(addslashes($data['user']->name ?? 'Guru')) ?>";
    var nip = "<?= htmlspecialchars(addslashes($data['profileData']->nip ?? '-')) ?>";
    var barcode = "<?= htmlspecialchars(addslashes($data['profileData']->barcode_id ?? '')) ?>";
    var qrContainer = document.getElementById('profile-qrcode');
    if (!qrContainer) return;

    var qrHtml = qrContainer.innerHTML;
    var w = window.open('', '_blank');
    w.document.write('<html><head><title>Kartu Presensi - ' + name + '</title>');
    w.document.write('<style>');
    w.document.write('body{font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:90vh;margin:0;background:#f8fafc;}');
    w.document.write('.card{width:320px;background:#fff;border:2px solid #0f7a4a;border-radius:16px;padding:24px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08);}');
    w.document.write('.header{border-bottom:2px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px;}');
    w.document.write('.school{font-size:11px;font-weight:bold;color:#0f7a4a;letter-spacing:1px;text-transform:uppercase;}');
    w.document.write('.title{font-size:15px;font-weight:bold;color:#1e293b;margin:4px 0 0;}');
    w.document.write('.name{font-size:16px;font-weight:bold;color:#0f172a;margin:12px 0 4px;}');
    w.document.write('.nip{font-size:12px;color:#64748b;font-family:monospace;margin-bottom:16px;}');
    w.document.write('.qr-wrap{display:inline-block;padding:12px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;}');
    w.document.write('.qr-wrap img, .qr-wrap canvas{display:block;margin:0 auto;}');
    w.document.write('.barcode-code{font-family:monospace;font-size:13px;font-weight:bold;color:#0f7a4a;margin-top:12px;letter-spacing:1px;}');
    w.document.write('.footer{margin-top:16px;font-size:10px;color:#94a3b8;border-top:1px dashed #cbd5e1;padding-top:8px;}');
    w.document.write('@media print{body{background:#fff;}.card{box-shadow:none;border-color:#333;}}');
    w.document.write('</style></head><body>');
    w.document.write('<div class="card">');
    w.document.write('<div class="header">');
    w.document.write('<div class="school">SMK NU WARGABINANGUN</div>');
    w.document.write('<div class="title">KARTU PRESENSI GURU</div>');
    w.document.write('</div>');
    w.document.write('<div class="name">' + name + '</div>');
    w.document.write('<div class="nip">NIP: ' + nip + '</div>');
    w.document.write('<div class="qr-wrap">' + qrHtml + '</div>');
    w.document.write('<div class="barcode-code">' + barcode + '</div>');
    w.document.write('<div class="footer">Scan kartu ini pada mesin presensi atau kamera scanner</div>');
    w.document.write('</div>');
    w.document.write('</body></html>');
    w.document.close();

    setTimeout(function() {
        w.print();
    }, 400);
}
</script>
