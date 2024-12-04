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

    //Estadisticas routes
    Route::get('/estadisticas', [DashboardController::class, 'graphicsChart'])
        ->name('estadisticas');

    // User management routes
    Route::get('/adminuser', [UserController::class, 'index'])->name('adminuser');
    Route::resource('users', UserController::class)->except(['index']);

    Route::get('/estadisticas2', function () {
        return view('estadisticas2', ['headerWord' => 'Estadísticas 2']);
    })->name('estadisticas2');

    Route::get('/userlog', function () {
        return view('userlog', ['headerWord' => 'Registro de actividades']);
    })->name('userlog');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');



    // Transaction routes
    Route::get('/transacciones', [TransactionController::class, 'index'])->name('transacciones'); // Cambiado el nombre para que coincida con el menú
    Route::get('/transacciones/{transaction}', [TransactionController::class, 'show'])->name('transacciones.show');

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
