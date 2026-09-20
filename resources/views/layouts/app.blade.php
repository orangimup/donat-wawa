<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Donat Wawa — Authentic Artisanal Donuts')</title>
    <script>
        window.isAuthenticated = @json(auth()->check());
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