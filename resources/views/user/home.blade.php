@extends('layouts.app')

@section('title', 'Donat Wawa')

@push('styles')
    @vite(['resources/css/home.css', 'resources/css/basket-bar.css'])
@endpush

@section('content')

    <section class="hero">
        <div class="hero-donuts" aria-hidden="true">
            <img src="{{ asset('assets/images/header.png') }}"
                style="width:100%; height:100%; object-fit:cover; object-position:center;" alt="">
        </div>
        <div class="container-ww">
            <div class="hero-content">
                <h1>Authentic Artisanal Donuts Made with Love.</h1>
                <p>Freshly baked every morning in Malang using premium ingredients. From our kitchen to your doorstep,
                    experience the warmth of home-style gourmet donuts.</p>
                <div class="hero-actions">
                    <a href="#menu" class="btn btn-primary">Order Now</a>
                    <a href="{{ route('about') }}" class="btn btn-outline">About Us</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="bundling">
        <div class="container-ww">
            <div class="section-heading">
                <h2>Favorite Bundle Packages</h2>
                <p>The perfect choice to enjoy with family, close friends, or office colleagues.</p>
            </div>
            <div class="bundle-grid">
                @foreach($bundles as $bundle)
                    @include('components.bundle-card', ['bundle' => $bundle])
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" id="menu" style="padding-top:0;">
        <div class="container-ww">
            <div class="section-heading">
                <h2>Favorite Menu</h2>
                <p>Choose your favorite flavor. Made fresh every day with high-quality ingredients.</p>
            </div>
            <div class="menu-grid">
                @forelse($menuItems as $item)
                    @include('components.menu-card', ['item' => $item])
                @empty
                    <p style="grid-column: 1/-1; text-align:center; color:#8A7C72;">No menu items yet — check back soon.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:24px;">
        <div class="container-ww">
            <div class="promo-banner">
                <img class="bg" src="{{ asset('assets/images/promo-banner.png') }}" alt="" aria-hidden="true">
                <div class="overlay"></div>
                <div class="promo-content">
                    <div class="promo-text">
                        <p class="promo-eyebrow" style="color: #FBBF24;">100% QUALITY</p>
                        <h2>The Delight of Brioche Donuts Without Synthetic Preservatives.</h2>
                        <p>Every batch of dough is made with high-quality butter, natural yeast, and omega eggs. Fried at
                            precise temperatures to produce donuts that rise soft without excess oiliness.</p>
                        <div class="promo-stats">
                            <div>
                                <p class="stat-num">100%</p>
                                <p class="stat-label">Natural Halal Ingredients</p>
                            </div>
                            <div>
                                <p class="stat-num">Open Daily</p>
                                <p class="stat-label">From 9:00 AM</p>
                            </div>
                        </div>
                    </div>
                    <a href="menu" class="btn btn-white">Order for Your Event</a>
                </div>
            </div>
        </div>
    </section>

    @include('components.basket-bar')

@endsection

@push('scripts')
    @vite('resources/js/basket.js')
@endpush