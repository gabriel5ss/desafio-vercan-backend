<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<State> */
class StateFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'ibge_code' => fake()->unique()->numberBetween(1, 99),
            'code' => mb_strtoupper(fake()->unique()->lexify('??')),
            'name' => fake('pt_BR')->format('state'),
        ];
    }
}
