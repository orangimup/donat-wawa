@extends('layouts.admin')

@section('title', 'Delivery Zone Management — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/delivery-zone.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 2, ',', '.');

        $sortLabels = [
            'distance' => 'Distance',
            'latest' => 'Latest',
            'fee_low' => 'Fee: Low to High',
            'fee_high' => 'Fee: High to Low',
        ];

        $oldActive = old('is_active', '1') === '1';
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>Delivery Zone Management</h1>
            <p>Manage delivery coverage areas and delivery fee to ensure customers get accurate information during
                checkout.</p>
        </div>
        <button type="button" class="admin-btn" onclick="openZoneModal('add')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                stroke-linecap="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"></path>
            </svg>
            New Delivery Fee
        </button>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="admin-filter-pills">
                <a href="{{ route('admin.delivery-zone', array_filter(['search' => $search, 'sort' => $sort])) }}"
                    class="admin-filter-pill {{ $status === 'all' ? 'is-active' : '' }}">All</a>
                @foreach ($statusLabels as $key => $label)
                    <a href="{{ route('admin.delivery-zone', array_filter(['status' => $key, 'search' => $search, 'sort' => $sort])) }}"
                        class="admin-filter-pill {{ $status === $key ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <form method="GET" class="admin-sort" id="sortForm">
                <label for="sortTrigger">Sort by:</label>
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" id="sortValue" value="{{ $sort }}">

                <div class="admin-select" id="sortSelect">
                    <button type="button" class="admin-select-trigger" id="sortTrigger" aria-haspopup="listbox"
                        aria-expanded="false">
                        <span id="sortTriggerLabel">{{ $sortLabels[$sort] ?? 'Distance' }}</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <ul class="admin-select-menu" id="sortMenu" role="listbox">
                        @foreach ($sortLabels as $key => $label)
                            <li role="option" data-value="{{ $key }}"
                                class="admin-select-option {{ $sort === $key ? 'is-selected' : '' }}">{{ $label }}</li>
                        @endforeach
                    </ul>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table zone-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th class="zone-col-range">Distance Range (KM)</th>
                        <th>Delivery Fee</th>
                        <th class="zone-col-status">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($zones as $zone)
                        <tr>
                            <td>{{ $loop->iteration + ($zones->currentPage() - 1) * $zones->perPage() }}</td>
                            <td class="zone-col-range zone-nowrap">{{ $zone['label'] }}</td>
                            <td class="zone-nowrap">{{ $formatRupiah($zone['fee']) }}</td>
                            <td class="zone-col-status">
                                <span class="zone-status-pill zone-status-{{ $zone['status'] }}">
                                    {{ $statusLabels[$zone['status']] }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <button type="button" class="admin-icon-btn"
                                        aria-label="Edit zone {{ $zone['label'] }}"
                                        onclick="openZoneModal('edit', this)"
                                        data-zone-id="{{ $zone['id'] }}"
                                        data-range="{{ $zone['label'] }}"
                                        data-fee="{{ $zone['fee'] }}"
                                        data-active="{{ $zone['status'] === 'active' ? '1' : '0' }}"
                                        data-update-url="{{ route('admin.delivery-zone.update', $zone['id']) }}">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.delivery-zone.destroy', $zone['id']) }}" method="POST"
                                        class="admin-delete-form" data-name="the {{ $zone['label'] }} zone">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="admin-icon-btn admin-icon-btn-danger"
                                            aria-label="Delete zone {{ $zone['label'] }}"
                                            onclick="openDeleteModal(this.closest('form'))">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6M14 11v6"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="5">No delivery zones found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <span class="admin-pagination-info">
                Showing {{ $zones->count() }} of {{ $zones->total() }} entries
            </span>
            {{ $zones->onEachSide(1)->links('admin.partials.pagination') }}
        </div>
    </div>

    {{-- Add / Edit popup --}}
    <div class="admin-modal-overlay admin-modal-overlay-center zone-modal-overlay" id="zoneModalOverlay">
        <div class="admin-modal zone-modal" role="dialog" aria-modal="true" aria-labelledby="zoneModalTitle">
            <div class="zone-modal-head">
                <h2 id="zoneModalTitle">Add Delivery Zone</h2>
                <button type="button" class="admin-modal-close" onclick="closeZoneModal()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="zoneForm" method="POST" action="{{ route('admin.delivery-zone.store') }}" novalidate>
                @csrf
                <input type="hidden" name="_method" id="zoneMethod" value="POST" disabled>
                <input type="hidden" name="_mode" id="zoneMode" value="{{ old('_mode', 'add') }}">
                <input type="hidden" name="_zone_id" id="zoneId" value="{{ old('_zone_id') }}">
                <input type="hidden" name="is_active" id="zoneActive" value="{{ $oldActive ? '1' : '0' }}">

                <div class="zone-modal-body">
                    <div class="zone-field">
                        <label for="distance_range">Distance Range (KM)</label>
                        <div class="zone-input {{ $errors->has('distance_range') ? 'has-error' : '' }}" id="rangeBox">
                            <input type="text" name="distance_range" id="distance_range" maxlength="30"
                                placeholder="Example: 0 - 2 km" value="{{ old('distance_range') }}" autocomplete="off">
                        </div>
                        <span class="zone-error" id="rangeError">@error('distance_range'){{ $message }}@enderror</span>
                    </div>

                    <div class="zone-field">
                        <label for="fee">Delivery Fee</label>
                        <div class="zone-input {{ $errors->has('fee') ? 'has-error' : '' }}" id="feeBox">
                            <span>Rp</span>
                            <input type="number" name="fee" id="fee" min="0" step="500" placeholder="0"
                                value="{{ old('fee') }}">
                        </div>
                        <span class="zone-error" id="feeError">@error('fee'){{ $message }}@enderror</span>
                    </div>

                    <div class="zone-toggle">
                        <button type="button" class="admin-toggle-switch {{ $oldActive ? 'is-on' : '' }}"
                            id="zoneToggle" role="switch" aria-checked="{{ $oldActive ? 'true' : 'false' }}"
                            aria-labelledby="zoneToggleLabel"></button>
                        <span id="zoneToggleLabel">Active</span>
                    </div>
                </div>

                <div class="zone-modal-footer">
                    <button type="button" class="admin-btn admin-btn-ghost" onclick="closeZoneModal()">Cancel</button>
                    <button type="submit" class="admin-btn" id="zoneSubmit">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete confirmation (same pattern as the product page) --}}
    <div class="admin-modal-overlay admin-modal-overlay-center" id="deleteModalOverlay">
        <div class="admin-modal admin-modal-sm">
            <div class="admin-modal-head">
                <div>
                    <h2>Delete Zone?</h2>
                    <p class="admin-modal-subtitle" id="deleteModalText">Are you sure you want to delete this zone?</p>
                </div>
                <button type="button" class="admin-modal-close" onclick="closeDeleteModal()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="admin-form-actions">
                <div class="admin-form-actions-right">
                    <button type="button" class="admin-btn admin-btn-outline"
                        onclick="closeDeleteModal()">Cancel</button>
                    <button type="button" class="admin-btn admin-btn-danger" id="deleteConfirmBtn">Delete Zone</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        /* Sort-by dropdown (same behaviour as the other admin index pages) */
        (function () {
            const sortSelect = document.getElementById('sortSelect');
            const sortTrigger = document.getElementById('sortTrigger');
            const sortTriggerLabel = document.getElementById('sortTriggerLabel');
            const sortMenu = document.getElementById('sortMenu');
            const sortValue = document.getElementById('sortValue');
            const sortForm = document.getElementById('sortForm');

            function closeSortMenu() {
                sortSelect.classList.remove('is-open');
                sortTrigger.setAttribute('aria-expanded', 'false');
            }

            sortTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = sortSelect.classList.toggle('is-open');
                sortTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            sortMenu.querySelectorAll('.admin-select-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    sortValue.value = opt.dataset.value;
                    sortTriggerLabel.textContent = opt.textContent;
                    sortMenu.querySelectorAll('.admin-select-option').forEach(function (o) { o.classList.remove('is-selected'); });
                    opt.classList.add('is-selected');
                    closeSortMenu();
                    sortForm.submit();
                });
            });

            document.addEventListener('click', function (e) {
                if (!sortSelect.contains(e.target)) closeSortMenu();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSortMenu();
            });
        })();

        /* Add / Edit zone popup */
        (function () {
            const STORE_URL = @json(route('admin.delivery-zone.store'));

            const overlay = document.getElementById('zoneModalOverlay');
            const form = document.getElementById('zoneForm');
            const title = document.getElementById('zoneModalTitle');
            const submitBtn = document.getElementById('zoneSubmit');
            const methodField = document.getElementById('zoneMethod');
            const modeField = document.getElementById('zoneMode');
            const idField = document.getElementById('zoneId');
            const activeField = document.getElementById('zoneActive');
            const toggle = document.getElementById('zoneToggle');
            const toggleLabel = document.getElementById('zoneToggleLabel');

            const rangeInput = document.getElementById('distance_range');
            const feeInput = document.getElementById('fee');
            const rangeBox = document.getElementById('rangeBox');
            const feeBox = document.getElementById('feeBox');
            const rangeError = document.getElementById('rangeError');
            const feeError = document.getElementById('feeError');

            const RANGE_PATTERN = /^\s*\d+(?:[.,]\d+)?\s*[-\u2013]\s*\d+(?:[.,]\d+)?\s*(?:km)?\s*$/i;

            function setActive(isActive) {
                activeField.value = isActive ? '1' : '0';
                toggle.classList.toggle('is-on', isActive);
                toggle.setAttribute('aria-checked', isActive ? 'true' : 'false');
                toggleLabel.textContent = isActive ? 'Active' : 'Inactive';
            }

            function clearErrors() {
                rangeError.textContent = '';
                feeError.textContent = '';
                rangeBox.classList.remove('has-error');
                feeBox.classList.remove('has-error');
            }

            function show() {
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
                rangeInput.focus();
            }

            function configure(mode, btn, keepValues) {
                modeField.value = mode;

                if (mode === 'edit') {
                    form.action = btn.dataset.updateUrl;
                    methodField.value = 'PUT';
                    methodField.disabled = false;
                    idField.value = btn.dataset.zoneId;
                    title.textContent = 'Edit Delivery Zone';
                    submitBtn.textContent = 'Update';

                    if (!keepValues) {
                        rangeInput.value = btn.dataset.range;
                        feeInput.value = btn.dataset.fee;
                        setActive(btn.dataset.active === '1');
                    }
                } else {
                    form.action = STORE_URL;
                    methodField.disabled = true;
                    idField.value = '';
                    title.textContent = 'Add Delivery Zone';
                    submitBtn.textContent = 'Save';

                    if (!keepValues) {
                        rangeInput.value = '';
                        feeInput.value = '';
                        setActive(true);
                    }
                }
            }

            window.openZoneModal = function (mode, btn) {
                clearErrors();
                configure(mode, btn, false);
                show();
            };

            window.closeZoneModal = function () {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            };

            toggle.addEventListener('click', function () {
                setActive(activeField.value !== '1');
            });

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeZoneModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('open')) closeZoneModal();
            });

            rangeInput.addEventListener('input', function () {
                rangeError.textContent = '';
                rangeBox.classList.remove('has-error');
            });

            feeInput.addEventListener('input', function () {
                feeError.textContent = '';
                feeBox.classList.remove('has-error');
            });

            /* The server validates the same rules (plus overlapping ranges); this only saves a round trip */
            form.addEventListener('submit', function (e) {
                let valid = true;

                if (!RANGE_PATTERN.test(rangeInput.value)) {
                    rangeError.textContent = rangeInput.value.trim() === ''
                        ? 'Please enter the distance range.'
                        : 'Enter the range like "0 - 2 km".';
                    rangeBox.classList.add('has-error');
                    valid = false;
                }

                if (feeInput.value === '' || Number(feeInput.value) < 0) {
                    feeError.textContent = 'Please enter the delivery fee.';
                    feeBox.classList.add('has-error');
                    valid = false;
                }

                if (!valid) e.preventDefault();
            });

            /* Re-open the popup after a server-side validation error */
            @if ($errors->any() && $retryMode)
                (function () {
                    @if ($retryMode === 'edit' && $retryId)
                        const btn = document.querySelector('[data-zone-id="{{ $retryId }}"]');
                        if (btn) { configure('edit', btn, true); setActive(activeField.value === '1'); show(); }
                    @else
                        configure('add', null, true);
                        setActive(activeField.value === '1');
                        show();
                    @endif
                })();
            @endif
        })();

        /* Delete confirmation */
        (function () {
            const overlay = document.getElementById('deleteModalOverlay');
            const text = document.getElementById('deleteModalText');
            const confirmBtn = document.getElementById('deleteConfirmBtn');
            let activeForm = null;

            window.openDeleteModal = function (form) {
                activeForm = form;
                text.textContent = 'Are you sure you want to delete ' + form.dataset.name + '? This action cannot be undone.';
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            };

            window.closeDeleteModal = function () {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
                activeForm = null;
            };

            confirmBtn.addEventListener('click', function () {
                if (activeForm) activeForm.submit();
            });

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeDeleteModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('open')) closeDeleteModal();
            });
        })();
    </script>
@endpush