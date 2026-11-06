<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

Route::middleware('auth')->group(function (): void {
    // Route::get('/user', [AuthController::class, 'user']);
    // Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/locations/states', [LocationController::class, 'states']);
    Route::get('/locations/states/{state}/cities', [LocationController::class, 'cities'])->whereNumber('state');

    // Route::post('/suppliers', [App\Http\Controllers\Api\SupplierController::class, 'store']);
    // Route::get('/suppliers/{supplier}', [App\Http\Controllers\Api\SupplierController::class, 'show'])->whereNumber('supplier');
    // Route::put('/suppliers/{supplier}', [App\Http\Controllers\Api\SupplierController::class, 'update'])->whereNumber('supplier');
    // Route::delete('/suppliers/{supplier}', [App\Http\Controllers\Api\SupplierController::class, 'destroy'])->whereNumber('supplier');
});
