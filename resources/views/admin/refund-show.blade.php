@extends('layouts.admin')

@section('title', 'Process Refund — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/refund.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 2, ',', '.');

        $decision = old('decision', 'approve');
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>Process Refund</h1>
            <p>Request ID: {{ $refund['code'] }}</p>
        </div>
        <span class="refund-status-pill is-large refund-status-{{ $refund['status'] }}">
            {{ $statusLabels[$refund['status']] }}
        </span>
    </div>

    <div class="refund-detail-grid">

        <div class="refund-detail-main">

            {{-- Transaction details --}}
            <div class="refund-card">
                <h2 class="refund-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                        <path d="M10 9H8"></path>
                        <path d="M16 13H8"></path>
                        <path d="M16 17H8"></path>
                    </svg>
                    Transaction Details
                </h2>

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
                        <span class="refund-fact-label">Requested Amount</span>
                        <div class="refund-fact-value is-amount">{{ $formatRupiah($refund['amount']) }}</div>
                    </div>
                    <div>
                        <span class="refund-fact-label">Reason Code</span>
                        <div><span class="refund-reason-pill">{{ $refund['reason'] }}</span></div>
                    </div>
                </div>

                <span class="refund-fact-label">Customer Description</span>
                <p class="refund-quote">"{{ $refund['description'] }}"</p>
            </div>

            {{-- Customer evidence --}}
            <div class="refund-card">
                <h2 class="refund-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                        <circle cx="9" cy="9" r="2"></circle>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                    Customer Evidence
                </h2>

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

            {{-- Bank account --}}
            <div class="refund-card">
                <h2 class="refund-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                        <line x1="2" x2="22" y1="10" y2="10"></line>
                    </svg>
                    Bank Account Information
                </h2>

                <div class="refund-bank">
                    <span class="refund-bank-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                            <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                            <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                            <path d="M10 6h4"></path>
                            <path d="M10 10h4"></path>
                            <path d="M10 14h4"></path>
                            <path d="M10 18h4"></path>
                        </svg>
                    </span>
                    <div class="refund-bank-info">
                        <strong>{{ $refund['bank_name'] }}</strong>
                        <span>{{ $refund['bank_holder'] }} &bull; **** {{ $refund['bank_last4'] }}</span>
                    </div>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Processing action --}}
        <div class="refund-card refund-action-card">
            <h2 class="refund-card-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                    <path d="m9 11 3 3L22 4"></path>
                </svg>
                Processing Action
            </h2>

            @if ($isFinal)
                <div class="refund-result">
                    <strong>
                        {{ $refund['status'] === 'refunded' ? 'Refund transferred' : 'Refund request rejected' }}
                    </strong>
                    This request has been finalized and can no longer be changed.
                </div>

                @if ($refund['proof_name'])
                    <div class="refund-result">
                        <strong>Proof of transfer</strong>
                        {{ $refund['proof_name'] }}
                    </div>
                @endif

                @if ($refund['notes'])
                    <div class="refund-result">
                        <strong>Resolution notes</strong>
                        {{ $refund['notes'] }}
                    </div>
                @endif
            @else
                @if ($awaitingTransfer)
                    <p class="refund-hint">
                        This request has been approved. Upload the proof of transfer to mark it as refunded.
                    </p>
                @endif

                <form id="refundForm" method="POST" action="{{ route('admin.refund.finalize', $refund['id']) }}"
                    enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="refund-field">
                        <span class="refund-field-label">Determination Status</span>
                        <div class="refund-segmented" role="radiogroup" aria-label="Determination status">
                            <label>
                                <input type="radio" name="decision" value="approve"
                                    {{ $decision === 'approve' ? 'checked' : '' }}>
                                <span>Approve</span>
                            </label>
                            <label class="{{ $awaitingTransfer ? 'is-disabled' : '' }}">
                                <input type="radio" name="decision" value="reject"
                                    {{ $decision === 'reject' && ! $awaitingTransfer ? 'checked' : '' }}
                                    {{ $awaitingTransfer ? 'disabled' : '' }}>
                                <span>Reject</span>
                            </label>
                        </div>
                        @error('decision')
                            <span class="admin-form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="refund-field" id="proofField">
                        <span class="refund-field-label">Upload Proof of Transfer</span>
                        <label class="admin-dropzone refund-dropzone" id="proofDropzone" for="proof">
                            <span class="admin-dropzone-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" x2="12" y1="3" y2="15"></line>
                                </svg>
                            </span>
                            <span class="admin-dropzone-text" id="proofText">Click to upload or drag and drop</span>
                            <span class="admin-dropzone-hint">PNG, JPG or PDF (max. 5MB)</span>
                        </label>
                        <input type="file" name="proof" id="proof" class="admin-file-input"
                            accept=".png,.jpg,.jpeg,.pdf,image/png,image/jpeg,application/pdf">
                        <span class="admin-form-error" id="proofError">@error('proof'){{ $message }}@enderror</span>
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

                    <button type="submit" class="refund-submit">Finalize Refund</button>
                </form>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
        <script>
            (function () {
                const awaitingTransfer = @json($awaitingTransfer);
                const MAX_SIZE = 5 * 1024 * 1024;
                const ALLOWED = ['image/png', 'image/jpeg', 'application/pdf'];

                const form = document.getElementById('refundForm');
                if (!form) return; /* finalized requests have no form */

                const radios = form.querySelectorAll('input[name="decision"]');
                const proofField = document.getElementById('proofField');
                const dropzone = document.getElementById('proofDropzone');
                const proofInput = document.getElementById('proof');
                const proofText = document.getElementById('proofText');
                const proofError = document.getElementById('proofError');
                const notes = document.getElementById('notes');
                const notesError = document.getElementById('notesError');
                const notesHint = document.getElementById('notesHint');
                const defaultText = proofText.textContent;

                function currentDecision() {
                    const checked = form.querySelector('input[name="decision"]:checked');
                    return checked ? checked.value : 'approve';
                }

                function syncDecision() {
                    const reject = currentDecision() === 'reject';
                    proofField.classList.toggle('is-hidden', reject);
                    notesHint.style.display = reject ? 'inline' : 'none';
                }

                function clearFile() {
                    proofInput.value = '';
                    proofText.textContent = defaultText;
                    dropzone.classList.remove('has-file');
                }

                function setFile(file) {
                    proofError.textContent = '';

                    if (!ALLOWED.includes(file.type)) {
                        clearFile();
                        proofError.textContent = 'The proof must be a PNG, JPG or PDF file.';
                        return;
                    }

                    if (file.size > MAX_SIZE) {
                        clearFile();
                        proofError.textContent = 'The proof may not be larger than 5MB.';
                        return;
                    }

                    proofText.textContent = file.name;
                    dropzone.classList.add('has-file');
                }

                radios.forEach(function (radio) { radio.addEventListener('change', syncDecision); });

                proofInput.addEventListener('change', function () {
                    if (proofInput.files.length) setFile(proofInput.files[0]);
                });

                ['dragenter', 'dragover'].forEach(function (name) {
                    dropzone.addEventListener(name, function (e) {
                        e.preventDefault();
                        dropzone.classList.add('is-dragover');
                    });
                });

                ['dragleave', 'drop'].forEach(function (name) {
                    dropzone.addEventListener(name, function (e) {
                        e.preventDefault();
                        dropzone.classList.remove('is-dragover');
                    });
                });

                dropzone.addEventListener('drop', function (e) {
                    if (!e.dataTransfer.files.length) return;
                    const file = e.dataTransfer.files[0];
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    proofInput.files = transfer.files;
                    setFile(file);
                });

                notes.addEventListener('input', function () { notesError.textContent = ''; });

                /* The server validates the same rules; this only saves a round trip */
                form.addEventListener('submit', function (e) {
                    const reject = currentDecision() === 'reject';
                    let valid = true;

                    if (reject && notes.value.trim() === '') {
                        notesError.textContent = 'Please write a note explaining why the refund is rejected.';
                        valid = false;
                    }

                    if (!reject && awaitingTransfer && !proofInput.files.length) {
                        proofError.textContent = 'Please upload the proof of transfer.';
                        valid = false;
                    }

                    if (!valid) e.preventDefault();
                });

                syncDecision();
            })();
        </script>
@endpush