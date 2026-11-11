<?php

declare(strict_types=1);

namespace Modules\Supplier\Services\Actions;

use App\Models\City;
use Illuminate\Support\Arr;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;

final class SupplierAction
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $registry
     */
    public function handle(array $data, ?Supplier $supplier = null, ?array $registry = null): Supplier
    {
        $supplier ??= new Supplier();
        $currentPerson = $supplier->exists ? $supplier->supplierable : null;

        if ($data['type'] === 'individual') {
            $person = $currentPerson instanceof Individual ? $currentPerson : new Individual();
            $person->fill(Arr::only($data, ['name', 'nickname', 'cpf', 'rg']))->save();
        } else {
            $person = $currentPerson instanceof Company ? $currentPerson : new Company();
            $person->fill(Arr::only($data, ['corporate_name', 'trade_name', 'cnpj', 'state_registration_indicator', 'state_registration', 'municipal_registration', 'tax_regime']))->save();
        }

        if ($person instanceof Company) {
            $person->cnpj_status = $registry['situacao'] ?? null;
            $person->save();
        }

        $city = City::with('state')->findOrFail($data['city_id']);

        $supplier->fill(Arr::only($data, [
            'state_id', 'city_id', 'active', 'postal_code', 'street',
            'number', 'complement', 'district', 'reference', 'state', 'city',
            'condominium', 'condominium_address', 'condominium_number',
        ]));

        $supplier->state = $city->state->code;
        $supplier->city = $city->name;
        $supplier->observation = isset($data['observation']) ? json_encode($data['observation'], JSON_UNESCAPED_UNICODE) : null;
        $supplier->supplierable()->associate($person);
        $supplier->save();

        if ($currentPerson && $currentPerson::class !== $person::class) {
            $currentPerson->delete();
        }

        return $supplier;
    }
}
