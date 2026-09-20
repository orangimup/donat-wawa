@php
    $value = max(0, min(5, (float) ($value ?? 0)));
    $size = $size ?? 18;
    $points = '12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26';
    $rounded = floor($value * 2) / 2;
@endphp
<span class="review-stars" role="img" aria-label="{{ number_format($value, 1) }} out of 5 stars">
    @for ($i = 1; $i <= 5; $i++)
        @php
            $fill = $rounded >= $i ? 'full' : ($rounded >= $i - 0.5 ? 'half' : 'empty');
        @endphp
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"
            stroke-linejoin="round" aria-hidden="true">
            @if ($fill === 'full')
                <polygon points="{{ $points }}" fill="currentColor"></polygon>
            @elseif ($fill === 'half')
                <polygon points="{{ $points }}" fill="none"></polygon>
                <polygon points="{{ $points }}" fill="currentColor" stroke="none" clip-path="url(#reviewStarHalf)"></polygon>
            @else
                <polygon points="{{ $points }}" fill="none" opacity="0.3"></polygon>
            @endif
        </svg>
    @endfor
</span>