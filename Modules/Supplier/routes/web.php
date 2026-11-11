<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\SupplierController;

Route::middleware(['auth'])->group(function (): void {
    Route::resource('suppliers', SupplierController::class)
        ->names('suppliers');
});
