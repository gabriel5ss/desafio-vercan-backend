<?php

declare(strict_types=1);

namespace Modules\Supplier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\Supplier;

/** @extends Factory<Contact> */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'is_primary' => false,
            'name' => fake()->name(),
            'company' => null,
            'position' => null,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_primary' => true,
            'name' => null,
            'company' => null,
            'position' => null,
        ]);
    }
}
