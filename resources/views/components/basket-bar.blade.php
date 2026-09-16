{{-- Basket sementara: ngambang (floating card) di bawah viewport, cuma
     muncul kalau ada item. Sengaja gak full-width biar keliatan ngambang,
     bukan nempel kayak bar biasa. --}}
<div class="basket-floating-bar" id="basketFloatingBar" hidden>
    <div class="basket-floating-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
            <path d="M3 6h18"></path>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
        <span class="basket-floating-badge" id="basketFloatingBadge">0</span>
    </div>

    <div class="basket-floating-info">
        <strong id="basketFloatingCount">0 Item</strong>
        <span id="basketFloatingTotal">Rp0</span>
    </div>

    <a href="{{ url('/checkout') }}" class="basket-floating-cta">
        Check out
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"></path></svg>
    </a>
</div>