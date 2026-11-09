<?php

namespace Tests\Feature\Models;

use App\Models\City;
use App\Models\State;
use Tests\TestCase;

class StateTest extends TestCase
{
    public function test_cities(): void
    {
        $state = State::factory()->create();
        $cities = City::factory()->count(2)->for($state)->create();
        City::factory()->create();

        $relatedCities = $state->fresh()->cities;

        $this->assertContainsOnlyInstancesOf(City::class, $relatedCities);
        $this->assertSame($cities->modelKeys(), $relatedCities->pluck('id')->sort()->values()->all());
    }
}
