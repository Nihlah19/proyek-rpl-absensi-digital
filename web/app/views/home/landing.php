<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Presensi Sekolah Terpadu SMK NU Wargabinangun berbasis IoT RFID RC522 Siswa, QR Barcode Guru, Multi-Channel WhatsApp & Telegram, dan Asisten AI Google Gemini.">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <title><?= $data['judul'] ?? 'Sistem Presensi RFID Siswa & QR Guru — SMK NU Wargabinangun' ?></title>

    <!-- PWA Settings & Favicons -->
    <link rel="manifest" href="<?= BASEURL; ?>/manifest.json">
    <meta name="theme-color" content="#163300">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Presensi RFID">
    <link rel="apple-touch-icon" href="<?= BASEURL; ?>/assets/img/apple-touch-icon.png?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASEURL; ?>/assets/img/icon-192.png?v=3">
    <link rel="shortcut icon" href="<?= BASEURL; ?>/assets/img/logo.png?v=3" type="image/png">

    <!-- Typography & Offline-Ready Local Assets (Zero Render-Blocking External CDNs) -->
    <link rel="preload" href="<?= BASEURL; ?>/assets/css/tailwind.css?v=3.0" as="style">
    <link rel="preload" href="<?= BASEURL; ?>/assets/vendor/fonts/plus-jakarta-sans/pjs-4.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= BASEURL; ?>/assets/vendor/fontawesome/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Compiled Offline Tailwind CSS + Local WebFonts & Icons -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/tailwind.css?v=3.0">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fonts/plus-jakarta-sans/plus-jakarta-sans.css?v=1">
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fontawesome/css/all.min.css?v=6.5">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        /* Wise Design System Core Tokens (Instant First-Paint Fallback) */
        .bg-forest { background-color: #163300; }
        .text-forest { color: #163300; }
        .border-forest { border-color: #163300; }
        .bg-lime { background-color: #9fe870; }
        .text-lime { color: #9fe870; }
        .border-lime { border-color: #9fe870; }
        .bg-spruce { background-color: #054d28; }
        .text-spruce { color: #054d28; }
        .bg-linen { background-color: #e2f6d5; }
        .text-charcoal { color: #454745; }
        .text-obsidian { color: #0e0f0c; }
        .bg-obsidian { background-color: #0e0f0c; }
        .text-pebble { color: #868685; }
        .text-slate { color: #6a6c6a; }
        .bg-fog { background-color: #e8ebe6; }
        .border-fog { border-color: #e8ebe6; }
        .bg-paper { background-color: #ffffff; }
        .rounded-pill { border-radius: 9999px; }
        .rounded-card { border-radius: 10px; }
        .rounded-largeCard { border-radius: 28px; }
        
        html, body {
            scroll-behavior: smooth;
            overflow-x: hidden;
            max-width: 100%;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #ffffff;
            color: #454745;
        }

        section, main {
            scroll-margin-top: 5rem;
            min-width: 0;
        }

        #fitur, #alur-kerja, #akses-peran, footer {
            content-visibility: auto;
            contain-intrinsic-size: 1px 650px;
        }

        /* Wise Sans Signature Shout: Ultra-heavy blocky display type */
        .wise-headline {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 900;
            letter-spacing: -0.035em;
            line-height: 0.92;
            color: #0e0f0c;
        }

        .wise-headline-dark {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 900;
            letter-spacing: -0.035em;
            line-height: 0.92;
            color: #9fe870;
        }

        /* Pill component helper with smooth tactile response */
        .btn-pill-lime {
            background-color: #9fe870;
            color: #163300;
            font-weight: 700;
            border-radius: 9999px;
            padding: 12px 26px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            position: relative;
        }
        .btn-pill-lime:hover {
            background-color: #8ee05c;
            transform: translateY(-2px);
            box-shadow: 0 10px 24px -4px rgba(159, 232, 112, 0.5);
        }
        .btn-pill-lime:active {
            transform: scale(0.97) translateY(0);
        }
        .btn-pill-lime i {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-pill-lime:hover i {
            transform: translateX(3px);
        }

        .btn-pill-outline {
            background-color: #ffffff;
            color: #163300;
            border: 1px solid #163300;
            font-weight: 700;
            border-radius: 9999px;
            padding: 12px 24px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }
        .btn-pill-outline:hover {
            background-color: #163300;
            color: #9fe870;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(22, 51, 0, 0.2);
        }
        .btn-pill-outline:active {
            transform: scale(0.97) translateY(0);
        }

        /* Flat elevation */
        .wise-card-shadow {
            box-shadow: rgba(0, 0, 0, 0.08) 0px 6px 20px 0px;
        }

        /* ── SMOOTH SCROLL REVEAL ANIMATIONS ── */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal-on-scroll.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger Delays */
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }
        .delay-500 { transition-delay: 500ms; }

        /* ── INTERACTIVE CARD HOVER LIFT ── */
        .wise-card-hover {
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
        }
        .wise-card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 32px -8px rgba(22, 51, 0, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04);
            border-color: #9fe870;
        }

        /* Dark Card Hover Lift */
        .wise-card-hover-dark {
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
        }
        .wise-card-hover-dark:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.45);
            border-color: #9fe870;
        }

        /* Micro Icon Scaling on Parent Hover */
        .icon-bubble {
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.25s ease, color 0.25s ease;
        }
        .group:hover .icon-bubble {
            transform: scale(1.1);
            background-color: #9fe870;
            color: #163300;
        }

        /* Live Indicator Breathing Pulse */
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.55; }
        }
        .pulse-live {
            animation: pulseDot 2s infinite ease-in-out;
        }

        /* Simulator Swap Ripple Animation */
        @keyframes simSwap {
            0% { opacity: 0.35; transform: scale(0.97) translateY(4px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .sim-swap-anim {
            animation: simSwap 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Hero Entrance (Zero LCP opacity block) */
        @keyframes heroFadeIn {
            0% { opacity: 1; transform: translateY(8px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .hero-entrance {
            animation: heroFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Metric block hover */
        .metric-hover {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
        }
        .metric-hover:hover {
            transform: translateX(4px);
            border-color: #9fe870;
        }

        /* Respect Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .reveal-on-scroll { opacity: 1 !important; transform: none !important; transition: none !important; }
            .wise-card-hover:hover, .wise-card-hover-dark:hover, .btn-pill-lime:hover, .metric-hover:hover { transform: none !important; }
            .pulse-live, .hero-entrance, .sim-swap-anim { animation: none !important; }
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f2f5f0; }
        ::-webkit-scrollbar-thumb { background: #868685; border-radius: 9999px; }
    </style>
</head>

<body class="bg-paper text-charcoal min-h-screen flex flex-col overflow-x-hidden selection:bg-lime selection:text-forest">

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- TOP NAVIGATION BAR (Wise Style: Paper Background, 64px, Pill Switcher)      -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<header class="sticky top-0 z-50 h-16 bg-paper border-b border-fog transition-colors w-full">
    <div class="max-w-[1200px] mx-auto px-3 sm:px-6 h-full flex items-center justify-between gap-2 sm:gap-4 min-w-0">
        
        <!-- Brand / Identity -->
        <a href="<?= BASEURL; ?>" class="flex items-center gap-2 sm:gap-3 group min-w-0">
            <picture class="shrink-0">
                <source srcset="<?= BASEURL; ?>/assets/img/logo.webp?v=4" type="image/webp">
                <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" alt="Logo SMK NU Wargabinangun" width="40" height="40" class="h-8 w-8 sm:h-10 sm:w-10 object-contain shrink-0" fetchpriority="high" decoding="async">
            </picture>
            <span class="block text-xs sm:text-sm font-extrabold tracking-tight text-obsidian uppercase font-sans leading-tight truncate">SMK NU WARGABINANGUN</span>
        </a>

        <!-- Segmented Tab Nav Switcher (Wise Signature Pill Nav) -->
        <nav id="topNavSwitcher" class="hidden lg:flex items-center bg-fog rounded-full p-1 border border-pebble/30 shrink-0">
            <a href="#hero" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-forest bg-lime transition-all">Beranda</a>
            <a href="#fitur" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-charcoal hover:text-forest transition-colors">Fitur Presensi</a>
            <a href="#alur-kerja" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-charcoal hover:text-forest transition-colors">Alur Sistem</a>
            <a href="#akses-peran" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-charcoal hover:text-forest transition-colors">Peran Pengguna</a>
        </nav>

        <!-- Right Action: Outlined Pill Login Portal -->
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <?php if (!empty($data['isLoggedIn'])): ?>
            <a href="<?= BASEURL; ?>/dashboard" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full border border-forest bg-forest text-lime hover:bg-spruce text-[11px] sm:text-xs font-bold transition-all shadow-sm whitespace-nowrap">
                <i class="fa-solid fa-gauge text-xs"></i>
                <span class="hidden sm:inline">Ke Dashboard (<?= htmlspecialchars(explode(' ', $data['userName'] ?? '')[0]) ?>)</span>
                <span class="sm:hidden">Dashboard</span>
            </a>
            <?php else: ?>
            <a href="<?= BASEURL; ?>/auth/login" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full border border-forest bg-paper hover:bg-forest hover:text-lime text-forest text-[11px] sm:text-xs font-bold transition-all whitespace-nowrap">
                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                <span class="hidden sm:inline">Masuk Portal</span>
                <span class="sm:hidden">Masuk</span>
            </a>
            <?php endif; ?>

            <!-- Mobile Hamburger Toggle -->
            <button onclick="toggleMobileNav()" class="lg:hidden w-9 h-9 rounded-full bg-fog border border-pebble/40 text-forest flex items-center justify-center shrink-0" aria-label="Toggle Navigation">
                <i id="mobileNavIcon" class="fa-solid fa-bars text-sm"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation Drawer -->
<div id="mobileNavPanel" class="hidden lg:hidden fixed top-16 left-0 w-full bg-paper border-b border-fog px-6 py-5 space-y-3 z-40 shadow-xl">
    <a href="#hero" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-forest border-b border-fog">Beranda</a>
    <a href="#fitur" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-forest border-b border-fog">Fitur Presensi</a>
    <a href="#alur-kerja" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-forest border-b border-fog">Alur Sistem</a>
    <a href="#akses-peran" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-forest border-b border-fog">Peran Pengguna</a>
    <?php if (!empty($data['isLoggedIn'])): ?>
    <a href="<?= BASEURL; ?>/dashboard" class="block py-2 text-sm font-bold text-forest">
        <i class="fa-solid fa-gauge mr-2"></i> Buka Dashboard
    </a>
    <?php else: ?>
    <a href="<?= BASEURL; ?>/auth/login" class="block py-2 text-sm font-bold text-spruce">
        <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Buka Portal Login
    </a>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- HERO SECTION (Wise Confident Loud Voice: Massive Block Display Type)        -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<section id="hero" class="pt-10 pb-14 sm:pt-20 sm:pb-24 border-b border-fog">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 min-w-0">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 sm:gap-12 lg:gap-8 items-center min-w-0">
            
            <!-- Left Hero Content (7 Cols) -->
            <div class="lg:col-span-7 min-w-0 space-y-6 hero-entrance">
                
                <!-- Massive Wise Display Headline -->
                <h1 class="wise-headline text-[28px] sm:text-5xl lg:text-6xl uppercase leading-none break-words">
                    ABSENSI DIGITAL SMK NU WARGABINANGUN
                </h1>

                <!-- Body Copy in Warm Charcoal (18px) -->
                <p class="text-charcoal text-base sm:text-lg lg:text-[19px] leading-relaxed max-w-xl font-normal">
                    Menghubungkan gerbang sekolah, guru kelas, dan orang tua murid secara transparan. Tap kartu RFID instan untuk siswa, scan QR Barcode untuk guru, serta pemberitahuan otomatis ke orang tua tanpa antrean.
                </p>

                <!-- Action Group: Filled Lime CTA + Text Link Button -->
                <div class="pt-2 flex flex-wrap items-center gap-4 sm:gap-6">
                    <a href="<?= BASEURL; ?>/auth/login" class="btn-pill-lime text-base py-3.5 px-8 font-bold">
                        <span>Masuk ke Portal Presensi</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    
                    <a href="#fitur" class="text-forest font-semibold text-base underline decoration-2 underline-offset-4 hover:text-spruce transition-colors">
                        Pelajari Fitur Sistem &darr;
                    </a>
                </div>

                <!-- Clean Benefits Checklist -->
                <div class="pt-6 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-semibold text-slate border-t border-fog">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-forest font-bold"></i> Notifikasi Otomatis Orang Tua</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-forest font-bold"></i> Kartu RFID Siswa &amp; QR Guru</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-forest font-bold"></i> Rekap Kehadiran Real-time</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-forest font-bold"></i> Bebas Antrean di Gerbang</span>
                </div>

            </div>

            <!-- Right Hero Widget: Wise-Style Dual Surface Simulator (5 Cols) -->
            <div class="lg:col-span-5 min-w-0">
                <div class="bg-fog rounded-[24px] sm:rounded-[28px] p-4 sm:p-6 border border-pebble/30 wise-card-shadow space-y-4 min-w-0">
                    
                    <!-- Segmented Selector inside Card (Pill Switcher) -->
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <span class="text-xs font-bold text-obsidian tracking-wider uppercase">Simulasi Presensi</span>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-paper border border-pebble/30 text-[11px] font-semibold text-forest shrink-0">
                            <span class="w-2 h-2 rounded-full bg-[#10b981] pulse-live"></span> Sistem Aktif
                        </div>
                    </div>

                    <!-- Interactive Mode Tabs -->
                    <div class="grid grid-cols-2 gap-1.5 sm:gap-2 bg-paper p-1 rounded-full border border-pebble/30">
                        <button id="simTabRfid" onclick="setSimMode('rfid')" class="py-1.5 px-2 sm:px-3 rounded-full text-[11px] sm:text-xs font-bold transition-all bg-lime text-forest truncate">
                            <i class="fa-solid fa-id-card mr-1"></i> RFID Siswa
                        </button>
                        <button id="simTabQr" onclick="setSimMode('qr')" class="py-1.5 px-2 sm:px-3 rounded-full text-[11px] sm:text-xs font-bold transition-all text-charcoal hover:text-forest truncate">
                            <i class="fa-solid fa-qrcode mr-1"></i> QR Guru
                        </button>
                    </div>

                    <!-- Inset White Card: Reader Input & Result -->
                    <div id="simDisplayBox" class="bg-paper rounded-[10px] p-3.5 sm:p-5 border border-pebble/30 space-y-3 transition-all duration-300 min-w-0">
                        <div class="flex items-center justify-between gap-2 text-xs text-pebble flex-wrap">
                            <span id="simReaderLabel" class="min-w-0">Metode: Kartu RFID Siswa</span>
                            <span id="simTagType" class="text-forest font-semibold shrink-0">RFID SISWA</span>
                        </div>

                        <!-- Scanned ID Pill -->
                        <div class="flex items-center justify-between gap-2 bg-fog px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-[10px] border border-pebble/20 flex-wrap">
                            <div class="flex items-center gap-2 min-w-0">
                                <i id="simIcon" class="fa-solid fa-id-card text-forest text-sm shrink-0"></i>
                                <span id="simTokenCode" class="font-bold text-obsidian text-xs sm:text-sm truncate">Kartu RFID Siswa Aktif</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-linen text-forest font-bold text-[10px] tracking-wide uppercase shrink-0">Terverifikasi</span>
                        </div>

                        <!-- Tap Result -->
                        <div class="pt-2 border-t border-fog flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <h4 id="simPersonName" class="text-sm font-bold text-obsidian truncate">NIhlah Auliya</h4>
                                <p id="simPersonMeta" class="text-xs text-slate">NIS: 202410012 &bull; Kelas: 11 TKJ</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-block px-2.5 py-1 rounded-full bg-lime text-forest text-xs font-extrabold">HADIR</span>
                                <span class="block text-[11px] font-mono text-pebble">06:48 WIB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Instant Dispatch Status -->
                    <div class="bg-paper rounded-[10px] p-3.5 sm:p-4 border border-pebble/30 space-y-2 text-xs min-w-0">
                        <div class="flex items-center justify-between gap-2 text-charcoal flex-wrap">
                            <span class="flex items-center gap-2 font-medium">
                                <i class="fa-brands fa-whatsapp text-forest font-bold"></i> Notifikasi Orang Tua:
                            </span>
                            <span class="font-semibold text-spruce">Terkirim Langsung</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-charcoal flex-wrap">
                            <span class="flex items-center gap-2 font-medium">
                                <i class="fa-brands fa-telegram text-[#0b4c72] font-bold"></i> Laporan Wali Kelas:
                            </span>
                            <span class="font-semibold text-spruce">Diterima Otomatis</span>
                        </div>
                    </div>

                    <div class="text-center">
                        <span class="text-[11px] font-medium text-pebble">Pencatatan Kehadiran Sekolah Otomatis &bull; Cepat &amp; Akurat</span>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- TRUST NUMBERS STRIP (4 Columns on Paper / Fog Dividers)                     -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<section class="py-12 bg-paper border-b border-fog">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            
            <div class="space-y-1 border-l-2 border-forest pl-4 metric-hover reveal-on-scroll delay-100">
                <span class="wise-headline text-3xl sm:text-4xl text-obsidian block">&lt; 1 Detik</span>
                <p class="text-xs sm:text-sm font-medium text-slate">Pencatatan kehadiran siswa cepat dan tertib</p>
            </div>

            <div class="space-y-1 border-l-2 border-forest pl-4 metric-hover reveal-on-scroll delay-200">
                <span class="wise-headline text-3xl sm:text-4xl text-obsidian block">100% Otomatis</span>
                <p class="text-xs sm:text-sm font-medium text-slate">Pemberitahuan resmi langsung ke orang tua</p>
            </div>

            <div class="space-y-1 border-l-2 border-forest pl-4 metric-hover reveal-on-scroll delay-300">
                <span class="wise-headline text-3xl sm:text-4xl text-obsidian block">Real-time</span>
                <p class="text-xs sm:text-sm font-medium text-slate">Laporan kehadiran kelas siap setiap saat</p>
            </div>

            <div class="space-y-1 border-l-2 border-forest pl-4 metric-hover reveal-on-scroll delay-400">
                <span class="wise-headline text-3xl sm:text-4xl text-obsidian block">Bebas Antrean</span>
                <p class="text-xs sm:text-sm font-medium text-slate">Proses gerbang lancar saat jam masuk sekolah</p>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- FITUR SISTEM (3-Column Feature Rows on Fog with Inter 700 Headings)         -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<section id="fitur" class="py-16 sm:py-24 bg-fog border-b border-pebble/30">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-12">
        
        <!-- Section Header -->
        <div class="max-w-2xl space-y-3 reveal-on-scroll">
            <h2 class="wise-headline text-3xl sm:text-5xl uppercase">
                Kemudahan presensi untuk kenyamanan bersama.
            </h2>
            <p class="text-charcoal text-base sm:text-lg">
                Dirancang untuk mendukung kedisiplinan dan transparansi kehadiran siswa antara sekolah, guru, dan keluarga.
            </p>
        </div>

        <!-- 6 Feature Cards (10px Radius, Paper Surface) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- 1. Presensi Kartu Praktis -->
            <div class="group wise-card-hover bg-paper rounded-[10px] p-6 border border-pebble/30 flex flex-col justify-between space-y-4 reveal-on-scroll delay-100">
                <div class="space-y-3">
                    <div class="icon-bubble w-12 h-12 rounded-full bg-linen text-forest flex items-center justify-center text-xl">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Presensi Kartu Praktis</h3>
                    <p class="text-sm text-charcoal leading-relaxed">
                        Siswa cukup menempelkan kartu siswa saat tiba dan pulang sekolah. Proses verifikasi berlangsung sangat cepat tanpa antrean panjang di gerbang.
                    </p>
                </div>
                <div class="pt-3 border-t border-fog flex items-center gap-2 text-xs font-semibold text-spruce">
                    <i class="fa-solid fa-check"></i> Cepat &amp; Praktis
                </div>
            </div>

            <!-- 2. Scan QR Barcode Guru -->
            <div class="group wise-card-hover bg-paper rounded-[10px] p-6 border border-pebble/30 flex flex-col justify-between space-y-4 reveal-on-scroll delay-200">
                <div class="space-y-3">
                    <div class="icon-bubble w-12 h-12 rounded-full bg-linen text-forest flex items-center justify-center text-xl">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Scan QR Barcode Guru</h3>
                    <p class="text-sm text-charcoal leading-relaxed">
                        Presensi harian khusus dewan guru menggunakan pemindaian QR Barcode di mesin gerbang maupun portal guru secara cepat dan akurat.
                    </p>
                </div>
                <div class="pt-3 border-t border-fog flex items-center gap-2 text-xs font-semibold text-spruce">
                    <i class="fa-solid fa-check"></i> Presensi QR Khusus Guru
                </div>
            </div>

            <!-- 3. Pencatatan Aman & Akurat -->
            <div class="group wise-card-hover bg-paper rounded-[10px] p-6 border border-pebble/30 flex flex-col justify-between space-y-4 reveal-on-scroll delay-300">
                <div class="space-y-3">
                    <div class="icon-bubble w-12 h-12 rounded-full bg-linen text-forest flex items-center justify-center text-xl">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Pencatatan Aman &amp; Terjaga</h3>
                    <p class="text-sm text-charcoal leading-relaxed">
                        Seluruh rekaman kehadiran tersimpan secara aman dan terorganisir untuk memastikan rekam jejak kedisiplinan siswa selalu akurat dan terpercaya.
                    </p>
                </div>
                <div class="pt-3 border-t border-fog flex items-center gap-2 text-xs font-semibold text-spruce">
                    <i class="fa-solid fa-check"></i> Data Selalu Terlindungi
                </div>
            </div>

            <!-- 4. Notifikasi Orang Tua -->
            <div class="group wise-card-hover bg-paper rounded-[10px] p-6 border border-pebble/30 flex flex-col justify-between space-y-4 reveal-on-scroll delay-100">
                <div class="space-y-3">
                    <div class="icon-bubble w-12 h-12 rounded-full bg-linen text-forest flex items-center justify-center text-xl">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Notifikasi ke Orang Tua</h3>
                    <p class="text-sm text-charcoal leading-relaxed">
                        Orang tua murid menerima pesan pemberitahuan otomatis saat putra-putrinya tiba atau pulang sekolah untuk memberikan rasa tenang kepada keluarga.
                    </p>
                </div>
                <div class="pt-3 border-t border-fog flex items-center gap-2 text-xs font-semibold text-spruce">
                    <i class="fa-solid fa-check"></i> Transparansi Keluarga
                </div>
            </div>

            <!-- 5. Laporan Wali Kelas -->
            <div class="group wise-card-hover bg-paper rounded-[10px] p-6 border border-pebble/30 flex flex-col justify-between space-y-4 reveal-on-scroll delay-200">
                <div class="space-y-3">
                    <div class="icon-bubble w-12 h-12 rounded-full bg-linen text-forest flex items-center justify-center text-xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Laporan untuk Wali Kelas</h3>
                    <p class="text-sm text-charcoal leading-relaxed">
                        Wali kelas dan guru dapat memantau daftar siswa yang hadir maupun yang belum hadir di kelas secara langsung setiap pagi dengan mudah.
                    </p>
                </div>
                <div class="pt-3 border-t border-fog flex items-center gap-2 text-xs font-semibold text-spruce">
                    <i class="fa-solid fa-check"></i> Pantauan Kelas Harian
                </div>
            </div>

            <!-- 6. Ringkasan & Analisis Cerdas -->
            <div class="group wise-card-hover bg-paper rounded-[10px] p-6 border border-pebble/30 flex flex-col justify-between space-y-4 reveal-on-scroll delay-300">
                <div class="space-y-3">
                    <div class="icon-bubble w-12 h-12 rounded-full bg-linen text-forest flex items-center justify-center text-xl">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Ringkasan &amp; Analisis Cerdas</h3>
                    <p class="text-sm text-charcoal leading-relaxed">
                        Membantu pihak sekolah merangkum tren kehadiran harian dan bulanan secara otomatis untuk mendukung kedisiplinan dan evaluasi belajar siswa.
                    </p>
                </div>
                <div class="pt-3 border-t border-fog flex items-center gap-2 text-xs font-semibold text-spruce">
                    <i class="fa-solid fa-check"></i> Bantuan Cerdas Sekolah
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- INVERTED DARK SECTION (Wise Signature: Forest Ink #163300 + Lime Headline)  -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<section id="alur-kerja" class="py-16 sm:py-24 bg-forest text-paper">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-12">
        
        <div class="max-w-3xl space-y-4 reveal-on-scroll">
            <h2 class="wise-headline-dark text-3xl sm:text-5xl lg:text-6xl uppercase">
                Tidak ada lagi siswa terlambat tanpa konfirmasi.
            </h2>
            <p class="text-fog text-base sm:text-lg leading-relaxed font-normal">
                Dari sentuhan kartu di gerbang hingga laporan rapi yang langsung terhubung ke orang tua dan wali kelas dalam hitungan detik.
            </p>
        </div>

        <!-- 4 Step Cards Inverted -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="group wise-card-hover-dark bg-spruce/60 border border-lime/20 rounded-[10px] p-6 space-y-4 flex flex-col justify-between reveal-on-scroll delay-100">
                <div class="space-y-3">
                    <span class="font-mono text-2xl font-black text-lime transition-transform duration-300 group-hover:scale-110 inline-block">01</span>
                    <h4 class="text-base font-bold text-paper">Tap RFID Siswa &amp; QR Guru</h4>
                    <p class="text-xs text-fog leading-relaxed">
                        Siswa menempelkan kartu RFID di mesin gerbang, sementara guru melakukan scan QR Barcode dengan cepat dan tertib.
                    </p>
                </div>
                <span class="text-[11px] font-semibold text-lime">&bull; Deteksi Otomatis</span>
            </div>

            <div class="group wise-card-hover-dark bg-spruce/60 border border-lime/20 rounded-[10px] p-6 space-y-4 flex flex-col justify-between reveal-on-scroll delay-200">
                <div class="space-y-3">
                    <span class="font-mono text-2xl font-black text-lime transition-transform duration-300 group-hover:scale-110 inline-block">02</span>
                    <h4 class="text-base font-bold text-paper">Waktu Tercatat Otomatis</h4>
                    <p class="text-xs text-fog leading-relaxed">
                        Sistem memverifikasi jam kehadiran siswa secara akurat untuk memastikan kedisiplinan belajar.
                    </p>
                </div>
                <span class="text-[11px] font-semibold text-lime">&bull; Jadwal Sekolah Aktif</span>
            </div>

            <div class="group wise-card-hover-dark bg-spruce/60 border border-lime/20 rounded-[10px] p-6 space-y-4 flex flex-col justify-between reveal-on-scroll delay-300">
                <div class="space-y-3">
                    <span class="font-mono text-2xl font-black text-lime transition-transform duration-300 group-hover:scale-110 inline-block">03</span>
                    <h4 class="text-base font-bold text-paper">Notifikasi ke Orang Tua</h4>
                    <p class="text-xs text-fog leading-relaxed">
                        Pesan pemberitahuan resmi langsung terkirim ke ponsel orang tua untuk memberikan rasa tenang.
                    </p>
                </div>
                <span class="text-[11px] font-semibold text-lime">&bull; Informasi Cepat &amp; Jelas</span>
            </div>

            <div class="group wise-card-hover-dark bg-spruce/60 border border-lime/20 rounded-[10px] p-6 space-y-4 flex flex-col justify-between reveal-on-scroll delay-400">
                <div class="space-y-3">
                    <span class="font-mono text-2xl font-black text-lime transition-transform duration-300 group-hover:scale-110 inline-block">04</span>
                    <h4 class="text-base font-bold text-paper">Laporan Rekap Kelas</h4>
                    <p class="text-xs text-fog leading-relaxed">
                        Wali kelas dan pihak sekolah menerima rekapitulasi siswa yang hadir dan izin hari ini.
                    </p>
                </div>
                <span class="text-[11px] font-semibold text-lime">&bull; Rekap Rapi &amp; Siap Cetak</span>
            </div>

        </div>

        <!-- Inset White Card inside Dark Section (Wise Signature Pattern) -->
        <div class="wise-card-hover bg-paper rounded-[10px] p-4 sm:p-8 text-charcoal border border-fog space-y-6 reveal-on-scroll delay-200 min-w-0">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 min-w-0">
                <div class="min-w-0">
                    <span class="text-xs font-bold text-spruce uppercase tracking-wider">Transparansi Informasi</span>
                    <h3 class="text-lg sm:text-xl font-bold text-obsidian break-words">Pemberitahuan Terpercaya untuk Orang Tua</h3>
                </div>
                <a href="<?= BASEURL; ?>/auth/login" class="btn-pill-lime text-xs sm:text-sm py-2.5 px-5 sm:px-6 font-bold shrink-0">
                    Buka Portal Presensi &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 min-w-0">
                <div class="bg-fog p-3.5 sm:p-4 rounded-[10px] border border-pebble/30 space-y-2 min-w-0">
                    <div class="flex items-center gap-2 text-xs font-bold text-forest">
                        <i class="fa-brands fa-whatsapp text-sm shrink-0"></i> Contoh Pesan Orang Tua:
                    </div>
                    <div class="bg-paper p-3 rounded-[8px] text-xs font-sans text-charcoal border border-pebble/20 leading-relaxed break-words">
                        "Yth. Bpk/Ibu Wali Murid, ananda <strong>NIhlah Auliya (11 TKJ)</strong> telah hadir di SMK NU Wargabinangun pada pukul <strong>06:48 WIB</strong>. Terima kasih."
                    </div>
                </div>

                <div class="bg-fog p-3.5 sm:p-4 rounded-[10px] border border-pebble/30 space-y-2 min-w-0">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#0b4c72]">
                        <i class="fa-regular fa-clipboard text-sm shrink-0"></i> Contoh Laporan Kelas:
                    </div>
                    <div class="bg-paper p-3 rounded-[8px] text-xs font-sans text-charcoal border border-pebble/20 leading-relaxed break-words">
                        "[REKAP KELAS 11 TKJ] Siswa NIhlah Auliya terverifikasi hadir di sekolah. Total siswa hadir: 31 Siswa, Izin/Sakit: 1 Siswa."
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- PERAN PENGGUNA (4 Clean Cards on Paper)                                     -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<section id="akses-peran" class="py-16 sm:py-24 bg-paper border-b border-fog">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-12">
        
        <div class="max-w-2xl space-y-3 reveal-on-scroll">
            <h2 class="wise-headline text-3xl sm:text-5xl uppercase">
                Satu sistem untuk seluruh warga sekolah.
            </h2>
            <p class="text-charcoal text-base sm:text-lg">
                Setiap peran memiliki hak akses yang disesuaikan untuk kenyamanan penggunaan dan keteraturan data sekolah.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Siswa -->
            <div class="group wise-card-hover bg-fog rounded-[10px] p-6 border border-pebble/30 space-y-4 reveal-on-scroll delay-100">
                <div class="icon-bubble w-10 h-10 rounded-full bg-paper text-forest flex items-center justify-center font-bold shadow-sm">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Siswa</h3>
                <ul class="text-xs text-charcoal space-y-2 font-medium">
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Tap kartu RFID siswa di gerbang</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Presensi mandiri PKL via kamera &amp; GPS</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Riwayat kehadiran mandiri</li>
                </ul>
            </div>

            <!-- Guru & Piket -->
            <div class="group wise-card-hover bg-fog rounded-[10px] p-6 border border-pebble/30 space-y-4 reveal-on-scroll delay-200">
                <div class="icon-bubble w-10 h-10 rounded-full bg-paper text-forest flex items-center justify-center font-bold shadow-sm">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Guru &amp; Piket</h3>
                <ul class="text-xs text-charcoal space-y-2 font-medium">
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Scan QR Barcode untuk presensi guru</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Verifikasi izin dan keterlambatan</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Catatan jadwal mengajar kelas</li>
                </ul>
            </div>

            <!-- Wali Kelas -->
            <div class="group wise-card-hover bg-fog rounded-[10px] p-6 border border-pebble/30 space-y-4 reveal-on-scroll delay-300">
                <div class="icon-bubble w-10 h-10 rounded-full bg-paper text-forest flex items-center justify-center font-bold shadow-sm">
                    <i class="fa-solid fa-users-rectangle"></i>
                </div>
                <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Wali Kelas</h3>
                <ul class="text-xs text-charcoal space-y-2 font-medium">
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Pantau kehadiran khusus rombel kelasnya</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Rekapitulasi ketidakhadiran siswa</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Komunikasi terarah ke wali murid</li>
                </ul>
            </div>

            <!-- Kepala Sekolah & Admin -->
            <div class="group wise-card-hover bg-fog rounded-[10px] p-6 border border-pebble/30 space-y-4 reveal-on-scroll delay-400">
                <div class="icon-bubble w-10 h-10 rounded-full bg-paper text-forest flex items-center justify-center font-bold shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Kepsek &amp; Admin</h3>
                <ul class="text-xs text-charcoal space-y-2 font-medium">
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Laporan eksekutif kehadiran sekolah</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Pengaturan data master siswa &amp; kelas</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Ringkasan dan rekapitulasi data</li>
                </ul>
            </div>

        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- FOOTER (Wise Style Clean Restrained Foundation)                            -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<footer class="bg-paper py-12 border-t border-fog text-charcoal">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 space-y-8">
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-6 pb-6 border-b border-fog">
            <div class="flex items-start sm:items-center gap-3 min-w-0">
                <picture class="shrink-0">
                    <source srcset="<?= BASEURL; ?>/assets/img/logo.webp?v=4" type="image/webp">
                    <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" alt="Logo SMK NU Wargabinangun" width="44" height="44" class="h-10 w-10 sm:h-11 sm:w-11 object-contain shrink-0" loading="lazy" decoding="async">
                </picture>
                <div class="min-w-0">
                    <h4 class="text-sm font-extrabold text-obsidian uppercase leading-snug font-sans">SMK NU WARGABINANGUN</h4>
                    <p class="text-xs text-slate mt-1 max-w-md leading-relaxed break-words">Jl. Raya Wargabinangun No. 107, Desa Wargabinangun, Kecamatan Kaliwedi, Kabupaten Cirebon, Jawa Barat 45165</p>
                </div>
            </div>
            <div class="flex items-center sm:justify-end shrink-0 self-start sm:self-center">
                <picture>
                    <source srcset="<?= BASEURL; ?>/assets/img/smk-bisa-hebat.webp?v=1" type="image/webp">
                    <img src="<?= BASEURL; ?>/assets/img/smk-bisa-hebat.png?v=1" alt="Logo SMK Bisa Hebat" width="175" height="52" class="h-8 sm:h-11 w-auto max-w-[125px] sm:max-w-[175px] object-contain shrink-0" loading="lazy" decoding="async">
                </picture>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate">
            <p>&copy; 2026 SMK NU Wargabinangun. Powered by Nihlah.Auliya</p>
            <a href="<?= BASEURL; ?>/auth/login" class="font-bold text-forest hover:text-spruce">Portal Login</a>
        </div>

    </div>
</footer>

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- JAVASCRIPT LOGIC                                                           -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<script>
// ── MOBILE MENU TOGGLE ───────────────────────────────────────────────────────
function toggleMobileNav() {
    const panel = document.getElementById('mobileNavPanel');
    const icon = document.getElementById('mobileNavIcon');
    if (!panel) return;
    const isHidden = panel.classList.toggle('hidden');
    if (icon) {
        icon.className = isHidden ? 'fa-solid fa-bars text-sm' : 'fa-solid fa-xmark text-sm';
    }
}

// ── SIMULATOR INTERACTION (RFID vs QR) ───────────────────────────────────────
function setSimMode(mode) {
    const tabRfid = document.getElementById('simTabRfid');
    const tabQr = document.getElementById('simTabQr');
    const readerLabel = document.getElementById('simReaderLabel');
    const tagType = document.getElementById('simTagType');
    const icon = document.getElementById('simIcon');
    const tokenCode = document.getElementById('simTokenCode');
    const displayBox = document.getElementById('simDisplayBox');

    // Trigger smooth ripple animation
    if (displayBox) {
        displayBox.classList.remove('sim-swap-anim');
        void displayBox.offsetWidth; // reflow
        displayBox.classList.add('sim-swap-anim');
    }

    const simPersonName = document.getElementById('simPersonName');
    const simPersonMeta = document.getElementById('simPersonMeta');

    if (mode === 'rfid') {
        tabRfid.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all bg-lime text-forest';
        tabQr.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all text-charcoal hover:text-forest';
        if (readerLabel) readerLabel.textContent = 'Metode: Kartu RFID Siswa';
        if (tagType) tagType.textContent = 'RFID SISWA';
        if (icon) icon.className = 'fa-solid fa-id-card text-forest text-sm';
        if (tokenCode) tokenCode.textContent = 'Kartu RFID Siswa Aktif';
        if (simPersonName) simPersonName.textContent = 'NIhlah Auliya';
        if (simPersonMeta) simPersonMeta.textContent = 'NIS: 202410012 • Kelas: 11 TKJ';
    } else {
        tabQr.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all bg-lime text-forest';
        tabRfid.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all text-charcoal hover:text-forest';
        if (readerLabel) readerLabel.textContent = 'Metode: Scan QR Barcode Guru';
        if (tagType) tagType.textContent = 'QR BARCODE GURU';
        if (icon) icon.className = 'fa-solid fa-qrcode text-forest text-sm';
        if (tokenCode) tokenCode.textContent = 'QR Barcode Guru Terverifikasi';
        if (simPersonName) simPersonName.textContent = 'Ronidah, S.Kom';
        if (simPersonMeta) simPersonMeta.textContent = 'Guru Mapel • Wali Kelas 10 TKJ';
    }
}

// ── DYNAMIC NAV PILL TONE (Follows click & active section) ───────────────────
function initNavPillActiveState() {
    const navLinks = document.querySelectorAll('.nav-pill-item');
    const sections = ['hero', 'fitur', 'alur-kerja', 'akses-peran'];

    function setActivePill(targetHash) {
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === targetHash) {
                link.classList.remove('text-charcoal');
                link.classList.add('bg-lime', 'text-forest');
            } else {
                link.classList.remove('bg-lime', 'text-forest');
                link.classList.add('text-charcoal');
            }
        });
    }

    // Click handler for instant response
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetHash = this.getAttribute('href');
            setActivePill(targetHash);
        });
    });

    // Scroll Observer
    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -60% 0px',
        threshold: 0
    };

    const sectionElements = sections.map(id => document.getElementById(id)).filter(Boolean);

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setActivePill('#' + entry.target.id);
            }
        });
    }, observerOptions);

    sectionElements.forEach(sec => observer.observe(sec));
}

// ── SCROLL REVEAL OBSERVER (Smooth Entrance) ────────────────────────────────
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal-on-scroll');
    if (!('IntersectionObserver' in window)) {
        reveals.forEach(el => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px -30px 0px'
    });

    reveals.forEach(el => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', () => {
    initNavPillActiveState();
    initScrollReveal();
});
</script>

<!-- PWA Service Worker script -->
<script>
    const BASE_URL = '<?= BASEURL; ?>';
</script>
<script defer src="<?= BASEURL; ?>/assets/js/pwa.js?v=3.0"></script>

</body>
</html>