@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
{{-- Toolbar tetap di atas; hanya grid menu yang scroll (sticky CSS gagal karena overflow-hidden di layout) --}}
<div id="pos-page" class="absolute inset-0 flex flex-col min-h-0">

    <div id="pos-sticky-bar"
         class="flex-shrink-0 z-30 px-4 md:px-6 pt-3 pb-3 border-b border-stone-200/90 shadow-sm"
         style="background-color: var(--color-bg, #F5F5F4);">
        <div class="relative mb-3 max-w-xl">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#78716C] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
            </svg>
            <input id="search-input" type="text" placeholder="Search menu items..."
                   class="w-full pl-9 pr-4 py-2.5 text-sm bg-white rounded-2xl border border-stone-200 focus:ring-2 focus:ring-primary/30 outline-none shadow-sm"
                   value="{{ request('search') }}">
        </div>

        <div class="flex gap-4 md:gap-6 border-b border-stone-200 overflow-x-auto scrollbar-hide -mb-px">
            <button type="button"
                data-category="all"
                onclick="posModule.switchCategory('all')"
                class="category-tab pb-3 text-sm font-semibold tracking-wider transition-colors relative whitespace-nowrap flex-shrink-0
                    {{ $category === 'all' || !$category
                        ? 'text-[#1C1917] after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-primary'
                        : 'text-[#78716C] hover:text-[#1C1917]' }}">
                ALL
            </button>
            @foreach($categories as $cat)
            <button type="button"
                data-category="{{ $cat->slug }}"
                onclick="posModule.switchCategory('{{ $cat->slug }}')"
                class="category-tab pb-3 text-sm font-semibold tracking-wider transition-colors relative whitespace-nowrap flex-shrink-0
                    {{ $category === $cat->slug
                        ? 'text-[#1C1917] after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-primary'
                        : 'text-[#78716C] hover:text-[#1C1917]' }}">
                {{ strtoupper($cat->name) }}
            </button>
            @endforeach
        </div>
    </div>

    <div id="pos-scroll-area" class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-4 md:px-6 pt-4 pb-24 md:pb-6">

        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-[#1C1917]">Menu Items</h2>
                <span class="text-sm text-[#78716C] font-medium">(<span id="product-count">{{ $products->count() }}</span> Items)</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4" id="product-grid">
            @forelse($products as $product)
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm transition-all
                        {{ $product->stock > 0 ? 'hover:shadow-md cursor-pointer group' : 'opacity-50 cursor-not-allowed' }}"
                data-product-id="{{ $product->id }}"
                data-product-name="{{ $product->name }}"
                data-product-price="{{ $product->price }}"
                data-product-image="{{ $product->image_url }}"
                data-product-stock="{{ $product->stock }}"
                {{ $product->stock > 0 ? 'onclick=cartModule.addToCart(this)' : '' }}>
                <div class="aspect-square overflow-hidden bg-stone-100 relative">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                        class="w-full h-full object-cover {{ $product->stock > 0 ? 'group-hover:scale-105 transition-transform duration-300' : '' }}">
                    @if($product->stock <= 0)
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">Habis</span>
                    </div>
                    @endif
                </div>
                <div class="p-3">
                    <h3 class="text-sm font-semibold text-[#1C1917] truncate">{{ $product->name }}</h3>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-sm font-bold text-primary">{{ $product->formatted_price }}</span>
                        @if($product->stock > 0)
                        <button class="w-6 h-6 flex items-center justify-center text-stone-400 hover:text-primary transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v8M8 12h8"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-16 text-[#78716C]">
                <svg class="w-12 h-12 mx-auto mb-3 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p>No products found.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===================== ORDER PANEL ===================== --}}
{{-- Desktop: floating draggable | Mobile: bottom sheet --}}
<div id="order-panel"
    class="hidden fixed bg-white shadow-xl z-30 overflow-hidden select-none flex flex-col
            md:rounded-2xl md:w-80 md:bottom-6 md:left-6 md:right-auto
            bottom-0 left-0 right-0 rounded-t-3xl">

    {{-- Drag handle (mobile) --}}
    <div class="flex justify-center pt-3 md:hidden flex-shrink-0">
        <div class="w-10 h-1 bg-stone-200 rounded-full"></div>
    </div>

    {{-- Header --}}
    <div id="order-panel-handle"
        class="flex items-center justify-between px-4 pt-3 pb-2 cursor-grab active:cursor-grabbing flex-shrink-0">
        <div class="flex items-center gap-2">
            <h3 class="font-bold text-[#1C1917] text-base">Your Order</h3>
            <span id="panel-item-count" class="text-xs font-bold bg-primary text-white px-2 py-0.5 rounded-full">0 ITEMS</span>
        </div>
        <button id="panel-close-btn" class="w-6 h-6 flex items-center justify-center text-stone-400 hover:text-red-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Items --}}
    <div id="order-items-list" class="px-4 py-2 space-y-3 overflow-y-auto max-h-[50vh] md:max-h-[220px]"></div>

    {{-- Footer --}}
    <div class="px-4 pt-3 pb-6 md:pb-4 border-t border-stone-100 mt-2 flex-shrink-0">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-[#1C1917]">Total</span>
            <span id="panel-total" class="text-base font-bold text-primary">Rp0</span>
        </div>
        <button id="checkout-btn" onclick="checkoutModule.openCheckout()"
                class="w-full bg-primary hover:bg-[#EA580C] text-white font-semibold py-3 rounded-2xl transition-colors flex items-center justify-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5h15M17 21a1 1 0 100-2 1 1 0 000 2zM7 21a1 1 0 100-2 1 1 0 000 2z"/>
            </svg>
            Checkout
        </button>
    </div>
</div>

{{-- Mobile Cart FAB --}}
<button id="mobile-cart-fab"
        onclick="document.getElementById('order-panel').classList.remove('hidden'); this.classList.add('hidden');"
        class="hidden fixed bottom-6 right-6 z-20 w-14 h-14 bg-primary rounded-full shadow-lg shadow-orange-300 items-center justify-center md:hidden">
    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5h15M17 21a1 1 0 100-2 1 1 0 000 2zM7 21a1 1 0 100-2 1 1 0 000 2z"/>
    </svg>
    <span id="fab-badge" class="hidden absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-white text-[9px] font-bold items-center justify-center">0</span>
</button>

{{-- Overlay for mobile panel --}}
<div id="panel-overlay" class="hidden fixed inset-0 bg-black/30 z-20 md:hidden"
    onclick="document.getElementById('order-panel').classList.add('hidden'); document.getElementById('panel-overlay').classList.add('hidden'); if(parseInt(document.getElementById('panel-item-count').textContent)>0){document.getElementById('mobile-cart-fab').classList.remove('hidden');document.getElementById('mobile-cart-fab').classList.add('flex');}"></div>

{{-- Checkout Modal --}}
@include('pos.partials.checkout-modal')

@endsection

@push('scripts')
<script src="{{ asset('js/modules/posModule.js') }}"></script>
<script src="{{ asset('js/modules/checkoutModule.js') }}"></script>
<script>
window.initPosPage = function () {
    const panel = document.getElementById('order-panel');
    if (!panel) return;

    const overlay = document.getElementById('panel-overlay');
    const fab = document.getElementById('mobile-cart-fab');
    const fabBadge = document.getElementById('fab-badge');

    if (!window._posAddToCartHooked) {
        window._posAddToCartHooked = true;
        const origAddToCart = cartModule.addToCart.bind(cartModule);
        cartModule.addToCart = function(el) {
            origAddToCart(el);
            if (window.innerWidth < 768) {
                panel.classList.remove('hidden');
                overlay?.classList.remove('hidden');
                fab?.classList.add('hidden');
            }
        };
    }

    if (!window._posFabObserver && fabBadge) {
        window._posFabObserver = true;
        const observer = new MutationObserver(() => {
            const countEl = document.getElementById('panel-item-count');
            const count = countEl ? parseInt(countEl.textContent) : 0;
            if (fabBadge) {
                fabBadge.textContent = count;
                if (count > 0) {
                    fabBadge.classList.remove('hidden');
                    fabBadge.classList.add('flex');
                } else {
                    fabBadge.classList.add('hidden');
                    fab?.classList.add('hidden');
                }
            }
        });
        const itemCount = document.getElementById('panel-item-count');
        if (itemCount) observer.observe(itemCount, { childList: true, characterData: true, subtree: true });
    }
};

window.initPosPage();
document.addEventListener('DOMContentLoaded', window.initPosPage);
</script>
@endpush