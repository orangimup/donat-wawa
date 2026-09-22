@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Order Detail'))
@section('body-class', 'od-body')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/order-history.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

        $steps = [
            'waiting' => ['key' => 'received', 'label' => __('Order Received')],
            'process' => ['key' => 'process', 'label' => __('In Process')],
            'shipped' => ['key' => 'shipped', 'label' => __('On the Way')],
            'done' => ['key' => 'completed', 'label' => __('Order Completed')],
        ];
        $stepOrder = array_keys($steps);
        $currentIndex = array_search($order['status'], $stepOrder, true);
        $isCancelled = $order['status'] === 'rejected';

        $statusPillClass = match ($order['status']) {
            'waiting' => 'is-waiting',
            'process' => 'is-process',
            'shipped' => 'is-shipped',
            'done' => 'is-done',
            'rejected' => 'is-cancelled',
        };
    @endphp

    <div class="container-ww od-page">

        <div class="od-topbar">
            <a href="{{ route('settings.orders') }}" class="od-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                </svg>
                {{ __('Back to Order History') }}
            </a>

            @unless ($isCancelled)
                {{-- NOTE: invoice generation isn't wired up yet (no order backend/PDF export). --}}
                {{-- This is UI-only for now; hook it up to a real route once invoices are implemented. --}}
                <a href="#" class="od-invoice-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    {{ __('Download Invoice') }}
                </a>
            @endunless
        </div>

        <div class="od-card od-summary-card">
            <div>
                <div class="od-order-code">
                    {{ $order['code'] }}
                    <span class="oh-status-pill {{ $statusPillClass }}">{{ __($statusLabels[$order['status']]) }}</span>
                </div>
                <div class="od-order-meta">
                    {{ __('Placed on') }}: <strong>{{ $order['placed_at']->format('d M Y, H:i') }} WIB</strong>
                    &bull; {{ __('Method') }}: <strong>{{ $order['method'] }}</strong>
                </div>
            </div>
            <div class="od-total-block">
                <span>{{ __('Total Payment') }}</span>
                <strong>{{ $formatRupiah($order['total']) }}</strong>
            </div>
        </div>

        @if ($isCancelled)
            <div class="od-card od-cancelled-notice">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M15 9l-6 6M9 9l6 6"></path>
                </svg>
                <div>
                    <strong>{{ __('This order was cancelled') }}</strong>
                    <span>{{ __($order['rejection_reason']) }}</span>
                </div>
            </div>
        @else
            <div class="od-card od-stepper-card">
                <div class="od-stepper">
                    @foreach ($steps as $status => $step)
                        @php
                            $index = array_search($status, $stepOrder, true);
                            $state = $index < $currentIndex ? 'done' : ($index === $currentIndex ? 'current' : 'upcoming');
                        @endphp
                        <div class="od-step od-step--{{ $state }}">
                            <span class="od-step-dot">
                                @if ($state === 'done' || $step['key'] === 'received')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                @elseif ($step['key'] === 'process')
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z">
                                        </path>
                                    </svg>
                                @elseif ($step['key'] === 'shipped')
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z">
                                        </path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                @elseif ($step['key'] === 'completed')
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1Z"></path>
                                        <line x1="4" y1="22" x2="4" y2="15"></line>
                                    </svg>
                                @endif
                            </span>
                            <span class="od-step-label">{{ $step['label'] }}</span>
                            <span class="od-step-time">
                                @if ($state === 'done' || $state === 'current')
                                    {{ $order['placed_at']->format('H:i') }} WIB
                                @else
                                    &mdash;
                                @endif
                            </span>
                        </div>
                        @if (!$loop->last)
                            <div class="od-step-line {{ $index < $currentIndex ? 'is-filled' : '' }}"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <div class="od-card">
            <h2 class="od-card-title">
                {{ __('Ordered Items') }}
                <span class="od-card-title-meta">{{ __(':count item(s), :qty pcs', ['count' => $order['items']->count(), 'qty' => $order['qty_total']]) }}</span>
            </h2>
            <div class="od-items">
                @foreach ($order['items'] as $item)
                    <div class="od-item">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                        <div class="od-item-info">
                            <strong>{{ $item['name'] }}</strong>
                            <span>{{ __('Qty') }}: {{ $item['qty'] }}</span>
                        </div>
                        <span class="od-item-price">{{ $formatRupiah($item['price'] * $item['qty']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="od-grid-2">
            <div class="od-card">
                <div class="od-card-title-row">
                    <h2 class="od-card-title">{{ __('Payment Summary') }}</h2>
                    <span class="od-paid-pill">{{ __('Paid') }}</span>
                </div>
                <div class="od-summary-row">
                    <span>{{ __('Products Subtotal') }}</span>
                    <span>{{ $formatRupiah($order['subtotal']) }}</span>
                </div>
                <div class="od-summary-row">
                    <span>{{ __('Shipping Fee') }}</span>
                    <span>{{ $formatRupiah($order['shipping_fee']) }}</span>
                </div>
                <div class="od-summary-row od-summary-total">
                    <span>{{ __('Total') }}</span>
                    <strong>{{ $formatRupiah($order['total']) }}</strong>
                </div>
            </div>

            <div class="od-card">
                <h2 class="od-card-title">{{ __('Payment Method') }}</h2>
                <div class="od-method-card">
                    <div>
                        <strong>{{ $order['method'] }}</strong>
                        @if ($order['txn'])
                            <span>{{ __('Transaction ID') }}: {{ $order['txn'] }}</span>
                        @endif
                    </div>
                    @unless ($isCancelled)
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2ea44f" stroke-width="2.4"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    @endunless
                </div>
                @unless ($isCancelled)
                    <p class="od-method-note">
                        {{ __('Payment was automatically verified by the Donat Wawa payment gateway.') }}
                    </p>
                    <div class="od-payment-time">
                        <span>{{ __('Payment Time') }}:</span>
                        <strong>{{ $order['placed_at']->format('d M Y, H:i') }}</strong>
                    </div>
                @endunless
            </div>
        </div>

        <div class="od-grid-2">
            <div class="od-card">
                <div class="od-card-title-row">
                    <h2 class="od-card-title">{{ __('Customer Info') }}</h2>
                    <svg class="od-card-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <strong class="od-block-name">{{ auth()->user()->name }}</strong>
                <p class="od-block-line">{{ auth()->user()->email }}</p>
                @if (auth()->user()->phone)
                    <p class="od-block-line">{{ auth()->user()->phone }}</p>
                @endif
            </div>

            <div class="od-card">
                <div class="od-card-title-row">
                    <h2 class="od-card-title">{{ __('Shipping Address') }}</h2>
                    <svg class="od-card-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <strong class="od-block-name">{{ __($order['address_label']) }}</strong>
                <p class="od-block-line">{{ $order['address_lines'] }}</p>
                @if ($order['address_note'])
                    <p class="od-block-note">{{ __('Note') }}: "{{ __($order['address_note']) }}"</p>
                @endif
            </div>
        </div>

        <div class="od-help-bar">
            <div>
                <strong>{{ __('Need help with this order?') }}</strong>
                <span>{{ __('Our Customer Service team is available daily, 08:00 – 21:00 WIB.') }}</span>
            </div>
            <a href="{{ url('/about') }}" class="btn btn-primary btn-sm">{{ __('Contact Customer Service') }}</a>
        </div>

    </div>

@endsection