<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class MapConfigController extends Controller
{
    public function __invoke()
    {
        $defaultPosition = config('services.google.default_position', ['lat' => 0, 'lng' => 0]);

        return response()->json([
            'default_position' => [
                'lat' => (float) ($defaultPosition['lat'] ?? 0),
                'lng' => (float) ($defaultPosition['lng'] ?? 0),
            ],
            'marker_styles' => config('services.google.marker_styles'),
            'maps_api_key' => config('services.google.maps_api_key'),
            'map_radius_km' => (float) config('buildings.map.half_side_km', 1.0),
        ]);
    }
}
