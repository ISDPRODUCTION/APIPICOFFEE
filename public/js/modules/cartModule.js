/**
 * cartModule.js
 * Handles all cart UI rendering + stock validation.
 */

const cartModule = (() => {

    let _panelManuallyHidden = false;

    // ── Helper: ambil stok dari DOM product card ───────────────────────────────
    function _getStockFromDOM(productId) {
        const el = document.querySelector(`[data-product-id="${productId}"]`);
        return el ? parseInt(el.dataset.productStock) : 9999;
    }

    // ── Add item from DOM ──────────────────────────────────────────────────────
    function addToCart(productEl) {
        const productId = parseInt(productEl.dataset.productId);
        const stock     = parseInt(productEl.dataset.productStock) || 0;

        // Cek stok vs qty di cart saat ini
        const state    = cartStore.getState();
        const existing = state.items.find(i => i.product_id === productId);
        const currentQty = existing ? existing.quantity : 0;

        if (currentQty >= stock) {
            _showStockToast(productEl.dataset.productName, stock);
            return;
        }

        const product = {
            product_id: productId,
            name:       productEl.dataset.productName,
            price:      parseInt(productEl.dataset.productPrice),
            image:      productEl.dataset.productImage,
            stock:      stock,
        };

        cartStore.addItem(product);

        // On mobile: show panel + overlay when item added
        if (window.innerWidth < 768) {
            _panelManuallyHidden = false;
            const panel   = document.getElementById('order-panel');
            const overlay = document.getElementById('panel-overlay');
            const fab     = document.getElementById('mobile-cart-fab');
            if (panel)   { panel.classList.remove('hidden'); }
            if (overlay) { overlay.classList.remove('hidden'); }
            if (fab)     { fab.classList.add('hidden'); }
        }
    }

    // ── Toast stok habis ───────────────────────────────────────────────────────
    function _showStockToast(name, stock) {
        let toast = document.getElementById('stock-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'stock-toast';
            toast.className = 'fixed top-5 left-1/2 -translate-x-1/2 z-[999] px-4 py-3 bg-red-500 text-white text-sm font-semibold rounded-2xl shadow-lg transition-all';
            document.body.appendChild(toast);
        }
        toast.textContent = stock === 0
            ? `${name} sudah habis!`
            : `${name} tersisa ${stock}, tidak bisa tambah lagi.`;
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.style.opacity = '0';
        }, 2500);
    }

    function showPanel() {
        _panelManuallyHidden = false;
        const panel   = document.getElementById('order-panel');
        const overlay = document.getElementById('panel-overlay');
        const fab     = document.getElementById('mobile-cart-fab');
        if (panel)   { panel.classList.remove('hidden'); }
        if (overlay && window.innerWidth < 768) { overlay.classList.remove('hidden'); }
        if (fab)     { fab.classList.add('hidden'); fab.classList.remove('flex'); }
    }

    // ── Render cart panel ──────────────────────────────────────────────────────
    function render(state) {
        const panel    = document.getElementById('order-panel');
        const list     = document.getElementById('order-items-list');
        const countEl  = document.getElementById('panel-item-count');
        const totalEl  = document.getElementById('panel-total');
        const badge    = document.getElementById('cart-badge');
        const fab      = document.getElementById('mobile-cart-fab');
        const fabBadge = document.getElementById('fab-badge');

        if (!panel) return;

        const totalQty = state.items.reduce((acc, i) => acc + i.quantity, 0);

        // Show/hide panel
        if (totalQty === 0) {
            panel.classList.add('hidden');
            const overlay = document.getElementById('panel-overlay');
            if (overlay) overlay.classList.add('hidden');
            if (fab) { fab.classList.add('hidden'); fab.classList.remove('flex'); }
            _panelManuallyHidden = false;
        } else if (!_panelManuallyHidden) {
            if (window.innerWidth >= 768) {
                panel.classList.remove('hidden');
            }
        }

        // Header badge
        if (badge) {
            if (totalQty > 0) { badge.classList.remove('hidden'); badge.textContent = totalQty; }
            else               { badge.classList.add('hidden'); }
        }

        // Mobile FAB badge
        if (fab && fabBadge && totalQty > 0 && _panelManuallyHidden) {
            fab.classList.remove('hidden');
            fab.classList.add('flex');
            fabBadge.textContent = totalQty;
            fabBadge.classList.remove('hidden');
            fabBadge.classList.add('flex');
        }

        // Item count label
        if (countEl) countEl.textContent = `${totalQty} ITEMS`;

        // Total
        if (totalEl) totalEl.textContent = formatRp(state.subtotal);

        // Item list
        if (list) {
            list.innerHTML = state.items.map(item => {
                const stock = item.stock ?? _getStockFromDOM(item.product_id);
                const atMax = item.quantity >= stock;
                return `
                <div class="flex items-center gap-3" data-item-id="${item.product_id}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#1C1917] truncate">${item.name}</p>
                        <p class="text-xs text-[#78716C]">${formatRp(item.price)}</p>
                        ${atMax ? `<p class="text-[10px] text-red-500 font-semibold mt-0.5">Stok maks: ${stock}</p>` : ''}
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button onclick="cartStore.decreaseQty(${item.product_id})"
                                class="w-6 h-6 flex items-center justify-center rounded-full border border-stone-200 text-[#78716C] hover:border-primary hover:text-primary transition-colors text-sm font-bold">
                            −
                        </button>
                        <span class="text-sm font-semibold text-[#1C1917] w-4 text-center">${item.quantity}</span>
                        <button onclick="cartStore.increaseQty(${item.product_id})"
                                ${atMax ? 'disabled title="Stok habis"' : ''}
                                class="w-6 h-6 flex items-center justify-center rounded-full transition-colors text-sm font-bold
                                       ${atMax
                                           ? 'bg-stone-200 text-stone-400 cursor-not-allowed'
                                           : 'bg-primary text-white hover:bg-[#EA580C]'}">
                            +
                        </button>
                    </div>
                </div>`;
            }).join('');
        }
    }

    function formatRp(amount) {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    function init() {
        cartStore.subscribe(render);

        const panel   = document.getElementById('order-panel');
        const handle  = document.getElementById('order-panel-handle');
        const overlay = document.getElementById('panel-overlay');
        const fab     = document.getElementById('mobile-cart-fab');

        // Drag (desktop only)
        if (panel && handle && window.innerWidth >= 768) {
            dragModule.enableDrag(panel, handle);
            // dragModule.loadPosition(panel); // Force default CSS position (bottom-left)
        }

        // Close button
        const closeBtn = document.getElementById('panel-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                _panelManuallyHidden = true;
                panel.classList.add('hidden');

                if (window.innerWidth < 768) {
                    if (overlay) overlay.classList.add('hidden');
                    const totalQty = cartStore.getState().items.reduce((a, i) => a + i.quantity, 0);
                    if (totalQty > 0 && fab) {
                        fab.classList.remove('hidden');
                        fab.classList.add('flex');
                        const fabBadge = document.getElementById('fab-badge');
                        if (fabBadge) {
                            fabBadge.textContent = totalQty;
                            fabBadge.classList.remove('hidden');
                            fabBadge.classList.add('flex');
                        }
                    }
                } else {
                    cartStore.reset();
                }
            });
        }

        // Mobile overlay click
        if (overlay) {
            overlay.addEventListener('click', () => {
                _panelManuallyHidden = true;
                panel.classList.add('hidden');
                overlay.classList.add('hidden');
                const totalQty = cartStore.getState().items.reduce((a, i) => a + i.quantity, 0);
                if (totalQty > 0 && fab) {
                    fab.classList.remove('hidden');
                    fab.classList.add('flex');
                    const fabBadge = document.getElementById('fab-badge');
                    if (fabBadge) {
                        fabBadge.textContent = totalQty;
                        fabBadge.classList.remove('hidden');
                        fabBadge.classList.add('flex');
                    }
                }
            });
        }

        // FAB click
        if (fab) {
            fab.addEventListener('click', () => { showPanel(); });
        }

        // Cart toggle button (header)
        const toggleBtn = document.getElementById('cart-toggle-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const state = cartStore.getState();
                if (state.items.length > 0) {
                    if (panel.classList.contains('hidden')) {
                        showPanel();
                    } else {
                        _panelManuallyHidden = true;
                        panel.classList.add('hidden');
                    }
                }
            });
        }

        render(cartStore.getState());
    }

    // Delegasi event agar search tetap berfungsi setelah navigasi SPA mengganti header
    if (!window._cartSearchDelegated) {
        window._cartSearchDelegated = true;
        let searchTimer;
        document.addEventListener('input', (e) => {
            if (e.target.id !== 'search-input' || typeof posModule === 'undefined') return;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                posModule.loadProducts({ search: e.target.value });
            }, 400);
        });
    }

    return { addToCart, render, init, showPanel };
})();

window.cartModule = cartModule;