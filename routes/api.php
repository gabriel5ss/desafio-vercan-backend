<?php

declare(strict_types=1);

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::post('/forgot-password', [PasswordResetController::class, 'sendCode'])
    ->middleware('throttle:6,1');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('throttle:6,1');

Route::middleware('auth')->group(function (): void {
    Route::get('/locations/states', [LocationController::class, 'states']);
    Route::get('/locations/states/{state}/cities', [LocationController::class, 'cities'])
        ->whereNumber('state');
});
