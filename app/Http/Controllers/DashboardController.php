<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function index(): View
    {
        // Get transaction data for the last 30 days
        $transactionsPerDay = $this->transactionRepository->getTransactionsPerDay();

        // Format the data for the chart
        $chartData = [
            'labels' => $transactionsPerDay->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            })->toArray(),
            'values' => $transactionsPerDay->pluck('total')->toArray(),
        ];

        return view('dash', compact('chartData'));
    }

    public function indexpiechart(): View
    {
        // Existing transaction per day data
        $transactionsPerDay = $this->transactionRepository->getTransactionsPerDay();
        $chartData = [
            'labels' => $transactionsPerDay->pluck('date')->toArray(),
            'values' => $transactionsPerDay->pluck('total')->toArray(),
        ];

        // Modified person type distribution datajkkk
        $personTypeData = $this->transactionRepository->getPersonTypeDistribution();
        $pieChartData = [
            'labels' => $personTypeData->pluck('display_name')->toArray(),
            'values' => $personTypeData->pluck('total')->toArray(),
            'percentages' => $personTypeData->pluck('percentage')->toArray(),
        ];

        return view('dash', compact('chartData', 'pieChartData'));
    }

    public function revenueChart(): View
    {
        $revenueData = $this->transactionRepository->getRevenueData();

        // Format data for ChartJS
        $revenueChartData = [
            'labels' => $revenueData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m/Y');
            }),
            'values' => $revenueData->pluck('total_revenue')
        ];

        return view('estadisticas', compact('revenueChartData'));
    }

}
