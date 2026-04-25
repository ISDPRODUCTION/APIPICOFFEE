/**
 * navigatorModule.js
 * Client-side SPA-like navigation for Laravel MPA.
 *
 * - Intercepts clicks on internal nav links
 * - Shows a top progress bar immediately (instant feedback)
 * - Fetches the target page via AJAX in the background
 * - Swaps only the <main> content area (sidebar/header stay untouched)
 * - Updates page title, URL, and sidebar active states
 * - Supports browser back/forward buttons
 * - Prefetches pages on hover for even faster perceived navigation
 */
const navigatorModule = (() => {

    // ── Progress Bar ────────────────────────────────────────────
    let progressBar = null;
    let progressTimer = null;

    function createProgressBar() {
        const bar = document.createElement('div');
        bar.id = 'nav-progress-bar';
        bar.style.cssText = `
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(to right, #F97316, #fb923c);
            z-index: 9999;
            transition: width 0.2s ease, opacity 0.3s ease;
            pointer-events: none;
            box-shadow: 0 0 8px rgba(249,115,22,0.6);
        `;
        document.body.appendChild(bar);
        progressBar = bar;
    }

    function startProgress() {
        if (!progressBar) createProgressBar();
        clearTimeout(progressTimer);
        progressBar.style.opacity = '1';
        progressBar.style.width = '0%';

        let width = 0;
        const step = () => {
            // Ease toward 85% to simulate indeterminate progress
            if (width < 35) width += 8;
            else if (width < 60) width += 4;
            else if (width < 80) width += 1.5;
            else if (width < 85) width += 0.3;
            progressBar.style.width = width + '%';
            if (width < 85) progressTimer = setTimeout(step, 80);
        };
        step();
    }

    function finishProgress() {
        clearTimeout(progressTimer);
        if (!progressBar) return;
        progressBar.style.width = '100%';
        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 300);
        }, 200);
    }

    function failProgress() {
        clearTimeout(progressTimer);
        if (!progressBar) return;
        progressBar.style.background = '#ef4444';
        progressBar.style.width = '100%';
        setTimeout(() => { progressBar.style.opacity = '0'; }, 400);
    }

    // ── Cache for prefetched pages ───────────────────────────────
    const cache = new Map();

    // ── Core Navigation ─────────────────────────────────────────
    async function navigate(url, pushState = true) {
        startProgress();

        try {
            let html;

            // Use cached version if available (from prefetch)
            if (cache.has(url)) {
                html = cache.get(url);
            } else {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Nav-Prefetch': 'true',
                    }
                });

                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                // If redirected (e.g., auth guard), follow it normally
                if (res.redirected) {
                    window.location.href = res.url;
                    return;
                }

                html = await res.text();
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Extract new <main> content
            const newMain = doc.querySelector('main');
            const currentMain = document.querySelector('main');

            if (!newMain || !currentMain) {
                // Fallback: full page navigation
                window.location.href = url;
                return;
            }

            // Swap content with fade
            currentMain.style.opacity = '0';
            currentMain.style.transition = 'opacity 0.12s ease';

            await new Promise(r => setTimeout(r, 80));

            currentMain.innerHTML = newMain.innerHTML;

            // Update page title
            const newTitle = doc.querySelector('title');
            if (newTitle) document.title = newTitle.textContent;

            // Update URL
            if (pushState) {
                window.history.pushState({ url }, '', url);
            }

            // Fade in
            requestAnimationFrame(() => {
                currentMain.style.opacity = '1';
            });

            // Update sidebar active states
            updateSidebarState(url);

            // Re-run any inline @push('scripts') from the new page
            reinitPageScripts(doc, newMain);

            finishProgress();

            // Scroll main area to top
            currentMain.scrollTop = 0;

        } catch (err) {
            console.error('[Navigator] Failed:', err);
            failProgress();
            // Fallback to normal navigation
            window.location.href = url;
        }
    }

    // ── Re-initialize page-specific scripts ─────────────────────
    function reinitPageScripts(doc, newMain) {
        // Build set of ALL currently loaded script srcs to avoid re-declaring globals
        const loadedSrcs = new Set(
            Array.from(document.querySelectorAll('script[src]')).map(s => s.src)
        );

        // Find all <script> tags in the body of the fetched page
        const scripts = doc.querySelectorAll('body script');

        scripts.forEach(oldScript => {
            if (oldScript.src) {
                // Skip any script already present in the DOM (by exact src match)
                if (loadedSrcs.has(oldScript.src)) return;
            }

            const newScript = document.createElement('script');
            newScript.setAttribute('data-nav-page', '1');

            if (oldScript.src) {
                newScript.src = oldScript.src;
                // Track so subsequent navs skip it too
                loadedSrcs.add(oldScript.src);
            } else {
                // Inline script – wrap in self-invoking function to avoid scope pollution
                // and fire immediately since DOMContentLoaded already ran
                newScript.textContent = `(function(){\n${oldScript.textContent}\n// Simulate DOMContentLoaded for inline handlers\ndocument.dispatchEvent(new Event('DOMContentLoaded'));\n})();`;
            }

            document.body.appendChild(newScript);
        });
    }

    // ── Sidebar Active State ─────────────────────────────────────
    function updateSidebarState(url) {
        const navLinks = document.querySelectorAll('#sidebar nav a');
        navLinks.forEach(link => {
            const linkPath = new URL(link.href, window.location.origin).pathname;
            const currentPath = new URL(url, window.location.origin).pathname;
            const isActive = currentPath.startsWith(linkPath) && linkPath !== '/' || currentPath === linkPath;

            if (isActive) {
                link.classList.add('nav-active');
                link.classList.remove('text-[#78716C]', 'hover:bg-stone-100');
            } else {
                link.classList.remove('nav-active');
                link.classList.add('text-[#78716C]', 'hover:bg-stone-100');
            }
        });
    }

    // ── Prefetch on hover ────────────────────────────────────────
    function prefetch(url) {
        if (cache.has(url)) return;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Nav-Prefetch': 'true',
            }
        })
        .then(res => res.ok ? res.text() : null)
        .then(html => {
            if (html) cache.set(url, html);
        })
        .catch(() => {});
    }

    // ── Link Interception ────────────────────────────────────────
    function shouldIntercept(link) {
        if (!link) return false;
        if (link.target === '_blank') return false;
        if (link.hasAttribute('download')) return false;
        if (link.getAttribute('data-no-ajax')) return false;

        const href = link.href;
        if (!href || href.startsWith('javascript:')) return false;

        const origin = window.location.origin;
        if (!href.startsWith(origin)) return false;

        // Skip certain routes
        const skipPaths = ['/logout', '/login', '/forgot-password', '/receipt'];
        const path = new URL(href).pathname;
        if (skipPaths.some(p => path.startsWith(p))) return false;

        return true;
    }

    function init() {
        // Intercept all clicks
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!shouldIntercept(link)) return;

            const url = link.href;

            // Same page? Do nothing
            if (url === window.location.href) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            closeSidebar();
            navigate(url);
        });

        // Prefetch on hover
        let prefetchTimeout;
        document.addEventListener('mouseover', (e) => {
            const link = e.target.closest('a');
            if (!shouldIntercept(link)) return;
            prefetchTimeout = setTimeout(() => prefetch(link.href), 350);
        });
        document.addEventListener('mouseout', () => {
            clearTimeout(prefetchTimeout);
        });

        // Handle browser back/forward
        window.addEventListener('popstate', (e) => {
            const url = e.state?.url || window.location.href;
            navigate(url, false);
        });

        // Push initial state
        window.history.replaceState({ url: window.location.href }, '', window.location.href);

        createProgressBar();
    }

    return { init, navigate, prefetch };
})();

window.navigatorModule = navigatorModule;
navigatorModule.init();
