<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Donat Wawa — Authentic Artisanal Donuts')</title>
    @php
        $i18n = [
            'locale' => app()->getLocale() === 'id' ? 'id-ID' : 'en-US',
            'item' => __('Item'),
            'processing' => __('Processing...'),
            'proceed' => __('Proceed to Payment'),
            'paymentFailed' => __('Payment failed, please try again.'),
            'somethingWrong' => __('Something went wrong, please try again.'),
            'trackUnavailable' => __('Order tracking is not available yet — coming soon once the backend is wired up.'),
            'sameDay' => __('Same-Day'),
            'scheduled' => __('Scheduled Order'),
            'paymentSuccess' => __('Payment Success'),
            'awaitingPayment' => __('Awaiting Payment'),
            'saving' => __('Saving...'),
            'directions' => __('Get directions'),
            'remove' => __('Remove'),
        ];
    @endphp
    <script>
        window.isAuthenticated = @json(auth()->check());
        window.I18N = @json($i18n);
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/base.css', 'resources/css/home.css', 'resources/css/auth.css', 'resources/js/app.js'])
    @stack('styles')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>

<body class="@yield('body-class')">

    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    @unless (View::hasSection('hide-footer'))
        @include('partials.footer')
    @endunless

    @guest
        @include('popups.auth-overlay')
    @endguest

    @stack('scripts')
</body>

</html>