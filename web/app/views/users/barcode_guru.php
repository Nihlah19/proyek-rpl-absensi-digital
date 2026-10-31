<!-- Halaman Cetak Barcode Guru -->
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASEURL ?>/users"
           class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-slate-800">
                <i class="fa-solid fa-qrcode text-primary mr-2"></i>QR Code Guru
            </h2>
            <p class="text-slate-500 text-sm mt-1">Cetak QR Code untuk absensi guru</p>
        </div>

        <!-- Info Guru -->
        <div class="bg-slate-50 rounded-xl p-4 mb-6">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-slate-500">Nama:</span>
                    <span class="font-semibold text-slate-800 ml-1"><?= htmlspecialchars($data['guru']->name) ?></span>
                </div>
                <div>
                    <span class="text-slate-500">NIP:</span>
                    <span class="font-semibold text-slate-800 ml-1"><?= htmlspecialchars($data['guru']->nip) ?></span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-500">Barcode ID:</span>
                    <code class="bg-white text-slate-700 px-2 py-0.5 rounded text-xs border border-slate-200 ml-1"><?= htmlspecialchars($data['guru']->barcode_id ?? '-') ?></code>
                </div>
            </div>
        </div>

        <!-- Barcode Display -->
        <div class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-slate-200 rounded-xl mb-6" id="barcode-container">
            <?php if (!empty($data['guru']->barcode_id)): ?>
                <!-- Tempat QR Code -->
                <div id="qrcode" class="p-2 bg-white rounded-xl shadow-sm border border-slate-100 flex justify-center"></div>
                <p class="text-xs text-slate-500 mt-4 font-mono font-semibold tracking-wider text-center"><?= htmlspecialchars($data['guru']->barcode_id) ?></p>
            <?php else: ?>
                <div class="text-center text-slate-400 py-8">
                    <i class="fa-solid fa-triangle-exclamation text-3xl mb-3 block text-amber-400"></i>
                    <p class="text-sm">Barcode ID belum diatur untuk guru ini.</p>
                    <p class="text-xs mt-1">Silakan edit data guru terlebih dahulu.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex gap-3 justify-center">
            <?php if (!empty($data['guru']->barcode_id)): ?>
            <button onclick="printBarcode()"
                    class="inline-flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors shadow-sm">
                <i class="fa-solid fa-print"></i> Cetak QR Code
            </button>
            <?php endif; ?>
            <a href="<?= BASEURL ?>/users"
               class="inline-flex items-center gap-2 border border-slate-200 text-slate-600 px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- QRCode Library -->
<script src="<?= BASEURL; ?>/assets/vendor/qrcodejs/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('qrcode');
    if (container) {
        new QRCode(container, {
            text: "<?= htmlspecialchars($data['guru']->barcode_id ?? '', ENT_QUOTES) ?>",
            width: 150,
            height: 150,
            colorDark : "#0f172a", // slate-900
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }
});

function printBarcode() {
    var container = document.getElementById('barcode-container');
    var name = "<?= htmlspecialchars(addslashes($data['guru']->name ?? '')) ?>";
    var nip = "<?= htmlspecialchars(addslashes($data['guru']->nip ?? '')) ?>";

    var w = window.open('', '_blank');
    w.document.write('<html><head><title>QR Code - ' + name + '</title>');
    w.document.write('<style>body{font-family:Arial,sans-serif;text-align:center;padding:40px;}');
    w.document.write('.info{margin-bottom:20px;font-size:14px;}.label{color:#666;}.value{font-weight:bold;}');
    w.document.write('.barcode-id{font-family:monospace;font-size:12px;color:#555;margin-top:8px;}');
    w.document.write('#qrcode img{margin:0 auto;}'); // Centering the QR Code image
    w.document.write('@media print{body{padding:20px;}}</style></head><body>');
    w.document.write('<div class="info"><span class="label">Nama: </span><span class="value">' + name + '</span></div>');
    w.document.write('<div class="info"><span class="label">NIP: </span><span class="value">' + nip + '</span></div>');
    w.document.write(container.innerHTML);
    w.document.write('</body></html>');
    w.document.close();
    
    // Tunggu image load selesai sebelum print
    setTimeout(function() {
        w.print();
    }, 500);
}
</script>
