<?php

declare(strict_types=1);

namespace Modules\Supplier\Services\Actions;

use Modules\Supplier\Models\Supplier;

final class SupplierDeleteAction
{
    public function handle(Supplier $supplier): void
    {
        $person = $supplier->supplierable;
        $supplier->delete();
        $person?->delete();
    }
}
