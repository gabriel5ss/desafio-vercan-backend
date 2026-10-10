<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function states(): JsonResponse
    {
        $states = State::query()->orderBy('name')->get(['id', 'code', 'name']);

        return response()->json(['data' => $states]);
    }

    public function cities(State $state): JsonResponse
    {
        $cities = City::query()->where('state_id', $state->id)->orderBy('name')->get(['id', 'name']);

        return response()->json(['data' => $cities]);
    }
}
