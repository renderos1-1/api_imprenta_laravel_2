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

    public function getRevenueData($days = 30)
    {
        return DB::table('transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CAST(
                (full_json::jsonb->\'tramite\'->\'datos\'->5->\'total_a_pagar\')::text
                AS DECIMAL(10,2))
            ) as total_revenue')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'completado')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    public function getDocumentTypeDistribution()
    {
        return Transaction::select(
            'document_type',
            DB::raw('COUNT(*) as total'),
            DB::raw('ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM transactions), 2) as percentage')
        )
            ->groupBy('document_type')
            ->get()
            ->map(function ($item) {
                $displayNames = [
                    'dui' => 'DUI',
                    'passport' => 'Pasaporte',
                    'nit' => 'NIT'
                ];
                $item->display_name = $displayNames[$item->document_type] ?? $item->document_type;
                return $item;
            });
    }

    public function getTodayTransactionsCount()
    {
        return Transaction::whereDate('created_at', Carbon::today())
            ->count();
    }


}

