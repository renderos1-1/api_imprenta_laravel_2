<?php

use App\Http\Controllers\Api\ChartDataController;
use Illuminate\Support\Facades\Route;

// Remove auth middleware temporarily for testing
Route::post('/chart-data/revenue', [ChartDataController::class, 'getRevenueData']);
Route::post('/chart-data/person-type', [ChartDataController::class, 'getPersonTypeData']);
Route::post('/chart-data/document-type', [ChartDataController::class, 'getDocumentTypeData']);
Route::post('/chart-data/department', [ChartDataController::class, 'getDepartmentData']);

// Add a test route to verify API is working
Route::get('/test', function() {
    return response()->json(['status' => 'API is working']);
});
