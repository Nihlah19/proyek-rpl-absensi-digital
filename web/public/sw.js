const CACHE_NAME = 'absensi-smk-v2.0';
const STATIC_ASSETS = [
    './offline.html',
    './manifest.json',
    './assets/img/logo.png',
    './assets/img/logo.webp',
    './assets/img/icon-192.png',
    './assets/img/icon-512.png',
    './assets/img/apple-touch-icon.png',
    './assets/css/tailwind.css',
    './assets/js/pwa.js',
    './assets/js/notification.js',
    './assets/js/script.js',
    './assets/vendor/tailwindcss/tailwind.min.js',
    './assets/vendor/fontawesome/css/all.min.css',
    './assets/vendor/fontawesome/webfonts/fa-solid-900.woff2',
    './assets/vendor/fontawesome/webfonts/fa-regular-400.woff2',
    './assets/vendor/fontawesome/webfonts/fa-brands-400.woff2',
    './assets/vendor/fonts/plus-jakarta-sans/plus-jakarta-sans.css',
    './assets/vendor/fonts/plus-jakarta-sans/pjs-1.woff2',
    './assets/vendor/fonts/plus-jakarta-sans/pjs-2.woff2',
    './assets/vendor/fonts/plus-jakarta-sans/pjs-3.woff2',
    './assets/vendor/fonts/plus-jakarta-sans/pjs-4.woff2',
    './assets/vendor/chartjs/chart.min.js',
    './assets/vendor/apexcharts/apexcharts.min.js'
];

// 1. Install Event: Cache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// 2. Activate Event: Clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. Fetch Event Strategy:
// - Navigation requests (pages): Network-First, fallback to offline.html
// - Static assets (images/css/js): Stale-While-Revalidate
// - API calls: Network-Only
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests (POST/PUT/DELETE)
    if (request.method !== 'GET') {
        return;
    }

    // Skip API and Chatbot endpoints (must be real-time network-only)
    if (url.pathname.includes('/api/') || url.pathname.includes('/chatbot')) {
        return;
    }

    // Strategy A: HTML navigation requests (pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    // Update cache with fresh copy if valid
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Offline fallback: try cache, or fallback to offline.html
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    const offlineFallback = await caches.match('./offline.html') || await caches.match('offline.html');
                    if (offlineFallback) {
                        return offlineFallback;
                    }
                    return new Response('<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Offline</title></head><body style="font-family:sans-serif;text-align:center;padding:40px;"><h2>Aplikasi Sedang Offline</h2><p>Pastikan HP terhubung ke internet.</p><button onclick="location.reload()">Muat Ulang</button></body></html>', {
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // Strategy B: Static assets (images, css, js, icons, fonts)
    if (
        request.destination === 'image' ||
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'font'
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                }).catch(() => {/* ignore network errors for background update */});

                // Return cached version immediately if available, otherwise wait for network
                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Default: Network with cache fallback
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

// 4. Push Event: Pop-up Notifikasi HP Layar Kunci / Status Bar (WhatsApp style)
self.addEventListener('push', function(event) {
    let payload = {
        title: 'Presensi SMK NU Wargabinangun',
        body: 'Ada notifikasi baru untuk Anda.',
        url: './dashboard',
        icon: './assets/img/icon-192.png',
        badge: './assets/img/icon-192.png',
        tag: 'absensi-notification',
        vibrate: [250, 100, 250, 100, 250],
        requireInteraction: true
    };

    if (event.data) {
        try {
            const data = event.data.json();
            payload.title = data.title || payload.title;
            payload.body = data.body || payload.body;
            if (data.url) payload.url = data.url;
            if (data.icon) payload.icon = data.icon;
            if (data.tag) payload.tag = data.tag;
        } catch (e) {
            payload.body = event.data.text() || payload.body;
        }
    }

    const options = {
        body: payload.body,
        icon: payload.icon,
        badge: payload.badge,
        tag: payload.tag + '-' + Date.now(),
        renotify: true,
        vibrate: payload.vibrate,
        requireInteraction: true,
        data: {
            url: payload.url,
            dateOfArrival: Date.now()
        },
        actions: [
            { action: 'open', title: 'Buka Aplikasi' },
            { action: 'close', title: 'Tutup' }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// 5. Notification Click Event: Klik pop-up langsung buka halaman terkait
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    const targetUrl = (event.notification.data && event.notification.data.url) 
        ? event.notification.data.url 
        : './dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

