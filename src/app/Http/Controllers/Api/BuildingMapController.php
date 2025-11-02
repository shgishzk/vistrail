<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingMapController extends Controller
{
    public function __invoke(Request $request)
    {
        $defaultPosition = config('services.google.default_position');

        $lat = (float) $request->input('lat', $defaultPosition['lat'] ?? 0);
        $lng = (float) $request->input('lng', $defaultPosition['lng'] ?? 0);

        // Half side of the square in kilometers (0.5km in each direction to form 1km square)
        $halfSideKm = 0.5;
        $kmPerDegreeLat = 111.32;
        $degLat = $halfSideKm / $kmPerDegreeLat;
        $cosLat = cos(deg2rad($lat));
        $cosLat = abs($cosLat) < 1e-6 ? 1e-6 : $cosLat;
        $degLng = $halfSideKm / ($kmPerDegreeLat * $cosLat);

        $buildings = Building::query()
            ->select(['id', 'name', 'lat', 'lng', 'self_lock_type', 'url'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->whereBetween('lat', [$lat - $degLat, $lat + $degLat])
            ->whereBetween('lng', [$lng - $degLng, $lng + $degLng])
            ->orderBy('name')
            ->get()
            ->map(function (Building $building) {
                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'lat' => (float) $building->lat,
                    'lng' => (float) $building->lng,
                    'self_lock_type' => $building->self_lock_type?->value,
                    'url' => $building->url,
                ];
            });

        return response()->json([
            'buildings' => $buildings,
            'default_position' => [
                'lat' => (float) ($defaultPosition['lat'] ?? 0),
                'lng' => (float) ($defaultPosition['lng'] ?? 0),
            ],
            'marker_styles' => config('services.google.marker_styles'),
            'maps_api_key' => config('services.google.maps_api_key'),
        ]);
    }
}
