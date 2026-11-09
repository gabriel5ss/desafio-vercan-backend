<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<City> */
class CityFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'ibge_code' => fake()->unique()->numberBetween(1000000, 9999999),
            'state_id' => State::factory(),
            'name' => fake()->city(),
        ];
    }
}
