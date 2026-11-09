<?php

namespace Modules\Supplier\Tests\Feature\Models;

use App\Models\City;
use App\Models\State;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    /**
     * @param  class-string<Individual|Company>|null  $personClass
     * @param  array<string, string|null>  $data
     */
    #[DataProvider('nameCases')]
    public function test_get_name_attribute(?string $personClass, array $data, ?string $expected): void
    {
        $supplier = new Supplier;
        $person = $personClass === null ? null : new $personClass($data);
        $supplier->setRelation('supplierable', $person);

        $this->assertSame($expected, $supplier->name);
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

    /**
     * @param  class-string<Individual|Company>|null  $personClass
     * @param  array<string, string|null>  $data
     */
    #[DataProvider('tradeNameCases')]
    public function test_get_trade_name_attribute(?string $personClass, array $data, string $expected): void
    {
        $supplier = new Supplier;
        $person = $personClass === null ? null : new $personClass($data);
        $supplier->setRelation('supplierable', $person);

        $this->assertSame($expected, $supplier->trade_name);
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

    /**
     * @param  class-string<Individual|Company>|null  $personClass
     * @param  array<string, string|null>  $data
     */
    #[DataProvider('documentCases')]
    public function test_get_document_attribute(?string $personClass, array $data, ?string $expected): void
    {
        $supplier = new Supplier;
        $person = $personClass === null ? null : new $personClass($data);
        $supplier->setRelation('supplierable', $person);

        $this->assertSame($expected, $supplier->document);
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

    /** @param class-string<Individual|Company> $personClass */
    #[TestWith([Individual::class], 'individual')]
    #[TestWith([Company::class], 'company')]
    public function test_supplierable(string $personClass): void
    {
        $person = $personClass::factory()->create();
        $supplier = Supplier::factory()->for($person, 'supplierable')->create();

        $supplier->load('supplierable');

        $this->assertInstanceOf($personClass, $supplier->supplierable);

        // $this->assertTrue($relatedPerson->is($person));
    }

    public function test_state(): void
    {
        $state = State::factory()->create(['id' => 10]);
        $supplier = Supplier::factory()->create(['state_id' => $state->id]);

        // $relatedState = $supplier->fresh()->stateInfo;

        // $this->assertTrue($relatedState->is($state));

        $supplier->load('stateInfo');

        $this->assertInstanceOf(State::class, $supplier->stateInfo);
    }

    public function test_city(): void
    {
        $city = City::factory()->create(['id' => 20]);
        $supplier = Supplier::factory()->create(['state_id' => $city->state_id, 'city_id' => $city->id]);

        $relatedCity = $supplier->fresh()->cityInfo;

        $this->assertTrue($relatedCity->is($city));
    }

    public function test_contacts(): void
    {
        $supplier = Supplier::factory()->create();
        Contact::factory()->count(2)->create(['supplier_id' => $supplier->id]);

        $supplier->load('contacts');

        // $this->assertCount(2, $relatedContacts);
        $this->assertInstanceOf(Contact::class, $supplier->contacts->first());
        $this->assertInstanceOf(Contact::class, $supplier->contacts->last());
    }

    /** @param list<int> $expected */
    #[DataProvider('sortingCases')]
    public function test_apply_sorting(?string $field, string $direction, array $expected): void
    {
        Supplier::factory()->for(Company::factory()->state([
            'corporate_name' => 'Zeta', 'trade_name' => 'Alfa', 'cnpj' => '04252011000110',
        ]), 'supplierable')->create(['id' => 10, 'active' => true, 'created_at' => '2026-01-01 12:00:00']);

        Supplier::factory()->for(Individual::factory()->state([
            'name' => 'Alfa', 'nickname' => 'Zeta', 'cpf' => '52998224725',
        ]), 'supplierable')->create(['id' => 20, 'active' => false, 'created_at' => '2026-01-02 12:00:00']);

        $suppliers = Supplier::query()->applySorting($field, $direction)->get();

        $this->assertSame($expected, $suppliers->modelKeys());
    }

    /** @param class-string<Individual|Company> $personClass */
    #[DataProvider('searchCases')]
    public function test_search_term(string $personClass, string $field, string $value): void
    {
        $supplier = Supplier::factory()
            ->for($personClass::factory()->state([$field => $value]), 'supplierable')
            ->create();

        Supplier::factory()->for(Individual::factory()->state([
            'name' => 'Outro', 'nickname' => 'Outro', 'cpf' => '12345678909',
        ]), 'supplierable')->create();

        $suppliers = Supplier::query()->searchTerm(" {$value} ")->get();

        $this->assertSame([$supplier->id], $suppliers->modelKeys());
    }

    #[TestWith([null])]
    #[TestWith(['   '])]
    public function test_empty_search(?string $search): void
    {
        $suppliers = Supplier::factory()->count(2)->create();

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
}
