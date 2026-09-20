@extends('layouts.app')

@section('title', 'Order Confirmed — Donat Wawa')

@section('body-class', 'page-order-confirmed')

@section('hide-footer')
@endsection

@push('styles')
    @vite(['resources/css/order-confirmed.css'])
@endpush

@section('content')

    <div id="confirmContent" class="confirm-page" hidden>

        <div class="confirm-hero">
            <div class="confirm-hero-icon">
                <img src="{{ asset('assets/icons/success.svg') }}" alt="Order berhasil">
            </div>
            <h1>Thank You!</h1>
            <p>Your delicious donut journey has begun. We've received your order and are waiting for payment confirmation to
                start baking.</p>
        </div>

        <div class="confirm-meta">
            <span class="confirm-order-id">
                <span id="confirmOrderCode">#—</span>
                <button type="button" id="confirmCopyCode" aria-label="Copy order number">
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
                <h2>Order Details</h2>
                <span class="confirm-status-badge is-pending" id="confirmStatusBadge">
                    <span>Awaiting Payment</span>
                </span>
            </div>

            <div id="confirmItems"></div>

            <div class="confirm-totals">
                <div class="confirm-totals-row">
                    <span>Subtotal</span>
                    <span id="confirmSubtotal">Rp0</span>
                </div>
                <div class="confirm-totals-row">
                    <span>Shipping Fee</span>
                    <span id="confirmShippingFee">Rp0</span>
                </div>
                <div class="confirm-totals-row confirm-total-final">
                    <span>Total Payment</span>
                    <span id="confirmTotal">Rp0</span>
                </div>
            </div>

            <div class="confirm-details">
                <div class="confirm-details-block">
                    <h3>Name</h3>
                    <p id="confirmName">—</p>
                </div>
                <div class="confirm-details-block">
                    <h3>Address</h3>
                    <p id="confirmAddress">—</p>
                </div>
                <div class="confirm-details-block">
                    <h3>Delivery Time</h3>
                    <p id="confirmDeliveryLabel">—</p>
                </div>
                <div class="confirm-details-block">
                    <h3>Phone</h3>
                    <p id="confirmPhone">—</p>
                </div>
            </div>
        </div>

        <div class="confirm-actions">
            <button type="button" class="btn btn-primary" id="confirmTrackBtn">Track Order Status</button>
            <a href="{{ url('/menu') }}" class="btn btn-outline">Back to Menu</a>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const DELIVERY_LABEL = {
                today: 'Same-Day',
                preorder: 'Scheduled Order',
            };

            let order = null;
            try {
                order = JSON.parse(sessionStorage.getItem('donat_last_order') || 'null');
            } catch (e) {
                order = null;
            }

            if (!order || !Array.isArray(order.items) || order.items.length === 0) {
                window.location.href = '{{ url('/checkout') }}';
                return;
            }

            function formatRupiah(value) {
                return 'Rp' + Number(value || 0).toLocaleString('id-ID');
            }

            document.getElementById('confirmContent').hidden = false;
            document.getElementById('confirmOrderCode').textContent = '#' + order.code;
            document.getElementById('confirmOrderDate').textContent = order.placedAtLabel || '';
            document.getElementById('confirmAddress').textContent = order.address || '—';
            document.getElementById('confirmPhone').textContent = order.phone || '—';
            document.getElementById('confirmName').textContent = order.name || '—';
            document.getElementById('confirmDeliveryLabel').textContent =
                DELIVERY_LABEL[order.deliveryMethod] || '—';

            const statusBadge = document.getElementById('confirmStatusBadge');
            const statusLabel = statusBadge.querySelector('span:last-child');
            if (order.status === 'success') {
                statusBadge.classList.remove('is-pending');
                statusBadge.classList.add('is-success');
                statusLabel.textContent = 'Payment Success';
            } else {
                statusBadge.classList.remove('is-success');
                statusBadge.classList.add('is-pending');
                statusLabel.textContent = 'Awaiting Payment';
            }

            document.getElementById('confirmItems').innerHTML = order.items.map(function (item) {
                return `
                            <div class="confirm-item">
                                <img src="${item.image}" alt="${item.name}">
                                <div class="confirm-item-info">
                                    <strong>${item.name}</strong>
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

            document.getElementById('confirmTrackBtn').addEventListener('click', function () {
                alert('Order tracking is not available yet — coming soon once the backend is wired up.');
            });
            if (window.DonatBasket && typeof DonatBasket.clear === 'function') {
                DonatBasket.clear();
            } else {
                sessionStorage.removeItem('donat_basket');
            }
        });
    </script>
@endpush