<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= BASEURL; ?>">
    <title><?= $data['judul'] ?? 'Masuk Portal - SMK NU WARGABINANGUN' ?></title>
    
    <!-- PWA Settings & Mobile Web App -->
    <link rel="manifest" href="<?= BASEURL; ?>/manifest.json">
    <meta name="theme-color" content="#163300">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Presensi RFID">
    <link rel="apple-touch-icon" href="<?= BASEURL; ?>/assets/img/apple-touch-icon.png?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASEURL; ?>/assets/img/icon-192.png?v=3">
    <link rel="shortcut icon" href="<?= BASEURL; ?>/assets/img/logo.png?v=3" type="image/png">
    
    <!-- Typography: Fast First Paint -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Precompiled Fast CSS First (Instant Render) -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    
    <!-- Non-Blocking Deferred Tailwind JIT for Extended Theme Tokens -->
    <script defer src="https://cdn.tailwindcss.com"></script>

    <!-- Wise Design Tokens Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: '#163300',     // Dominant brand dark
                        lime: '#9fe870',       // Electric lime voltage (CTA)
                        spruce: '#054d28',     // Secondary dark green
                        linen: '#e2f6d5',      // Pale green wash
                        charcoal: '#454745',   // Body text, dense UI
                        obsidian: '#0e0f0c',   // Display headlines
                        pebble: '#868685',     // Muted text, borders
                        slate: '#6a6c6a',      // Supporting text
                        fog: '#e8ebe6',        // Soft backgrounds
                        paper: '#ffffff',      // Page canvas & cards
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                    borderRadius: {
                        pill: '9999px',
                        card: '10px',
                        largeCard: '24px',
                    }
                }
            }
        }
    </script>
    
    <!-- Robust Standalone CSS Fallback (Jaminan Tampilan Rapi 100%) -->
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #f7f9f6;
            background: radial-gradient(circle at 50% 15%, rgba(159, 232, 112, 0.16) 0%, rgba(247, 249, 246, 0.96) 55%, #f7f9f6 100%);
            color: #454745;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .wise-headline {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 900;
            letter-spacing: -0.035em;
            line-height: 1.1;
            color: #0e0f0c;
        }

        /* ── CARD UTAMA LOGIN ── */
        .login-card-box {
            background-color: #ffffff !important;
            border-radius: 24px !important;
            box-shadow: 0 16px 36px -8px rgba(22, 51, 0, 0.08), 0 4px 16px -2px rgba(0, 0, 0, 0.04) !important;
            border: 1px solid rgba(134, 134, 133, 0.25) !important;
            width: 100%;
            max-width: 420px;
            padding: 2.25rem 2.25rem;
            position: relative;
            z-index: 10;
            margin: 0 auto;
        }

        @media (max-width: 480px) {
            .login-card-box {
                padding: 1.75rem 1.5rem;
            }
        }

        /* ── WARNA & UTILITAS STANDALONE ── */
        .bg-paper { background-color: #ffffff !important; }
        .text-obsidian { color: #0e0f0c !important; }
        .text-slate { color: #6a6c6a !important; }
        .text-pebble { color: #868685 !important; }
        .text-forest { color: #163300 !important; }
        .bg-forest { background-color: #163300 !important; }
        .text-lime { color: #9fe870 !important; }
        .bg-lime { background-color: #9fe870 !important; }
        .border-fog { border-color: #e8ebe6 !important; }

        /* ── LOGO HOVER LIFT ── */
        .logo-smooth {
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .logo-smooth:hover {
            transform: scale(1.06) translateY(-2px);
        }

        /* ── TACTILE LIME PILL BUTTON ── */
        .btn-pill-lime {
            width: 100%;
            padding: 13px 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: #9fe870 !important;
            color: #163300 !important;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            position: relative;
        }
        .btn-pill-lime:hover {
            background-color: #8ee05c !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 24px -4px rgba(159, 232, 112, 0.5);
        }
        .btn-pill-lime:active {
            transform: scale(0.98) translateY(0);
        }
        .btn-pill-lime i {
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-pill-lime:hover i {
            transform: translateX(4px);
        }

        /* ── INPUT FOCUS AURA ── */
        .wise-input {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #cbd0c8;
            background-color: #ffffff;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #0e0f0c;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .wise-input:focus {
            outline: none;
            border-color: #163300;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(159, 232, 112, 0.35);
        }

        /* ── SMOOTH ENTRANCE ANIMATION ── */
        @keyframes loginCardEntrance {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .login-card-anim {
            animation: loginCardEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            will-change: opacity, transform;
        }

        @media (prefers-reduced-motion: reduce) {
            .login-card-anim { animation: none !important; }
            .logo-smooth:hover, .btn-pill-lime:hover { transform: none !important; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 relative font-sans antialiased selection:bg-lime selection:text-forest">

    <!-- Login Card Container -->
    <div class="login-card-box login-card-anim space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <a href="<?= BASEURL; ?>" class="inline-block hover:opacity-95 transition-opacity">
                <img src="<?= BASEURL; ?>/assets/img/logo.png?v=4" 
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=SMK+NU&background=163300&color=9fe870&size=128&bold=true';"
                     alt="Logo SMK NU Wargabinangun" 
                     width="64" 
                     height="64" 
                     class="h-16 w-16 object-contain mx-auto mb-2 logo-smooth" 
                     fetchpriority="high">
            </a>

            <h1 class="wise-headline text-2xl sm:text-[28px] uppercase">
                Masuk Portal
            </h1>
            <p class="text-xs font-semibold text-slate uppercase tracking-wider">SMK NU Wargabinangun</p>
        </div>

        <?php Flasher::flash(); ?>

        <!-- Login Form -->
        <form action="<?= BASEURL; ?>/auth/login" method="POST" class="space-y-4">
            <?= CsrfHelper::input() ?>

            <!-- Username Field -->
            <div>
                <label for="username" class="block text-xs font-bold text-obsidian uppercase tracking-wider mb-2">Username</label>
                <div class="relative">
                    <input type="text"
                           id="username"
                           name="username"
                           placeholder="Masukkan username atau NIS"
                           autocomplete="username"
                           required
                           autofocus
                           class="wise-input pl-11">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-pebble">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-xs font-bold text-obsidian uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Masukkan password Anda"
                           autocomplete="current-password"
                           required
                           class="wise-input pl-11 pr-11">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-pebble">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </div>
                    <button type="button"
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-pebble hover:text-forest transition-colors"
                            aria-label="Tampilkan password">
                        <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button (Pill Lime) -->
            <div class="pt-2">
                <button type="submit" class="btn-pill-lime">
                    <span>Masuk Sekarang</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>

        <!-- Back Link -->
        <div class="text-center pt-4 border-t border-fog">
            <a href="<?= BASEURL; ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-forest underline decoration-1 underline-offset-4 hover:text-spruce transition-all group">
                <span class="group-hover:-translate-x-1 transition-transform duration-200 inline-block">&larr;</span>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </div>

    <!-- Footer Copyright -->
    <div class="mt-8 text-center text-xs text-slate">
        <p>&copy; 2026 SMK NU Wargabinangun. Powered by Nihlah.Auliya</p>
    </div>

    <!-- Password Toggle Script -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
    
    <!-- PWA Service Worker script -->
    <script>
        const BASE_URL = '<?= BASEURL; ?>';
    </script>
    <script src="<?= BASEURL; ?>/assets/js/pwa.js"></script>
</body>
</html>
