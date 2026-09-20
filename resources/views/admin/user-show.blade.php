@extends('layouts.admin')

@section('title', 'Detail User — Donat Wawa')

@push('styles')
    @vite('resources/css/admin/user.css')
@endpush

@section('content')

    @php
        // HANYA data order yang hardcode (data user tetap dari database via $user)
        $recentOrders = collect([
            ['code' => '#DW-0001', 'date' => 'Today, 08:30 AM',        'amount' => 'Rp 25.000', 'status' => 'Completed'],
            ['code' => '#DW-0002', 'date' => '02 Sep 2026, 08:30 WIB', 'amount' => 'Rp 25.000', 'status' => 'Completed'],
            ['code' => '#DW-0003', 'date' => '02 Sep 2026, 08:30 WIB', 'amount' => 'Rp 25.000', 'status' => 'Completed'],
        ])->take(8);
    @endphp

    <div class="admin-page-header">
        <div>
            <h1>Detail User</h1>
            <p>{{ $user->user_code }}</p>
        </div>
    </div>

    <div class="user-detail-grid">

        <div class="admin-card user-detail-profile-card">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="user-detail-avatar">
            @else
                <span class="user-detail-avatar user-detail-avatar-initial">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </span>
            @endif

            <h2 class="user-detail-name">{{ $user->name }}</h2>
            <p class="user-detail-email">{{ $user->email }}</p>

            <div class="user-detail-facts">
                <div class="user-detail-fact-row">
                    <span>Join Date</span>
                    <strong>{{ $user->created_at->format('d F Y') }}</strong>
                </div>
                <div class="user-detail-fact-row">
                    <span>No. telp</span>
                    <strong>{{ $user->phone ?: '—' }}</strong>
                </div>
                <div class="user-detail-fact-row">
                    <span>Total Orders</span>
                    <strong>{{ $recentOrders->count() }}</strong>
                </div>
            </div>
        </div>

        <div class="admin-card user-detail-orders-card">
            <div class="user-detail-orders-head">
                <h2>Recent Orders</h2>
            </div>

            <div class="user-detail-table-wrap">
                <table class="user-orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="user-detail-order-id">{{ $order['code'] }}</td>
                                <td class="user-detail-order-date {{ str_starts_with($order['date'], 'Today') ? 'is-today' : '' }}">{{ $order['date'] }}</td>
                                <td>{{ $order['amount'] }}</td>
                                <td>
                                    <span class="admin-status-pill {{ strtolower($order['status']) === 'completed' ? 'admin-status-active' : 'admin-status-inactive' }}">
                                        {{ $order['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr class="admin-empty-row">
                                <td colspan="4">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection