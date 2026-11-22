@extends('layouts.app')

@section('title', 'Order Confirmed — Donat Wawa')

@section('body-class', 'page-order-confirmed')

@section('hide-footer')
@endsection

@push('styles')
    @vite(['resources/css/order-confirmed.css'])
@endpush

@section('content')

    <p id="confirmVerifying" class="confirm-verifying" style="text-align:center;padding:4rem 1rem;">
        {{ __('Verifying your payment...') }}
    </p>

    <div id="confirmContent" class="confirm-page" hidden>

        <div class="confirm-hero">
            <div class="confirm-hero-icon">
                <img src="{{ asset('assets/icons/success.svg') }}" alt="{{ __('Order placed successfully') }}">
            </div>
            <h1>{{ __('Thank You!') }}</h1>
            <p>{{ __("Your payment has been received. We're getting your donuts ready — you can follow the progress in your order history.") }}
            </p>
        </div>

        <div class="confirm-meta">
            <span class="confirm-order-id">
                <span id="confirmOrderCode">#—</span>
                <button type="button" id="confirmCopyCode" aria-label="{{ __('Copy order number') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                </button>
            </span>
            <span class="confirm-order-date" id="confirmOrderDate"></span>
        </div>

        <div class="confirm-card">
            <div class="confirm-card-head">
                <h2>{{ __('Order Details') }}</h2>
                <span class="confirm-status-badge is-success" id="confirmStatusBadge">
                    <span>{{ __('Payment Success') }}</span>
                </span>
            </div>

            <div id="confirmItems"></div>

            <div class="confirm-totals">
                <div class="confirm-totals-row">
                    <span>{{ __('Subtotal') }}</span>
                    <span id="confirmSubtotal">Rp0</span>
                </div>
                <div class="confirm-totals-row">
                    <span>{{ __('Shipping Fee') }}</span>
                    <span id="confirmShippingFee">Rp0</span>
                </div>
                <div class="confirm-totals-row confirm-total-final">
                    <span>{{ __('Total Payment') }}</span>
                    <span id="confirmTotal">Rp0</span>
                </div>
            </div>

            <div class="confirm-details">
                <div class="confirm-details-block">
                    <h3>{{ __('Name') }}</h3>
                    <p id="confirmName">—</p>
                </div>
                <div class="confirm-details-block">
                    <h3>{{ __('Address') }}</h3>
                    <p id="confirmAddress">—</p>
                </div>
                <div class="confirm-details-block">
                    <h3>{{ __('Delivery Time') }}</h3>
                    <p id="confirmDeliveryLabel">—</p>
                </div>
                <div class="confirm-details-block">
                    <h3>{{ __('Phone') }}</h3>
                    <p id="confirmPhone">—</p>
                </div>
            </div>
        </div>

        <div class="confirm-actions">
            <button type="button" class="btn btn-primary" id="confirmTrackBtn">{{ __('Track Order Status') }}</button>
            <a href="{{ url('/menu') }}" class="btn btn-outline">{{ __('Back to Menu') }}</a>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const HISTORY_URL = '{{ route('settings.orders') }}';
            const CHECKOUT_URL = '{{ url('/checkout') }}';
            const DELIVERY_LABEL = {
                today: window.I18N.sameDay,
                preorder: window.I18N.scheduled,
            };

            let order = null;
            try {
                order = JSON.parse(sessionStorage.getItem('donat_last_order') || 'null');
            } catch (e) {
                order = null;
            }

            if (!order || !Array.isArray(order.items) || order.items.length === 0) {
                window.location.href = CHECKOUT_URL;
                return;
            }

            function formatRupiah(value) {
                return 'Rp' + Number(value || 0).toLocaleString('id-ID');
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value == null ? '' : String(value);
                return div.innerHTML;
            }

            /* Ask the server to pull the real payment status from Midtrans.
               Nothing the browser says ("success" from the Snap popup) is trusted. */
            function checkPayment() {
                return fetch('{{ url('/checkout/orders') }}/' + encodeURIComponent(order.code) + '/sync', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                }).then(function (res) {
                    if (!res.ok) { throw new Error('sync failed'); }
                    return res.json();
                });
            }

            function wait(ms) {
                return new Promise(function (resolve) { setTimeout(resolve, ms); });
            }

            /* If the popup said "success" the payment is usually settled a moment later on
               Midtrans' side (slow connection), so give it a couple more tries before giving up. */
            function verify() {
                const claimedSuccess = order.status === 'success';
                let attempt = 0;

                function run() {
                    return checkPayment().then(function (state) {
                        if (state.paid || !claimedSuccess || attempt >= 2) { return state; }
                        attempt += 1;
                        return wait(2000).then(run);
                    });
                }

                return run();
            }

            function notPaid() {
                /* Not paid (yet): this is NOT a success page. Send the customer back to checkout
                   so they can try paying again. */
                sessionStorage.removeItem('donat_last_order');
                window.location.replace(CHECKOUT_URL);
            }

            function showSuccess(orderUrl) {
                document.getElementById('confirmVerifying').hidden = true;
                document.getElementById('confirmContent').hidden = false;

                document.getElementById('confirmOrderCode').textContent = '#' + order.code;
                document.getElementById('confirmOrderDate').textContent = order.placedAtLabel || '';
                document.getElementById('confirmAddress').textContent = order.address || '—';
                document.getElementById('confirmPhone').textContent = order.phone || '—';
                document.getElementById('confirmName').textContent = order.name || '—';
                document.getElementById('confirmDeliveryLabel').textContent =
                    DELIVERY_LABEL[order.deliveryMethod] || '—';

                document.getElementById('confirmItems').innerHTML = order.items.map(function (item) {
                    return `
                        <div class="confirm-item">
                            <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
                            <div class="confirm-item-info">
                                <strong>${escapeHtml(item.name)}</strong>
                                <span>${item.qty} x ${formatRupiah(item.price)}</span>
                            </div>
                            <span class="confirm-item-price">${formatRupiah(item.price * item.qty)}</span>
                        </div>
                    `;
                }).join('');

                document.getElementById('confirmSubtotal').textContent = formatRupiah(order.subtotal);
                document.getElementById('confirmShippingFee').textContent = formatRupiah(order.shippingFee);
                document.getElementById('confirmTotal').textContent = formatRupiah(order.total);

                document.getElementById('confirmCopyCode').addEventListener('click', function () {
                    navigator.clipboard.writeText(order.code);
                });

                /* "Track Order Status" opens this order in the customer's order history. */
                document.getElementById('confirmTrackBtn').addEventListener('click', function () {
                    window.location.href = orderUrl || HISTORY_URL;
                });

                /* Only a paid order empties the basket. */
                if (window.DonatBasket && typeof DonatBasket.clear === 'function') {
                    DonatBasket.clear();
                } else {
                    sessionStorage.removeItem('donat_basket');
                }
            }

            verify()
                .then(function (state) {
                    if (state.paid) {
                        showSuccess(state.order_url);
                    } else {
                        notPaid();
                    }
                })
                .catch(function () {
                    /* Could not confirm the payment, so do not claim success. */
                    notPaid();
                });
        });
    </script>
@endpush
