<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {}

    public function getDailyReport(int $month, int $year): array
    {
        $isCurrentMonth = ($month === (int) date('m') && $year === (int) date('Y'));
        $ttl = $isCurrentMonth ? 600 : 604800; // 10 menit jika bulan ini, 7 hari jika bulan lalu

        return cache()->remember("report_daily_{$year}_{$month}", $ttl, function () use ($month, $year) {
            $data = $this->orderRepository->getDailyReportData($month, $year);

            return [
                'period'  => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'data'    => $data,
                'total'   => array_sum(array_column($data, 'revenue')),
                'count'   => array_sum(array_column($data, 'transaction_count')),
            ];
        });
    }

    public function getWeeklyReport(int $year): array
    {
        $isCurrentYear = ($year === (int) date('Y'));
        $ttl = $isCurrentYear ? 600 : 604800; // 10 menit jika tahun ini, 7 hari jika tahun lalu

        return cache()->remember("report_weekly_{$year}", $ttl, function () use ($year) {
            $data = $this->orderRepository->getWeeklyReportData($year);

            return [
                'period'  => 'Per Minggu ' . $year,
                'data'    => $data,
                'total'   => array_sum(array_column($data, 'revenue')),
                'count'   => array_sum(array_column($data, 'transaction_count')),
            ];
        });
    }

    public function getMonthlyReport(int $year): array
    {
        $isCurrentYear = ($year === (int) date('Y'));
        $ttl = $isCurrentYear ? 600 : 604800; // 10 menit jika tahun ini, 7 hari jika tahun lalu

        return cache()->remember("report_monthly_{$year}", $ttl, function () use ($year) {
            $data = $this->orderRepository->getMonthlyReportData($year);

            return [
                'period'  => (string) $year,
                'data'    => $data,
                'total'   => array_sum(array_column($data, 'revenue')),
                'count'   => array_sum(array_column($data, 'transaction_count')),
            ];
        });
    }

    public function getYearlyReport(): array
    {
        return cache()->remember("report_yearly", 3600, function () { // Cache 1 jam
            $data = $this->orderRepository->getYearlyReportData();

            return [
                'data'  => $data,
                'total' => array_sum(array_column($data, 'revenue')),
                'count' => array_sum(array_column($data, 'transaction_count')),
            ];
        });
    }

    public function exportDaily(int $month, int $year): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $report = $this->getDailyReport($month, $year);
        return Excel::download(new \App\Exports\DailyReportExport($report), "daily-report-{$month}-{$year}.xlsx");
    }

    public function exportMonthly(int $year): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $report = $this->getMonthlyReport($year);
        return Excel::download(new \App\Exports\MonthlyReportExport($report), "monthly-report-{$year}.xlsx");
    }

    public function exportPdf(string $type, int $month, int $year): \Illuminate\Http\Response
    {
        $report = match($type) {
            'monthly' => $this->getMonthlyReport($year),
            'yearly'  => $this->getYearlyReport(),
            default   => $this->getDailyReport($month, $year),
        };

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView("reports.pdf.{$type}", compact('report'));
        return $pdf->download("report-{$type}.pdf");
    }
}
