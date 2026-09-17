@extends('layouts.app')

@section('title', 'Checkout — Donat Wawa')

@section('body-class', 'page-checkout')

@section('hide-footer')
@endsection

@push('styles')
    @vite(['resources/css/product.css', 'resources/css/checkout.css'])
@endpush

@section('content')

    <div class="container-ww checkout-page">
        <div class="checkout-header">
            <h1>Checkout</h1>
        </div>

        <div id="checkoutEmpty" class="checkout-empty" hidden>
            <p>Your basket is empty.</p>
            <a href="{{ url('/menu') }}" class="btn btn-primary">View Menu</a>
        </div>

        <div id="checkoutContent" class="checkout-layout" hidden>
            {{-- Order Summary --}}
            <div class="checkout-card">
                <h2>Order Summary</h2>

                <div class="checkout-items" id="checkoutItems"></div>

                <div class="checkout-totals">
                    <div class="checkout-totals-row">
                        <span>Subtotal</span>
                        <span id="checkoutSubtotal">Rp0</span>
                    </div>
                    <div class="checkout-totals-row">
                        <span>Shipping Fee</span>
                        <span id="checkoutShippingFee">Rp0</span>
                    </div>
                    <div class="checkout-totals-row checkout-totals-final">
                        <span>Total</span>
                        <span id="checkoutTotal">Rp0</span>
                    </div>
                </div>
            </div>

            {{-- Customer & Delivery Details --}}
            <div class="checkout-card">
                <h2>Customer &amp; Delivery Details</h2>

                <form class="checkout-form" id="checkoutForm" onsubmit="return false;">
                    <label class="checkout-field">
                        <span class="checkout-field-label">Full Name<span class="required-mark">*</span></span>
                        <input type="text" name="name" placeholder="Enter your full name" value="{{ auth()->user()->name ?? '' }}" required>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">Email</span>
                        <div class="checkout-field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                            <input type="email" name="email" placeholder="customer@donatwawa.com" value="{{ auth()->user()->email ?? '' }}" readonly>
                        </div>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">Phone Number<span class="required-mark">*</span></span>
                        <div class="checkout-field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <input type="tel" name="phone" placeholder="08xx xxxx xxxx" value="{{ auth()->user()->phone ?? '' }}" pattern="[0-9+\s-]{8,15}" required>
                        </div>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">Delivery Address<span class="required-mark">*</span></span>
                        <textarea name="address" rows="3" placeholder="Delivery address details..." required>{{ auth()->user()->address ?? '' }}</textarea>
                    </label>

                    <div class="checkout-field">
                        <span class="checkout-field-label">Delivery Time</span>

                        <div class="delivery-option" id="deliveryToday" data-method="today">
                            <div class="delivery-option-head">
                                <strong>Same-Day Delivery</strong>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"></path></svg>
                            </div>
                            <p>Estimated arrival in 60-90 minutes</p>
                            <span class="delivery-option-price">Rp5.000</span>
                        </div>

                        <div class="delivery-option" id="deliveryPreorder" data-method="preorder">
                            <div class="delivery-option-head">
                                <strong>Scheduled Pre-Order</strong>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path></svg>
                            </div>
                            <p>Choose your own delivery time</p>
                            <span class="delivery-option-price">Rp10.000</span>
                        </div>

                        <div class="delivery-schedule" id="deliverySchedule" hidden>
                            <label>
                                <span>Delivery Date</span>
                                <input type="date" name="delivery_date">
                            </label>
                            <label>
                                <span>Delivery Slot</span>
                                <select name="delivery_time">
                                    <option>09:00 - 10:00</option>
                                    <option>10:00 - 11:00</option>
                                    <option>13:00 - 14:00</option>
                                    <option>16:00 - 17:00</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="checkout-payment-bar" id="checkoutPaymentBar" hidden>
        <div class="checkout-payment-inner">
            <div>
                <span class="checkout-payment-label">Total Payment</span>
                <strong id="checkoutPaymentTotal">IDR 0</strong>
            </div>
            <button type="button" class="btn btn-primary checkout-pay-btn" id="checkoutPayBtn">
                Proceed to Payment
            </button>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/basket.js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const SHIPPING_FEE = { today: 5000, preorder: 10000 };
            let deliveryMethod = 'today';

            function adjustBottomPadding() {
                const bar = document.getElementById('checkoutPaymentBar');
                const page = document.querySelector('.checkout-page');
                if (bar && !bar.hidden) {
                    page.style.paddingBottom = (bar.offsetHeight + 40) + 'px';
                } else {
                    page.style.paddingBottom = '56px';
                }
            }

            function render() {
                const items = DonatBasket.getItems();
                const empty = document.getElementById('checkoutEmpty');
                const content = document.getElementById('checkoutContent');
                const paymentBar = document.getElementById('checkoutPaymentBar');

                if (items.length === 0) {
                    empty.hidden = false;
                    content.hidden = true;
                    paymentBar.hidden = true;
                    adjustBottomPadding();
                    return;
                }

                empty.hidden = true;
                content.hidden = false;
                paymentBar.hidden = false;

                const list = document.getElementById('checkoutItems');
                list.innerHTML = items.map(function (item) {
                    return `
                        <div class="checkout-item" data-slug="${item.slug}">
                            <img src="${item.image}" alt="${item.name}">
                            <div class="checkout-item-info">
                                <strong>${item.name}</strong>
                                <span>${DonatBasket.format(item.price)}</span>
                                <div class="qty-stepper">
                                    <button type="button" class="qty-btn" data-action="dec">−</button>
                                    <span class="checkout-item-qty">${item.qty}</span>
                                    <button type="button" class="qty-btn" data-action="inc">+</button>
                                </div>
                            </div>
                            <div class="checkout-item-side">
                                <button type="button" class="checkout-item-remove" aria-label="Remove">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"></path></svg>
                                </button>
                                <strong class="checkout-item-total">${DonatBasket.format(item.price * item.qty)}</strong>
                            </div>
                        </div>
                    `;
                }).join('');

                const subtotal = DonatBasket.getTotalPrice();
                const shipping = SHIPPING_FEE[deliveryMethod];
                const total = subtotal + shipping;

                document.getElementById('checkoutSubtotal').textContent = DonatBasket.format(subtotal);
                document.getElementById('checkoutShippingFee').textContent = DonatBasket.format(shipping);
                document.getElementById('checkoutTotal').textContent = DonatBasket.format(total);
                document.getElementById('checkoutPaymentTotal').textContent = 'IDR ' + total.toLocaleString('id-ID');

                document.getElementById('deliveryToday').classList.toggle('is-selected', deliveryMethod === 'today');
                document.getElementById('deliveryPreorder').classList.toggle('is-selected', deliveryMethod === 'preorder');
                document.getElementById('deliverySchedule').hidden = deliveryMethod !== 'preorder';

                adjustBottomPadding();
            }

            document.getElementById('checkoutItems').addEventListener('click', function (e) {
                const row = e.target.closest('.checkout-item');
                if (!row) return;
                const slug = row.dataset.slug;
                const item = DonatBasket.getItems().find(function (i) { return i.slug === slug; });
                if (!item) return;

                if (e.target.closest('[data-action="inc"]')) {
                    DonatBasket.setQty(slug, item.qty + 1);
                } else if (e.target.closest('[data-action="dec"]')) {
                    DonatBasket.setQty(slug, item.qty - 1);
                } else if (e.target.closest('.checkout-item-remove')) {
                    DonatBasket.removeItem(slug);
                }
            });

            document.querySelectorAll('.delivery-option').forEach(function (option) {
                option.addEventListener('click', function (e) {
                    // Klik di dalam field jadwal (date/select) jangan ganti pilihan.
                    if (e.target.closest('.delivery-schedule')) return;
                    deliveryMethod = option.dataset.method;
                    render();

                    if (deliveryMethod === 'preorder') {
                        requestAnimationFrame(function () {
                            document.getElementById('deliverySchedule')
                                .scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    }
                });
            });

            document.getElementById('checkoutPayBtn').addEventListener('click', function () {
                const form = document.getElementById('checkoutForm');

                // Kalau delivery preorder dipilih, jadwal wajib diisi juga.
                const dateInput = form.querySelector('[name="delivery_date"]');
                if (deliveryMethod === 'preorder') {
                    dateInput.required = true;
                } else {
                    dateInput.required = false;
                }

                if (!form.reportValidity()) {
                    // Browser otomatis nampilin & fokus ke field yang masih kosong/invalid.
                    return;
                }

                alert('Payment & saving the order to the database is not available yet — this is only the FE page.');
            });

            function syncBasketWithDatabase() {
                const items = DonatBasket.getItems();
                if (items.length === 0) {
                    render();
                    return;
                }

                const slugs = items.map(function (item) { return item.slug; });
                const query = slugs.map(function (s) { return 'slugs[]=' + encodeURIComponent(s); }).join('&');

                fetch('{{ route('checkout.sync-basket') }}?' + query)
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        const fresh = data.items || {};

                        const synced = items
                            .filter(function (item) { return fresh[item.slug] && fresh[item.slug].available; })
                            .map(function (item) {
                                const dbItem = fresh[item.slug];
                                return {
                                    slug: item.slug,
                                    name: dbItem.name,
                                    price: Number(dbItem.price),
                                    image: dbItem.image,
                                    qty: item.qty,
                                };
                            });

                        DonatBasket.replaceAll(synced);
                    })
                    .catch(function () { /* offline/gagal fetch: pakai data sessionStorage apa adanya */ })
                    .finally(render);
            }

            window.addEventListener('basket:updated', render);
            syncBasketWithDatabase();
        });
    </script>
@endpush