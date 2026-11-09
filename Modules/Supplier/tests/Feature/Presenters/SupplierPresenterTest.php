<?php

namespace Modules\Supplier\Tests\Feature\Presenters;

use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\ContactMethod;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;
use Override;
use Tests\TestCase;

class SupplierPresenterTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['request']->setLaravelSession($this->app['session.store']);
    }

    public function test_default_data(): void
    {
        $data = (new Supplier)->present()->formData();

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

    public function test_individual_data(): void
    {
        $person = Individual::factory()->create(['name' => 'User', 'cpf' => '12345678909']);
        $supplier = Supplier::factory()->for($person, 'supplierable')->create([
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
        $company = Company::factory()->create(['corporate_name' => 'Empresa Ltda', 'cnpj' => '04252011000110']);
        $supplier = Supplier::factory()->for($company, 'supplierable')->create();

        $data = $supplier->present()->formData();

        $this->assertSame('company', $data['type']);
        $this->assertSame('Empresa Ltda', $data['corporate_name']);
        $this->assertSame('04252011000110', $data['cnpj']);
        $this->assertNull($data['cpf']);
    }

    public function test_contacts(): void
    {
        $supplier = Supplier::factory()->create();
        $primary = Contact::factory()->primary()->create(['supplier_id' => $supplier->id]);
        $additional = Contact::factory()->create(['supplier_id' => $supplier->id, 'name' => 'User']);
        ContactMethod::factory()->create(['contact_id' => $primary->id, 'value' => '11999999999']);
        ContactMethod::factory()->email()->create(['contact_id' => $primary->id, 'value' => 'principal@example.com']);
        ContactMethod::factory()->create(['contact_id' => $additional->id, 'value' => '21988887777']);
        ContactMethod::factory()->email()->create(['contact_id' => $additional->id, 'value' => 'user@example.com']);

        $data = $supplier->present()->formData();

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

        $data = (new Supplier)->present()->formData();

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
