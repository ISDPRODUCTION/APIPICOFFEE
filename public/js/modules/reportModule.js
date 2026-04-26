/**
 * reportModule.js
 * Manages Sales Report page: Chart.js initialisation, filter switching, export.
 */

const reportModule = (() => {

    let chartInstance = null;
    let currentType   = 'daily';
    let _currentRawData = [];  // keep reference for tooltip callbacks

    // ── Build Chart Options ────────────────────────────────────────────────────
    function _buildOptions(rawData) {
        return {
            responsive:          true,
            maintainAspectRatio: false,
            animation: { duration: 500, easing: 'easeInOutQuart' },
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
                            const idx = items[0].dataIndex;
                            const d   = rawData[idx];
                            if (!d) return '';
                            // Weekly: show range
                            if (d.week_start !== undefined) {
                                const fmt = dt => new Date(dt + 'T00:00:00')
                                    .toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                                return `${fmt(d.week_start)} – ${fmt(d.week_end)}`;
                            }
                            // Daily
                            if (d.date) {
                                return new Date(d.date + 'T00:00:00')
                                    .toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
                            }
                            return d.label ?? d.month ?? d.year ?? '';
                        },
                        label: (item) => {
                            const idx = item.dataIndex;
                            const d   = rawData[idx];
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
                    ticks:  {
                        color:     '#78716C',
                        font:      { size: 11 },
                        maxRotation: 0,
                    },
                    border: { display: false },
                },
                y: {
                    display: false,
                    grid:    { display: false },
                    border:  { display: false },
                },
            },
        };
    }

    // ── Build dataset from rawData ─────────────────────────────────────────────
    function _buildDataset(rawData, labels) {
        const revenue = rawData.map(d => d.revenue);
        const maxRev  = revenue.length > 0 ? Math.max(...revenue) : 0;

        return {
            label:           'Revenue',
            data:            revenue,
            backgroundColor: revenue.map(v => v === maxRev && v > 0 ? '#F97316' : '#E2E8F0'),
            hoverBackgroundColor: revenue.map(v => v === maxRev && v > 0 ? '#EA580C' : '#cbd5e1'),
            borderRadius:    10,
            borderSkipped:   false,
            barPercentage:   0.65,
            categoryPercentage: 0.8,
        };
    }

    // ── Format labels depending on data type ──────────────────────────────────
    function _formatLabels(rawData) {
        return rawData.map(d => {
            // Weekly aggregated data: has week_start + week_end
            if (d.week_start !== undefined) {
                const start = new Date(d.week_start + 'T00:00:00');
                const end   = new Date(d.week_end   + 'T00:00:00');
                const fmtDay = dt => dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }).toUpperCase();
                return `${fmtDay(start)} – ${fmtDay(end)}`;
            }
            // Daily data: has 'date'
            if (d.date) {
                const dt = new Date(d.date + 'T00:00:00');
                return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }).toUpperCase();
            }
            // Monthly data: has 'month' number
            if (d.month !== undefined) {
                return new Date(2000, d.month - 1, 1)
                    .toLocaleDateString('id-ID', { month: 'short' }).toUpperCase();
            }
            return String(d.year ?? d.label ?? '');
        });
    }

    // ── Chart initialisation ───────────────────────────────────────────────────
    function initChart() {
        const canvas = document.getElementById('revenue-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        // Destroy any existing instance (both tracked and orphaned)
        if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
        const orphan = Chart.getChart(canvas);
        if (orphan) orphan.destroy();

        const rawData = window.reportChartData || [];
        _currentRawData = rawData;

        const labels  = _formatLabels(rawData);
        const dataset = _buildDataset(rawData, labels);

        chartInstance = new Chart(canvas, {
            type: 'bar',
            data: { labels, datasets: [dataset] },
            options: _buildOptions(rawData),
        });
    }

    // ── Update chart with new data ─────────────────────────────────────────────
    function updateChart(rawData) {
        _currentRawData = rawData;

        if (!chartInstance) { initChart(); return; }

        const labels  = _formatLabels(rawData);
        const revenue = rawData.map(d => d.revenue);
        const maxRev  = revenue.length > 0 ? Math.max(...revenue) : 0;

        chartInstance.data.labels = labels;
        chartInstance.data.datasets[0].data              = revenue;
        chartInstance.data.datasets[0].backgroundColor   = revenue.map(v => v === maxRev && v > 0 ? '#F97316' : '#E2E8F0');
        chartInstance.data.datasets[0].hoverBackgroundColor = revenue.map(v => v === maxRev && v > 0 ? '#EA580C' : '#cbd5e1');

        // Rebuild options so tooltip callbacks reference new rawData
        chartInstance.options = _buildOptions(rawData);
        chartInstance.update('active');
    }

    // ── Filter switch (Daily / Weekly) ─────────────────────────────────────────
    async function handleFilterChange(type) {
        currentType = type;

        // Toggle button styles
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

        // Update period label
        const periodEl = document.getElementById('chart-period');
        if (periodEl) {
            periodEl.textContent = type === 'weekly'
                ? '7 Hari Terakhir'
                : `Periode ${new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}`;
        }

        try {
            const month = window.reportMonth;
            const year  = window.reportYear;
            const res   = await apiService.get(
                `/reports/chart-data?type=${type}&month=${month}&year=${year}`
            );
            updateChart(res.data || []);
        } catch (err) {
            console.error('Failed to load chart data', err);
        }
    }

    // ── Export ─────────────────────────────────────────────────────────────────
    function triggerExport() {
        const month = window.reportMonth;
        const year  = window.reportYear;
        window.open(`/reports/export?type=daily&month=${month}&year=${year}&format=xlsx`, '_blank');
    }

    function openFilter()  { /* overridden by inline script */ }
    function closeFilter() { /* overridden by inline script */ }

    // ── Init ───────────────────────────────────────────────────────────────────
    function init() {
        if (document.getElementById('revenue-chart')) {
            initChart();
        }
    }

    return { initChart, updateChart, handleFilterChange, triggerExport, openFilter, closeFilter, init };
})();

window.reportModule = reportModule;

// Auto-init: works on fresh load AND SPA navigation
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => reportModule.init());
} else {
    reportModule.init();
}
