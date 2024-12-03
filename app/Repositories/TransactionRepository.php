<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionRepository
{
    public function getTransactionsPerDay($days = 30)
    {
        return Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getPersonTypeDistribution()
    {
        return Transaction::select(
            'person_type',
            DB::raw('COUNT(*) as total'),
            DB::raw('ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM transactions), 2) as percentage')
        )
            ->groupBy('person_type')
            ->get()
            ->map(function ($item) {
                // Match the actual values in the databasesss
                $item->display_name = $item->person_type === 'natural' ? 'Natural' : 'Jurídica';
                return $item;
            });
    }

    public function getRevenueAnalysis($days = 7)
    {
        return Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as transactions_count'),
            DB::raw('COALESCE(SUM((full_json->\'tramite\'->\'datos\'->\'total_a_pagar\')::numeric), 0) as daily_revenue'),
            DB::raw('COALESCE(AVG((full_json->\'tramite\'->\'datos\'->\'total_a_pagar\')::numeric), 0) as avg_revenue')
        )
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->where('status', 'completado')  // Only include completed transactions
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                // Format numbers to 2 decimal places
                $item->daily_revenue = number_format($item->daily_revenue, 2, '.', '');
                $item->avg_revenue = number_format($item->avg_revenue, 2, '.', '');
                return $item;
            });
    }
}

