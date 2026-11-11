<?php

declare(strict_types=1);

namespace Modules\Supplier\Tests\Feature\Presenters;

use Closure;
use Modules\Supplier\Database\Factories\SupplierFactory;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SupplierPresenterTest extends TestCase
{
    private SupplierFactory $supplier;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::factory();

        $this->app['request']->setLaravelSession($this->app['session.store']);
        $this->withViewErrors([]);
    }

    /** @return iterable<string, array{Closure(array<string, mixed>): array<string, mixed>}> */
    public static function contactsProvider(): iterable
    {
        yield 'saved' => [static fn (array $input): array => []];
        yield 'old input' => [static fn (array $input): array => ['_old_input' => $input]];
    }

    /** @return iterable<string, array{string|array<string>}> */
    public static function invalidTypes(): iterable
    {
        yield 'valor desconhecido' => ['invalido'];
        yield 'array' => [['individual']];
    }

    public function test_default_data(): void
    {
        $data = $this->supplier->newModel()->present()->formData();

        $this->assertSame('company', $data['type']);
        $this->assertSame(1, $data['active']);
        $this->assertNull($data['cnpj']);
        $this->assertSame('', $data['primary_contact']['phones'][0]['value']);
        $this->assertSame('', $data['primary_contact']['emails'][0]['value']);
        $this->assertSame([], $data['additional_contacts']);
        $this->assertSame([], $data['observation']);
        $this->assertSame(['phones' => 1, 'emails' => 1], $data['next_contact_indexes']);
        $this->assertSame(0, $data['next_additional_contact_index']);
    }

    /** @param string|array<string> $type */
    #[DataProvider('invalidTypes')]
    public function test_form_data_failed(string|array $type): void
    {
        $supplier = $this->supplier->for(Individual::factory()->state(['name' => 'Alfa']), 'supplierable')->create();
        $this->withSession(['_old_input' => ['type' => $type]]);

        $data = $supplier->present()->formData();

        $this->assertSame('individual', $data['type']);
        $this->assertSame('Alfa', $data['name']);
    }

    public function test_individual_data(): void
    {
        $supplier = $this->supplier
            ->for(Individual::factory()->state(['name' => 'User', 'cpf' => '12345678909']), 'supplierable')
            ->create([
                'active' => false,
                'observation' => '{"content":"Observação"}',
            ]);

        $data = $supplier->present()->formData();

        $this->assertSame('individual', $data['type']);
        $this->assertSame('User', $data['name']);
        $this->assertSame('12345678909', $data['cpf']);
        $this->assertNull($data['cnpj']);
        $this->assertFalse($data['active']);
        $this->assertSame(['content' => 'Observação'], $data['observation']);
    }

    public function test_company_data(): void
    {
        $supplier = $this->supplier
            ->for(Company::factory()->state(['corporate_name' => 'Empresa Ltda', 'cnpj' => '04252011000110']), 'supplierable')
            ->create();

        $data = $supplier->present()->formData();

        $this->assertSame('company', $data['type']);
        $this->assertSame('Empresa Ltda', $data['corporate_name']);
        $this->assertSame('04252011000110', $data['cnpj']);
        $this->assertNull($data['cpf']);
    }

    #[DataProvider('contactsProvider')]
    public function test_contacts(Closure $sessionData): void
    {
        $supplier = $this->supplier->create();
        $primaryContact = Contact::factory()
            ->primary()
            ->create(['supplier_id' => $supplier->id]);
        $additionalContact = Contact::factory()
            ->create(['supplier_id' => $supplier->id, 'name' => 'User']);

        $primaryContact->methods()->createMany([
            ['kind' => 'phone', 'value' => '11999999999'],
            ['kind' => 'email', 'value' => 'principal@example.com'],
        ]);
        $additionalContact->methods()->createMany([
            ['kind' => 'phone', 'value' => '21988887777'],
            ['kind' => 'email', 'value' => 'user@example.com'],
        ]);

        $primaryPhone = $primaryContact->methods()->where('kind', 'phone')->firstOrFail();
        $primaryEmail = $primaryContact->methods()->where('kind', 'email')->firstOrFail();
        $additionalPhone = $additionalContact->methods()->where('kind', 'phone')->firstOrFail();
        $additionalEmail = $additionalContact->methods()->where('kind', 'email')->firstOrFail();
        $this->withSession($sessionData([
            'primary_contact' => ['id' => $primaryContact->id, 'phones' => [$primaryPhone->only(['id', 'value', 'label'])], 'emails' => [$primaryEmail->only(['id', 'value', 'label'])]],
            'additional_contacts' => [[...$additionalContact->only(['id', 'name']), 'phones' => [$additionalPhone->only(['id', 'value', 'label'])], 'emails' => [$additionalEmail->only(['id', 'value', 'label'])]]],
        ]));

        $data = $supplier->present()->formData();
        $primaryView = $this->view('supplier::partials.form._contacts', ['formData' => $data]);
        $additionalView = $this->view('supplier::partials.form._additional_contacts', ['formData' => $data]);

        $primaryView->assertSee('name="primary_contact[id]" value="'.$primaryContact->id.'"', false);
        $primaryView->assertSee('name="primary_contact[phones][0][id]" value="'.$primaryPhone->id.'"', false);
        $primaryView->assertSee('name="primary_contact[emails][0][id]" value="'.$primaryEmail->id.'"', false);
        $primaryView->assertSee('name="primary_contact[phones][__INDEX__][id]" value=""', false);
        $primaryView->assertSee('name="primary_contact[emails][__INDEX__][id]" value=""', false);
        $additionalView->assertSee('name="additional_contacts[0][id]" value="'.$additionalContact->id.'"', false);
        $additionalView->assertSee('name="additional_contacts[0][phones][0][id]" value="'.$additionalPhone->id.'"', false);
        $additionalView->assertSee('name="additional_contacts[0][emails][0][id]" value="'.$additionalEmail->id.'"', false);
        $additionalView->assertSee('name="additional_contacts[__CONTACT_INDEX__][id]" value=""', false);
        $this->assertSame('11999999999', $data['primary_contact']['phones'][0]['value']);
        $this->assertSame('principal@example.com', $data['primary_contact']['emails'][0]['value']);
        $this->assertCount(1, $data['additional_contacts']);
        $this->assertSame('User', $data['additional_contacts'][0]['fields']['name']['value']);

        $groups = $data['additional_contacts'][0]['method_groups'];

        $this->assertSame('21988887777', $groups['phones']['rows'][0]['method']['value']);
        $this->assertSame('user@example.com', $groups['emails']['rows'][0]['method']['value']);
    }

    public function test_contact_indexes(): void
    {
        $this->withSession(['_old_input' => [
            'primary_contact' => [
                'phones' => [2 => ['value' => '11999999999', 'label' => 'Celular']],
                'emails' => [6 => ['value' => 'contato@example.com', 'label' => 'Comercial']],
            ],
            'additional_contacts' => [3 => [
                'name' => 'User',
                'phones' => [5 => ['value' => '21988887777', 'label' => 'Celular']],
                'emails' => [],
            ]],
        ]]);

        $data = $this->supplier->newModel()->present()->formData();

        $this->assertSame(['phones' => 3, 'emails' => 7], $data['next_contact_indexes']);
        $this->assertSame(4, $data['next_additional_contact_index']);
        $this->assertSame('primary_contact.phones.2.value', $data['primary_contact']['phones'][2]['value_field']);
        $this->assertSame('primary_contact.phones.__INDEX__.value', $data['primary_contact_templates']['phones']['value_field']);

        $contact = $data['additional_contacts'][3];

        $this->assertSame('additional_contacts.3.name', $contact['fields']['name']['error_key']);
        $this->assertTrue($contact['fields']['name']['required']);
        $this->assertFalse($contact['fields']['company']['required']);
        $this->assertSame(120, $contact['fields']['position']['maxlength']);
        $this->assertSame(6, $contact['method_groups']['phones']['next_index']);
        $this->assertSame(0, $contact['method_groups']['emails']['next_index']);

        $template = $data['additional_contact_template']['method_groups']['phones']['template'];

        $this->assertSame('additional_contacts.__CONTACT_INDEX__.phones.__INDEX__.value', $template['methodForm']['value_field']);
    }
}
