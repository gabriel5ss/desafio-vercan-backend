<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/locations/states', [LocationController::class, 'states']);
    Route::get('/locations/states/{state}/cities', [LocationController::class, 'cities'])->whereNumber('state');

    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::get('/suppliers/cnpj/{cnpj}', [SupplierController::class, 'lookupCnpj'])->where('cnpj', '\\d{14}');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->whereNumber('supplier');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->whereNumber('supplier');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->whereNumber('supplier');
});
