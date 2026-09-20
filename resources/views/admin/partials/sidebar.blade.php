@php
    $adminNav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'key' => 'dashboard', 'icon' => 'dashboard'],
        ['label' => 'Product', 'route' => 'admin.menu-items.index', 'active_pattern' => 'admin.menu-items.*', 'key' => 'menu-items', 'icon' => 'product'],
        ['label' => 'Order', 'route' => null, 'key' => 'order', 'icon' => 'order'],
        ['label' => 'Transaction', 'route' => null, 'key' => 'transaction', 'icon' => 'transaction'],
        ['label' => 'Delivery Zone', 'route' => null, 'key' => 'delivery-zone', 'icon' => 'delivery'],
        ['label' => 'Refund', 'route' => null, 'key' => 'refund', 'icon' => 'refund'],
        ['label' => 'User', 'route' => 'admin.user', 'key' => 'user', 'icon' => 'user'],
        ['label' => 'Review', 'route' => null, 'key' => 'review', 'icon' => 'review'],
    ];

    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect>',
        'order' => '<path d="M6 6h15l-1.5 9h-12z"></path><path d="M6 6 5 3H2"></path><circle cx="9.5" cy="20" r="1.4" fill="currentColor" stroke="none"></circle><circle cx="17.5" cy="20" r="1.4" fill="currentColor" stroke="none"></circle>',
        'transaction' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v10M9.5 9.2c0-1.2 1.1-2.2 2.5-2.2s2.5 1 2.5 2c0 3-5 1.5-5 4.5 0 1.1 1.1 2 2.5 2s2.5-1 2.5-2.3"></path>',
        'delivery' => '<rect x="2" y="7" width="12" height="9"></rect><path d="M14 10h4l3 3v3h-7z"></path><circle cx="6.5" cy="18" r="1.8" fill="currentColor" stroke="none"></circle><circle cx="17" cy="18" r="1.8" fill="currentColor" stroke="none"></circle>',
        'refund' => '<path d="M4 4v5h5"></path><path d="M4.5 9A8 8 0 1 1 6 15.5"></path>',
        'user' => '<circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-3.6 3.6-6 8-6s8 2.4 8 6"></path>',
        'review' => '<path d="M4 5h16v11H8l-4 4z"></path>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path>',
    ];
@endphp

<aside class="admin-sidebar">
    <div class="admin-sidebar-head">
        <img src="{{ asset('assets/images/logo-text-admin.png') }}" alt="Donat Wawa" class="admin-logo-combined">
        <img src="{{ asset('assets/icons/sidebar.svg') }}" alt="Sidebar" class="admin-sidebar-icon">
    </div>

    <nav class="admin-nav">
        @foreach ($adminNav as $item)
            @php
                $isActive = $item['route'] && request()->routeIs($item['active_pattern'] ?? $item['route'] . '*');
                $isDisabled = ! $item['route'];
                $href = $item['route'] ? route($item['route']) : '#';
            @endphp
            <a
                href="{{ $isDisabled ? '#' : $href }}"
                class="admin-nav-link {{ $isActive ? 'active' : '' }} {{ $isDisabled ? 'disabled' : '' }}"
                @if ($isDisabled) aria-disabled="true" onclick="return false;" @endif
            >
                @if ($item['icon'] === 'product')
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 30 30" fill="currentColor">
                        <path d="M15 10C12.2375 10 10 12.2375 10 15C10 17.7625 12.2375 20 15 20C17.7625 20 20 17.7625 20 15C20 12.2375 17.7625 10 15 10ZM15 17.5C13.625 17.5 12.5 16.375 12.5 15C12.5 13.625 13.625 12.5 15 12.5C16.375 12.5 17.5 13.625 17.5 15C17.5 16.375 16.375 17.5 15 17.5Z"></path>
                        <path d="M25.3875 9.95C25.2625 9.975 25.1375 10 25 10C23.625 10 22.5 8.875 22.5 7.5375C22.5 7.5 22.525 7.375 22.525 7.3375C22.5383 7.13079 22.4995 6.92404 22.4121 6.73622C22.3248 6.54841 22.1917 6.38552 22.025 6.2625C21.8595 6.13712 21.6652 6.05537 21.4599 6.02479C21.2545 5.99421 21.0448 6.01577 20.85 6.0875C20.579 6.19327 20.2909 6.24835 20 6.25C18.65 6.25 17.5375 5.15 17.5125 3.8C17.5071 3.49691 17.391 3.20627 17.186 2.9829C16.9811 2.75954 16.7015 2.6189 16.4 2.5875C15.9375 2.5375 15.475 2.5 15 2.5C8.1125 2.5 2.5 8.1125 2.5 15C2.5 21.8875 8.1125 27.5 15 27.5C21.8875 27.5 27.5 21.8875 27.5 15C27.5 13.575 27.25 12.15 26.75 10.7625C26.652 10.4903 26.4626 10.2606 26.2142 10.1124C25.9657 9.96426 25.6735 9.90685 25.3875 9.95ZM15 25C9.4875 25 5 20.5125 5 15C5 9.4875 9.4875 5 15 5H15.1625C15.4563 6.09191 16.1068 7.05419 17.0106 7.73372C17.9144 8.41324 19.0195 8.77098 20.15 8.75C20.4134 9.76849 20.9912 10.678 21.8014 11.349C22.6116 12.02 23.6128 12.4184 24.6625 12.4875C24.875 13.3125 24.9875 14.1625 24.9875 15C24.9875 20.5125 20.5 25 14.9875 25H15Z"></path>
                    </svg>
                @else
                    <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        {!! $icons[$item['icon']] !!}
                    </svg>
                @endif
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="admin-logout-form">
        @csrf
        <button type="submit" class="admin-nav-link admin-logout">
            <svg class="admin-nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                {!! $icons['logout'] !!}
            </svg>
            <span>Logout</span>
        </button>
    </form>
</aside>
