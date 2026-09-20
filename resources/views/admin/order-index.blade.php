@extends('layouts.admin')

@section('title', 'Order Management — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/order.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 2, ',', '.');

        $sortLabels = [
            'latest' => 'Latest',
            'oldest' => 'Oldest',
            'customer' => 'Customer',
            'total_high' => 'Total: High to Low',
            'total_low' => 'Total: Low to High',
        ];
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>Order Management</h1>
            <p>Review and manage customer orders in real-time.</p>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="admin-filter-pills">
                <a href="{{ route('admin.orders.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                    class="admin-filter-pill {{ $status === 'all' ? 'is-active' : '' }}">All</a>
                @foreach ($statusLabels as $key => $label)
                    <a href="{{ route('admin.orders.index', array_filter(['status' => $key, 'search' => $search, 'sort' => $sort])) }}"
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
                        <span id="sortTriggerLabel">{{ $sortLabels[$sort] ?? 'Latest' }}</span>
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
            <table class="admin-table order-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th class="order-col-id">Order ID</th>
                        <th>Customer</th>
                        <th class="order-col-items">Items</th>
                        <th>Total Price</th>
                        <th>Type Delivery</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                            <td class="order-col-id">{{ $order['code'] }}</td>
                            <td>{{ $order['customer'] }}</td>
                            <td class="order-col-items"><span class="order-items">{{ $order['items_label'] }}</span></td>
                            <td class="order-total">{{ $formatRupiah($order['total']) }}</td>
                            <td class="order-delivery">{{ $order['delivery'] }}</td>
                            <td>
                                <span class="order-status-pill order-status-{{ $order['status'] }}">
                                    {{ $statusLabels[$order['status']] }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <button type="button" class="admin-icon-btn"
                                        aria-label="Manage order {{ $order['code'] }}"
                                        @if ($order['status'] === 'rejected')
                                            disabled title="Rejected orders cannot be changed"
                                        @else
                                            onclick="openOrderPopup(this)"
                                        @endif
                                        data-order-id="{{ $order['id'] }}"
                                        data-code="{{ $order['code'] }}"
                                        data-customer="{{ $order['customer'] }}"
                                        data-delivery="{{ $order['delivery'] }}"
                                        data-total="{{ $formatRupiah($order['total']) }}"
                                        data-items="{{ $order['items_popup'] }}"
                                        data-status="{{ $order['status'] }}"
                                        data-confirm-url="{{ route('admin.orders.confirm', $order['id']) }}"
                                        data-status-url="{{ route('admin.orders.status', $order['id']) }}">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path
                                                d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="8">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <span class="admin-pagination-info">
                Showing {{ $orders->count() }} of {{ $orders->total() }} entries
            </span>
            {{ $orders->onEachSide(1)->links('admin.partials.pagination') }}
        </div>
    </div>

    {{-- Popup 1: Order Confirmation (only for orders with Waiting status) --}}
    <div class="admin-modal-overlay admin-modal-overlay-center order-modal-overlay" id="orderConfirmOverlay">
        <div class="admin-modal order-modal order-modal-confirm" role="dialog" aria-modal="true"
            aria-labelledby="orderConfirmTitle">
            <div class="order-modal-head">
                <h2 id="orderConfirmTitle">Order Confirmation</h2>
                <button type="button" class="admin-modal-close" onclick="closeOrderPopups()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="orderConfirmForm" method="POST" action="" novalidate>
                @csrf
                <input type="hidden" name="_order_id" id="orderConfirmId" value="{{ old('_order_id') }}">
                <input type="hidden" name="_popup" value="confirm">

                <div class="order-modal-body">
                    <div class="order-notice">
                        <svg class="order-notice-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 8v4M12 16h.01"></path>
                        </svg>
                        <div>
                            <strong>Accept this order now?</strong>
                            <span>The order status will automatically change to <em>"Process"</em></span>
                        </div>
                    </div>

                    <div class="order-summary">
                        <div class="order-summary-row">
                            <div>
                                <span class="order-summary-label">Order ID</span>
                                <div class="order-summary-value is-strong" id="confirmCode"></div>
                            </div>
                            <div>
                                <span class="order-summary-label">Total Payment</span>
                                <div class="order-summary-value is-total" id="confirmTotal"></div>
                            </div>
                        </div>
                        <div class="order-summary-row">
                            <div>
                                <span class="order-summary-label">Customer</span>
                                <div class="order-summary-value" id="confirmCustomer"></div>
                            </div>
                            <div>
                                <span class="order-summary-label">Delivery</span>
                                <div class="order-summary-value" id="confirmDelivery"></div>
                            </div>
                        </div>
                        <div class="order-summary-row">
                            <div>
                                <span class="order-summary-label">Ordered Items</span>
                                <div class="order-summary-value" id="confirmItems"></div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-group full">
                        <label for="rejection_reason">
                            Rejection reason <small>(only fill in if rejecting the order)</small>
                        </label>
                        <input type="text" name="rejection_reason" id="rejection_reason"
                            placeholder="Example: Zen Matcha is out of stock..." maxlength="500"
                            value="{{ old('rejection_reason') }}">
                        <span class="admin-form-error" id="rejectionReasonError">@error('rejection_reason'){{ $message }}@enderror</span>
                    </div>
                </div>

                <div class="order-modal-footer">
                    <button type="submit" name="action" value="reject" class="admin-btn admin-btn-ghost">Reject</button>
                    <button type="submit" name="action" value="accept" class="admin-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5"></path>
                        </svg>
                        Accept
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Popup 2: Update Order Status (for accepted orders: Process / Shipped / Done) --}}
    <div class="admin-modal-overlay admin-modal-overlay-center order-modal-overlay" id="orderStatusOverlay">
        <div class="admin-modal order-modal" role="dialog" aria-modal="true" aria-labelledby="orderStatusTitle">
            <div class="order-modal-head">
                <h2 id="orderStatusTitle">Update Order Status</h2>
                <button type="button" class="admin-modal-close" onclick="closeOrderPopups()" aria-label="Close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round">
                        <path d="M18 6 6 18M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="orderStatusForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="_order_id" id="orderStatusId" value="{{ old('_order_id') }}">
                <input type="hidden" name="_popup" value="status">

                <div class="order-modal-body">
                    @php
                        $statusChoices = ['process' => 'Process', 'shipped' => 'Shipped', 'done' => 'Done'];
                    @endphp
                    <div class="admin-form-group">
                        <label for="orderStatusTrigger">New Status</label>
                        <div class="admin-select admin-select-form" id="orderStatusSelect">
                            <button type="button" class="admin-select-trigger" id="orderStatusTrigger"
                                aria-haspopup="listbox" aria-expanded="false">
                                <span id="orderStatusTriggerLabel">Process</span>
                                <svg width="10" height="6" viewBox="0 0 10 6" fill="none">
                                    <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <ul class="admin-select-menu" id="orderStatusMenu" role="listbox">
                                @foreach ($statusChoices as $key => $label)
                                    <li role="option" data-value="{{ $key }}" class="admin-select-option">{{ $label }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <input type="hidden" name="status" id="orderStatusValue" value="{{ old('status', 'process') }}">
                        @error('status')
                            <span class="admin-form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="order-modal-footer">
                    <button type="button" class="admin-btn admin-btn-ghost" onclick="closeOrderPopups()">Cancel</button>
                    <button type="submit" class="admin-btn">Update</button>
                </div>
            </form>
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

        /* Order popups: Waiting -> Confirmation, Process/Shipped/Done -> Update Status */
        (function () {
            const confirmOverlay = document.getElementById('orderConfirmOverlay');
            const confirmForm = document.getElementById('orderConfirmForm');
            const confirmId = document.getElementById('orderConfirmId');
            const reasonInput = document.getElementById('rejection_reason');
            const reasonError = document.getElementById('rejectionReasonError');

            const statusOverlay = document.getElementById('orderStatusOverlay');
            const statusForm = document.getElementById('orderStatusForm');
            const statusId = document.getElementById('orderStatusId');
            const statusValue = document.getElementById('orderStatusValue');
            const statusSelect = document.getElementById('orderStatusSelect');
            const statusTrigger = document.getElementById('orderStatusTrigger');
            const statusTriggerLabel = document.getElementById('orderStatusTriggerLabel');
            const statusMenu = document.getElementById('orderStatusMenu');
            const statusOptions = statusMenu.querySelectorAll('.admin-select-option');

            const setText = function (id, value) { document.getElementById(id).textContent = value; };

            function lockScroll(lock) { document.body.style.overflow = lock ? 'hidden' : ''; }

            function openConfirm(btn, keepFormState) {
                confirmForm.action = btn.dataset.confirmUrl;
                confirmId.value = btn.dataset.orderId;
                setText('confirmCode', btn.dataset.code);
                setText('confirmTotal', btn.dataset.total);
                setText('confirmCustomer', btn.dataset.customer);
                setText('confirmDelivery', btn.dataset.delivery);
                setText('confirmItems', btn.dataset.items);

                if (!keepFormState) {
                    reasonInput.value = '';
                    reasonError.textContent = '';
                }

                confirmOverlay.classList.add('open');
                lockScroll(true);
            }

            function setStatus(value) {
                statusValue.value = value;
                statusOptions.forEach(function (opt) {
                    const selected = opt.dataset.value === value;
                    opt.classList.toggle('is-selected', selected);
                    if (selected) statusTriggerLabel.textContent = opt.textContent;
                });
            }

            function closeStatusMenu() {
                statusSelect.classList.remove('is-open');
                statusTrigger.setAttribute('aria-expanded', 'false');
            }

            function openStatus(btn, keepFormState) {
                statusForm.action = btn.dataset.statusUrl;
                statusId.value = btn.dataset.orderId;
                setStatus(keepFormState ? statusValue.value : btn.dataset.status);
                closeStatusMenu();

                statusOverlay.classList.add('open');
                lockScroll(true);
            }

            window.openOrderPopup = function (btn) {
                if (btn.dataset.status === 'waiting') {
                    openConfirm(btn, false);
                } else if (btn.dataset.status !== 'rejected') {
                    openStatus(btn, false);
                }
            };

            window.closeOrderPopups = function () {
                confirmOverlay.classList.remove('open');
                statusOverlay.classList.remove('open');
                closeStatusMenu();
                lockScroll(false);
            };

            [confirmOverlay, statusOverlay].forEach(function (overlay) {
                overlay.addEventListener('click', function (e) {
                    if (e.target === overlay) closeOrderPopups();
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeOrderPopups();
            });

            /* Pressing Enter in the reason field must not trigger "Reject" (first submit button) */
            reasonInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') e.preventDefault();
            });

            reasonInput.addEventListener('input', function () { reasonError.textContent = ''; });

            /* Rejecting requires a reason; the server validates this too */
            confirmForm.addEventListener('submit', function (e) {
                const isReject = e.submitter && e.submitter.value === 'reject';
                if (isReject && reasonInput.value.trim() === '') {
                    e.preventDefault();
                    reasonError.textContent = 'Please enter a reason for rejecting the order.';
                    reasonInput.focus();
                }
            });

            /* Status dropdown */
            statusTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = statusSelect.classList.toggle('is-open');
                statusTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            statusOptions.forEach(function (opt) {
                opt.addEventListener('click', function () {
                    setStatus(opt.dataset.value);
                    closeStatusMenu();
                });
            });

            document.addEventListener('click', function (e) {
                if (!statusSelect.contains(e.target)) closeStatusMenu();
            });

            @if ($errors->any() && $retryOrder)
                (function () {
                    const btn = document.querySelector('[data-order-id="{{ $retryOrder['id'] }}"]');
                    if (!btn) return;
                    @if ($retryPopup === 'confirm')
                        openConfirm(btn, true);
                    @elseif ($retryPopup === 'status')
                        openStatus(btn, true);
                    @endif
                })();
            @endif
        })();
    </script>
@endpush