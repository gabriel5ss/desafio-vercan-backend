<?php

namespace App\Http\Resources;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Individual;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Supplier */
class SupplierResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $person = $this->supplierable;

        return [
            'id' => $this->id,
            'type' => $person instanceof Individual ? 'individual' : 'company',
            'name' => $person instanceof Individual ? $person->name : null,
            'cpf' => $person instanceof Individual ? $person->cpf : null,
            'nickname' => $person instanceof Individual ? $person->nickname : null,
            'rg' => $person instanceof Individual ? $person->rg : null,
            'corporate_name' => $person instanceof Individual ? null : $person?->corporate_name,
            'trade_name' => $person instanceof Individual ? $person->nickname : $person?->trade_name,
            'cnpj' => $person instanceof Individual ? null : $person?->cnpj,
            'state_registration_indicator' => $person instanceof Company ? $person->state_registration_indicator : null,
            'state_registration' => $person instanceof Company ? $person->state_registration : null,
            'municipal_registration' => $person instanceof Company ? $person->municipal_registration : null,
            'tax_regime' => $person instanceof Company ? $person->tax_regime : null,
            'cnpj_status' => $person instanceof Company ? $person->cnpj_status : null,
            'active' => $this->active,
            'postal_code' => $this->postal_code,
            'street' => $this->street,
            'number' => $this->number,
            'complement' => $this->complement,
            'district' => $this->district,
            'reference' => $this->reference,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'state' => $this->stateInfo->code ?? $this->state,
            'city' => $this->cityInfo->name ?? $this->city,
            'condominium' => $this->condominium,
            'condominium_address' => $this->condominium_address,
            'condominium_number' => $this->condominium_number,
            'observation' => $this->observation ? json_decode($this->observation, true) : null,
            'primary_contact' => $this->contacts->firstWhere('is_primary', true) ? $this->contactData($this->contacts->firstWhere('is_primary', true)) : null,
            'additional_contacts' => $this->contacts->where('is_primary', false)->values()->map(fn (Contact $contact): array => $this->contactData($contact)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /** @return array<string, mixed> */
    private function contactData(Contact $contact): array
    {
        return [
            'name' => $contact->name,
            'company' => $contact->company,
            'position' => $contact->position,
            'phones' => $contact->methods->where('kind', 'phone')->values()->map(fn ($method): array => ['value' => $method->value, 'label' => $method->label]),
            'emails' => $contact->methods->where('kind', 'email')->values()->map(fn ($method): array => ['value' => $method->value, 'label' => $method->label]),
        ];
    }
}
