@extends('layouts.admin')

@section('title', 'Process Refund — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/refund.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

        $decision = old('decision', 'approve');
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>Process Refund</h1>
            <p>Request ID: {{ $refund['code'] }} &bull; {{ $refund['source'] }}</p>
        </div>
        <span class="refund-status-pill is-large refund-status-{{ $refund['status'] }}">
            {{ $statusLabels[$refund['status']] }}
        </span>
    </div>

    <div class="refund-detail-grid">

        <div class="refund-detail-main">

            <div class="refund-card">
                <h2 class="refund-card-title"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 2v20l3-2 2.5 2L12 20l2.5 2L17 20l3 2V2l-3 2-2.5-2L12 4 9.5 2 7 4 4 2Z"></path><path d="M8 9h8M8 13h8"></path></svg> Transaction Details</h2>

                <div class="refund-facts">
                    <div>
                        <span class="refund-fact-label">Order ID</span>
                        <div class="refund-fact-value">{{ $refund['order_code'] }}</div>
                    </div>
                    <div>
                        <span class="refund-fact-label">Order Date</span>
                        <div class="refund-fact-value">{{ $refund['order_label'] }}</div>
                    </div>
                    <div>
                        <span class="refund-fact-label">Refund Amount</span>
                        <div class="refund-fact-value is-amount">{{ $formatRupiah($refund['amount']) }}</div>
                    </div>
                    <div>
                        <span class="refund-fact-label">Reason</span>
                        <div><span class="refund-reason-pill">{{ $refund['reason'] }}</span></div>
                    </div>
                </div>

                @if ($refund['description'])
                    <span class="refund-fact-label">{{ $isCustomerRequest ? 'Customer Description' : 'Details' }}</span>
                    <p class="refund-quote">"{{ $refund['description'] }}"</p>
                @endif
            </div>

            @if ($isCustomerRequest)
                <div class="refund-card">
                    <h2 class="refund-card-title"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"></rect><circle cx="9" cy="9" r="1.6"></circle><path d="m21 15-4.5-4.5L6 21"></path></svg> Customer Evidence</h2>

                    @if (count($refund['evidence']))
                        <div class="refund-evidence-list">
                            @foreach ($refund['evidence'] as $image)
                                <a href="{{ $image }}" target="_blank" rel="noopener" class="refund-evidence-item">
                                    <img src="{{ $image }}" alt="Customer evidence {{ $loop->iteration }}">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="refund-empty">The customer did not upload any evidence.</p>
                    @endif
                </div>
            @endif

            <div class="refund-card">
                <h2 class="refund-card-title"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M4 10h16M4 10 12 3l8 7M6 10v11M18 10v11M10 14v4M14 14v4"></path></svg> {{ $refund['is_manual'] ? 'Bank Transfer Details' : 'Payment Information' }}</h2>

                <div class="refund-bank">
                    @if (! empty($refund['payment_logo']))
                        <span class="refund-bank-logo">
                            <img src="{{ $refund['payment_logo'] }}" alt="{{ $refund['payment_method'] }}">
                        </span>
                    @else
                        <span class="refund-bank-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="3"></rect><path d="M2 10h20"></path></svg>
                        </span>
                    @endif
                    <div class="refund-bank-info">
                        <strong>Paid with {{ $refund['payment_method'] }}</strong>
                        <span class="refund-bank-sub">
                            {{ $refund['is_manual'] ? 'Refund via manual bank transfer' : 'Refund via the same e-wallet' }}
                        </span>
                    </div>
                </div>

                @if ($refund['is_manual'])
                    @if ($refund['bank'])
                        <div class="refund-transfer">
                            <div class="refund-transfer-row">
                                <span class="refund-fact-label">Bank</span>
                                <span class="refund-transfer-value">{{ $refund['bank']['name'] }}</span>
                            </div>
                            <div class="refund-transfer-row">
                                <span class="refund-fact-label">Account Number</span>
                                <span class="refund-transfer-value is-mono">{{ $refund['bank']['number'] }}</span>
                                <button type="button" class="refund-copy" data-copy="{{ $refund['bank']['number'] }}">Copy</button>
                            </div>
                            <div class="refund-transfer-row">
                                <span class="refund-fact-label">Account Holder</span>
                                <span class="refund-transfer-value">{{ $refund['bank']['holder'] }}</span>
                            </div>
                            <div class="refund-transfer-row is-total">
                                <span class="refund-fact-label">Amount to transfer</span>
                                <span class="refund-transfer-value is-amount">{{ $formatRupiah($refund['amount']) }}</span>
                                <button type="button" class="refund-copy" data-copy="{{ $refund['amount'] }}">Copy</button>
                            </div>
                        </div>
                    @else
                        <p class="refund-hint">The customer has not entered a bank account yet. The transfer can be made once they do (they see a button on their order page).</p>
                    @endif

                    @if ($refund['proof'])
                        <span class="refund-fact-label">Transfer proof</span>
                        <div class="refund-evidence-list">
                            <a href="{{ $refund['proof'] }}" target="_blank" rel="noopener" class="refund-evidence-item">
                                <img src="{{ $refund['proof'] }}" alt="Transfer proof">
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="refund-card refund-action-card">
            <h2 class="refund-card-title"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6l-8-3Z"></path><path d="m9 12 2.2 2.2L15.5 10"></path></svg> Processing Action</h2>

            @if ($isFinal)
                @php $isRefunded = $refund['status'] === 'refunded'; @endphp

                <div class="refund-result {{ $isRefunded ? 'is-success' : 'is-danger' }}">
                    <span class="refund-result-icon" aria-hidden="true">
                        @if ($isRefunded)
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
                        @else
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                        @endif
                    </span>
                    <div>
                        <strong>
                            {{ $isRefunded ? ($refund['is_manual'] ? 'Refund transferred to the customer' : 'Refund sent to the customer') : 'Refund request rejected' }}
                        </strong>
                        This request has been finalized and can no longer be changed.
                    </div>
                </div>

                @if ($refund['notes'])
                    <div class="refund-result is-note">
                        <div>
                            <strong>Resolution notes</strong>
                            {{ $refund['notes'] }}
                        </div>
                    </div>
                @endif
            @else
                @if ($refund['is_manual'] && ! $refund['bank'])
                    <div class="refund-notice">
                        <strong>Waiting for the customer's bank account</strong>
                        This order was paid by bank VA, so the money is transferred by hand. The button below unlocks
                        once the customer fills in their bank account (My Orders &rarr; Isi Rekening).
                    </div>
                @endif

                @if ($refund['error'])
                    <div class="refund-result">
                        <div>
                            <strong>The last attempt with Midtrans failed</strong>
                            {{ $refund['error'] }}
                        </div>
                    </div>
                @endif

                @if ($refund['is_manual'])
                    <p class="refund-hint">Transfer the money to the account shown, then upload the proof and confirm. {{ $isCustomerRequest ? 'You can also reject the request.' : '' }}</p>
                @elseif ($isCustomerRequest)
                    <p class="refund-hint">Approving sends the refund to the customer through Midtrans straight away.</p>
                @else
                    <p class="refund-hint">The customer is owed this refund because the paid order was rejected. It could not be completed automatically, so try again.</p>
                @endif

                <form id="refundForm" method="POST" action="{{ route('admin.refund.finalize', $refund['id']) }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    @if ($isCustomerRequest)
                        <div class="refund-field">
                            <span class="refund-field-label">Determination Status</span>
                            <div class="refund-segmented" role="radiogroup" aria-label="Determination status">
                                <label>
                                    <input type="radio" name="decision" value="approve"
                                        {{ $decision === 'approve' ? 'checked' : '' }}>
                                    <span>Approve</span>
                                </label>
                                <label>
                                    <input type="radio" name="decision" value="reject"
                                        {{ $decision === 'reject' ? 'checked' : '' }}>
                                    <span>Reject</span>
                                </label>
                            </div>
                            @error('decision')
                                <span class="admin-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="refund-field">
                            <label class="refund-field-label" for="notes">
                                Resolution Notes
                                <small id="notesHint" style="display: none;">(required when rejecting)</small>
                            </label>
                            <textarea name="notes" id="notes" rows="5" maxlength="1000"
                                placeholder="Enter notes to be sent to the customer...">{{ old('notes') }}</textarea>
                            <span class="admin-form-error" id="notesError">@error('notes'){{ $message }}@enderror</span>
                        </div>

                        @if ($refund['is_manual'])
                            @include('admin.partials.refund-proof-field')
                        @endif

                        <button type="submit" class="refund-submit">Finalize Refund</button>
                    @else
                        <input type="hidden" name="decision" value="approve">

                        @if ($refund['is_manual'])
                            @include('admin.partials.refund-proof-field')
                            <button type="submit" class="refund-submit" @disabled(! $refund['bank'])>Mark as Refunded</button>
                        @else
                            <button type="submit" class="refund-submit">Retry Refund via Midtrans</button>
                        @endif
                    @endif
                </form>
            @endif
        </div>
    </div>

    <p class="refund-back">
        <a href="{{ route('admin.refund') }}">&larr; Back to refunds</a>
    </p>

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.refund-copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const done = function () {
                    btn.textContent = 'Copied';
                    btn.classList.add('is-copied');
                    setTimeout(function () { btn.textContent = 'Copy'; btn.classList.remove('is-copied'); }, 1600);
                };
                if (navigator.clipboard) navigator.clipboard.writeText(btn.dataset.copy).then(done);
            });
        });

        (function () {
            const zone = document.getElementById('proofDropzone');
            const input = document.getElementById('bukti_transfer');
            if (!zone || !input) return;

            const img = document.getElementById('proofImg');
            const nameEl = document.getElementById('proofName');
            const sizeEl = document.getElementById('proofSize');
            const zoomBtn = document.getElementById('proofZoom');
            const removeBtn = document.getElementById('proofRemove');
            let url = null;

            function formatSize(bytes) {
                return bytes >= 1048576
                    ? (bytes / 1048576).toFixed(1) + ' MB'
                    : Math.max(1, Math.round(bytes / 1024)) + ' KB';
            }

            function sync() {
                if (url) { URL.revokeObjectURL(url); url = null; }
                const file = input.files && input.files[0];
                zone.classList.toggle('has-file', !!file);
                if (!file) { img.removeAttribute('src'); return; }
                url = URL.createObjectURL(file);
                img.src = url;
                nameEl.textContent = file.name;
                sizeEl.textContent = formatSize(file.size);
            }

            input.addEventListener('change', sync);

            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                input.value = '';
                sync();
            });

            zoomBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (url) window.open(url, '_blank');
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                zone.addEventListener(evt, function (e) { e.preventDefault(); zone.classList.add('is-dragover'); });
            });
            ['dragleave', 'drop'].forEach(function (evt) {
                zone.addEventListener(evt, function (e) { e.preventDefault(); zone.classList.remove('is-dragover'); });
            });
            zone.addEventListener('drop', function (e) {
                const file = e.dataTransfer && e.dataTransfer.files[0];
                if (file && /^image\/(png|jpe?g)$/.test(file.type)) {
                    input.files = e.dataTransfer.files;
                    sync();
                }
            });
        })();

        (function () {
            const form = document.getElementById('refundForm');
            const notes = document.getElementById('notes');
            if (!form || !notes) return;

            const notesError = document.getElementById('notesError');
            const notesHint = document.getElementById('notesHint');

            function currentDecision() {
                const checked = form.querySelector('input[name="decision"]:checked');
                return checked ? checked.value : 'approve';
            }

            const proofField = document.getElementById('proofField');

            function syncDecision() {
                notesHint.style.display = currentDecision() === 'reject' ? 'inline' : 'none';
                if (proofField) proofField.style.display = currentDecision() === 'approve' ? '' : 'none';
            }

            form.querySelectorAll('input[name="decision"]').forEach(function (radio) {
                radio.addEventListener('change', syncDecision);
            });

            notes.addEventListener('input', function () { notesError.textContent = ''; });

            form.addEventListener('submit', function (e) {
                if (currentDecision() === 'reject' && notes.value.trim() === '') {
                    notesError.textContent = 'Please write a note explaining why the refund is rejected.';
                    e.preventDefault();
                }
            });

            syncDecision();
        })();
    </script>
@endpush