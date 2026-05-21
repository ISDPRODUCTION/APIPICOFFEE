/**
 * reportModule.js
 * Manages Sales Report page: Chart.js, filter switching, export.
 * Optimised: single init, AbortController for fetch, skeleton loader.
 */

const reportModule = (() => {

    let chartInstance   = null;
    let currentType     = 'daily';
    let _pendingAbort   = null;  // AbortController for in-flight requests
    let _initialized    = false; // prevent double-init

    // ── Skeleton loader ────────────────────────────────────────────────────────
    function _showSkeleton() {
        const wrap = document.getElementById('chart-wrap');
        if (!wrap) return;
        wrap.innerHTML = `
            <div class="w-full h-full flex items-end justify-center gap-2 px-4 animate-pulse">
                ${[40,60,35,75,50,90,45,65,55,80].map(h =>
                    `<div class="flex-1 rounded-lg bg-stone-200" style="height:${h}%"></div>`
                ).join('')}
            </div>`;
    }

    function _showCanvas() {
        const wrap = document.getElementById('chart-wrap');
        if (!wrap) return;
        // Restore canvas if it was replaced by skeleton
        if (!document.getElementById('revenue-chart')) {
            wrap.innerHTML = '<canvas id="revenue-chart"></canvas>';
        }
    }

    // ── Tooltip options (closure over rawData) ─────────────────────────────────
    function _buildOptions(rawData) {
        return {
            responsive:          true,
            maintainAspectRatio: false,
            animation:           { duration: 350 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1C1917',
                    titleColor:      '#F5F5F4',
                    bodyColor:       '#a8a29e',
                    padding:         14,
                    cornerRadius:    14,
                    displayColors:   false,
                    titleFont:       { size: 12, weight: 'bold' },
                    bodyFont:        { size: 12 },
                    callbacks: {
                        title: (items) => {
                            const d = rawData[items[0].dataIndex];
                            if (!d) return '';
                            if (d.week_start !== undefined) {
                                const fmt = s => new Date(s + 'T00:00:00')
                                    .toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                                return `${fmt(d.week_start)} – ${fmt(d.week_end)}`;
                            }
                            if (d.date) {
                                return new Date(d.date + 'T00:00:00')
                                    .toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
                            }
                            return String(d.month ?? d.year ?? '');
                        },
                        label: (item) => {
                            const d = rawData[item.dataIndex];
                            if (!d) return '';
                            return [
                                `Total Transaksi   ${d.transaction_count}`,
                                `Penghasilan   Rp ${new Intl.NumberFormat('id-ID').format(d.revenue)}`,
                            ];
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid:   { display: false },
                    ticks:  { color: '#78716C', font: { size: 11 }, maxRotation: 0 },
                    border: { display: false },
                },
                y: { display: false },
            },
        };
    }

    // ── Label formatter ────────────────────────────────────────────────────────
    function _formatLabels(rawData) {
        return rawData.map(d => {
            if (d.week_start !== undefined) {
                const s = new Date(d.week_start + 'T00:00:00');
                const e = new Date(d.week_end   + 'T00:00:00');
                const f = dt => dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }).toUpperCase();
                return `${f(s)} – ${f(e)}`;
            }
            if (d.date) {
                return new Date(d.date + 'T00:00:00')
                    .toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }).toUpperCase();
            }
            if (d.month !== undefined) {
                return new Date(2000, d.month - 1, 1)
                    .toLocaleDateString('id-ID', { month: 'short' }).toUpperCase();
            }
            return String(d.year ?? '');
        });
    }

    // ── Build dataset ─────────────────────────────────────────────────────────
    function _buildDataset(rawData) {
        const revenue = rawData.map(d => d.revenue);
        const maxRev  = revenue.length > 0 ? Math.max(...revenue) : 0;
        return {
            label:               'Revenue',
            data:                revenue,
            backgroundColor:     revenue.map(v => v === maxRev && v > 0 ? '#F97316' : '#E2E8F0'),
            hoverBackgroundColor:revenue.map(v => v === maxRev && v > 0 ? '#EA580C' : '#CBD5E1'),
            borderRadius:        10,
            borderSkipped:       false,
            barPercentage:       0.65,
            categoryPercentage:  0.8,
        };
    }

    // ── Create/recreate chart ──────────────────────────────────────────────────
    function _createChart(rawData) {
        _showCanvas();
        const canvas = document.getElementById('revenue-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
        const orphan = Chart.getChart(canvas);
        if (orphan) orphan.destroy();

        chartInstance = new Chart(canvas, {
            type:    'bar',
            data:    { labels: _formatLabels(rawData), datasets: [_buildDataset(rawData)] },
            options: _buildOptions(rawData),
        });
    }

    // ── Public: init (called once per page) ───────────────────────────────────
    function initChart() {
        if (_initialized) return;      // ← prevent double-init
        _initialized = true;
        _createChart(window.reportChartData || []);
    }

    // ── Public: update with fresh data ────────────────────────────────────────
    function updateChart(rawData) {
        if (!chartInstance) {
            _initialized = false;
            window.reportChartData = rawData;
            initChart();
            return;
        }
        const labels  = _formatLabels(rawData);
        const revenue = rawData.map(d => d.revenue);
        const maxRev  = revenue.length > 0 ? Math.max(...revenue) : 0;

        chartInstance.data.labels                              = labels;
        chartInstance.data.datasets[0].data                   = revenue;
        chartInstance.data.datasets[0].backgroundColor        = revenue.map(v => v === maxRev && v > 0 ? '#F97316' : '#E2E8F0');
        chartInstance.data.datasets[0].hoverBackgroundColor   = revenue.map(v => v === maxRev && v > 0 ? '#EA580C' : '#CBD5E1');
        chartInstance.options = _buildOptions(rawData);
        chartInstance.update('active');
    }

    // ── Public: Daily / Weekly toggle ─────────────────────────────────────────
    async function handleFilterChange(type) {
        if (currentType === type) return;   // no-op if already active
        currentType = type;

        // Abort any in-flight request
        if (_pendingAbort) { _pendingAbort.abort(); }
        _pendingAbort = new AbortController();

        // Update button styles immediately (no waiting)
        ['daily', 'weekly'].forEach(t => {
            const btn = document.getElementById(`btn-${t}`);
            if (!btn) return;
            if (t === type) {
                btn.classList.add('bg-[#1C1917]', 'text-white');
                btn.classList.remove('bg-stone-100', 'text-[#78716C]', 'hover:bg-stone-200');
            } else {
                btn.classList.remove('bg-[#1C1917]', 'text-white');
                btn.classList.add('bg-stone-100', 'text-[#78716C]', 'hover:bg-stone-200');
            }
        });

        // Period label
        const periodEl = document.getElementById('chart-period');
        if (periodEl) {
            periodEl.textContent = type === 'weekly'
                ? `Per Minggu ${window.reportYear ?? new Date().getFullYear()}`
                : `Periode ${new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}`;
        }

        // Show skeleton while loading
        _showSkeleton();

        try {
            const month  = window.reportMonth;
            const year   = window.reportYear;
            const url    = `/reports/chart-data?type=${type}&month=${month}&year=${year}`;
            const res    = await fetch(url, {
                signal:  _pendingAbort.signal,
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) throw new Error('Network error');
            const json = await res.json();
            updateChart(json.data || []);
        } catch (err) {
            if (err.name === 'AbortError') return;  // request was cancelled, ignore
            console.error('Failed to load chart data', err);
            _showCanvas();  // remove skeleton on error
        }
    }

    // ── Export ─────────────────────────────────────────────────────────────────
    function triggerExport() {
        const month = window.reportMonth;
        const year  = window.reportYear;
        window.open(`/reports/export?type=daily&month=${month}&year=${year}&format=xlsx`, '_blank');
    }

    // ── Filter modal (date range) ──────────────────────────────────────────────
    let _currentPreset = 'today';

    function openFilter() {
        const modal = document.getElementById('filter-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeFilter() {
        const modal = document.getElementById('filter-modal');
        if (modal) modal.classList.add('hidden');
    }

    function selectPreset(btn) {
        document.querySelectorAll('.preset-btn').forEach(b => {
            b.classList.remove('border-primary', 'text-primary');
            b.classList.add('border-stone-200', 'text-[#78716C]');
        });
        btn.classList.add('border-primary', 'text-primary');
        btn.classList.remove('border-stone-200', 'text-[#78716C]');
        _currentPreset = btn.dataset.preset;
        const customRange = document.getElementById('custom-date-range');
        if (customRange) customRange.classList.toggle('hidden', _currentPreset !== 'custom');
    }

    function _getDateRange(preset) {
        const today = new Date();
        const fmt = d => d.toISOString().split('T')[0];
        switch (preset) {
            case 'today':
                return { from: fmt(today), to: fmt(today), label: 'Hari Ini' };
            case 'yesterday': {
                const yest = new Date(today);
                yest.setDate(yest.getDate() - 1);
                return { from: fmt(yest), to: fmt(yest), label: 'Kemarin' };
            }
            case 'this_week': {
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                return { from: fmt(weekStart), to: fmt(today), label: 'Minggu Ini' };
            }
            case 'this_month':
                return { from: fmt(new Date(today.getFullYear(), today.getMonth(), 1)), to: fmt(today), label: 'Bulan Ini' };
            case 'last_month':
                return {
                    from: fmt(new Date(today.getFullYear(), today.getMonth() - 1, 1)),
                    to: fmt(new Date(today.getFullYear(), today.getMonth(), 0)),
                    label: 'Bulan Lalu',
                };
            case 'custom': {
                const from = document.getElementById('filter-date-from')?.value;
                const to   = document.getElementById('filter-date-to')?.value;
                if (!from || !to) {
                    alert('Pilih tanggal dari dan sampai!');
                    return null;
                }
                return { from, to, label: `${from} s/d ${to}` };
            }
            default:
                return null;
        }
    }

    async function applyFilter() {
        const range = _getDateRange(_currentPreset);
        if (!range) return;
        closeFilter();

        const filterInfo = document.getElementById('filter-info');
        const filterText = document.getElementById('filter-info-text');
        const filterBadge = document.getElementById('filter-badge');
        if (filterInfo) filterInfo.classList.remove('hidden');
        if (filterText) filterText.textContent = `📅 Filter aktif: ${range.label}`;
        if (filterBadge) filterBadge.classList.remove('hidden');

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`/reports/filter?from=${range.from}&to=${range.to}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            });
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Gagal memuat filter');
                return;
            }

            const revenueFormatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.stats.revenue);
            const statRevenue = document.getElementById('stat-revenue');
            const statCount = document.getElementById('stat-count');
            const statLabel = document.getElementById('stat-label-revenue');
            if (statRevenue) statRevenue.textContent = revenueFormatted;
            if (statCount) statCount.textContent = data.stats.count + ' Transaksi';
            if (statLabel) statLabel.textContent = `Total Penjualan (${range.label})`;

            const cards = document.getElementById('transactions-cards');
            const tbody = document.getElementById('transactions-tbody');
            const emptyMsg = '<div class="py-10 text-center text-sm text-[#78716C]">Tidak ada transaksi.</div>';
            const emptyRow = '<tr><td colspan="5" class="py-10 text-center text-sm text-[#78716C]">Tidak ada transaksi.</td></tr>';

            if (data.orders.length === 0) {
                if (cards) cards.innerHTML = emptyMsg;
                if (tbody) tbody.innerHTML = emptyRow;
            } else {
                if (cards) {
                    cards.innerHTML = data.orders.map(o => `
                        <div class="p-4 hover:bg-stone-50 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <p class="text-sm font-bold text-[#1C1917]">#${o.order_number}</p>
                                    <p class="text-xs text-[#78716C]">${o.time} WIB</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-bold text-primary">${o.total}</p>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-600 text-xs font-bold rounded-lg uppercase">SELESAI</span>
                                </div>
                            </div>
                            <p class="text-xs text-[#78716C] leading-relaxed">${o.items}</p>
                        </div>
                    `).join('');
                }
                if (tbody) {
                    tbody.innerHTML = data.orders.map(o => `
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="py-3 px-6 text-sm font-semibold text-[#1C1917] whitespace-nowrap">#${o.order_number}</td>
                            <td class="py-3 px-4 text-sm text-[#78716C] whitespace-nowrap">${o.time} WIB</td>
                            <td class="py-3 px-4 text-sm text-[#78716C] max-w-[200px]">${o.items}</td>
                            <td class="py-3 px-4 text-sm font-semibold text-[#1C1917] whitespace-nowrap">${o.total}</td>
                            <td class="py-3 px-4"><span class="px-2.5 py-1 bg-green-100 text-green-600 text-xs font-bold rounded-lg uppercase">SELESAI</span></td>
                        </tr>
                    `).join('');
                }
            }
        } catch (err) {
            console.error('Filter error', err);
            alert('Gagal memuat data filter. Silakan coba lagi.');
        }
    }

    function clearFilter() {
        _currentPreset = 'today';
        document.querySelectorAll('.preset-btn').forEach(b => {
            b.classList.remove('border-primary', 'text-primary');
            b.classList.add('border-stone-200', 'text-[#78716C]');
        });
        const todayBtn = document.querySelector('[data-preset="today"]');
        if (todayBtn) {
            todayBtn.classList.add('border-primary', 'text-primary');
            todayBtn.classList.remove('border-stone-200', 'text-[#78716C]');
        }
        const filterInfo = document.getElementById('filter-info');
        const filterBadge = document.getElementById('filter-badge');
        const customRange = document.getElementById('custom-date-range');
        if (filterInfo) filterInfo.classList.add('hidden');
        if (filterBadge) filterBadge.classList.add('hidden');
        if (customRange) customRange.classList.add('hidden');
        closeFilter();
        window.location.reload();
    }

    // ── Init entry point ───────────────────────────────────────────────────────
    function init() {
        _initialized = false;  // reset so initChart() will actually run
        if (document.getElementById('revenue-chart')) {
            initChart();
        }
    }

    return {
        initChart, updateChart, handleFilterChange, triggerExport,
        openFilter, closeFilter, selectPreset, applyFilter, clearFilter, init,
    };
})();

window.reportModule = reportModule;

// Single init: runs AFTER inline script sets window.reportChartData
// The inline script calls reportModule.initChart() explicitly,
// so we do NOT auto-init here to avoid double initialisation.
