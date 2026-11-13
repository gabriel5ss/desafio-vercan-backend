<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login', 301);

// Route::view('/home', 'ubold.static.static')->name('static');

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('access_login');

    Route::view('/forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->middleware('throttle:6,1')->name('password.email');

    Route::view('/reset-password', 'auth.reset-password')
        ->name('password.reset');

    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::get('/profile', [AuthController::class, 'user'])
        ->name('profile');

    Route::view('/dashboard', 'dashboard.index')->name('dashboard');
});
