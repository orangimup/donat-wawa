@extends('layouts.app')

@section('title', $product->name . ' — Donat Wawa')

@push('styles')
    @vite('resources/css/product.css')
@endpush

@section('content')

    <div class="product-page-wrap">

        <section class="container-ww product-detail">
            <div class="product-media {{ ! $product->is_available ? 'is-soldout' : '' }}">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">

                @if ($product->badge === 'best_seller')
                    <span class="menu-badge">Best Seller</span>
                @elseif ($product->badge === 'new')
                    <span class="menu-badge">New Menu</span>
                @endif

                @unless ($product->is_available)
                    <span class="menu-soldout-pill">Sold Out</span>
                @endunless
            </div>

            <div class="product-info">
                <span class="product-category">{{ ucfirst($product->category) }}</span>
                <h1>{{ $product->name }}</h1>
                <p class="product-description">{{ $product->description ?: 'Belum ada deskripsi untuk produk ini.' }}</p>

                <div class="product-price-row">
                    <span class="product-price">{{ $product->price_formatted }}</span>
                    <span class="product-stock {{ $product->stock <= 5 && $product->stock > 0 ? 'is-low' : '' }}">
                        @if ($product->stock > 0)
                            Tersedia: {{ $product->stock }} pcs
                        @else
                            Stok habis
                        @endif
                    </span>
                </div>

                @if ($product->is_available)
                    <div class="product-order-form">
                        <span class="qty-label">QUANTITY</span>
                        <div class="qty-stepper">
                            <button type="button" class="qty-btn" id="qtyDecBtn" aria-label="Kurangi">−</button>
                            <input type="number" id="qtyInput" value="1" min="1" max="{{ $product->stock }}" readonly>
                            <button type="button" class="qty-btn" id="qtyIncBtn" aria-label="Tambah">+</button>
                        </div>
                    </div>

                    <div class="checkout-widget" id="checkoutWidget"
                        data-slug="{{ $product->slug }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ $product->price }}"
                        data-image="{{ $product->image_url }}">

                        <button type="button" class="btn btn-primary product-order-btn" id="checkoutPlainBtn">
                            Check out
                        </button>

                        <div class="basket-inline-bar" id="basketInlineBar" hidden>
                            <div class="basket-inline-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                    <path d="M3 6h18"></path>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                                <span class="basket-inline-badge" id="basketInlineBadge">0</span>
                            </div>

                            <div class="basket-inline-info">
                                <strong id="basketInlineCount">0 Item</strong>
                                <span id="basketInlineTotal">Rp0</span>
                            </div>

                            <button type="button" class="btn btn-primary basket-inline-cta" id="checkoutInlineBtn">
                                Check out
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"></path></svg>
                            </button>
                        </div>
                    </div>
                @else
                    <button type="button" class="btn btn-outline product-order-btn" disabled>Restocking Soon</button>
                @endif
            </div>
        </section>

        {{-- ===================== CUSTOMER REVIEWS ===================== --}}
        <section class="reviews-section">
            <div class="container-ww">
                <h2 class="reviews-title">Customer Reviews</h2>

                @php
                    $staticReviews = [
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
                    ];

                    $allModalReviews = [
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

                <div class="reviews-grid">
                    <div class="rating-summary-card">
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

                    @foreach ($staticReviews as $review)
                        <div class="review-card">
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

                <div class="reviews-footer">
                    <button type="button" class="reviews-view-all-btn" id="openReviewsModal">
                        <span>See All Reviews</span>
                        <span class="reviews-view-all-pill">128</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </section>

        {{-- ===================== REVIEWS MODAL POPUP ===================== --}}
        <div class="reviews-modal-overlay" id="reviewsModalOverlay" aria-hidden="true">
            <div class="reviews-modal-card" role="dialog" aria-modal="true" aria-labelledby="modalReviewsTitle">
                <div class="reviews-modal-header">
                    <div class="reviews-modal-title-wrap">
                        <h3 id="modalReviewsTitle" class="reviews-modal-title">Customer Reviews</h3>
                        <span class="reviews-modal-subtitle">{{ $product->name }} &bull; 128 Reviews</span>
                    </div>
                    <button type="button" class="reviews-modal-close" id="closeReviewsModal" aria-label="Close modal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="reviews-modal-body">
                    {{-- Rating Summary & Filters in Modal --}}
                    <div class="reviews-modal-summary">
                        <div class="reviews-modal-score-box">
                            <div class="reviews-modal-score-num">4.9</div>
                            <div>
                                <div class="star-rating" aria-label="4.9 out of 5 stars">
                                    <span class="star-rating-bg">★★★★★</span>
                                    <span class="star-rating-fg" style="width: {{ (4.9 / 5) * 100 }}%">★★★★★</span>
                                </div>
                                <div class="reviews-modal-score-sub">128 ratings total</div>
                            </div>
                        </div>

                        <div class="reviews-filter-pills" id="modalFilterPills">
                            <button type="button" class="review-filter-pill active" data-filter="all">All (128)</button>
                            <button type="button" class="review-filter-pill" data-filter="5">★ 5 (82)</button>
                            <button type="button" class="review-filter-pill" data-filter="4">★ 4 (14)</button>
                            <button type="button" class="review-filter-pill" data-filter="3">★ 3 (3)</button>
                            <button type="button" class="review-filter-pill" data-filter="2">★ 2 (1)</button>
                        </div>
                    </div>

                    {{-- Scrollable Reviews List --}}
                    <div class="reviews-modal-list" id="modalReviewsList">
                        @foreach ($allModalReviews as $review)
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

                    <div class="reviews-modal-empty" id="modalReviewsEmpty" style="display: none;">
                        <p>No reviews found for this rating filter.</p>
                    </div>
                </div>

                <div class="reviews-modal-footer">
                    <a href="{{ route('product.reviews', $product->slug) }}" class="reviews-modal-fullpage-link">
                        Open in separate page
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="similiar-products-section">
                <div class="container-ww">
                    <div class="section-heading-row">
                        <h2>Similar Products</h2>
                        <a href="{{ url('/menu') }}" class="see-all-link">
                            See All
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"></path></svg>
                        </a>
                    </div>
                    <div class="menu-grid">
                        @foreach ($related as $item)
                            @include('components.menu-card', ['item' => $item])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

    </div>

@endsection

@push('scripts')
    @vite('resources/js/basket.js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const widget = document.getElementById('checkoutWidget');
            if (!widget) return;

            const product = {
                slug: widget.dataset.slug,
                name: widget.dataset.name,
                price: widget.dataset.price,
                image: widget.dataset.image,
            };

            function currentQty() {
                const input = document.getElementById('qtyInput');
                return input ? parseInt(input.value, 10) || 1 : 1;
            }

            function stepQty(delta) {
                const input = document.getElementById('qtyInput');
                const max = parseInt(input.max || '99', 10);
                let value = parseInt(input.value || '1', 10) + delta;
                value = Math.max(1, Math.min(value, max));
                input.value = value;

                // Kalau produk ini SUDAH ada di keranjang, angka di stepper
                // ini mewakili qty yang beneran ada di basket — jadi begitu
                // digeser, langsung update basket juga (bukan cuma tampilan
                // lokal doang), supaya "4 Item" / total-nya ikut refresh.
                const existing = DonatBasket.getItems().find(function (i) { return i.slug === product.slug; });
                if (existing) {
                    DonatBasket.setQty(product.slug, value);
                }
            }

            document.getElementById('qtyDecBtn').addEventListener('click', function () { stepQty(-1); });
            document.getElementById('qtyIncBtn').addEventListener('click', function () { stepQty(1); });

            function addThenCheckout() {
                const added = DonatBasket.addItem(product, currentQty());
                if (!added) return; // guest: popup login sudah ditampilkan oleh addItem()
                window.location.href = '{{ url('/checkout') }}';
            }

            function goToCheckoutOnly() {
                // Produk ini (dan item lain) sudah ada di keranjang — tombol
                // ini di dalam basket-inline-bar cuma buat LANJUT, bukan
                // nambah qty lagi. Jangan panggil addItem() di sini.
                window.location.href = '{{ url('/checkout') }}';
            }

            function renderWidget() {
                const totalQty = DonatBasket.getTotalQty();
                const plainBtn = document.getElementById('checkoutPlainBtn');
                const inlineBar = document.getElementById('basketInlineBar');
                const qtyInput = document.getElementById('qtyInput');

                // Kalau produk INI sudah ada di keranjang, tampilkan qty-nya
                // yang sebenarnya (bukan selalu ke-reset jadi 1) supaya
                // stepper "QUANTITY" tidak menyesatkan.
                const existing = DonatBasket.getItems().find(function (i) { return i.slug === product.slug; });
                if (qtyInput && existing) {
                    qtyInput.value = existing.qty;
                }

                if (totalQty > 0) {
                    plainBtn.hidden = true;
                    inlineBar.hidden = false;
                    document.getElementById('basketInlineBadge').textContent = totalQty;
                    document.getElementById('basketInlineCount').textContent = totalQty + ' Item';
                    document.getElementById('basketInlineTotal').textContent = DonatBasket.format(DonatBasket.getTotalPrice());
                } else {
                    plainBtn.hidden = false;
                    inlineBar.hidden = true;
                }
            }

            document.getElementById('checkoutPlainBtn').addEventListener('click', addThenCheckout);
            document.getElementById('checkoutInlineBtn').addEventListener('click', goToCheckoutOnly);

            renderWidget();
            window.addEventListener('basket:updated', renderWidget);

            // ================= Reviews Modal Logic =================
            const openModalBtn = document.getElementById('openReviewsModal');
            const closeModalBtn = document.getElementById('closeReviewsModal');
            const reviewsOverlay = document.getElementById('reviewsModalOverlay');
            const filterPills = document.querySelectorAll('#modalFilterPills .review-filter-pill');
            const reviewCards = document.querySelectorAll('#modalReviewsList .review-card');
            const emptyNotice = document.getElementById('modalReviewsEmpty');

            function openReviewsModal() {
                if (!reviewsOverlay) return;
                reviewsOverlay.classList.add('open');
                reviewsOverlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeReviewsModal() {
                if (!reviewsOverlay) return;
                reviewsOverlay.classList.remove('open');
                reviewsOverlay.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            if (openModalBtn) {
                openModalBtn.addEventListener('click', openReviewsModal);
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeReviewsModal);
            }

            if (reviewsOverlay) {
                reviewsOverlay.addEventListener('click', function (e) {
                    if (e.target === reviewsOverlay) {
                        closeReviewsModal();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && reviewsOverlay && reviewsOverlay.classList.contains('open')) {
                    closeReviewsModal();
                }
            });

            // Rating Filter Pills
            filterPills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    filterPills.forEach(p => p.classList.remove('active'));
                    this.classList.add('active');

                    const filterValue = this.dataset.filter;
                    let visibleCount = 0;

                    reviewCards.forEach(function (card) {
                        const cardRating = card.dataset.rating;
                        if (filterValue === 'all' || cardRating === filterValue) {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (emptyNotice) {
                        emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
                    }
                });
            });
        });
    </script>
@endpush