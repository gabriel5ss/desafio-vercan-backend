<?php

declare(strict_types=1);

namespace Modules\Supplier\Tests\Feature\Models;

use App\Models\City;
use App\Models\State;
use Modules\Supplier\Database\Factories\SupplierFactory;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SupplierTest extends TestCase
{
    private SupplierFactory $supplier;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::factory();
    }

    /** @return array<string, array{class-string<Individual|Company>|null, array<string, string|null>, string|null}> */
    public static function nameCases(): array
    {
        return [
            'individual name' => [Individual::class, ['name' => 'Teste User'], 'Teste User'],
            'company name' => [Company::class, ['corporate_name' => 'Empresa Ltda'], 'Empresa Ltda'],
            'name without person' => [null, [], null],
        ];
    }

    /** @return array<string, array{class-string<Individual|Company>|null, array<string, string|null>, string}> */
    public static function tradeNameCases(): array
    {
        return [
            'individual trade name' => [Individual::class, ['nickname' => 'User'], 'User'],
            'company trade name' => [Company::class, ['trade_name' => 'Empresa'], 'Empresa'],
            'missing nickname' => [Individual::class, ['nickname' => null], ''],
            'missing trade name' => [Company::class, ['trade_name' => null], ''],
            'trade name without person' => [null, [], ''],
        ];
    }

    /** @return array<string, array{class-string<Individual|Company>|null, array<string, string|null>, string|null}> */
    public static function documentCases(): array
    {
        return [
            'individual document' => [Individual::class, ['cpf' => '12345678909'], '12345678909'],
            'company document' => [Company::class, ['cnpj' => '04252011000110'], '04252011000110'],
            'document without person' => [null, [], null],
        ];
    }

    /** @return array<string, array{string|null, string, list<int>}> */
    public static function sortingCases(): array
    {
        return [
            'name ascending' => ['name', 'asc', [20, 10]],
            'name descending' => ['name', 'desc', [10, 20]],
            'trade name' => ['trade_name', 'asc', [10, 20]],
            'document' => ['document', 'asc', [10, 20]],
            'active' => ['active', 'asc', [20, 10]],
            'newest first by default' => [null, 'asc', [20, 10]],
        ];
    }

    /** @return array<string, array{class-string<Individual|Company>, string, string}> */
    public static function searchCases(): array
    {
        return [
            'individual name' => [Individual::class, 'name', 'User'],
            'nickname' => [Individual::class, 'nickname', 'Contato 1'],
            'cpf' => [Individual::class, 'cpf', '52998224725'],
            'corporate name' => [Company::class, 'corporate_name', 'Empresa 1'],
            'trade name' => [Company::class, 'trade_name', 'Marca 1'],
            'cnpj' => [Company::class, 'cnpj', '04252011000110'],
        ];
    }

    /** @return array<string, array{class-string<Individual|Company>}> */
    public static function supplierableCases(): array
    {
        return [
            'individual' => [Individual::class],
            'company' => [Company::class],
        ];
    }

    /** @return array<string, array{string|null}> */
    public static function emptySearchCases(): array
    {
        return [
            'null search' => [null],
            'blank search' => ['   '],
        ];
    }

    /**
     * @param  class-string<Individual|Company>|null  $personClass
     * @param  array<string, string|null>  $data
     */
    #[DataProvider('nameCases')]
    public function test_get_name_attribute(?string $personClass, array $data, ?string $expected): void
    {
        $supplier = $this->supplier->newModel();
        $person = $personClass === null ? null : new $personClass($data);
        $supplier->setRelation('supplierable', $person);

        $this->assertSame($expected, $supplier->name);
    }

    /**
     * @param  class-string<Individual|Company>|null  $personClass
     * @param  array<string, string|null>  $data
     */
    #[DataProvider('tradeNameCases')]
    public function test_get_trade_name_attribute(?string $personClass, array $data, string $expected): void
    {
        $supplier = $this->supplier->newModel();
        $person = $personClass === null ? null : new $personClass($data);
        $supplier->setRelation('supplierable', $person);

        $this->assertSame($expected, $supplier->trade_name);
    }

    /**
     * @param  class-string<Individual|Company>|null  $personClass
     * @param  array<string, string|null>  $data
     */
    #[DataProvider('documentCases')]
    public function test_get_document_attribute(?string $personClass, array $data, ?string $expected): void
    {
        $supplier = $this->supplier->newModel();
        $person = $personClass === null ? null : new $personClass($data);
        $supplier->setRelation('supplierable', $person);

        $this->assertSame($expected, $supplier->document);
    }

    /** @param class-string<Individual|Company> $personClass */
    #[DataProvider('supplierableCases')]
    public function test_supplierable(string $personClass): void
    {
        $person = $personClass::factory()->create();
        $supplier = $this->supplier->for($person, 'supplierable')->create();

        $supplier->load('supplierable');

        $this->assertInstanceOf($personClass, $supplier->supplierable);
    }

    public function test_state(): void
    {
        $state = State::factory()->create(['id' => 10]);
        $supplier = $this->supplier->create(['state_id' => $state->id]);

        $supplier->load('stateInfo');

        $this->assertInstanceOf(State::class, $supplier->stateInfo);
    }

    public function test_city(): void
    {
        $city = City::factory()->create(['id' => 20]);
        $supplier = $this->supplier->create(['state_id' => $city->state_id, 'city_id' => $city->id]);

        $relatedCity = $supplier->fresh()->cityInfo;

        $this->assertTrue($relatedCity->is($city));
    }

    public function test_contacts(): void
    {
        $supplier = $this->supplier->create();
        Contact::factory()->count(2)->create(['supplier_id' => $supplier->id]);

        $supplier->load('contacts');

        $this->assertInstanceOf(Contact::class, $supplier->contacts->first());
        $this->assertInstanceOf(Contact::class, $supplier->contacts->last());
    }

    /** @param list<int> $expected */
    #[DataProvider('sortingCases')]
    public function test_apply_sorting(?string $field, string $direction, array $expected): void
    {
        $this->supplier->for(Company::factory()->state([
            'corporate_name' => 'Zeta', 'trade_name' => 'Alfa', 'cnpj' => '04252011000110',
        ]), 'supplierable')->create(['id' => 10, 'active' => true, 'created_at' => '2026-01-01 12:00:00']);

        $this->supplier->for(Individual::factory()->state([
            'name' => 'Alfa', 'nickname' => 'Zeta', 'cpf' => '52998224725',
        ]), 'supplierable')->create(['id' => 20, 'active' => false, 'created_at' => '2026-01-02 12:00:00']);

        $suppliers = Supplier::query()->applySorting($field, $direction)->get();

        $this->assertSame($expected, $suppliers->modelKeys());
    }

    /** @param class-string<Individual|Company> $personClass */
    #[DataProvider('searchCases')]
    public function test_search_term(string $personClass, string $field, string $value): void
    {
        $supplier = $this->supplier
            ->for($personClass::factory()->state([$field => $value]), 'supplierable')
            ->create();

        $this->supplier->for(Individual::factory()->state([
            'name' => 'Outro', 'nickname' => 'Outro', 'cpf' => '12345678909',
        ]), 'supplierable')->create();

        $suppliers = Supplier::query()->searchTerm(" {$value} ")->get();

        $this->assertSame([$supplier->id], $suppliers->modelKeys());
    }

    #[DataProvider('emptySearchCases')]
    public function test_empty_search(?string $search): void
    {
        $suppliers = $this->supplier->count(2)->create();

        $result = Supplier::query()->searchTerm($search)->orderBy('id')->get();

        $this->assertSame($suppliers->modelKeys(), $result->modelKeys());
    }

    public function test_form_options(): void
    {
        $lastState = State::factory()->create(['name' => 'Estado 2']);
        $firstState = State::factory()->create(['name' => 'Estado 1']);
        $lastCity = City::factory()->for($lastState)->create(['name' => 'Cidade 2']);
        $firstCity = City::factory()->for($firstState)->create(['name' => 'Cidade 1']);

        $options = Supplier::formOptions();

        $this->assertSame([$firstState->id, $lastState->id], $options['states']->modelKeys());
        $this->assertSame([$firstCity->id, $lastCity->id], $options['cities']->modelKeys());
    }
}
