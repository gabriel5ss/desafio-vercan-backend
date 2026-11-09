<?php

namespace Modules\Supplier\Tests\Feature\Http\Requests;

use App\Models\City;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Override;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SupplierRequestTest extends TestCase
{
    private User $user;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.receitaws_url' => 'https://receitaws.test/v1/cnpj/']);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    #[TestWith(['individual', 'cpf'], 'individual')]
    #[TestWith(['company', 'cnpj'], 'company')]
    public function test_prepare_for_validation(string $type, string $document): void
    {
        $data = $this->validData($type);
        $data = array_replace($data, $this->personData('individual'), $this->personData('company'), [
            'cpf' => '123.456.789-09',
            'cnpj' => '04.252.011/0001-10',
            'postal_code' => '01310-100',
            'primary_contact' => [
                'phones' => [['value' => '(11) 99999-9999']],
                'emails' => [['value' => ' contato@exemplo.com ']],
            ],
            'additional_contacts' => [[
                'name' => 'User',
                'phones' => [['value' => '(21) 98888-7777', 'label' => 'Celular']],
                'emails' => [['value' => ' user@exemplo.com ', 'label' => 'Pessoal']],
            ]],
        ]);

        $data[$type === 'individual' ? 'cnpj' : 'cpf'] = '123456789012345';
        $this->fakeCompanyLookup();

        $this->post(route('suppliers.store'), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirectToRoute('suppliers.index');

        $supplier = Supplier::query()->sole();
        $this->assertSame($this->personData($type)[$document], $supplier->document);
        $this->assertSame('01310100', $supplier->postal_code);
        $this->assertDatabaseCount($type === 'individual' ? 'companies' : 'individuals', 0);
        Http::assertSentCount($type === 'company' ? 1 : 0);
    }

    public function test_allows_current_cpf_on_update(): void
    {
        $data = $this->validData();
        $person = Individual::factory()->create(['cpf' => $data['cpf']]);
        $supplier = Supplier::factory()
            ->for($person, 'supplierable')
            ->create();

        $this->put(route('suppliers.update', $supplier), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirectToRoute('suppliers.index');

        $this->assertDatabaseHas('individuals', ['id' => $person->id, 'cpf' => $data['cpf']]);
    }

    public function test_allows_current_cnpj_on_update(): void
    {
        $data = $this->validData('company');
        $company = Company::factory()->create(['cnpj' => $data['cnpj']]);
        $supplier = Supplier::factory()
            ->for($company, 'supplierable')
            ->create();
        $this->fakeCompanyLookup();

        $this->put(route('suppliers.update', $supplier), $data)
            ->assertSessionHasNoErrors()
            ->assertRedirectToRoute('suppliers.index');

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'cnpj' => $data['cnpj']]);
        Http::assertSentCount(1);
    }

    public function test_rejects_invalid_cpf(): void
    {
        $data = $this->validData();
        $data['cpf'] = '12345678919';

        $this->postJson(route('suppliers.store'), $data)
            ->assertUnprocessable()
            ->assertInvalid(['cpf' => 'O CPF informado é inválido.']);
    }

    public function test_rejects_invalid_cnpj(): void
    {
        $data = $this->validData('company');
        $data['cnpj'] = '04252011000120';

        $this->postJson(route('suppliers.store'), $data)
            ->assertUnprocessable()
            ->assertInvalid(['cnpj' => 'O CNPJ informado é inválido.']);
    }

    private function fakeCompanyLookup(): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response(['status' => 'OK', 'situacao' => 'ATIVA']),
        ]);
    }

    /** @return array<string, mixed> */
    private function validData(string $type = 'individual'): array
    {
        $state = State::factory()->create();
        $city = City::factory()->for($state)->create();

        return [
            ...$this->personData($type),
            'type' => $type,
            'active' => true,
            'postal_code' => '01310100',
            'street' => 'Avenida Paulista',
            'number' => '100',
            'district' => 'Bela Vista',
            'state_id' => $state->id,
            'city_id' => $city->id,
            'condominium' => false,
            'primary_contact' => [
                'phones' => [['value' => '11999999999']],
                'emails' => [['value' => 'contato@example.com']],
            ],
        ];
    }

    /** @return array<string, string> */
    private function personData(string $type): array
    {
        return match ($type) {
            'individual' => ['name' => 'Teste User', 'cpf' => '12345678909', 'rg' => '123456789'],
            'company' => [
                'corporate_name' => 'Empresa Exemplo Ltda',
                'trade_name' => 'Empresa Exemplo',
                'cnpj' => '04252011000110',
                'state_registration_indicator' => 'isento',
                'tax_regime' => 'payable_by_provider',
            ],
        };
    }
}
