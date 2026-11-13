<?php

declare(strict_types=1);

namespace Modules\Supplier\Services;

use App\Services\ReceitaWsService;
use Facades\Modules\Supplier\Services\Actions\SupplierAction;
use Facades\Modules\Supplier\Services\Actions\SupplierContactAction;
use Facades\Modules\Supplier\Services\Actions\SupplierDeleteAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Supplier\Models\Supplier;
use Throwable;

class SupplierService
{
    public function __construct(private readonly ReceitaWsService $receitaWs)
    {
    }

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Supplier $supplier = null): void
    {
        $registry = null;
        if ($data['type'] === 'company') {
            $registry = $this->receitaWs->lookup($data['cnpj']);
            if (! $registry) {
                throw ValidationException::withMessages(['cnpj' => ['O CNPJ informado não pôde ser validado. Verifique o número e tente novamente.']]);
            }
        }

        $supplier ??= new Supplier();

        DB::beginTransaction();

        try {
            // adicionado para evitar situações onde
            // dois usuarios modifiquem o mesmo registro
            if ($supplier->exists) {
                $supplier->refreshForUpdate();
            }

            $supplier = SupplierAction::handle($data, $supplier, $registry);

            SupplierContactAction::handle($supplier, $data);

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function delete(Supplier $supplier): void
    {
        DB::beginTransaction();

        try {
            SupplierDeleteAction::handle($supplier);

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
