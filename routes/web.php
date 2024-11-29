<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

    // User management routes
    Route::get('/adminuser', [UserController::class, 'index'])->name('adminuser');
    Route::resource('users', UserController::class)->except(['index']);

    Route::get('/estadisticas', function () {
        return view('estadisticas');
    })->name('estadisticas');

    Route::get('/estadisticas2', function () {
        return view('estadisticas2');
    })->name('estadisticas2');

    Route::get('/graficos', function () {
        return view('graficos');
    })->name('graficos');

    Route::get('/transacciones', function () {
        return view('transacciones');
    })->name('transacciones');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
