<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <title><?= $data['judul'] ?? '404 - Halaman Tidak Ditemukan' ?> · SMK NU Wargabinangun</title>
    <link rel="icon" type="image/png" href="<?= BASEURL; ?>/assets/img/logo.png?v=3">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/tailwind.css?v=3.0">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fonts/plus-jakarta-sans/plus-jakarta-sans.css?v=1">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fontawesome/css/all.min.css?v=6.5">
</head>
<body class="bg-[#f8faf7] text-[#0e0f0c] min-h-screen flex flex-col overflow-x-hidden font-['Plus_Jakarta_Sans',sans-serif] antialiased">
    <!-- Header Minimalis -->
    <header class="w-full bg-white border-b border-[#e8ebe6] py-3.5 px-6">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="<?= BASEURL; ?>" class="flex items-center gap-3">
                <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" alt="Logo SMK" class="h-9 w-9 object-contain">
                <div>
                    <span class="block font-extrabold text-[#163300] text-sm tracking-tight leading-tight">SMK NU WARGABINANGUN</span>
                    <span class="block text-[11px] font-semibold text-[#64748b]">Sistem Presensi RFID &amp; AI</span>
                </div>
            </a>
            <a href="<?= BASEURL; ?>/auth/login" class="px-4 py-2 rounded-full border border-[#163300] text-[#163300] hover:bg-[#163300] hover:text-[#9fe870] text-xs font-bold transition-all">
                Masuk Portal →
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="max-w-md w-full text-center bg-white rounded-2xl p-8 border border-[#e8ebe6] shadow-[0_4px_24px_rgba(0,0,0,0.04)]">
            <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-[#e2f6d5] flex items-center justify-center text-[#163300] text-2xl font-black">
                404
            </div>
            <h1 class="text-2xl font-black text-[#163300] mb-2">Halaman Tidak Ditemukan</h1>
            <p class="text-sm text-[#454745] leading-relaxed mb-6">
                Tautan yang Anda tuju mungkin sudah kedaluwarsa, salah ketik, atau telah dipindahkan ke alamat baru.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="<?= BASEURL; ?>" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-[#9fe870] text-[#163300] font-bold text-xs hover:bg-[#8ee05d] transition-all shadow-sm">
                    <i class="fa-solid fa-house text-xs"></i> Kembali ke Beranda
                </a>
                <a href="<?= BASEURL; ?>/auth/login" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full border border-[#163300] text-[#163300] font-bold text-xs hover:bg-slate-50 transition-all">
                    <i class="fa-solid fa-right-to-bracket text-xs"></i> Portal Login
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-5 px-6 border-t border-[#e8ebe6] bg-white text-center text-xs text-[#64748b]">
        &copy; 2026 <strong class="text-[#163300]">SMK NU Wargabinangun</strong>. All Rights Reserved.
    </footer>
</body>
</html>
