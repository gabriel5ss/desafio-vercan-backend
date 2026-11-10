<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\SupplierController;

Route::middleware('auth')->group(function (): void {
    Route::get('/suppliers/cnpj/{cnpj}', [SupplierController::class, 'lookupCnpj'])
        ->where('cnpj', '\\d{14}')
        ->name('suppliers.lookup-cnpj');

    Route::get('/suppliers', [SupplierController::class, 'indexApi'])->name('suppliers.index');
});
