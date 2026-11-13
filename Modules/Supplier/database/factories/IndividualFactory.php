<?php

declare(strict_types=1);

namespace Modules\Supplier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Supplier\Models\Individual;

/** @extends Factory<Individual> */
class IndividualFactory extends Factory
{
    protected $model = Individual::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nickname' => null,
            'cpf' => fake('pt_BR')->unique()->format('cpf', [false]),
            'rg' => fake()->numerify('#########'),
        ];
    }
}
