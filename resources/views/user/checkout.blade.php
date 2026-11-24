@extends('layouts.app')

@section('title', 'Checkout — Donat Wawa')

@section('body-class', 'page-checkout')

@section('hide-footer')
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    @vite(['resources/css/product.css', 'resources/css/checkout.css'])
@endpush

@section('content')

    <div class="container-ww checkout-page">
        <div class="checkout-header">
            <h1>{{ __('Checkout') }}</h1>
        </div>

        <div id="checkoutEmpty" class="checkout-empty" hidden>
            <p>{{ __('Your basket is empty.') }}</p>
            <a href="{{ url('/menu') }}" class="btn btn-primary">{{ __('View Menu') }}</a>
        </div>

        <div id="checkoutContent" class="checkout-layout" hidden>
            <div class="checkout-card">
                <h2>{{ __('Order Summary') }}</h2>

                <div class="checkout-items" id="checkoutItems"></div>

                <div class="checkout-totals">
                    <div class="checkout-totals-row">
                        <span>{{ __('Subtotal') }}</span>
                        <span id="checkoutSubtotal">Rp0</span>
                    </div>
                    <div class="checkout-totals-row">
                        <span>{{ __('Shipping Fee') }}</span>
                        <span id="checkoutShippingFee">Rp0</span>
                    </div>
                    <div class="checkout-totals-row checkout-totals-final">
                        <span>{{ __('Total') }}</span>
                        <span id="checkoutTotal">Rp0</span>
                    </div>
                </div>
            </div>

            <div class="checkout-card">
                <h2>{{ __('Customer & Delivery Details') }}</h2>

                <form class="checkout-form" id="checkoutForm" onsubmit="return false;">
                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Full Name') }}<span class="required-mark">*</span></span>
                        <input type="text" name="name" placeholder="{{ __('Enter your full name') }}"
                            value="{{ auth()->user()->name ?? '' }}" required>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Email') }}</span>
                        <div class="checkout-field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            </svg>
                            <input type="email" name="email" placeholder="customer@donatwawa.com"
                                value="{{ auth()->user()->email ?? '' }}" readonly>
                        </div>
                    </label>

                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Phone Number') }}<span
                                class="required-mark">*</span></span>
                        <div class="checkout-field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                            <input type="tel" name="phone" placeholder="08xx xxxx xxxx"
                                value="{{ auth()->user()->phone ?? '' }}" pattern="[0-9+\s-]{8,15}" required>
                        </div>
                    </label>

                    <div class="checkout-field">
                        <label class="checkout-field-label" for="addressSearch">{{ __('Delivery Address') }}<span
                                class="required-mark">*</span></label>

                        <div class="address-combo" id="addressCombo">
                            <textarea id="addressSearch" name="address_search" rows="3" autocomplete="new-password"
                                spellcheck="false" role="combobox" aria-expanded="false" aria-controls="addressList"
                                aria-autocomplete="list" maxlength="200" placeholder="{{ __('Type your street or area') }}"
                                required></textarea>

                            <ul class="address-list" id="addressList" role="listbox" hidden></ul>
                        </div>

                        <input type="hidden" id="addrLat" name="lat">
                        <input type="hidden" id="addrLng" name="lng">
                        <input type="hidden" id="addrArea" name="address_area">
                        <small class="address-status" id="addressStatus" data-state=""></small>

                        <div class="address-map-wrap">
                            <div class="address-map" id="addressMap"></div>
                            <button type="button" class="address-locate" id="addressLocate">{{ __('Use my location') }}</button>
                        </div>
                        <small class="address-hint">{{ __('Tap the map or drag the pin to your exact house location.') }}</small>
                    </div>

                    <label class="checkout-field">
                        <span class="checkout-field-label">{{ __('Address Details') }}<span
                                class="required-mark">*</span></span>
                        <input type="text" id="addressDetail" name="address_detail" maxlength="200" required
                            placeholder="{{ __('House number, landmark, e.g. green gate next to the mosque') }}">
                    </label>

                    <div class="checkout-field">
                        <span class="checkout-field-label">{{ __('Delivery Time') }}</span>

                        <div class="delivery-option" id="deliveryToday" data-method="today">
                            <div class="delivery-option-head">
                                <strong>{{ __('Same-Day') }}</strong>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M13 2 3 14h7l-1 8 10-12h-7l1-8Z"></path>
                                </svg>
                            </div>
                            <p>{{ __('Estimated arrival in 60-90 minutes') }}</p>
                        </div>

                        <div class="delivery-option" id="deliveryPreorder" data-method="preorder">
                            <div class="delivery-option-head">
                                <strong>{{ __('Scheduled Order') }}</strong>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                                </svg>
                            </div>
                            <p>{{ __('Choose your own delivery time') }}</p>
                        </div>

                        <div class="delivery-schedule" id="deliverySchedule" hidden>
                            <label>
                                <span>{{ __('Delivery Date') }}</span>
                                <input type="date" name="delivery_date" min="{{ $earliestPreOrderDate }}">
                            </label>
                            <label>
                                <span>{{ __('Delivery Slot') }}</span>
                                <select name="delivery_time">
                                    @foreach ($deliverySlots as $slot)
                                        <option>{{ $slot }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="checkout-payment-bar" id="checkoutPaymentBar" hidden>
        <div class="checkout-payment-inner">
            <div>
                <span class="checkout-payment-label">{{ __('Total Payment') }}</span>
                <strong id="checkoutPaymentTotal">IDR 0</strong>
            </div>
            <button type="button" class="btn btn-primary checkout-pay-btn" id="checkoutPayBtn">
                {{ __('Proceed to Payment') }}
            </button>
        </div>
    </div>

@endsection

@push('scripts')
    @vite('resources/js/basket.js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    @php
        $checkoutText = [
            'choose' => __('Pick an address from the list or mark it on the map to see your shipping fee.'),
            'searching' => __('Searching address...'),
            'markOnMap' => __("My address isn't listed - mark it on the map"),
            'locating' => __('Getting your location...'),
            'locationFailed' => __('Could not get your location. Allow location access or mark it on the map.'),
            'pinned' => __('Pinned location'),
            'calculating' => __('Calculating shipping fee...'),
            'noResults' => __('No address found. Try the street name and area.'),
            'pickAddress' => __('Please choose a suggestion or mark your location on the map.'),
            'km' => __('km'),
            'zone' => __('Zone'),
            'searchError' => __('Address search is unavailable right now. Please try again in a moment.'),
        ];
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const T = @json($checkoutText);
            const DELIVERY_TYPE = { today: 'same_day', preorder: 'pre_order' };
            let pendingPayment = null;
            let deliveryMethod = 'today';
            let shippingQuote = null;

            function adjustBottomPadding() {
                const bar = document.getElementById('checkoutPaymentBar');
                const page = document.querySelector('.checkout-page');
                if (bar && !bar.hidden) {
                    page.style.paddingBottom = (bar.offsetHeight + 40) + 'px';
                } else {
                    page.style.paddingBottom = '56px';
                }
            }

            function render() {
                const items = DonatBasket.getItems();
                const empty = document.getElementById('checkoutEmpty');
                const content = document.getElementById('checkoutContent');
                const paymentBar = document.getElementById('checkoutPaymentBar');

                if (items.length === 0) {
                    empty.hidden = false;
                    content.hidden = true;
                    paymentBar.hidden = true;
                    adjustBottomPadding();
                    return;
                }

                empty.hidden = true;
                content.hidden = false;
                paymentBar.hidden = false;

                const list = document.getElementById('checkoutItems');
                list.innerHTML = items.map(function (item) {
                    return `
                                    <div class="checkout-item" data-slug="${item.slug}">
                                        <img src="${item.image}" alt="${item.name}">
                                        <div class="checkout-item-info">
                                            <strong>${item.name}</strong>
                                            <span>${DonatBasket.format(item.price)}</span>
                                            <div class="qty-stepper">
                                                <button type="button" class="qty-btn" data-action="dec">−</button>
                                                <span class="checkout-item-qty">${item.qty}</span>
                                                <button type="button" class="qty-btn" data-action="inc">+</button>
                                            </div>
                                        </div>
                                        <div class="checkout-item-side">
                                            <button type="button" class="checkout-item-remove" aria-label="${window.I18N.remove}">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"></path></svg>
                                            </button>
                                            <strong class="checkout-item-total">${DonatBasket.format(item.price * item.qty)}</strong>
                                        </div>
                                    </div>
                                `;
                }).join('');

                const subtotal = DonatBasket.getTotalPrice();
                const shipping = shippingQuote ? shippingQuote.fee : 0;
                const total = subtotal + shipping;

                document.getElementById('checkoutSubtotal').textContent = DonatBasket.format(subtotal);
                document.getElementById('checkoutShippingFee').textContent = shippingQuote ? DonatBasket.format(shipping) : '-';
                document.getElementById('checkoutTotal').textContent = DonatBasket.format(total);
                document.getElementById('checkoutPaymentTotal').textContent = 'IDR ' + total.toLocaleString('id-ID');

                document.getElementById('deliveryToday').classList.toggle('is-selected', deliveryMethod === 'today');
                document.getElementById('deliveryPreorder').classList.toggle('is-selected', deliveryMethod === 'preorder');
                document.getElementById('deliverySchedule').hidden = deliveryMethod !== 'preorder';

                adjustBottomPadding();
            }

            document.getElementById('checkoutItems').addEventListener('click', function (e) {
                const row = e.target.closest('.checkout-item');
                if (!row) return;
                const slug = row.dataset.slug;
                const item = DonatBasket.getItems().find(function (i) { return i.slug === slug; });
                if (!item) return;

                if (e.target.closest('[data-action="inc"]')) {
                    DonatBasket.setQty(slug, item.qty + 1);
                } else if (e.target.closest('[data-action="dec"]')) {
                    DonatBasket.setQty(slug, item.qty - 1);
                } else if (e.target.closest('.checkout-item-remove')) {
                    DonatBasket.removeItem(slug);
                }
            });

            document.querySelectorAll('.delivery-option').forEach(function (option) {
                option.addEventListener('click', function (e) {
                    if (e.target.closest('.delivery-schedule')) return;
                    deliveryMethod = option.dataset.method;
                    pendingPayment = null;
                    render();

                    if (deliveryMethod === 'preorder') {
                        requestAnimationFrame(function () {
                            document.getElementById('deliverySchedule')
                                .scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    }
                });
            });

            const addressInput = document.getElementById('addressSearch');
            const addressList = document.getElementById('addressList');
            const addressStatus = document.getElementById('addressStatus');
            const addressDetail = document.getElementById('addressDetail');
            const latInput = document.getElementById('addrLat');
            const lngInput = document.getElementById('addrLng');
            const areaInput = document.getElementById('addrArea');

            const GEO = {
                base: 'https://api.geoapify.com/v1',
                key: @json(config('shipping.browser_key')),
                bounds: @json(config('shipping.search_bounds')),
                origin: @json(config('shipping.origin')),
            };

            let suggestions = [];
            let activeIndex = -1;
            let debounceTimer = null;
            let suggestAbort = null;
            let quoteRun = 0;
            let reverseRun = 0;
            let lastHouseNo = '';
            let pickedArea = '';
            const suggestCache = new Map();

            function setStatus(text, state) {
                addressStatus.textContent = text;
                addressStatus.dataset.state = state || '';
            }

            function closeList() {
                addressList.hidden = true;
                addressList.innerHTML = '';
                addressInput.setAttribute('aria-expanded', 'false');
                addressInput.removeAttribute('aria-activedescendant');
                activeIndex = -1;
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value;
                return div.innerHTML;
            }

            /* ---------- Peta + pin (Leaflet) ---------- */
            const map = L.map('addressMap', { scrollWheelZoom: false })
                .setView([GEO.origin.lat, GEO.origin.lng], 12);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            // Peta ada di dalam blok yang awalnya hidden, jadi ukurannya harus dihitung ulang saat tampil.
            new ResizeObserver(function () { map.invalidateSize(); }).observe(document.getElementById('addressMap'));

            const pinIcon = L.divIcon({
                className: 'address-pin',
                html: '<svg width="32" height="40" viewBox="0 0 32 40"><path d="M16 0C7.2 0 0 7.1 0 15.9 0 27.4 16 40 16 40s16-12.6 16-24.1C32 7.1 24.8 0 16 0z" fill="#7a1f2b"/><circle cx="16" cy="15.5" r="6" fill="#fff"/></svg>',
                iconSize: [32, 40],
                iconAnchor: [16, 40],
            });

            L.marker([GEO.origin.lat, GEO.origin.lng], {
                interactive: false,
                icon: L.divIcon({ className: 'address-shop', html: '<span>Donat Wawa</span>', iconSize: [0, 0] }),
            }).addTo(map);

            let pin = null;

            function placePin(lat, lng) {
                if (!pin) {
                    pin = L.marker([lat, lng], { draggable: true, icon: pinIcon }).addTo(map);
                    pin.on('dragend', function () {
                        const p = pin.getLatLng();
                        pinMoved(p.lat, p.lng);
                    });
                } else {
                    pin.setLatLng([lat, lng]);
                }
            }

            map.on('click', function (e) {
                closeList();
                placePin(e.latlng.lat, e.latlng.lng);
                pinMoved(e.latlng.lat, e.latlng.lng);
            });

            /* ---------- Autocomplete (langsung ke Geoapify dari browser) ---------- */

            // "Jalan Pahlawan No. 339, Balearjosari" -> cari "Jalan Pahlawan, Balearjosari",
            // nomor rumahnya (339) dipisah dan dimasukkan ke kolom Detail Alamat.
            function parseQuery(raw) {
                let houseNo = '';
                let text = raw.replace(/\b(?:nomor|nmr|no)\.?\s*([0-9]+[a-z]?(?:\s*[\/-]\s*[0-9a-z]+)?)/i, function (m, n) {
                    houseNo = n.replace(/\s+/g, '');
                    return ' ';
                });
                text = text.replace(/\brt\.?\s*\d+(\s*\/?\s*rw\.?\s*\d+)?/ig, ' ').replace(/\brw\.?\s*\d+/ig, ' ');
                text = text.replace(/\s+/g, ' ').replace(/\s*,\s*(,\s*)*/g, ', ').replace(/^[,\s]+|[,\s]+$/g, '');
                return { text: text, houseNo: houseNo };
            }

            function showList() {
                if (addressList.querySelector('.address-attribution') === null) {
                    addressList.insertAdjacentHTML('beforeend', '<li class="address-attribution" aria-hidden="true">Powered by Geoapify &middot; &copy; OpenStreetMap contributors</li>');
                }
                addressList.hidden = false;
                addressInput.setAttribute('aria-expanded', 'true');
            }

            function renderLoading() {
                addressList.innerHTML = '<li class="address-empty">' + escapeHtml(T.searching) + '</li>';
                showList();
            }

            function renderSuggestions() {
                let html = '';

                if (suggestions.length === 0) {
                    html += '<li class="address-empty">' + escapeHtml(T.noResults) + '</li>';
                } else {
                    html += suggestions.map(function (s, i) {
                        return '<li class="address-option' + (i === activeIndex ? ' is-active' : '') + '" role="option" id="addressOption' + i + '" data-index="' + i + '">'
                            + '<strong>' + escapeHtml(s.main) + '</strong>'
                            + (s.secondary ? '<span>' + escapeHtml(s.secondary) + '</span>' : '')
                            + '</li>';
                    }).join('');
                }

                html += '<li class="address-option address-option-map" data-map="1"><strong>' + escapeHtml(T.markOnMap) + '</strong></li>';

                addressList.innerHTML = html;
                showList();
            }

            function fetchSuggestions(query) {
                const cacheKey = query.toLowerCase();

                if (suggestCache.has(cacheKey)) {
                    suggestions = suggestCache.get(cacheKey);
                    activeIndex = -1;
                    renderSuggestions();
                    return;
                }

                if (suggestAbort) { suggestAbort.abort(); }
                const controller = new AbortController();
                suggestAbort = controller;
                const timer = setTimeout(function () { controller.abort(); }, 6000);

                const b = GEO.bounds;
                const params = new URLSearchParams({
                    text: query,
                    lang: 'id',
                    limit: '6',
                    format: 'json',
                    filter: 'rect:' + [b.low.lng, b.low.lat, b.high.lng, b.high.lat].join(','),
                    bias: 'proximity:' + GEO.origin.lng + ',' + GEO.origin.lat,
                    apiKey: GEO.key,
                });

                renderLoading();

                fetch(GEO.base + '/geocode/autocomplete?' + params.toString(), { signal: controller.signal })
                    .then(function (res) {
                        if (!res.ok) { throw new Error(T.searchError); }
                        return res.json();
                    })
                    .then(function (body) {
                        const seen = new Set();

                        suggestions = (body.results || []).filter(function (r) {
                            if (r.lat == null || r.lon == null || seen.has(r.place_id)) { return false; }
                            seen.add(r.place_id);
                            return true;
                        }).map(function (r) {
                            return {
                                main: r.address_line1 || r.formatted || '',
                                secondary: r.address_line2 || '',
                                area: r.formatted || '',
                                lat: r.lat,
                                lng: r.lon,
                            };
                        });

                        suggestCache.set(cacheKey, suggestions);
                        activeIndex = -1;
                        renderSuggestions();
                    })
                    .catch(function () {
                        if (suggestAbort !== controller) { return; }
                        closeList();
                        setStatus(T.searchError, 'error');
                    })
                    .finally(function () { clearTimeout(timer); });
            }

            /* ---------- Ongkir ---------- */
            function resetQuote() {
                if (!shippingQuote && !latInput.value) { return; }
                shippingQuote = null;
                latInput.value = '';
                lngInput.value = '';
                quoteRun++;
                pendingPayment = null;
                render();
            }

            function requestQuote(lat, lng) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                addressInput.setCustomValidity('');
                shippingQuote = null;
                pendingPayment = null;
                render();
                setStatus(T.calculating, 'loading');

                const run = ++quoteRun;

                postJson('{{ route('checkout.shipping-quote') }}', { lat: lat, lng: lng })
                    .then(function (quote) {
                        if (run !== quoteRun) { return; }
                        shippingQuote = quote;
                        pendingPayment = null;
                        setStatus(quote.distance_km.toFixed(1) + ' ' + T.km + ' - ' + T.zone + ' ' + quote.zone, 'ok');
                        render();
                    })
                    .catch(function (err) {
                        if (run !== quoteRun) { return; }
                        shippingQuote = null;
                        setStatus(err.message || T.searchError, 'error');
                        render();
                    });
            }

            function setArea(text) {
                pickedArea = text;
                areaInput.value = text;
            }

            // Pin digeser / peta diketuk / "lokasiku": hitung ongkir langsung, lalu cari nama areanya.
            function pinMoved(lat, lng) {
                const fallback = T.pinned + ' (' + lat.toFixed(5) + ', ' + lng.toFixed(5) + ')';
                setArea(fallback);
                requestQuote(lat, lng);

                const run = ++reverseRun;
                const params = new URLSearchParams({ lat: lat, lon: lng, lang: 'id', format: 'json', apiKey: GEO.key });

                fetch(GEO.base + '/geocode/reverse?' + params.toString())
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (body) {
                        if (run !== reverseRun || !body || !body.results || !body.results[0]) { return; }
                        const r = body.results[0];
                        const text = r.formatted || [r.address_line1, r.address_line2].filter(Boolean).join(', ');
                        if (!text) { return; }
                        setArea(text);
                        addressInput.value = text;
                    })
                    .catch(function () { /* nama area hanya pelengkap, ongkir tetap jalan */ });
            }

            function chooseSuggestion(index) {
                const picked = suggestions[index];
                if (!picked) { return; }

                addressInput.value = picked.main + (picked.secondary ? ', ' + picked.secondary : '');
                setArea(picked.area || addressInput.value);
                reverseRun++;

                if (lastHouseNo && !addressDetail.value.trim()) {
                    addressDetail.value = 'No. ' + lastHouseNo;
                }

                closeList();
                placePin(picked.lat, picked.lng);
                map.setView([picked.lat, picked.lng], 17);
                requestQuote(picked.lat, picked.lng);
            }

            function fullAddress() {
                const detail = addressDetail.value.trim();
                return detail ? detail + ', ' + pickedArea : pickedArea;
            }

            document.getElementById('addressLocate').addEventListener('click', function () {
                if (!navigator.geolocation) { setStatus(T.locationFailed, 'error'); return; }
                setStatus(T.locating, 'loading');

                navigator.geolocation.getCurrentPosition(function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.setView([lat, lng], 17);
                    placePin(lat, lng);
                    pinMoved(lat, lng);
                }, function () {
                    setStatus(T.locationFailed, 'error');
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            addressInput.addEventListener('input', function () {
                resetQuote();
                addressInput.setCustomValidity('');

                const parsed = parseQuery(addressInput.value.trim());
                clearTimeout(debounceTimer);

                if (parsed.text.length < 3) {
                    if (suggestAbort) { suggestAbort.abort(); suggestAbort = null; }
                    closeList();
                    setStatus(T.choose, '');
                    return;
                }

                lastHouseNo = parsed.houseNo;
                setStatus(T.choose, '');
                debounceTimer = setTimeout(function () { fetchSuggestions(parsed.text); }, 250);
            });

            addressInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && (addressList.hidden || suggestions.length === 0 || activeIndex < 0)) {
                    e.preventDefault();
                    return;
                }

                if (addressList.hidden || suggestions.length === 0) { return; }

                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    const step = e.key === 'ArrowDown' ? 1 : -1;
                    activeIndex = (activeIndex + step + suggestions.length) % suggestions.length;
                    renderSuggestions();
                    addressInput.setAttribute('aria-activedescendant', 'addressOption' + activeIndex);
                } else if (e.key === 'Enter' && activeIndex >= 0) {
                    e.preventDefault();
                    chooseSuggestion(activeIndex);
                } else if (e.key === 'Escape') {
                    closeList();
                }
            });

            addressList.addEventListener('mousedown', function (e) {
                const option = e.target.closest('.address-option');
                if (!option) { return; }
                e.preventDefault();

                if (option.dataset.map) {
                    closeList();
                    setStatus(T.choose, '');
                    document.getElementById('addressMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                chooseSuggestion(Number(option.dataset.index));
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('#addressCombo')) { closeList(); }
            });

            function postJson(url, payload) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (body) {
                        if (!res.ok) {
                            const first = body.errors ? Object.values(body.errors)[0] : null;
                            const error = new Error((first && first[0]) || body.message || window.I18N.somethingWrong);
                            error.status = res.status;
                            throw error;
                        }
                        return body;
                    });
                });
            }

            function createPayment(form, formData, items) {
                const payload = {
                    name: formData.get('name'),
                    phone: formData.get('phone'),
                    lat: formData.get('lat'),
                    lng: formData.get('lng'),
                    address_area: formData.get('address_area'),
                    address_detail: formData.get('address_detail'),
                    delivery_type: DELIVERY_TYPE[deliveryMethod],
                    items: items.map(function (item) { return { slug: item.slug, qty: item.qty }; }),
                };

                if (deliveryMethod === 'preorder') {
                    payload.delivery_date = formData.get('delivery_date');
                    payload.delivery_slot = formData.get('delivery_time');
                }

                return postJson('{{ route('checkout.store') }}', payload).then(function (order) {
                    return postJson('{{ route('checkout.snap-token') }}', { order_code: order.order_code })
                        .then(function (data) {
                            return { order: order, snapToken: data.snap_token };
                        });
                });
            }

            document.getElementById('checkoutForm').addEventListener('input', function () {
                pendingPayment = null;
            });
            window.addEventListener('basket:updated', function () {
                pendingPayment = null;
            });

            document.getElementById('checkoutPayBtn').addEventListener('click', function () {
                const form = document.getElementById('checkoutForm');

                const dateInput = form.querySelector('[name="delivery_date"]');
                dateInput.required = (deliveryMethod === 'preorder');

                if (!shippingQuote) {
                    addressInput.setCustomValidity(T.pickAddress);
                } else {
                    addressInput.setCustomValidity('');
                }

                if (!form.reportValidity()) {
                    return;
                }

                const items = DonatBasket.getItems();
                const formData = new FormData(form);

                const payBtn = document.getElementById('checkoutPayBtn');
                payBtn.disabled = true;
                payBtn.textContent = window.I18N.processing;

                function resetButton() {
                    payBtn.disabled = false;
                    payBtn.textContent = window.I18N.proceed;
                }

                const ready = pendingPayment
                    ? Promise.resolve(pendingPayment)
                    : createPayment(form, formData, items);

                ready
                    .then(function (payment) {
                        pendingPayment = payment;

                        const now = new Date();
                        const orderPayload = {
                            code: payment.order.order_code,
                            placedAtLabel: now.toLocaleDateString(window.I18N.locale, {
                                day: 'numeric', month: 'long', year: 'numeric',
                            }) + ', ' + now.toLocaleTimeString(window.I18N.locale, {
                                hour: '2-digit', minute: '2-digit',
                            }) + ' WIB',
                            items: items,
                            subtotal: payment.order.subtotal,
                            shippingFee: payment.order.shipping_fee,
                            total: payment.order.total,
                            name: formData.get('name'),
                            email: formData.get('email'),
                            phone: formData.get('phone'),
                            address: fullAddress(formData),
                            deliveryMethod: deliveryMethod,
                            deliveryDate: formData.get('delivery_date'),
                            deliveryTime: formData.get('delivery_time'),
                        };

                        function wait(ms) {
                            return new Promise(function (resolve) { setTimeout(resolve, ms); });
                        }

                        function verifyPaid(claimedSuccess, attempt) {
                            return postJson('{{ url('/checkout/orders') }}/' + encodeURIComponent(orderPayload.code) + '/sync', {})
                                .then(function (state) {
                                    if (state.paid || !claimedSuccess || attempt >= 2) { return state; }
                                    return wait(2000).then(function () { return verifyPaid(claimedSuccess, attempt + 1); });
                                });
                        }

                        function finish(status, result) {
                            verifyPaid(status === 'success', 0)
                                .then(function (state) {
                                    if (!state.paid) {
                                        alert(window.I18N.paymentNotReceived);
                                        resetButton();
                                        return;
                                    }

                                    orderPayload.status = 'success';
                                    orderPayload.paymentResult = result;
                                    sessionStorage.setItem('donat_last_order', JSON.stringify(orderPayload));
                                    window.location.href = '{{ route('checkout.success') }}';
                                })
                                .catch(function () {
                                    alert(window.I18N.paymentNotReceived);
                                    resetButton();
                                });
                        }

                        window.snap.pay(payment.snapToken, {
                            onSuccess: function (result) { finish('success', result); },
                            onPending: function (result) { finish('pending', result); },
                            onError: function () {
                                alert(window.I18N.paymentFailed);
                                resetButton();
                            },
                            onClose: function () {
                                resetButton();
                            },
                        });
                    })
                    .catch(function (err) {
                        pendingPayment = null;
                        alert(err.message || window.I18N.somethingWrong);
                        resetButton();
                    });
            });

            function syncBasketWithDatabase() {
                const items = DonatBasket.getItems();
                if (items.length === 0) {
                    render();
                    return;
                }

                const slugs = items.map(function (item) { return item.slug; });
                const query = slugs.map(function (s) { return 'slugs[]=' + encodeURIComponent(s); }).join('&');

                fetch('{{ route('checkout.sync-basket') }}?' + query)
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        const fresh = data.items || {};

                        const synced = items
                            .filter(function (item) { return fresh[item.slug] && fresh[item.slug].available; })
                            .map(function (item) {
                                const dbItem = fresh[item.slug];
                                return {
                                    slug: item.slug,
                                    name: dbItem.name,
                                    price: Number(dbItem.price),
                                    image: dbItem.image,
                                    qty: item.qty,
                                };
                            });

                        DonatBasket.replaceAll(synced);
                    })
                    .catch(function () { })
                    .finally(render);
            }

            window.addEventListener('basket:updated', render);
            syncBasketWithDatabase();
        });
    </script>
@endpush