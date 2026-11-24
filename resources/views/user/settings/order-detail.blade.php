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
        $formatRupiah = fn($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

        // Paid -> admin confirms -> being prepared -> on the way -> completed.
        $fmtTime = fn($at) => $at ? $at->format('H:i') . ' WIB' : null;
        $steps = [
            ['key' => 'paid', 'label' => __('Order Paid'), 'time' => $fmtTime($order['paid_at'])],
            ['key' => 'confirmed', 'label' => __('Order Confirmed'), 'time' => $fmtTime($order['confirmed_at'])],
            ['key' => 'process', 'label' => __('In Process'), 'time' => $fmtTime($order['confirmed_at'])],
            ['key' => 'shipped', 'label' => __('On the Way'), 'time' => $fmtTime($order['shipped_at'])],
            ['key' => 'completed', 'label' => __('Order Completed'), 'time' => $fmtTime($order['completed_at'])],
        ];
        // Index of the step the order is at now. "waiting" + paid means the admin has not confirmed yet.
        $currentIndex = match (true) {
            $order['status'] === 'waiting' && !$order['paid'] => 0,
            $order['status'] === 'waiting' => 1,
            $order['status'] === 'process' => 2,
            $order['status'] === 'shipped' => 3,
            default => count($steps) - 1,
        };
        $allDone = $order['status'] === 'done';
        $isCancelled = $order['status'] === 'rejected';
        $wasPaid = $order['paid'] || $order['refund_status'] !== null;

        $statusPillClass = match ($order['status']) {
            'waiting' => 'is-waiting',
            'process' => 'is-process',
            'shipped' => 'is-shipped',
            'done' => 'is-done',
            'rejected' => 'is-cancelled',
        };
    @endphp

    <div class="container-ww od-page" id="order-live" data-live-version="{{ md5(json_encode($order)) }}">

        <div class="od-topbar">
            <a href="{{ route('settings.orders') }}" class="od-back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                </svg>
                {{ __('Back to Order History') }}
            </a>

            <div class="od-topbar-actions">
                @unless ($isCancelled)
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
                @if ($order['can_cancel'])
                    <form method="POST" action="{{ route('settings.orders.cancel', $order['id']) }}"
                        data-confirm="{{ __('Are you sure you want to cancel this order?') }}"
                        data-confirm-title="{{ __('Cancel this order?') }}" data-confirm-ok="{{ __('Yes, cancel') }}">
                        @csrf
                        <button type="submit" class="od-invoice-btn od-cancel-btn">{{ __('Cancel Order') }}</button>
                    </form>
                @endif
                @if ($order['can_review'])
                    <a href="{{ route('settings.orders.review', $order['id']) }}" class="od-invoice-btn od-outline-btn">
                        {{ __('Beri Ulasan') }}
                    </a>
                @endif
                @if ($order['can_refund'])
                    <a href="{{ route('settings.orders.refund', $order['id']) }}" class="od-invoice-btn">
                        {{ __('Ajukan Refund') }}
                    </a>
                @endif
                @if ($order['can_complete'])
                    <form method="POST" action="{{ route('settings.orders.complete', $order['id']) }}"
                        data-confirm="{{ __('Have you received this order?') }}"
                        data-confirm-title="{{ __('Order received?') }}" data-confirm-ok="{{ __('Yes, received') }}">
                        @csrf
                        <button type="submit" class="od-invoice-btn od-outline-btn">{{ __('Pesanan Diterima') }}</button>
                    </form>
                @endif
            </div>
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

        @if ($errors->has('order'))
            <div class="od-card od-cancelled-notice">
                <div>
                    <strong>{{ $errors->first('order') }}</strong>
                </div>
            </div>
        @endif

        @if ($order['status'] === 'waiting' && !$order['paid'])
            <div class="od-card od-cancelled-notice">
                <div>
                    <strong>{{ __('Payment not received yet. If you have already paid, the status will update automatically in a moment.') }}</strong>
                </div>
            </div>
        @endif

        @if ($order['refund_needs_bank'])
            <div class="od-card od-refund-notice">
                <span class="od-refund-notice-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18M4 10h16M4 10 12 3l8 7M6 10v11M18 10v11M10 14v4M14 14v4"></path>
                    </svg>
                </span>
                <div class="od-refund-notice-text">
                    <strong>{{ __('Refund menunggu rekening bankmu') }}</strong>
                    <span>{{ __('Your payment will be refunded by bank transfer. Please enter your bank account.') }}</span>
                </div>
                <a href="{{ route('settings.orders.refund.bank', $order['id']) }}"
                    class="od-bank-btn">{{ __('Isi Rekening') }}</a>
            </div>
        @endif

        @unless ($isCancelled)
            <div class="od-card od-stepper-card">
                <div class="od-stepper {{ $allDone ? 'od-stepper--done' : '' }}">
                    @foreach ($steps as $index => $step)
                        @php
                            $state = ($index < $currentIndex || $allDone) ? 'done' : ($index === $currentIndex ? 'current' : 'upcoming');
                        @endphp
                        <div class="od-step od-step--{{ $state }}" style="--i: {{ $index }}">
                            <span class="od-step-dot">
                                @if ($state === 'done')
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                @elseif ($step['key'] === 'paid')
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                        <line x1="2" y1="10" x2="22" y2="10"></line>
                                    </svg>
                                @elseif ($step['key'] === 'confirmed')
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="8" y="2" width="8" height="4" rx="1"></rect>
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                        <path d="m9 14 2 2 4-4"></path>
                                    </svg>
                                @elseif ($step['key'] === 'process')
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z">
                                        </path>
                                    </svg>
                                @elseif ($step['key'] === 'shipped')
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z">
                                        </path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                @else
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1Z"></path>
                                        <line x1="4" y1="22" x2="4" y2="15"></line>
                                    </svg>
                                @endif
                            </span>
                            <span class="od-step-label">{{ $step['label'] }}</span>
                            <span class="od-step-time">
                                @if ($state !== 'upcoming' && $step['time'])
                                    {{ $step['time'] }}
                                @else
                                    &mdash;
                                @endif
                            </span>
                        </div>
                        @if (!$loop->last)
                            <div class="od-step-line {{ $index < $currentIndex || $allDone ? 'is-filled' : '' }} {{ $index === $currentIndex && !$allDone ? 'is-progress' : '' }}"
                                style="--i: {{ $index }}"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endunless

        <div class="od-card">
            <h2 class="od-card-title">
                {{ __('Ordered Items') }}
                <span
                    class="od-card-title-meta">{{ __(':count item(s), :qty pcs', ['count' => $order['items']->count(), 'qty' => $order['qty_total']]) }}</span>
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
                    @if ($order['refund_status'] === 'refunded')
                        <span class="od-paid-pill od-paid-pill--refunded">{{ __('Refunded') }}</span>
                    @elseif ($wasPaid)
                        <span class="od-paid-pill">{{ __('Paid') }}</span>
                    @else
                        <span class="od-paid-pill od-paid-pill--unpaid">{{ __('Unpaid') }}</span>
                    @endif
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
                <div class="od-method-box">
                    @if ($order['method_logo'])
                        <span class="od-method-logo">
                            <img src="{{ $order['method_logo'] }}" alt="{{ $order['method'] }}">
                        </span>
                    @else
                        {{-- No logo for this method (e.g. credit card, unpaid): fall back to the name --}}
                        <strong class="od-method-name">{{ $order['method'] !== '-' ? $order['method'] : __('Unpaid') }}</strong>
                    @endif
                </div>
                @if ($wasPaid)
                    @unless ($isCancelled)
                        <p class="od-method-note">
                            {{ __('Payment was automatically verified by the Donat Wawa payment gateway.') }}
                        </p>
                    @endunless
                    @if ($order['paid_at'])
                        <div class="od-payment-time">
                            <span>{{ __('Payment Time') }}:</span>
                            <strong>{{ $order['paid_at']->format('d M Y, H:i') }} WIB</strong>
                        </div>
                    @endif
                @else
                    <p class="od-method-note">{{ __('No payment received yet.') }}</p>
                @endif
            </div>
        </div>

        @if ($order['refund'])
            @php
                $refund = $order['refund'];
                // "pembeli" = the customer asked for it. Anything else was started by the system
                // (the order was cancelled), so the customer did not "request" anything.
                $byCustomer = $refund['source'] === 'pembeli';
                $refundPill = match (true) {
                    $refund['status'] === 'refunded' => __('Refunded'),
                    $refund['status'] === 'rejected' => __('Refund rejected'),
                    $byCustomer => __('Awaiting review'),
                    $order['refund_needs_bank'] => __('Waiting for bank account'),
                    default => __('Refund in progress'),
                };
                $refundMessage = match (true) {
                    $refund['status'] === 'refunded' => __('Your payment has been refunded.'),
                    $refund['status'] === 'rejected' => __('Your refund request was rejected.'),
                    $byCustomer => __('Your refund request is waiting for review.'),
                    $order['refund_needs_bank'] => __('Your order was cancelled, so your payment will be refunded. Add your bank account so we can send it.'),
                    default => __('Your refund is being processed.'),
                };
            @endphp
            <div class="od-card od-refund-status od-refund-status--{{ $refund['status'] }}">
                <div class="od-refund-status-head">
                    <h2 class="od-card-title">{{ __('Refund') }}</h2>
                    <span class="od-refund-status-pill">{{ $refundPill }}</span>
                </div>
                <p>{{ $refundMessage }}</p>
                <div class="od-summary-row">
                    <span>{{ __('Refund Amount') }}</span>
                    <strong>{{ $formatRupiah($refund['amount']) }}</strong>
                </div>
                @if ($refund['requested_at'])
                    <div class="od-summary-row">
                        <span>{{ $byCustomer ? __('Requested on') : __('Started on') }}</span>
                        <span>{{ $refund['requested_at']->format('d M Y, H:i') }} WIB</span>
                    </div>
                @endif
                @if ($refund['processed_at'])
                    <div class="od-summary-row">
                        <span>{{ __('Processed on') }}</span>
                        <span>{{ $refund['processed_at']->format('d M Y, H:i') }} WIB</span>
                    </div>
                @endif
                @if ($refund['notes'] && $refund['status'] === 'rejected')
                    <p class="od-refund-status-note">{{ __('Note') }}: {{ $refund['notes'] }}</p>
                @endif
            </div>
        @endif

        <div class="od-grid-2">
            <div class="od-card">
                <div class="od-card-title-row">
                    <h2 class="od-card-title">{{ __('Customer Info') }}</h2>
                    <svg class="od-card-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    <svg class="od-card-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

        @if (!$order['refund'] && $order['can_refund'])
            <div class="od-card od-refund-notice od-refund-prompt">
                <div class="od-refund-notice-text">
                    <strong>{{ __('Not happy with this order?') }}</strong>
                    <span>{{ __('You can request a refund until :deadline WIB.', ['deadline' => $order['refund_deadline']]) }}</span>
                </div>
                <a href="{{ route('settings.orders.refund', $order['id']) }}" class="od-bank-btn">{{ __('Ajukan Refund') }}</a>
            </div>
        @endif

    </div>

    @include('partials.confirm-modal')

@endsection

@push('scripts')
    <script>
        (function () {
            const selector = '#order-live';
            const interval = 5000;
            let timer = null;
            let busy = false;

            function modalOpen() {
                const modal = document.getElementById('dw-confirm');
                return !!modal && !modal.hidden;
            }

            async function poll() {
                if (busy) { return; }
                busy = true;

                try {
                    const current = document.querySelector(selector);
                    if (!current) { return; }

                    const res = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });
                    if (!res.ok) { return; }

                    const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                    const fresh = doc.querySelector(selector);
                    if (!fresh || modalOpen()) { return; }
                    if (fresh.dataset.liveVersion === current.dataset.liveVersion) { return; }

                    current.innerHTML = fresh.innerHTML;
                    current.dataset.liveVersion = fresh.dataset.liveVersion;
                    window.dispatchEvent(new CustomEvent('live:updated', { detail: { selector: selector } }));
                } catch (e) {
                } finally {
                    busy = false;
                }
            }

            function schedule() {
                clearTimeout(timer);
                timer = setTimeout(async function () {
                    if (!document.hidden) { await poll(); }
                    schedule();
                }, interval);
            }

            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) { poll(); }
            });

            schedule();
        })();
    </script>
@endpush