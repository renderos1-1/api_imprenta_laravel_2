<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\RoleController;

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
    // In web.php
    Route::middleware(['auth'])->group(function () {
        Route::get('/estadisticas', [DashboardController::class, 'graphicsChart'])
            ->name('estadisticas');
    });

    // Estadisticas 2 route
    Route::get('/estadisticas2', function () {
        return view('estadisticas2', ['headerWord' => 'Estadísticas 2']);
    })->name('estadisticas2');

    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/admin', [UserController::class, 'index'])->name('adminuser');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // User log route
    Route::get('/userlog', [UserLogController::class, 'index'])->name('userlog');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Transaction routes
    Route::prefix('transacciones')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('transacciones');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('transacciones.show');
    });

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::resource('roles', RoleController::class);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
