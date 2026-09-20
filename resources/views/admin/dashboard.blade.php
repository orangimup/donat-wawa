@extends('layouts.admin')

@section('title', 'Dashboard — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/dashboard.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 2, ',', '.');
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Overview of your bakery's performance today.</p>
        </div>
        <button type="button" class="admin-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                stroke-linecap="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14"></path>
            </svg>
            New Order
        </button>
    </div>

    <div class="dash-stats">
        @foreach ($stats as $stat)
            <div class="dash-stat-card {{ $stat['featured'] ? 'is-featured' : '' }}">
                <div class="dash-stat-head">
                    <h2 class="dash-stat-title">{{ $stat['title'] }}</h2>
                    <span class="dash-stat-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="8" cy="21" r="1"></circle>
                            <circle cx="19" cy="21" r="1"></circle>
                            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                        </svg>
                    </span>
                </div>
                <p class="dash-stat-value">{{ $stat['value'] }}</p>
                <div class="dash-stat-foot">
                    @if ($stat['badge'])
                        <span class="dash-stat-badge">{{ $stat['badge'] }}</span>
                    @endif
                    <span title="{{ $stat['caption'] }}">{{ $stat['caption'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="dash-grid">
        {{-- Daily order count --}}
        <div class="dash-card dash-chart-card">
            <div class="dash-chart-head">
                <h2 class="dash-chart-title">
                    <span class="dash-chart-title-icon" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="8" cy="21" r="1"></circle>
                            <circle cx="19" cy="21" r="1"></circle>
                            <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                        </svg>
                    </span>
                    Daily order count
                </h2>

                <div class="dash-range" id="dashRange" role="tablist" aria-label="Chart range">
                    <button type="button" class="dash-range-btn" data-range="today">Today</button>
                    <button type="button" class="dash-range-btn" data-range="monthly">Monthly</button>
                    <button type="button" class="dash-range-btn is-active" data-range="weekly">Weekly</button>
                </div>
            </div>

            <div class="dash-chart">
                <div class="dash-axis" id="dashAxis" aria-hidden="true"></div>
                <div class="dash-plot">
                    <div class="dash-bars" id="dashBars"></div>
                    <div class="dash-labels" id="dashLabels"></div>
                </div>
            </div>
        </div>

        <div class="dash-side">
            {{-- Revenue trend --}}
            <div class="dash-card dash-trend-card">
                <div class="dash-side-head">
                    <h2 class="dash-side-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                            <polyline points="16 7 22 7 22 13"></polyline>
                        </svg>
                        Revenue Trend
                    </h2>
                    <span class="dash-trend-badge">{{ $trend['change'] }}</span>
                </div>
                <div class="dash-trend-chart">
                    <svg viewBox="0 0 300 100" preserveAspectRatio="none" role="img" aria-label="Revenue trend">
                        <defs>
                            <linearGradient id="dashTrendFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#7b2026" stop-opacity="0.38"></stop>
                                <stop offset="100%" stop-color="#7b2026" stop-opacity="0.12"></stop>
                            </linearGradient>
                        </defs>
                        <path d="{{ $trend['area'] }}" fill="url(#dashTrendFill)"></path>
                        <path d="{{ $trend['line'] }}" class="dash-trend-line"></path>
                    </svg>
                </div>
            </div>

            {{-- Top product --}}
            <div class="dash-card dash-top-card">
                <h2 class="dash-side-title">Top Product</h2>
                <a href="{{ $topProduct['url'] }}" class="dash-top-item">
                    <img src="{{ $topProduct['image'] }}" alt="{{ $topProduct['name'] }}" class="dash-top-thumb">
                    <div class="dash-top-info">
                        <strong>{{ $topProduct['name'] }}</strong>
                        <span>{{ number_format($topProduct['sold'], 0, ',', '.') }} sold</span>
                    </div>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Latest orders --}}
    <div class="dash-card dash-orders-card">
        <div class="dash-orders-head">
            <h2>Latest Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="dash-view-all">View All</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table dash-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th class="dash-col-id">Order ID</th>
                        <th>Customer</th>
                        <th class="dash-col-items">Items</th>
                        <th>Total Price</th>
                        <th>Type Delivery</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestOrders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="dash-col-id">{{ $order['code'] }}</td>
                            <td>{{ $order['customer'] }}</td>
                            <td class="dash-col-items"><span class="dash-items">{{ $order['items'] }}</span></td>
                            <td class="dash-nowrap">{{ $formatRupiah($order['total']) }}</td>
                            <td class="dash-nowrap">{{ $order['delivery'] }}</td>
                            <td>
                                <span class="dash-status-pill dash-status-{{ $order['status'] }}">
                                    {{ $statusLabels[$order['status']] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr class="admin-empty-row">
                            <td colspan="7">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        /* Daily order count chart: Today / Monthly / Weekly */
        (function () {
            const DATA = @json($chart);

            const axisEl = document.getElementById('dashAxis');
            const barsEl = document.getElementById('dashBars');
            const labelsEl = document.getElementById('dashLabels');
            const rangeEl = document.getElementById('dashRange');

            /* Axis with 4 steps, rounded up to a "nice" step size */
            function niceAxis(max) {
                const steps = [1, 2, 3, 4, 5, 10, 15, 20, 25, 30, 40, 50, 75, 100, 150, 200, 250, 300, 400, 500, 1000];
                const raw = Math.max(max, 1) / 4;
                const step = steps.find(function (s) { return s >= raw; }) || Math.ceil(raw);
                return { step: step, max: step * 4 };
            }

            function render(range) {
                const set = DATA[range];
                const axis = niceAxis(Math.max.apply(null, set.values));
                const peak = set.values.indexOf(Math.max.apply(null, set.values));

                axisEl.innerHTML = '';
                for (let i = 0; i <= 4; i++) {
                    const tick = document.createElement('span');
                    tick.textContent = axis.step * i;
                    tick.style.bottom = (i * 25) + '%';
                    axisEl.appendChild(tick);
                }

                barsEl.innerHTML = '';
                labelsEl.innerHTML = '';

                set.values.forEach(function (value, index) {
                    const col = document.createElement('div');
                    col.className = 'dash-bar-col';

                    const bar = document.createElement('div');
                    bar.className = 'dash-bar' + (index === peak ? ' is-active' : '');
                    bar.style.height = '0%';
                    bar.title = set.labels[index] + ': ' + value + ' orders';

                    const tip = document.createElement('span');
                    tip.className = 'dash-bar-tip';
                    tip.textContent = value;
                    bar.appendChild(tip);

                    bar.addEventListener('mouseenter', function () {
                        barsEl.querySelectorAll('.dash-bar').forEach(function (b) { b.classList.remove('is-active'); });
                        bar.classList.add('is-active');
                    });

                    col.appendChild(bar);
                    barsEl.appendChild(col);

                    const label = document.createElement('span');
                    label.textContent = set.labels[index];
                    labelsEl.appendChild(label);

                    /* animate from 0 to the real height */
                    requestAnimationFrame(function () {
                        requestAnimationFrame(function () {
                            bar.style.height = (value / axis.max * 100) + '%';
                        });
                    });
                });

                /* Keep the peak highlighted when the pointer leaves the chart */
                barsEl.onmouseleave = function () {
                    barsEl.querySelectorAll('.dash-bar').forEach(function (b, i) {
                        b.classList.toggle('is-active', i === peak);
                    });
                };
            }

            rangeEl.querySelectorAll('.dash-range-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    rangeEl.querySelectorAll('.dash-range-btn').forEach(function (b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    render(btn.dataset.range);
                });
            });

            render('weekly');
        })();
    </script>
@endpush