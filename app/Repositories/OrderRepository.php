<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function __construct(
        private readonly Order     $model,
        private readonly OrderItem $itemModel,
    ) {}

    public function create(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items) {
            $order = $this->model->create($orderData);

            foreach ($items as $item) {
                $this->itemModel->create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'product_name'  => $item['name'],
                    'product_price' => $item['price'],
                    'quantity'      => $item['quantity'],
                    'subtotal'      => $item['price'] * $item['quantity'],
                ]);
            }

            return $order->load('items.product', 'cashier');
        });
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model->with('items.product', 'cashier')
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function findById(int $id): ?Order
    {
        return $this->model->with('items.product', 'cashier')->find($id);
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return $this->model->with('items', 'cashier')
            ->orderByDesc('order_date')
            ->limit($limit)
            ->get();
    }

    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('items', 'cashier')
            ->orderByDesc('order_date')
            ->paginate($perPage);
    }

    public function getDailyRevenue(Carbon $date): int
    {
        return (int) $this->model
            ->whereDate('order_date', $date)
            ->where('status', 'completed')
            ->sum('total');
    }

    public function getDailyTransactionCount(Carbon $date): int
    {
        return $this->model
            ->whereDate('order_date', $date)
            ->where('status', 'completed')
            ->count();
    }

    public function getDailyReportData(int $month, int $year): array
    {
        return $this->model
            ->selectRaw('DATE(order_date) as date, COUNT(*) as transaction_count, SUM(total) as revenue')
            ->whereMonth('order_date', $month)
            ->whereYear('order_date', $year)
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function getMonthlyReportData(int $year): array
    {
        return $this->model
            ->selectRaw('MONTH(order_date) as month, YEAR(order_date) as year, COUNT(*) as transaction_count, SUM(total) as revenue')
            ->whereYear('order_date', $year)
            ->where('status', 'completed')
            ->groupBy('year', 'month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    public function getWeeklyReportData(): array
    {
        $start = Carbon::now()->subDays(6)->startOfDay();
        $end   = Carbon::now()->endOfDay();

        return $this->model
            ->selectRaw('DATE(order_date) as date, COUNT(*) as transaction_count, SUM(total) as revenue')
            ->whereBetween('order_date', [$start, $end])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function getYearlyReportData(): array
    {
        return $this->model
            ->selectRaw('YEAR(order_date) as year, COUNT(*) as transaction_count, SUM(total) as revenue')
            ->where('status', 'completed')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->toArray();
    }
}
