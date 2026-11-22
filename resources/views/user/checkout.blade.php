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
            <h1>{{ __('Checkout') }}</h1>
        </div>

        <div id="checkoutEmpty" class="checkout-empty" hidden>
            <p>{{ __('Your basket is empty.') }}</p>
            <a href="{{ url('/menu') }}" class="btn btn-primary">{{ __('View Menu') }}</a>
        </div>

        <div id="checkoutContent" class="checkout-layout" hidden>
            {{-- Order Summary --}}
            <div class="checkout-card">
                <h2>{{ __('Order Summary') }}</h2>

                <div class="checkout-items" id="checkoutItems"></div>

                <div class="checkout-totals">
                    <div class="checkout-totals-row">
                        <span>{{ __('Subtotal') }}</span>
                        <span id="checkoutSubtotal">Rp0</span>
                    </div>
                    <div class="checkout-totals-row">
                        <span>{{ __('Shipping Fee') }}</span>
                        <span id="checkoutShippingFee">Rp0</span>
                    </div>
                    <div class="checkout-totals-row checkout-totals-final">
                        <span>{{ __('Total') }}</span>
                        <span id="checkoutTotal">Rp0</span>
                    </div>
                </div>
            </div>

            {{-- Customer & Delivery Details --}}
            <div class="checkout-card">
                <h2>{{ __('Customer & Delivery Details') }}</h2>

                <form class="checkout-form" id="checkoutForm" onsubmit="return false;">
                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Full Name') }}<span class="required-mark">*</span></span>
                        <input type="text" name="name" placeholder="{{ __('Enter your full name') }}"
                            value="{{ auth()->user()->name ?? '' }}" required>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Email') }}</span>
                        <div class="checkout-field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            </svg>
                            <input type="email" name="email" placeholder="customer@donatwawa.com"
                                value="{{ auth()->user()->email ?? '' }}" readonly>
                        </div>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Phone Number') }}<span class="required-mark">*</span></span>
                        <div class="checkout-field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                            <input type="tel" name="phone" placeholder="08xx xxxx xxxx"
                                value="{{ auth()->user()->phone ?? '' }}" pattern="[0-9+\s-]{8,15}" required>
                        </div>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Delivery Address') }}<span class="required-mark">*</span></span>
                        <textarea name="address" rows="3" placeholder="{{ __('Delivery address details...') }}"
                            required>{{ auth()->user()->address ?? '' }}</textarea>
                    </label>

                    <div class="checkout-field">
                        <span class="checkout-field-label">{{ __('Delivery Time') }}</span>

                        <div class="delivery-option" id="deliveryToday" data-method="today">
                            <div class="delivery-option-head">
                                <strong>{{ __('Same-Day') }}</strong>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"></path>
                                </svg>
                            </div>
                            <p>{{ __('Estimated arrival in 60-90 minutes') }}</p>
                            <span class="delivery-option-price">Rp{{ number_format($shippingFees['same_day'], 0, ',', '.') }}</span>
                        </div>

                        <div class="delivery-option" id="deliveryPreorder" data-method="preorder">
                            <div class="delivery-option-head">
                                <strong>{{ __('Scheduled Order') }}</strong>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                                </svg>
                            </div>
                            <p>{{ __('Choose your own delivery time') }}</p>
                            <span class="delivery-option-price">Rp{{ number_format($shippingFees['pre_order'], 0, ',', '.') }}</span>
                        </div>

                        <div class="delivery-schedule" id="deliverySchedule" hidden>
                            <label>
                                <span>{{ __('Delivery Date') }}</span>
                                <input type="date" name="delivery_date" min="{{ $earliestPreOrderDate }}">
                            </label>
                            <label>
                                <span>{{ __('Delivery Slot') }}</span>
                                <select name="delivery_time">
                                    @foreach ($deliverySlots as $slot)
                                        <option>{{ $slot }}</option>
                                    @endforeach
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
                <span class="checkout-payment-label">{{ __('Total Payment') }}</span>
                <strong id="checkoutPaymentTotal">IDR 0</strong>
            </div>
            <button type="button" class="btn btn-primary checkout-pay-btn" id="checkoutPayBtn">
                {{ __('Proceed to Payment') }}
            </button>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/basket.js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const SHIPPING_FEE = { today: {{ $shippingFees['same_day'] }}, preorder: {{ $shippingFees['pre_order'] }} };
            const DELIVERY_TYPE = { today: 'same_day', preorder: 'pre_order' };
            let pendingPayment = null;
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
                                    <button type="button" class="checkout-item-remove" aria-label="${window.I18N.remove}">
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
                    if (e.target.closest('.delivery-schedule')) return;
                    deliveryMethod = option.dataset.method;
                    pendingPayment = null;
                    render();

                    if (deliveryMethod === 'preorder') {
                        requestAnimationFrame(function () {
                            document.getElementById('deliverySchedule')
                                .scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    }
                });
            });

            function postJson(url, payload) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (body) {
                        if (!res.ok) {
                            const first = body.errors ? Object.values(body.errors)[0] : null;
                            const error = new Error((first && first[0]) || body.message || window.I18N.somethingWrong);
                            error.status = res.status;
                            throw error;
                        }
                        return body;
                    });
                });
            }

            function createPayment(form, formData, items) {
                const payload = {
                    name: formData.get('name'),
                    phone: formData.get('phone'),
                    address: formData.get('address'),
                    delivery_type: DELIVERY_TYPE[deliveryMethod],
                    items: items.map(function (item) { return { slug: item.slug, qty: item.qty }; }),
                };

                if (deliveryMethod === 'preorder') {
                    payload.delivery_date = formData.get('delivery_date');
                    payload.delivery_slot = formData.get('delivery_time');
                }

                return postJson('{{ route('checkout.store') }}', payload).then(function (order) {
                    return postJson('{{ route('checkout.snap-token') }}', { order_code: order.order_code })
                        .then(function (data) {
                            return { order: order, snapToken: data.snap_token };
                        });
                });
            }

            document.getElementById('checkoutForm').addEventListener('input', function () {
                pendingPayment = null;
            });
            window.addEventListener('basket:updated', function () {
                pendingPayment = null;
            });

            document.getElementById('checkoutPayBtn').addEventListener('click', function () {
                const form = document.getElementById('checkoutForm');

                const dateInput = form.querySelector('[name="delivery_date"]');
                dateInput.required = (deliveryMethod === 'preorder');

                if (!form.reportValidity()) {
                    return;
                }

                const items = DonatBasket.getItems();
                const formData = new FormData(form);

                const payBtn = document.getElementById('checkoutPayBtn');
                payBtn.disabled = true;
                payBtn.textContent = window.I18N.processing;

                function resetButton() {
                    payBtn.disabled = false;
                    payBtn.textContent = window.I18N.proceed;
                }

                const ready = pendingPayment
                    ? Promise.resolve(pendingPayment)
                    : createPayment(form, formData, items);

                ready
                    .then(function (payment) {
                        pendingPayment = payment;

                        const now = new Date();
                        const orderPayload = {
                            code: payment.order.order_code,
                            placedAtLabel: now.toLocaleDateString(window.I18N.locale, {
                                day: 'numeric', month: 'long', year: 'numeric',
                            }) + ', ' + now.toLocaleTimeString(window.I18N.locale, {
                                hour: '2-digit', minute: '2-digit',
                            }) + ' WIB',
                            items: items,
                            subtotal: payment.order.subtotal,
                            shippingFee: payment.order.shipping_fee,
                            total: payment.order.total,
                            name: formData.get('name'),
                            email: formData.get('email'),
                            phone: formData.get('phone'),
                            address: formData.get('address'),
                            deliveryMethod: deliveryMethod,
                            deliveryDate: formData.get('delivery_date'),
                            deliveryTime: formData.get('delivery_time'),
                        };

                        function wait(ms) {
                            return new Promise(function (resolve) { setTimeout(resolve, ms); });
                        }

                        /* Ask the server (which asks Midtrans) whether the order is really paid.
                           The popup's own "success"/"pending" is never trusted. When it says
                           "success" the settlement can lag a moment, so try a couple more times. */
                        function verifyPaid(claimedSuccess, attempt) {
                            return postJson('{{ url('/checkout/orders') }}/' + encodeURIComponent(orderPayload.code) + '/sync', {})
                                .then(function (state) {
                                    if (state.paid || !claimedSuccess || attempt >= 2) { return state; }
                                    return wait(2000).then(function () { return verifyPaid(claimedSuccess, attempt + 1); });
                                });
                        }

                        function finish(status, result) {
                            verifyPaid(status === 'success', 0)
                                .then(function (state) {
                                    if (!state.paid) {
                                        /* Not paid: stay here so the customer can try again
                                           (same order and payment token, no duplicate order). */
                                        alert(window.I18N.paymentNotReceived);
                                        resetButton();
                                        return;
                                    }

                                    orderPayload.status = 'success';
                                    orderPayload.paymentResult = result;
                                    sessionStorage.setItem('donat_last_order', JSON.stringify(orderPayload));
                                    window.location.href = '{{ route('checkout.success') }}';
                                })
                                .catch(function () {
                                    alert(window.I18N.paymentNotReceived);
                                    resetButton();
                                });
                        }

                        window.snap.pay(payment.snapToken, {
                            onSuccess: function (result) { finish('success', result); },
                            onPending: function (result) { finish('pending', result); },
                            onError: function () {
                                alert(window.I18N.paymentFailed);
                                resetButton();
                            },
                            onClose: function () {
                                resetButton();
                            },
                        });
                    })
                    .catch(function (err) {
                        pendingPayment = null;
                        alert(err.message || window.I18N.somethingWrong);
                        resetButton();
                    });
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
                    .catch(function () { })
                    .finally(render);
            }

            window.addEventListener('basket:updated', render);
            syncBasketWithDatabase();
        });
    </script>
@endpush