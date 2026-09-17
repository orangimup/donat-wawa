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
                    <a href="{{ route('product.reviews', $product->slug) }}" class="reviews-view-all-btn">
                        <span>See All Reviews</span>
                        <span class="reviews-view-all-pill">128</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>
        </section>

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
        });
    </script>
@endpush