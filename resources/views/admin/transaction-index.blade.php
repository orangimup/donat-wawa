@extends('layouts.admin')

@section('title', 'Transaction Management — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/transaction.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 2, ',', '.');

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
            <h1>{{ __('Transaction Management') }}</h1>
            <p>{{ __('Monitor and manage Midtrans payment gateways.') }}</p>
        </div>
    </div>

    <div class="trx-stats">
        @foreach ($stats as $stat)
            <div class="trx-stat-card {{ $stat['featured'] ? 'is-featured' : '' }}">
                <div class="trx-stat-head">
                    <h2 class="trx-stat-title">{{ __($stat['title']) }}</h2>
                    <span class="trx-stat-icon" aria-hidden="true">
                        @if ($stat['icon'] === 'trend')
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                <polyline points="16 7 22 7 22 13"></polyline>
                            </svg>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                            </svg>
                        @endif
                    </span>
                </div>
                <p class="trx-stat-value">{{ $stat['value'] }}</p>
                <div class="trx-stat-foot">
                    @if ($stat['badge'])
                        <span class="trx-stat-badge">{{ $stat['badge'] }}</span>
                    @endif
                    <span>{{ __($stat['caption']) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="admin-filter-pills">
                <a href="{{ route('admin.transactions.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                    class="admin-filter-pill {{ $status === 'all' ? 'is-active' : '' }}">{{ __('All') }}</a>
                @foreach ($statusLabels as $key => $label)
                    <a href="{{ route('admin.transactions.index', array_filter(['status' => $key, 'search' => $search, 'sort' => $sort])) }}"
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
            <table class="admin-table trx-table">
                <thead>
                    <tr>
                        <th>{{ __('No.') }}</th>
                        <th class="trx-col-id">{{ __('Transaction ID') }}</th>
                        <th>{{ __('Date & Time') }}</th>
                        <th class="trx-col-customer">{{ __('Customer') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr>
                            <td>{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                            <td class="trx-col-id">{{ $trx['code'] }}</td>
                            <td>
                                <span class="trx-date">{{ $trx['date_label'] }}</span>
                                <span class="trx-time">{{ $trx['time_label'] }}</span>
                            </td>
                            <td class="trx-col-customer">{{ $trx['customer'] }}</td>
                            <td class="trx-amount">{{ $formatRupiah($trx['amount']) }}</td>
                            <td class="trx-method">{{ $trx['method'] }}</td>
                            <td>
                                <span class="trx-status-pill trx-status-{{ $trx['status'] }}">
                                    {{ __($statusLabels[$trx['status']]) }}
                                </span>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <button type="button" class="admin-icon-btn"
                                        aria-label="{{ __('View transaction :p1', ['p1' => $trx['code']]) }}">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.4" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="8">{{ __('No transactions found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">
            <span class="admin-pagination-info">
                {{ __('Showing :count of :total entries', ['count' => $transactions->count(), 'total' => $transactions->total()]) }}
            </span>
            {{ $transactions->onEachSide(1)->links('admin.partials.pagination') }}
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
    </script>
@endpush