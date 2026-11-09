<?php

namespace Modules\Supplier\Tests\Feature\Models;

use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\ContactMethod;
use Modules\Supplier\Models\Supplier;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ContactTest extends TestCase
{
    #[TestWith([1, true], 'primary contact')]
    #[TestWith([0, false], 'additional contact')]
    public function test_get_is_primary_attribute(int $value, bool $expected): void
    {
        $contact = Contact::factory()->make(['supplier_id' => null, 'is_primary' => $value]);

        $this->assertSame($expected, $contact->is_primary);
    }

    public function test_methods(): void
    {
        $supplier = Supplier::factory()->create();
        $contact = Contact::factory()->create(['supplier_id' => $supplier->id]);
        ContactMethod::factory()->create(['contact_id' => $contact->id, 'value' => '11999999999']);
        ContactMethod::factory()->email()->create(['contact_id' => $contact->id, 'value' => 'contato@example.com']);

        $methods = $contact->fresh()->methods;

        $this->assertCount(2, $methods);
    }
}
