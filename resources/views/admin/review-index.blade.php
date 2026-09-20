@extends('layouts.admin')

@section('title', 'Review Management — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/review.css')
@endpush

@section('content')

    @php
        $sortLabels = [
            'latest' => 'Latest',
            'oldest' => 'Oldest',
            'rating_high' => 'Rating: High to Low',
            'rating_low' => 'Rating: Low to High',
            'product' => 'Product',
        ];

        $ratingTabs = [
            '5' => '5 star',
            '4' => '4 star',
            '3' => '3 star & below',
        ];
    @endphp

    {{-- Shared clip path for half stars (see admin.partials.stars) --}}
    <svg width="0" height="0" style="position: absolute;" aria-hidden="true" focusable="false">
        <defs>
            <clipPath id="reviewStarHalf">
                <rect x="0" y="0" width="12" height="24"></rect>
            </clipPath>
        </defs>
    </svg>

    <div class="admin-page-header">
        <div>
            <h1>Review Management</h1>
            <p>Monitor customer feedback and maintain the quality of the Donat Wawa experience.</p>
        </div>
    </div>

    <div class="review-stats">
        <div class="review-stat-card is-featured">
            <div class="review-stat-head">
                <h2 class="review-stat-title">Total Reviews</h2>
                <span class="review-stat-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        <path d="M13 8H7"></path>
                        <path d="M17 12H7"></path>
                    </svg>
                </span>
            </div>
            <div class="review-stat-foot">
                <p class="review-stat-value">{{ $totalReviews }}</p>
                <div class="review-stat-meta">
                    <span class="review-stat-badge">+12%</span>
                    <span>this month</span>
                </div>
            </div>
        </div>

        <div class="review-stat-card">
            <div class="review-stat-head">
                <h2 class="review-stat-title">Average Ratings</h2>
                <span class="review-stat-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor"
                        stroke-width="1.5" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"></polygon>
                    </svg>
                </span>
            </div>
            <div class="review-stat-foot">
                <p class="review-stat-value">{{ number_format($averageRating, 1) }}<small>/5.0</small></p>
                <div class="review-stat-stars">
                    @include('admin.partials.stars', ['value' => $averageRating, 'size' => 16])
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="admin-filter-pills">
                <a href="{{ route('admin.review', array_filter(['search' => $search, 'sort' => $sort])) }}"
                    class="admin-filter-pill {{ $rating === 'all' ? 'is-active' : '' }}">All</a>
                @foreach ($ratingTabs as $key => $label)
                    <a href="{{ route('admin.review', array_filter(['rating' => $key, 'search' => $search, 'sort' => $sort])) }}"
                        class="admin-filter-pill {{ $rating === (string) $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <form method="GET" class="admin-sort" id="sortForm">
                <label for="sortTrigger">Sort by:</label>
                <input type="hidden" name="rating" value="{{ $rating }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" id="sortValue" value="{{ $sort }}">

                <div class="admin-select" id="sortSelect">
                    <button type="button" class="admin-select-trigger" id="sortTrigger" aria-haspopup="listbox"
                        aria-expanded="false">
                        <span id="sortTriggerLabel">{{ $sortLabels[$sort] ?? 'Latest' }}</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <ul class="admin-select-menu" id="sortMenu" role="listbox">
                        @foreach ($sortLabels as $key => $label)
                            <li role="option" data-value="{{ $key }}"
                                class="admin-select-option {{ $sort === $key ? 'is-selected' : '' }}">{{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table review-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th class="review-col-product">Product</th>
                        <th>Review</th>
                        <th class="review-col-rating">Rating</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>{{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}</td>
                            <td class="review-col-product">{{ $review['product'] }}</td>
                            <td><span class="review-excerpt" title="{{ $review['text'] }}">&ldquo;{{ $review['text'] }}&rdquo;</span></td>
                            <td class="review-col-rating">
                                @include('admin.partials.stars', ['value' => $review['rating'], 'size' => 18])
                            </td>
                            <td class="review-nowrap">{{ $review['date_label'] }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <button type="button" class="admin-icon-btn"
                                        aria-label="View review by {{ $review['reviewer'] }}"
                                        onclick="openReviewModal(this)"
                                        data-reviewer="{{ $review['reviewer'] }}"
                                        data-email="{{ $review['email'] }}"
                                        data-since="{{ $review['member_since'] }}"
                                        data-product="{{ $review['product'] }}"
                                        data-price="{{ $review['product_price'] }}"
                                        data-image="{{ $review['product_image'] }}"
                                        data-rating="{{ $review['rating'] }}"
                                        data-text="{{ $review['text'] }}"
                                        data-date="{{ $review['date_label'] }}"
                                        data-order="{{ $review['order_code'] }}">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.4" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.review.destroy', $review['id']) }}" method="POST"
                                        class="admin-delete-form"
                                        data-name="the review by {{ $review['reviewer'] }} for {{ $review['product'] }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-icon-btn admin-icon-btn-danger"
                                            aria-label="Delete review by {{ $review['reviewer'] }}"
                                            onclick="openDeleteModal(this.closest('form'))">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="6">No reviews found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <span class="admin-pagination-info">
                Showing {{ $reviews->count() }} of {{ $reviews->total() }} entries
            </span>
            {{ $reviews->onEachSide(1)->links('admin.partials.pagination') }}
        </div>
    </div>

    {{-- Review Detail popup --}}
    <div class="admin-modal-overlay admin-modal-overlay-center review-modal-overlay" id="reviewModalOverlay">
        <div class="admin-modal review-modal" role="dialog" aria-modal="true" aria-labelledby="reviewModalTitle">
            <div class="review-modal-head">
                <h2 id="reviewModalTitle">Review Detail</h2>
                <button type="button" class="admin-modal-close" onclick="closeReviewModal()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="review-modal-body">
                <div class="review-user">
                    <span class="review-avatar" id="reviewAvatar" aria-hidden="true"></span>
                    <div class="review-user-info">
                        <span class="review-user-name" id="reviewName"></span>
                        <span class="review-user-email" id="reviewEmail"></span>
                    </div>
                    <div class="review-since">
                        <span>Member since</span>
                        <strong id="reviewSince"></strong>
                    </div>
                </div>

                <div class="review-product">
                    <img src="" alt="" id="reviewProductImage">
                    <div>
                        <strong id="reviewProductName"></strong>
                        <span id="reviewProductPrice"></span>
                    </div>
                </div>

                <div class="review-rating-row">
                    <span class="review-stars" id="reviewStars" role="img"></span>
                    <span class="review-rating-score"><span id="reviewScore"></span><small>/5.0</small></span>
                </div>

                <blockquote class="review-quote" id="reviewText"></blockquote>

                <div class="review-meta">
                    <span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <path d="M16 2v4M8 2v4M3 10h18"></path>
                        </svg>
                        Reviewed on <span id="reviewDate"></span>
                    </span>
                    <span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"></path>
                            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
                            <path d="M12 17.5v-11"></path>
                        </svg>
                        <span id="reviewOrder"></span>
                    </span>
                </div>
            </div>

            <div class="review-modal-footer">
                <button type="button" class="admin-btn" onclick="closeReviewModal()">Close</button>
            </div>
        </div>
    </div>

    {{-- Delete confirmation (same pattern as the product page) --}}
    <div class="admin-modal-overlay admin-modal-overlay-center" id="deleteModalOverlay">
        <div class="admin-modal admin-modal-sm">
            <div class="admin-modal-head">
                <div>
                    <h2>Delete Review?</h2>
                    <p class="admin-modal-subtitle" id="deleteModalText">Are you sure you want to delete this review?</p>
                </div>
                <button type="button" class="admin-modal-close" onclick="closeDeleteModal()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="admin-form-actions">
                <div class="admin-form-actions-right">
                    <button type="button" class="admin-btn admin-btn-outline"
                        onclick="closeDeleteModal()">Cancel</button>
                    <button type="button" class="admin-btn admin-btn-danger" id="deleteConfirmBtn">Delete Review</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        /* Sort-by dropdown (same behaviour as the other admin index pages) */
        (function () {
            const sortSelect = document.getElementById('sortSelect');
            const sortTrigger = document.getElementById('sortTrigger');
            const sortTriggerLabel = document.getElementById('sortTriggerLabel');
            const sortMenu = document.getElementById('sortMenu');
            const sortValue = document.getElementById('sortValue');
            const sortForm = document.getElementById('sortForm');

            function closeSortMenu() {
                sortSelect.classList.remove('is-open');
                sortTrigger.setAttribute('aria-expanded', 'false');
            }

            sortTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = sortSelect.classList.toggle('is-open');
                sortTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            sortMenu.querySelectorAll('.admin-select-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    sortValue.value = opt.dataset.value;
                    sortTriggerLabel.textContent = opt.textContent;
                    sortMenu.querySelectorAll('.admin-select-option').forEach(function (o) { o.classList.remove('is-selected'); });
                    opt.classList.add('is-selected');
                    closeSortMenu();
                    sortForm.submit();
                });
            });

            document.addEventListener('click', function (e) {
                if (!sortSelect.contains(e.target)) closeSortMenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSortMenu();
            });
        })();

        /* Review Detail popup */
        (function () {
            const overlay = document.getElementById('reviewModalOverlay');
            const STAR = '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26';

            const setText = function (id, value) { document.getElementById(id).textContent = value; };

            function renderStars(rating) {
                const wrap = document.getElementById('reviewStars');
                wrap.setAttribute('aria-label', rating + ' out of 5 stars');
                wrap.innerHTML = '';

                for (let i = 1; i <= 5; i++) {
                    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    svg.setAttribute('width', '20');
                    svg.setAttribute('height', '20');
                    svg.setAttribute('viewBox', '0 0 24 24');
                    svg.setAttribute('stroke', 'currentColor');
                    svg.setAttribute('stroke-width', '1.6');
                    svg.setAttribute('stroke-linejoin', 'round');
                    svg.setAttribute('aria-hidden', 'true');

                    const poly = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
                    poly.setAttribute('points', STAR);
                    poly.setAttribute('fill', i <= rating ? 'currentColor' : 'none');
                    if (i > rating) poly.setAttribute('opacity', '0.3');

                    svg.appendChild(poly);
                    wrap.appendChild(svg);
                }
            }

            window.openReviewModal = function (btn) {
                const d = btn.dataset;

                setText('reviewAvatar', d.reviewer.charAt(0).toUpperCase());
                setText('reviewName', d.reviewer);
                setText('reviewEmail', d.email);
                setText('reviewSince', d.since);

                const image = document.getElementById('reviewProductImage');
                image.src = d.image;
                image.alt = d.product;
                setText('reviewProductName', d.product);
                setText('reviewProductPrice', d.price);

                renderStars(parseInt(d.rating, 10));
                setText('reviewScore', parseFloat(d.rating).toFixed(1));
                setText('reviewText', '\u201C' + d.text + '\u201D');
                setText('reviewDate', d.date);
                setText('reviewOrder', d.order);

                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            };

            window.closeReviewModal = function () {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            };

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeReviewModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('open')) closeReviewModal();
            });
        })();

        /* Delete confirmation */
        (function () {
            const overlay = document.getElementById('deleteModalOverlay');
            const text = document.getElementById('deleteModalText');
            const confirmBtn = document.getElementById('deleteConfirmBtn');
            let activeForm = null;

            window.openDeleteModal = function (form) {
                activeForm = form;
                text.textContent = 'Are you sure you want to delete ' + form.dataset.name + '? This action cannot be undone.';
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            };

            window.closeDeleteModal = function () {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
                activeForm = null;
            };

            confirmBtn.addEventListener('click', function () {
                if (activeForm) activeForm.submit();
            });

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeDeleteModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('open')) closeDeleteModal();
            });
        })();
    </script>
@endpush