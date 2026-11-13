<?php

declare(strict_types=1);

namespace Modules\Supplier\Http\Requests;

use App\Services\Rules\CnpjValidatorRule;
use App\Services\Rules\CpfValidatorRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $routeSupplier = $this->route('supplier');

        $supplier = $routeSupplier instanceof Supplier
            ? $routeSupplier->loadMissing('supplierable')
            : ($routeSupplier ? Supplier::with('supplierable')->find($routeSupplier) : null);

        $person = $supplier?->supplierable;

        $individualId = $person instanceof Individual ? $person->id : 'NULL';

        $companyId = $person instanceof Company ? $person->id : 'NULL';

        return [
            'type' => ['required', 'in:individual,company'],
            'name' => ['required_if:type,individual', 'nullable', 'string', 'max:255'],
            'cpf' => [
                'required_if:type,individual',
                'nullable',
                'digits:11',
                "unique:individuals,cpf,{$individualId},id",
                app(CpfValidatorRule::class),
            ],
            'nickname' => ['nullable', 'string', 'max:120'],
            'rg' => ['required_if:type,individual', 'nullable', 'string', 'max:30'],
            'corporate_name' => ['required_if:type,company', 'nullable', 'string', 'max:255'],
            'trade_name' => ['required_if:type,company', 'nullable', 'string', 'max:255'],
            'cnpj' => [
                'required_if:type,company',
                'nullable',
                'digits:14',
                "unique:companies,cnpj,{$companyId},id",
                app(CnpjValidatorRule::class),
            ],
            'state_registration_indicator' => ['required_if:type,company', 'nullable', 'in:contribuinte,isento,nao_contribuinte'],
            'state_registration' => ['nullable', 'string', 'max:30'],
            'municipal_registration' => ['nullable', 'string', 'max:30'],
            'tax_regime' => ['required_if:type,company', 'nullable', 'in:withheld_by_recipient,payable_by_provider'],
            'active' => ['required', 'boolean'],
            'postal_code' => ['required', 'digits:8'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:120'],
            'reference' => ['nullable', 'string', 'max:255'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id,state_id,'.$this->integer('state_id')],
            'condominium' => ['required', 'boolean'],
            'condominium_address' => ['required_if:condominium,true', 'nullable', 'string', 'max:255'],
            'condominium_number' => ['required_if:condominium,true', 'nullable', 'string', 'max:30'],
            'observation' => ['nullable', 'array'],
            'observation.content' => ['nullable', 'string'],
            'primary_contact' => ['required', 'array'],
            'primary_contact.id' => ['nullable', 'integer', 'min:1'],
            'primary_contact.phones' => ['required', 'array', 'min:1'],
            'primary_contact.phones.*.id' => ['nullable', 'integer', 'min:1'],
            'primary_contact.phones.*.value' => ['required', 'regex:/^\d{10,11}$/'],
            'primary_contact.phones.*.label' => ['nullable', 'string', 'max:30'],
            'primary_contact.emails' => ['required', 'array', 'min:1'],
            'primary_contact.emails.*.id' => ['nullable', 'integer', 'min:1'],
            'primary_contact.emails.*.value' => ['required', 'email:rfc', 'max:255'],
            'primary_contact.emails.*.label' => ['nullable', 'string', 'max:30'],
            'additional_contacts' => ['sometimes', 'array'],
            'additional_contacts.*.id' => ['nullable', 'integer', 'min:1'],
            'additional_contacts.*.name' => ['required', 'string', 'max:255'],
            'additional_contacts.*.company' => ['nullable', 'string', 'max:255'],
            'additional_contacts.*.position' => ['nullable', 'string', 'max:120'],
            'additional_contacts.*.phones' => ['sometimes', 'array'],
            'additional_contacts.*.phones.*.id' => ['nullable', 'integer', 'min:1'],
            'additional_contacts.*.phones.*.value' => ['required', 'regex:/^\d{10,11}$/'],
            'additional_contacts.*.phones.*.label' => ['required', 'string', 'max:30'],
            'additional_contacts.*.emails' => ['sometimes', 'array'],
            'additional_contacts.*.emails.*.id' => ['nullable', 'integer', 'min:1'],
            'additional_contacts.*.emails.*.value' => ['required', 'email:rfc', 'max:255'],
            'additional_contacts.*.emails.*.label' => ['required', 'string', 'max:30'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        foreach (['cpf', 'cnpj', 'postal_code'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = preg_replace('/\D/', '', $data[$field]) ?? '';
            }
        }

        if (isset($data['primary_contact']) && is_array($data['primary_contact'])) {
            $data['primary_contact'] = $this->normalizeContact($data['primary_contact']);
        }

        foreach (is_array($data['additional_contacts'] ?? null) ? $data['additional_contacts'] : [] as $index => $contact) {
            if (is_array($contact)) {
                $data['additional_contacts'][$index] = $this->normalizeContact($contact);
            }
        }

        if (($data['type'] ?? null) === 'company') {
            unset($data['name'], $data['cpf'], $data['rg']);
        } elseif (($data['type'] ?? null) === 'individual') {
            unset(
                $data['corporate_name'], $data['trade_name'], $data['cnpj'], $data['cnpj_status'],
                $data['state_registration_indicator'], $data['state_registration'],
                $data['municipal_registration'], $data['tax_regime'],
            );
        }

        $this->replace($data);
    }

    /** @param array<string, mixed> $contact
     * @return array<string, mixed>
     */
    private function normalizeContact(array $contact): array
    {
        foreach (['phones', 'emails'] as $kind) {
            foreach ($contact[$kind] ?? [] as $index => $method) {
                if (! is_array($method)) {
                    continue;
                }
                if ($kind === 'phones' && isset($method['value']) && is_string($method['value'])) {
                    $contact[$kind][$index]['value'] = preg_replace('/\D/', '', $method['value']) ?? '';
                } elseif ($kind === 'emails' && isset($method['value']) && is_string($method['value'])) {
                    $contact[$kind][$index]['value'] = mb_strtolower(mb_trim($method['value']));
                }
            }
        }

        return $contact;
    }
}
