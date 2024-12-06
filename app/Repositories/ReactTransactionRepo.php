<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReactTransactionRepo
{
    /**
     * Get transactions revenue data within a date range
     */
    // app/Repositories/ReactTransactionRepo.php

    public function getRevenueData($startDate = null, $endDate = null)
    {
        try {
            $query = DB::table('transactions')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COALESCE(SUM(
                    CASE
                        WHEN full_json->\'tramite\'->\'datos\' IS NOT NULL
                        AND jsonb_array_length(full_json->\'tramite\'->\'datos\') >= 6
                        THEN CAST(full_json#>\'{tramite,datos,5,total_a_pagar}\' AS DECIMAL(10,2))
                        ELSE 0
                    END
                ), 0) as total')
                )
                ->where('status', 'completado');

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            return $query->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

        } catch (\Exception $e) {
            Log::error('Revenue data query failed', [
                'error' => $e->getMessage(),
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            throw $e;
        }
    }



    /**
     * Get distribution of transactions by person type within a date range
     */
    public function getPersonTypeDistribution($startDate = null, $endDate = null)
    {
        $query = Transaction::select(
            'person_type',
            DB::raw('COUNT(*) as total')
        );

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $result = $query->groupBy('person_type')->get();
        $total = $result->sum('total');

        return $result->map(function ($item) use ($total) {
            $item->percentage = $total > 0 ? round(($item->total * 100.0) / $total, 2) : 0;
            $item->display_name = $item->person_type === 'persona_natural' ? 'Natural' : 'Jurídica';
            return $item;
        });
    }

    public function getDocumentTypeDistribution($startDate = null, $endDate = null)
    {
        $query = DB::table('transactions')
            ->select(
                'document_type',
                DB::raw('COUNT(*) as value'),
                DB::raw('ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM transactions), 2) as percentage')
            );

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        return $query->groupBy('document_type')
            ->get()
            ->map(function ($item) {
                $displayNames = [
                    'dui' => 'DUI',
                    'passport' => 'Pasaporte',
                    'nit' => 'NIT'
                ];
                $item->name = $displayNames[$item->document_type] ?? $item->document_type;
                return $item;
            });
    }

    public function getTransactionsByDepartment($startDate = null, $endDate = null)
    {
        $query = DB::table('transactions')
            ->select(
                DB::raw("jsonb_extract_path_text(full_json, 'tramite', 'datos', 'departamento_y_municipio', 'cstateName') as department"),
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull(DB::raw("jsonb_extract_path_text(full_json, 'tramite', 'datos', 'departamento_y_municipio', 'cstateName')"));

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        return $query->groupBy('department')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->department,
                    'total' => $item->total
                ];
            });
    }

}
