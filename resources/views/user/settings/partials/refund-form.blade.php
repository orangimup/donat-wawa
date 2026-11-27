@php
    $refund = $refund ?? null;
    $hasRefund = $refund !== null;
    $bankOnly = $hasRefund;
    $selectedReason = old('reason', $refund?->alasan);
    $selectedReason = array_key_exists((string) $selectedReason, $reasons) ? $selectedReason : null;
    $isCancelled = $order['status'] === 'rejected';
    $banks = ['BCA', 'Mandiri', 'BNI', 'BRI'];
    $currentBank = trim((string) old('bank_name', $refund?->nama_bank));
    $bankIsOther = $currentBank !== '' && !in_array($currentBank, $banks, true);
    $bankLabel = $bankIsOther ? __('Other') : ($currentBank !== '' ? $currentBank : null);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="order-refund-grid">
    @csrf

    <div class="order-refund-col">

        <div class="order-refund-card accent-1">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 4h16v16H4z"></path>
                    <path d="M8 8h8M8 12h8M8 16h5"></path>
                </svg>
                {{ $isCancelled ? __('Step 1: Select Canceled Order') : __('Step 1: Select Order') }}
            </h2>

            @php
                $items = collect($order['items'])->values();
                $showNumbers = $items->count() > 1;
                $refundTotal = $hasRefund ? $refund->jumlah_refund : $order['total'];
            @endphp

            <ul class="order-refund-items">
                @foreach ($items as $i => $item)
                    <li class="order-refund-item">
                        @if ($showNumbers)
                            <span class="order-refund-item-no" aria-hidden="true">{{ $i + 1 }}</span>
                        @endif
                        <img src="{{ $item['image'] }}" alt="" class="order-refund-item-img" loading="lazy">
                        <div class="order-refund-item-info">
                            <strong>{{ $item['name'] }}</strong>
                            <span>{{ __('Qty') }}: {{ $item['qty'] }}</span>
                        </div>
                        <span class="order-refund-item-price">{{ 'Rp ' . number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="order-refund-total">
                <span class="order-refund-pill {{ $order['status'] === 'done' ? 'is-done' : '' }}">
                    {{ __($statusLabels[$order['status']]) }}
                </span>
                <span class="order-refund-total-label">{{ __('Refund Amount') }}</span>
                <strong>{{ 'Rp ' . number_format($refundTotal, 0, ',', '.') }}</strong>
            </div>
        </div>

        @unless ($bankOnly)
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

                <div class="admin-select admin-select-form" id="reason-dropdown">
                    <input type="hidden" name="reason" id="reason-value" value="{{ $selectedReason }}">

                    <button type="button" class="admin-select-trigger {{ $selectedReason ? '' : 'is-placeholder' }}"
                        id="reason-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span id="reason-trigger-label">{{ $selectedReason ? $reasons[$selectedReason] : __('Select a reason...') }}</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <ul class="admin-select-menu" id="reason-panel" role="listbox">
                        @foreach ($reasons as $key => $label)
                            <li role="option" data-value="{{ $key }}"
                                class="admin-select-option {{ $selectedReason === $key ? 'is-selected' : '' }}">{{ $label }}</li>
                        @endforeach
                    </ul>
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

                <textarea name="details" rows="5" class="order-refund-textarea" maxlength="1000" @required(!$hasRefund)
                    placeholder="{{ __('Please describe the issue with your donut...') }}">{{ old('details', $refund?->detail_alasan) }}</textarea>
            </div>
        @endunless

    </div>

    <div class="order-refund-col">

        @unless ($bankOnly)
            <div class="order-refund-card accent-4">
                <h2>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                        <circle cx="9" cy="9" r="2"></circle>
                        <path d="M21 15l-5-5L5 21"></path>
                    </svg>
                    {{ __('Step 4: Upload Evidence') }}
                </h2>

                <label class="order-refund-dropzone" id="refund-dropzone">
                    <input type="file" name="evidence[]" id="refund-evidence" accept="image/png,image/jpeg" multiple hidden>
                    <span class="order-refund-dropzone-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </span>
                    <strong id="refund-dropzone-text">{{ __('Click to upload or drag and drop') }}</strong>
                    <span>{{ __('PNG, JPG or JPEG (max. 5MB each, up to 3 photos)') }}</span>
                </label>

                <p class="order-refund-evidence-error" id="refund-evidence-error" role="alert" hidden></p>
                <div class="order-refund-previews" id="refund-previews"></div>
            </div>
        @endunless

        @if ($needsBank)
            <div class="order-refund-card accent-5">
                <h2>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M10 18v-7"></path>
                        <path
                            d="M11.12 2.198a2 2 0 0 1 1.76.006l7.866 3.847c.476.233.31.949-.22.949H3.474c-.53 0-.695-.716-.22-.949z">
                        </path>
                        <path d="M14 18v-7"></path>
                        <path d="M18 18v-7"></path>
                        <path d="M3 22h18"></path>
                        <path d="M6 18v-7"></path>
                    </svg>
                    {{ $bankOnly ? __('Step 2: Bank Details') : __('Step 5: Bank Details') }}
                </h2>

                <p class="order-refund-bank-note">
                    {{ __('You paid by bank transfer, so we send the refund to the bank account below by hand. Please double-check the number.') }}
                </p>

                <div class="order-refund-fields">
                    <div>
                        <span class="order-refund-label" id="bank-label">{{ __('Bank Name') }}</span>

                        <div class="admin-select admin-select-form" id="bank-dropdown">
                            <input type="hidden" name="bank_name" id="bank-value" value="{{ $currentBank }}">

                            <button type="button" class="admin-select-trigger {{ $bankLabel ? '' : 'is-placeholder' }}"
                                id="bank-trigger" aria-haspopup="listbox" aria-expanded="false"
                                aria-labelledby="bank-label">
                                <span id="bank-trigger-label">{{ $bankLabel ?? __('Select a bank...') }}</span>
                                <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true">
                                    <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
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

                        <input type="text" id="bank-other" class="order-refund-input order-refund-bank-other"
                            maxlength="100" autocomplete="off" placeholder="{{ __('Type your bank name') }}"
                            value="{{ $bankIsOther ? $currentBank : '' }}" @if (!$bankIsOther) hidden @endif>
                    </div>

                    <label>
                        <span class="order-refund-label">{{ __('Account Number') }}</span>
                        <input type="text" name="account_number" class="order-refund-input" inputmode="numeric"
                            pattern="[0-9]{5,20}" maxlength="20" required autocomplete="off" placeholder="1234567890"
                            value="{{ old('account_number', $refund?->nomor_rekening) }}">
                    </label>

                    <label>
                        <span class="order-refund-label">{{ __('Account Holder Name') }}</span>
                        <input type="text" name="account_holder_name" class="order-refund-input" maxlength="100"
                            required autocomplete="off" placeholder="{{ __('Name as printed on the account') }}"
                            value="{{ old('account_holder_name', $refund?->nama_pemilik_rekening) }}">
                    </label>
                </div>
            </div>
        @endif

    </div>

    <div class="order-refund-actions">
        <a href="{{ $cancelUrl }}" class="order-refund-cancel">{{ __('Cancel') }}</a>
        <button type="submit" class="order-refund-submit">
            {{ $hasRefund ? __('Save Bank Details') : __('Submit Request') }}
        </button>
    </div>
</form>

@unless ($bankOnly)
    @push('scripts')
        <script>
            (function () {
                const MAX = 3;
                const MAX_SIZE = 5 * 1024 * 1024;
                const input = document.getElementById('refund-evidence');
                const text = document.getElementById('refund-dropzone-text');
                const dropzone = document.getElementById('refund-dropzone');
                const list = document.getElementById('refund-previews');
                const error = document.getElementById('refund-evidence-error');
                const defaultText = text.textContent;
                const messages = {
                    limit: @json(__('You can upload up to 3 photos.')),
                    size: @json(__('Each photo must be 5MB or smaller.')),
                    type: @json(__('Only PNG, JPG or JPEG photos are allowed.')),
                    remove: @json(__('Remove photo')),
                    count: @json(__(':count of 3 photos')),
                };
                let files = [];

                function sync() {
                    const transfer = new DataTransfer();
                    files.forEach(function (file) { transfer.items.add(file); });
                    input.files = transfer.files;
                }

                function showError(message) {
                    error.textContent = message;
                    error.hidden = !message;
                }

                function render() {
                    list.querySelectorAll('img').forEach(function (img) { URL.revokeObjectURL(img.src); });
                    list.innerHTML = '';

                    files.forEach(function (file, index) {
                        const item = document.createElement('div');
                        item.className = 'order-refund-preview';

                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.alt = file.name;

                        const name = document.createElement('span');
                        name.textContent = file.name;

                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'order-refund-preview-remove';
                        remove.setAttribute('aria-label', messages.remove);
                        remove.textContent = '×';
                        remove.addEventListener('click', function () {
                            files.splice(index, 1);
                            sync();
                            render();
                            showError('');
                        });

                        item.append(img, name, remove);
                        list.appendChild(item);
                    });

                    text.textContent = files.length ? messages.count.replace(':count', files.length) : defaultText;
                    dropzone.classList.toggle('is-full', files.length >= MAX);
                }

                function add(incoming) {
                    showError('');

                    Array.from(incoming).forEach(function (file) {
                        const okType = /^image\/(png|jpeg)$/.test(file.type) || /\.(png|jpe?g|jfif)$/i.test(file.name);

                        if (files.length >= MAX) { showError(messages.limit); return; }
                        if (!okType) { showError(messages.type); return; }
                        if (file.size > MAX_SIZE) { showError(messages.size); return; }

                        files.push(file);
                    });

                    sync();
                    render();
                }

                input.addEventListener('change', function () { add(input.files); });

                dropzone.addEventListener('click', function (e) {
                    if (files.length >= MAX) {
                        e.preventDefault();
                        showError(messages.limit);
                    }
                });

                ['dragover', 'dragenter'].forEach(function (name) {
                    dropzone.addEventListener(name, function (e) { e.preventDefault(); dropzone.classList.add('is-drag'); });
                });
                ['dragleave', 'drop'].forEach(function (name) {
                    dropzone.addEventListener(name, function () { dropzone.classList.remove('is-drag'); });
                });
                dropzone.addEventListener('drop', function (e) {
                    e.preventDefault();
                    if (e.dataTransfer && e.dataTransfer.files.length) {
                        add(e.dataTransfer.files);
                    }
                });
            })();

            (function () {
                const wrapper = document.getElementById('reason-dropdown');
                const trigger = document.getElementById('reason-trigger');
                const label = document.getElementById('reason-trigger-label');
                const menu = document.getElementById('reason-panel');
                const value = document.getElementById('reason-value');
                const form = wrapper.closest('form');
                const options = menu.querySelectorAll('.admin-select-option');

                function setOpen(open) {
                    wrapper.classList.toggle('is-open', open);
                    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                }

                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    setOpen(!wrapper.classList.contains('is-open'));
                });

                options.forEach(function (option) {
                    option.addEventListener('click', function () {
                        value.value = option.dataset.value;
                        label.textContent = option.textContent;
                        trigger.classList.remove('is-placeholder', 'is-invalid');
                        options.forEach(function (o) { o.classList.toggle('is-selected', o === option); });
                        setOpen(false);
                    });
                });

                document.addEventListener('click', function (e) {
                    if (!wrapper.contains(e.target)) { setOpen(false); }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { setOpen(false); }
                });

                form.addEventListener('submit', function (e) {
                    if (!value.value) {
                        e.preventDefault();
                        trigger.classList.add('is-invalid');
                        trigger.focus();
                    }
                });
            })();
        </script>
    @endpush
@endunless

@if ($needsBank)
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
                    setOpen(!wrapper.classList.contains('is-open'));
                });

                options.forEach(function (option) {
                    option.addEventListener('click', function () {
                        const isOther = option.dataset.value === '__other';

                        label.textContent = option.textContent;
                        trigger.classList.remove('is-placeholder', 'is-invalid');
                        options.forEach(function (o) { o.classList.toggle('is-selected', o === option); });
                        other.hidden = !isOther;
                        value.value = isOther ? other.value.trim() : option.dataset.value;
                        setOpen(false);

                        if (isOther) { other.focus(); }
                    });
                });

                other.addEventListener('input', function () { value.value = other.value.trim(); });

                document.addEventListener('click', function (e) {
                    if (!wrapper.contains(e.target)) { setOpen(false); }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { setOpen(false); }
                });

                form.addEventListener('submit', function (e) {
                    if (!value.value) {
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
@endif