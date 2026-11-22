@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Refund Bank Details'))
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/order-refund.css')
    <style>
        body {
            background: #fff;
        }
    </style>
@endpush

@section('content')

    <div class="container-ww order-refund-layout">

        <div class="order-refund-head">
            <h1>{{ __('Refund Bank Details') }}</h1>
            <p>{{ __('Your payment will be refunded by bank transfer. Tell us where to send :amount.', ['amount' => 'Rp ' . number_format($refund->jumlah_refund, 0, ',', '.')]) }}</p>
        </div>

        @if ($errors->any())
            <p class="order-refund-error" role="alert">{{ $errors->first() }}</p>
        @endif

        @include('user.settings.partials.refund-form', [
            'action' => route('settings.orders.refund.bank.store', $order['id']),
            'cancelUrl' => route('settings.orders.show', $order['id']),
            'needsBank' => true,
        ])

    </div>

@endsection
