<?php

namespace App\Services;

use App\Models\City;
use App\Models\Company;
use App\Models\Individual;
use App\Models\Supplier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierService
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

        return DB::transaction(function () use ($data, $supplier, $registry): Supplier {
            $supplier ??= new Supplier;
            $currentPerson = $supplier->exists ? $supplier->supplierable : null;

            if ($data['type'] === 'individual') {
                $person = $currentPerson instanceof Individual ? $currentPerson : new Individual;
                $person->fill(Arr::only($data, ['name', 'nickname', 'cpf', 'rg']))->save();
            } else {
                $person = $currentPerson instanceof Company ? $currentPerson : new Company;
                $person->fill(Arr::only($data, ['corporate_name', 'trade_name', 'cnpj', 'state_registration_indicator', 'state_registration', 'municipal_registration', 'tax_regime']))->save();
            }

            if ($person instanceof Company) {
                $person->cnpj_status = $registry['situacao'] ?? null;
                $person->save();
            }

            $city = City::with('state')->findOrFail($data['city_id']);
            $supplier->fill(Arr::only($data, [
                'state_id', 'city_id', 'active', 'postal_code', 'street', 'number', 'complement', 'district', 'reference', 'state', 'city',
                'condominium', 'condominium_address', 'condominium_number',
            ]));
            $supplier->state = $city->state->code;
            $supplier->city = $city->name;
            $supplier->observation = isset($data['observation']) ? json_encode($data['observation'], JSON_UNESCAPED_UNICODE) : null;
            $supplier->supplierable()->associate($person);
            $supplier->save();

            $supplier->contacts()->delete();
            $this->saveContact($supplier, $data['primary_contact'], true);
            foreach ($data['additional_contacts'] ?? [] as $contact) {
                $this->saveContact($supplier, $contact, false);
            }

            if ($currentPerson && $currentPerson::class !== $person::class) {
                $currentPerson->delete();
            }

            return $supplier->load(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods']);
        });
    }

    public function delete(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier): void {
            $person = $supplier->supplierable;
            $supplier->delete();
            $person?->delete();
        });
    }

    /** @param array<string, mixed> $data */
    private function saveContact(Supplier $supplier, array $data, bool $is_primary): void
    {
        $contact = $supplier->contacts()->create([
            'is_primary' => $is_primary,
            'name' => $is_primary ? null : $data['name'],
            'company' => $is_primary ? null : ($data['company'] ?? null),
            'position' => $is_primary ? null : ($data['position'] ?? null),
        ]);

        foreach (['phones' => 'phone', 'emails' => 'email'] as $field => $kind) {
            $contact->methods()->createMany(array_map(
                fn (array $method): array => [
                    'kind' => $kind,
                    'value' => $method['value'],
                    'label' => $method['label'] ?? null,
                ],
                $data[$field] ?? [],
            ));
        }
    }
}
