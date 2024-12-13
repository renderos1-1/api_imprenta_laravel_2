<?php

namespace App\Http\Controllers;

use App\Services\Export\DepartmentExportService;
use App\Services\Export\DocumentTypeExportService;
use App\Services\Export\RevenueExportService;
use App\Services\Export\PersonTypeExportService;
use App\Services\Export\StageDurationExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ExportController extends Controller
{
    protected array $exportServices = [
        'revenue' => RevenueExportService::class,
        'person-type' => PersonTypeExportService::class,
        'document-type' => DocumentTypeExportService::class,
        'department' => DepartmentExportService::class,
        'stage-duration' => StageDurationExportService::class,


    ];

    public function export(Request $request, string $type)
    {
        try {
            // Add validation
            $validated = $request->validate([
                'format' => 'required|string|in:pdf,excel,csv',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            Log::info('Export request received', [
                'type' => $type,
                'format' => $request->format,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'all_data' => $request->all()
            ]);

            if (!array_key_exists($type, $this->exportServices)) {
                throw new Exception('Invalid export type: ' . $type);
            }

            $serviceClass = $this->exportServices[$type];
            $service = app($serviceClass);

            $result = $service->export(
                $validated['format'],
                $validated['start_date'],
                $validated['end_date']
            );

            // Set proper headers for file download
            $headers = [
                'Content-Type' => $result['mime'],
                'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"',
                'Content-Length' => strlen($result['content']),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];

            return response($result['content'], 200, $headers);

        } catch (Exception $e) {
            Log::error('Export failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Export failed: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
