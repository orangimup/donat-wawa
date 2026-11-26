<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('Admin') . ' — Donat Wawa')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/base.css', 'resources/css/admin/produk.css'])
    @stack('styles')
    <style>
        .admin-notif {
            position: relative;
            flex-shrink: 0;
        }

        .admin-notif+.admin-profile {
            margin-left: 0;
        }

        .admin-notif-btn {
            position: relative;
            padding: 0 18px;
        }

        .admin-notif-badge {
            position: absolute;
            top: 9px;
            right: 11px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: var(--primary);
            color: #fff;
            font: 600 11px/18px 'Be Vietnam Pro', sans-serif;
            text-align: center;
            box-sizing: border-box;
        }

        .admin-notif-badge[hidden] {
            display: none;
        }

        .admin-notif-panel {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 400px;
            max-width: calc(100vw - 32px);
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(46, 12, 14, 0.16), 0 0 0 1px rgba(46, 12, 14, 0.05);
            overflow: hidden;
            z-index: 60;
        }

        .admin-notif-panel[hidden] {
            display: none;
        }

        .admin-notif-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px 14px 18px;
            border-bottom: 1px solid #efe7e3;
            font: 700 16px 'Be Vietnam Pro', sans-serif;
            color: #2e1e1a;
        }

        .admin-notif-tabs {
            display: flex;
            gap: 4px;
        }

        .admin-notif-tab {
            border: 0;
            background: transparent;
            color: rgba(86, 66, 65, 0.85);
            font: 600 12.5px 'Be Vietnam Pro', sans-serif;
            padding: 7px 14px;
            border-radius: 999px;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .admin-notif-tab:hover {
            background: #f3efed;
            color: #2e1e1a;
        }

        .admin-notif-tab.is-active,
        .admin-notif-tab.is-active:hover {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 10px rgba(123, 32, 38, 0.28);
        }

        .admin-notif-list {
            max-height: 420px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #e3d2ce transparent;
        }

        .admin-notif-list::-webkit-scrollbar {
            width: 6px;
        }

        .admin-notif-list::-webkit-scrollbar-thumb {
            background: #e3d2ce;
            border-radius: 6px;
        }

        .admin-notif-empty {
            margin: 0;
            padding: 32px 16px 36px;
            text-align: center;
            font: 500 13px 'Be Vietnam Pro', sans-serif;
            color: rgba(86, 66, 65, 0.7);
        }

        .admin-notif-foot {
            display: flex;
            justify-content: center;
            gap: 4px;
            padding: 6px 8px;
            border-top: 1px solid #f1eae7;
        }

        .admin-notif-foot button {
            border: 0;
            background: transparent;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            font: 700 12.5px 'Be Vietnam Pro', sans-serif;
            color: var(--primary);
        }

        .admin-notif-foot button:hover {
            background: rgba(123, 32, 38, 0.08);
        }

        .admin-notif-foot button:disabled {
            opacity: .5;
            cursor: default;
            background: transparent;
        }

        .dw-n {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            text-decoration: none;
            color: inherit;
        }

        .dw-n-media {
            position: relative;
            flex: none;
            width: 56px;
            height: 56px;
        }

        .dw-n-thumb,
        .dw-n-icon {
            display: block;
            width: 56px;
            height: 56px;
            border-radius: 17px;
        }

        .dw-n-thumb {
            object-fit: cover;
            background: #f6ece9;
            box-shadow: 0 0 0 1px rgba(46, 12, 14, 0.07);
        }

        .dw-n-icon {
            background: #f3e3e0;
            color: var(--primary);
            font: 700 22px 'Be Vietnam Pro', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dw-n-body {
            flex: 1;
            min-width: 0;
        }

        .dw-n-body p {
            margin: 0;
        }

        .dw-n-title {
            font: 600 14px/1.4 'Be Vietnam Pro', sans-serif;
            color: #2e1e1a;
        }

        .dw-n-text {
            margin-top: 3px !important;
            font: 500 13px/1.45 'Be Vietnam Pro', sans-serif;
            color: #5c4f4b;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .dw-n-meta {
            display: flex;
            align-items: center;
            gap: 0 6px;
            margin-top: 9px;
            font: 500 12px/1.3 'Be Vietnam Pro', sans-serif;
            color: #9a908b;
        }

        .dw-n-meta>span+span::before {
            content: '\00b7';
            margin-right: 6px;
            color: #cdbfba;
            font-weight: 700;
        }

        .dw-n-meta .dw-n-time {
            margin-left: auto;
            white-space: nowrap;
        }

        .dw-n-meta .dw-n-time::before {
            display: none;
        }

        .dw-n-code {
            font-weight: 600;
            color: #7a6f6b;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dw-n-total {
            font-weight: 700;
            color: var(--primary);
        }

        .dw-n-top {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
        }

        .dw-n-top .dw-n-time {
            flex: none;
            font: 500 11.5px/1.4 'Be Vietnam Pro', sans-serif;
            color: #9a908b;
            white-space: nowrap;
        }

        .dw-toast .dw-n-top .dw-n-time {
            display: none;
        }

        .admin-notif-list .dw-n {
            align-items: flex-start;
            padding: 18px 20px;
            transition: background .15s ease;
        }

        .admin-notif-list .dw-n+.dw-n {
            border-top: 1px solid #f1eae7;
        }

        .admin-notif-list a.dw-n:hover {
            background: #faf6f4;
        }

        .admin-notif-list .dw-n-media,
        .admin-notif-list .dw-n-thumb,
        .admin-notif-list .dw-n-icon {
            width: 44px;
            height: 44px;
        }

        .admin-notif-list .dw-n-thumb,
        .admin-notif-list .dw-n-icon {
            border-radius: 13px;
        }

        .admin-notif-list .dw-n-icon {
            background: #f3e3e0;
            color: var(--primary);
            font-size: 18px;
        }

        .dw-n-badge {
            position: absolute;
            right: -6px;
            bottom: -6px;
            width: 26px;
            height: 26px;
            box-sizing: border-box;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dw-n-badge svg {
            width: 15px;
            height: 15px;
        }

        .admin-notif-list .dw-n.is-unread .dw-n-media::after {
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

        .admin-notif-list .dw-n-title {
            font-size: 13.5px;
            font-weight: 600;
        }

        .admin-notif-list .dw-n-text {
            margin-top: 5px !important;
            font-size: 12.5px;
        }

        .admin-notif-list .dw-n-meta {
            margin-top: 8px;
            font-size: 11.5px;
        }

        .admin-toasts {
            position: fixed;
            top: 88px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 380px;
            max-width: calc(100vw - 32px);
            pointer-events: none;
        }

        .dw-toast {
            pointer-events: auto;
            cursor: pointer;
            align-items: center;
            padding: 14px 14px 14px 16px;
            background: radial-gradient(140% 200% at 0% 100%, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 55%), var(--primary);
            color: #fff;
            border: 1.5px solid rgba(255, 255, 255, 0.92);
            border-top: 1.5px solid rgba(255, 255, 255, 0.92);
            border-radius: 26px;
            box-shadow: 0 0 0 5px rgba(123, 32, 38, 0.14), 0 18px 36px rgba(46, 12, 14, 0.3);
            overflow: hidden;
            animation: dwToastIn 0.28s ease;
            transition: opacity 0.2s, transform 0.2s;
        }

        .dw-toast .dw-n-icon {
            width: 40px;
            height: 40px;
            font-size: 17px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
        }

        .dw-toast .dw-n-media {
            width: 40px;
            height: 40px;
        }

        .dw-toast .dw-n-thumb {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: none;
        }

        .dw-toast .dw-n-meta {
            display: none;
        }

        .dw-toast .dw-n-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
        }

        .dw-toast .dw-n-text {
            margin-top: 2px !important;
            color: rgba(255, 255, 255, 0.78);
        }

        .dw-toast .dw-n-close {
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

        .dw-toast .dw-n-close:hover {
            transform: scale(1.08);
        }

        .dw-toast.is-leaving {
            opacity: 0;
            transform: translateX(16px);
        }

        .dw-toast-bar {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 100%;
            background: rgba(255, 255, 255, 0.55);
            animation: dwToastBar 6s linear forwards;
        }

        @keyframes dwToastIn {
            from {
                opacity: 0;
                transform: translateX(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes dwToastBar {
            from {
                width: 100%;
            }

            to {
                width: 0;
            }
        }

        tr.is-flash td {
            animation: dwRowFlash 3s ease-out;
        }

        @keyframes dwRowFlash {
            0%,
            60% {
                background: #e6f5ea;
            }

            100% {
                background: transparent;
            }
        }

        @media (max-width: 640px) {
            .admin-toasts {
                top: 76px;
                right: 16px;
            }
        }
    </style>
</head>

<body class="admin-body">

    <div class="admin-shell" id="adminShell">
        @include('admin.partials.sidebar')

        <div class="admin-main">
            <header class="admin-topbar">
                <button type="button" class="admin-sidebar-toggle-mobile"
                    onclick="document.getElementById('adminShell').classList.toggle('sidebar-open')"
                    aria-label="{{ __('Open menu') }}">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>

                {{-- FIX: input di depan, tombol (dengan ikon di dalamnya) setelah input --}}
                <form method="GET" class="admin-search" role="search">
                    <input type="text" name="search" placeholder="{{ __('Search...') }}"
                        value="{{ request('search') }}">
                    <button type="submit" class="admin-search-btn" aria-label="{{ __('Search') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                    </button>
                </form>

                <div class="lang-menu admin-lang">
                    <button type="button" class="admin-lang-btn" aria-label="{{ __('Choose language') }}"
                        aria-haspopup="true" onclick="toggleAdminLangMenu(event)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
                        </svg>
                        <span class="admin-lang-code">{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <div class="lang-dropdown" id="langDropdown">
                        <form method="POST" action="{{ route('language.switch', 'id') }}">
                            @csrf
                            <button type="submit"
                                class="lang-dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}">Bahasa
                                Indonesia</button>
                        </form>
                        <form method="POST" action="{{ route('language.switch', 'en') }}">
                            @csrf
                            <button type="submit"
                                class="lang-dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">English
                                (US)</button>
                        </form>
                    </div>
                </div>

                <div class="admin-notif" id="adminNotif">
                    <button type="button" class="admin-lang-btn admin-notif-btn" id="adminNotifBtn"
                        aria-label="{{ __('Notifications') }}" aria-haspopup="true" aria-expanded="false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path
                                d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6">
                            </path>
                            <path d="M9 17v1a3 3 0 0 0 6 0v-1"></path>
                        </svg>
                        <span class="admin-notif-badge" id="adminNotifBadge" hidden>0</span>
                    </button>
                    <div class="admin-notif-panel" id="adminNotifPanel" hidden>
                        <div class="admin-notif-head">
                            <span>{{ __('Notifications') }}</span>
                            <div class="admin-notif-tabs" role="tablist">
                                <button type="button" class="admin-notif-tab is-active" id="adminNotifTabAll" role="tab"
                                    aria-selected="true">{{ __('All') }}</button>
                                <button type="button" class="admin-notif-tab" id="adminNotifTabUnread" role="tab"
                                    aria-selected="false">{{ __('Unread') }}</button>
                            </div>
                        </div>
                        <div class="admin-notif-list" id="adminNotifList"></div>
                        <div class="admin-notif-foot">
                            <button type="button" id="adminNotifReadAll" disabled>{{ __('Mark all as read') }}</button>
                            <button type="button" id="adminNotifClear">{{ __('Clear all') }}</button>
                        </div>
                    </div>
                </div>

                <div class="admin-profile">
                    @if (auth()->user()?->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="admin-avatar">
                    @else
                        <span
                            class="admin-avatar admin-avatar-initial">{{ strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                    @endif
                    <div class="admin-profile-text">
                        <strong>{{ auth()->user()->name ?? __('Admin') }}</strong>
                        <span>{{ auth()->user()->email ?? '' }}</span>
                    </div>
                </div>
            </header>

            <main class="admin-content">
                @if (session('status'))
                    <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="admin-alert admin-alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <div class="admin-toasts" id="adminToasts" aria-live="polite"></div>

    @php
        $notifT = [
            'title' => __('Order received by customer'),
            'text' => __(':code from :customer is now completed.'),
            'empty' => __('No notifications yet.'),
            'now' => __('Just now'),
            'sec' => __(':n s ago'),
            'min' => __(':n min ago'),
            'hour' => __(':n h ago'),
            'close' => __('Close'),
            'caughtUp' => __('All caught up'),
        ];
    @endphp
    <script>
        /* Keeps a part of the page fresh without a reload: asks for the same page again every few seconds and swaps
           the element in only when its data-live-version changed. Returns the poll function (call it to refresh now). */
        window.dwLiveRefresh = function (selector, opts) {
            opts = opts || {};
            var interval = opts.interval || 5000;
            var skip = opts.skip || function () { return !!document.querySelector('.admin-modal-overlay.open'); };
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
    @stack('scripts')
    <script>
        /* Admin bell: paid orders, completed orders, refund requests/failures and new reviews. The list lives in localStorage so it survives page changes. */
        (function () {
            var URL = @json(route('admin.notifications'));
            var T = @json($notifT);
            var KEY_LIST = 'dwAdminNotifs';
            var KEY_SINCE = 'dwAdminNotifSince';
            var INTERVAL = 5000, TOAST_MS = 6000, FRESH_SECONDS = 60, MAX = 20;

            var btn = document.getElementById('adminNotifBtn');
            var panel = document.getElementById('adminNotifPanel');
            var list = document.getElementById('adminNotifList');
            var badge = document.getElementById('adminNotifBadge');
            var clearBtn = document.getElementById('adminNotifClear');
            var readAllBtn = document.getElementById('adminNotifReadAll');
            var tabAll = document.getElementById('adminNotifTabAll');
            var tabUnread = document.getElementById('adminNotifTabUnread');
            var tab = 'all';
            var toasts = document.getElementById('adminToasts');
            if (!btn || !panel || !list || !toasts || !readAllBtn || !tabAll || !tabUnread) { return; }

            var CLOSE = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"></path></svg>';
            var offset = 0;
            var busy = false;

            var BADGES = {
                order_paid: '<path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2"></path><path d="M14 8h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5m2 0v1.5m0 -9v1.5"></path>',
                order_completed: '<path d="M20 6 9 17l-5-5"></path>',
                refund_requested: '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path>',
                refund_failed: '<path d="M18 6 6 18M6 6l12 12"></path>',
                review_created: '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>'
            };

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

            function line(cls, text) {
                var p = document.createElement('p');
                p.className = cls;
                p.textContent = text;
                return p;
            }

            /* Same card for the dropdown and the toast. Text goes in with textContent: customer names are user input. */
            function card(n, isToast) {
                var el = document.createElement(isToast ? 'div' : 'a');
                el.className = 'dw-n' + (!isToast && !n.read ? ' is-unread' : '');
                if (!isToast) {
                    el.href = n.url;
                    el.addEventListener('click', function () { markRead(n.key); });
                }

                var media = document.createElement('span');
                media.className = 'dw-n-media';
                var visual = document.createElement('span');
                visual.className = 'dw-n-icon';
                visual.textContent = (n.customer || '').trim().charAt(0).toUpperCase();
                media.appendChild(visual);
                if (BADGES[n.type]) {
                    var mark = document.createElement('span');
                    mark.className = 'dw-n-badge';
                    mark.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + BADGES[n.type] + '</svg>';
                    media.appendChild(mark);
                }
                if (n.thumb) {
                    var img = document.createElement('img');
                    img.className = 'dw-n-thumb';
                    img.alt = '';
                    img.onload = function () { visual.replaceWith(img); };
                    img.src = n.thumb;
                }

                var body = document.createElement('div');
                body.className = 'dw-n-body';
                // Items from the server carry their own (already translated) title and text; old ones saved by an
                // earlier version of this bell only have a code and a customer, so they fall back to the completed-order wording.
                var top = document.createElement('div');
                top.className = 'dw-n-top';
                top.appendChild(line('dw-n-title', n.title || T.title));
                var time = document.createElement('span');
                time.className = 'dw-n-time';
                time.textContent = ago(n.at);
                time.dataset.at = n.at;
                top.appendChild(time);
                body.appendChild(top);
                body.appendChild(line('dw-n-text', n.text || T.text
                    .replace(':code', function () { return n.code; })
                    .replace(':customer', function () { return n.customer; })));

                var meta = document.createElement('div');
                meta.className = 'dw-n-meta';
                [['dw-n-total', n.total]].forEach(function (part) {
                    if (!part[1]) { return; }
                    var span = document.createElement('span');
                    span.className = part[0];
                    span.textContent = part[1];
                    meta.appendChild(span);
                });
                if (meta.childNodes.length) { body.appendChild(meta); }

                el.appendChild(media);
                el.appendChild(body);
                return el;
            }

            function markRead(key) {
                var items = read(KEY_LIST, []);
                items.forEach(function (n) { if (n.key === key) { n.read = true; } });
                write(KEY_LIST, items);
            }

            function render() {
                var items = read(KEY_LIST, []);
                var unread = items.filter(function (n) { return !n.read; }).length;

                badge.textContent = unread > 9 ? '9+' : unread;
                badge.hidden = unread === 0;
                readAllBtn.disabled = unread === 0;
                clearBtn.disabled = items.length === 0;

                tabAll.classList.toggle('is-active', tab === 'all');
                tabUnread.classList.toggle('is-active', tab === 'unread');
                tabAll.setAttribute('aria-selected', tab === 'all');
                tabUnread.setAttribute('aria-selected', tab === 'unread');

                list.textContent = '';
                var shown = tab === 'unread' ? items.filter(function (n) { return !n.read; }) : items;
                if (!shown.length) {
                    list.appendChild(line('admin-notif-empty', tab === 'unread' ? T.caughtUp : T.empty));
                    return;
                }
                shown.forEach(function (n) { list.appendChild(card(n, false)); });
            }

            function toast(n) {
                var el = card(n, true);
                el.classList.add('dw-toast');

                var bar = document.createElement('span');
                bar.className = 'dw-toast-bar';
                var close = document.createElement('button');
                close.type = 'button';
                close.className = 'dw-n-close';
                close.setAttribute('aria-label', T.close);
                close.innerHTML = CLOSE;
                el.appendChild(close);
                el.appendChild(bar);

                var timer = setTimeout(remove, TOAST_MS);
                function remove() {
                    clearTimeout(timer);
                    el.classList.add('is-leaving');
                    setTimeout(function () { el.remove(); }, 200);
                }
                close.addEventListener('click', function (e) { e.stopPropagation(); remove(); });
                el.addEventListener('click', function () { markRead(n.key); window.location.href = n.url; });

                toasts.insertBefore(el, toasts.firstChild);
                while (toasts.children.length > 3) { toasts.lastChild.remove(); }
            }

            async function poll() {
                if (busy) { return; }
                busy = true;
                try {
                    var since = Number(read(KEY_SINCE, 0)) || 0;
                    var res = await fetch(URL + (since ? '?since=' + since : ''), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin',
                        cache: 'no-store'
                    });
                    if (!res.ok) { return; }
                    var data = await res.json();
                    offset = data.now - Math.floor(Date.now() / 1000);

                    var items = read(KEY_LIST, []);
                    var known = {};
                    items.forEach(function (n) { known[n.key] = true; });
                    var fresh = (data.items || []).filter(function (i) { return !known[i.key]; });

                    if (fresh.length) {
                        fresh.forEach(function (i) {
                            items.unshift({ key: i.key, id: i.id, type: i.type, title: i.title, text: i.text, code: i.code, total: i.total, thumb: i.thumb, customer: i.customer, at: i.at, url: i.url, read: false });
                        });
                        write(KEY_LIST, items.slice(0, MAX));
                        render();
                        fresh.filter(function (i) { return i.at >= data.now - FRESH_SECONDS; }).slice(-3).forEach(toast);
                        window.dispatchEvent(new CustomEvent('admin:order-completed', {
                            detail: { ids: fresh.map(function (i) { return i.id; }) }
                        }));
                    }
                    // A 2 second overlap so nothing falls between two polls; duplicates are dropped by key above.
                    write(KEY_SINCE, Math.max(0, data.now - 2));
                } catch (e) {
                } finally {
                    busy = false;
                }
            }

            function openPanel() {
                panel.hidden = false;
                btn.setAttribute('aria-expanded', 'true');
            }
            function closePanel() {
                if (panel.hidden) { return; }
                panel.hidden = true;
                btn.setAttribute('aria-expanded', 'false');
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (panel.hidden) { openPanel(); } else { closePanel(); }
            });
            document.addEventListener('click', function (e) {
                if (!panel.hidden && !document.getElementById('adminNotif').contains(e.target)) { closePanel(); }
            });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closePanel(); } });

            tabAll.addEventListener('click', function () { tab = 'all'; render(); });
            tabUnread.addEventListener('click', function () { tab = 'unread'; render(); });

            readAllBtn.addEventListener('click', function () {
                var items = read(KEY_LIST, []);
                items.forEach(function (n) { n.read = true; });
                write(KEY_LIST, items);
                render();
            });

            clearBtn.addEventListener('click', function () {
                write(KEY_LIST, []);
                write(KEY_SINCE, serverNow());
                render();
            });

            // Another admin tab changed the list: keep this one in step.
            window.addEventListener('storage', function (e) { if (e.key === KEY_LIST) { render(); } });
            setInterval(function () {
                document.querySelectorAll('.dw-n-time[data-at]').forEach(function (el) { el.textContent = ago(Number(el.dataset.at)); });
            }, 30000);

            function schedule() {
                setTimeout(async function () {
                    if (!document.hidden) { await poll(); }
                    schedule();
                }, INTERVAL);
            }
            document.addEventListener('visibilitychange', function () { if (!document.hidden) { poll(); } });

            render();
            poll();
            schedule();
        })();
    </script>
    <script>
        function toggleAdminLangMenu(e) {
            if (e) e.stopPropagation();
            var dd = document.getElementById('langDropdown');
            if (dd) dd.classList.toggle('open');
        }
        document.addEventListener('click', function (e) {
            var menu = document.querySelector('.admin-lang');
            var dd = document.getElementById('langDropdown');
            if (menu && dd && !menu.contains(e.target)) dd.classList.remove('open');
        });
    </script>
    <script>
        (function () {
            var shell = document.getElementById('adminShell');
            var toggleBtn = document.getElementById('sidebarCollapseToggle');

            try {
                if (localStorage.getItem('adminSidebarCollapsed') === '1') {
                    shell.classList.add('sidebar-collapsed');
                }
            } catch (e) { }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    shell.classList.toggle('sidebar-collapsed');
                    try {
                        localStorage.setItem('adminSidebarCollapsed', shell.classList.contains('sidebar-collapsed') ? '1' : '0');
                    } catch (e) { }
                });
            }
        })();
    </script>
</body>

</html>