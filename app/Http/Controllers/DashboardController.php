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

        // Get today's transactions count
        $todayTransactions = $this->transactionRepository->getTodayTransactionsCount();


        // Format the data for the chart
        $chartData = [
            'labels' => $transactionsPerDay->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            })->toArray(),
            'values' => $transactionsPerDay->pluck('total')->toArray(),
        ];

        return view('dash', compact('chartData', 'todayTransactions'));
    }


    public function graphicsChart(): View
    {
        //Codigo para el grafico de pastel juridico/natural
        $personTypeData = $this->transactionRepository->getPersonTypeDistribution();
        $pieChartData = [
            'labels' => $personTypeData->pluck('display_name')->toArray(),
            'values' => $personTypeData->pluck('total')->toArray(),
            'percentages' => $personTypeData->pluck('percentage')->toArray(),
        ];

        //Revenue Chart, grafico para ver la recaudacion
        $revenueData = $this->transactionRepository->getRevenueData();
        $revenueChartData = [
            'labels' => $revenueData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m/Y');
            }),
            'values' => $revenueData->pluck('total_revenue')
        ];

        //Codigo para ver el tipo de documento que se ha usado
        $documentTypeData = $this->transactionRepository->getDocumentTypeDistribution();
        $documentTypeChartData = [
            'labels' => $documentTypeData->pluck('display_name')->toArray(),
            'values' => $documentTypeData->pluck('total')->toArray(),
            'percentages' => $documentTypeData->pluck('percentage')->toArray(),
        ];



        return view('estadisticas', compact('pieChartData','revenueChartData','documentTypeChartData'));
    }

}
