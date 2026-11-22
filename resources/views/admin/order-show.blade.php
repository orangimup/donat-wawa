@extends('layouts.admin')

@section('title', 'Order Detail — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/order.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');
        $payment = $order['payment'];
        $paymentLabels = [
            'pending' => 'Waiting for payment',
            'settled' => 'Paid',
            'expired' => 'Payment expired',
            'failed' => 'Payment failed',
            'cancelled' => 'Payment cancelled',
            'refunded' => 'Refunded',
        ];
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>Order Detail</h1>
            <p>{{ $order['code'] }} &bull; {{ $order['placed_label'] }}</p>
        </div>
        <span class="order-status-pill order-status-{{ $order['status'] }}">{{ $statusLabels[$order['status']] }}</span>
    </div>

    <div class="order-detail-grid">

        <div class="order-detail-main">

            {{-- Customer & delivery --}}
            <div class="admin-card order-detail-card">
                <h2 class="order-detail-title">Customer &amp; Delivery</h2>
                <div class="order-detail-facts">
                    <div>
                        <span class="order-summary-label">Customer</span>
                        <div class="order-summary-value">{{ $order['customer'] }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">Phone</span>
                        <div class="order-summary-value">{{ $order['phone'] }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">Email</span>
                        <div class="order-summary-value">{{ $order['email'] }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">Delivery type</span>
                        <div class="order-summary-value">{{ $order['delivery'] }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">Delivery date</span>
                        <div class="order-summary-value">{{ $order['delivery_date'] ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">Time slot</span>
                        <div class="order-summary-value">{{ $order['delivery_slot'] ?? '-' }}</div>
                    </div>
                    <div class="is-wide">
                        <span class="order-summary-label">Address</span>
                        <div class="order-summary-value">{{ $order['address'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="admin-card order-detail-card">
                <h2 class="order-detail-title">Ordered Items</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table order-detail-items">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="is-num">Price</th>
                                <th class="is-num">Qty</th>
                                <th class="is-num">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order['items'] as $item)
                                <tr>
                                    <td>
                                        <div class="order-detail-product">
                                            <img src="{{ $item['image'] }}" alt="" width="40" height="40">
                                            <span>{{ $item['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="is-num">{{ $formatRupiah($item['price']) }}</td>
                                    <td class="is-num">{{ $item['qty'] }}</td>
                                    <td class="is-num is-strong">{{ $formatRupiah($item['line_total']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="order-detail-side">

            {{-- Payment --}}
            <div class="admin-card order-detail-card">
                <h2 class="order-detail-title">Payment</h2>
                <div class="order-summary">
                    <div class="order-summary-row">
                        <div>
                            <span class="order-summary-label">Status</span>
                            <div class="order-summary-value is-strong">{{ $paymentLabels[$payment['status']] ?? 'No payment yet' }}</div>
                        </div>
                        <div>
                            <span class="order-summary-label">Method</span>
                            <div class="order-summary-value">{{ $payment['method'] }}</div>
                        </div>
                    </div>
                    @if ($payment['paid_label'])
                        <div class="order-summary-row">
                            <div>
                                <span class="order-summary-label">Paid at</span>
                                <div class="order-summary-value">{{ $payment['paid_label'] }}</div>
                            </div>
                        </div>
                    @endif
                    <div class="order-summary-row">
                        <div>
                            <span class="order-summary-label">Subtotal</span>
                            <div class="order-summary-value">{{ $formatRupiah($order['subtotal']) }}</div>
                        </div>
                        <div>
                            <span class="order-summary-label">Shipping</span>
                            <div class="order-summary-value">{{ $formatRupiah($order['shipping_fee']) }}</div>
                        </div>
                    </div>
                    <div class="order-summary-row">
                        <div>
                            <span class="order-summary-label">Total</span>
                            <div class="order-summary-value is-total">{{ $formatRupiah($order['total']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Refund (if any) --}}
            @if ($order['refund'])
                <div class="admin-card order-detail-card">
                    <h2 class="order-detail-title">Refund</h2>
                    <p class="order-detail-text">
                        {{ $order['refund']['source'] }} &bull; {{ $formatRupiah($order['refund']['amount']) }}
                        &bull; <strong>{{ ucfirst($order['refund']['status']) }}</strong>
                    </p>
                    <a href="{{ route('admin.refund.show', $order['refund']['id']) }}" class="admin-btn admin-btn-ghost order-detail-link">Open refund</a>
                </div>
            @endif

            {{-- Accept / reject lives in the order list (Manage button), so this page is view-only. --}}
            @if ($order['status'] === 'rejected' && $order['rejection_reason'])
                <div class="admin-card order-detail-card">
                    <h2 class="order-detail-title">Rejection reason</h2>
                    <p class="order-detail-text">{{ $order['rejection_reason'] }}</p>
                </div>
            @endif
        </div>
    </div>

    <p class="order-detail-back"><a href="{{ route('admin.orders.index') }}">&larr; Back to orders</a></p>

@endsection
