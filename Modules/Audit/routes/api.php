<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Audit\App\Http\Controllers\AuditController;

Route::middleware('auth')->group(function (): void {
    Route::get('/audits/{root_type}/{root_id}', [AuditController::class, 'history'])
        ->whereNumber('root_id')
        ->name('audits.history');

    Route::get('/audits', [AuditController::class, 'indexApi'])->name('audits.index');
});
