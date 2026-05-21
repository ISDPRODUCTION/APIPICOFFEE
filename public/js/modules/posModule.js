/**
 * posModule.js
 * Handle category switching & product search tanpa reload halaman.
 * v2 – cache prefetch per kategori untuk switching instan.
 */
const posModule = (() => {

    let _currentCategory = (() => {
        const params = new URLSearchParams(window.location.search);
        return params.get('category') || 'all';
    })();

    // Cache HTML grid per category untuk switching instan
    const _cache = new Map();
    let _abortController = null;

    function switchCategory(category) {
        if (_currentCategory === category) return; // sudah aktif, skip
        _currentCategory = category;

        // Update tampilan tab aktif
        document.querySelectorAll('.category-tab').forEach(btn => {
            const isActive = btn.dataset.category === category;
            btn.classList.toggle('text-[#1C1917]', isActive);
            btn.classList.toggle('text-[#78716C]', !isActive);
            btn.classList.remove('hover:text-[#1C1917]');
            if (isActive) {
                btn.classList.add('after:absolute', 'after:bottom-0', 'after:left-0', 'after:right-0', 'after:h-0.5', 'after:bg-primary');
            } else {
                btn.classList.remove('after:absolute', 'after:bottom-0', 'after:left-0', 'after:right-0', 'after:h-0.5', 'after:bg-primary');
                btn.classList.add('hover:text-[#1C1917]');
            }
        });

        loadProducts({ category });
    }

    function _buildUrl(params = {}) {
        const query = Object.assign({ category: _currentCategory }, params);
        const url = new URL(window.location.href);
        // Hapus search agar tidak mengganggu switch kategori
        Object.entries(query).forEach(([k, v]) => {
            if (v && v !== 'all') url.searchParams.set(k, v);
            else url.searchParams.delete(k);
        });
        return url;
    }

    function loadProducts(params = {}) {
        const grid = document.getElementById('product-grid');
        if (!grid) return;

        const url = _buildUrl(params);
        const cacheKey = url.toString();

        // Jika sudah di cache, tampilkan langsung tanpa loading
        if (_cache.has(cacheKey)) {
            const { gridHTML, countText } = _cache.get(cacheKey);
            _applyResult(grid, gridHTML, countText);
            window.history.replaceState({}, '', cacheKey);
            return;
        }

        // Batalkan request sebelumnya jika masih berlangsung
        if (_abortController) _abortController.abort();
        _abortController = new AbortController();

        // Loading state ringan – hanya opacity, tanpa pointer-events block
        grid.style.opacity = '0.4';
        grid.style.transition = 'opacity 0.15s ease';

        fetch(url.toString(), {
            signal: _abortController.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('product-grid');
            const newCount = doc.getElementById('product-count');

            const gridHTML = newGrid ? newGrid.innerHTML : '';
            const countText = newCount ? newCount.textContent : '';

            // Simpan ke cache
            _cache.set(cacheKey, { gridHTML, countText });

            _applyResult(grid, gridHTML, countText);
            window.history.replaceState({}, '', url.toString());
        })
        .catch(err => {
            if (err.name !== 'AbortError') console.error('Load products error:', err);
        })
        .finally(() => {
            grid.style.opacity = '1';
        });
    }

    function _applyResult(grid, gridHTML, countText) {
        grid.innerHTML = gridHTML;
        grid.style.opacity = '1';

        const countEl = document.getElementById('product-count');
        if (countEl && countText) countEl.textContent = countText;
    }

    // Prefetch kategori lain agar switching berikutnya instan
    function _prefetchOthers(skipCategory) {
        const slugs = Array.from(document.querySelectorAll('.category-tab'))
            .map(btn => btn.dataset.category)
            .filter(Boolean);
        const allCategories = ['all', ...slugs.filter(s => s !== 'all')];
        allCategories.forEach(cat => {
            if (cat === skipCategory) return;
            const url = _buildUrl({ category: cat });
            const key = url.toString();
            if (_cache.has(key)) return;

            setTimeout(() => {
                fetch(key, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newGrid = doc.getElementById('product-grid');
                        const newCount = doc.getElementById('product-count');
                        _cache.set(key, {
                            gridHTML: newGrid ? newGrid.innerHTML : '',
                            countText: newCount ? newCount.textContent : ''
                        });
                    })
                    .catch(() => {});
            }, 200); // delay agar tidak berebut bandwidth dengan request utama
        });
    }

    // Inisialisasi: prefetch semua kategori saat idle
    function init() {
        // Dinonaktifkan: prefetch otomatis ternyata memberatkan jaringan
        // dan membuat navigasi ke halaman lain terasa lemot.
    }

    return { switchCategory, loadProducts, init };
})();

window.posModule = posModule;
posModule.init();