document.addEventListener('DOMContentLoaded', function () {

    /* auth popup login regis*/
    const authOverlay = document.getElementById('authOverlay');

    window.openAuthPopup = function (which) {
        if (!authOverlay) return;
        const login = document.getElementById('loginPopup');
        const register = document.getElementById('registerPopup');
        if (which === 'register') {
            login.style.display = 'none';
            register.style.display = 'block';
        } else {
            register.style.display = 'none';
            login.style.display = 'block';
        }
        authOverlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.closeAuthPopup = function () {
        if (!authOverlay) return;
        authOverlay.classList.remove('open');
        document.body.style.overflow = '';
    };

    window.toggleAuthPassword = function (btn) {
        const input = btn.previousElementSibling;
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
    };

    if (authOverlay) {
        authOverlay.addEventListener('click', function (e) {
            if (e.target === authOverlay) {
                closeAuthPopup();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAuthPopup();
        });

        const autoOpen = authOverlay.getAttribute('data-auth-open');
        if (autoOpen === 'login' || autoOpen === 'register') {
            openAuthPopup(autoOpen);
        }
    }

    /* user nav dropdown */
    const userDropdown = document.getElementById('userDropdown');

    window.toggleUserMenu = function () {
        if (!userDropdown) return;
        userDropdown.classList.toggle('open');
    };

    if (userDropdown) {
        document.addEventListener('click', function (e) {
            const menu = document.querySelector('.user-menu');
            if (menu && !menu.contains(e.target)) {
                userDropdown.classList.remove('open');
            }
        });
    }

});

    /* language dropdown navbar */
    const langDropdown = document.getElementById('langDropdown');

    window.toggleLangMenu = function () {
        if (!langDropdown) return;
        langDropdown.classList.toggle('open');
    };

    if (langDropdown) {
        document.addEventListener('click', function (e) {
            const menu = document.querySelector('.lang-menu');
            if (menu && !menu.contains(e.target)) {
                langDropdown.classList.remove('open');
            }
        });
    }