<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', __('Admin') . ' — Donat Wawa')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@500;600;700&display=swap" rel="stylesheet">
@vite(['resources/css/base.css', 'resources/css/admin/produk.css'])
@stack('styles')
</head>
<body class="admin-body">

<div class="admin-shell" id="adminShell">
    @include('admin.partials.sidebar')

    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="admin-sidebar-toggle-mobile" onclick="document.getElementById('adminShell').classList.toggle('sidebar-open')" aria-label="{{ __('Open menu') }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            {{-- FIX: input di depan, tombol (dengan ikon di dalamnya) setelah input --}}
            <form method="GET" class="admin-search" role="search">
                <input type="text" name="search" placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
                <button type="submit" class="admin-search-btn" aria-label="{{ __('Search') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </button>
            </form>

            <div class="lang-menu admin-lang">
                <button type="button" class="admin-lang-btn" aria-label="{{ __('Choose language') }}" aria-haspopup="true" onclick="toggleAdminLangMenu(event)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
                    </svg>
                    <span class="admin-lang-code">{{ strtoupper(app()->getLocale()) }}</span>
                </button>
                <div class="lang-dropdown" id="langDropdown">
                    <form method="POST" action="{{ route('language.switch', 'id') }}">
                        @csrf
                        <button type="submit" class="lang-dropdown-item {{ app()->getLocale() === 'id' ? 'active' : '' }}">Bahasa Indonesia</button>
                    </form>
                    <form method="POST" action="{{ route('language.switch', 'en') }}">
                        @csrf
                        <button type="submit" class="lang-dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}">English (US)</button>
                    </form>
                </div>
            </div>

            <div class="admin-profile">
                @if (auth()->user()?->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="admin-avatar">
                @else
                    <span class="admin-avatar admin-avatar-initial">{{ strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
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

@stack('scripts')
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
    } catch (e) {}

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            shell.classList.toggle('sidebar-collapsed');
            try {
                localStorage.setItem('adminSidebarCollapsed', shell.classList.contains('sidebar-collapsed') ? '1' : '0');
            } catch (e) {}
        });
    }
})();
</script>
</body>
</html>