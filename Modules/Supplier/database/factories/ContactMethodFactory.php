<?php

declare(strict_types=1);

namespace Modules\Supplier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Supplier\Models\Contact;
use Modules\Supplier\Models\ContactMethod;

/** @extends Factory<ContactMethod> */
final class ContactMethodFactory extends Factory
{
    protected $model = ContactMethod::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'kind' => 'phone',
            'value' => fake()->numerify('119########'),
            'label' => 'Celular',
        ];
    }

    public function email(): static
    {
        return $this->state(fn (array $attributes): array => [
            'kind' => 'email',
            'value' => fake()->safeEmail(),
            'label' => 'Pessoal',
        ]);
    }
}
