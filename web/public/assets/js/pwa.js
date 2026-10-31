/**
 * PWA Helper & Service Worker Registration
 * Sistem Absensi SMK NU Wargabinangun
 * Note: Install prompt popup has been disabled per user request.
 */
(function() {
    'use strict';

    // Helper to get base URL dynamically
    function getBaseUrl() {
        if (typeof BASE_URL !== 'undefined' && BASE_URL) return BASE_URL;
        const metaBase = document.querySelector('meta[name="base-url"]');
        if (metaBase && metaBase.getAttribute('content')) return metaBase.getAttribute('content');
        const pathParts = window.location.pathname.split('/');
        const pubIndex = pathParts.indexOf('public');
        if (pubIndex !== -1) {
            return window.location.origin + pathParts.slice(0, pubIndex + 1).join('/');
        }
        return window.location.origin;
    }

    // 1. Register Service Worker (Offline caching & PWA support without annoying popups)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            const swUrl = getBaseUrl() + '/sw.js';
            navigator.serviceWorker.register(swUrl)
                .then(function(registration) {
                    // Service Worker registered quietly
                })
                .catch(function(error) {
                    console.warn('[PWA] Service Worker registration failed:', error);
                });
        });
    }

    // 2. Prevent default browser install prompts and do NOT show custom install banner
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        // Prompt is blocked and suppressed so it does not disturb users
    });
})();
