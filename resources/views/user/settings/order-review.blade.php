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

                    <div class="order-review-section">
                        <h2><span class="order-review-step">1</span>{{ __('Overall Satisfaction') }}</h2>
                        <p>{{ __("How was your experience with Donat Wawa's warm artisanal donuts this time?") }}</p>

                        <div class="order-review-rating-box">
                            <div class="order-review-stars" id="review-stars">
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
                            <p class="order-review-rating-label" id="review-rating-label">{{ $reviewLabels[5] }}</p>
                            <input type="hidden" name="rating" id="review-rating-input" value="5">
                        </div>
                    </div>

                    <div class="order-review-section">
                        <h2><span class="order-review-step">2</span>{{ __('Detailed Review') }}</h2>
                        <p>{{ __('Tell us the details of your favorite donut flavor, the texture of the potato dough, or how friendly our service was.') }}
                        </p>

                        <div class="order-review-comment-box">
                            <textarea name="comment" id="review-comment" rows="4"
                                placeholder="{{ __('Write your review here...') }}"></textarea>
                        </div>
                    </div>

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
            const stars = document.querySelectorAll('#review-stars .order-review-star');
            const label = document.getElementById('review-rating-label');
            const input = document.getElementById('review-rating-input');

            function paint(value) {
                stars.forEach((star) => {
                    star.classList.toggle('is-filled', Number(star.dataset.value) <= value);
                });
            }

            function select(value) {
                input.value = value;
                label.textContent = labels[value] ?? '';
                paint(value);
            }

            stars.forEach((star) => {
                const value = Number(star.dataset.value);
                star.addEventListener('mouseenter', () => paint(value));
                star.addEventListener('mouseleave', () => paint(Number(input.value)));
                star.addEventListener('click', () => select(value));
            });

            select(5);
        })();
    </script>
@endpush