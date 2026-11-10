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

final class SupplierService
{
    public function __construct(private readonly ReceitaWsService $receitaWs) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Supplier $supplier = null): Supplier
    {
        $registry = null;
        if ($data['type'] === 'company') {
            $registry = $this->receitaWs->lookup($data['cnpj']);
            if (! $registry) {
                throw ValidationException::withMessages(['cnpj' => ['O CNPJ informado não pôde ser validado. Verifique o número e tente novamente.']]);
            }
        }

        DB::beginTransaction();

        try {
            $supplier = SupplierAction::handle($data, $supplier, $registry);

            $supplier->contacts()->delete();
            SupplierContactAction::handle($supplier, $data['primary_contact'], true);
            foreach ($data['additional_contacts'] ?? [] as $contact) {
                SupplierContactAction::handle($supplier, $contact, false);
            }

            $supplier->load(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods']);

            DB::commit();

            return $supplier;
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
