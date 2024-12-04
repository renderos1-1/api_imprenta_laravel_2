<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionRepository
{
    /**
     * Get transactions count per day within a date range or for the last X days
     */
    public function getTransactionsPerDay($startDate = null, $endDate = null)
    {
        $query = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        );

        $this->applyDateFilter($query, $startDate, $endDate);

        return $query->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get distribution of transactions by person type
     */
    public function getPersonTypeDistribution($startDate = null, $endDate = null)
    {
        $query = Transaction::select(
            'person_type',
            DB::raw('COUNT(*) as total')
        );

        $this->applyDateFilter($query, $startDate, $endDate);

        $result = $query->groupBy('person_type')->get();

        // Calculate total for percentage
        $total = $result->sum('total');

        return $result->map(function ($item) use ($total) {
            $item->percentage = $total > 0 ? round(($item->total * 100.0) / $total, 2) : 0;
            $item->display_name = $item->person_type === 'persona_natural' ? 'Natural' : 'Jurídica';
            return $item;
        });
    }

    /**
     * Get revenue data within a date range or for the last X days
     */
    public function getRevenueData($startDate = null, $endDate = null)
    {
        $query = DB::table('transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CAST(
                    (full_json::jsonb->\'tramite\'->\'datos\'->5->\'total_a_pagar\')::text
                    AS DECIMAL(10,2))
                ) as total_revenue')
            )
            ->where('status', 'completado');

        $this->applyDateFilter($query, $startDate, $endDate);

        return $query->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get distribution of transactions by document type
     */
    public function getDocumentTypeDistribution($startDate = null, $endDate = null)
    {
        $query = Transaction::select(
            'document_type',
            DB::raw('COUNT(*) as total')
        );

        $this->applyDateFilter($query, $startDate, $endDate);

        $result = $query->groupBy('document_type')->get();

        // Calculate total for percentage
        $total = $result->sum('total');

        return $result->map(function ($item) use ($total) {
            $displayNames = [
                'dui' => 'DUI',
                'passport' => 'Pasaporte',
                'nit' => 'NIT'
            ];
            $item->percentage = $total > 0 ? round(($item->total * 100.0) / $total, 2) : 0;
            $item->display_name = $displayNames[$item->document_type] ?? $item->document_type;
            return $item;
        });
    }

    /**
     * Get today's transaction count
     */
    public function getTodayTransactionsCount()
    {
        return Transaction::whereDate('created_at', Carbon::today())
            ->count();
    }

    /**
     * Get today's revenue
     */
    public function getTodayRevenue()
    {
        return DB::table('transactions')
            ->select(
                DB::raw('SUM(CAST(
                    (full_json::jsonb->\'tramite\'->\'datos\'->5->\'total_a_pagar\')::text
                    AS DECIMAL(10,2))
                ) as daily_revenue')
            )
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'completado')
            ->first()
            ->daily_revenue ?? 0.00;
    }

    /**
     * Apply date filter to query
     */
    private function applyDateFilter($query, $startDate = null, $endDate = null)
    {
        if ($startDate instanceof Carbon && $endDate instanceof Carbon) {
            $query->whereBetween('created_at', [
                $startDate->startOfDay(),
                $endDate->endOfDay()
            ]);
        } else {
            // Default to last 30 days if no dates provided
            $query->where('created_at', '>=', now()->subDays(30));
        }
    }
}
