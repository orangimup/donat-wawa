@extends('layouts.app')

@section('title', 'Donat Wawa - About')

@push('styles')
    @vite('resources/css/about.css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')

    <section class="about-hero">
        <div class="container-ww about-hero-grid">
            <div class="about-hero-text">
                <span class="about-badge">{{ __('Official Store') }}</span>
                <h1>{{ __('Visit Our Store') }}</h1>
                <p>{{ __('Enjoy the warmth of our authentic potato donuts straight from the kitchen. A cozy place to share laughter and sweetness with the ones you love.') }}</p>
            </div>
            <div class="about-hero-image">
                <img src="{{ asset('assets/images/about-store.png') }}" alt="{{ __('Donat Wawa Store') }}">
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container-ww about-location-grid">
            <div class="location-card">
                <h2>{{ __('Location Details') }}</h2>

                <div class="location-item">
                    <span class="location-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </span>
                    <div>
                        <p class="location-label">{{ __('Full Address') }}</p>
                        <p class="location-value">Jl. Pahlawan No. 339, Balearjosari, Malang</p>
                    </div>
                </div>

                <div class="location-item">
                    <span class="location-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 7v5l3 3"></path>
                        </svg>
                    </span>
                    <div>
                        <p class="location-label">{{ __('Operating Hours') }}</p>
                        <p class="location-value">{{ __('Open Daily: 14.00 - 21.00 WIB') }}</p>
                    </div>
                </div>

                <div class="location-actions">
                    <a href="#" class="btn-contact btn-contact-solid">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.4 8.4 0 0 1-4-1L3 20l1-4.5a8.4 8.4 0 0 1-1-4A8.5 8.5 0 0 1 11.5 3h.5a8.5 8.5 0 0 1 9 8.5Z">
                            </path>
                        </svg>
                        {{ __('Contact CS') }}
                    </a>
                </div>
            </div>

            <div class="location-map">
                <div id="store-map" data-name="Donat Wawa Store"
                    data-address="Jl. Pahlawan No. 339, Balearjosari, Kec. Blimbing, Kota Malang, Jawa Timur 65126"
                    data-lat="-7.926559324446892" data-lng="112.65646351745289">
                </div>
            </div>
        </div>
    </section>

    <section class="section about-gallery-section">
        <div class="container-ww">
            <div class="section-heading">
                <h2>{{ __("Customer's Favorite Corner") }}</h2>
                <p>{{ __('Discover the comfort in every corner of our store.') }}</p>
            </div>
            <div class="about-gallery-grid">
                @foreach($gallery as $photo)
                    <div class="about-gallery-item">
                        <img src="{{ $photo }}" alt="{{ __('Donat Wawa store corner') }}">
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mapEl = document.getElementById('store-map');
            if (!mapEl) return;

            const storeName = mapEl.dataset.name || 'Donat Wawa Store';
            const address = mapEl.dataset.address || '';
            const fallbackLat = parseFloat(mapEl.dataset.lat) || -7.926559324446892;
            const fallbackLng = parseFloat(mapEl.dataset.lng) || 112.65646351745289;

            const map = L.map('store-map', { scrollWheelZoom: false })
                .setView([fallbackLat, fallbackLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            function addMarker(lat, lng) {
                const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;

                const popupHtml = `
                <div class="store-popup">
                    <div class="store-popup-header">
                        <strong>${storeName}</strong>
                        <div class="store-popup-actions">
                            <a href="${directionsUrl}" target="_blank" rel="noopener" title="${window.I18N.directions}">➜</a>
                        </div>
                    </div>
                    <p class="store-popup-address">${address}</p>
                </div>
            `;

                L.marker([lat, lng]).addTo(map)
                    .bindPopup(popupHtml, {
                        closeButton: false,
                        minWidth: 300,
                        maxWidth: 320,
                    })
                    .openPopup();

                setTimeout(function () {
                    map.panBy([0, -50]);
                }, 200);
            }
            if (address) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(address)}`)
                    .then((res) => res.json())
                    .then((data) => {
                        if (data && data[0]) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            map.setView([lat, lng], 16);
                            addMarker(lat, lng);
                        } else {
                            addMarker(fallbackLat, fallbackLng);
                        }
                    })
                    .catch(() => addMarker(fallbackLat, fallbackLng));
            } else {
                addMarker(fallbackLat, fallbackLng);
            }
        });
    </script>
@endpush