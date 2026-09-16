@php
    $loginOpen = old('intent') === 'login';
@endphp

<div class="auth-popup" id="loginPopup" style="display:none;">
    <button type="button" class="auth-close" aria-label="Close" onclick="closeAuthPopup()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"></path></svg>
    </button>

    <div class="auth-head">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Donat Wawa" class="auth-logo">
        <h2>Welcome Back!</h2>
        <p>Sign in and continue enjoying sweet happiness in every bite.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <input type="hidden" name="intent" value="login">

        <div class="auth-field">
            <label for="login-email">Email</label>
            <div class="auth-input {{ $errors->has('email') && $loginOpen ? 'has-error' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"></path>
                    <path d="m3.5 6.5 8.5 7 8.5-7"></path>
                </svg>
                <input type="email" id="login-email" name="email" placeholder="name@email.com"
                    value="{{ $loginOpen ? old('email') : '' }}" required autocomplete="email">
            </div>
            @if ($errors->has('email') && $loginOpen)
                <span class="auth-error">{{ $errors->first('email') }}</span>
            @endif
        </div>

        <div class="auth-field">
            <label for="login-password">Password</label>
            <div class="auth-input">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                </svg>
                <input type="password" id="login-password" name="password" placeholder="Enter your password" required
                    autocomplete="current-password">
                <button type="button" class="auth-eye" aria-label="Show password"
                    onclick="toggleAuthPassword(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            <div class="auth-field-foot">
                @if ($errors->has('password') && $loginOpen)
                    <span class="auth-error">{{ $errors->first('password') }}</span>
                @else
                    <span></span>
                @endif
                <a href="#" class="auth-link-sm" onclick="return false;">Forgot Password?</a>
            </div>
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Sign In</button>
    </form>

    <div class="auth-divider"><span>or sign in with</span></div>

    <button type="button" class="auth-google" onclick="return false;">
        <svg width="18" height="18" viewBox="0 0 48 48">
            <path fill="#FFC107"
                d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 8 3l6-6C34.1 5.5 29.3 3.5 24 3.5 12.7 3.5 3.5 12.7 3.5 24S12.7 44.5 24 44.5 44.5 35.3 44.5 24c0-1.2-.1-2.4-.9-3.5Z">
            </path>
            <path fill="#FF3D00"
                d="m6.3 14.7 6.6 4.8C14.6 15.9 18.9 13 24 13c3.1 0 5.8 1.1 8 3l6-6C34.1 6.1 29.3 4 24 4 16 4 9.1 8.6 6.3 14.7Z">
            </path>
            <path fill="#4CAF50"
                d="M24 44.5c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.6 26.7 36.5 24 36.5c-5.3 0-9.6-3.3-11.3-8l-6.5 5c2.8 6.1 9.6 11 17.8 11Z">
            </path>
            <path fill="#1976D2"
                d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.2-4.2 5.6l6.2 5.2C40.9 36 44.5 30.6 44.5 24c0-1.2-.1-2.4-.9-3.5Z">
            </path>
        </svg>
        Google
    </button>

    <p class="auth-switch">Don't have an account? <a href="#" onclick="openAuthPopup('register'); return false;">Register
            Now</a></p>
</div>