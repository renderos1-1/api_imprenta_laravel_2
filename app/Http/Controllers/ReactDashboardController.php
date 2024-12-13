<?php

namespace App\Http\Controllers;

use App\Repositories\ReactTransactionRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReactDashboardController extends Controller
{
    protected ReactTransactionRepo $transactionRepository;

    public function __construct(ReactTransactionRepo $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getStats()
    {
        try {
            return response()->json([
                'transactionsToday' => [
                    'value' => $this->transactionRepository->getTransactionsCount('today'),
                    'change' => $this->transactionRepository->getTransactionsChange()
                ],
                'processedDocs' => [
                    'value' => $this->transactionRepository->getProcessedDocumentsCount(),
                    'change' => $this->transactionRepository->getProcessedDocumentsChange()
                ],
                'revenue' => [
                    'value' => $this->transactionRepository->getRevenueToday(),
                    'change' => $this->transactionRepository->getRevenueChange()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching dashboard stats', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error fetching stats'], 500);
        }
    }

    public function getTransactions(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $transactions = $this->transactionRepository->getTransactionsPerDay($startDate, $endDate);

            return response()->json($transactions);
        } catch (\Exception $e) {
            Log::error('Error fetching transactions data', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error fetching transactions'], 500);
        }
    }
}
