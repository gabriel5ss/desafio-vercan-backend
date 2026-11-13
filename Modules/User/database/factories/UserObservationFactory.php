<?php

declare(strict_types=1);

namespace Modules\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\User\Models\User;
use Modules\User\Models\UserObservation;

/**
 * Gera observações relacionadas para os testes.
 *
 * @extends Factory<UserObservation>
 */
class UserObservationFactory extends Factory
{
    /** @var class-string<UserObservation> Model criada pela factory. */
    protected $model = UserObservation::class;

    /**
     * Define os atributos da observação.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'content' => fake()->sentence()];
    }
}
