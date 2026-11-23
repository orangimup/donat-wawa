@extends('layouts.admin')

@section('title', 'Refund Management — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/refund.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

        $sortLabels = [
            'latest' => 'Latest',
            'oldest' => 'Oldest',
            'customer' => 'Customer',
            'amount_high' => 'Amount: High to Low',
            'amount_low' => 'Amount: Low to High',
        ];
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>{{ __('Refund Management') }}</h1>
            <p>{{ __('Review and manage customer refund requests.') }}</p>
        </div>
    </div>

    <div class="refund-stats">
        <div class="refund-stat-card is-featured">
            <div class="refund-stat-head">
                <h2 class="refund-stat-title">{{ __('Pending Refunds') }}</h2>
                <span class="refund-stat-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 14v2.2l1.6 1"></path>
                        <path d="M16 4h2a2 2 0 0 1 2 2v.832"></path>
                        <path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h2"></path>
                        <circle cx="16" cy="16" r="6"></circle>
                        <rect x="8" y="2" width="8" height="4" rx="1"></rect>
                    </svg>
                </span>
            </div>
            <div class="refund-stat-foot">
                <p class="refund-stat-value">{{ $pendingCount }}<small>{{ __('request') }}</small></p>
                <div class="refund-stat-meta">
                    @if ($waitingBankCount > 0)
                        <span class="refund-stat-badge">{{ $waitingBankCount }}</span>
                        <span>{{ __('waiting for bank details') }}</span>
                    @elseif ($pendingCount > 0)
                        <span>{{ __('Needs your action') }}</span>
                    @else
                        <span>{{ __('All caught up') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="refund-stat-card">
            <div class="refund-stat-head">
                <h2 class="refund-stat-title">{{ __('Total Refunded') }}</h2>
                <span class="refund-stat-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"></path>
                        <path d="M12 18V6"></path>
                    </svg>
                </span>
            </div>
            <div class="refund-stat-foot">
                <p class="refund-stat-value">Rp {{ number_format($totalRefunded, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="admin-filter-pills">
                <a href="{{ route('admin.refund', array_filter(['search' => $search, 'sort' => $sort])) }}"
                    class="admin-filter-pill {{ $status === 'all' ? 'is-active' : '' }}">{{ __('All') }}</a>
                @foreach ($statusLabels as $key => $label)
                    <a href="{{ route('admin.refund', array_filter(['status' => $key, 'search' => $search, 'sort' => $sort])) }}"
                        class="admin-filter-pill {{ $status === $key ? 'is-active' : '' }}">{{ __($label) }}</a>
                @endforeach
            </div>

            <form method="GET" class="admin-sort" id="sortForm">
                <label for="sortTrigger">{{ __('Sort by:') }}</label>
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" id="sortValue" value="{{ $sort }}">

                <div class="admin-select" id="sortSelect">
                    <button type="button" class="admin-select-trigger" id="sortTrigger" aria-haspopup="listbox"
                        aria-expanded="false">
                        <span id="sortTriggerLabel">{{ __($sortLabels[$sort] ?? 'Latest') }}</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <ul class="admin-select-menu" id="sortMenu" role="listbox">
                        @foreach ($sortLabels as $key => $label)
                            <li role="option" data-value="{{ $key }}"
                                class="admin-select-option {{ $sort === $key ? 'is-selected' : '' }}">{{ __($label) }}</li>
                        @endforeach
                    </ul>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table refund-table">
                <thead>
                    <tr>
                        <th>{{ __('Refund') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Requested') }}</th>
                        <th class="is-num">{{ __('Amount') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="is-action">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($refunds as $refund)
                        <tr class="refund-row" data-href="{{ route('admin.refund.show', $refund['id']) }}">
                            <td>
                                <span class="refund-cell-main">{{ $refund['code'] }}</span>
                                <span class="refund-cell-sub">{{ $refund['order_code'] }}</span>
                            </td>
                            <td>
                                <div class="refund-customer">
                                    <span class="refund-avatar" aria-hidden="true">{{ strtoupper(mb_substr($refund['customer'], 0, 1)) }}</span>
                                    <span class="refund-cell-main">{{ $refund['customer'] }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="refund-cell-main refund-nowrap">{{ $refund['request_date'] }}</span>
                                <span class="refund-cell-sub">{{ $refund['request_time'] }}</span>
                            </td>
                            <td class="is-num">
                                <span class="refund-amount">{{ $formatRupiah($refund['amount']) }}</span>
                            </td>
                            <td>
                                <span class="refund-cell-main">{{ $refund['source'] }}</span>
                                <span class="refund-payout {{ $refund['is_manual'] ? 'is-manual' : 'is-auto' }}">
                                    {{ $refund['is_manual'] ? __('Bank transfer') : 'Midtrans' }}
                                </span>
                            </td>
                            <td>
                                <span class="refund-status-pill refund-status-{{ $refund['status'] }}">
                                    {{ __($statusLabels[$refund['status']]) }}
                                </span>
                                @if ($refund['status'] === 'pending' && $refund['needs_bank'])
                                    <span class="refund-cell-hint is-warn">{{ __('Waiting for bank details') }}</span>
                                @elseif ($refund['status'] === 'pending' && $refund['error'])
                                    <span class="refund-cell-hint is-danger">{{ __('Midtrans failed · retry') }}</span>
                                @endif
                            </td>
                            <td class="is-action">
                                @if ($refund['status'] === 'pending')
                                    <a href="{{ route('admin.refund.show', $refund['id']) }}" class="refund-icon-btn"
                                        title="{{ __('Process refund') }}" aria-label="{{ __('Process refund :p1', ['p1' => $refund['code']]) }}">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 12a9 9 0 1 0 3-6.7L3 8"></path>
                                            <path d="M3 3v5h5"></path>
                                            <path d="M14.5 9.5h-3a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-3"></path>
                                            <path d="M12 8v1.5M12 15.5V17"></path>
                                        </svg>
                                    </a>
                                @else
                                    <a href="{{ route('admin.refund.show', $refund['id']) }}" class="refund-icon-btn"
                                        title="{{ __('View refund') }}" aria-label="{{ __('View refund :p1', ['p1' => $refund['code']]) }}">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="7">{{ __('No refund requests found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <span class="admin-pagination-info">
                {{ __('Showing :count of :total entries', ['count' => $refunds->count(), 'total' => $refunds->total()]) }}
            </span>
            {{ $refunds->onEachSide(1)->links('admin.partials.pagination') }}
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.refund-row[data-href]').forEach(function (row) {
            row.addEventListener('click', function (e) {
                if (e.target.closest('a, button')) return;
                window.location = row.dataset.href;
            });
        });

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
    </script>
@endpush