<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected $transactionRepository;

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

    // Add this to your DashboardController temporarily
    public function statistics(): View
    {
        // Get a sample transaction
        $sample = Transaction::first();
        \Log::info('Sample Transaction:', [
            'full_json' => $sample->full_json,
            'structure' => json_encode($sample->full_json, JSON_PRETTY_PRINT)
        ]);

        $revenueData = $this->transactionRepository->getRevenueAnalysis();
        return view('estadisticas', compact('revenueData'));
    }
}
