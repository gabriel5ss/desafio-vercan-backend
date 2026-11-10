<?php

declare(strict_types=1);

namespace Modules\Supplier\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Supplier\Models\Company;

/** @extends Factory<Company> */
final class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'corporate_name' => fake()->company(),
            'trade_name' => fake()->company(),
            'cnpj' => fake('pt_BR')->unique()->format('cnpj', [false]),
            'state_registration_indicator' => 'isento',
            'state_registration' => null,
            'municipal_registration' => null,
            'tax_regime' => 'payable_by_provider',
            'cnpj_status' => 'ATIVA',
        ];
    }
}
