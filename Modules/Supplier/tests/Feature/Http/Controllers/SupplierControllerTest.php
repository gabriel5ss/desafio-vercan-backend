<?php

declare(strict_types=1);

namespace Modules\Supplier\Tests\Feature\Http\Controllers;

use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use JMac\Testing\Traits\AdditionalAssertions;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SupplierControllerTest extends TestCase
{
    use AdditionalAssertions;

    private User $user;

    private Supplier $supplier;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['services.receitaws_url' => 'https://receitaws.test/v1/cnpj/']);

        $this->user = User::factory()->create();
        $this->supplier = Supplier::factory()->create();
    }

    /** @return iterable<string, array{string, string}> */
    public static function supplierPageCases(): iterable
    {
        yield 'show' => ['suppliers.show', 'supplier::show'];
        yield 'edit' => ['suppliers.edit', 'supplier::create'];
    }

    /** @return iterable<string, array{array<string, mixed>, int}> */
    public static function indexApiCases(): iterable
    {
        yield 'list' => [[], 2];
        yield 'search and sort' => [['search' => ['value' => 'Alfa'], 'order' => [['column' => 0, 'dir' => 'asc']]], 1];
    }

    /** @return iterable<string, array{?string, string}> */
    public static function lookupCnpjCases(): iterable
    {
        yield 'trade name' => ['Empresa Exemplo', 'Empresa Exemplo'];
        yield 'corporate name fallback' => [null, 'Empresa Ltda'];
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function validData(): iterable
    {
        yield 'individual' => [[
            'name' => 'Fornecedor Teste',
            'nickname' => null,
            'cpf' => '12345678909',
            'rg' => '123456789',
            'type' => 'individual',
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
        ]];
    }

    public function test_index(): void
    {
        $route = route('suppliers.index');

        $response = $this->actingAs($this->user)
            ->get($route);

        $response->assertSuccessful();
        $response->assertSee('Fornecedores');

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_create(): void
    {
        $route = route('suppliers.create');
        $response = $this->actingAs($this->user)
            ->get($route);

        $response->assertSuccessful();
        $response->assertViewIs('supplier::create');
        $response->assertViewHasAll(['states', 'cities', 'formData']);

        $this->assertAuthenticatedAs($this->user);
    }

    #[DataProvider('supplierPageCases')]
    public function test_supplier_page(string $route, string $view): void
    {
        $response = $this->actingAs($this->user)
            ->get(route($route, $this->supplier));

        $response->assertSuccessful();
        $response->assertViewIs($view);
        $response->assertViewHas('supplier', fn (Supplier $actual): bool => $actual->is($this->supplier));

        $this->assertAuthenticatedAs($this->user);
    }

    #[DataProvider('validData')]
    public function test_store(array $data): void
    {
        $city = City::factory()->create();
        $data['state_id'] = $city->state_id;
        $data['city_id'] = $city->id;

        $response = $this->actingAs($this->user)
            ->post(route('suppliers.store'), $data);

        $response->assertRedirectToRoute('suppliers.index');
        $response->assertSessionHas('status', 'Fornecedor cadastrado com sucesso.');

        $this->assertDatabaseHas('suppliers', ['city_id' => $data['city_id']]);
        $this->assertDatabaseHas('individuals', ['cpf' => $data['cpf']]);

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_store_failed(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('suppliers.store'), []);

        $response->assertUnprocessable();
        $response->assertInvalid(['type', 'city_id']);

        $this->assertDatabaseCount('suppliers', 1);

        $this->assertAuthenticatedAs($this->user);
    }

    #[DataProvider('validData')]
    public function test_update(array $data): void
    {
        $city = City::factory()->create();
        $data['state_id'] = $city->state_id;
        $data['city_id'] = $city->id;

        $response = $this->actingAs($this->user)
            ->put(route('suppliers.update', $this->supplier), $data);

        $response->assertRedirectToRoute('suppliers.index');
        $response->assertSessionHas('status', 'Fornecedor atualizado com sucesso.');

        $this->assertDatabaseHas('individuals', ['id' => $this->supplier->supplierable_id, 'name' => $data['name']]);

        $this->assertAuthenticatedAs($this->user);
    }

    #[DataProvider('validData')]
    public function test_update_failed(array $data): void
    {
        $person = $this->supplier->supplierable;
        $city = City::factory()->create();
        $data['state_id'] = $city->state_id;
        $data['city_id'] = $city->id;
        $data['name'] = '';

        $response = $this->actingAs($this->user)
            ->putJson(route('suppliers.update', $this->supplier), $data);

        $response->assertUnprocessable();
        $response->assertInvalid(['name']);

        $this->assertDatabaseHas('individuals', ['id' => $person->id, 'name' => $person->name]);
        $this->assertDatabaseHas('suppliers', ['id' => $this->supplier->id, 'city_id' => $this->supplier->city_id]);

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_destroy(): void
    {
        $response = $this->actingAs($this->user)
            ->delete(route('suppliers.destroy', $this->supplier));

        $response->assertRedirectToRoute('suppliers.index');
        $response->assertSessionHas('status', 'Fornecedor removido com sucesso.');

        $this->assertModelMissing($this->supplier);

        $this->assertAuthenticatedAs($this->user);
    }

    #[DataProvider('indexApiCases')]
    public function test_index_api(array $filters, int $count): void
    {
        $this->supplier->supplierable->update(['name' => 'Alfa']);
        $this->supplier->update(['created_at' => '2026-01-02 12:00:00']);
        Supplier::factory()->for(Individual::factory()->state(['name' => 'Beta']), 'supplierable')
            ->create(['created_at' => '2026-01-01 12:00:00']);
        $query = array_replace([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'columns' => array_map(fn (string $field): array => ['data' => $field, 'name' => $field === 'edit_url' ? null : $field],
                ['name', 'trade_name', 'document', 'active', 'edit_url']),
        ], $filters);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.suppliers.index', $query));

        $response->assertSuccessful();
        $response->assertJsonCount($count, 'data');
        $response->assertJsonPath('data.0.name', 'Alfa');

        $this->assertAuthenticatedAs($this->user);
    }

    #[DataProvider('lookupCnpjCases')]
    public function test_lookup_cnpj(?string $tradeName, string $expected): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response([
                'status' => 'OK', 'nome' => 'Empresa Ltda', 'fantasia' => $tradeName, 'situacao' => 'ATIVA',
            ]),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.suppliers.lookup-cnpj', ['cnpj' => '04252011000110']));

        $response->assertSuccessful();
        $response->assertJson(['data' => [
            'corporate_name' => 'Empresa Ltda', 'trade_name' => $expected, 'cnpj_status' => 'ATIVA',
        ]]);

        Http::assertSentCount(1);

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_lookup_cnpj_failed(): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response(['status' => 'ERROR']),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.suppliers.lookup-cnpj', ['cnpj' => '04252011000110']));

        $response->assertUnprocessable();
        $response->assertInvalid([
            'cnpj' => 'O CNPJ informado não pôde ser validado. Verifique o número e tente novamente.',
        ]);

        Http::assertSentCount(1);

        $this->assertAuthenticatedAs($this->user);
    }
}
