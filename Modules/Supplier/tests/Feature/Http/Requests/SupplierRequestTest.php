<?php

declare(strict_types=1);

namespace Modules\Supplier\Tests\Feature\Http\Requests;

use App\Models\City;
use Illuminate\Support\Facades\Http;
use JMac\Testing\Traits\AdditionalAssertions;
use Modules\Supplier\Database\Factories\SupplierFactory;
use Modules\Supplier\Http\Requests\SupplierRequest;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Modules\User\Models\User;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SupplierRequestTest extends TestCase
{
    use AdditionalAssertions;

    private User $user;

    private SupplierFactory $supplier;

    private City $city;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.receitaws_url' => 'https://receitaws.test/v1/cnpj/']);

        $this->user = User::factory()->create();

        $this->supplier = Supplier::factory();

        $this->city = City::factory()->create();

        $this->actingAs($this->user);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function validData(): array
    {
        $address = [
            'active' => true,
            'postal_code' => '01310100',
            'street' => 'Avenida Paulista',
            'number' => '100',
            'district' => 'Bela Vista',
            'condominium' => false,
            'primary_contact' => [
                'phones' => [['value' => '11999999999']],
                'emails' => [['value' => 'contato@example.com']],
            ],
        ];

        return [
            'individual' => [[
                ...$address,
                'type' => 'individual',
                'name' => 'Teste User',
                'cpf' => '12345678909',
                'rg' => '123456789',
            ]],
            'company' => [[
                ...$address,
                'type' => 'company',
                'corporate_name' => 'Empresa Exemplo Ltda',
                'trade_name' => 'Empresa Exemplo',
                'cnpj' => '04252011000110',
                'state_registration_indicator' => 'isento',
                'tax_regime' => 'payable_by_provider',
            ]],
        ];
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function individualData(): array
    {
        return [
            'individual' => [[
                'active' => true,
                'postal_code' => '01310100',
                'street' => 'Avenida Paulista',
                'number' => '100',
                'district' => 'Bela Vista',
                'condominium' => false,
                'primary_contact' => [
                    'phones' => [['value' => '11999999999']],
                    'emails' => [['value' => 'contato@example.com']],
                ],
                'type' => 'individual',
                'name' => 'Teste User',
                'cpf' => '12345678909',
                'rg' => '123456789',
            ]],
        ];
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function companyData(): array
    {
        return [
            'company' => [[
                'active' => true,
                'postal_code' => '01310100',
                'street' => 'Avenida Paulista',
                'number' => '100',
                'district' => 'Bela Vista',
                'condominium' => false,
                'primary_contact' => [
                    'phones' => [['value' => '11999999999']],
                    'emails' => [['value' => 'contato@example.com']],
                ],
                'type' => 'company',
                'corporate_name' => 'Empresa Exemplo Ltda',
                'trade_name' => 'Empresa Exemplo',
                'cnpj' => '04252011000110',
                'state_registration_indicator' => 'isento',
                'tax_regime' => 'payable_by_provider',
            ]],
        ];
    }

    /** @return array<string, array{string, class-string}> */
    public static function formRequestCases(): array
    {
        return [
            'store' => ['suppliers.store', SupplierRequest::class],
            'update' => ['suppliers.update', SupplierRequest::class],
        ];
    }

    /** @param class-string $request */
    #[DataProvider('formRequestCases')]
    public function test_form_requests(string $route, string $request): void
    {
        $this->assertRouteUsesFormRequest($route, $request);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('validData')]
    public function test_prepare_for_validation(array $data): void
    {
        $type = $data['type'];
        $document = $type === 'individual' ? 'cpf' : 'cnpj';
        $expectedDocument = $data[$document];
        $data = array_replace(
            $data,
            [
                'state_id' => $this->city->state_id,
                'city_id' => $this->city->id,
                'name' => 'Teste User',
                'rg' => '123456789',
                'corporate_name' => 'Empresa Exemplo Ltda',
                'trade_name' => 'Empresa Exemplo',
                'state_registration_indicator' => 'isento',
                'tax_regime' => 'payable_by_provider',
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
            ],
        );

        $data[$type === 'individual' ? 'cnpj' : 'cpf'] = '123456789012345';

        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response(['status' => 'OK', 'situacao' => 'ATIVA']),
        ]);

        $response = $this->post(route('suppliers.store'), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirectToRoute('suppliers.index');

        $supplier = Supplier::query()->sole();

        $this->assertSame($expectedDocument, $supplier->document);
        $this->assertSame('01310100', $supplier->postal_code);
        $this->assertDatabaseCount($type === 'individual' ? 'companies' : 'individuals', 0);
        Http::assertSentCount($type === 'company' ? 1 : 0);

        $this->assertAuthenticatedAs($this->user);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('individualData')]
    public function test_cpf_on_update(array $data): void
    {
        $data['state_id'] = $this->city->state_id;
        $data['city_id'] = $this->city->id;
        $person = Individual::factory()->create(['cpf' => $data['cpf']]);
        $supplier = $this->supplier
            ->for($person, 'supplierable')
            ->create(['state_id' => $this->city->state_id, 'city_id' => $this->city->id]);

        $response = $this->put(route('suppliers.update', $supplier), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirectToRoute('suppliers.index');

        $this->assertDatabaseHas('individuals', ['id' => $person->id, 'cpf' => $data['cpf']]);

        $this->assertAuthenticatedAs($this->user);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('companyData')]
    public function test_cnpj_on_update(array $data): void
    {
        $data['state_id'] = $this->city->state_id;
        $data['city_id'] = $this->city->id;
        $company = Company::factory()->create(['cnpj' => $data['cnpj']]);
        $supplier = $this->supplier
            ->for($company, 'supplierable')
            ->create(['state_id' => $this->city->state_id, 'city_id' => $this->city->id]);
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response(['status' => 'OK', 'situacao' => 'ATIVA']),
        ]);

        $response = $this->put(route('suppliers.update', $supplier), $data);

        $response->assertSessionHasNoErrors();
        $response->assertRedirectToRoute('suppliers.index');

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'cnpj' => $data['cnpj']]);
        Http::assertSentCount(1);

        $this->assertAuthenticatedAs($this->user);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('companyData')]
    public function test_store_failed(array $data): void
    {
        $data['state_id'] = $this->city->state_id;
        $data['city_id'] = $this->city->id;
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response(['status' => 'ERROR'], 503),
        ]);

        $response = $this->postJson(route('suppliers.store'), $data);

        $response->assertServiceUnavailable();
        $this->assertDatabaseCount('suppliers', 0);
        $this->assertDatabaseMissing('companies', ['cnpj' => '04252011000110']);
        $this->assertAuthenticatedAs($this->user);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('individualData')]
    public function test_cpf_failed(array $data): void
    {
        $data['state_id'] = $this->city->state_id;
        $data['city_id'] = $this->city->id;
        $data['cpf'] = '12345678919';

        $response = $this->postJson(route('suppliers.store'), $data);

        $response->assertUnprocessable();
        $response->assertInvalid(['cpf' => 'O CPF informado é inválido.']);

        $this->assertAuthenticatedAs($this->user);
    }

    /** @param array<string, mixed> $data */
    #[DataProvider('companyData')]
    public function test_cnpj_failed(array $data): void
    {
        $data['state_id'] = $this->city->state_id;
        $data['city_id'] = $this->city->id;
        $data['cnpj'] = '04252011000120';

        $response = $this->postJson(route('suppliers.store'), $data);

        $response->assertUnprocessable();
        $response->assertInvalid(['cnpj' => 'O CNPJ informado é inválido.']);

        $this->assertAuthenticatedAs($this->user);
    }
}
