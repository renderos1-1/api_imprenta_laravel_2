<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Display the statistics view
     */
    public function index(): View
    {
        // Get transaction data for the last 30 days
        $transactionsPerDay = $this->transactionRepository->getTransactionsPerDay();

        // Format chart data
        $chartData = [
            'labels' => $transactionsPerDay->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m/Y');
            })->toArray(),
            'values' => $transactionsPerDay->pluck('total')->toArray(),
        ];

        // Get today's statistics
        $todayTransactions = $this->transactionRepository->getTodayTransactionsCount();
        $todayRevenue = $this->transactionRepository->getTodayRevenue();

        // Format the revenue for display
        $formattedTodayRevenue = number_format($todayRevenue, 2);

        return view('dash', compact('chartData', 'todayTransactions', 'formattedTodayRevenue'));
    }

    /**
     * Display the statistics view with detailed charts
     */
    public function graphicsChart(): View
    {
        // Default to last 7 days
        $endDate = now();
        $startDate = now()->subDays(7);

        // Get data for each chart
        $personTypeData = $this->transactionRepository->getPersonTypeDistribution($startDate, $endDate);
        $revenueData = $this->transactionRepository->getRevenueData($startDate, $endDate);
        $documentTypeData = $this->transactionRepository->getDocumentTypeDistribution($startDate, $endDate);

        // Format data for view
        $pieChartData = [
            'labels' => $personTypeData->pluck('display_name')->toArray(),
            'values' => $personTypeData->pluck('total')->toArray(),
            'percentages' => $personTypeData->pluck('percentage')->toArray(),
        ];

        $revenueChartData = [
            'labels' => $revenueData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m/Y');
            }),
            'values' => $revenueData->pluck('total_revenue')
        ];

        $documentTypeChartData = [
            'labels' => $documentTypeData->pluck('display_name')->toArray(),
            'values' => $documentTypeData->pluck('total')->toArray(),
            'percentages' => $documentTypeData->pluck('percentage')->toArray(),
        ];

        return view('estadisticas', compact('pieChartData', 'revenueChartData', 'documentTypeChartData'));
    }

    /**
     * Get chart data for AJAX requests
     */
    public function getChartData(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();

            // Get updated data for each chart
            $personTypeData = $this->transactionRepository->getPersonTypeDistribution($startDate, $endDate);
            $revenueData = $this->transactionRepository->getRevenueData($startDate, $endDate);
            $documentTypeData = $this->transactionRepository->getDocumentTypeDistribution($startDate, $endDate);

            return response()->json([
                'personType' => [
                    'labels' => $personTypeData->pluck('display_name')->toArray(),
                    'values' => $personTypeData->pluck('total')->toArray(),
                    'percentages' => $personTypeData->pluck('percentage')->toArray(),
                ],
                'revenue' => [
                    'labels' => $revenueData->pluck('date')->map(fn($date) => Carbon::parse($date)->format('d/m/Y'))->toArray(),
                    'values' => $revenueData->pluck('total_revenue')->toArray()
                ],
                'documentType' => [
                    'labels' => $documentTypeData->pluck('display_name')->toArray(),
                    'values' => $documentTypeData->pluck('total')->toArray(),
                    'percentages' => $documentTypeData->pluck('percentage')->toArray(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export chart data
     */
    public function exportChart(Request $request, string $type)
    {
        try {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $format = $request->input('format', 'xlsx');

            // Get the data based on chart type
            switch ($type) {
                case 'personType':
                    $data = $this->transactionRepository->getPersonTypeDistribution($startDate, $endDate)->toArray();
                    break;
                case 'revenue':
                    $data = $this->transactionRepository->getRevenueData($startDate, $endDate)->toArray();
                    break;
                case 'documentType':
                    $data = $this->transactionRepository->getDocumentTypeDistribution($startDate, $endDate)->toArray();
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid chart type: {$type}");
            }

            // Handle different export formats
            switch ($format) {
                case 'pdf':
                    return PDF::loadView("exports.{$type}", ['data' => $data])
                        ->download("{$type}_export.pdf");
                case 'csv':
                    return Excel::download(new ChartDataExport($data), "{$type}_export.csv");
                case 'xlsx':
                default:
                    return Excel::download(new ChartDataExport($data), "{$type}_export.xlsx");
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
