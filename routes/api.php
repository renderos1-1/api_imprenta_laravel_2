<?php

use App\Http\Controllers\ExportController;
use App\Http\Controllers\Api\ChartDataController;
use App\Services\PdfExportService;
use Illuminate\Support\Facades\Route;

// Remove auth middleware temporarily for testing
Route::post('/chart-data/revenue', [ChartDataController::class, 'getRevenueData']);
Route::post('/chart-data/person-type', [ChartDataController::class, 'getPersonTypeData']);
Route::post('/chart-data/document-type', [ChartDataController::class, 'getDocumentTypeData']);
Route::post('/chart-data/department', [ChartDataController::class, 'getDepartmentData']);
Route::post('/chart-data/stage-duration', [ChartDataController::class, 'getStageDurationData']);



//Export Routes
Route::post('/export/{type}', [ExportController::class, 'export'])
    ->name('export.chart');


// Add a test route to verify API is working
Route::get('/test', function() {
    return response()->json(['status' => 'API is working']);
});


Route::get('/test-pdf', function(PdfExportService $service) {
    return $service->generateRevenueReport(
        '2024-01-01',
        '2024-12-31'
    )->stream();
});