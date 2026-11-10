<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login', 301);

// Route::view('/home', 'ubold.static.static')->name('static');

Route::middleware('guest')->group(function (): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('access_login');

    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
        ->middleware('throttle:6,1')->name('password.email');
    Route::view('/reset-password', 'auth.reset-password')->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'user'])->name('profile');
    Route::view('/dashboard', 'dashboard.index')->name('dashboard');

    // Route::get('/suppliers', [App\Http\Controllers\Api\SupplierController::class, 'index'])->name('suppliers.index');
    // Route::get('/suppliers/create', [App\Http\Controllers\Api\SupplierController::class, 'create'])->name('suppliers.create');
    // Route::post('/suppliers', [App\Http\Controllers\Api\SupplierController::class, 'store'])->name('suppliers.store');
    // Route::get('/suppliers/{supplier}/edit', [App\Http\Controllers\Api\SupplierController::class, 'edit'])->whereNumber('supplier')->name('suppliers.edit');
    // Route::get('/suppliers/{supplier}', [App\Http\Controllers\Api\SupplierController::class, 'show'])->whereNumber('supplier')->name('suppliers.show');
    // Route::put('/suppliers/{supplier}', [App\Http\Controllers\Api\SupplierController::class, 'update'])->whereNumber('supplier')->name('suppliers.update');
    // Route::delete('/suppliers/{supplier}', [App\Http\Controllers\Api\SupplierController::class, 'destroy'])->whereNumber('supplier')->name('suppliers.destroy');
});
