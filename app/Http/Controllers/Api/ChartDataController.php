<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ReactTransactionRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChartDataController extends Controller
{
    protected ReactTransactionRepo $transactionRepository;

    public function __construct(ReactTransactionRepo $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getRevenueData(Request $request)
    {
        try {
            // Log the incoming request
            Log::info('Revenue request received', [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date')
            ]);

            // Basic validation
            if (!$request->filled(['start_date', 'end_date'])) {
                return response()->json([
                    'error' => 'Missing required dates'
                ], 400);
            }

            // Get data from repository with error catching
            try {
                $data = $this->transactionRepository->getRevenueData(
                    $request->input('start_date'),
                    $request->input('end_date')
                );

                // Log successful query
                Log::info('Revenue data retrieved', [
                    'count' => count($data),
                    'first_record' => $data->first()
                ]);

                return response()->json($data);

            } catch (\Exception $e) {
                Log::error('Repository error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Controller error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error retrieving revenue data',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function getDestinationTypeData(Request $request)
    {
        try {
            Log::info('Destination type data request received', $request->all());

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $data = $this->transactionRepository->getDestinationTypeDistribution($startDate, $endDate);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in getDestinationTypeData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error retrieving destination type data'
            ], 500);
        }
    }


    public function getPersonTypeData(Request $request)
    {
        try {
            // Log incoming request
            Log::info('Person type data request received', [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date')
            ]);

            // Validate dates
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $data = $this->transactionRepository->getPersonTypeDistribution(
                $validated['start_date'],
                $validated['end_date']
            );

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('Error in getPersonTypeData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error retrieving person type data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getDocumentTypeData(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $data = $this->transactionRepository->getDocumentTypeDistribution($startDate, $endDate);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in getDocumentTypeData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error retrieving document type data'
            ], 500);
        }
    }

    public function getDepartmentsData(Request $request)
    {
        try {
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            $data = $this->transactionRepository->getHierarchicalTransactionsByDepartment(
                $startDate,
                $endDate
            );

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in getDepartmentsData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error retrieving department data'
            ], 500);
        }
    }

    public function getStatusDistributionData(Request $request)
    {
        try {
            Log::info('Status distribution data request received', $request->all());

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $data = $this->transactionRepository->getTransactionStatusDistribution($startDate, $endDate);

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in getStatusDistributionData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error retrieving status distribution data'
            ], 500);
        }
    }



}
