<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Donat Wawa — Authentic Artisanal Donuts')</title>
    @php
        $i18n = [
            'locale' => app()->getLocale() === 'id' ? 'id-ID' : 'en-US',
            'item' => __('Item'),
            'processing' => __('Processing...'),
            'proceed' => __('Proceed to Payment'),
            'paymentFailed' => __('Payment failed, please try again.'),
            'paymentNotReceived' => __('Payment has not been received yet. Please try again.'),
            'somethingWrong' => __('Something went wrong, please try again.'),
            'verifyingPayment' => __('Verifying your payment...'),
            'sameDay' => __('Same-Day'),
            'scheduled' => __('Scheduled Order'),
            'paymentSuccess' => __('Payment Success'),
            'awaitingPayment' => __('Awaiting Payment'),
            'saving' => __('Saving...'),
            'directions' => __('Get directions'),
            'remove' => __('Remove'),
        ];
    @endphp
    <script>
        window.isAuthenticated = @json(auth()->check());
        window.I18N = @json($i18n);
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/base.css', 'resources/css/home.css', 'resources/css/auth.css', 'resources/js/app.js'])
    @stack('styles')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
    @auth
        <style>
            /* Notifications: the floating card (snackbar) and the bell dropdown in the navbar */
            .dw-snack {
                position: fixed;
                top: 84px;
                right: 24px;
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 14px;
                width: 380px;
                max-width: calc(100vw - 24px);
                box-sizing: border-box;
                padding: 14px 14px 14px 16px;
                background: radial-gradient(140% 200% at 0% 100%, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 55%), var(--primary);
                color: #fff;
                border: 1.5px solid rgba(255, 255, 255, 0.92);
                border-radius: 26px;
                box-shadow: 0 0 0 5px rgba(123, 32, 38, 0.14), 0 18px 36px rgba(46, 12, 14, 0.3);
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                transform: translateY(-12px);
                transition: transform .35s cubic-bezier(.2, .8, .2, 1), opacity .25s ease, visibility 0s linear .35s;
            }

            .dw-snack.is-open {
                opacity: 1;
                visibility: visible;
                transform: none;
                transition-delay: 0s;
            }

            .dw-snack-link {
                position: absolute;
                inset: 0;
                border-radius: inherit;
            }

            .dw-snack-icon {
                flex: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.14);
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            .dw-snack-text {
                min-width: 0;
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .dw-snack-text strong {
                font-size: 15px;
                font-weight: 700;
                line-height: 1.35;
                color: #fff;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .dw-snack-text small {
                font-size: 13px;
                line-height: 1.4;
                color: rgba(255, 255, 255, 0.78);
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                word-break: break-word;
            }

            .dw-snack-bar {
                position: absolute;
                left: 0;
                bottom: 0;
                height: 3px;
                width: 100%;
                background: rgba(255, 255, 255, 0.55);
                pointer-events: none;
            }

            .dw-snack-close {
                position: relative;
                z-index: 1;
                flex: none;
                width: 34px;
                height: 34px;
                padding: 0;
                border: 0;
                border-radius: 50%;
                background: #fff;
                color: var(--primary);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: transform .15s ease;
            }

            .dw-snack-close:hover {
                transform: scale(1.08);
            }

            .notif-menu {
                position: relative;
            }

            .notif-btn {
                position: relative;
            }

            .notif-badge {
                position: absolute;
                top: -3px;
                right: -4px;
                min-width: 16px;
                height: 16px;
                padding: 0 4px;
                box-sizing: border-box;
                border-radius: 999px;
                background: var(--primary);
                color: #fff;
                font: 700 10px/16px 'Plus Jakarta Sans', sans-serif;
                text-align: center;
                pointer-events: none;
            }

            .notif-panel {
                position: absolute;
                top: calc(100% + 12px);
                right: 0;
                width: 400px;
                max-width: calc(100vw - 24px);
                background: #fff;
                border-radius: 20px;
                box-shadow: 0 16px 40px rgba(46, 12, 14, 0.16), 0 0 0 1px rgba(46, 12, 14, 0.05);
                z-index: 60;
                overflow: hidden;
            }

            .notif-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 16px 14px 18px;
                border-bottom: 1px solid #efe7e3;
            }

            .notif-head strong {
                font-size: 16px;
                font-weight: 700;
                color: #2e1e1a;
            }

            .notif-tabs {
                display: flex;
                gap: 4px;
            }

            .notif-tab {
                border: 0;
                background: transparent;
                color: var(--neutral-600);
                font: inherit;
                font-size: 12.5px;
                font-weight: 600;
                padding: 7px 14px;
                border-radius: 999px;
                cursor: pointer;
                transition: background .15s ease, color .15s ease;
            }

            .notif-tab:hover {
                background: #f3efed;
                color: #2e1e1a;
            }

            .notif-tab.is-active,
            .notif-tab.is-active:hover {
                background: var(--primary);
                color: #fff;
                box-shadow: 0 4px 10px rgba(123, 32, 38, 0.28);
            }

            .notif-list {
                max-height: 420px;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #e3d2ce transparent;
            }

            .notif-list::-webkit-scrollbar {
                width: 6px;
            }

            .notif-list::-webkit-scrollbar-thumb {
                background: #e3d2ce;
                border-radius: 6px;
            }

            .notif-item {
                display: flex;
                align-items: center;
                gap: 14px;
                padding: 18px 20px;
                color: inherit;
                text-decoration: none;
                transition: background .15s ease;
            }

            .notif-item+.notif-item {
                border-top: 1px solid #f1eae7;
            }

            .notif-item:hover {
                background: #faf6f4;
            }

            .notif-icon {
                position: relative;
                flex: none;
                width: 40px;
                height: 40px;
                border-radius: 12px;
                background: var(--primary);
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .notif-item.is-unread .notif-icon::after {
                content: "";
                position: absolute;
                top: -4px;
                right: -4px;
                width: 8px;
                height: 8px;
                box-sizing: content-box;
                border-radius: 50%;
                background: #e0505c;
                border: 2px solid #fff;
            }

            .notif-item-body {
                min-width: 0;
                flex: 1;
            }

            .notif-item-body p {
                margin: 0;
            }

            .notif-item-top {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 8px;
            }

            .notif-item-title {
                font-size: 13.5px;
                font-weight: 600;
                line-height: 1.4;
                color: #2e1e1a;
            }

            .notif-item-time {
                flex: none;
                font-size: 11.5px;
                line-height: 1.4;
                color: #9a908b;
                white-space: nowrap;
            }

            .notif-item-text {
                margin-top: 5px !important;
                font-size: 12.5px;
                line-height: 1.45;
                color: #5c4f4b;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                word-break: break-word;
            }

            .notif-item-note {
                margin-top: 4px !important;
                font-size: 12.5px;
                line-height: 1.4;
                color: #b3261e;
            }

            .notif-foot {
                display: flex;
                justify-content: center;
                padding: 6px 8px;
                border-top: 1px solid #f1eae7;
            }

            .notif-readall {
                border: 0;
                background: transparent;
                color: var(--primary);
                font-size: 12.5px;
                font-weight: 700;
                cursor: pointer;
                padding: 8px 14px;
                border-radius: 8px;
            }

            .notif-readall:hover {
                background: rgba(123, 32, 38, 0.08);
            }

            .notif-readall:disabled {
                opacity: .5;
                cursor: default;
                background: transparent;
            }

            .notif-empty {
                margin: 0;
                padding: 32px 16px 36px;
                text-align: center;
                font-size: 13px;
                color: var(--neutral-600);
            }

            @media (max-width: 640px) {
                .dw-snack {
                    top: 72px;
                    left: 12px;
                    right: 12px;
                    width: auto;
                    max-width: none;
                }

                .notif-panel {
                    position: fixed;
                    top: 72px;
                    left: 12px;
                    right: 12px;
                    width: auto;
                    max-width: none;
                }
            }
        </style>
    @endauth
</head>

<body class="@yield('body-class')">

    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    @unless (View::hasSection('hide-footer'))
        @include('partials.footer')
    @endunless

    @guest
        @include('popups.auth-overlay')
    @endguest

    @auth
        <div class="dw-snack" id="dwSnack" role="status" aria-live="polite">
            <span class="dw-snack-icon" id="dwSnackIcon" aria-hidden="true"></span>
            <span class="dw-snack-text">
                <strong id="dwSnackTitle"></strong>
                <small id="dwSnackBody"></small>
            </span>
            <a href="#" class="dw-snack-link" id="dwSnackLink" aria-label="{{ __('View') }}"></a>
            <span class="dw-snack-bar" id="dwSnackBar"></span>
            <button type="button" class="dw-snack-close" id="dwSnackClose" aria-label="{{ __('Close') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endauth

    <script>
        /* Keeps a part of the page fresh without a reload: asks for the same page again every few seconds and swaps
           the element in only when its data-live-version changed. Returns the poll function (call it to refresh now). */
        window.dwLiveRefresh = function (selector, opts) {
            opts = opts || {};
            var interval = opts.interval || 5000;
            // Never swap the page under an open confirm dialog: its form would be thrown away.
            var skip = opts.skip || function () {
                var modal = document.getElementById('dw-confirm');
                return !!modal && !modal.hidden;
            };
            var timer = null;
            var busy = false;

            async function poll() {
                if (busy) { return; }
                busy = true;
                try {
                    var current = document.querySelector(selector);
                    if (!current) { return; }
                    var res = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });
                    if (!res.ok) { return; }
                    var doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                    var fresh = doc.querySelector(selector);
                    // No target in the answer (session ended, error page...): leave the page alone.
                    if (!fresh || skip()) { return; }
                    if (fresh.dataset.liveVersion === current.dataset.liveVersion) { return; }
                    current.innerHTML = fresh.innerHTML;
                    current.dataset.liveVersion = fresh.dataset.liveVersion;
                    window.dispatchEvent(new CustomEvent('live:updated', { detail: { selector: selector } }));
                } catch (e) {
                } finally {
                    busy = false;
                }
            }

            function schedule() {
                clearTimeout(timer);
                timer = setTimeout(async function () {
                    if (!document.hidden) { await poll(); }
                    schedule();
                }, interval);
            }

            document.addEventListener('visibilitychange', function () { if (!document.hidden) { poll(); } });
            schedule();
            return poll;
        };
    </script>

    @auth
        @php
            $notifT = [
                'now' => __('Just now'),
                'sec' => __(':n s ago'),
                'min' => __(':n min ago'),
                'hour' => __(':n h ago'),
                'empty' => __('No notifications yet.'),
                'all' => __('All'),
                'unread' => __('Unread'),
                'caughtUp' => __('All caught up'),
            ];
        @endphp
        <script>
            /* Notifications: one poll feeds the bell (history + unread badge) and the snackbar (what just happened).
               Which messages exist at all is decided on the server by Settings > Notifications: a switched-off type is never stored. */
            (function () {
                var el = document.getElementById('dwSnack');
                if (!el) { return; }

                var URL = @json(route('notifications.index'));
                var READ_ALL = @json(route('notifications.read-all'));
                var UID = @json(auth()->id());
                var T = @json($notifT);
                var KEY_SEEN = 'dwNotifSeen:' + UID;
                var INTERVAL = 6000, SHOW_MS = 6000, FRESH_SECONDS = 60;

                var iconEl = document.getElementById('dwSnackIcon');
                var titleEl = document.getElementById('dwSnackTitle');
                var bodyEl = document.getElementById('dwSnackBody');
                var linkEl = document.getElementById('dwSnackLink');
                var closeEl = document.getElementById('dwSnackClose');
                var barEl = document.getElementById('dwSnackBar');

                var btn = document.getElementById('notifBtn');
                var panel = document.getElementById('notifPanel');
                var list = document.getElementById('notifList');
                var badge = document.getElementById('notifBadge');
                var readAllBtn = document.getElementById('notifReadAll');
                var tabAll = document.getElementById('notifTabAll');
                var tabUnread = document.getElementById('notifTabUnread');
                var tab = 'all';
                var hasBell = !!(btn && panel && list && badge && readAllBtn);

                var GLYPHS = {
                    check: '<path d="M20 6 9 17l-5-5"></path>',
                    truck: '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle>',
                    clock: '<circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path>',
                    chef: '<path d="M12 3c1.918 0 3.52 1.35 3.91 3.151a4 4 0 0 1 2.09 7.723l0 7.126h-12v-7.126a4 4 0 1 1 2.092 -7.723a4 4 0 0 1 3.908 -3.151z"></path><path d="M6.161 17.009l11.839 -.009"></path>',
                    receipt: '<path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2"></path><path d="M14 8h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5m2 0v1.5m0 -9v1.5"></path>',
                    x: '<path d="M18 6 6 18M6 6l12 12"></path>',
                    refund: '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path>',
                    star: '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>',
                    spark: '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"></path>',
                    bell: '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>'
                };
                var EVENTS = {
                    order_process: ['chef', 'warning'], order_rejected: ['x', 'error'], order_shipped: ['truck', 'info'],
                    order_done: ['check', 'success'], payment_success: ['receipt', 'success'], payment_failed: ['x', 'error'],
                    refund_refunded: ['refund', 'success'], refund_rejected: ['x', 'error'], refund_needs_bank: ['refund', 'warning'],
                    review_reminder: ['star', 'warning'], new_product: ['spark', 'info']
                };
                function pick(event) { return EVENTS[event] || ['bell', 'info']; }
                function glyph(event) {
                    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + GLYPHS[pick(event)[0]] + '</svg>';
                }

                var items = [];
                var unread = 0;
                var offset = 0;
                var signature = '';
                var queue = [];
                var showing = false;
                var hideTimer = null;
                var busy = false;

                function read(key, fallback) {
                    try { var v = JSON.parse(localStorage.getItem(key)); return v == null ? fallback : v; } catch (e) { return fallback; }
                }
                function write(key, value) {
                    try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { }
                }
                function serverNow() { return Math.floor(Date.now() / 1000) + offset; }

                function ago(at) {
                    var s = Math.max(0, serverNow() - at);
                    if (s < 10) { return T.now; }
                    if (s < 60) { return T.sec.replace(':n', s); }
                    if (s < 3600) { return T.min.replace(':n', Math.floor(s / 60)); }
                    if (s < 86400) { return T.hour.replace(':n', Math.floor(s / 3600)); }
                    return new Date(at * 1000).toLocaleDateString(document.documentElement.lang || undefined, { day: 'numeric', month: 'short' });
                }

                /* ---------- snackbar ---------- */
                function runBar(ms, from) {
                    if (!barEl) { return; }
                    barEl.style.transition = 'none';
                    barEl.style.width = from;
                    void barEl.offsetWidth;
                    barEl.style.transition = 'width ' + ms + 'ms linear';
                    barEl.style.width = '0px';
                }

                function next() {
                    if (showing || !queue.length) { return; }
                    showing = true;
                    var n = queue.shift();
                    iconEl.innerHTML = glyph(n.event);
                    el.className = 'dw-snack';
                    titleEl.textContent = n.title;
                    bodyEl.textContent = n.body;
                    linkEl.href = n.open_url;
                    el.classList.add('is-open');
                    runBar(SHOW_MS, el.clientWidth + 'px');
                    hideTimer = setTimeout(hide, SHOW_MS);
                }

                function hide() {
                    clearTimeout(hideTimer);
                    el.classList.remove('is-open');
                    // Wait for the slide-up before the next one drops in.
                    setTimeout(function () { showing = false; next(); }, 450);
                }

                closeEl.addEventListener('click', hide);
                el.addEventListener('mouseenter', function () {
                    clearTimeout(hideTimer);
                    if (barEl) {
                        var w = barEl.getBoundingClientRect().width;
                        barEl.style.transition = 'none';
                        barEl.style.width = w + 'px';
                    }
                });
                el.addEventListener('mouseleave', function () {
                    if (el.classList.contains('is-open')) {
                        runBar(2500, barEl ? barEl.style.width : '0px');
                        hideTimer = setTimeout(hide, 2500);
                    }
                });

                /* ---------- bell ---------- */
                function p(cls, text) {
                    var node = document.createElement('p');
                    node.className = cls;
                    node.textContent = text;   // titles and reasons can contain user input: never innerHTML
                    return node;
                }

                function row(n) {
                    var a = document.createElement('a');
                    a.className = 'notif-item' + (n.read ? '' : ' is-unread');
                    a.href = n.open_url;

                    var icon = document.createElement('span');
                    icon.className = 'notif-icon';
                    icon.innerHTML = glyph(n.event);

                    var body = document.createElement('div');
                    body.className = 'notif-item-body';

                    var top = document.createElement('div');
                    top.className = 'notif-item-top';
                    top.appendChild(p('notif-item-title', n.title));
                    var time = p('notif-item-time', ago(n.at));
                    time.dataset.at = n.at;
                    top.appendChild(time);

                    body.appendChild(top);
                    if (n.body) { body.appendChild(p('notif-item-text', n.body)); }
                    if (n.note) { body.appendChild(p('notif-item-note', n.note)); }

                    a.appendChild(icon);
                    a.appendChild(body);
                    return a;
                }

                function renderBell() {
                    if (!hasBell) { return; }
                    badge.textContent = unread > 9 ? '9+' : unread;
                    badge.hidden = unread === 0;
                    readAllBtn.disabled = unread === 0;

                    // Only rebuild the list when something changed, so it does not jump while it is being read.
                    var sig = tab + '|' + unread + '|' + items.map(function (i) { return i.id + (i.read ? 'r' : 'u'); }).join(',');
                    if (sig === signature) { return; }
                    signature = sig;

                    tabAll.classList.toggle('is-active', tab === 'all');
                    tabUnread.classList.toggle('is-active', tab === 'unread');
                    tabAll.setAttribute('aria-selected', tab === 'all');
                    tabUnread.setAttribute('aria-selected', tab === 'unread');

                    list.textContent = '';
                    var shown = tab === 'unread' ? items.filter(function (n) { return !n.read; }) : items;
                    if (!shown.length) {
                        list.appendChild(p('notif-empty', tab === 'unread' ? T.caughtUp : T.empty));
                        return;
                    }
                    shown.forEach(function (n) { list.appendChild(row(n)); });
                }

                function setTab(name) {
                    if (tab === name) { return; }
                    tab = name;
                    signature = '';
                    renderBell();
                }

                function openPanel() { panel.hidden = false; btn.setAttribute('aria-expanded', 'true'); }
                function closePanel() { if (panel.hidden) { return; } panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); }

                if (hasBell) {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (panel.hidden) { openPanel(); } else { closePanel(); }
                    });
                    document.addEventListener('click', function (e) {
                        if (!panel.hidden && !document.getElementById('notifMenu').contains(e.target)) { closePanel(); }
                    });
                    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closePanel(); } });
                    tabAll.addEventListener('click', function () { setTab('all'); });
                    tabUnread.addEventListener('click', function () { setTab('unread'); });

                    readAllBtn.addEventListener('click', async function () {
                        try {
                            var meta = document.querySelector('meta[name="csrf-token"]');
                            var res = await fetch(READ_ALL, {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': meta ? meta.content : '' },
                                credentials: 'same-origin'
                            });
                            if (!res.ok) { return; }
                            items.forEach(function (n) { n.read = true; });
                            unread = 0;
                            renderBell();
                        } catch (e) { }
                    });

                    setInterval(function () {
                        list.querySelectorAll('.notif-item-time[data-at]').forEach(function (node) { node.textContent = ago(Number(node.dataset.at)); });
                    }, 30000);
                }

                /* ---------- polling ---------- */
                async function poll() {
                    if (busy) { return; }
                    busy = true;
                    try {
                        var res = await fetch(URL, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            credentials: 'same-origin',
                            cache: 'no-store'
                        });
                        if (!res.ok) { return; }
                        var data = await res.json();

                        offset = data.now - Math.floor(Date.now() / 1000);
                        items = data.items || [];
                        unread = data.unread || 0;
                        renderBell();

                        var seen = read(KEY_SEEN, []);
                        var fresh = items.filter(function (i) { return seen.indexOf(i.id) === -1; });

                        if (fresh.length) {
                            write(KEY_SEEN, seen.concat(fresh.map(function (i) { return i.id; })).slice(-100));
                            // Only news from the last minute pops up; the rest is waiting in the bell.
                            fresh.filter(function (i) { return !i.read && i.at >= data.now - FRESH_SECONDS; })
                                .sort(function (a, b) { return a.at - b.at; })
                                .forEach(function (i) { queue.push(i); });
                            next();
                            window.dispatchEvent(new CustomEvent('dw:order-updated'));
                        }
                    } catch (e) {
                    } finally {
                        busy = false;
                    }
                }

                function schedule() {
                    setTimeout(async function () {
                        if (!document.hidden) { await poll(); }
                        schedule();
                    }, INTERVAL);
                }
                document.addEventListener('visibilitychange', function () { if (!document.hidden) { poll(); } });

                poll();
                schedule();
            })();
        </script>
    @endauth

    @stack('scripts')
</body>

</html>