@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Order History'))
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/settings.css')
    @vite('resources/css/order-history.css')
@endpush

@section('content')

    @php
        $formatRupiah = fn ($amount) => 'Rp ' . number_format($amount, 0, ',', '.');

        $tabs = [
            'pending' => __('Pending'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
        ];

        $groupOfStatus = [
            'waiting' => 'pending',
            'process' => 'pending',
            'shipped' => 'pending',
            'done' => 'completed',
            'rejected' => 'cancelled',
        ];

        $pillLabel = [
            'pending' => __('Active'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
        ];

        $pillClass = [
            'pending' => 'active',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
        ];
    @endphp

    <div class="container-ww settings-layout">

        @include('partials.settings-sidebar', ['active' => 'order-history'])

        <div class="settings-content order-history-content">

            <div class="order-history-head">
                <h1>{{ __('Order History') }}</h1>

                <a href="{{ url('/menu') }}" class="order-history-view-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                        <path d="M3 6h18"></path>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    {{ __('View Order') }}
                </a>
            </div>

            <div class="order-history-tabs-row">
                <div class="order-history-tabs">
                    <a href="{{ route('settings.orders') }}"
                        class="order-history-tab {{ $group === 'all' ? 'is-active' : '' }}">
                        {{ __('All Order') }}
                        <span class="order-history-tab-count">{{ $counts['all'] }}</span>
                    </a>
                    @foreach ($tabs as $key => $label)
                        <a href="{{ route('settings.orders', ['status' => $key]) }}"
                            class="order-history-tab {{ $group === $key ? 'is-active' : '' }}">
                            {{ $label }}
                            <span class="order-history-tab-count">{{ $counts[$key] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="order-history-card">
                <div class="order-history-table-wrap">
                    <table class="order-history-table">
                        <thead>
                            <tr>
                                <th>{{ __('Items') }}</th>
                                <th>{{ __('Date & Time') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Method') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                @php
                                    $first = $order['items']->first();
                                    $extra = $order['items']->count() - 1;
                                    $rowGroup = $groupOfStatus[$order['status']];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="order-history-item-cell">
                                            <img src="{{ $first['image'] }}" alt="{{ $first['name'] }}">
                                            <div>
                                                <strong>{{ $first['name'] }}</strong>
                                                <span>
                                                    {{ __('Qty: :qty', ['qty' => $first['qty']]) }}
                                                    @if ($extra > 0)
                                                        &bull; {{ __('+:count more items', ['count' => $extra]) }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $order['placed_at']->translatedFormat('d M Y') }}</td>
                                    <td class="order-history-amount">{{ $formatRupiah($order['total']) }}</td>
                                    <td>{{ $order['method'] }}</td>
                                    <td>
                                        <span class="order-history-pill order-history-pill-{{ $pillClass[$rowGroup] }}">
                                            {{ $pillLabel[$rowGroup] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('settings.orders.show', $order['id']) }}" class="order-history-see">
                                            {{ __('See Details') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="order-history-empty-row">
                                    <td colspan="6">
                                        {{ __('No orders found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($orders->total() > 0)
                    <div class="order-history-footer">
                        <span class="order-history-count">
                            {{ __('Showing :count of :total entries', ['count' => $orders->count(), 'total' => $orders->total()]) }}
                        </span>
                        <div class="order-history-pagination">
                            {{ $orders->onEachSide(1)->links('components.pagination') }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection