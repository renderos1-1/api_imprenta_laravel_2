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
            ->selectRaw("
            datos.value->'departamento_y_municipio'->>'cstateName' as department,
            COUNT(*) as total
        ")
            ->fromRaw("
            transactions,
            jsonb_array_elements(full_json->'tramite'->'datos') as datos
        ")
            ->whereRaw("datos.value->>'departamento_y_municipio' IS NOT NULL");

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        // Add debug logging
        \Log::info('Department query:', ['sql' => $query->toSql()]);

        return $query->groupBy('department')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->department ?? 'No Departamento',
                    'total' => $item->total
                ];
            });
    }

    public function getAverageStageDuration($startDate = null, $endDate = null)
    {
        // Define the correct order of stages
        $stageOrder = [
            'Solicitud' => 1,
            'Revisión de documentos' => 2,
            'Subsanar Observaciones' => 3,
            'Revisar observaciones' => 4,
            'Cotización' => 5,
            'Ciudadano revisa cotización' => 6,
            'Adjuntar mandamiento de pago' => 7,
            'Revisión de pago' => 8,
            'Notificación de publicación (resolutor)' => 9
        ];

        $query = DB::table('transactions')
            ->selectRaw("
            etapas.value->'tarea'->>'nombre' as stage_name,
            AVG(
                EXTRACT(EPOCH FROM
                    (etapas.value->>'fecha_termino')::timestamp -
                    (etapas.value->>'fecha_inicio')::timestamp
                )/60
            ) as average_duration_minutes
        ")
            ->fromRaw("
            transactions,
            jsonb_array_elements(full_json->'tramite'->'etapas') as etapas
        ")
            ->whereRaw("etapas.value->>'estado' = 'completado'");

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        $result = $query->groupBy('stage_name')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->stage_name,
                    'duration' => round($item->average_duration_minutes, 2)
                ];
            });

        // Sort the results according to the defined order
        return $result->sort(function ($a, $b) use ($stageOrder) {
            $orderA = $stageOrder[$a['name']] ?? 999;
            $orderB = $stageOrder[$b['name']] ?? 999;
            return $orderA - $orderB;
        })->values();
    }


}
