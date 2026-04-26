/**
 * reportModule.js
 * Manages Sales Report page: Chart.js initialisation, filter switching, export.
 */

const reportModule = (() => {

    let chartInstance = null;
    let currentType   = 'daily';

    // ── Chart initialisation ──────────────────────────────────────────────────
    function initChart() {
        const canvas = document.getElementById('revenue-chart');
        if (!canvas || typeof Chart === 'undefined') return;

        if (chartInstance) {
            chartInstance.destroy();
        }

        const rawData = window.reportChartData || [];

        const labels  = rawData.map(d => {
            const date = new Date(d.date);
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
                    backgroundColor: rawData.map((_, i) =>
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
                                return d.date;
                            },
                            label: (item) => {
                                const idx = item.dataIndex;
                                const d   = rawData[idx];
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
        if (!chartInstance) return;

        const labels  = data.map(d => d.date ?? d.month ?? d.year);
        const revenue = data.map(d => d.revenue);

        chartInstance.data.labels              = labels;
        chartInstance.data.datasets[0].data    = revenue;
        chartInstance.data.datasets[0].backgroundColor = revenue.map((_, i) =>
            i === revenue.indexOf(Math.max(...revenue)) ? '#F97316' : '#E2E8F0'
        );
        chartInstance.update();
    }

    // ── Filter switch ─────────────────────────────────────────────────────────
    async function handleFilterChange(type) {
        currentType = type;

        // Toggle button styles
        ['daily', 'weekly'].forEach(t => {
            const btn = document.getElementById(`btn-${t}`);
            if (!btn) return;
            if (t === type) {
                btn.className = btn.className.replace('bg-stone-100 text-[#78716C]', 'bg-[#1C1917] text-white');
            } else {
                btn.className = btn.className.replace('bg-[#1C1917] text-white', 'bg-stone-100 text-[#78716C]');
            }
        });

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

    function openFilter() {
        // Future: open a filter modal
        console.log('Filter modal – TODO');
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    function init() {
        if (document.getElementById('revenue-chart')) {
            initChart();
        }
    }

    return { initChart, updateChart, handleFilterChange, triggerExport, openFilter, init };
})();

window.reportModule = reportModule;

// Auto-init when DOM is ready
document.addEventListener('DOMContentLoaded', () => reportModule.init());
