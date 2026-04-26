@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="p-4 md:p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 gap-3">
        <h1 class="text-xl md:text-2xl font-bold text-[#1C1917]">Sales Report</h1>
        <div class="flex items-center gap-2">
            <button onclick="reportModule.openFilter()"
                    class="flex items-center gap-2 px-3 md:px-4 py-2 bg-white border border-stone-200 rounded-2xl text-sm font-medium text-[#1C1917] hover:bg-stone-50 transition-colors">
                <svg class="w-4 h-4 text-[#78716C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0014 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586a1 1 0 00-.293-.707L1.293 6.707A1 1 0 011 6V4z"/>
                </svg>
                <span class="hidden sm:inline">Filter</span>
                <span id="filter-badge" class="hidden w-2 h-2 bg-primary rounded-full"></span>
            </button>
            <button onclick="reportModule.triggerExport()"
                    class="flex items-center gap-2 px-3 md:px-4 py-2 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors shadow-lg shadow-orange-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="hidden sm:inline">Export</span>
            </button>
        </div>
    </div>

    {{-- Active filter info --}}
    <div id="filter-info" class="hidden mb-4 px-4 py-2.5 bg-orange-50 border border-orange-100 rounded-2xl flex items-center justify-between">
        <p class="text-sm text-primary font-medium" id="filter-info-text"></p>
        <button onclick="reportModule.clearFilter()" class="text-xs text-[#78716C] hover:text-red-500 font-semibold">✕ Reset Filter</button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-[#78716C]" id="stat-label-revenue">Total Penjualan Hari Ini</p>
                    <p class="text-2xl md:text-3xl font-bold text-primary mt-1" id="stat-revenue">
                        Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}
                    </p>
                    <p class="text-sm mt-2">
                        <span class="{{ $stats['revenue_change'] >= 0 ? 'text-green-500' : 'text-red-500' }} font-semibold">
                            {{ $stats['revenue_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['revenue_change']) }}%
                        </span>
                        <span class="text-[#78716C]"> dari kemarin</span>
                    </p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-primary opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 md:p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-[#78716C]">Jumlah Transaksi</p>
                    <p class="text-2xl md:text-3xl font-bold text-[#1C1917] mt-1" id="stat-count">{{ $stats['today_count'] }} Transaksi</p>
                    <p class="text-sm mt-2">
                        <span class="{{ $stats['count_change'] >= 0 ? 'text-green-500' : 'text-red-500' }} font-semibold">
                            {{ $stats['count_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['count_change']) }}%
                        </span>
                        <span class="text-[#78716C]"> dari kemarin</span>
                    </p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-stone-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6 mb-6">
        <div class="flex items-start justify-between mb-4 gap-3">
            <div class="min-w-0">
                <h2 class="text-base md:text-lg font-bold text-[#1C1917]">Histori Pendapatan Harian</h2>
                <p class="text-sm text-[#78716C]" id="chart-period">Periode {{ $report['period'] }}</p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button onclick="reportModule.handleFilterChange('daily')"
                        id="btn-daily"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#1C1917] text-white transition-colors">
                    Daily
                </button>
                <button onclick="reportModule.handleFilterChange('weekly')"
                        id="btn-weekly"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-stone-100 text-[#78716C] hover:bg-stone-200 transition-colors">
                    Weekly
                </button>
            </div>
        </div>
        <div class="relative h-48 md:h-56">
            <canvas id="revenue-chart"></canvas>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4 md:px-6 py-4 border-b border-stone-100">
            <h2 class="text-base font-bold text-[#1C1917]">Histori Transaksi Terakhir</h2>
            <a href="#" class="text-sm font-semibold text-primary hover:text-[#EA580C]">Lihat Semua</a>
        </div>

        {{-- Mobile card view --}}
        <div class="block md:hidden divide-y divide-stone-100" id="transactions-cards">
            @forelse($recentOrders as $order)
            <div class="p-4 hover:bg-stone-50 transition-colors">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <p class="text-sm font-bold text-[#1C1917]">#{{ $order->order_number }}</p>
                        <p class="text-xs text-[#78716C]">{{ $order->order_date->format('H:i') }} WIB</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-primary">{{ $order->formatted_total }}</p>
                        <span class="px-2 py-0.5 bg-green-100 text-green-600 text-xs font-bold rounded-lg uppercase">
                            {{ $order->status === 'completed' ? 'SELESAI' : strtoupper($order->status) }}
                        </span>
                    </div>
                </div>
                <p class="text-xs text-[#78716C] leading-relaxed">
                    {{ $order->items->map(fn($i) => $i->quantity . 'x ' . $i->product_name)->join(', ') }}
                </p>
            </div>
            @empty
            <div class="py-10 text-center text-sm text-[#78716C]">No transactions yet.</div>
            @endforelse
        </div>

        {{-- Desktop table view --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[560px]">
                <thead>
                    <tr class="border-b border-stone-100">
                        <th class="text-left py-3 px-6 text-xs font-bold text-[#78716C] uppercase tracking-wider whitespace-nowrap">ID Transaksi</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider whitespace-nowrap">Waktu</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Menu</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider whitespace-nowrap">Total Harga</th>
                        <th class="text-left py-3 px-4 text-xs font-bold text-[#78716C] uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody id="transactions-tbody" class="divide-y divide-stone-100">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="py-3 px-6 text-sm font-semibold text-[#1C1917] whitespace-nowrap">#{{ $order->order_number }}</td>
                        <td class="py-3 px-4 text-sm text-[#78716C] whitespace-nowrap">{{ $order->order_date->format('H:i') }} WIB</td>
                        <td class="py-3 px-4 text-sm text-[#78716C] max-w-[200px]">
                            {{ $order->items->map(fn($i) => $i->quantity . 'x ' . $i->product_name)->join(', ') }}
                        </td>
                        <td class="py-3 px-4 text-sm font-semibold text-[#1C1917] whitespace-nowrap">{{ $order->formatted_total }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 bg-green-100 text-green-600 text-xs font-bold rounded-lg uppercase whitespace-nowrap">
                                {{ $order->status === 'completed' ? 'SELESAI' : strtoupper($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-sm text-[#78716C]">No transactions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Filter Modal --}}
<div id="filter-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="reportModule.closeFilter()"></div>
    <div class="relative bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full sm:max-w-sm z-10 p-6">
        <div class="flex justify-center mb-4 sm:hidden"><div class="w-10 h-1 bg-stone-200 rounded-full"></div></div>
        <div class="flex items-start justify-between mb-5">
            <h2 class="text-xl font-bold text-[#1C1917]">Filter Laporan</h2>
            <button onclick="reportModule.closeFilter()" class="text-stone-400 hover:text-[#1C1917]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-[#1C1917] mb-2">Rentang Waktu</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['today' => 'Hari Ini', 'yesterday' => 'Kemarin', 'this_week' => 'Minggu Ini', 'this_month' => 'Bulan Ini', 'last_month' => 'Bulan Lalu', 'custom' => 'Kustom'] as $val => $label)
                    <button type="button" data-preset="{{ $val }}"
                            onclick="reportModule.selectPreset(this)"
                            class="preset-btn px-3 py-2 rounded-xl text-sm font-medium border-2 transition-colors
                                   {{ $val === 'today' ? 'border-primary text-primary' : 'border-stone-200 text-[#78716C] hover:border-stone-300' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div id="custom-date-range" class="hidden space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Dari Tanggal</label>
                    <input type="date" id="filter-date-from"
                           class="w-full px-4 py-2.5 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1C1917] mb-1.5">Sampai Tanggal</label>
                    <input type="date" id="filter-date-to"
                           class="w-full px-4 py-2.5 rounded-2xl border border-stone-200 text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                </div>
            </div>
        </div>
        <div class="flex gap-3 mt-6">
            <button onclick="reportModule.clearFilter()"
                    class="flex-1 py-3 text-sm font-semibold border-2 border-stone-200 rounded-2xl text-[#78716C] hover:border-stone-300 transition-colors">
                Reset
            </button>
            <button onclick="reportModule.applyFilter()"
                    class="flex-1 py-3 bg-primary hover:bg-[#EA580C] text-white rounded-2xl text-sm font-semibold transition-colors">
                Terapkan
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/modules/reportModule.js') }}"></script>
<script>
window.reportChartData = @json($report['data']);
window.reportMonth = {{ $month }};
window.reportYear = {{ $year }};

// Re-init chart now that reportChartData is set
if (window.reportModule) { reportModule.initChart(); }

document.addEventListener('DOMContentLoaded', function() {
    window._filterExt = {
        currentPreset: 'today',

        openFilter() { document.getElementById('filter-modal').classList.remove('hidden'); },
        closeFilter() { document.getElementById('filter-modal').classList.add('hidden'); },

        selectPreset(btn) {
            document.querySelectorAll('.preset-btn').forEach(b => {
                b.classList.remove('border-primary', 'text-primary');
                b.classList.add('border-stone-200', 'text-[#78716C]');
            });
            btn.classList.add('border-primary', 'text-primary');
            btn.classList.remove('border-stone-200', 'text-[#78716C]');
            this.currentPreset = btn.dataset.preset;
            document.getElementById('custom-date-range').classList.toggle('hidden', this.currentPreset !== 'custom');
        },

        getDateRange(preset) {
            const today = new Date();
            const fmt = d => d.toISOString().split('T')[0];
            switch(preset) {
                case 'today': return { from: fmt(today), to: fmt(today), label: 'Hari Ini' };
                case 'yesterday':
                    const yest = new Date(today); yest.setDate(yest.getDate() - 1);
                    return { from: fmt(yest), to: fmt(yest), label: 'Kemarin' };
                case 'this_week':
                    const weekStart = new Date(today); weekStart.setDate(today.getDate() - today.getDay());
                    return { from: fmt(weekStart), to: fmt(today), label: 'Minggu Ini' };
                case 'this_month':
                    return { from: fmt(new Date(today.getFullYear(), today.getMonth(), 1)), to: fmt(today), label: 'Bulan Ini' };
                case 'last_month':
                    return { from: fmt(new Date(today.getFullYear(), today.getMonth() - 1, 1)), to: fmt(new Date(today.getFullYear(), today.getMonth(), 0)), label: 'Bulan Lalu' };
                case 'custom':
                    const from = document.getElementById('filter-date-from').value;
                    const to = document.getElementById('filter-date-to').value;
                    if (!from || !to) { alert('Pilih tanggal dari dan sampai!'); return null; }
                    return { from, to, label: `${from} s/d ${to}` };
            }
        },

        async applyFilter() {
            const range = this.getDateRange(this.currentPreset);
            if (!range) return;
            this.closeFilter();

            document.getElementById('filter-info').classList.remove('hidden');
            document.getElementById('filter-info-text').textContent = `📅 Filter aktif: ${range.label}`;
            document.getElementById('filter-badge').classList.remove('hidden');

            const res = await fetch(`/reports/filter?from=${range.from}&to=${range.to}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await res.json();

            if (data.success) {
                // Format revenue properly (backend returns integer, format as Rp X.XXX)
                const revenueFormatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.stats.revenue);
                document.getElementById('stat-revenue').textContent = revenueFormatted;
                document.getElementById('stat-count').textContent = data.stats.count + ' Transaksi';
                document.getElementById('stat-label-revenue').textContent = `Total Penjualan (${range.label})`;

                // Update mobile cards
                const cards = document.getElementById('transactions-cards');
                // Update desktop tbody
                const tbody = document.getElementById('transactions-tbody');

                if (data.orders.length === 0) {
                    if (cards) cards.innerHTML = `<div class="py-10 text-center text-sm text-[#78716C]">Tidak ada transaksi.</div>`;
                    if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-sm text-[#78716C]">Tidak ada transaksi.</td></tr>`;
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
            }
        },

        clearFilter() {
            this.currentPreset = 'today';
            document.querySelectorAll('.preset-btn').forEach(b => {
                b.classList.remove('border-primary', 'text-primary');
                b.classList.add('border-stone-200', 'text-[#78716C]');
            });
            document.querySelector('[data-preset="today"]').classList.add('border-primary', 'text-primary');
            document.getElementById('filter-info').classList.add('hidden');
            document.getElementById('filter-badge').classList.add('hidden');
            document.getElementById('custom-date-range').classList.add('hidden');
            this.closeFilter();
            window.location.reload();
        }
    };

    setTimeout(() => {
        if (window.reportModule) {
            reportModule.openFilter  = () => window._filterExt.openFilter();
            reportModule.closeFilter = () => window._filterExt.closeFilter();
            reportModule.selectPreset = (btn) => window._filterExt.selectPreset(btn);
            reportModule.applyFilter = () => window._filterExt.applyFilter();
            reportModule.clearFilter = () => window._filterExt.clearFilter();
        }
    }, 50);
});
</script>
@endpush