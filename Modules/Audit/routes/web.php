<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Audit\App\Http\Controllers\AuditController;

Route::middleware('auth')->group(function (): void {
    Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');
});
