@extends('layouts.app')

@section('title', 'Donat Wawa - ' . __('Beri Ulasan'))
@section('body-class', 'settings-page')
@section('hide-footer')
@endsection

@push('styles')
    @vite('resources/css/order-review.css')
@endpush

@section('content')

    <div class="container-ww order-review-layout">
        <div class="order-review-card-outer">
            <div class="order-review-head">
                <h1>{{ __('Rate Your Order') }}</h1>
                <p>{{ __('Share your sweet experience enjoying our artisanal donuts to help us keep serving the best warmth of flavor every day.') }}
                </p>
            </div>

            <div class="order-review-card">
                <form method="POST" action="{{ route('settings.orders.review.store', $order['id']) }}" id="review-form">
                    @csrf

                    @if ($errors->any())
                        <p class="order-review-error" role="alert">{{ $errors->first() }}</p>
                    @endif

                    @foreach ($reviewItems as $item)
                        <div class="order-review-item" data-review-item>
                            <div class="order-review-product">
                                <img src="{{ $item['image'] }}" alt="" width="56" height="56">
                                <strong>{{ $item['name'] }}</strong>
                            </div>

                            <div class="order-review-section">
                                <p>{{ __("How was your experience with Donat Wawa's warm artisanal donuts this time?") }}</p>

                                <div class="order-review-rating-box">
                                    <div class="order-review-stars" data-stars>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <button type="button" class="order-review-star" data-value="{{ $i }}"
                                                aria-label="{{ $i }} star">
                                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <path
                                                        d="M12 2.5l2.98 6.04 6.67.97-4.83 4.7 1.14 6.65L12 17.9l-5.96 3.13 1.14-6.65-4.83-4.7 6.67-.97L12 2.5z" />
                                                </svg>
                                            </button>
                                        @endfor
                                    </div>
                                    <p class="order-review-rating-label" data-label></p>
                                    {{-- Empty = this product is skipped. --}}
                                    <input type="hidden" name="reviews[{{ $item['id'] }}][rating]" data-rating-input
                                        value="{{ old('reviews.' . $item['id'] . '.rating') }}">
                                </div>
                            </div>

                            <div class="order-review-section">
                                <div class="order-review-comment-box">
                                    <textarea name="reviews[{{ $item['id'] }}][comment]" rows="3" maxlength="1000"
                                        placeholder="{{ __('Write your review here...') }}">{{ old('reviews.' . $item['id'] . '.comment') }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="order-review-actions">
                        <a href="{{ route('settings.orders.show', $order['id']) }}"
                            class="order-review-cancel">{{ __('Cancel') }}</a>
                        <button type="submit" class="order-review-submit">{{ __('Submit Review') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const labels = @json($reviewLabels);

            document.querySelectorAll('[data-review-item]').forEach((item) => {
                const stars = item.querySelectorAll('[data-stars] .order-review-star');
                const label = item.querySelector('[data-label]');
                const input = item.querySelector('[data-rating-input]');

                function paint(value) {
                    stars.forEach((star) => {
                        star.classList.toggle('is-filled', Number(star.dataset.value) <= value);
                    });
                }

                function select(value) {
                    input.value = value || '';
                    label.textContent = labels[value] ?? '';
                    paint(value);
                }

                stars.forEach((star) => {
                    const value = Number(star.dataset.value);
                    star.addEventListener('mouseenter', () => paint(value));
                    star.addEventListener('mouseleave', () => paint(Number(input.value) || 0));
                    star.addEventListener('click', () => select(value));
                });

                select(Number(input.value) || 0);
            });
        })();
    </script>
@endpush