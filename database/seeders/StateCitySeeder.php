<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StateCitySeeder extends Seeder
{
    public function run(): void
    {
        $baseUrl = 'https://servicodados.ibge.gov.br/api/v1/localidades';
        $states = Http::acceptJson()->timeout(30)->get("{$baseUrl}/estados", ['orderBy' => 'nome'])->throw()->json();

        if (! is_array($states) || $states === []) {
            throw new RuntimeException('The IBGE API returned no states.');
        }

        $citiesByState = [];
        foreach ($states as $state) {
            $cities = Http::acceptJson()->timeout(30)
                ->get("{$baseUrl}/estados/{$state['id']}/municipios", ['orderBy' => 'nome'])
                ->throw()
                ->json();

            if (! is_array($cities)) {
                throw new RuntimeException("The IBGE API returned invalid cities for {$state['sigla']}.");
            }

            $citiesByState[$state['sigla']] = $cities;
        }

        DB::transaction(function () use ($states, $citiesByState): void {
            $now = now();
            DB::table('states')->upsert(array_map(fn (array $state): array => [
                'ibge_code' => $state['id'],
                'code' => $state['sigla'],
                'name' => $state['nome'],
                'created_at' => $now,
                'updated_at' => $now,
            ], $states), ['ibge_code'], ['code', 'name', 'updated_at']);

            $stateIds = DB::table('states')->pluck('id', 'code');
            foreach ($citiesByState as $stateCode => $cities) {
                foreach (array_chunk($cities, 500) as $chunk) {
                    DB::table('cities')->upsert(array_map(fn (array $city): array => [
                        'ibge_code' => $city['id'],
                        'state_id' => $stateIds[$stateCode],
                        'name' => $city['nome'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $chunk), ['ibge_code'], ['state_id', 'name', 'updated_at']);
                }
            }
        });
    }
}
