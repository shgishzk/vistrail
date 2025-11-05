<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function __invoke(Request $request)
    {
        $defaultPosition = config('services.google.default_position');

        $lat = (float) $request->input('lat', $defaultPosition['lat'] ?? 0);
        $lng = (float) $request->input('lng', $defaultPosition['lng'] ?? 0);

        // Half side of the square in kilometres (±1.5km gives a 3km window)
        $halfSideKm = 1.5;
        $latRad = deg2rad($lat);
        // 1 degree of latitude approximates this many kilometres (WGS84)
        $kmPerDegreeLat = 110.574;
        $degLat = $halfSideKm / $kmPerDegreeLat;
        $cosLat = cos($latRad);
        $kmPerDegreeLng = 111.320 * max(abs($cosLat), 1e-6);
        $degLng = $halfSideKm / $kmPerDegreeLng;

        $buildings = Building::query()
            ->select(['id', 'name', 'lat', 'lng', 'self_lock_type', 'url'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->whereBetween('lat', [$lat - $degLat, $lat + $degLat])
            ->whereBetween('lng', [$lng - $degLng, $lng + $degLng])
            ->where('is_public', true)
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
        ]);
    }
}
