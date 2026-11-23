@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Refund Bank Details'))
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

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

        $items = collect($order['items'])->values();
        $firstItem = $items->first();
        $extraItems = $items->count() - 1;

        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI'];
        $currentBank = trim((string) old('bank_name', $refund->nama_bank));
        $bankIsOther = $currentBank !== '' && ! in_array($currentBank, $banks, true);
        $bankLabel = $bankIsOther ? __('Other') : ($currentBank !== '' ? $currentBank : null);
    @endphp

    <div class="rb-page">
        <header class="rb-head">
            <h1>{{ __('Refund Bank Details') }}</h1>
            <p>{{ __('We send your refund by bank transfer. Tell us which account should receive it.') }}</p>
        </header>

        <section class="rb-summary">
            <div class="rb-summary-amount">
                <span>{{ __('Refund Amount') }}</span>
                <strong>{{ $formatRupiah($refund->jumlah_refund) }}</strong>
            </div>

            <div class="rb-summary-order">
                @if ($firstItem)
                    <img src="{{ $firstItem['image'] }}" alt="" class="rb-summary-img">
                @endif
                <div class="rb-summary-info">
                    <strong>
                        {{ $firstItem['name'] ?? $order['code'] }}
                        @if ($extraItems > 0)
                            <span>{{ __('+:count more items', ['count' => $extraItems]) }}</span>
                        @endif
                    </strong>
                    <span>{{ $order['code'] }}</span>
                </div>
                <span class="rb-summary-status">{{ __($statusLabels[$order['status']]) }}</span>
            </div>
        </section>

        <form method="POST" action="{{ route('settings.orders.refund.bank.store', $order['id']) }}" class="rb-card">
            @csrf

            <h2 class="rb-card-title">{{ __('Where should we send it?') }}</h2>

            @if ($errors->any())
                <p class="rb-alert" role="alert">{{ $errors->first() }}</p>
            @endif

            <div class="rb-field">
                <span class="order-refund-label" id="bank-label">{{ __('Bank Name') }}</span>

                <div class="admin-select admin-select-form" id="bank-dropdown">
                    <input type="hidden" name="bank_name" id="bank-value" value="{{ $currentBank }}">

                    <button type="button" class="admin-select-trigger {{ $bankLabel ? '' : 'is-placeholder' }}"
                        id="bank-trigger" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="bank-label">
                        <span id="bank-trigger-label">{{ $bankLabel ?? __('Select a bank...') }}</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <ul class="admin-select-menu admin-select-menu--scroll" id="bank-menu" role="listbox">
                        @foreach ($banks as $bank)
                            <li role="option" data-value="{{ $bank }}"
                                class="admin-select-option {{ $currentBank === $bank ? 'is-selected' : '' }}">{{ $bank }}</li>
                        @endforeach
                        <li role="option" data-value="__other"
                            class="admin-select-option {{ $bankIsOther ? 'is-selected' : '' }}">{{ __('Other') }}</li>
                    </ul>
                </div>

                <input type="text" id="bank-other" class="order-refund-input order-refund-bank-other" maxlength="100"
                    autocomplete="off" placeholder="{{ __('Type your bank name') }}"
                    value="{{ $bankIsOther ? $currentBank : '' }}" @if (! $bankIsOther) hidden @endif>
            </div>

            <label class="rb-field">
                <span class="order-refund-label">{{ __('Account Number') }}</span>
                <input type="text" name="account_number" class="order-refund-input" inputmode="numeric"
                    pattern="[0-9]{5,20}" maxlength="20" required autocomplete="off" placeholder="1234567890"
                    value="{{ old('account_number', $refund->nomor_rekening) }}">
            </label>

            <label class="rb-field">
                <span class="order-refund-label">{{ __('Account Holder Name') }}</span>
                <input type="text" name="account_holder_name" class="order-refund-input" maxlength="100" required
                    autocomplete="off" placeholder="{{ __('Name as printed on the account') }}"
                    value="{{ old('account_holder_name', $refund->nama_pemilik_rekening) }}">
            </label>

            <p class="rb-note">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4M12 8h.01"></path>
                </svg>
                <span>{{ __('Please double-check the details. We send the refund by hand, so a wrong number will delay it.') }}</span>
            </p>

            <button type="submit" class="rb-submit">{{ __('Save Bank Details') }}</button>
            <a href="{{ route('settings.orders.show', $order['id']) }}" class="rb-cancel">{{ __('Cancel') }}</a>
        </form>

    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const wrapper = document.getElementById('bank-dropdown');
            const trigger = document.getElementById('bank-trigger');
            const label = document.getElementById('bank-trigger-label');
            const menu = document.getElementById('bank-menu');
            const value = document.getElementById('bank-value');
            const other = document.getElementById('bank-other');
            const form = wrapper.closest('form');
            const options = menu.querySelectorAll('.admin-select-option');

            function setOpen(open) {
                wrapper.classList.toggle('is-open', open);
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                setOpen(! wrapper.classList.contains('is-open'));
            });

            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    const isOther = option.dataset.value === '__other';

                    label.textContent = option.textContent;
                    trigger.classList.remove('is-placeholder', 'is-invalid');
                    options.forEach(function (o) { o.classList.toggle('is-selected', o === option); });
                    other.hidden = ! isOther;
                    value.value = isOther ? other.value.trim() : option.dataset.value;
                    setOpen(false);

                    if (isOther) { other.focus(); }
                });
            });

            other.addEventListener('input', function () { value.value = other.value.trim(); });

            document.addEventListener('click', function (e) {
                if (! wrapper.contains(e.target)) { setOpen(false); }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { setOpen(false); }
            });

            form.addEventListener('submit', function (e) {
                if (! value.value) {
                    e.preventDefault();
                    if (other.hidden) {
                        trigger.classList.add('is-invalid');
                        trigger.focus();
                    } else {
                        other.focus();
                    }
                }
            });
        })();
    </script>
@endpush