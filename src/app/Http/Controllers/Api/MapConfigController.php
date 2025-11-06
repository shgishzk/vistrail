<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class MapConfigController extends Controller
{
    public function __invoke()
    {
        $defaults = Setting::defaults();

        $defaultPosition = [
            'lat' => Setting::getFloat(Setting::KEY_GOOGLE_MAPS_DEFAULT_LAT, (float) ($defaults[Setting::KEY_GOOGLE_MAPS_DEFAULT_LAT] ?? 0)),
            'lng' => Setting::getFloat(Setting::KEY_GOOGLE_MAPS_DEFAULT_LNG, (float) ($defaults[Setting::KEY_GOOGLE_MAPS_DEFAULT_LNG] ?? 0)),
        ];

        $mapRadiusKm = Setting::getFloat(
            Setting::KEY_BUILDING_MAP_HALF_SIDE_KM,
            (float) ($defaults[Setting::KEY_BUILDING_MAP_HALF_SIDE_KM] ?? 1.0),
        );

        return response()->json([
            'default_position' => [
                'lat' => (float) ($defaultPosition['lat'] ?? 0),
                'lng' => (float) ($defaultPosition['lng'] ?? 0),
            ],
            'marker_styles' => config('services.google.marker_styles'),
            'maps_api_key' => config('services.google.maps_api_key'),
            'map_radius_km' => max((float) $mapRadiusKm, 0.1),
        ]);
    }
}
