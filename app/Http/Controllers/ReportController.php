<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Models\Order;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly OrderService  $orderService,
    ) {}

    public function index(Request $request): View
    {
        $month  = (int) $request->get('month', Carbon::now()->month);
        $year   = (int) $request->get('year', Carbon::now()->year);
        $type   = $request->get('type', 'daily');

        $report       = $this->reportService->getDailyReport($month, $year);
        $weeklyReport = $this->reportService->getWeeklyReport($year);
        $recentOrders = $this->orderService->getRecentOrders(10);
        $stats        = $this->orderService->getDashboardStats();

        return view('reports.index', compact('report', 'weeklyReport', 'recentOrders', 'stats', 'month', 'year', 'type'));
    }

    public function chartData(Request $request): JsonResponse
    {
        $month = (int) $request->get('month', Carbon::now()->month);
        $year  = (int) $request->get('year', Carbon::now()->year);
        $type  = $request->get('type', 'daily');

        $data = match($type) {
            'weekly'  => $this->reportService->getWeeklyReport($year),
            'monthly' => $this->reportService->getMonthlyReport($year),
            'yearly'  => $this->reportService->getYearlyReport(),
            default   => $this->reportService->getDailyReport($month, $year),
        };

        return response()
            ->json($data)
            ->header('Cache-Control', 'private, max-age=120');
    }

    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($request->from)->startOfDay();
        $to   = Carbon::parse($request->to)->endOfDay();

        $orders = Order::with('items')
            ->whereBetween('order_date', [$from, $to])
            ->where('status', 'completed')
            ->orderByDesc('order_date')
            ->get();

        $revenue = $orders->sum('total');
        $count   = $orders->count();

        $ordersData = $orders->map(fn($o) => [
            'order_number' => $o->order_number,
            'time'         => Carbon::parse($o->order_date)->format('H:i'),
            'items'        => $o->items->map(fn($i) => $i->quantity . 'x ' . $i->product_name)->join(', '),
            'total'        => 'Rp ' . number_format($o->total, 0, ',', '.'),
            'status'       => $o->status,
        ]);

        // Chart data: group per HARI agar setiap bar = 1 hari
        $chartData = Order::selectRaw('DATE(order_date) as date, COUNT(*) as transaction_count, SUM(total) as revenue')
            ->whereBetween('order_date', [$from, $to])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        return response()->json([
            'success'    => true,
            'stats'      => [
                'revenue' => $revenue,
                'count'   => $count,
            ],
            'orders'     => $ordersData,
            'chart_data' => $chartData,
        ]);
    }

    public function export(Request $request)
    {
        $month  = (int) $request->get('month', Carbon::now()->month);
        $year   = (int) $request->get('year', Carbon::now()->year);
        $type   = $request->get('type', 'daily');
        $format = $request->get('format', 'xlsx');

        if ($format === 'pdf') {
            return $this->reportService->exportPdf($type, $month, $year);
        }

        return match($type) {
            'monthly' => $this->reportService->exportMonthly($year),
            default   => $this->reportService->exportDaily($month, $year),
        };
    }
}