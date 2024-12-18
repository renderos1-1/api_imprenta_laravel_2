<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReactTransactionRepo
{


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
            $query->whereBetween('start_date', [
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

    public function getDestinationTypeDistribution($startDate = null, $endDate = null)
    {
        try {
            // Start building the base query
            $query = DB::table('transactions')
                ->crossJoin(DB::raw("
                LATERAL jsonb_array_elements(
                    CASE
                        WHEN jsonb_typeof(full_json->'datos') = 'array'
                        THEN full_json->'datos'
                        ELSE '[]'::jsonb
                    END
                ) AS value
            "))
                ->whereRaw("value->>'destinatario' IS NOT NULL");

            // Add date range filter if provided
            if ($startDate && $endDate) {
                $query->whereBetween('transactions.start_date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            // Get the results with proper naming and calculations
            $results = $query
                ->select(
                    DB::raw("
                    CASE
                        WHEN value->>'destinatario' = 'para_mi' THEN 'Trámite Personal'
                        WHEN value->>'destinatario' = 'para_tercero' THEN 'Trámite para Terceros'
                        ELSE value->>'destinatario'
                    END as display_name
                "),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy(DB::raw("value->>'destinatario'"))
                ->get();

            // Calculate total for percentage
            $total = $results->sum('total');

            // Transform results adding percentage
            return $results->map(function ($item) use ($total) {
                return [
                    'name' => $item->display_name,
                    'value' => $item->total,
                    'percentage' => $total > 0
                        ? round(($item->total * 100.0) / $total, 2)
                        : 0
                ];
            });

        } catch (\Exception $e) {
            Log::error('Error getting destination type distribution', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    public function getHierarchicalTransactionsByDepartment($startDate = null, $endDate = null)
    {
        try {
            // First get department totals
            $departmentQuery = DB::table('transactions')
                ->select([
                    'state_code',
                    'state_name',
                    DB::raw('COUNT(*) as total_transactions')
                ])
                ->whereNotNull('state_name');

            // Apply date filters if provided
            if ($startDate && $endDate) {
                $departmentQuery->whereBetween('start_date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            $departments = $departmentQuery
                ->groupBy('state_code', 'state_name')
                ->get();

            // Now get municipality data for each department
            foreach ($departments as $dept) {
                $municipalityQuery = DB::table('transactions')
                    ->select([
                        'city_code',
                        'city_name',
                        DB::raw('COUNT(*) as transactions'),
                        // Include person_type to split between personal and third party
                        DB::raw("SUM(CASE WHEN person_type = 'persona_natural' THEN 1 ELSE 0 END) as personal_transactions"),
                        DB::raw("SUM(CASE WHEN person_type = 'persona_juridica' THEN 1 ELSE 0 END) as third_party_transactions")
                    ])
                    ->where('state_code', $dept->state_code);

                // Apply same date filters
                if ($startDate && $endDate) {
                    $municipalityQuery->whereBetween('created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }

                $municipalities = $municipalityQuery
                    ->groupBy('city_code', 'city_name')
                    ->get();

                // Add municipalities to department object
                $dept->municipalities = $municipalities;

                // Add split between personal and third party at department level
                $dept->personal_transactions = $municipalities->sum('personal_transactions');
                $dept->third_party_transactions = $municipalities->sum('third_party_transactions');
            }

            Log::info('Hierarchical department data retrieved', [
                'department_count' => $departments->count()
            ]);

            return $departments;

        } catch (\Exception $e) {
            Log::error('Error getting hierarchical department data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getTransactionStatusDistribution($startDate = null, $endDate = null)
    {
        try {
            $query = DB::table('transactions')
                ->select(
                    DB::raw('DATE(start_date) as date'), // Changed from created_at to start_date
                    'status',
                    DB::raw('COUNT(*) as count')
                );

            if ($startDate && $endDate) {
                $query->whereBetween('start_date', [ // Changed from created_at to start_date
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            $results = $query->groupBy('date', 'status')
                ->orderBy('date', 'asc')
                ->get();

            // Restructure data for the chart
            $dateGroups = $results->groupBy('date');

            return $dateGroups->map(function ($group) {
                $date = $group[0]->date;
                $pendingCount = $group->firstWhere('status', 'pendiente')?->count ?? 0;
                $completedCount = $group->firstWhere('status', 'completado')?->count ?? 0;

                return [
                    'date' => $date,
                    'Pendientes' => $pendingCount,
                    'Completados' => $completedCount,
                    'Total' => $pendingCount + $completedCount
                ];
            })->values();

        } catch (\Exception $e) {
            Log::error('Transaction status distribution query failed', [
                'error' => $e->getMessage(),
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            throw $e;
        }
    }

    public function getDetailedPersonTypeData($startDate = null, $endDate = null)
    {
        try {
            $query = DB::table('transactions')
                ->select([
                    'created_at',
                    'id',
                    'document_type',
                    'person_type',
                    'document_number',
                    'status',
                    'full_json'
                ]);

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            Log::info('Executing person type query', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            $result = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function($item) {
                    $jsonData = json_decode($item->full_json);
                    return (object)[
                        'created_at' => $item->created_at,
                        'transaction_id' => $item->id,
                        'document_type' => $item->document_type,
                        'document_number' => $item->document_number,
                        'person_type' => $item->person_type,
                        'status' => $item->status,
                        'tramite_id' => $jsonData->tramite->id ?? null
                    ];
                });

            Log::info('Query result count', [
                'count' => $result->count()
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error getting detailed person type data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get transactions count for a specific period
     */
    public function getTransactionsCount($period = 'today')
    {
        try {
            $query = DB::table('transactions');

            if ($period === 'today') {
                $query->whereDate('start_date', today());
            }

            return $query->count();
        } catch (\Exception $e) {
            Log::error('Error getting transactions count', [
                'error' => $e->getMessage(),
                'period' => $period
            ]);
            throw $e;
        }
    }

    /**
     * Calculate percentage change in transactions compared to previous day
     */
    public function getTransactionsChange()
    {
        try {
            $today = $this->getTransactionsCount('today');
            $yesterday = DB::table('transactions')
                ->whereDate('start_date', today()->subDay())
                ->count();

            if ($yesterday === 0) return 0;
            return round((($today - $yesterday) / $yesterday) * 100, 1);
        } catch (\Exception $e) {
            Log::error('Error calculating transactions change', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    public function getProcessedDocumentsCount()
    {
        try {
            return DB::table('transactions')
                ->where('status', 'completado')
                ->whereMonth('created_at', now()->month)
                ->count();
        } catch (\Exception $e) {
            Log::error('Error getting processed documents count', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    public function getProcessedDocumentsChange()
    {
        try {
            $today = DB::table('transactions')
                ->where('status', 'completado')
                ->whereDate('created_at', today())
                ->count();

            $yesterday = DB::table('transactions')
                ->where('status', 'completado')
                ->whereDate('created_at', today()->subDay())
                ->count();

            if ($yesterday === 0) return 0;
            return round((($today - $yesterday) / $yesterday) * 100, 1);
        } catch (\Exception $e) {
            Log::error('Error calculating processed documents change', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    public function getRevenueToday()
    {
        try {
            return DB::table('transactions')
                ->whereDate('created_at', today())
                ->where('status', 'completado')
                ->sum(DB::raw('CAST(full_json#>\'{tramite,datos,5,total_a_pagar}\' AS DECIMAL(10,2))'));
        } catch (\Exception $e) {
            Log::error('Error getting today revenue', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    public function getRevenueChange()
    {
        try {
            $today = $this->getRevenueToday();

            $yesterday = DB::table('transactions')
                ->whereDate('created_at', today()->subDay())
                ->where('status', 'completado')
                ->sum(DB::raw('CAST(full_json#>\'{tramite,datos,5,total_a_pagar}\' AS DECIMAL(10,2))'));

            if ($yesterday === 0) return 0;
            return round((($today - $yesterday) / $yesterday) * 100, 1);
        } catch (\Exception $e) {
            Log::error('Error calculating revenue change', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    public function getTransactionsPerDay($startDate = null, $endDate = null)
    {
        try {
            $query = DB::table('transactions')
                ->select(
                    DB::raw('DATE(start_date) as date'),
                    DB::raw('COUNT(*) as total')
                );

            if ($startDate && $endDate) {
                $query->whereBetween('start_date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }

            return $query->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting transactions per day', [
                'error' => $e->getMessage(),
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            throw $e;
        }
    }




}
