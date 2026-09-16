<article class="bundle-card">
    <div class="bundle-card-media">
        @if(!empty($bundle['badge']))
            <span class="badge">{{ $bundle['badge'] }}</span>
        @endif
        <img src="{{ $bundle['image'] }}" alt="{{ $bundle['name'] }}">
    </div>
    <div class="bundle-card-body">
        <h3>{{ $bundle['name'] }}</h3>
        <p>{{ $bundle['description'] }}</p>
        <div class="bundle-card-footer">
            <span class="price">{{ $bundle['price_formatted'] }}</span>
            <a href="{{ url('/order') }}?product={{ urlencode($bundle['name']) }}" class="btn btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                Order Now
            </a>
        </div>
    </div>
</article>