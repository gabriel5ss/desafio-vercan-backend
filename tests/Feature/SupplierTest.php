<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private State $state;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = State::create(['ibge_code' => 35, 'code' => 'SP', 'name' => 'São Paulo']);
        $this->city = City::create(['ibge_code' => 3509502, 'state_id' => $this->state->id, 'name' => 'Campinas']);
    }

    public function test_authenticated_user_can_create_and_read_a_physical_person_supplier(): void
    {
        $response = $this->withToken($this->token())->postJson('/api/suppliers', $this->supplierData());

        $response->assertCreated()
            ->assertJsonPath('data.type', 'individual')
            ->assertJsonPath('data.cpf', '52998224725')
            ->assertJsonPath('data.primary_contact.phones.0.value', '19987654321')
            ->assertJsonPath('data.primary_contact.phones.1.value', '1932345678')
            ->assertJsonPath('data.primary_contact.emails.0.value', 'contato@example.com')
            ->assertJsonPath('data.additional_contacts.0.emails.0.value', 'joao@example.com');

        $supplierId = $response->json('data.id');
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplierId,
            'supplierable_type' => 'individual',
        ]);
        $this->assertDatabaseHas('individuals', ['cpf' => '52998224725', 'name' => 'Maria Exemplo', 'nickname' => 'Mari', 'rg' => '123456789']);
        $this->assertDatabaseHas('suppliers', ['id' => $supplierId, 'condominium_address' => 'Bloco A', 'condominium_number' => '12']);
        $this->assertDatabaseMissing('suppliers', ['tax_regime' => 'withheld_by_recipient']);

        $this->withToken($this->token())->getJson("/api/suppliers/{$supplierId}")
            ->assertOk()
            ->assertJsonPath('data.city', 'Campinas');
        $this->withToken($this->token())->getJson('/api/suppliers?search=Maria')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Maria Exemplo');
    }

    public function test_authenticated_user_can_create_a_company_supplier_after_registry_lookup(): void
    {
        Http::fake(['receitaws.com.br/*' => Http::response([
            'status' => 'OK',
            'nome' => 'Empresa Exemplo Ltda',
            'fantasia' => 'Exemplo',
            'situacao' => 'ATIVA',
        ])]);

        $response = $this->withToken($this->token())->postJson('/api/suppliers', $this->supplierData('company'));

        $response->assertCreated()
            ->assertJsonPath('data.type', 'company')
            ->assertJsonPath('data.cnpj', '11222333000181')
            ->assertJsonPath('data.cnpj_status', 'ATIVA');
        $this->assertDatabaseHas('suppliers', ['supplierable_type' => 'company']);
        $this->assertDatabaseHas('companies', ['cnpj' => '11222333000181', 'state_registration_indicator' => 'contribuinte', 'tax_regime' => 'withheld_by_recipient']);

        $this->withToken($this->token())->getJson('/api/suppliers/cnpj/11222333000181')
            ->assertOk()
            ->assertJsonPath('data.corporate_name', 'Empresa Exemplo Ltda');
    }

    public function test_supplier_request_requires_valid_documents_and_primary_phone_but_allows_no_email(): void
    {
        $invalidDocument = $this->supplierData();
        $invalidDocument['cpf'] = '111.111.111-11';

        $this->withToken($this->token())->postJson('/api/suppliers', $invalidDocument)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf']);

        $supplierWithoutEmail = $this->supplierData();
        $supplierWithoutEmail['primary_contact']['emails'] = [];

        $this->withToken($this->token())->postJson('/api/suppliers', $supplierWithoutEmail)
            ->assertCreated()
            ->assertJsonPath('data.primary_contact.emails', []);

        $missingCondominiumDetails = $this->supplierData();
        unset($missingCondominiumDetails['condominium_address'], $missingCondominiumDetails['condominium_number']);
        $this->withToken($this->token())->postJson('/api/suppliers', $missingCondominiumDetails)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['condominium_address', 'condominium_number']);

        $invalidPhone = $this->supplierData();
        $invalidPhone['primary_contact']['phones'][0]['value'] = '(19) 123-456';
        $this->withToken($this->token())->postJson('/api/suppliers', $invalidPhone)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['primary_contact.phones.0.value']);
    }

    public function test_supplier_can_be_updated_and_deleted(): void
    {
        $response = $this->withToken($this->token())->postJson('/api/suppliers', $this->supplierData());
        $supplierId = $response->assertCreated()->json('data.id');

        $updated = $this->supplierData();
        $updated['name'] = 'Nome Atualizado';
        $this->withToken($this->token())->putJson("/api/suppliers/{$supplierId}", $updated)
            ->assertOk()
            ->assertJsonPath('data.name', 'Nome Atualizado');

        $this->withToken($this->token())->deleteJson("/api/suppliers/{$supplierId}")
            ->assertOk()
            ->assertJsonPath('data.message', 'Fornecedor removido com sucesso.');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplierId]);
        $this->assertDatabaseCount('individuals', 0);
    }

    public function test_supplier_endpoints_require_authentication(): void
    {
        $this->getJson('/api/suppliers')->assertUnauthorized();
        $this->postJson('/api/suppliers', [])->assertUnauthorized();
    }

    public function test_supplier_list_sorts_the_full_result_set_before_paginating(): void
    {
        $token = $this->token();
        $first = $this->supplierData();
        $first['name'] = 'Zebra Fornecedor';
        $this->withToken($token)->postJson('/api/suppliers', $first)->assertCreated();

        $second = $this->supplierData();
        $second['name'] = 'Alpha Fornecedor';
        $second['cpf'] = '111.444.777-35';
        $this->withToken($token)->postJson('/api/suppliers', $second)->assertCreated();

        $this->withToken($token)->getJson('/api/suppliers?sort_by=name&sort_direction=asc&per_page=1&page=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.name', 'Alpha Fornecedor');

        $this->withToken($token)->getJson('/api/suppliers?sort_by=name&sort_direction=asc&per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.name', 'Zebra Fornecedor');
    }

    public function test_supplier_list_rejects_unsupported_sorting_options(): void
    {
        $this->withToken($this->token())
            ->getJson('/api/suppliers?sort_by=unsupported&sort_direction=asc')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_by']);
    }

    /** @return array<string, mixed> */
    private function supplierData(string $type = 'individual'): array
    {
        return [
            'type' => $type,
            'name' => 'Maria Exemplo',
            'cpf' => '529.982.247-25',
            'nickname' => 'Mari',
            'rg' => '123456789',
            'corporate_name' => 'Empresa Exemplo Ltda',
            'trade_name' => 'Exemplo',
            'cnpj' => '11.222.333/0001-81',
            'state_registration_indicator' => 'contribuinte',
            'state_registration' => '123456',
            'municipal_registration' => null,
            'tax_regime' => 'withheld_by_recipient',
            'active' => true,
            'postal_code' => '13000-000',
            'street' => 'Rua Exemplo',
            'number' => '10',
            'complement' => null,
            'district' => 'Centro',
            'reference' => null,
            'state_id' => $this->state->id,
            'city_id' => $this->city->id,
            'condominium' => true,
            'condominium_address' => 'Bloco A',
            'condominium_number' => '12',
            'observation' => null,
            'primary_contact' => [
                'phones' => [
                    ['value' => '(19) 98765-4321', 'label' => 'Celular'],
                    ['value' => '(19) 3234-5678', 'label' => 'Comercial'],
                ],
                'emails' => [
                    ['value' => 'contato@example.com', 'label' => 'Comercial'],
                    ['value' => 'maria@example.com', 'label' => 'Pessoal'],
                ],
            ],
            'additional_contacts' => [[
                'name' => 'João Contato',
                'company' => 'Exemplo',
                'position' => 'Financeiro',
                'phones' => [['value' => '(19) 3234-5678', 'label' => 'Comercial']],
                'emails' => [['value' => 'joao@example.com', 'label' => 'Comercial']],
            ]],
        ];
    }

    private function token(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }
}
