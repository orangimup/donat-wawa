@extends('layouts.app')

@section('title', $product->name . ' ' . __('Reviews') . ' — Donat Wawa')

@push('styles')
    @vite('resources/css/product.css')
@endpush

@section('content')

    <div class="product-page-wrap reviews-standalone-wrap">

        <section class="reviews-section reviews-full-page">
            <div class="container-ww">
                <div class="reviews-page-header">
                    <h1 class="reviews-title">{{ __('Customer Reviews') }}</h1>
                    <p class="reviews-page-product-info">{{ $product->name }} &bull; {{ __(':count Reviews total', ['count' => $reviewSummary['count']]) }}</p>
                </div>

                <div class="reviews-page-layout">
                    {{-- Left Column: Summary + filter --}}
                    <div class="reviews-page-sidebar">
                        <div class="rating-summary-card rating-summary-wide">
                            <h3>{{ __('Rating Summary') }}</h3>
                            <div class="rating-summary-body">
                                <div class="rating-summary-left">
                                    <div class="rating-score">{{ number_format($reviewSummary['average'], 1) }}</div>
                                    <div class="star-rating" aria-label="{{ __(':rating out of 5 stars', ['rating' => number_format($reviewSummary['average'], 1)]) }}">
                                        <span class="star-rating-bg">★★★★★</span>
                                        <span class="star-rating-fg" style="width: {{ ($reviewSummary['average'] / 5) * 100 }}%">★★★★★</span>
                                    </div>
                                    <span class="rating-count">{{ __(':count Reviews', ['count' => $reviewSummary['count']]) }}</span>
                                </div>
                                <div class="rating-bars">
                                    @foreach ($reviewSummary['breakdown'] as $star => $row)
                                        <div class="rating-bar-row">
                                            <span>{{ $star }}</span>
                                            <div class="rating-bar-track">
                                                <div class="rating-bar-fill" style="width: {{ $row['percent'] }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Filter by rating (server side: ?rating=1..5) --}}
                        <div class="reviews-page-filter-box">
                            <span class="reviews-filter-title">{{ __('Filter by Rating') }}</span>
                            <div class="reviews-filter-pills" id="pageFilterPills">
                                <a href="{{ route('product.reviews', $product->slug) }}"
                                    class="review-filter-pill {{ $ratingFilter === null ? 'active' : '' }}">{{ __('All') }} ({{ $reviewSummary['count'] }})</a>
                                @foreach ($reviewSummary['breakdown'] as $star => $row)
                                    <a href="{{ route('product.reviews', [$product->slug, 'rating' => $star]) }}"
                                        class="review-filter-pill {{ $ratingFilter === $star ? 'active' : '' }}">★ {{ $star }} ({{ $row['count'] }})</a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Reviews List --}}
                    <div class="reviews-page-content">
                        <div class="reviews-list" id="pageReviewsList">
                            @forelse ($reviews as $review)
                                <div class="review-card" data-rating="{{ $review['star_group'] }}">
                                    <div class="review-card-head">
                                        <div class="review-avatar">{{ $review['initial'] }}</div>
                                        <div class="review-meta">
                                            <strong>{{ $review['name'] }}</strong>
                                            <span class="review-time">{{ $review['time'] }}</span>
                                        </div>
                                        <div class="star-rating star-rating-sm" aria-label="{{ __(':rating out of 5 stars', ['rating' => $review['rating']]) }}">
                                            <span class="star-rating-bg">★★★★★</span>
                                            <span class="star-rating-fg" style="width: {{ ($review['rating'] / 5) * 100 }}%">★★★★★</span>
                                        </div>
                                    </div>
                                    @if (filled($review['text']))
                                        <p class="review-text">{{ $review['text'] }}</p>
                                    @endif
                                </div>
                            @empty
                                <div class="reviews-modal-empty">
                                    <p>{{ $ratingFilter === null
                                        ? __('Belum ada ulasan untuk produk ini.')
                                        : __('No reviews found for this rating filter.') }}</p>
                                </div>
                            @endforelse
                        </div>

                        @if ($reviews->hasPages())
                            <nav class="reviews-pagination" aria-label="{{ __('Reviews pagination') }}">
                                {{ $reviews->onEachSide(1)->links('components.pagination') }}
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </section>

    </div>

@endsection
