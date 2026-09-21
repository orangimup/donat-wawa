@php
    $status = $item['status'] ?? 'available';
    $isSoldOut = $status === 'unavailable';
    $badgeLabels = [
        'best_seller' => __('Best Seller'),
        'special' => __('Special'),
        'new' => __('New Menu'),
    ];
    $badgeKey = $item['badge'] ?? null;
    $badgeLabel = $badgeLabels[$badgeKey] ?? null;
@endphp

<article class="menu-card {{ $isSoldOut ? 'is-soldout' : '' }}" data-category="{{ $item['category'] ?? 'all' }}">
    <a href="{{ route('product.show', $item['slug']) }}" class="menu-card-link">
        <div class="menu-card-media">
            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy">

            @if($badgeLabel)
                <span class="menu-badge">{{ $badgeLabel }}</span>
            @endif

            @if($isSoldOut)
                <span class="menu-soldout-pill">{{ __('Sold Out') }}</span>
            @endif
        </div>

        <div class="menu-card-body">
            <h3>{{ $item['name'] }}</h3>
            <p>{{ $item['description'] }}</p>
        </div>
    </a>

    <div class="menu-card-footer">
        @if($isSoldOut)
            <button type="button" class="btn-restock" disabled>{{ __('Restocking Soon') }}</button>
        @else
            <span class="price">{{ $item['price_formatted'] }}</span>

            <div class="basket-qty-widget" data-slug="{{ $item['slug'] }}" data-name="{{ $item['name'] }}"
                data-price="{{ $item['price'] }}" data-image="{{ $item['image'] }}">

                <button type="button" class="basket-decrement-btn" aria-label="{{ __('Decrease :name', ['name' => $item['name']]) }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                    </svg>
                </button>
                <span class="basket-qty-value">0</span>

                <button type="button" class="plus-btn basket-add-btn" aria-label="{{ __('Increase :name', ['name' => $item['name']]) }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                </button>
            </div>
        @endif
    </div>
</article>