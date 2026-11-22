{{--
    Pretty replacement for the browser's confirm() box.
    Put data-confirm="Message" on a <form> (optionally data-confirm-title, data-confirm-ok) and include this partial once.
--}}
<div class="dw-modal" id="dw-confirm" hidden>
    <div class="dw-modal-backdrop" data-dw-close></div>
    <div class="dw-modal-box" role="alertdialog" aria-modal="true" aria-labelledby="dw-confirm-title"
        aria-describedby="dw-confirm-text">
        <span class="dw-modal-icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8 12 3 3 8v8l9 5 9-5V8Z"></path>
                <path d="m8.5 12 2.5 2.5 4.5-5"></path>
            </svg>
        </span>
        <h3 id="dw-confirm-title">{{ __('Confirm') }}</h3>
        <p id="dw-confirm-text"></p>
        <div class="dw-modal-actions">
            <button type="button" class="dw-modal-btn dw-modal-btn--ghost" data-dw-close>{{ __('Not yet') }}</button>
            <button type="button" class="dw-modal-btn dw-modal-btn--primary" id="dw-confirm-ok">{{ __('Yes') }}</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const modal = document.getElementById('dw-confirm');
            const title = document.getElementById('dw-confirm-title');
            const text = document.getElementById('dw-confirm-text');
            const ok = document.getElementById('dw-confirm-ok');
            const defaults = { title: title.textContent, ok: ok.textContent };
            let pendingForm = null;
            let lastFocus = null;

            function open(form) {
                pendingForm = form;
                lastFocus = document.activeElement;
                title.textContent = form.dataset.confirmTitle || defaults.title;
                text.textContent = form.dataset.confirm;
                ok.textContent = form.dataset.confirmOk || defaults.ok;
                modal.hidden = false;
                requestAnimationFrame(function () { modal.classList.add('is-open'); });
                ok.focus();
            }

            function close() {
                modal.classList.remove('is-open');
                modal.hidden = true;
                pendingForm = null;
                if (lastFocus) { lastFocus.focus(); }
            }

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form.dataset || !form.dataset.confirm || form.dataset.confirmed === '1') { return; }
                e.preventDefault();
                open(form);
            });

            ok.addEventListener('click', function () {
                if (!pendingForm) { return; }
                const form = pendingForm;
                form.dataset.confirmed = '1';
                modal.hidden = true;
                form.submit();
            });

            modal.querySelectorAll('[data-dw-close]').forEach(function (el) {
                el.addEventListener('click', close);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.hidden) { close(); }
            });
        })();
    </script>
@endpush
