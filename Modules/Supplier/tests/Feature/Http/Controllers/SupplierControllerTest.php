<?php

namespace Modules\Supplier\Tests\Feature\Http\Controllers;

use App\Models\City;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use JMac\Testing\Traits\AdditionalAssertions;
use Modules\Supplier\Http\Requests\SupplierRequest;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
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

    /** @param class-string $request */
    #[DataProvider('formRequestCases')]
    public function test_form_requests(string $route, string $request): void
    {
        // converter em teste de unidade
        $this->assertRouteUsesFormRequest($route, $request);
    }

    /** @return array<string, array{string, class-string}> */
    public static function formRequestCases(): array
    {
        return [
            'store' => ['suppliers.store', SupplierRequest::class],
            'update' => ['suppliers.update', SupplierRequest::class],
        ];
    }

    public function test_index(): void
    {
        $route = route('suppliers.index');
        $response = $this->actingAs($this->user)
            ->get($route);

        $response->assertSuccessful();
        // $response->assertViewIs('supplier::index');
        $response->assertSee('Fornecedores');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_create(): void
    {
        $this->actingAs($this->user)
            ->get(route('suppliers.create'))
            ->assertOk()->assertViewIs('supplier::create')
            ->assertViewHasAll(['states', 'cities', 'formData']);
    }

    #[TestWith(['suppliers.show', 'supplier::show'])]
    #[TestWith(['suppliers.edit', 'supplier::create'])]
    public function test_supplier_page(string $route, string $view): void
    {
        $this->actingAs($this->user)
            ->get(route($route, $this->supplier))
            ->assertOk()->assertViewIs($view)
            ->assertViewHas('supplier', fn (Supplier $actual): bool => $actual->is($this->supplier));
    }

    public function test_store(): void
    {
        $data = $this->validData();

        $this->actingAs($this->user)
            ->post(route('suppliers.store'), $data)
            ->assertRedirectToRoute('suppliers.index')
            ->assertSessionHas('status', 'Fornecedor cadastrado com sucesso.');

        $this->assertDatabaseHas('suppliers', ['city_id' => $data['city_id']]);
        $this->assertDatabaseHas('individuals', ['cpf' => $data['cpf']]);

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_store_failed(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('suppliers.store'), [])
            ->assertUnprocessable()->assertInvalid(['type', 'city_id']);

        $this->assertDatabaseCount('suppliers', 1);
    }

    public function test_update(): void
    {
        $data = $this->validData();

        $this->actingAs($this->user)
            ->put(route('suppliers.update', $this->supplier), $data)
            ->assertRedirectToRoute('suppliers.index')
            ->assertSessionHas('status', 'Fornecedor atualizado com sucesso.');

        $this->assertDatabaseHas('individuals', ['id' => $this->supplier->supplierable_id, 'name' => $data['name']]);
    }

    public function test_update_failed(): void
    {
        $person = $this->supplier->supplierable;
        $data = $this->validData();
        $data['name'] = '';

        $this->actingAs($this->user)
            ->putJson(route('suppliers.update', $this->supplier), $data)
            ->assertUnprocessable()->assertInvalid(['name']);

        $this->assertDatabaseHas('individuals', ['id' => $person->id, 'name' => $person->name]);
        $this->assertDatabaseHas('suppliers', ['id' => $this->supplier->id, 'city_id' => $this->supplier->city_id]);
    }

    public function test_destroy(): void
    {
        $this->actingAs($this->user)
            ->delete(route('suppliers.destroy', $this->supplier))
            ->assertRedirectToRoute('suppliers.index')
            ->assertSessionHas('status', 'Fornecedor removido com sucesso.');

        $this->assertModelMissing($this->supplier);
    }

    #[TestWith([[], 2], 'list')]
    #[TestWith([['search' => ['value' => 'Alfa'], 'order' => [['column' => 0, 'dir' => 'asc']]], 1], 'search and sort')]
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

        $this->actingAs($this->user)
            ->getJson(route('api.suppliers.index', $query))
            ->assertOk()->assertJsonCount($count, 'data')->assertJsonPath('data.0.name', 'Alfa');
    }

    #[TestWith(['Empresa Exemplo', 'Empresa Exemplo'], 'trade name')]
    #[TestWith([null, 'Empresa Ltda'], 'corporate name fallback')]
    public function test_lookup_cnpj(?string $tradeName, string $expected): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response([
                'status' => 'OK', 'nome' => 'Empresa Ltda', 'fantasia' => $tradeName, 'situacao' => 'ATIVA',
            ]),
        ]);

        $this->actingAs($this->user)
            ->getJson(route('api.suppliers.lookup-cnpj', ['cnpj' => '04252011000110']))
            ->assertOk()->assertJson(['data' => [
                'corporate_name' => 'Empresa Ltda', 'trade_name' => $expected, 'cnpj_status' => 'ATIVA',
            ]]);

        Http::assertSentCount(1);
    }

    public function test_lookup_cnpj_failed(): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response(['status' => 'ERROR']),
        ]);

        $this->actingAs($this->user)
            ->getJson(route('api.suppliers.lookup-cnpj', ['cnpj' => '04252011000110']))
            ->assertUnprocessable()->assertInvalid([
                'cnpj' => 'O CNPJ informado não pôde ser validado. Verifique o número e tente novamente.',
            ]);

        Http::assertSentCount(1);
    }

    /** @return array<string, mixed> */
    private function validData(): array
    {
        $city = City::factory()->create();

        return [
            ...Individual::factory()->raw(['name' => 'Fornecedor Teste']),
            'type' => 'individual',
            'state_id' => $city->state_id,
            'city_id' => $city->id,
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
    }
}
