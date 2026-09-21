@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Notification Settings'))
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/settings.css')
@endpush

@section('content')

    <div class="container-ww settings-layout">

        @include('partials.settings-sidebar', ['active' => 'notifications'])

        <div class="settings-content">
            <h1>{{ __('Account Settings') }}</h1>

            @if (session('status'))
                <div class="settings-alert">{{ session('status') }}</div>
            @endif

            <h3 class="settings-section-title settings-section-title--first">{{ __('Manage how you receive alerts') }}</h3>

            <form method="POST" action="{{ route('settings.notifications.update') }}">
                @csrf

                <div class="settings-card">
                    @php
                        $notifItems = [
                            ['name' => 'notif_order_status', 'label' => 'Order Status Updates', 'desc' => 'Receive updates regarding your active orders and delivery status.'],
                            ['name' => 'notif_order_confirmation', 'label' => 'Order Confirmation Updates', 'desc' => 'Get notified when your order is accepted or declined by the store.'],
                            ['name' => 'notif_review_reminder', 'label' => 'Review Reminder', 'desc' => 'Get reminded to leave a review once your order is completed.'],
                            ['name' => 'notif_new_product', 'label' => 'New Product Alerts', 'desc' => 'Be the first to know when we introduce new donuts or flavors.'],
                            ['name' => 'notif_daily_reminder', 'label' => 'Daily Reminders', 'desc' => 'Friendly reminders to check our daily special menu.'],
                        ];
                    @endphp

                    @foreach ($notifItems as $item)
                        <div class="settings-row settings-row-static settings-toggle-row">
                            <div>
                                <div class="settings-toggle-label">{{ __($item['label']) }}</div>
                                <div class="settings-toggle-desc">{{ __($item['desc']) }}</div>
                            </div>
                            <label class="settings-switch">
                                <input type="checkbox" name="{{ $item['name'] }}" value="1"
                                    onchange="this.form.submit()"
                                    {{ old($item['name'], $preference->{$item['name']}) ? 'checked' : '' }}
                                <span class="settings-switch-track"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    </div>

@endsection