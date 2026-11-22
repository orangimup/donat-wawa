@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Request a Refund'))
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
            <h1>{{ __('Request a Refund') }}</h1>
            <p>{{ __('We apologize for the inconvenience. Please fill out the form below to process the refund.') }}</p>
            <p>{{ $needsBank
                ? __('You can request a refund until :deadline WIB. Once approved, we transfer the money to the bank account you enter below.', ['deadline' => $deadline])
                : __('You can request a refund until :deadline WIB. Once approved, the money is returned to the e-wallet you paid with.', ['deadline' => $deadline]) }}</p>
        </div>

        @if ($errors->any())
            <p class="order-refund-error" role="alert">{{ $errors->first() }}</p>
        @endif

        @include('user.settings.partials.refund-form', [
            'action' => route('settings.orders.refund.store', $order['id']),
            'cancelUrl' => route('settings.orders.show', $order['id']),
        ])

    </div>

@endsection
