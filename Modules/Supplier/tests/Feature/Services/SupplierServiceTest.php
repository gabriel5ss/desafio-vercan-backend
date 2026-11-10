<?php

declare(strict_types=1);

namespace Modules\Supplier\Tests\Feature\Services;

use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\ContactMethod;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Modules\Supplier\Services\SupplierService;
use Override;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;
use TypeError;

final class SupplierServiceTest extends TestCase
{
    private SupplierService $service;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.receitaws_url' => 'https://receitaws.test/v1/cnpj/']);
        $this->service = app(SupplierService::class);
    }

    public function test_save_individual(): void
    {
        $data = $this->validData();
        $city = City::with('state')->findOrFail($data['city_id']);
        $data['state'] = 'XX';
        $data['city'] = 'Outra cidade';
        $data['observation'] = ['content' => 'Observação'];
        $data['additional_contacts'] = [[
            'name' => 'User', 'company' => 'Empresa', 'position' => 'Financeiro',
            'phones' => [['value' => '21988887777', 'label' => 'Celular']],
        ]];

        $supplier = $this->service->save($data)->fresh(['supplierable', 'contacts']);

        $this->assertModelExists($supplier);
        $this->assertDatabaseHas('individuals', ['id' => $supplier->supplierable_id, 'cpf' => $data['cpf'], 'name' => $data['name']]);
        $this->assertSame($city->state->code, $supplier->state);
        $this->assertSame($city->name, $supplier->city);
        $this->assertSame('{"content":"Observação"}', $supplier->observation);
        $this->assertCount(2, $supplier->contacts);

        $primary = $supplier->contacts->firstWhere('is_primary', true);
        $additional = $supplier->contacts->firstWhere('is_primary', false);

        $this->assertDatabaseHas('contacts', ['id' => $primary->id, 'name' => null, 'company' => null, 'position' => null]);
        $this->assertDatabaseHas('contacts', ['id' => $additional->id, 'name' => 'User', 'company' => 'Empresa', 'position' => 'Financeiro']);
        $this->assertDatabaseHas('contact_methods', ['contact_id' => $primary->id, 'kind' => 'phone', 'value' => '11999999999', 'label' => null]);
        $this->assertDatabaseHas('contact_methods', ['contact_id' => $primary->id, 'kind' => 'email', 'value' => 'contato@example.com', 'label' => 'Comercial']);
        $this->assertDatabaseHas('contact_methods', ['contact_id' => $additional->id, 'kind' => 'phone', 'value' => '21988887777', 'label' => 'Celular']);

        Http::assertNothingSent();
    }

    public function test_save_company(): void
    {
        $data = $this->validData('company');
        $this->fakeCompanyLookup();

        $supplier = $this->service->save($data)->fresh(['supplierable', 'contacts']);

        $this->assertModelExists($supplier);
        $this->assertInstanceOf(Company::class, $supplier->supplierable);

        $this->assertDatabaseHas('companies', [
            'id' => $supplier->supplierable_id,
            'cnpj' => $data['cnpj'],
            'corporate_name' => $data['corporate_name'],
            'cnpj_status' => 'ATIVA',
        ]);

        Http::assertSentCount(1);
    }

    public function test_rejects_unconfirmed_cnpj(): void
    {
        $data = $this->validData('company');
        Http::fake([
            config('services.receitaws_url').$data['cnpj'] => Http::response(['status' => 'ERROR']),
        ]);

        $this->assertThrows(
            fn () => $this->service->save($data),
            fn (ValidationException $exception): bool => $exception->errors() === [
                'cnpj' => ['O CNPJ informado não pôde ser validado. Verifique o número e tente novamente.'],
            ],
        );

        $this->assertDatabaseCount('suppliers', 0);
        $this->assertDatabaseCount('companies', 0);
        $this->assertDatabaseCount('contacts', 0);

        Http::assertSentCount(1);
    }

    /** @param class-string<Individual|Company> $personClass */
    #[TestWith([Individual::class, 'individual'])]
    #[TestWith([Company::class, 'company'])]
    public function test_update(string $personClass, string $type): void
    {
        $supplier = Supplier::factory()->for($personClass::factory(), 'supplierable')->create();
        $person = $supplier->supplierable;
        $contact = Contact::factory()->primary()->create(['supplier_id' => $supplier->id]);
        $method = ContactMethod::factory()->create(['contact_id' => $contact->id]);
        $data = $this->validData($type);
        $this->fakeCompanyLookup();

        $updated = $this->service->save($data, $supplier)->fresh(['supplierable', 'contacts']);

        $this->assertSame($supplier->id, $updated->id);
        $this->assertSame($person->id, $updated->supplierable_id);
        $this->assertInstanceOf($personClass, $updated->supplierable);
        $this->assertSame($data[$type === 'individual' ? 'name' : 'corporate_name'], $updated->name);
        $this->assertSame($data['city_id'], $updated->city_id);
        $this->assertNull($updated->observation);
        $this->assertModelMissing($contact);
        $this->assertModelMissing($method);
        $this->assertCount(1, $updated->contacts);

        Http::assertSentCount($type === 'company' ? 1 : 0);
    }

    /**
     * @param  class-string<Individual|Company>  $previousClass
     * @param  class-string<Individual|Company>  $newClass
     */
    #[TestWith([Individual::class, Company::class, 'company'])]
    #[TestWith([Company::class, Individual::class, 'individual'])]
    public function test_changes_person_type(string $previousClass, string $newClass, string $type): void
    {
        $supplier = Supplier::factory()->for($previousClass::factory(), 'supplierable')->create();
        $previousPerson = $supplier->supplierable;
        $data = $this->validData($type);
        $this->fakeCompanyLookup();

        $updated = $this->service->save($data, $supplier)->fresh(['supplierable', 'contacts']);

        $this->assertSame($supplier->id, $updated->id);
        $this->assertInstanceOf($newClass, $updated->supplierable);
        $this->assertModelExists($updated->supplierable);
        $this->assertModelMissing($previousPerson);

        Http::assertSentCount($type === 'company' ? 1 : 0);
    }

    /** @param class-string<Individual|Company> $personClass */
    #[TestWith([Individual::class])]
    #[TestWith([Company::class])]
    public function test_delete_supplier(string $personClass): void
    {
        $supplier = Supplier::factory()->for($personClass::factory(), 'supplierable')->create();
        $person = $supplier->supplierable;
        $contact = Contact::factory()->create(['supplier_id' => $supplier->id]);
        $method = ContactMethod::factory()->create(['contact_id' => $contact->id]);
        $otherSupplier = Supplier::factory()->create();

        $this->service->delete($supplier);

        $this->assertModelMissing($supplier);
        $this->assertModelMissing($person);
        $this->assertModelMissing($contact);
        $this->assertModelMissing($method);
        $this->assertModelExists($otherSupplier);
    }

    public function test_save_rolls_back_when_contact_creation_fails(): void
    {
        $supplier = Supplier::factory()->create();
        $person = $supplier->supplierable;
        $originalName = $person->name;
        $originalCityId = $supplier->city_id;
        $contact = Contact::factory()->primary()->create(['supplier_id' => $supplier->id]);
        $method = ContactMethod::factory()->create(['contact_id' => $contact->id]);
        $data = $this->validData();
        $data['name'] = 'Nome que deve ser revertido';
        $data['primary_contact']['phones'] = [null];
        $transactionLevel = DB::transactionLevel();

        $this->assertThrows(fn () => $this->service->save($data, $supplier), TypeError::class);

        $this->assertSame($transactionLevel, DB::transactionLevel());
        $this->assertSame($originalName, $person->fresh()->name);
        $this->assertSame($originalCityId, $supplier->fresh()->city_id);
        $this->assertModelExists($contact);
        $this->assertModelExists($method);
        $this->assertDatabaseCount('contacts', 1);
    }

    public function test_delete_rolls_back_when_person_deletion_fails(): void
    {
        $supplier = Supplier::factory()->create();
        $person = $supplier->supplierable;
        $contact = Contact::factory()->primary()->create(['supplier_id' => $supplier->id]);
        $method = ContactMethod::factory()->create(['contact_id' => $contact->id]);
        $transactionLevel = DB::transactionLevel();
        $dispatcher = Individual::getEventDispatcher();
        Individual::setEventDispatcher(clone $dispatcher);
        Individual::deleting(function (): void {
            throw new RuntimeException('Falha ao excluir a pessoa.');
        });

        try {
            $this->assertThrows(fn () => $this->service->delete($supplier), RuntimeException::class);
        } finally {
            Individual::setEventDispatcher($dispatcher);
        }

        $this->assertSame($transactionLevel, DB::transactionLevel());
        $this->assertModelExists($supplier);
        $this->assertModelExists($person);
        $this->assertModelExists($contact);
        $this->assertModelExists($method);
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
        $city = City::factory()->create();
        $person = match ($type) {
            'individual' => Individual::factory()->raw(['cpf' => '12345678909']),
            'company' => Company::factory()->raw(['cnpj' => '04252011000110']),
        };

        return [
            ...$person,
            'type' => $type,
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
                'emails' => [['value' => 'contato@example.com', 'label' => 'Comercial']],
            ],
        ];
    }
}
