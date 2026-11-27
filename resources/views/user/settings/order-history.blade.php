@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Order History'))
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/settings.css')
    @vite('resources/css/order-history.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

        // Shopee-style tabs: one per stage of an order, plus "needs a review" and "refunds".
        $tabs = [
            'processing' => __('Processing'),
            'shipped' => __('Shipping'),
            'completed' => __('Completed'),
            'review' => __('To Review'),
            'cancelled' => __('Cancelled'),
            'refund' => __('Refunds'),
        ];

        // One badge per order: the real stage of the order, unless a refund is moving (that matters more).
        $badgeOf = function (array $order): array {
            if ($order['refund_status'] === 'refunded') {
                return ['refunded', __('Refunded')];
            }

            if (in_array($order['refund_status'], ['pending', 'approved', 'picked_up'], true)) {
                return ['refund-pending', match (true) {
                    $order['refund_status'] === 'approved' => __('Refund disetujui'),
                    $order['refund_status'] === 'picked_up' => __('Barang diambil'),
                    ($order['refund']['source'] ?? null) === 'pembeli' => __('Awaiting review'),
                    default => __('Refund in progress'),
                }];
            }

            return match ($order['status']) {
                'waiting' => ['waiting', __('Waiting')],
                'process' => ['process', __('Process')],
                'shipped' => ['shipped', __('Shipped')],
                'done' => ['done', __('Completed')],
                default => ['cancelled', __('Cancelled')],
            };
        };

        // The ONE thing the customer most likely wants to do next with this order.
        $primaryAction = function (array $order): ?string {
            return match (true) {
                $order['refund_needs_bank'] => 'bank',
                $order['can_complete'] => 'complete',
                $order['can_review'] => 'review',
                $order['can_refund'] => 'refund',
                default => null,
            };
        };
    @endphp

    <div class="container-ww settings-layout">

        @include('partials.settings-sidebar', ['active' => 'my-orders'])

        <div class="settings-content order-history-content">

            <div class="order-history-head">
                <h1>{{ __('Order History') }}</h1>

                <a href="{{ url('/menu') }}" class="order-history-view-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                        <path d="M3 6h18"></path>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    {{ __('View Order') }}
                </a>
            </div>

            {{-- Refreshed in place by the live-refresh script below when an order changes --}}
            <div id="order-history-live"
                data-live-version="{{ md5(json_encode([$orders->items(), $counts])) }}">
                <div class="order-history-tabs-row">
                    <div class="order-history-tabs">
                        <a href="{{ route('settings.orders') }}"
                            class="order-history-tab {{ $group === 'all' ? 'is-active' : '' }}">
                            {{ __('All Order') }}
                            <span class="order-history-tab-count">{{ $counts['all'] }}</span>
                        </a>
                        @foreach ($tabs as $key => $label)
                            <a href="{{ route('settings.orders', ['status' => $key]) }}"
                                class="order-history-tab {{ $group === $key ? 'is-active' : '' }}">
                                {{ $label }}
                                <span class="order-history-tab-count">{{ $counts[$key] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="order-history-card">
                    <div class="order-history-table-wrap">
                        <table class="order-history-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Order') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Total') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="is-action">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    @php
                                        $first = $order['items']->first();
                                        $extra = $order['items']->count() - 1;
                                        [$badgeKey, $badgeLabel] = $badgeOf($order);
                                        $action = $primaryAction($order);
                                    @endphp
                                    <tr class="order-history-row" data-href="{{ route('settings.orders.show', $order['id']) }}">
                                        <td>
                                            <div class="order-history-item-cell">
                                                <img src="{{ $first['image'] }}" alt="{{ $first['name'] }}">
                                                <div>
                                                    <strong>{{ $first['name'] }}</strong>
                                                    <span>
                                                        {{ __('Qty: :qty', ['qty' => $first['qty']]) }}
                                                        @if ($extra > 0)
                                                            &bull; {{ __('+:count more items', ['count' => $extra]) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('Date') }}">
                                            <span class="oh-cell-main">{{ $order['placed_at']->translatedFormat('d M Y') }}</span>
                                            <span class="oh-cell-sub">{{ $order['placed_at']->format('H:i') }} WIB</span>
                                        </td>
                                        <td data-label="{{ __('Total') }}">
                                            <span class="order-history-amount">{{ $formatRupiah($order['total']) }}</span>
                                            <span class="oh-cell-sub">{{ ($order['paid'] || $order['refund_status'] !== null) && $order['method'] !== '-' ? $order['method'] : __('Unpaid') }}</span>
                                        </td>
                                        <td data-label="{{ __('Status') }}">
                                            <span class="oh-badge oh-badge--{{ $badgeKey }}">{{ $badgeLabel }}</span>
                                            @if ($order['refund_status'] === 'rejected')
                                                <span class="oh-cell-hint is-danger">{{ __('Refund rejected') }}</span>
                                            @endif
                                        </td>
                                        <td class="is-action">
                                            <div class="order-history-actions" onclick="event.stopPropagation()">
                                                @if ($action === 'complete')
                                                    <form method="POST" action="{{ route('settings.orders.complete', $order['id']) }}"
                                                        class="order-history-inline-form"
                                                        data-confirm="{{ __('Have you received this order?') }}"
                                                        data-confirm-title="{{ __('Order received?') }}"
                                                        data-confirm-ok="{{ __('Yes, received') }}">
                                                        @csrf
                                                        <button type="submit" class="oh-btn oh-btn--primary">{{ __('Pesanan Diterima') }}</button>
                                                    </form>
                                                @elseif ($action === 'review')
                                                    <a href="{{ route('settings.orders.review', $order['id']) }}" class="oh-btn oh-btn--primary">{{ __('Beri Ulasan') }}</a>
                                                @elseif ($action === 'bank')
                                                    <a href="{{ route('settings.orders.refund.bank', $order['id']) }}" class="oh-btn oh-btn--attention">{{ __('Isi Rekening') }}</a>
                                                @elseif ($action === 'refund')
                                                    <a href="{{ route('settings.orders.refund', $order['id']) }}" class="oh-btn oh-btn--primary">{{ __('Ajukan Refund') }}</a>
                                                @endif

                                                <a href="{{ route('settings.orders.show', $order['id']) }}" class="oh-link">{{ __('See Details') }}</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="order-history-empty-row">
                                        <td colspan="5">
                                            {{ __('No orders found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($orders->total() > 0)
                        <div class="order-history-footer">
                            <span class="order-history-count">
                                {{ __('Showing :count of :total entries', ['count' => $orders->count(), 'total' => $orders->total()]) }}
                            </span>
                            <div class="order-history-pagination">
                                {{ $orders->onEachSide(1)->links('components.pagination') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

@include('partials.confirm-modal')

@endsection

@push('scripts')
    <script>
        // Delegated, because the rows are replaced when an order changes (live refresh).
        document.addEventListener('click', function (e) {
            const row = e.target.closest('.order-history-row[data-href]');
            if (row) {
                window.location = row.dataset.href;
            }
        });
    </script>
    <script>
        (function () {
            var poll = dwLiveRefresh('#order-history-live');
            window.addEventListener('dw:order-updated', poll);
        })();
    </script>
@endpush