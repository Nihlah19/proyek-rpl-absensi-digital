/**
 * Presensi RFID & AI - Realtime Notification Engine v2.0
 * Mengelola Izin Notifikasi HP, Web Push PWA, Polling Realtime,
 * Suara Pop-up Beranimasi, In-App Floating Toasts, dan Status Izin Browser.
 */
(function() {
    'use strict';

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

    const BASE = getBaseUrl();
    let lastNotifId = parseInt(localStorage.getItem('absensi_last_notif_id') || '0', 10);
    let pollTimer = null;
    let sharedAudioCtx = null;

    // Web Audio Synthesizer: Suara notifikasi lembut khas WA/Slack
    function getAudioCtx() {
        if (!sharedAudioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                sharedAudioCtx = new AudioContext();
            }
        }
        return sharedAudioCtx;
    }

    function unlockAudio() {
        const ctx = getAudioCtx();
        if (ctx && ctx.state === 'suspended') {
            ctx.resume().catch(function() {});
        }
    }

    // Aktifkan Audio Context saat pertama kali layar disentuh / diklik
    ['click', 'touchstart', 'keydown'].forEach(function(evt) {
        document.addEventListener(evt, unlockAudio, { once: true, passive: true });
    });

    function playNotifChime() {
        try {
            // Getaran HP (jika didukung perangkat Android)
            if ('vibrate' in navigator) {
                try { navigator.vibrate([200, 100, 200]); } catch(e) {}
            }

            const ctx = getAudioCtx();
            if (!ctx) return;

            const triggerOscillators = function() {
                const now = ctx.currentTime;
                // Nada 1 (D5 587.33 Hz)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                gain1.gain.setValueAtTime(0, now);
                gain1.gain.linearRampToValueAtTime(0.24, now + 0.04);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.3);

                // Nada 2 (A5 880 Hz harmonic pop)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.1);
                gain2.gain.setValueAtTime(0, now + 0.1);
                gain2.gain.linearRampToValueAtTime(0.28, now + 0.14);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.1);
                osc2.stop(now + 0.48);
            };

            if (ctx.state === 'suspended') {
                ctx.resume().then(triggerOscillators).catch(function() {});
            } else {
                triggerOscillators();
            }
        } catch (e) {
            // Fallback diam
        }
    }

    // Modal Alert Kustom Cantik & Informatif
    function showCustomAlertModal(title, messageHtml, type) {
        const modalId = 'notif-custom-modal';
        const existing = document.getElementById(modalId);
        if (existing) existing.remove();

        let iconBg = '#ECFDF5';
        let iconColor = '#059669';
        let iconClass = 'fa-solid fa-circle-info';
        let btnBg = '#059669';

        if (type === 'danger') {
            iconBg = '#FEF2F2'; iconColor = '#DC2626'; iconClass = 'fa-solid fa-triangle-exclamation'; btnBg = '#DC2626';
        } else if (type === 'warning') {
            iconBg = '#FFFBEB'; iconColor = '#D97706'; iconClass = 'fa-solid fa-circle-exclamation'; btnBg = '#D97706';
        }

        const modal = document.createElement('div');
        modal.id = modalId;
        modal.style.cssText = 'position: fixed; inset: 0; z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px);';
        modal.innerHTML = `
            <div style="background: white; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); max-width: 440px; width: 100%; padding: 22px 24px; animation: slideUpModal 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: ${iconBg}; color: ${iconColor}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 18px;">
                        <i class="${iconClass}"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="font-size: 15px; font-weight: 700; color: #0F172A; margin: 0 0 6px 0;">${title}</h3>
                        <div style="font-size: 12px; color: #475569; line-height: 1.55;">${messageHtml}</div>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px;">
                    <button onclick="document.getElementById('${modalId}').remove()" style="background: ${btnBg}; color: white; border: none; padding: 8px 20px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer;">
                        Mengerti
                    </button>
                </div>
            </div>
            <style>
                @keyframes slideUpModal {
                    from { opacity: 0; transform: translateY(16px) scale(0.96); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
            </style>
        `;
        document.body.appendChild(modal);
    }

    // 1. Inisialisasi Permintaan Izin Notifikasi (Banner Ramah di Layar)
    function checkPermissionPrompt() {
        if (!('Notification' in window)) return;

        // Jangan tampilkan jika sudah diizinkan atau ditolak
        if (Notification.permission === 'granted' || Notification.permission === 'denied') {
            if (Notification.permission === 'granted') {
                registerPushSubscription();
            }
            updatePermissionStatusUI();
            return;
        }

        // Cek jika ditutup dalam 24 jam terakhir
        const dismissedAt = localStorage.getItem('absensi_notif_prompt_dismissed');
        const now = Date.now();
        if (dismissedAt && (now - parseInt(dismissedAt, 10)) < (24 * 60 * 60 * 1000)) {
            return;
        }

        setTimeout(renderPermissionBanner, 2500);
    }

    function renderPermissionBanner() {
        if (document.getElementById('notif-permission-banner')) return;

        const banner = document.createElement('div');
        banner.id = 'notif-permission-banner';
        banner.className = 'fixed bottom-5 left-4 right-4 max-w-md mx-auto z-50 animate-slide-up';
        banner.innerHTML = `
            <div style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); border: 1.5px solid #10B981; border-radius: 20px; box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.28); padding: 16px 18px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: #ECFDF5; border: 1px solid #A7F3D0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #0F7A4A; font-size: 20px;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 700; color: #0F172A; margin-bottom: 3px;">Aktifkan Notifikasi Layar</div>
                        <div style="font-size: 12px; color: #64748B; line-height: 1.45; margin-bottom: 12px;">
                            Dapatkan pop-up saat absensi scan berhasil, izin disetujui, atau ada catatan penting dari guru/kepsek.
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button id="btnAllowNotif" style="background: #0F7A4A; color: white; border: none; padding: 7px 16px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(15,122,74,0.3);">
                                <i class="fa-solid fa-check"></i> Aktifkan
                            </button>
                            <button id="btnDismissNotif" style="background: #F1F5F9; color: #64748B; border: none; padding: 7px 14px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                Nanti Saja
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(banner);

        document.getElementById('btnAllowNotif').addEventListener('click', function() {
            unlockAudio();
            requestNotificationPermission();
            banner.remove();
        });

        document.getElementById('btnDismissNotif').addEventListener('click', function() {
            localStorage.setItem('absensi_notif_prompt_dismissed', Date.now().toString());
            banner.remove();
        });
    }

    // 2. Minta Izin Notifikasi ke Browser / HP
    window.requestNotificationPermission = function(callback) {
        unlockAudio();

        if (!('Notification' in window)) {
            showCustomAlertModal(
                'Perangkat Tidak Mendukung Web Notification',
                'Browser perangkat Anda tidak mendukung API notifikasi layar sistem (OS).<br><br>Namun <strong>Pop-up In-App (di dalam halaman) dan Suara</strong> akan tetap bekerja secara otomatis saat website dibuka.',
                'warning'
            );
            if (typeof callback === 'function') callback(false);
            return;
        }

        if (Notification.permission === 'denied') {
            showCustomAlertModal(
                'Izin Notifikasi Diblokir di Browser',
                'Izin notifikasi saat ini berstatus <strong>Ditolak (Blocked)</strong> di browser Anda.<br><br>' +
                '<strong>Cara Membuka Blokir:</strong><br>' +
                '1. Klik ikon <strong>gembok / pengaturan situs</strong> di sebelah kiri URL address bar (atas).<br>' +
                '2. Cari menu <strong>Notifikasi / Permissions</strong>.<br>' +
                '3. Ubah dari <em>Blokir / Block</em> menjadi <strong>Izinkan / Allow</strong>.<br>' +
                '4. Muat ulang (refresh) halaman ini.',
                'danger'
            );
            if (typeof callback === 'function') callback(false);
            return;
        }

        Notification.requestPermission().then(function(permission) {
            updatePermissionStatusUI();
            if (permission === 'granted') {
                registerPushSubscription();
                playNotifChime();
                showDevicePopUp({
                    id: 'perm-granted-' + Date.now(),
                    judul: '✅ Pop-up Notifikasi Berhasil Diaktifkan!',
                    pesan: 'Perangkat Anda siap menerima pop-up kehadiran, catatan, dan izin secara realtime.',
                    tipe: 'general',
                    url: BASE + '/dashboard'
                });
                if (typeof callback === 'function') callback(true);
            } else if (permission === 'denied') {
                showCustomAlertModal(
                    'Izin Pop-up Ditolak',
                    'Anda memilih untuk tidak mengizinkan pop-up sistem. Anda tetap dapat melihat notifikasi melalui lonceng di pojok atas.',
                    'warning'
                );
                if (typeof callback === 'function') callback(false);
            }
        });
    };

    // Update Status Badge di Dropdown
    function updatePermissionStatusUI() {
        const badge = document.getElementById('notifPermStatusBadge');
        if (!badge) return;

        if (!('Notification' in window)) {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500';
            badge.textContent = 'Tidak Didukung';
            return;
        }

        if (Notification.permission === 'granted') {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700';
            badge.textContent = '🟢 Aktif';
        } else if (Notification.permission === 'denied') {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700';
            badge.textContent = '🔴 Diblokir';
        } else {
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700';
            badge.textContent = '🟡 Belum Diatur';
        }
    }

    // 3. Daftarkan Push Subscription ke Backend
    function registerPushSubscription() {
        if (!('serviceWorker' in navigator)) return;

        navigator.serviceWorker.getRegistration().then(function(reg) {
            if (!reg) return;
            if (reg.pushManager) {
                reg.pushManager.getSubscription().then(function(sub) {
                    if (sub) {
                        sendSubscriptionToServer(sub);
                    } else {
                        const endpoint = 'native-pwa-' + navigator.userAgent.replace(/[^a-zA-Z0-9]/g, '').slice(0, 30) + '-' + Date.now();
                        sendSubscriptionToServer({ endpoint: endpoint });
                    }
                }).catch(function() {
                    const fallbackEndpoint = 'browser-client-' + Math.random().toString(36).substring(2);
                    sendSubscriptionToServer({ endpoint: fallbackEndpoint });
                });
            }
        }).catch(function() {});
    }

    function sendSubscriptionToServer(sub) {
        let payload = {
            endpoint: sub.endpoint || 'client-endpoint'
        };
        if (sub.toJSON) {
            payload = sub.toJSON();
        }

        fetch(BASE + '/api/v1/notifikasi.php?action=subscribe', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(function(res) {
            return res.json();
        }).then(function(json) {
            // Subscription berhasil disimpan
        }).catch(function() {});
    }

    // 4. Pop-up Notifikasi Sistem di HP / Laptop + In-App Toast
    function showDevicePopUp(item) {
        const title = item.judul || 'Presensi SMK NU Wargabinangun';
        const targetUrl = item.url ? (item.url.startsWith('http') ? item.url : (BASE + '/' + item.url.replace(/^\//, ''))) : (BASE + '/dashboard');

        // 1. Tampilkan selalu In-App Toast & Mainkan Suara (Bekerja 100% di semua device & browser)
        showInAppToast(item);
        playNotifChime();

        // 2. Tampilkan Pop-Up Native OS / Desktop jika izin browser sudah diberikan
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const notif = new Notification(title, {
                    body: item.pesan || '',
                    icon: BASE + '/assets/img/icon-192.png',
                    badge: BASE + '/assets/img/icon-192.png',
                    tag: 'notif-' + (item.id || Date.now()),
                    data: { url: targetUrl }
                });
                notif.onclick = function() {
                    window.focus();
                    if (targetUrl && targetUrl !== '#') window.location.href = targetUrl;
                };
            } catch (err) {
                // Di beberapa browser Android, 'new Notification()' dilarang dan memerlukan ServiceWorker
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistration().then(function(reg) {
                        if (reg && reg.showNotification) {
                            reg.showNotification(title, {
                                body: item.pesan || '',
                                icon: BASE + '/assets/img/icon-192.png',
                                badge: BASE + '/assets/img/icon-192.png',
                                tag: 'notif-' + (item.id || Date.now()),
                                vibrate: [250, 100, 250, 100, 250],
                                data: { url: targetUrl }
                            });
                        }
                    }).catch(function() {});
                }
            }
        }
    }

    // Container untuk In-App Toast Stacking
    function getToastContainer() {
        let container = document.getElementById('notifToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifToastContainer';
            container.style.cssText = 'position: fixed; top: 76px; right: 16px; z-index: 99999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; max-width: 380px; width: calc(100% - 32px);';
            document.body.appendChild(container);
        }
        return container;
    }

    // 5. In-App Floating Toast Mengambang di Layar
    function showInAppToast(item) {
        const container = getToastContainer();
        const toastId = 'toast-notif-' + (item.id || Date.now());
        
        // Hindari duplikat toast yang sama
        if (document.getElementById(toastId)) return;

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.style.cssText = 'pointer-events: auto; transform: translateY(-16px); opacity: 0; transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;';

        let iconBg = '#ECFDF5';
        let iconColor = '#059669';
        let iconClass = 'fa-solid fa-bell';

        const tipe = (item.tipe || '').toLowerCase();
        if (tipe.includes('masuk') || tipe.includes('hadir')) {
            iconBg = '#ECFDF5'; iconColor = '#059669'; iconClass = 'fa-solid fa-circle-check';
        } else if (tipe.includes('pulang_cepat')) {
            if (tipe.includes('approved')) {
                iconBg = '#ECFDF5'; iconColor = '#059669'; iconClass = 'fa-solid fa-circle-check';
            } else if (tipe.includes('rejected')) {
                iconBg = '#FEF2F2'; iconColor = '#DC2626'; iconClass = 'fa-solid fa-circle-xmark';
            } else {
                iconBg = '#FFFBEB'; iconColor = '#D97706'; iconClass = 'fa-solid fa-clock-rotate-left';
            }
        } else if (tipe.includes('pulang')) {
            iconBg = '#EFF6FF'; iconColor = '#2563EB'; iconClass = 'fa-solid fa-right-from-bracket';
        } else if (tipe.includes('catatan')) {
            iconBg = '#EEF2FF'; iconColor = '#4F46E5'; iconClass = 'fa-solid fa-comment-dots';
        }

        const targetUrl = item.url ? (item.url.startsWith('http') ? item.url : (BASE + '/' + item.url.replace(/^\//, ''))) : '#';

        toast.innerHTML = `
            <div style="background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(16px); border-radius: 16px; border: 1.5px solid #E2E8F0; box-shadow: 0 20px 40px -12px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(0,0,0,0.03); padding: 14px 16px; display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                <div style="width: 38px; height: 38px; border-radius: 12px; background: ${iconBg}; color: ${iconColor}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 17px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                    <i class="${iconClass}"></i>
                </div>
                <div style="flex: 1; min-width: 0;" onclick="handleNotifItemClick(${item.id ? "'" + item.id + "'" : "null"}, '${targetUrl}')">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 2px;">
                        <span style="font-size: 13px; font-weight: 700; color: #0F172A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.judul || 'Notifikasi'}</span>
                        <span style="font-size: 9.5px; font-weight: 700; color: #059669; background: #ECFDF5; padding: 2px 6px; border-radius: 999px; border: 1px solid #A7F3D0; flex-shrink: 0;">Baru</span>
                    </div>
                    <div style="font-size: 12px; color: #475569; line-height: 1.45; word-break: break-word;">${item.pesan || ''}</div>
                </div>
                <button onclick="dismissToast('${toastId}')" style="background: transparent; border: none; color: #94A3B8; font-size: 14px; cursor: pointer; padding: 4px; border-radius: 6px;" title="Tutup">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;

        container.appendChild(toast);

        // Animasi masuk
        requestAnimationFrame(function() {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });

        // Hapus otomatis setelah 7 detik
        setTimeout(function() {
            dismissToast(toastId);
        }, 7000);
    }

    window.dismissToast = function(id) {
        const toast = document.getElementById(id);
        if (toast) {
            toast.style.transform = 'translateY(-16px)';
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }
    };

    // Tracking ID notifikasi yang sudah dipopupkan di sesi ini
    function getShownIds() {
        try {
            return JSON.parse(sessionStorage.getItem('absensi_shown_notif_ids') || '[]');
        } catch(e) {
            return [];
        }
    }

    function recordShownId(id) {
        if (!id) return;
        const shown = getShownIds();
        if (shown.indexOf(id) === -1) {
            shown.push(id);
            if (shown.length > 50) shown.shift();
            sessionStorage.setItem('absensi_shown_notif_ids', JSON.stringify(shown));
        }
    }

    // 6. Polling Berkala untuk Notifikasi Baru
    function fetchNotifications(isFirstLoad) {
        fetch(BASE + '/api/v1/notifikasi.php?action=list&limit=15')
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (!json.ok) return;

                updateBellBadge(json.count || 0);

                if (Array.isArray(json.data) && json.data.length > 0) {
                    const shownIds = getShownIds();
                    const latestItem = json.data[0];
                    const currentTopId = parseInt(latestItem.id, 10);

                    // Filter item yang belum dibaca
                    const unreadItems = json.data.filter(function(i) {
                        return parseInt(i.is_read, 10) === 0;
                    });

                    if (isFirstLoad) {
                        // Di first load: jangan pop-up suara / banner untuk notifikasi lama yang sudah berhari-hari
                        // Hanya pop-up jika notifikasi benar-benar baru (kurang dari 5 menit lalu)
                        if (unreadItems.length > 0) {
                            const topUnread = unreadItems[0];
                            const notifTime = topUnread.created_at ? new Date(topUnread.created_at.replace(' ', 'T')).getTime() : 0;
                            const isVeryRecent = notifTime > 0 && ((Date.now() - notifTime) < (5 * 60 * 1000));
                            if (isVeryRecent && shownIds.indexOf(topUnread.id) === -1) {
                                recordShownId(topUnread.id);
                                showDevicePopUp(topUnread);
                            }
                        }
                    } else {
                        // Polling realtime: Tampilkan pop-up untuk setiap notifikasi baru
                        const newItems = json.data.filter(function(i) {
                            const id = parseInt(i.id, 10);
                            const unread = parseInt(i.is_read, 10) === 0;
                            return unread && (id > lastNotifId || shownIds.indexOf(id) === -1);
                        });

                        newItems.forEach(function(item) {
                            recordShownId(item.id);
                            showDevicePopUp(item);
                        });
                    }

                    if (currentTopId > lastNotifId) {
                        lastNotifId = currentTopId;
                        localStorage.setItem('absensi_last_notif_id', lastNotifId.toString());
                    }

                    renderDropdownItems(json.data);
                } else {
                    renderEmptyDropdown();
                }
            })
            .catch(function(e) {
                // Silently handle offline
            });
    }

    function updateBellBadge(unreadCount) {
        const badge = document.getElementById('notifBadge');
        const badgeCount = document.getElementById('notifBadgeCount');
        if (!badge) return;

        if (unreadCount > 0) {
            badge.classList.remove('hidden');
            if (badgeCount) {
                badgeCount.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badgeCount.classList.remove('hidden');
            }
        } else {
            badge.classList.add('hidden');
            if (badgeCount) badgeCount.classList.add('hidden');
        }
    }

    function renderDropdownItems(items) {
        const listContainer = document.getElementById('notifDropdownList');
        if (!listContainer) return;

        if (items.length === 0) {
            renderEmptyDropdown();
            return;
        }

        let html = '';
        items.forEach(function(item) {
            const isUnread = parseInt(item.is_read, 10) === 0;
            const bgClass = isUnread 
                ? 'bg-[#ecf6e8]/60 dark:bg-[#1b2b1f]/50 hover:bg-[#ecf6e8] dark:hover:bg-[#1b2b1f]' 
                : 'hover:bg-slate-50/80 dark:hover:bg-[#162319]';
            const dotIndicator = isUnread 
                ? '<span class="w-2 h-2 rounded-full bg-[#10b981] shrink-0" title="Belum dibaca"></span>' 
                : '';
            const targetUrl = item.url ? (item.url.startsWith('http') ? item.url : (BASE + '/' + item.url.replace(/^\//, ''))) : '#';

            let iconClass = 'fa-solid fa-bell text-[#2e6d22] dark:text-[#9fe870]';
            let iconBg = 'bg-[#ecf6e8] dark:bg-[#1b2b1f]';
            const tipe = (item.tipe || '').toLowerCase();
            if (tipe.includes('masuk') || tipe.includes('hadir')) {
                iconClass = 'fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400';
                iconBg = 'bg-emerald-50 dark:bg-emerald-950/60';
            } else if (tipe.includes('pulang_cepat')) {
                iconClass = 'fa-solid fa-clock-rotate-left text-amber-600 dark:text-amber-400';
                iconBg = 'bg-amber-50 dark:bg-amber-950/60';
            } else if (tipe.includes('pulang')) {
                iconClass = 'fa-solid fa-right-from-bracket text-sky-600 dark:text-sky-400';
                iconBg = 'bg-sky-50 dark:bg-sky-950/60';
            } else if (tipe.includes('catatan')) {
                iconClass = 'fa-solid fa-comment-dots text-indigo-600 dark:text-indigo-400';
                iconBg = 'bg-indigo-50 dark:bg-indigo-950/60';
            }

            html += `
                <div class="p-3.5 border-b border-slate-100 dark:border-[#233527]/70 flex items-start gap-3 transition-colors cursor-pointer ${bgClass}" onclick="handleNotifItemClick(${item.id}, '${targetUrl}')">
                    <div class="w-8 h-8 rounded-xl ${iconBg} flex items-center justify-center shrink-0 text-xs shadow-2xs">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1.5 mb-0.5">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">${item.judul || 'Notifikasi'}</span>
                            ${dotIndicator}
                        </div>
                        <p class="text-[11.5px] text-slate-600 dark:text-slate-300 break-words leading-relaxed line-clamp-2">${item.pesan || ''}</p>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block font-medium">${timeAgo(item.created_at)}</span>
                    </div>
                </div>
            `;
        });

        listContainer.innerHTML = html;
    }

    function renderEmptyDropdown() {
        const listContainer = document.getElementById('notifDropdownList');
        if (!listContainer) return;
        listContainer.innerHTML = `
            <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                <i class="fa-regular fa-bell-slash text-2xl mb-2 text-slate-300 dark:text-slate-600"></i>
                <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Belum ada notifikasi baru</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Pemberitahuan presensi dan izin akan muncul di sini</p>
            </div>
        `;
    }

    function timeAgo(dateString) {
        if (!dateString) return '';
        const d = new Date(dateString.replace(/-/g, '/'));
        const seconds = Math.floor((new Date() - d) / 1000);
        if (seconds < 60) return 'Baru saja';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + ' mnt lalu';
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + ' jam lalu';
        const days = Math.floor(hours / 24);
        return days + ' hari lalu';
    }

    window.handleNotifItemClick = function(id, url) {
        if (id && !isNaN(parseInt(id, 10)) && !String(id).startsWith('test-')) {
            fetch(BASE + '/api/v1/notifikasi.php?action=mark_read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notif_id: id })
            }).finally(function() {
                if (url && url !== '#' && url !== 'javascript:;') {
                    window.location.href = url;
                } else {
                    fetchNotifications(false);
                }
            });
        } else if (url && url !== '#' && url !== 'javascript:;') {
            window.location.href = url;
        }
    };

    window.markAllNotifAsRead = function() {
        fetch(BASE + '/api/v1/notifikasi.php?action=mark_read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        }).then(function() {
            updateBellBadge(0);
            fetchNotifications(false);
        });
    };

    window.toggleNotifDropdown = function() {
        const menu = document.getElementById('notifDropdownMenu');
        if (!menu) return;
        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden')) {
            updatePermissionStatusUI();
        }
    };

    // 7. Tes Pop-up & Suara Notifikasi Secara Langsung (Trigger Satu Klik)
    window.testNotification = function() {
        unlockAudio();
        
        // A. Tampilkan preview In-App Toast & Mainkan Suara Segera
        const testItem = {
            id: 'test-' + Date.now(),
            judul: '🔔 Tes Pop-up Presensi RFID',
            pesan: 'Notifikasi pop-up dan suara berhasil berbunyi di perangkat Anda! (' + (new Date()).toLocaleTimeString() + ')',
            tipe: 'absen_masuk',
            url: BASE + '/dashboard'
        };
        showDevicePopUp(testItem);

        // B. Kirim tes ke server agar tercatat riil di database dan sinkron dengan lonceng
        fetch(BASE + '/api/v1/notifikasi.php?action=test', {
            method: 'POST'
        }).then(function(res) { return res.json(); })
          .then(function(data) {
              if (data && data.ok) {
                  fetchNotifications(false);
              }
          }).catch(function() {});

        // C. Cek status OS notification
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                setTimeout(function() {
                    requestNotificationPermission();
                }, 800);
            } else if (Notification.permission === 'denied') {
                setTimeout(function() {
                    showCustomAlertModal(
                        'Pop-up Layar OS Masih Terblokir',
                        'Pop-up In-App (di dalam halaman) dan suara <strong>BERHASIL</strong> muncul.<br><br>' +
                        'Namun pop-up layar luar (OS desktop / HP tray) masih diblokir oleh browser. Klik ikon <strong>gembok / pengaturan situs</strong> di sebelah kiri URL untuk mengubah Notifikasi ke <strong>Izinkan / Allow</strong>.',
                        'warning'
                    );
                }, 600);
            }
        }
    };

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', function(e) {
        const container = document.getElementById('notifDropdownContainer');
        const menu = document.getElementById('notifDropdownMenu');
        if (container && menu && !container.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    // Mulai saat halaman siap
    window.addEventListener('load', function() {
        checkPermissionPrompt();
        fetchNotifications(true);
        updatePermissionStatusUI();

        // Polling setiap 12 detik
        pollTimer = setInterval(function() {
            fetchNotifications(false);
        }, 12000);
    });

})();
