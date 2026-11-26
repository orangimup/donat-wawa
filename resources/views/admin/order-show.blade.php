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

    {{-- Refreshed in place by dwLiveRefresh (layouts/admin) when this order changes --}}
    <div id="order-live" data-live-version="{{ md5(json_encode($order)) }}">

    <div class="admin-page-header">
        <div>
            <h1>{{ __('Order Detail') }}</h1>
            <p>{{ $order['code'] }} &bull; {{ $order['placed_label'] }}</p>
        </div>
        <span class="order-status-pill order-status-{{ $order['status'] }}">{{ __($statusLabels[$order['status']]) }}</span>
    </div>

    <div class="order-detail-grid">

        <div class="order-detail-main">

            {{-- Customer & delivery --}}
            <div class="admin-card order-detail-card">
                <h2 class="order-detail-title">{{ __('Customer & Delivery') }}</h2>
                <div class="order-detail-facts">
                    <div>
                        <span class="order-summary-label">{{ __('Customer') }}</span>
                        <div class="order-summary-value">{{ $order['customer'] }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">{{ __('Phone') }}</span>
                        <div class="order-summary-value">{{ $order['phone'] }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">{{ __('Email') }}</span>
                        <div class="order-summary-value">{{ $order['email'] }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">{{ __('Delivery type') }}</span>
                        <div class="order-summary-value">{{ __($order['delivery']) }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">{{ __('Delivery date') }}</span>
                        <div class="order-summary-value">{{ $order['delivery_date'] ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="order-summary-label">{{ __('Time slot') }}</span>
                        <div class="order-summary-value">{{ $order['delivery_slot'] ?? '-' }}</div>
                    </div>
                    <div class="is-wide">
                        <span class="order-summary-label">{{ __('Address') }}</span>
                        <div class="order-summary-value">{{ $order['address'] }}</div>
                    </div>
                    <div class="is-wide">
                        <span class="order-summary-label">{{ __('Distance') }}</span>
                        <div class="order-summary-value">{{ $order['distance_km'] !== null ? number_format($order['distance_km'], 1) . ' km' : '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="admin-card order-detail-card">
                <h2 class="order-detail-title">{{ __('Ordered Items') }}</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table order-detail-items">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th class="is-num">{{ __('Price') }}</th>
                                <th class="is-num">{{ __('Qty') }}</th>
                                <th class="is-num">{{ __('Subtotal') }}</th>
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
                <h2 class="order-detail-title">{{ __('Payment') }}</h2>
                <div class="order-summary">
                    <div class="order-summary-row">
                        <div>
                            <span class="order-summary-label">{{ __('Status') }}</span>
                            <div class="order-summary-value is-strong">{{ __($paymentLabels[$payment['status']] ?? 'No payment yet') }}</div>
                        </div>
                        <div>
                            <span class="order-summary-label">{{ __('Method') }}</span>
                            <div class="order-summary-value">{{ $payment['method'] }}</div>
                        </div>
                    </div>
                    @if ($payment['paid_label'])
                        <div class="order-summary-row">
                            <div>
                                <span class="order-summary-label">{{ __('Paid at') }}</span>
                                <div class="order-summary-value">{{ $payment['paid_label'] }}</div>
                            </div>
                        </div>
                    @endif
                    <div class="order-summary-row">
                        <div>
                            <span class="order-summary-label">{{ __('Subtotal') }}</span>
                            <div class="order-summary-value">{{ $formatRupiah($order['subtotal']) }}</div>
                        </div>
                        <div>
                            <span class="order-summary-label">{{ __('Shipping fee') }}</span>
                            <div class="order-summary-value">{{ $formatRupiah($order['shipping_fee']) }}</div>
                        </div>
                    </div>
                    <div class="order-summary-row">
                        <div>
                            <span class="order-summary-label">{{ __('Total') }}</span>
                            <div class="order-summary-value is-total">{{ $formatRupiah($order['total']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($order['refund'])
                <div class="admin-card order-detail-card order-refund-card">
                    <div class="order-refund-card-head">
                        <h2 class="order-detail-title">{{ __('Refund') }}</h2>
                        <span class="order-refund-pill order-refund-{{ $order['refund']['status'] }}">{{ match ($order['refund']['status']) { 'refunded' => __('Refunded'), 'rejected' => __('Refund rejected'), default => __('Refund pending') } }}</span>
                    </div>
                    <div class="order-refund-card-facts">
                        <div>
                            <span class="order-summary-label">{{ __('Source') }}</span>
                            <div class="order-refund-card-value">{{ $order['refund']['source'] }}</div>
                        </div>
                        <div>
                            <span class="order-summary-label">{{ __('Refund Amount') }}</span>
                            <div class="order-refund-card-value is-amount">{{ $formatRupiah($order['refund']['amount']) }}</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.refund.show', $order['refund']['id']) }}" class="admin-btn admin-btn-ghost order-detail-link order-refund-card-btn">{{ __('Detail refund') }}</a>
                </div>
            @endif

            @if ($order['status'] === 'rejected' && $order['rejection_reason'])
                <div class="admin-card order-detail-card">
                    <h2 class="order-detail-title">{{ __('Rejection reason') }}</h2>
                    <p class="order-detail-text">{{ $order['rejection_reason'] }}</p>
                </div>
            @endif
        </div>
    </div>

    <p class="order-detail-back"><a href="{{ route('admin.orders.index') }}">{{ __('← Back to orders') }}</a></p>

    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            var poll = dwLiveRefresh('#order-live');
            window.addEventListener('admin:order-completed', poll);
        })();
    </script>
@endpush