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
        <form method="GET" action="{{ url('/menu') }}" class="menu-toolbar">
            <div class="menu-search">
                <input type="text" name="search" placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
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
                        class="filter-pill {{ $activeCategory === $key ? 'is-active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        {{-- Menu grid --}}
        <section class="section" style="padding-top:0;">
            <div class="menu-grid">
                @forelse($menuItems as $item)
                    @include('components.menu-card', ['item' => $item])
                @empty
                    <p style="grid-column: 1/-1; text-align:center; color:#8A7C72;">{{ __('No menu items match your search.') }}</p>
                @endforelse
            </div>
        </section>

        {{-- Pagination --}}
        @if(method_exists($menuItems, 'links'))
            <div class="menu-pagination">
                {{ $menuItems->onEachSide(1)->links('components.pagination') }}
            </div>
        @endif

    </div>

    @include('components.basket-bar')

@endsection

@push('scripts')
    @vite('resources/js/basket.js')
@endpush