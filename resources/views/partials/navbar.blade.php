<header class="navbar">
    <div class="container-ww navbar-inner">
        <a href="{{ url('/') }}" aria-label="Donat Wawa — Beranda">
            <img src="{{ asset('assets/images/logo-text-new.png') }}" alt="Donat Wawa" class="logo-img">
        </a>
        <nav class="nav-links">
            <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
            <a href="{{ url('/menu') }}" class="nav-link {{ request()->is('menu*') ? 'active' : '' }}">Menu</a>
            <a href="{{ url('/about') }}" class="nav-link {{ request()->is('about*') ? 'active' : '' }}">About</a>
        </nav>
        <div class="nav-right">
            <div class="lang-menu">
                <button type="button" class="icon-btn" aria-label="Pilih bahasa" onclick="toggleLangMenu()">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
                    </svg>
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
            @auth
                <button type="button" class="icon-btn" aria-label="Notifikasi">
                    <img src="{{ asset('assets/icons/notifications.svg') }}" alt="" class="icon-img">
                </button>
            @endauth
            @auth
                <div class="user-menu">
                    <button type="button" class="avatar avatar-btn" aria-label="Profil saya" onclick="toggleUserMenu()">
                        @if (auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Foto profil pengguna">
                        @else
                            <span class="avatar-initial">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-dropdown-menu">
                            <a href="{{ route('profile') }}" class="user-dropdown-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Profile
                            </a>
                            <a href="#" class="user-dropdown-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="M12 7v5l3 3"></path>
                                </svg>
                                My Orders
                            </a>
                        </div>
                        <div class="user-dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="user-dropdown-menu">
                            @csrf
                            <button type="submit" class="user-dropdown-item user-dropdown-item-danger">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <path d="M16 17l5-5-5-5"></path>
                                    <path d="M21 12H9"></path>
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <button type="button" class="btn btn-primary btn-sm" onclick="openAuthPopup('login')">Login</button>
            @endauth
            <button type="button" class="hamburger-btn" id="hamburgerBtn" aria-label="Buka menu" aria-expanded="false" aria-controls="mobileNavPanel" onclick="toggleMobileMenu()">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </div>
    <div class="mobile-nav-backdrop" id="mobileNavBackdrop" onclick="toggleMobileMenu()"></div>
    <nav class="mobile-nav-panel" id="mobileNavPanel">
        <button type="button" class="mobile-nav-close" aria-label="Tutup menu" onclick="toggleMobileMenu()">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"></path>
                <path d="M6 6l12 12"></path>
            </svg>
        </button>
        <a href="{{ url('/') }}" class="mobile-nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
        <a href="{{ url('/menu') }}" class="mobile-nav-link {{ request()->is('menu*') ? 'active' : '' }}">Menu</a>
        <a href="{{ url('/about') }}" class="mobile-nav-link {{ request()->is('about*') ? 'active' : '' }}">About</a>
    </nav>
</header>