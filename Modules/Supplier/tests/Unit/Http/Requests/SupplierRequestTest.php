<?php

namespace Modules\Supplier\Tests\Unit\Http\Requests;

use Closure;
use Modules\Supplier\Http\Requests\SupplierRequest;
use Override;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Stringable;

class SupplierRequestTest extends TestCase
{
    protected SupplierRequest $request;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new SupplierRequest;
    }

    public function test_authorize(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    #[TestWith([true, 'required'], 'condominium requires address and number')]
    #[TestWith([false, ''], 'without condominium fields are optional')]
    public function test_rules(bool $condominium, string $required): void
    {
        $this->request->merge(['condominium' => $condominium, 'state_id' => 35]);
        $rules = [
            'type' => ['required', 'in:"individual","company"'],
            'name' => ['required_if:type,individual', 'nullable', 'string', 'max:255'],
            'cpf' => ['required_if:type,individual', 'nullable', 'digits:11', 'unique:individuals,cpf,NULL,id', Closure::class],
            'nickname' => ['nullable', 'string', 'max:120'],
            'rg' => ['required_if:type,individual', 'nullable', 'string', 'max:30'],
            'corporate_name' => ['required_if:type,company', 'nullable', 'string', 'max:255'],
            'trade_name' => ['required_if:type,company', 'nullable', 'string', 'max:255'],
            'cnpj' => ['required_if:type,company', 'nullable', 'digits:14', 'unique:companies,cnpj,NULL,id', Closure::class],
            'state_registration_indicator' => ['required_if:type,company', 'nullable', 'in:"contribuinte","isento","nao_contribuinte"'],
            'state_registration' => ['nullable', 'string', 'max:30'],
            'municipal_registration' => ['nullable', 'string', 'max:30'],
            'tax_regime' => ['required_if:type,company', 'nullable', 'in:"withheld_by_recipient","payable_by_provider"'],
            'active' => ['required', 'boolean'],
            'postal_code' => ['required', 'digits:8'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:120'],
            'reference' => ['nullable', 'string', 'max:255'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id,state_id,"35"'],
            'condominium' => ['required', 'boolean'],
            'condominium_address' => [$required, 'nullable', 'string', 'max:255'],
            'condominium_number' => [$required, 'nullable', 'string', 'max:30'],
            'observation' => ['nullable', 'array'],
            'observation.content' => ['nullable', 'string'],
            'primary_contact' => ['required', 'array'],
            'primary_contact.phones' => ['required', 'array', 'min:1'],
            'primary_contact.phones.*.value' => ['required', 'regex:/^\d{10,11}$/'],
            'primary_contact.phones.*.label' => ['nullable', 'string', 'max:30'],
            'primary_contact.emails' => ['required', 'array', 'min:1'],
            'primary_contact.emails.*.value' => ['required', 'email:rfc', 'max:255'],
            'primary_contact.emails.*.label' => ['nullable', 'string', 'max:30'],
            'additional_contacts' => ['sometimes', 'array'],
            'additional_contacts.*.name' => ['required', 'string', 'max:255'],
            'additional_contacts.*.company' => ['nullable', 'string', 'max:255'],
            'additional_contacts.*.position' => ['nullable', 'string', 'max:120'],
            'additional_contacts.*.phones' => ['sometimes', 'array'],
            'additional_contacts.*.phones.*.value' => ['required', 'regex:/^\d{10,11}$/'],
            'additional_contacts.*.phones.*.label' => ['required', 'string', 'max:30'],
            'additional_contacts.*.emails' => ['sometimes', 'array'],
            'additional_contacts.*.emails.*.value' => ['required', 'email:rfc', 'max:255'],
            'additional_contacts.*.emails.*.label' => ['required', 'string', 'max:30'],
        ];

        $actualRules = $this->request->rules();
        array_walk_recursive($actualRules, function (mixed &$rule): void {
            $rule = match (true) {
                $rule instanceof Closure => Closure::class,
                $rule instanceof Stringable => (string) $rule,
                default => $rule,
            };
        });

        $this->assertSame($rules, $actualRules);
    }
}
