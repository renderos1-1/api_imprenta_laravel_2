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
                // Match the actual values in the databases
                $item->display_name = $item->person_type === 'natural' ? 'Natural' : 'Jurídica';
                return $item;
            });
    }
}
