<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Company;
use App\Models\Individual;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use RuntimeException;

class SupplierDemoSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::query()
            ->with('state')
            ->where('name', 'Campinas')
            ->whereHas('state', fn ($query) => $query->where('code', 'SP'))
            ->first();

        if (! $city) {
            throw new RuntimeException('Run StateCitySeeder before SupplierDemoSeeder.');
        }

        $individuals = [
            ['name' => 'Ana Beatriz Martins', 'nickname' => 'Ana Martins', 'rg' => '451239876'],
            ['name' => 'Bruno Carvalho Lima', 'nickname' => 'Bruno Lima', 'rg' => '328741965'],
            ['name' => 'Carla Mendes Rocha', 'nickname' => null, 'rg' => '287654319'],
            ['name' => 'Daniel Oliveira Costa', 'nickname' => 'Dani Costa', 'rg' => '419852736'],
            ['name' => 'Eduarda Ferreira Alves', 'nickname' => null, 'rg' => '365987214'],
        ];

        foreach ($individuals as $index => $data) {
            $person = Individual::query()->updateOrCreate(
                ['cpf' => $this->cpf($index + 1)],
                $data,
            );

            $supplier = Supplier::query()->firstOrNew([
                'supplierable_type' => 'individual',
                'supplierable_id' => $person->id,
            ]);
            $supplier->supplierable()->associate($person);
            $this->saveSupplier($supplier, $city, $index + 1);
        }

        $companies = [
            ['corporate_name' => 'Aurora Comércio de Alimentos Ltda.', 'trade_name' => 'Aurora Alimentos'],
            ['corporate_name' => 'Brisa Soluções Digitais Ltda.', 'trade_name' => 'Brisa Digital'],
            ['corporate_name' => 'Central Obras e Serviços Ltda.', 'trade_name' => 'Central Obras'],
            ['corporate_name' => 'Delta Materiais Industriais Ltda.', 'trade_name' => 'Delta Industrial'],
            ['corporate_name' => 'Estação Logística Brasil Ltda.', 'trade_name' => 'Estação Logística'],
        ];

        foreach ($companies as $index => $data) {
            $company = Company::query()->updateOrCreate(
                ['cnpj' => $this->cnpj($index + 1)],
                $data + [
                    'state_registration_indicator' => 'contribuinte',
                    'state_registration' => (string) (123456000 + $index),
                    'municipal_registration' => null,
                    'tax_regime' => $index % 2 === 0 ? 'withheld_by_recipient' : 'payable_by_provider',
                    'cnpj_status' => 'ATIVA',
                ],
            );

            $supplier = Supplier::query()->firstOrNew([
                'supplierable_type' => 'company',
                'supplierable_id' => $company->id,
            ]);
            $supplier->supplierable()->associate($company);
            $this->saveSupplier($supplier, $city, $index + 6);
        }

        $this->command->info('Created or updated 10 demo suppliers (5 individuals and 5 companies).');
    }

    private function saveSupplier(Supplier $supplier, City $city, int $index): void
    {
        $supplier->fill([
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'active' => $index % 4 !== 0,
            'postal_code' => '13000000',
            'street' => "Rua de Teste {$index}",
            'number' => (string) (100 + $index),
            'complement' => null,
            'district' => $index % 2 === 0 ? 'Centro' : 'Cambuí',
            'reference' => null,
            'state' => 'SP',
            'city' => 'Campinas',
            'condominium' => false,
            'condominium_address' => null,
            'condominium_number' => null,
            'observation' => null,
        ])->save();

        $contact = $supplier->contacts()->firstOrCreate(['is_primary' => true]);
        $contact->methods()->updateOrCreate(
            ['kind' => 'phone', 'value' => '1998765'.str_pad((string) $index, 4, '0', STR_PAD_LEFT)],
            ['label' => 'Celular'],
        );
        $contact->methods()->updateOrCreate(
            ['kind' => 'email', 'value' => "fornecedor{$index}@example.test"],
            ['label' => 'Comercial'],
        );
    }

    private function cpf(int $index): string
    {
        $base = str_pad((string) (123456789 + $index * 137), 9, '0', STR_PAD_LEFT);
        $firstDigit = $this->checkDigit($base, range(10, 2));
        $secondDigit = $this->checkDigit($base.$firstDigit, range(11, 2));

        return $base.$firstDigit.$secondDigit;
    }

    private function cnpj(int $index): string
    {
        $base = '12345678'.str_pad((string) $index, 4, '0', STR_PAD_LEFT);
        $firstDigit = $this->checkDigit($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $secondDigit = $this->checkDigit($base.$firstDigit, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $base.$firstDigit.$secondDigit;
    }

    /** @param list<int> $weights */
    private function checkDigit(string $digits, array $weights): int
    {
        $sum = 0;
        foreach ($weights as $index => $weight) {
            $sum += (int) $digits[$index] * $weight;
        }
        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
