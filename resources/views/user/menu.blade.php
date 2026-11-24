@extends('layouts.app')

@section('title', 'Donat Wawa')

@push('styles')
    @vite(['resources/css/menu.css', 'resources/css/basket-bar.css'])
@endpush

@section('content')

    <section class="menu-hero">
        <div class="container-ww">
            <h1>{{ __('Explore Our Menu') }}</h1>
            <p>{{ __('Discover our handcrafted selection of pillowy donuts, from timeless classics to seasonal specials, baked fresh daily with home-baked warmth.') }}</p>
        </div>
    </section>

    <div class="container-ww">

        {{-- Search + Filter toolbar --}}
        <form method="GET" action="{{ url('/menu') }}" class="menu-toolbar" id="menu-search-form"
            data-category="{{ request('category', 'all') }}">
            <div class="menu-search">
                <input type="text" name="search" id="menu-search-input" placeholder="{{ __('Search...') }}"
                    value="{{ request('search') }}" autocomplete="off">
                <button type="submit" aria-label="{{ __('Search') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                        stroke-linecap="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </button>
            </div>

            <div class="menu-filters">
                @php
                    $activeCategory = request('category', 'all');
                    $filters = [
                        'all' => __('All'),
                        'classics' => __('Classics'),
                        'special' => __('Special'),
                        'best_seller' => __('Best Seller'),
                        'new' => __('New Menu'),
                    ];
                @endphp
                @foreach($filters as $key => $label)
                    <a href="{{ url('/menu') }}?category={{ $key }}{{ request('search') ? '&search=' . urlencode(request('search')) : '' }}"
                        data-category="{{ $key }}"
                        class="filter-pill {{ $activeCategory === $key ? 'is-active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        {{-- Menu grid + pagination (replaced in place by the live search) --}}
        <div id="menu-results">
            @fragment('menu-results')
            <section class="section" style="padding-top:0;">
                <div class="menu-grid">
                    @forelse($menuItems as $item)
                        @include('components.menu-card', ['item' => $item])
                    @empty
                        <p style="grid-column: 1/-1; text-align:center; color:#8A7C72;">{{ __('No menu items match your search.') }}</p>
                    @endforelse
                </div>
            </section>

            @if(method_exists($menuItems, 'links'))
                <div class="menu-pagination">
                    {{ $menuItems->onEachSide(1)->links('components.pagination') }}
                </div>
            @endif
            @endfragment
        </div>

    </div>

    @include('components.basket-bar')

@endsection

@push('scripts')
    @vite('resources/js/basket.js')

    <script>
        // Live search: results update while the user types, no page reload.
        (function () {
            const form = document.getElementById('menu-search-form');
            const input = document.getElementById('menu-search-input');
            const results = document.getElementById('menu-results');
            if (!form || !input || !results) { return; }

            const pills = Array.from(form.querySelectorAll('.filter-pill[data-category]'));
            let category = form.dataset.category || 'all';
            let timer = null;
            let controller = null;

            function urlFor(withCategory) {
                const params = new URLSearchParams();
                const term = input.value.trim();
                if (withCategory || category !== 'all') { params.set('category', category); }
                if (term) { params.set('search', term); }
                const qs = params.toString();
                return form.action + (qs ? '?' + qs : '');
            }

            // Keep the pill links in step with what is typed, so a pill click keeps the search.
            function syncPills() {
                const term = input.value.trim();
                pills.forEach(function (pill) {
                    const params = new URLSearchParams({ category: pill.dataset.category });
                    if (term) { params.set('search', term); }
                    pill.href = form.action + '?' + params.toString();
                    pill.classList.toggle('is-active', pill.dataset.category === category);
                });
            }

            async function load() {
                const url = urlFor(false);

                if (controller) { controller.abort(); }
                controller = new AbortController();
                results.classList.add('is-loading');

                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        credentials: 'same-origin',
                        signal: controller.signal,
                    });
                    if (!res.ok) { throw new Error('HTTP ' + res.status); }

                    results.innerHTML = await res.text();
                    history.replaceState(null, '', url);
                    syncPills();
                    results.classList.remove('is-loading');
                    // basket.js re-binds the +/- buttons of the new cards.
                    window.dispatchEvent(new Event('menu:updated'));
                } catch (e) {
                    if (e.name === 'AbortError') { return; }
                    // Something went wrong: fall back to a normal page load.
                    window.location.href = url;
                }
            }

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(load, 250);
            });

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                clearTimeout(timer);
                load();
            });

            pills.forEach(function (pill) {
                pill.addEventListener('click', function (e) {
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) { return; }
                    e.preventDefault();
                    category = pill.dataset.category;
                    clearTimeout(timer);
                    load();
                });
            });
        })();
    </script>
@endpush