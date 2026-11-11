<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <meta name="description" content="Sistem Presensi RFID dan QR Code Terpadu SMK NU Wargabinangun.">
    <title><?= $data['judul'] ?? 'ABSENSI SMK NU WARGABINANGUN' ?></title>
    
    <!-- Theme Flash Prevention (FOUC) Script -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('theme') || localStorage.getItem('smk_theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    <!-- PWA Settings & Mobile Web App -->
    <link rel="manifest" href="<?= BASEURL; ?>/manifest.json">
    <meta name="theme-color" content="#059669">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Presensi RFID">
    <link rel="apple-touch-icon" href="<?= BASEURL; ?>/assets/img/apple-touch-icon.png?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASEURL; ?>/assets/img/icon-192.png?v=3">
    <link rel="shortcut icon" href="<?= BASEURL; ?>/assets/img/logo.png?v=3" type="image/png">
    
    <!-- Google Fonts: Plus Jakarta Sans, Inter, JetBrains Mono (Professional Balanced Dashboard Typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Precompiled Fast CSS First (Render-Blocking Zero Delay) -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    
    <!-- Non-Blocking Deferred Tailwind Script for Runtime Helpers -->
    <script defer src="https://cdn.tailwindcss.com"></script>
    <!-- Conditional Module Loading (Only load heavy JS on pages that actually use them to boost performance) -->
    <?php
    $curUrl = $_GET['url'] ?? '';
    $isDash = empty($curUrl) || str_starts_with($curUrl, 'dashboard');
    $isScan = str_contains($curUrl, 'scan');
    $isQr   = str_contains($curUrl, 'barcode') || str_contains($curUrl, 'profile') || $isDash;
    ?>
    <?php if ($isDash || !empty($data['load_chart'])): ?>
    <script defer src="<?= BASEURL; ?>/assets/vendor/chartjs/chart.min.js"></script>
    <?php endif; ?>
    <?php if ($isScan || $isDash || !empty($data['load_scanner'])): ?>
    <script defer src="<?= BASEURL; ?>/assets/vendor/html5-qrcode/html5-qrcode.min.js"></script>
    <?php endif; ?>
    <?php if ($isQr || !empty($data['load_qrcode'])): ?>
    <script defer src="<?= BASEURL; ?>/assets/vendor/qrcodejs/qrcode.min.js"></script>
    <?php endif; ?>    

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        forest: {
                            DEFAULT: '#163300',
                            900: '#0e2400',
                            800: '#163300',
                            700: '#1e4402',
                            600: '#285804',
                            500: '#3c7a0d',
                        },
                        lime: {
                            DEFAULT: '#9fe870',
                            400: '#b4f088',
                            500: '#9fe870',
                            600: '#86d853',
                        },
                        brand: {
                            50: '#f0f8ec',
                            100: '#ddf1d5',
                            200: '#bfe5b0',
                            300: '#9cd385',
                            400: '#75be5c',
                            500: '#52a637',
                            600: '#3e8428',
                            700: '#316721',
                            800: '#163300',
                            900: '#0e2400',
                        },
                        primary: '#163300',
                        'primary-dark': '#0e2400',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        info: '#0ea5e9'
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
                        headline: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        body: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace']
                    },
                    borderRadius: {
                        xs: '4px',
                        sm: '6px',
                        DEFAULT: '10px',
                        md: '12px',
                        lg: '14px',
                        xl: '16px',
                        '2xl': '20px',
                        full: '9999px'
                    }
                }
            }
        }
    </script>

    <style>
        /* ========== Clean & Calm Design System Tokens ========== */
        *, *::before, *::after { box-sizing: border-box; }
        
        body, button, input, select, textarea {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            letter-spacing: -0.012em;
        }

        body {
            background-color: #f8faf7;
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            letter-spacing: -0.022em;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
        }

        /* Number tabular lining so dates, hours (WIB), metrics & IDs align cleanly */
        table, .tabular-nums, .font-mono, code, pre {
            font-variant-numeric: tabular-nums;
        }

        /* Typography Monospace */
        code, pre, .font-mono {
            font-family: 'JetBrains Mono', monospace !important;
        }

        /* ── Standardized Calm Dashboard Card ── */
        .dashboard-card, .vh-card, .gd-card {
            background-color: #ffffff;
            border: 1px solid #e3e8e1;
            border-radius: 14px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02), 0 4px 14px -2px rgba(22, 51, 0, 0.03);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        }
        .dashboard-card:hover, .vh-card:hover, .gd-card:hover {
            border-color: #cbd5c8;
        }
        html.dark .dashboard-card, html.dark .vh-card, html.dark .gd-card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.4);
        }
        html.dark .dashboard-card:hover, html.dark .vh-card:hover, html.dark .gd-card:hover {
            border-color: #475569 !important;
        }

        /* ── Hover Lift Animation (Micro-interaction) ── */
        .hover-lift {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(22, 51, 0, 0.07), 0 2px 6px 0 rgba(0, 0, 0, 0.02);
            border-color: #cbd5c8;
        }
        html.dark .hover-lift:hover {
            border-color: #475569 !important;
            box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.6) !important;
        }

        /* ── Clean & Humble Dashboard Card System ── */
        .dashboard-card, .vh-card, .gd-card, .apex-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            transition: border-color 0.15s ease;
        }
        .dashboard-card:hover, .vh-card:hover, .gd-card:hover, .apex-card:hover {
            border-color: #cbd5e1;
        }
        html.dark .dashboard-card, html.dark .vh-card, html.dark .gd-card, html.dark .apex-card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: none;
        }
        html.dark .dashboard-card:hover, html.dark .vh-card:hover, html.dark .gd-card:hover, html.dark .apex-card:hover {
            border-color: #475569 !important;
        }

        /* Clean KPI Metric Card */
        .apex-metric-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
            transition: border-color 0.15s ease;
        }
        .apex-metric-card:hover {
            border-color: #cbd5e1;
        }
        html.dark .apex-metric-card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html.dark .apex-metric-card:hover {
            border-color: #475569 !important;
        }

        /* Metric Icon Box */
        .apex-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* Trend Badges */
        .apex-trend-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 1.5px 7px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }
        .apex-trend-up {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        html.dark .apex-trend-up {
            background-color: rgba(6, 78, 59, 0.4);
            color: #6ee7b7;
            border-color: #065f46;
        }
        .apex-trend-neutral {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        html.dark .apex-trend-neutral {
            background-color: #1e293b;
            color: #94a3b8;
            border-color: #334155;
        }
        .apex-trend-warn {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        html.dark .apex-trend-warn {
            background-color: rgba(120, 53, 15, 0.35);
            color: #fcd34d;
            border-color: #78350f;
        }
        .apex-trend-down {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        html.dark .apex-trend-down {
            background-color: rgba(127, 29, 29, 0.35);
            color: #fca5a5;
            border-color: #7f1d1d;
        }

        /* Simple Header Bar */
        .apex-hero-banner {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
        }
        html.dark .apex-hero-banner {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        /* Professional Clean Buttons */
        .btn-primary, .apex-btn-primary, .vh-btn-primary, .vh-btn-sage, .gd-btn-primary {
            background-color: #163300 !important;
            color: #ffffff !important;
            border: 1px solid #163300 !important;
            border-radius: 8px !important;
            padding: 7px 14px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            transition: all 0.15s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .btn-primary:hover, .apex-btn-primary:hover, .vh-btn-primary:hover, .vh-btn-sage:hover, .gd-btn-primary:hover {
            background-color: #234f03 !important;
            border-color: #234f03 !important;
        }
        html.dark .btn-primary, html.dark .apex-btn-primary, html.dark .vh-btn-primary, html.dark .vh-btn-sage, html.dark .gd-btn-primary {
            background-color: #9fe870 !important;
            border-color: #9fe870 !important;
            color: #163300 !important;
        }
        html.dark .btn-primary:hover, html.dark .apex-btn-primary:hover, html.dark .vh-btn-primary:hover, html.dark .vh-btn-sage:hover, html.dark .gd-btn-primary:hover {
            background-color: #b4f088 !important;
            border-color: #b4f088 !important;
        }

        .btn-secondary, .apex-btn-secondary, .vh-btn-secondary, .gd-btn-secondary {
            background-color: #ffffff !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 7px 14px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            transition: all 0.15s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
        }
        .btn-secondary:hover, .apex-btn-secondary:hover, .vh-btn-secondary:hover, .gd-btn-secondary:hover {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }
        html.dark .btn-secondary, html.dark .apex-btn-secondary, html.dark .vh-btn-secondary, html.dark .gd-btn-secondary {
            background-color: #1b281e !important;
            color: #e2e8f0 !important;
            border-color: #283d2e !important;
        }
        html.dark .btn-secondary:hover, html.dark .apex-btn-secondary:hover, html.dark .vh-btn-secondary:hover, html.dark .gd-btn-secondary:hover {
            background-color: #243528 !important;
            border-color: #3d5843 !important;
            color: #ffffff !important;
        }

        /* Input Presets */
        .vh-input, .gd-input {
            background-color: #ffffff !important;
            border: 1px solid #cbd5c8 !important;
            border-radius: 10px !important;
            padding: 8px 14px !important;
            font-size: 13px !important;
            color: #0f172a !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }
        .vh-input::placeholder, .gd-input::placeholder {
            color: #94a3b8 !important;
        }
        .vh-input:focus, .gd-input:focus {
            outline: none !important;
            border-color: #163300 !important;
            box-shadow: 0 0 0 3px rgba(159, 232, 112, 0.35) !important;
        }

        /* ══════════════════════════════════════════════════════════════════════
           DUAL-THEME MASTER DESIGN SYSTEM (CALM LIGHT & COHESIVE DARK)
           ══════════════════════════════════════════════════════════════════════ */
        html.dark body {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            color-scheme: dark;
        }

        /* Harmonisasi Container & Card bg-white di seluruh halaman */
        html.dark .bg-white:not(.keep-white) {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }

        html.dark .bg-white\/90,
        html.dark .bg-white\/95,
        html.dark .bg-white\/80 {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        html.dark .bg-slate-50,
        html.dark .bg-slate-50\/80,
        html.dark .bg-slate-50\/50 {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        html.dark .bg-slate-100,
        html.dark .bg-slate-100\/90,
        html.dark .bg-slate-100\/80,
        html.dark .bg-slate-100\/70 {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        /* Text Contrast Balancing */
        html.dark .text-slate-900,
        html.dark .text-slate-800 {
            color: #f8fafc !important;
        }

        html.dark .text-slate-700,
        html.dark .text-slate-600 {
            color: #cbd5e1 !important;
        }

        html.dark .text-slate-500,
        html.dark .text-slate-400 {
            color: #94a3b8 !important;
        }

        /* Borders & Dividers */
        html.dark .border-slate-100,
        html.dark .border-slate-200,
        html.dark .border-slate-200\/90,
        html.dark .border-slate-200\/80,
        html.dark .border-slate-200\/60,
        html.dark .border-slate-200\/50,
        html.dark .border-slate-300 {
            border-color: #334155 !important;
        }

        html.dark .divide-slate-100 > * + *,
        html.dark .divide-slate-100\/80 > * + *,
        html.dark .divide-slate-200 > * + * {
            border-color: #334155 !important;
        }

        /* Global Inputs, Selects & Textareas */
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="file"]),
        html.dark select,
        html.dark textarea,
        html.dark .vh-input,
        html.dark .gd-input {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #64748b !important;
        }

        html.dark input:focus,
        html.dark select:focus,
        html.dark textarea:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25) !important;
        }

        /* Table Components in Dark Mode */
        html.dark table thead tr,
        html.dark table thead th {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        html.dark table tbody tr {
            border-color: #1e293b !important;
        }

        html.dark table tbody tr:hover {
            background-color: rgba(28, 42, 65, 0.45) !important;
        }

        /* Modals in Dark Mode */
        html.dark .modal-content,
        html.dark #regModal .bg-white,
        html.dark #modalTambahSiswa .bg-white,
        html.dark #modalTambahGuru .bg-white,
        html.dark #modalImportExcel .bg-white,
        html.dark #modalResetPassword .bg-white,
        html.dark [id*="modal"] .bg-white {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        /* Tom Select Plugin Dark Styling */
        html.dark .ts-wrapper .ts-control {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html.dark .ts-wrapper.single .ts-control {
            background-color: #0f172a !important;
        }
        html.dark .ts-dropdown {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html.dark .ts-dropdown .option {
            color: #cbd5e1 !important;
        }
        html.dark .ts-dropdown .option:hover,
        html.dark .ts-dropdown .active {
            background-color: #334155 !important;
            color: #34d399 !important;
        }

        /* Shadows in Dark Mode */
        html.dark .shadow-sm,
        html.dark .shadow-md,
        html.dark .shadow-lg,
        html.dark .shadow-xl,
        html.dark .shadow-2xl,
        html.dark .shadow-2xs,
        html.dark .shadow-soft {
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.6) !important;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        html.dark ::-webkit-scrollbar-thumb { background: #334155; }
        html.dark ::-webkit-scrollbar-thumb:hover { background: #3b4e6b; }
        html { scroll-behavior: smooth; }

        /* Sidebar Responsive & Transitions */
        #sidebarMenu {
            transform: translateX(-100%);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @media (min-width: 768px) {
            #sidebarMenu {
                transform: translateX(0);
            }
        }
        #sidebarMenu.sidebar-closed {
            transform: translateX(-100%) !important;
        }
        #sidebarMenu.sidebar-open {
            transform: translateX(0) !important;
        }

        #mainContent {
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1), width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @media (min-width: 768px) {
            #mainContent {
                margin-left: 16rem;
                width: calc(100% - 16rem);
            }
            #mainContent.content-expanded {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        table { border-collapse: collapse; }
        button { cursor: pointer; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 font-sans antialiased flex min-h-screen overflow-x-hidden text-slate-800 dark:text-slate-100 transition-colors duration-150">

<!-- Overlay Mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/40 dark:bg-black/60  z-30 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

<!-- Navbar Atas -->
<nav class="bg-white dark:bg-slate-900  border-b border-slate-200 dark:border-slate-800 fixed top-0 left-0 w-full z-50 h-16 flex items-center px-4 sm:px-6 transition-colors duration-150">
    <div class="flex items-center justify-between w-full">
        <!-- Kiri: Logo & Toggle Sidebar -->
        <div class="flex items-center gap-3">
            <button id="sidebarToggleBtn" onclick="toggleSidebar()" class="text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center justify-center w-9 h-9" title="Buka / Tutup Menu">
                <i id="sidebarToggleIcon" class="fa-solid fa-bars text-base"></i>
            </button>
            <a href="<?= BASEURL; ?>/dashboard" class="flex items-center gap-3 group transition-all">
                <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" alt="Logo SMK NU Wargabinangun" width="36" height="36" class="h-9 w-9 object-contain shrink-0" fetchpriority="high">
                <div class="hidden sm:block">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight leading-tight uppercase font-headline">SMK NU WARGABINANGUN</h2>
                    <p class="text-[10.5px] text-[#2e6d22] dark:text-[#9fe870] font-semibold tracking-wider uppercase leading-tight flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]  inline-block"></span> Presensi RFID &amp; AI
                    </p>
                </div>
            </a>
        </div>

        <!-- Kanan: Notifikasi, Theme Switcher & Navbar Menu Pengguna -->
        <div class="flex items-center gap-1.5 sm:gap-2">
            <?php if (SessionHelper::get('user_id')): ?>
                <?php
                $currentAvatar = SessionHelper::get('avatar');
                $activeUid = SessionHelper::get('user_id');
                $avatarUploadDir = __DIR__ . '/../../../public/uploads/avatars/';
                
                // Sinkronkan avatar dari DB jika session belum terisi
                if ($activeUid && (empty($currentAvatar) || !file_exists($avatarUploadDir . $currentAvatar))) {
                    try {
                        $dbSyncAvatar = new Database();
                        $dbSyncAvatar->query("SELECT avatar FROM users WHERE id = :id");
                        $dbSyncAvatar->bind(':id', $activeUid);
                        $userRowSync = $dbSyncAvatar->single();
                        if ($userRowSync && !empty($userRowSync->avatar)) {
                            $currentAvatar = $userRowSync->avatar;
                            SessionHelper::set('avatar', $currentAvatar);
                        }
                    } catch (Exception $e) {}
                }
                $hasValidAvatar = (!empty($currentAvatar) && file_exists($avatarUploadDir . $currentAvatar));

                // Cek Barcode ID untuk Guru / Wali / Kepsek
                $headerBarcodeId = null;
                if (in_array(SessionHelper::get('role'), ['guru', 'wali_kelas', 'walikelas', 'kepsek'])) {
                    try {
                        $dbHeader = new Database();
                        $dbHeader->query("SELECT id, barcode_id FROM guru WHERE user_id = :uid");
                        $dbHeader->bind(':uid', SessionHelper::get('user_id'));
                        $guruHeaderData = $dbHeader->single();
                        if ($guruHeaderData) {
                            $headerBarcodeId = $guruHeaderData->barcode_id;
                            if (empty($headerBarcodeId)) {
                                $headerBarcodeId = sprintf('QR-GURU-%03d', $guruHeaderData->id);
                                $dbHeader->query("UPDATE guru SET barcode_id = :bc WHERE id = :gid");
                                $dbHeader->bind(':bc', $headerBarcodeId);
                                $dbHeader->bind(':gid', $guruHeaderData->id);
                                $dbHeader->execute();
                            }
                        }
                    } catch (Exception $e) {}
                }
                ?>

                <!-- 1. NOTIFIKASI BELL (Dropdown) -->
                <div class="relative" id="notifDropdownContainer">
                    <button id="notifBellBtn" onclick="toggleNotifDropdown()" class="relative text-slate-600 dark:text-slate-300 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 flex items-center justify-center w-9 h-9 border border-transparent hover:border-slate-200 dark:hover:border-slate-700" title="Notifikasi Kehadiran & Catatan">
                        <i class="fa-regular fa-bell text-base"></i>
                        <span id="notifBadge" class="hidden absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                        <span id="notifBadgeCount" class="hidden absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full border border-white dark:border-slate-900"></span>
                    </button>
                    
                    <!-- Dropdown Panel (Responsive on Mobile & Desktop) -->
                    <div id="notifDropdownMenu" class="hidden fixed inset-x-3 top-16 sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:mt-2.5 sm:w-96 bg-white dark:bg-[#1e293b] rounded-2xl shadow-xl border border-slate-200/90 dark:border-[#334155] z-50 animate-fade-in overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-[#334155] flex items-center justify-between bg-slate-50/60 dark:bg-[#18261c]/50">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">Pemberitahuan</span>
                                <span class="text-[10px] font-bold bg-[#ecf6e8] dark:bg-[#1b2b1f] text-[#2e6d22] dark:text-[#9fe870] px-2 py-0.5 rounded-full border border-[#d2e5cc] dark:border-[#334155]">Realtime</span>
                            </div>
                            <button onclick="markAllNotifAsRead()" class="text-[11px] font-semibold text-[#2e6d22] dark:text-[#9fe870] hover:underline cursor-pointer">
                                Tandai Dibaca
                            </button>
                        </div>
                        <div id="notifDropdownList" class="max-h-[calc(70vh-80px)] sm:max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-[#334155]">
                            <div class="p-6 text-center text-slate-400 dark:text-slate-500">
                                <i class="fa-solid fa-spinner fa-spin text-lg mb-2 text-emerald-600 dark:text-emerald-400"></i>
                                <p class="text-xs">Memuat notifikasi...</p>
                            </div>
                        </div>
                        <div class="px-4 py-2.5 border-t border-slate-100 dark:border-[#334155] bg-slate-50/60 dark:bg-[#18261c]/40 text-center">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Notifikasi presensi &amp; catatan sekolah</p>
                        </div>
                    </div>
                </div>



                <!-- 3. NAVBAR USER PROFILE MENU DROPDOWN -->
                <div class="relative" id="userMenuDropdownContainer">
                    <button id="userMenuBtn" onclick="toggleUserDropdown()" type="button" class="flex items-center gap-2 p-1 sm:pr-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800/80 border border-slate-200/80 dark:border-slate-800 transition-all text-left group cursor-pointer" title="Buka Menu Akun">
                        <!-- Avatar -->
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 flex items-center justify-center overflow-hidden shrink-0 font-bold text-xs border border-emerald-200 dark:border-emerald-800/60 ring-1 ring-emerald-500/20">
                            <?php if ($hasValidAvatar): ?>
                                <img src="<?= BASEURL; ?>/uploads/avatars/<?= htmlspecialchars($currentAvatar) ?>?v=<?= filemtime($avatarUploadDir . $currentAvatar) ?>" alt="Avatar" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?= strtoupper(substr(SessionHelper::get('name') ?? 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>

                        <!-- Info Nama & Role di Desktop -->
                        <div class="hidden sm:block min-w-0 pr-0.5">
                            <p class="text-xs font-semibold text-slate-900 dark:text-slate-100 truncate max-w-[130px] leading-tight group-hover:text-emerald-700 dark:group-hover:text-emerald-400">
                                <?= htmlspecialchars(SessionHelper::get('name') ?? '') ?>
                            </p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium uppercase tracking-wider leading-tight">
                                <?= ucfirst(SessionHelper::get('role') ?? '') ?>
                            </p>
                        </div>

                        <i id="userMenuCaret" class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 transition-transform duration-200 hidden sm:inline-block ml-0.5"></i>
                    </button>

                    <!-- Dropdown Panel Menu -->
                    <div id="userDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-64 bg-white dark:bg-[#1e293b] rounded-xl shadow-xl border border-slate-200 dark:border-[#334155] z-50 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800/80 animate-fade-in">
                        <!-- User Identity Header -->
                        <div class="p-3.5 bg-slate-50/70 dark:bg-[#18261c]/60 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 flex items-center justify-center overflow-hidden shrink-0 font-bold text-sm border border-emerald-200 dark:border-emerald-800/60">
                                <?php if ($hasValidAvatar): ?>
                                    <img src="<?= BASEURL; ?>/uploads/avatars/<?= htmlspecialchars($currentAvatar) ?>?v=<?= filemtime($avatarUploadDir . $currentAvatar) ?>" alt="Avatar" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <?= strtoupper(substr(SessionHelper::get('name') ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                    <?= htmlspecialchars(SessionHelper::get('name') ?? '') ?>
                                </p>
                                <div class="mt-0.5 flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 uppercase">
                                        <?= htmlspecialchars(SessionHelper::get('role') ?? '') ?>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono truncate">@<?= htmlspecialchars(SessionHelper::get('username') ?? '') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Navigasi Akun -->
                        <div class="p-1.5 space-y-0.5 text-xs">
                            <?php if ($headerBarcodeId): ?>
                            <!-- QR Code Presensi Saya -->
                            <button type="button" onclick="toggleUserDropdown(); openHeaderQrModal();" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80 font-medium transition-colors text-left cursor-pointer">
                                <div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs shrink-0">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <span class="flex-1">QR Code Presensi</span>
                                <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-1.5 py-0.5 rounded border border-emerald-200/60 dark:border-emerald-800/50 font-mono">Buka</span>
                            </button>
                            <?php endif; ?>

                            <!-- Profil & Pengaturan -->
                            <a href="<?= BASEURL; ?>/profile" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80 font-medium transition-colors">
                                <div class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs shrink-0">
                                    <i class="fa-solid fa-user-gear"></i>
                                </div>
                                <span class="flex-1">Profil Akun</span>
                            </a>

                            <!-- Switch Tema (Dark/Light) -->
                            <button type="button" onclick="toggleTheme()" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80 font-medium transition-colors text-left cursor-pointer">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center text-xs shrink-0">
                                        <i id="dropdownThemeIcon" class="fa-solid fa-circle-half-stroke"></i>
                                    </div>
                                    <span>Mode Tampilan</span>
                                </div>
                                <span id="userMenuThemeLabel" class="text-[10px] font-semibold text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                    Terang / Gelap
                                </span>
                            </button>
                        </div>

                        <!-- Logout / Keluar -->
                        <div class="p-1.5">
                            <a href="<?= BASEURL; ?>/auth/logout" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-semibold transition-colors">
                                <div class="w-6 h-6 rounded-md bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs shrink-0">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </div>
                                <span>Keluar / Logout</span>
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <a href="<?= BASEURL; ?>/auth/login" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg transition-colors shadow-xs">
                    <i class="fa-solid fa-lock text-xs"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (isset($headerBarcodeId) && $headerBarcodeId): ?>
<!-- Modal QR Code -->
<div id="headerQrModal" class="fixed inset-0 z-50 bg-slate-900/50 dark:bg-black/70  hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-sm p-6 text-center animate-fade-in">
        <h3 class="text-base font-bold text-slate-900 dark:text-white font-headline">QR Code Presensi Guru</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4"><?= htmlspecialchars(SessionHelper::get('name') ?? '') ?></p>
        <div id="headerQrCodeContainer" class="p-4 bg-white rounded-xl inline-block shadow-xs border border-slate-200 mx-auto"></div>
        <p class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300 mt-3"><?= htmlspecialchars($headerBarcodeId) ?></p>
        <div class="flex gap-2 mt-5">
            <button onclick="downloadHeaderQr()" class="flex-1 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-colors text-xs flex items-center justify-center gap-2">
                <i class="fa-solid fa-download"></i> Unduh QR
            </button>
            <button onclick="document.getElementById('headerQrModal').classList.add('hidden')" class="flex-1 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
var headerQrInstance = null;
function openHeaderQrModal() {
    var modal = document.getElementById('headerQrModal');
    if (modal) modal.classList.remove('hidden');
    var container = document.getElementById('headerQrCodeContainer');
    if (container && !headerQrInstance) {
        container.innerHTML = '';
        headerQrInstance = new QRCode(container, {
            text: "<?= htmlspecialchars($headerBarcodeId, ENT_QUOTES) ?>",
            width: 180,
            height: 180,
            colorDark : "#0f172a",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }
}

function downloadHeaderQr() {
    var container = document.getElementById('headerQrCodeContainer');
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
        a.download = "QR-Presensi-<?= htmlspecialchars($headerBarcodeId, ENT_QUOTES) ?>.png";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    } else {
        alert('Gambar QR belum siap diunduh.');
    }
}
</script>
<?php endif; ?>

<!-- Sidebar Menu (Clean, Consistent & Dual-Theme Ready) -->
<aside id="sidebarMenu" class="fixed top-16 left-0 z-40 w-64 bg-white dark:bg-[#1e293b] border-r border-slate-200 dark:border-slate-800 flex flex-col h-[calc(100vh-4rem)] transition-colors duration-150">
    <div class="flex flex-col h-full">
        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-6">
            <div>
                <p class="px-3 mb-2 text-[10px] font-bold text-slate-400 dark:text-emerald-500/70 uppercase tracking-widest font-sans">Menu Utama</p>
                <ul class="space-y-1">
                    <?php
                        $reqUri = $_SERVER['REQUEST_URI'] ?? '';
                        $userRole = SessionHelper::get('role');

                        // Standalone checkers
                        $isDashboardActive = (
                            strpos($reqUri, '/dashboard') !== false ||
                            $reqUri === '/AbsensiRFID/web/public/' ||
                            $reqUri === '/AbsensiRFID/web/public/index.php' ||
                            rtrim($reqUri, '/') === rtrim(parse_url(BASEURL, PHP_URL_PATH) ?? '', '/')
                        );
                        $isCatatanActive = (strpos($reqUri, '/catatan') !== false);

                        // Jadwal Checkers
                        $isJadwalGuru = (
                            strpos($reqUri, '/jadwal/guru') !== false ||
                            strpos($reqUri, '/jadwal/detail_guru') !== false ||
                            strpos($reqUri, '/jadwal/edit_guru') !== false ||
                            strpos($reqUri, '/jadwal/update_guru') !== false
                        );
                        $isJadwalSiswa = (strpos($reqUri, '/jadwal') !== false && !$isJadwalGuru);
                        $isJadwalGroupActive = ($isJadwalSiswa || $isJadwalGuru);

                        // Presensi & Rekap Checkers
                        $isRekapActive = (strpos($reqUri, '/rekap') !== false);
                        $isApprovalActive = (strpos($reqUri, '/izin/approval') !== false || strpos($reqUri, '/izin/detail') !== false);
                        $isScannerActive = (strpos($reqUri, '/guru/scanner') !== false);
                        $isPklKelompokActive = (strpos($reqUri, '/pkl/kelompok') !== false);
                        $isPklMonitoringActive = (strpos($reqUri, '/pkl/monitoring') !== false);
                        $isPklGroupActive = (strpos($reqUri, '/pkl') !== false && strpos($reqUri, '/absensi/pkl') === false);
                        $isPresensiAdminActive = ($isRekapActive || $isApprovalActive);
                        $isPresensiKepsekActive = ($isRekapActive || $isApprovalActive);
                        $isPresensiWaliActive = ($isScannerActive || $isRekapActive || $isApprovalActive);

                        // Live Monitor Checkers
                        $isMonitorSiswa = (strpos($reqUri, '/riwayat') !== false && strpos($reqUri, '/riwayat/guru') === false && strpos($reqUri, '/izin/riwayat') === false);
                        $isMonitorGuru = (strpos($reqUri, '/riwayat/guru') !== false);
                        $isMonitorGroupActive = ($isMonitorSiswa || $isMonitorGuru);

                        // Data Master Checkers
                        $isUsersActive = (strpos($reqUri, '/users') !== false);
                        $isRfidActive = (strpos($reqUri, '/rfid') !== false);
                        $isMasterGroupActive = ($isUsersActive || $isRfidActive);

                        // Konfigurasi Checkers
                        $isWaActive = (strpos($reqUri, '/wa') !== false || strpos($reqUri, '/whatsapp') !== false || strpos($reqUri, '/admin/wa') !== false);
                        $isTelegramActive = (strpos($reqUri, '/kelas') !== false || strpos($reqUri, '/telegram') !== false);
                        $isIotActive = (strpos($reqUri, '/admin/iot') !== false || strpos($reqUri, '/iot') !== false);
                        $isConfigGroupActive = ($isWaActive || $isTelegramActive || $isIotActive);

                        // Siswa & Guru Specific
                        $isSiswaAbsensi = (strpos($reqUri, '/absensi/siswa') !== false);
                        $isPengajuanIzin = (strpos($reqUri, '/izin/riwayat') !== false || strpos($reqUri, '/izin/ajukan') !== false);
                        $isPklActive = (strpos($reqUri, '/absensi/pkl') !== false || strpos($reqUri, '/pkl') !== false);

                        // Cek apakah siswa saat ini berada di kelas berstatus PKL
                        $isStudentPkl = false;
                        if ($userRole === 'siswa') {
                            $headerUid = (int)SessionHelper::get('user_id');
                            if ($headerUid) {
                                $headerDb = new Database();
                                $headerDb->query("
                                    SELECT k.status as status_kelas 
                                    FROM siswa s 
                                    JOIN kelas k ON s.kelas_id = k.id 
                                    WHERE s.user_id = :uid 
                                    LIMIT 1
                                ");
                                $headerDb->bind(':uid', $headerUid);
                                $hRes = $headerDb->single();
                                $isStudentPkl = ($hRes && ($hRes->status_kelas ?? '') === 'pkl');
                            }
                        }

                        // Cek apakah user adalah Pembimbing PKL
                        $isUserPembimbingPkl = false;
                        if (in_array($userRole, ['guru', 'wali_kelas', 'walikelas'])) {
                            $headerUid = (int)SessionHelper::get('user_id');
                            if ($headerUid) {
                                require_once __DIR__ . '/../../models/Pkl.php';
                                $pklCheckModel = new Pkl();
                                $isUserPembimbingPkl = $pklCheckModel->isUserPembimbingPkl($headerUid);
                            }
                        }

                        $isSiswaPresensiGroupActive = ($isSiswaAbsensi || $isPengajuanIzin || $isPklActive);
                    ?>

                    <!-- 1. DASHBOARD (All Roles) -->
                    <li>
                        <a href="<?= BASEURL; ?>/dashboard"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-colors <?= $isDashboardActive ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 font-semibold border-l-4 border-emerald-600 dark:border-emerald-500' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 font-medium' ?>">
                            <i class="fa-solid fa-house w-5 text-center text-sm <?= $isDashboardActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                            <span class="truncate">Dashboard</span>
                        </a>
                    </li>

                    <?php if ($userRole === 'admin'): ?>
                    <!-- ADMIN: 2. JADWAL PELAJARAN (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-jadwal', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isJadwalGroupActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-calendar-days w-5 text-center text-sm <?= $isJadwalGroupActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Jadwal Pelajaran</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isJadwalGroupActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-jadwal" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isJadwalGroupActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/jadwal"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isJadwalSiswa ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-graduation-cap text-xs w-4 text-center shrink-0 <?= $isJadwalSiswa ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Jadwal Siswa</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/jadwal/guru"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isJadwalGuru ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-chalkboard-user text-xs w-4 text-center shrink-0 <?= $isJadwalGuru ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Jadwal Guru</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- ADMIN: 3. PRESENSI & REKAP (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-presensi', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isPresensiAdminActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-clipboard-user w-5 text-center text-sm <?= $isPresensiAdminActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Presensi & Rekap</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isPresensiAdminActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-presensi" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isPresensiAdminActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/rekap-absensi?tipe=siswa"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= ($isRekapActive && (($_GET['tipe'] ?? 'siswa') === 'siswa')) ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-graduation-cap text-xs w-4 text-center shrink-0 text-emerald-600"></i>
                                    <span class="truncate">Rekap Presensi Siswa</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/rekap-absensi?tipe=guru"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= ($isRekapActive && (($_GET['tipe'] ?? '') === 'guru')) ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-chalkboard-user text-xs w-4 text-center shrink-0 text-sky-600"></i>
                                    <span class="truncate">Rekap Presensi Guru</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/izin/approval"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isApprovalActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-clipboard-check text-xs w-4 text-center shrink-0 <?= $isApprovalActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Riwayat Persetujuan</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- ADMIN: 4. GURU PEMBIMBING PKL (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-admin-pkl', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isPklGroupActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-user-tie w-5 text-center text-sm <?= $isPklGroupActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Guru Pembimbing PKL</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isPklGroupActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-admin-pkl" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isPklGroupActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/pkl/kelompok"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isPklKelompokActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-sliders text-xs w-4 text-center shrink-0 <?= $isPklKelompokActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Pengaturan Kelompok &amp; Lokasi</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/pkl/monitoring"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isPklMonitoringActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-clock-rotate-left text-xs w-4 text-center shrink-0 <?= $isPklMonitoringActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Log Riwayat &amp; Monitoring</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- ADMIN: 5. LIVE MONITOR (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-monitor', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isMonitorGroupActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-desktop w-5 text-center text-sm <?= $isMonitorGroupActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Live Monitor</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isMonitorGroupActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-monitor" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isMonitorGroupActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/riwayat" target="_blank"
                                   class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isMonitorSiswa ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <div class="flex items-center gap-2.5 truncate">
                                        <i class="fa-solid fa-users text-xs w-4 text-center shrink-0 <?= $isMonitorSiswa ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                        <span class="truncate">Live Monitor Siswa</span>
                                    </div>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400 opacity-60"></i>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/riwayat/guru" target="_blank"
                                   class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isMonitorGuru ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <div class="flex items-center gap-2.5 truncate">
                                        <i class="fa-solid fa-chalkboard-teacher text-xs w-4 text-center shrink-0 <?= $isMonitorGuru ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                        <span class="truncate">Live Monitor Guru</span>
                                    </div>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400 opacity-60"></i>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- ADMIN: 5. DATA MASTER (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-master', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isMasterGroupActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-database w-5 text-center text-sm <?= $isMasterGroupActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Data Master</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isMasterGroupActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-master" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isMasterGroupActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/users"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isUsersActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-user-gear text-xs w-4 text-center shrink-0 <?= $isUsersActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Kelola Pengguna</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/rfid"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isRfidActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-id-card text-xs w-4 text-center shrink-0 <?= $isRfidActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Data & Registrasi RFID</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- ADMIN: 6. KONFIGURASI SISTEM (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-config', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isConfigGroupActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-sliders w-5 text-center text-sm <?= $isConfigGroupActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Konfigurasi Sistem</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isConfigGroupActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-config" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isConfigGroupActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/wa"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isWaActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-brands fa-whatsapp text-sm w-4 text-center shrink-0 <?= $isWaActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-emerald-600/80 dark:text-emerald-400/80' ?>"></i>
                                    <span class="truncate">Konfigurasi WhatsApp</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/kelas"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isTelegramActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-brands fa-telegram text-sm w-4 text-center shrink-0 <?= $isTelegramActive ? 'text-sky-500' : 'text-sky-500/80' ?>"></i>
                                    <span class="truncate">Konfigurasi Telegram</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/admin/iot"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isIotActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-microchip text-xs w-4 text-center shrink-0 <?= $isIotActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Kontrol & Konfigurasi IoT</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/dashboard/admin#ai-config" onclick="if(typeof openAiConfigModal === 'function'){ openAiConfigModal(); return false; }"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium">
                                    <i class="fa-solid fa-sliders text-xs w-4 text-center shrink-0 text-emerald-600 dark:text-emerald-400"></i>
                                    <span class="truncate">Pengaturan Chatbot</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($userRole === 'kepsek'): ?>
                    <!-- KEPSEK: PRESENSI GURU (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-kepsek-presensi', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isPresensiKepsekActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-clipboard-user w-5 text-center text-sm <?= $isPresensiKepsekActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Presensi Guru</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isPresensiKepsekActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-kepsek-presensi" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isPresensiKepsekActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/rekap-absensi?tipe=guru"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= ($isRekapActive && (($_GET['tipe'] ?? 'guru') === 'guru')) ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-chalkboard-user text-xs w-4 text-center shrink-0 text-sky-600"></i>
                                    <span class="truncate">Rekap Presensi Guru</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/rekap-absensi?tipe=siswa"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= ($isRekapActive && (($_GET['tipe'] ?? '') === 'siswa')) ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-graduation-cap text-xs w-4 text-center shrink-0 text-emerald-600"></i>
                                    <span class="truncate">Monitoring Siswa</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/izin/approval"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isApprovalActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-clipboard-check text-xs w-4 text-center shrink-0 <?= $isApprovalActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Persetujuan Izin Guru</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($userRole, ['wali_kelas', 'walikelas'])): ?>
                    <!-- WALI KELAS: PRESENSI KELAS (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-wali-presensi', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isPresensiWaliActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-clipboard-user w-5 text-center text-sm <?= $isPresensiWaliActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Presensi Kelas</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isPresensiWaliActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-wali-presensi" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isPresensiWaliActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/guru/scanner"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isScannerActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-qrcode text-xs w-4 text-center shrink-0 <?= $isScannerActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Scanner Absensi</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/rekap-absensi"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isRekapActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-file-lines text-xs w-4 text-center shrink-0 <?= $isRekapActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Rekap Absensi Kelas</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/izin/approval"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isApprovalActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-clipboard-check text-xs w-4 text-center shrink-0 <?= $isApprovalActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Persetujuan Izin</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/izin/riwayat"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-colors <?= $isPengajuanIzin ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 font-semibold border-l-4 border-emerald-600 dark:border-emerald-500' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 font-medium' ?>">
                            <i class="fa-solid fa-paper-plane w-5 text-center text-sm <?= $isPengajuanIzin ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                            <span class="truncate">Pengajuan Izin</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($userRole === 'guru'): ?>
                    <!-- GURU: SCANNER & PENGAJUAN -->
                    <li>
                        <a href="<?= BASEURL; ?>/guru/scanner"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-colors <?= $isScannerActive ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 font-semibold border-l-4 border-emerald-600 dark:border-emerald-500' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 font-medium' ?>">
                            <i class="fa-solid fa-qrcode w-5 text-center text-sm <?= $isScannerActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                            <span class="truncate">Scanner Absensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL; ?>/izin/riwayat"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-colors <?= $isPengajuanIzin ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 font-semibold border-l-4 border-emerald-600 dark:border-emerald-500' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 font-medium' ?>">
                            <i class="fa-solid fa-paper-plane w-5 text-center text-sm <?= $isPengajuanIzin ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                            <span class="truncate">Pengajuan Izin</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($userRole, ['guru', 'wali_kelas', 'walikelas']) && $isUserPembimbingPkl): ?>
                    <!-- GURU PEMBIMBING PKL -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-guru-pkl', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isPklGroupActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-user-tie w-5 text-center text-sm <?= $isPklGroupActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Guru Pembimbing PKL</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isPklGroupActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-guru-pkl" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isPklGroupActive ? '' : 'hidden' ?>">
                            <li>
                                <a href="<?= BASEURL; ?>/pkl/kelompok"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isPklKelompokActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-sliders text-xs w-4 text-center shrink-0 <?= $isPklKelompokActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Pengaturan Kelompok &amp; Lokasi</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/pkl/monitoring"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isPklMonitoringActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-clock-rotate-left text-xs w-4 text-center shrink-0 <?= $isPklMonitoringActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Log Riwayat &amp; Monitoring</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if ($userRole === 'siswa'): ?>
                    <!-- SISWA: PRESENSI & IZIN (Nested List) -->
                    <li>
                        <button type="button" onclick="toggleNavSubmenu('submenu-siswa-presensi', this)"
                                class="group w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-medium transition-colors <?= $isSiswaPresensiGroupActive ? 'bg-emerald-50/70 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80' ?>">
                            <div class="flex items-center gap-3 truncate">
                                <i class="fa-solid fa-clipboard-user w-5 text-center text-sm <?= $isSiswaPresensiGroupActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                                <span class="truncate">Presensi & Izin</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 submenu-arrow <?= $isSiswaPresensiGroupActive ? 'rotate-180 text-emerald-600 dark:text-emerald-400' : '' ?>"></i>
                        </button>
                        <ul id="submenu-siswa-presensi" class="ml-5 pl-3 py-1 space-y-0.5 border-l border-slate-200 dark:border-slate-800 <?= $isSiswaPresensiGroupActive ? '' : 'hidden' ?>">
                            <?php if ($isStudentPkl): ?>
                            <li>
                                <a href="<?= BASEURL; ?>/absensi/pkl"
                                   class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isPklActive ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <div class="flex items-center gap-2.5 truncate">
                                        <i class="fa-solid fa-camera-retro text-xs w-4 text-center shrink-0 <?= $isPklActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-indigo-500' ?>"></i>
                                        <span class="truncate font-semibold text-emerald-900 dark:text-emerald-200">Presensi PKL</span>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold uppercase bg-emerald-100 dark:bg-emerald-900/80 text-emerald-700 dark:text-emerald-300">
                                        PKL
                                    </span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <li>
                                <a href="<?= BASEURL; ?>/absensi/siswa"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isSiswaAbsensi ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-clock-rotate-left text-xs w-4 text-center shrink-0 <?= $isSiswaAbsensi ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Riwayat Absensi</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASEURL; ?>/izin/riwayat"
                                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md text-xs transition-colors <?= $isPengajuanIzin ? 'text-emerald-700 dark:text-emerald-300 font-semibold bg-emerald-50 dark:bg-emerald-950/50' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/70 dark:hover:bg-slate-800/60 font-medium' ?>">
                                    <i class="fa-solid fa-paper-plane text-xs w-4 text-center shrink-0 <?= $isPengajuanIzin ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                                    <span class="truncate">Pengajuan Izin</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (in_array($userRole, ['siswa', 'guru', 'wali_kelas', 'walikelas', 'kepsek'])): ?>
                    <!-- CATATAN (Siswa, Guru, Wali Kelas, Kepsek) -->
                    <li>
                        <a href="<?= BASEURL; ?>/catatan"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs transition-colors <?= $isCatatanActive ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 font-semibold border-l-4 border-emerald-600 dark:border-emerald-500' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100/80 dark:hover:bg-slate-800/80 font-medium' ?>">
                            <i class="fa-solid fa-clipboard-list w-5 text-center text-sm <?= $isCatatanActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>"></i>
                            <span class="truncate">Catatan</span>
                        </a>
                    </li>
                    <?php endif; ?>

                </ul>
            </div>
        </nav>

    </div>
</aside>

<!-- Wrapper Utama Konten -->
<div id="mainContent" class="flex-1 w-full pt-16 flex flex-col items-center bg-slate-50 dark:bg-slate-950 min-h-screen transition-colors duration-150">
    <main class="w-full max-w-7xl px-3 sm:px-6 py-6 text-slate-800 dark:text-slate-100">
        <?php Flasher::flash(); ?>
        <?php if (SessionHelper::get('role') === 'admin') $this->view('templates/ai_modal'); ?>

<script>
// ── THEME SWITCHER LOGIC ─────────────────────────────────────────────────────
function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    localStorage.setItem('smk_theme', isDark ? 'dark' : 'light');
    updateThemeIcon(isDark);
    window.dispatchEvent(new CustomEvent('themeChanged', { detail: { isDark } }));
}

function updateThemeIcon(isDark) {
    const icon = document.getElementById('themeToggleIcon');
    if (icon) {
        icon.className = isDark 
            ? 'fa-solid fa-sun text-base text-amber-400' 
            : 'fa-solid fa-moon text-base text-slate-600 dark:text-slate-400';
    }
    const dropIcon = document.getElementById('dropdownThemeIcon');
    if (dropIcon) {
        dropIcon.className = isDark ? 'fa-solid fa-sun text-xs text-amber-400' : 'fa-solid fa-moon text-xs text-slate-500 dark:text-slate-400';
    }
    const dropLabel = document.getElementById('userMenuThemeLabel');
    if (dropLabel) {
        dropLabel.textContent = isDark ? 'Gelap' : 'Terang';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateThemeIcon(document.documentElement.classList.contains('dark'));
});

// ── SIDEBAR SUBMENU ACCORDION TOGGLE ─────────────────────────────────────────
function toggleNavSubmenu(menuId, btn) {
    const submenu = document.getElementById(menuId);
    if (!submenu) return;
    const isHidden = submenu.classList.toggle('hidden');
    const arrow = btn ? btn.querySelector('.submenu-arrow') : null;
    if (arrow) {
        if (isHidden) {
            arrow.classList.remove('rotate-180');
        } else {
            arrow.classList.add('rotate-180');
        }
    }
}

// ── SIDEBAR RESPONSIVE TOGGLE ────────────────────────────────────────────────
function toggleSidebar() {
    const sidebar = document.getElementById('sidebarMenu');
    const overlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');
    const icon = document.getElementById('sidebarToggleIcon');

    if (window.innerWidth < 768) {
        // Mobile drawer
        const isOpen = sidebar.classList.toggle('sidebar-open');
        overlay.classList.toggle('hidden', !isOpen);
        if (icon) icon.className = isOpen ? 'fa-solid fa-xmark text-base' : 'fa-solid fa-bars text-base';
    } else {
        // Desktop collapse
        const isClosed = sidebar.classList.toggle('sidebar-closed');
        mainContent.classList.toggle('content-expanded', isClosed);
        if (icon) icon.className = isClosed ? 'fa-solid fa-bars text-base' : 'fa-solid fa-bars-staggered text-base';
    }
}

// ── NOTIFICATION DROPDOWN TOGGLE ─────────────────────────────────────────────
function toggleNotifDropdown() {
    const menu = document.getElementById('notifDropdownMenu');
    if (menu) menu.classList.toggle('hidden');
    // Tutup user dropdown jika terbuka
    const userMenu = document.getElementById('userDropdownMenu');
    if (userMenu) userMenu.classList.add('hidden');
}

// ── USER PROFILE NAVBAR MENU DROPDOWN ─────────────────────────────────────────
function toggleUserDropdown() {
    const userMenu = document.getElementById('userDropdownMenu');
    const caret = document.getElementById('userMenuCaret');
    if (!userMenu) return;
    const isHidden = userMenu.classList.toggle('hidden');
    if (caret) {
        caret.style.transform = isHidden ? '' : 'rotate(180deg)';
    }
    // Tutup notif menu jika terbuka
    const notifMenu = document.getElementById('notifDropdownMenu');
    if (notifMenu) notifMenu.classList.add('hidden');
}

// Global click-outside listener untuk menutup menu dropdown
document.addEventListener('click', function(e) {
    // 1. Notif Dropdown
    const notifContainer = document.getElementById('notifDropdownContainer');
    const notifMenu = document.getElementById('notifDropdownMenu');
    if (notifContainer && notifMenu && !notifContainer.contains(e.target)) {
        notifMenu.classList.add('hidden');
    }

    // 2. User Menu Dropdown
    const userContainer = document.getElementById('userMenuDropdownContainer');
    const userMenu = document.getElementById('userDropdownMenu');
    const userCaret = document.getElementById('userMenuCaret');
    if (userContainer && userMenu && !userContainer.contains(e.target)) {
        userMenu.classList.add('hidden');
        if (userCaret) userCaret.style.transform = '';
    }
});

// Tutup dropdown dengan tombol Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const notifMenu = document.getElementById('notifDropdownMenu');
        if (notifMenu) notifMenu.classList.add('hidden');
        const userMenu = document.getElementById('userDropdownMenu');
        if (userMenu) userMenu.classList.add('hidden');
        const userCaret = document.getElementById('userMenuCaret');
        if (userCaret) userCaret.style.transform = '';
    }
});
</script>