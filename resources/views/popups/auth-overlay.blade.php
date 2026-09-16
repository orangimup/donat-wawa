@php
    $authOpen = old('intent');
@endphp

<div class="auth-overlay" id="authOverlay" data-auth-open="{{ $authOpen }}">
    @include('popups.login-popup')
    @include('popups.register-popup')
</div>