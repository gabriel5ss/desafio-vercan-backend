<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\State;
use App\Models\User;
use Database\Seeders\StateCitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_seeder_updates_ibge_states_and_cities(): void
    {
        Http::fake([
            'servicodados.ibge.gov.br/api/v1/localidades/estados?*' => Http::response([
                ['id' => 35, 'sigla' => 'SP', 'nome' => 'São Paulo'],
            ]),
            'servicodados.ibge.gov.br/api/v1/localidades/estados/35/municipios?*' => Http::response([
                ['id' => 3509502, 'nome' => 'Campinas'],
            ]),
        ]);

        $this->seed(StateCitySeeder::class);
        $this->seed(StateCitySeeder::class);

        $this->assertDatabaseCount('states', 1);
        $this->assertDatabaseHas('states', ['ibge_code' => 35, 'code' => 'SP', 'name' => 'São Paulo']);
        $this->assertDatabaseCount('cities', 1);
        $this->assertDatabaseHas('cities', ['ibge_code' => 3509502, 'name' => 'Campinas']);
    }

    public function test_authenticated_user_can_list_states_and_cities(): void
    {
        $state = State::create(['ibge_code' => 35, 'code' => 'SP', 'name' => 'São Paulo']);
        City::create(['ibge_code' => 3509502, 'state_id' => $state->id, 'name' => 'Campinas']);
        $token = User::factory()->create()->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/locations/states')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'SP');
        $this->withToken($token)->getJson("/api/locations/states/{$state->id}/cities")
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Campinas');
    }
}
