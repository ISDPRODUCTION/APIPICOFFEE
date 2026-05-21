/**
 * themeStore.js
 * Applies accent color + dark mode globally via CSS variables.
 * Loaded in <head> of app.blade.php — runs before DOM renders.
 */
(function () {

    const PALETTES = {
        '#F97316': { p: '#F97316', hover: '#EA580C', light: '#FFF7ED', light2: '#FFEDD5', text: '#F97316', shadow: 'rgba(249,115,22,0.25)' },
        '#3B82F6': { p: '#3B82F6', hover: '#2563EB', light: '#EFF6FF', light2: '#DBEAFE', text: '#3B82F6', shadow: 'rgba(59,130,246,0.25)' },
        '#10B981': { p: '#10B981', hover: '#059669', light: '#ECFDF5', light2: '#D1FAE5', text: '#10B981', shadow: 'rgba(16,185,129,0.25)' },
        '#8B5CF6': { p: '#8B5CF6', hover: '#7C3AED', light: '#F5F3FF', light2: '#EDE9FE', text: '#8B5CF6', shadow: 'rgba(139,92,246,0.25)' },
        '#EF4444': { p: '#EF4444', hover: '#DC2626', light: '#FEF2F2', light2: '#FEE2E2', text: '#EF4444', shadow: 'rgba(239,68,68,0.25)' },
    };

    const DARK_MODE = { bg: '#111827', surface: '#1F2937', border: '#374151', text: '#F9FAFB', muted: '#9CA3AF', input: '#374151' };
    const LIGHT_MODE = { bg: '#F5F5F4', surface: '#FFFFFF', border: '#E7E5E4', text: '#1C1917', muted: '#78716C', input: '#F5F5F4' };

    function applyTheme(color, darkMode) {
        const c = PALETTES[color] || PALETTES['#F97316'];
        const m = darkMode ? DARK_MODE : LIGHT_MODE;

        // Inject or update <style id="theme-vars">
        const css = `
            :root {
                --color-primary:  ${c.p};
                --color-hover:    ${c.hover};
                --color-light:    ${c.light};
                --color-light2:   ${c.light2};
                --color-shadow:   ${c.shadow};
                --color-bg:       ${m.bg};
                --color-surface:  ${m.surface};
                --color-border:   ${m.border};
                --color-text:     ${m.text};
                --color-muted:    ${m.muted};
                --color-input:    ${m.input};
            }

            /* ── Global bg & text ─────────────────── */
            html, body { background-color: var(--color-bg) !important; color: var(--color-text) !important; }

            /* ── White surfaces ───────────────────── */
            .bg-white,
            aside#sidebar,
            header,
            .bg-stone-50 { background-color: var(--color-surface) !important; }

            /* ── Borders ──────────────────────────── */
            .border-stone-200, .border-stone-100,
            aside#sidebar, header,
            .divide-stone-100 > * + * { border-color: var(--color-border) !important; }

            /* ── Text colors ──────────────────────── */
            .text-\\[\\#1C1917\\] { color: var(--color-text) !important; }
            .text-\\[\\#78716C\\] { color: var(--color-muted) !important; }

            /* ── Primary accent ───────────────────── */
            .text-primary,
            .text-\\[\\#F97316\\],
            .text-\\[\\#3B82F6\\],
            .text-\\[\\#10B981\\],
            .text-\\[\\#8B5CF6\\],
            .text-\\[\\#EF4444\\] { color: var(--color-primary) !important; }

            .bg-primary,
            .bg-\\[\\#F97316\\] { background-color: var(--color-primary) !important; }

            .hover\\:bg-\\[\\#EA580C\\]:hover,
            .hover\\:bg-primary:hover { background-color: var(--color-hover) !important; }

            .border-primary { border-color: var(--color-primary) !important; }

            /* ── Accent light backgrounds ─────────── */
            .bg-orange-50, .bg-orange-100,
            .bg-blue-50,   .bg-blue-100,
            .bg-green-50,  .bg-green-100,
            .bg-purple-50, .bg-purple-100,
            .bg-red-50,    .bg-red-100 { background-color: var(--color-light) !important; }

            .bg-orange-100.hover\\:bg-orange-200:hover { background-color: var(--color-light2) !important; }

            /* ── Nav active state ─────────────────── */
            .nav-active {
                background-color: var(--color-light) !important;
                color: var(--color-primary) !important;
            }
            .nav-active svg { stroke: var(--color-primary) !important; }

            /* ── Shift status card (kontras tetap terbaca) ── */
            #shift-status-card {
                background-color: ${darkMode ? m.surface : c.light} !important;
                border: 1px solid ${darkMode ? m.border : 'transparent'};
            }
            #shift-status-card .shift-label {
                color: var(--color-primary) !important;
            }
            #shift-status-card .shift-user-name {
                color: ${darkMode ? m.text : '#1C1917'} !important;
            }
            #shift-status-card .shift-user-meta {
                color: ${darkMode ? m.muted : '#78716C'} !important;
            }

            /* ── Sidebar brand name color ─────────── */
            aside#sidebar span.text-\\[\\#F97316\\],
            aside#sidebar span.text-\\[\\#3B82F6\\],
            aside#sidebar span.text-\\[\\#10B981\\],
            aside#sidebar span.text-\\[\\#8B5CF6\\],
            aside#sidebar span.text-\\[\\#EF4444\\] { color: var(--color-primary) !important; }

            /* ── Cart badge & button ──────────────── */
            #cart-badge,
            span.bg-\\[\\#F97316\\] { background-color: var(--color-primary) !important; }
            .text-\\[\\#F97316\\] { color: var(--color-primary) !important; }

            /* ── Focus rings ──────────────────────── */
            .focus\\:ring-\\[\\#F97316\\]\\/30:focus,
            .focus\\:ring-primary\\/30:focus {
                --tw-ring-color: color-mix(in srgb, var(--color-primary) 30%, transparent) !important;
            }

            /* ── Role badge in employee table ─────── */
            .bg-orange-100.text-primary {
                background-color: var(--color-light) !important;
                color: var(--color-primary) !important;
            }

            /* ── Inputs ───────────────────────────── */
            input:not([type=checkbox]):not([type=radio]),
            select,
            textarea {
                background-color: var(--color-input) !important;
                color: var(--color-text) !important;
                border-color: var(--color-border) !important;
            }

            /* ── Category badges ──────────────────── */
            .bg-stone-100 {
                background-color: var(--color-input) !important;
            }

            /* ── Table hover ──────────────────────── */
            tbody tr:hover { background-color: var(--color-bg) !important; }

            /* ── Modal surfaces ───────────────────── */
            #add-category-modal .bg-white,
            #add-menu-modal .bg-white,
            #edit-menu-modal .bg-white,
            #delete-modal .bg-white {
                background-color: var(--color-surface) !important;
            }

            /* ── Shadow accent ────────────────────── */
            .shadow-orange-200 { box-shadow: 0 4px 14px var(--color-shadow) !important; }

            /* ── Scrollbar ────────────────────────── */
            ::-webkit-scrollbar { width: 6px; height: 6px; }
            ::-webkit-scrollbar-track { background: var(--color-bg); }
            ::-webkit-scrollbar-thumb { background: var(--color-border); border-radius: 4px; }
        `;

        let el = document.getElementById('theme-vars');
        if (!el) {
            el = document.createElement('style');
            el.id = 'theme-vars';
            // Insert as first child of <head> so it loads before everything else
            document.head.insertBefore(el, document.head.firstChild);
        }
        el.textContent = css;
    }

    window.themeStore = { applyTheme, PALETTES };

})();