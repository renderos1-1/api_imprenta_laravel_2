<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard routes
    Route::get('/dash', [DashboardController::class, 'index'])->name('dash');

    // Statistics routes
    Route::prefix('estadisticas')->group(function () {
        Route::get('/', [DashboardController::class, 'graphicsChart'])
            ->name('estadisticas');

        // Chart data API endpoints
        Route::post('/api/chart-data/{type}', [DashboardController::class, 'getChartData'])
            ->name('chart.data');

        // Export endpoints
        Route::post('/export/{type}', [DashboardController::class, 'exportChart'])
            ->name('chart.export');
    });

    // Estadisticas 2 route
    Route::get('/estadisticas2', function () {
        return view('estadisticas2', ['headerWord' => 'Estadísticas 2']);
    })->name('estadisticas2');

    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/admin', [UserController::class, 'index'])->name('adminuser');
        Route::resource('/', UserController::class)->except(['index']);
    });

    // User log route
    Route::get('/userlog', function () {
        return view('userlog', ['headerWord' => 'Registro de actividades']);
    })->name('userlog');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Transaction routes
    Route::prefix('transacciones')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])
            ->name('transacciones');
        Route::get('/{transaction}', [TransactionController::class, 'show'])
            ->name('transacciones.show');
    });

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])
            ->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
