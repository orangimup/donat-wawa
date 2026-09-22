@php
    $settingsNav = [
        ['label' => 'Account', 'route' => 'profile', 'key' => 'account'],
        ['label' => 'Notifications', 'route' => 'settings.notifications', 'key' => 'notifications'],
        ['label' => 'My Orders', 'route' => 'settings.orders', 'key' => 'my-orders'],
        ['label' => 'Languages', 'route' => 'settings.languages', 'key' => 'languages'],
    ];
@endphp

<aside class="settings-sidebar">
    <h2>{{ __('Settings') }}</h2>
    <nav class="settings-nav">
        @foreach ($settingsNav as $item)
            @php
                $isActive = $item['key'] === ($active ?? null);
                $isDisabled = $item['disabled'] ?? false;
                $href = $item['route'] ? route($item['route']) : '#';
            @endphp
            <a
                href="{{ $isDisabled ? '#' : $href }}"
                class="settings-nav-link {{ $isActive ? 'active' : '' }} {{ $isDisabled ? 'disabled' : '' }}"
                @if ($isDisabled) aria-disabled="true" onclick="return false;" @endif
            >
                {{ __($item['label']) }}
            </a>
        @endforeach
    </nav>
</aside>