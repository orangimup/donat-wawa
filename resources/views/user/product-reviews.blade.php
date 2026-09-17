@extends('layouts.app')

@section('title', $product->name . ' Reviews — Donat Wawa')

@push('styles')
    @vite('resources/css/product.css')
@endpush

@section('content')

    <div class="product-page-wrap reviews-standalone-wrap">

        <section class="reviews-section reviews-full-page">
            <div class="container-ww">
                <div class="reviews-page-header">
                    <h1 class="reviews-title">Customer Reviews</h1>
                    <p class="reviews-page-product-info">{{ $product->name }} &bull; 128 Reviews total</p>
                </div>

                @php
                    $allReviews = [
                        ['initial' => 'S', 'name' => 'Siti N.', 'time' => '1 week ago', 'rating' => 4.5, 'star_group' => 4,
                            'text' => 'Enak, glazing cokelatnya tebal dan mantap. Buat yang suka banget cokelat pasti doyan. Bagussss buat harga segini.'],
                        ['initial' => 'B', 'name' => 'Budi S.', 'time' => '2 weeks ago', 'rating' => 5, 'star_group' => 5,
                            'text' => 'Favorit anak-anak di rumah! Bagussss banget kualitasnya. Rasanya konsisten dari dulu, cokelatnya premium.'],
                        ['initial' => 'D', 'name' => 'Dian M.', 'time' => '3 weeks ago', 'rating' => 4.5, 'star_group' => 4,
                            'text' => 'Cocok banget dipaduin sama kopi pait. Manisnya pas, gak lebay. Bagussss, recommended!'],
                        ['initial' => 'R', 'name' => 'Rina K.', 'time' => '1 month ago', 'rating' => 5, 'star_group' => 5,
                            'text' => 'Donat paling enak se-Jakarta! Bagussss pelayanannya juga cepat. Cokelatnya beneran dark chocolate yang mahal.'],
                        ['initial' => 'F', 'name' => 'Fajar H.', 'time' => '1 month ago', 'rating' => 5, 'star_group' => 5,
                            'text' => 'Teksturnya lembut banget, toppingnya melimpah. Bagussss buat oleh-oleh atau camilan sore.'],
                        ['initial' => 'A', 'name' => 'Anisa R.', 'time' => '1 month ago', 'rating' => 4, 'star_group' => 4,
                            'text' => 'Donatnya enak, toppingnya banyak. Teksturnya empuk walau dimakan besok paginya. Overall puas!'],
                        ['initial' => 'T', 'name' => 'Tono W.', 'time' => '2 months ago', 'rating' => 5, 'star_group' => 5,
                            'text' => 'Selalu jadi pilihan buat hampers dan traktiran kantor. Rasanya konsisten enak dari tahun ke tahun.'],
                        ['initial' => 'M', 'name' => 'Maya L.', 'time' => '2 months ago', 'rating' => 4.5, 'star_group' => 4,
                            'text' => 'Donat terenak yang pernah aku coba! Cokelatnya rich banget, gak pelit. Worth every penny.'],
                        ['initial' => 'H', 'name' => 'Hendra P.', 'time' => '3 months ago', 'rating' => 5, 'star_group' => 5,
                            'text' => 'Beli 2 lusin buat acara keluarga, langsung ludes dalam sekejap! Pasti repeat order lagi.'],
                        ['initial' => 'L', 'name' => 'Lina S.', 'time' => '3 months ago', 'rating' => 4, 'star_group' => 4,
                            'text' => 'Teksturnya pas, gak bikin enek. Cocok buat yang gak suka donat terlalu manis.'],
                        ['initial' => 'K', 'name' => 'Kiki V.', 'time' => '3 months ago', 'rating' => 3, 'star_group' => 3,
                            'text' => 'Rasanya lumayan enak, cuma waktu pengiriman agak lama kemarin. Overall oke.'],
                        ['initial' => 'W', 'name' => 'Wahyu P.', 'time' => '4 months ago', 'rating' => 5, 'star_group' => 5,
                            'text' => 'Best artisan donut! Dough-nya super fluffy, topping cokelatnya melimpah ruah.'],
                    ];

                    $ratingBreakdown = [
                        5 => 82,
                        4 => 14,
                        3 => 3,
                        2 => 1,
                        1 => 0,
                    ];
                @endphp

                <div class="reviews-page-layout">
                    {{-- Left Column: Sticky Summary --}}
                    <div class="reviews-page-sidebar">
                        <div class="rating-summary-card rating-summary-wide">
                            <h3>Rating Summary</h3>
                            <div class="rating-summary-body">
                                <div class="rating-summary-left">
                                    <div class="rating-score">4.9</div>
                                    <div class="star-rating" aria-label="4.9 out of 5 stars">
                                        <span class="star-rating-bg">★★★★★</span>
                                        <span class="star-rating-fg" style="width: {{ (4.9 / 5) * 100 }}%">★★★★★</span>
                                    </div>
                                    <span class="rating-count">128 Reviews</span>
                                </div>
                                <div class="rating-bars">
                                    @foreach ($ratingBreakdown as $star => $percent)
                                        <div class="rating-bar-row">
                                            <span>{{ $star }}</span>
                                            <div class="rating-bar-track">
                                                <div class="rating-bar-fill" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Filter by rating --}}
                        <div class="reviews-page-filter-box">
                            <span class="reviews-filter-title">Filter by Rating</span>
                            <div class="reviews-filter-pills" id="pageFilterPills">
                                <button type="button" class="review-filter-pill active" data-filter="all">All (128)</button>
                                <button type="button" class="review-filter-pill" data-filter="5">★ 5 (82)</button>
                                <button type="button" class="review-filter-pill" data-filter="4">★ 4 (14)</button>
                                <button type="button" class="review-filter-pill" data-filter="3">★ 3 (3)</button>
                                <button type="button" class="review-filter-pill" data-filter="2">★ 2 (1)</button>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Reviews List --}}
                    <div class="reviews-page-content">
                        <div class="reviews-list" id="pageReviewsList">
                            @foreach ($allReviews as $review)
                                <div class="review-card" data-rating="{{ $review['star_group'] }}">
                                    <div class="review-card-head">
                                        <div class="review-avatar">{{ $review['initial'] }}</div>
                                        <div class="review-meta">
                                            <strong>{{ $review['name'] }}</strong>
                                            <span class="review-time">{{ $review['time'] }}</span>
                                        </div>
                                        <div class="star-rating star-rating-sm" aria-label="{{ $review['rating'] }} out of 5 stars">
                                            <span class="star-rating-bg">★★★★★</span>
                                            <span class="star-rating-fg" style="width: {{ ($review['rating'] / 5) * 100 }}%">★★★★★</span>
                                        </div>
                                    </div>
                                    <p class="review-text">{{ $review['text'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="reviews-modal-empty" id="pageReviewsEmpty" style="display: none;">
                            <p>No reviews found for this rating filter.</p>
                        </div>

                        <nav class="reviews-pagination" id="pageReviewsPagination" aria-label="Reviews pagination"></nav>
                    </div>
                </div>
            </div>
        </section>

    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const PAGE_SIZE = 8;

            const pills = document.querySelectorAll('#pageFilterPills .review-filter-pill');
            const cards = Array.from(document.querySelectorAll('#pageReviewsList .review-card'));
            const emptyEl = document.getElementById('pageReviewsEmpty');
            const paginationEl = document.getElementById('pageReviewsPagination');

            let currentFilter = 'all';
            let currentPage = 1;

            function getFilteredCards() {
                return cards.filter(function (card) {
                    return currentFilter === 'all' || card.dataset.rating === currentFilter;
                });
            }

            function renderPagination(totalItems) {
                paginationEl.innerHTML = '';
                const totalPages = Math.ceil(totalItems / PAGE_SIZE);
                if (totalPages <= 1) return;

                function makeBtn(label, page, opts) {
                    opts = opts || {};
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'review-page-btn' + (opts.active ? ' active' : '') + (opts.nav ? ' review-page-nav' : '');
                    btn.textContent = label;
                    btn.disabled = !!opts.disabled;
                    if (!opts.disabled && !opts.active) {
                        btn.addEventListener('click', function () {
                            currentPage = page;
                            renderPage();
                            document.getElementById('pageReviewsList').scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }
                    return btn;
                }

                function makeEllipsis() {
                    const span = document.createElement('span');
                    span.className = 'review-page-ellipsis';
                    span.textContent = '…';
                    return span;
                }

                paginationEl.appendChild(makeBtn('‹', currentPage - 1, { nav: true, disabled: currentPage === 1 }));

                const pagesToShow = new Set([1, totalPages, currentPage, currentPage - 1, currentPage + 1]);
                let lastRendered = 0;

                Array.from(pagesToShow)
                    .filter(function (p) { return p >= 1 && p <= totalPages; })
                    .sort(function (a, b) { return a - b; })
                    .forEach(function (p) {
                        if (lastRendered && p - lastRendered > 1) {
                            paginationEl.appendChild(makeEllipsis());
                        }
                        paginationEl.appendChild(makeBtn(String(p), p, { active: p === currentPage }));
                        lastRendered = p;
                    });

                paginationEl.appendChild(makeBtn('›', currentPage + 1, { nav: true, disabled: currentPage === totalPages }));
            }

            function renderPage() {
                const filtered = getFilteredCards();
                const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
                if (currentPage > totalPages) currentPage = totalPages;

                const start = (currentPage - 1) * PAGE_SIZE;
                const end = start + PAGE_SIZE;

                cards.forEach(function (card) { card.style.display = 'none'; });
                filtered.slice(start, end).forEach(function (card) { card.style.display = ''; });

                if (emptyEl) {
                    emptyEl.style.display = filtered.length === 0 ? 'block' : 'none';
                }

                renderPagination(filtered.length);
            }

            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    pills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.filter;
                    currentPage = 1;
                    renderPage();
                });
            });

            renderPage();
        });
    </script>
@endpush