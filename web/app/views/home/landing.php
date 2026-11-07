<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Presensi Sekolah Terpadu SMK NU Wargabinangun berbasis IoT RFID RC522, QR Code Siswa, Multi-Channel WhatsApp & Telegram, dan Asisten AI Google Gemini.">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <title><?= $data['judul'] ?? 'Sistem Presensi RFID & QR Code — SMK NU Wargabinangun' ?></title>

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

    <!-- Typography: Inter (Wise style standard) & JetBrains Mono for Hardware UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Preload Critical Icon WebFont for Zero FOIT & Fast Icon Paint -->
    <link rel="preload" href="<?= BASEURL; ?>/assets/vendor/fontawesome/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Local Vendor Libraries -->
    <script src="<?= BASEURL; ?>/assets/vendor/tailwindcss/tailwind.min.js"></script>
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/vendor/fontawesome/css/all.min.css">

    <!-- Wise Design Tokens via Tailwind CSS -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: '#163300',     // Dominant brand dark
                        lime: '#9fe870',       // Electric lime voltage (CTA & active states)
                        spruce: '#054d28',     // Secondary dark green
                        linen: '#e2f6d5',      // Pale green wash / badges
                        charcoal: '#454745',   // Body text, dense UI
                        obsidian: '#0e0f0c',   // Display headlines, high contrast
                        pebble: '#868685',     // Muted secondary text, borders
                        slate: '#6a6c6a',      // Supporting body text
                        fog: '#e8ebe6',        // Card surfaces & soft dividers
                        paper: '#ffffff',      // Page canvas & white cards
                    },
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    borderRadius: {
                        pill: '9999px',
                        card: '10px',
                        largeCard: '28px',
                    }
                }
            }
        };
    </script>

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
        
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #ffffff;
            color: #454745;
            overflow-x: hidden;
        }

        section {
            scroll-margin-top: 5rem;
        }

        /* Wise Sans Signature Shout: Ultra-heavy blocky display type */
        .wise-headline {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 900;
            letter-spacing: -0.035em;
            line-height: 0.92;
            color: #0e0f0c;
        }

        .wise-headline-dark {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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

        /* Hero Entrance */
        @keyframes heroFadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .hero-entrance {
            animation: heroFadeIn 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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

<body class="bg-paper text-charcoal min-h-screen flex flex-col selection:bg-lime selection:text-forest">

<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<!-- TOP NAVIGATION BAR (Wise Style: Paper Background, 64px, Pill Switcher)      -->
<!-- ═══════════════════════════════════════════════════════════════════════════ -->
<header class="sticky top-0 z-50 h-16 bg-paper border-b border-fog transition-colors">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-full flex items-center justify-between gap-4">
        
        <!-- Brand / Identity -->
        <a href="<?= BASEURL; ?>" class="flex items-center gap-3 group">
            <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" alt="Logo SMK NU Wargabinangun" width="40" height="40" class="h-10 w-10 object-contain shrink-0" fetchpriority="high">
            <span class="block text-sm font-extrabold tracking-tight text-obsidian uppercase font-sans leading-none">SMK NU WARGABINANGUN</span>
        </a>

        <!-- Segmented Tab Nav Switcher (Wise Signature Pill Nav) -->
        <nav id="topNavSwitcher" class="hidden lg:flex items-center bg-fog rounded-full p-1 border border-pebble/30">
            <a href="#hero" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-forest bg-lime transition-all">Beranda</a>
            <a href="#fitur" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-charcoal hover:text-forest transition-colors">Fitur Presensi</a>
            <a href="#alur-kerja" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-charcoal hover:text-forest transition-colors">Alur Sistem</a>
            <a href="#akses-peran" class="nav-pill-item px-4 py-1.5 rounded-full text-xs font-semibold text-charcoal hover:text-forest transition-colors">Peran Pengguna</a>
        </nav>

        <!-- Right Action: Outlined Pill Login Portal -->
        <div class="flex items-center gap-3">
            <?php if (!empty($data['isLoggedIn'])): ?>
            <a href="<?= BASEURL; ?>/dashboard" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-forest bg-forest text-lime hover:bg-spruce text-xs font-bold transition-all shadow-sm">
                <i class="fa-solid fa-gauge text-xs"></i>
                <span>Ke Dashboard (<?= htmlspecialchars(explode(' ', $data['userName'] ?? '')[0]) ?>)</span>
            </a>
            <?php else: ?>
            <a href="<?= BASEURL; ?>/auth/login" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-forest bg-paper hover:bg-forest hover:text-lime text-forest text-xs font-bold transition-all">
                <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                <span>Masuk Portal</span>
            </a>
            <?php endif; ?>

            <!-- Mobile Hamburger Toggle -->
            <button onclick="toggleMobileNav()" class="lg:hidden w-9 h-9 rounded-full bg-fog border border-pebble/40 text-forest flex items-center justify-center" aria-label="Toggle Navigation">
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
<section id="hero" class="pt-12 pb-16 sm:pt-20 sm:pb-24 border-b border-fog">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
            
            <!-- Left Hero Content (7 Cols) -->
            <div class="lg:col-span-7 space-y-6 hero-entrance">
                
                <!-- Massive Wise Display Headline -->
                <h1 class="wise-headline text-4xl sm:text-6xl lg:text-[76px] uppercase leading-none">
                    Presensi gerbang secepat kedipan mata.
                </h1>

                <!-- Body Copy in Warm Charcoal (18px) -->
                <p class="text-charcoal text-base sm:text-lg lg:text-[19px] leading-relaxed max-w-xl font-normal">
                    Menghubungkan gerbang sekolah, guru kelas, dan orang tua murid secara transparan. Tap kartu instan, kode QR pelajar cadangan, serta pemberitahuan otomatis ke orang tua tanpa antrean.
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
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-forest font-bold"></i> Kartu Fisik &amp; QR Siswa</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-forest font-bold"></i> Rekap Kehadiran Real-time</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-forest font-bold"></i> Bebas Antrean di Gerbang</span>
                </div>

            </div>

            <!-- Right Hero Widget: Wise-Style Dual Surface Simulator (5 Cols) -->
            <div class="lg:col-span-5">
                <div class="bg-fog rounded-[28px] p-6 border border-pebble/30 wise-card-shadow space-y-4">
                    
                    <!-- Segmented Selector inside Card (Pill Switcher) -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-obsidian tracking-wider uppercase">Simulasi Presensi</span>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-paper border border-pebble/30 text-[11px] font-semibold text-forest">
                            <span class="w-2 h-2 rounded-full bg-[#10b981] pulse-live"></span> Sistem Aktif
                        </div>
                    </div>

                    <!-- Interactive Mode Tabs -->
                    <div class="grid grid-cols-2 gap-2 bg-paper p-1 rounded-full border border-pebble/30">
                        <button id="simTabRfid" onclick="setSimMode('rfid')" class="py-1.5 px-3 rounded-full text-xs font-bold transition-all bg-lime text-forest">
                            <i class="fa-solid fa-id-card mr-1"></i> Kartu Siswa
                        </button>
                        <button id="simTabQr" onclick="setSimMode('qr')" class="py-1.5 px-3 rounded-full text-xs font-bold transition-all text-charcoal hover:text-forest">
                            <i class="fa-solid fa-qrcode mr-1"></i> QR Digital
                        </button>
                    </div>

                    <!-- Inset White Card: Reader Input & Result -->
                    <div id="simDisplayBox" class="bg-paper rounded-[10px] p-5 border border-pebble/30 space-y-3 transition-all duration-300">
                        <div class="flex items-center justify-between text-xs text-pebble">
                            <span id="simReaderLabel">Metode: Kartu Siswa</span>
                            <span id="simTagType" class="text-forest font-semibold">RFID SISWA</span>
                        </div>

                        <!-- Scanned ID Pill -->
                        <div class="flex items-center justify-between bg-fog px-3.5 py-2.5 rounded-[10px] border border-pebble/20">
                            <div class="flex items-center gap-2">
                                <i id="simIcon" class="fa-solid fa-id-card text-forest text-sm"></i>
                                <span id="simTokenCode" class="font-bold text-obsidian text-sm">Kartu Siswa Aktif</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-linen text-forest font-bold text-[10px] tracking-wide uppercase">Terverifikasi</span>
                        </div>

                        <!-- Student Tap Result -->
                        <div class="pt-2 border-t border-fog flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-obsidian">Ahmad Fauzi</h4>
                                <p class="text-xs text-slate">NIS: 202410012 &bull; Kelas: 11 TKJ</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-2.5 py-1 rounded-full bg-lime text-forest text-xs font-extrabold">HADIR</span>
                                <span class="block text-[11px] font-mono text-pebble">06:48 WIB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Instant Dispatch Status -->
                    <div class="bg-paper rounded-[10px] p-4 border border-pebble/30 space-y-2 text-xs">
                        <div class="flex items-center justify-between text-charcoal">
                            <span class="flex items-center gap-2 font-medium">
                                <i class="fa-brands fa-whatsapp text-forest font-bold"></i> Notifikasi Orang Tua:
                            </span>
                            <span class="font-semibold text-spruce">Terkirim Langsung</span>
                        </div>
                        <div class="flex items-center justify-between text-charcoal">
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

            <!-- 2. Kartu Digital Siswa -->
            <div class="group wise-card-hover bg-paper rounded-[10px] p-6 border border-pebble/30 flex flex-col justify-between space-y-4 reveal-on-scroll delay-200">
                <div class="space-y-3">
                    <div class="icon-bubble w-12 h-12 rounded-full bg-linen text-forest flex items-center justify-center text-xl">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3 class="text-lg font-bold text-obsidian group-hover:text-forest transition-colors">Kartu Pelajar Digital</h3>
                    <p class="text-sm text-charcoal leading-relaxed">
                        Solusi alternatif jika kartu fisik tertinggal. Setiap siswa memiliki kode QR kartu pelajar digital yang dapat ditunjukkan langsung melalui smartphone.
                    </p>
                </div>
                <div class="pt-3 border-t border-fog flex items-center gap-2 text-xs font-semibold text-spruce">
                    <i class="fa-solid fa-check"></i> Solusi Cadangan Nyaman
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
                    <h4 class="text-base font-bold text-paper">Siswa Tiba di Sekolah</h4>
                    <p class="text-xs text-fog leading-relaxed">
                        Siswa menempelkan kartu siswa atau menunjukkan QR digital di gerbang dengan mudah dan tertib.
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
        <div class="wise-card-hover bg-paper rounded-[10px] p-6 sm:p-8 text-charcoal border border-fog space-y-6 reveal-on-scroll delay-200">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold text-spruce uppercase tracking-wider">Transparansi Informasi</span>
                    <h3 class="text-xl font-bold text-obsidian">Pemberitahuan Terpercaya untuk Orang Tua</h3>
                </div>
                <a href="<?= BASEURL; ?>/auth/login" class="btn-pill-lime text-sm py-2.5 px-6 font-bold shrink-0">
                    Buka Portal Presensi &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-fog p-4 rounded-[10px] border border-pebble/30 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-forest">
                        <i class="fa-brands fa-whatsapp text-sm"></i> Contoh Pesan Orang Tua:
                    </div>
                    <div class="bg-paper p-3 rounded-[8px] text-xs font-sans text-charcoal border border-pebble/20 leading-relaxed">
                        "Yth. Bpk/Ibu Wali Murid, ananda <strong>Ahmad Fauzi (11 TKJ)</strong> telah hadir di SMK NU Wargabinangun pada pukul <strong>06:48 WIB</strong>. Terima kasih."
                    </div>
                </div>

                <div class="bg-fog p-4 rounded-[10px] border border-pebble/30 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#0b4c72]">
                        <i class="fa-regular fa-clipboard text-sm"></i> Contoh Laporan Kelas:
                    </div>
                    <div class="bg-paper p-3 rounded-[8px] text-xs font-sans text-charcoal border border-pebble/20 leading-relaxed">
                        "[REKAP KELAS 11 TKJ] Siswa Ahmad Fauzi terverifikasi hadir di sekolah. Total siswa hadir: 31 Siswa, Izin/Sakit: 1 Siswa."
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
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Tap kartu siswa di gerbang</li>
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Tunjukkan QR digital jika kartu tertinggal</li>
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
                    <li class="flex items-start gap-2"><i class="fa-solid fa-check text-forest mt-0.5"></i> Bantuan pemindaian kode QR siswa</li>
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
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 pb-6 border-b border-fog">
            <div class="flex items-center gap-3">
                <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" alt="Logo SMK NU Wargabinangun" width="40" height="40" class="h-10 w-10 object-contain shrink-0" loading="lazy" decoding="async">
                <div>
                    <h4 class="text-sm font-extrabold text-obsidian uppercase leading-none font-sans">SMK NU WARGABINANGUN</h4>
                    <p class="text-xs text-slate mt-1">Jl. Lapangan Bola Wargabinangun, Cirebon, Jawa Barat</p>
                </div>
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

    if (mode === 'rfid') {
        tabRfid.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all bg-lime text-forest';
        tabQr.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all text-charcoal hover:text-forest';
        if (readerLabel) readerLabel.textContent = 'Metode: Kartu Siswa';
        if (tagType) tagType.textContent = 'RFID SISWA';
        if (icon) icon.className = 'fa-solid fa-id-card text-forest text-sm';
        if (tokenCode) tokenCode.textContent = 'Kartu Siswa Aktif';
    } else {
        tabQr.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all bg-lime text-forest';
        tabRfid.className = 'py-1.5 px-3 rounded-full text-xs font-bold transition-all text-charcoal hover:text-forest';
        if (readerLabel) readerLabel.textContent = 'Metode: QR Siswa Digital';
        if (tagType) tagType.textContent = 'QR PELAJAR';
        if (icon) icon.className = 'fa-solid fa-qrcode text-forest text-sm';
        if (tokenCode) tokenCode.textContent = 'QR Code Terverifikasi';
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
<script src="<?= BASEURL; ?>/assets/js/pwa.js"></script>

</body>
</html>