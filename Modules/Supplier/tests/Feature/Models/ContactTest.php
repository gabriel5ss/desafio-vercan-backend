<?php

declare(strict_types=1);

namespace Modules\Supplier\Tests\Feature\Models;

use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\ContactMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ContactTest extends TestCase
{
    /** @return array<string, array{int, bool}> */
    public static function isPrimaryCases(): array
    {
        return [
            'primary contact' => [1, true],
            'additional contact' => [0, false],
        ];
    }

    #[DataProvider('isPrimaryCases')]
    public function test_get_is_primary_attribute(int $value, bool $expected): void
    {
        $contact = Contact::factory()->make(['supplier_id' => null, 'is_primary' => $value]);

        $this->assertSame($expected, $contact->is_primary);
    }

    public function test_methods(): void
    {
        $contact = Contact::factory()
            ->has(ContactMethod::factory()->state(['value' => '11999999999']), 'methods')
            ->has(ContactMethod::factory()->email()->state(['value' => 'contato@example.com']), 'methods')
            ->create();

        ContactMethod::factory()->create();

        $methods = $contact->fresh()->methods;

        $this->assertCount(2, $methods);
        $this->assertSame([
            'email' => 'contato@example.com',
            'phone' => '11999999999',
        ], $methods->pluck('value', 'kind')->sortKeys()->all());
    }
}
