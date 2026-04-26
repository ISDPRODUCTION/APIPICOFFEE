/**
 * reportModule.js
 * Manages Sales Report page: Chart.js initialisation, filter switching, export.
 * Fixed: Canvas reuse, daily/weekly toggle, SPA navigation support.
 */

const reportModule = (() => {

    let chartInstance = null;
    let currentType   = 'daily';

    // ── Chart initialisation ──────────────────────────────────────────────────
    function initChart() {
        const canvas = document.getElementById('revenue-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        // Always destroy existing instance before creating new one
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        // Also destroy any orphaned Chart.js instance attached to the canvas
        const existingChart = Chart.getChart(canvas);
        if (existingChart) {
            existingChart.destroy();
        }

        const rawData = window.reportChartData || [];

        const labels  = rawData.map(d => {
            const date = new Date(d.date + 'T00:00:00');
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }).toUpperCase();
        });
        const revenue = rawData.map(d => d.revenue);

        chartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label:           'Revenue',
                    data:            revenue,
                    backgroundColor: revenue.map((_, i) =>
                        i === revenue.indexOf(Math.max(...revenue))
                            ? '#F97316'
                            : '#E2E8F0'
                    ),
                    borderRadius:    8,
                    borderSkipped:   false,
                    barPercentage:   0.6,
                }],
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                animation: {
                    duration: 400,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1C1917',
                        titleColor:      '#F5F5F4',
                        bodyColor:       '#F97316',
                        padding:         12,
                        cornerRadius:    12,
                        callbacks: {
                            title: (items) => {
                                const idx = items[0].dataIndex;
                                const d   = rawData[idx];
                                return d ? d.date : '';
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
                        ticks:  { color: '#78716C', font: { size: 11 } },
                        border: { display: false },
                    },
                    y: {
                        grid:    { color: '#F5F5F4' },
                        ticks:   { color: '#78716C', font: { size: 11 } },
                        border:  { display: false },
                        display: false,
                    },
                },
            },
        });
    }

    function updateChart(data) {
        if (!chartInstance) {
            initChart();
            return;
        }

        const labels  = data.map(d => {
            // Handle both daily (has 'date') and weekly (has 'month'/'year')
            if (d.date) {
                const dt = new Date(d.date + 'T00:00:00');
                return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }).toUpperCase();
            }
            return d.month ?? d.year ?? '';
        });
        const revenue = data.map(d => d.revenue);
        const maxRev  = revenue.length > 0 ? Math.max(...revenue) : 0;

        chartInstance.data.labels              = labels;
        chartInstance.data.datasets[0].data    = revenue;
        chartInstance.data.datasets[0].backgroundColor = revenue.map((v, i) =>
            v === maxRev && v > 0 ? '#F97316' : '#E2E8F0'
        );
        chartInstance.update('active');
    }

    // ── Filter switch ─────────────────────────────────────────────────────────
    async function handleFilterChange(type) {
        currentType = type;

        // Toggle button styles using data-active attribute
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
            periodEl.textContent = type === 'weekly' ? 'Data Mingguan' : `Periode bulan ini`;
        }

        try {
            const month = window.reportMonth;
            const year  = window.reportYear;
            const data  = await apiService.get(
                `/reports/chart-data?type=${type}&month=${month}&year=${year}`
            );
            updateChart(data.data || []);
        } catch (err) {
            console.error('Failed to load chart data', err);
        }
    }

    // ── Export ─────────────────────────────────────────────────────────────────
    function triggerExport() {
        const month  = window.reportMonth;
        const year   = window.reportYear;
        const url    = `/reports/export?type=daily&month=${month}&year=${year}&format=xlsx`;
        window.open(url, '_blank');
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

// Auto-init: works both on fresh page load AND SPA navigation
// On fresh load, DOMContentLoaded fires and init() runs.
// On SPA nav, the inline script in the page has already set window.reportChartData
// before this file executes, so we can call init() directly.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => reportModule.init());
} else {
    // DOM is already ready (SPA navigation scenario)
    reportModule.init();
}
