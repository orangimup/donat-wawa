@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Request a Refund'))
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/order-refund.css')
    <style>
        body {
            background: #fff;
        }
    </style>
@endpush

@section('content')

    <div class="container-ww order-refund-layout">

        <div class="order-refund-head">
            <h1>{{ __('Request a Refund') }}</h1>
            <p>{{ __('We apologize for the inconvenience. Please fill out the form below to process the refund.') }}</p>
        </div>

        <form method="POST" action="{{ route('settings.orders.refund.store', $order['id']) }}" enctype="multipart/form-data"
            class="order-refund-grid">
            @csrf

            <div class="order-refund-col">

                <div class="order-refund-card accent-1">
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 4h16v16H4z"></path>
                            <path d="M8 8h8M8 12h8M8 16h5"></path>
                        </svg>
                        {{ __('Step 1: Select Canceled Order') }}
                    </h2>

                    @php
                        $refundFirst = $order['items']->first();
                        $refundExtra = $order['items']->count() - 1;
                    @endphp

                    <div class="order-refund-order-row">
                        <img src="{{ $refundFirst['image'] }}" alt="{{ $refundFirst['name'] }}"
                            class="order-refund-order-image">
                        <div class="order-refund-order-info">
                            <span>{{ $order['code'] }}</span>
                            <strong>{{ $refundFirst['name'] }}</strong>
                            @if ($refundExtra > 0)
                                <span>{{ __('+:count more items', ['count' => $refundExtra]) }}</span>
                            @endif
                        </div>
                        <div class="order-refund-order-right">
                            <strong>{{ 'Rp ' . number_format($order['total'], 0, ',', '.') }}</strong>
                            <span class="oh-status-pill is-{{ $order['status'] === 'done' ? 'done' : 'cancelled' }}">
                                {{ $statusLabels[$order['status']] }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="order-refund-card accent-2">
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.1 9a3 3 0 1 1 3.6 3c-.7.6-1.2 1.1-1.2 2"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        {{ __('Step 2: Reason for Refund') }}
                    </h2>

                    <div class="order-refund-dropdown" id="reason-dropdown">
                        <button type="button" class="order-refund-dropdown-trigger" id="reason-trigger">
                            <span id="reason-trigger-label">{{ __('Select a reason...') }}</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                class="order-refund-dropdown-chevron">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>

                        <div class="order-refund-dropdown-panel" id="reason-panel" hidden>
                            @foreach ($reasons as $key => $label)
                                <button type="button" class="order-refund-dropdown-option" data-value="{{ $key }}"
                                    data-label="{{ $label }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        <select name="reason" id="reason-select" class="order-refund-dropdown-native" required>
                            <option value="" disabled selected>{{ __('Select a reason...') }}</option>
                            @foreach ($reasons as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="order-refund-card accent-3">
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.1 9a3 3 0 1 1 3.6 3c-.7.6-1.2 1.1-1.2 2"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        {{ __('Step 3: What went wrong') }}
                    </h2>

                    <textarea name="details" rows="5" class="order-refund-textarea"
                        placeholder="{{ __('Please describe the issue with your donut...') }}" required></textarea>
                </div>

            </div>

            <div class="order-refund-col">

                <div class="order-refund-card accent-4">
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 21h18M4 10h16M4 10 12 3l8 7M6 10v11M18 10v11M10 14v4M14 14v4"></path>
                        </svg>
                        {{ __('Step 4: Upload Evidence') }}
                    </h2>

                    <label class="order-refund-dropzone" id="refund-dropzone">
                        <input type="file" name="evidence" id="refund-evidence" accept="image/png,image/jpeg" hidden>
                        <span class="order-refund-dropzone-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="M21 15l-5-5L5 21"></path>
                            </svg>
                        </span>
                        <strong id="refund-dropzone-text">{{ __('Click to upload or drag and drop') }}</strong>
                        <span>{{ __('PNG, JPG or JPEG (max. 5MB)') }}</span>
                    </label>
                </div>

                <div class="order-refund-card accent-5">
                    <h2>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 21h18M4 10h16M4 10 12 3l8 7M6 10v11M18 10v11M10 14v4M14 14v4"></path>
                        </svg>
                        {{ __('Step 5: Bank Details') }}
                    </h2>

                    <div class="order-refund-fields">
                        <input type="text" name="bank_name" class="order-refund-input"
                            placeholder="{{ __('Bank Name (e.g. BCA, Mandiri)') }}" required>
                        <input type="text" name="account_number" class="order-refund-input"
                            placeholder="{{ __('Account Number') }}" required>
                        <input type="text" name="account_holder_name" class="order-refund-input"
                            placeholder="{{ __('Account Holder Name') }}" required>
                    </div>
                </div>

            </div>

            <div class="order-refund-actions">
                <a href="{{ route('settings.orders.show', $order['id']) }}"
                    class="order-refund-cancel">{{ __('Cancel') }}</a>
                <button type="submit" class="order-refund-submit">{{ __('Submit Request') }}</button>
            </div>
        </form>

    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('refund-evidence');
            const text = document.getElementById('refund-dropzone-text');
            const defaultText = text.textContent;

            input.addEventListener('change', function () {
                text.textContent = input.files.length ? input.files[0].name : defaultText;
            });
        })();

        (function () {
            const wrapper = document.getElementById('reason-dropdown');
            const trigger = document.getElementById('reason-trigger');
            const label = document.getElementById('reason-trigger-label');
            const panel = document.getElementById('reason-panel');
            const select = document.getElementById('reason-select');
            const options = panel.querySelectorAll('.order-refund-dropdown-option');

            function close() {
                panel.hidden = true;
                trigger.classList.remove('is-open');
            }

            function open() {
                panel.hidden = false;
                trigger.classList.add('is-open');
            }

            trigger.addEventListener('click', function () {
                panel.hidden ? open() : close();
            });

            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    select.value = option.dataset.value;
                    label.textContent = option.dataset.label;
                    options.forEach((o) => o.classList.toggle('is-selected', o === option));
                    close();
                });
            });

            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) {
                    close();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    close();
                }
            });
        })();
    </script>
@endpush