<?php

declare(strict_types=1);

namespace Modules\Supplier\Database\Factories;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Supplier\Models\Company;
use Modules\Supplier\Models\Individual;
use Modules\Supplier\Models\Supplier;

/** @extends Factory<Supplier> */
final class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'supplierable_type' => (new Individual())->getMorphClass(),
            'supplierable_id' => Individual::factory(),
            'state_id' => State::factory(),
            'city_id' => fn (array $attributes): int => City::factory()->create(['state_id' => $attributes['state_id']])->id,
            'state' => fn (array $attributes): string => State::findOrFail($attributes['state_id'])->code,
            'city' => fn (array $attributes): string => City::findOrFail($attributes['city_id'])->name,
            'active' => true,
            'postal_code' => fake()->numerify('########'),
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'district' => fake()->word(),
            'condominium' => false,
        ];
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes): array => [
            'supplierable_type' => (new Company())->getMorphClass(),
            'supplierable_id' => Company::factory(),
        ]);
    }
}
