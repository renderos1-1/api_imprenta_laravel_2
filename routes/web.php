<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');  // Redirect root to login page
});

// Guest middleware group (for non-authenticated users)
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [AuthenticatedSessionController::class, 'show'])  // Changed 'create' to 'show' to match your controller
    ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset Routes (if you plan to use them)
    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    //     ->name('password.request');
    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    //     ->name('password.email');
    // Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    //     ->name('password.reset');
    // Route::post('reset-password', [NewPasswordController::class, 'store'])
    //     ->name('password.update');
});

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout route
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Keep this if you have additional auth routes in auth.php
require __DIR__.'/auth.php';
