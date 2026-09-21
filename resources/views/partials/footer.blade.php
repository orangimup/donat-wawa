<footer class="footer" id="about">
    <div class="container-ww">
        <div class="footer-grid">
            <div>
                <img src="{{ asset('assets/images/text-logo-maroon.png') }}" alt="Donat Wawa" class="footer-logo-img" style="margin-bottom:14px;">
                <p class="footer-about">{{ __('Sharing handcrafted happiness one pillowy bite at a time. Local, fresh, and always artisanal.') }}</p>
            </div>
            <div class="footer-col">
                <h4>{{ mb_strtoupper(__('Store')) }}</h4>
                <a href="#">{{ __('Store Hours: 2PM - 9PM') }}</a>
                <a href="#">Jl. Pahlawan No. 339, Balearjosari, Kec. Blimbing, Kota Malang</a>
            </div>
            <div class="footer-col">
                <h4>{{ mb_strtoupper(__('Contact')) }}</h4>
                <a href="#">Instagram</a>
                <a href="#">WhatsApp</a>
            </div>
            <div class="footer-col">
                <h4>{{ mb_strtoupper(__('Legal')) }}</h4>
                <a href="#">{{ __('Terms of Service') }}</a>
                <a href="#">{{ __('Privacy Policy') }}</a>
            </div>
        </div>
        <div class="footer-bottom">&copy; {{ date('Y') }} Donat Wawa. {{ __('All rights reserved.') }}</div>
    </div>
</footer>