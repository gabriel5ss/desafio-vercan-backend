<?php

namespace Tests\Feature\Models;

use App\Models\City;
use App\Models\State;
use Tests\TestCase;

class CityTest extends TestCase
{
    public function test_state(): void
    {
        $state = State::factory()->create();
        $city = City::factory()->for($state)->create();

        $relatedState = $city->fresh()->state;

        $this->assertInstanceOf(State::class, $relatedState);
        $this->assertSame($state->id, $relatedState->id);
    }
}
