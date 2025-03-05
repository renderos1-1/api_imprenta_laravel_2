<?php

use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\Api\ChartDataController;
use App\Http\Controllers\ReactDashboardController;
use App\Services\PdfExportService;
use Illuminate\Support\Facades\Route;

// Remove auth middleware temporarily for testing
Route::post('/chart-data/revenue', [ChartDataController::class, 'getRevenueData']);
Route::post('/chart-data/person-type', [ChartDataController::class, 'getPersonTypeData']);
Route::post('/chart-data/document-type', [ChartDataController::class, 'getDocumentTypeData']);
Route::post('/chart-data/departments', [ChartDataController::class, 'getDepartmentsData']);
Route::post('/chart-data/status-distribution', [ChartDataController::class, 'getStatusDistributionData']);
Route::post('/chart-data/destination-type', [ChartDataController::class, 'getDestinationTypeData']);


//Dashboard routes
Route::get('/dashboard/stats', [ReactDashboardController::class, 'getStats']);
Route::post('/dashboard/transactions', [ReactDashboardController::class, 'getTransactions']);


//Export Routes
Route::post('/export/{type}', [ExportController::class, 'export'])
    ->name('export.chart')
    ->where('type', 'revenue|person-type|document-type|department|stage-duration|stage-duration');

//PERMISSIONS
Route::middleware(['auth:sanctum'])->group(function () {
    // User management routes - require 'manage-users' permission
    Route::middleware(['permission:manage-users'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::get('roles-list', [UserController::class, 'roles']);
    });

    // Role management routes - require 'manage-roles' permission
    Route::middleware(['permission:manage-roles'])->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions-list', [RoleController::class, 'permissions']);
    });
});
