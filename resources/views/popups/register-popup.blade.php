@php
    $registerOpen = old('intent') === 'register';
@endphp

<div class="auth-popup" id="registerPopup" style="display:none;">
    <button type="button" class="auth-close" aria-label="Close" onclick="closeAuthPopup()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"></path></svg>
    </button>

    <div class="auth-head">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Donat Wawa" class="auth-logo">
        <h2>Welcome!</h2>
        <p>Sign up and experience sweet happiness in every bite.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <input type="hidden" name="intent" value="register">

        <div class="auth-field">
            <label for="register-name">Full Name</label>
            <div class="auth-input {{ $errors->has('name') && $registerOpen ? 'has-error' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"></circle>
                    <path d="M4 21c1.5-4.5 5-6 8-6s6.5 1.5 8 6"></path>
                </svg>
                <input type="text" id="register-name" name="name" placeholder="Enter your full name"
                    value="{{ $registerOpen ? old('name') : '' }}" required autocomplete="name">
            </div>
            @if ($errors->has('name') && $registerOpen)
                <span class="auth-error">{{ $errors->first('name') }}</span>
            @endif
        </div>

        <div class="auth-field">
            <label for="register-email">Email</label>
            <div class="auth-input {{ $errors->has('email') && $registerOpen ? 'has-error' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"></path>
                    <path d="m3.5 6.5 8.5 7 8.5-7"></path>
                </svg>
                <input type="email" id="register-email" name="email" placeholder="name@email.com"
                    value="{{ $registerOpen ? old('email') : '' }}" required autocomplete="email">
            </div>
            @if ($errors->has('email') && $registerOpen)
                <span class="auth-error">{{ $errors->first('email') }}</span>
            @endif
        </div>

        <div class="auth-field">
            <label for="register-phone">Phone Number</label>
            <div class="auth-input {{ $errors->has('phone') && $registerOpen ? 'has-error' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 2 .7 3a2 2 0 0 1-.5 2.1L8 10.1a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c1 .4 2 .6 3 .7a2 2 0 0 1 1.6 2Z">
                    </path>
                </svg>
                <input type="tel" id="register-phone" name="phone" placeholder="08xx xxxx xxxx"
                    value="{{ $registerOpen ? old('phone') : '' }}" required autocomplete="tel">
            </div>
            @if ($errors->has('phone') && $registerOpen)
                <span class="auth-error">{{ $errors->first('phone') }}</span>
            @endif
        </div>

        <div class="auth-field">
            <label for="register-password">Password</label>
            <div class="auth-input {{ $errors->has('password') && $registerOpen ? 'has-error' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                </svg>
                <input type="password" id="register-password" name="password" placeholder="Minimum 8 characters" required
                    autocomplete="new-password" minlength="8">
                <button type="button" class="auth-eye" aria-label="Show password"
                    onclick="toggleAuthPassword(this)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            @if ($errors->has('password') && $registerOpen)
                <span class="auth-error">{{ $errors->first('password') }}</span>
            @endif
        </div>

        <button type="submit" class="btn btn-primary auth-submit">Register Now</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="#" onclick="openAuthPopup('login'); return false;">Sign In</a>
    </p>
</div>