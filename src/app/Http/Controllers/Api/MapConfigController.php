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

        $markerStyles = config('services.google.marker_styles', []);
        $markerDefaults = [
            'has_lock' => [
                'background' => $defaults[Setting::KEY_GOOGLE_MARKER_HAS_LOCK_BACKGROUND] ?? '#FFC107',
                'borderColor' => $defaults[Setting::KEY_GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR] ?? '#FF8F00',
                'glyphColor' => $defaults[Setting::KEY_GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR] ?? '#FF8F00',
            ],
            'no_lock' => [
                'background' => $defaults[Setting::KEY_GOOGLE_MARKER_NO_LOCK_BACKGROUND] ?? '#4CAF50',
                'borderColor' => $defaults[Setting::KEY_GOOGLE_MARKER_NO_LOCK_BORDER_COLOR] ?? '#388E3C',
                'glyphColor' => $defaults[Setting::KEY_GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR] ?? '#388E3C',
            ],
        ];

        $markerStyles['has_lock']['background'] = Setting::getValue(
            Setting::KEY_GOOGLE_MARKER_HAS_LOCK_BACKGROUND,
            $markerDefaults['has_lock']['background']
        ) ?: $markerDefaults['has_lock']['background'];
        $markerStyles['has_lock']['borderColor'] = Setting::getValue(
            Setting::KEY_GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR,
            $markerDefaults['has_lock']['borderColor']
        ) ?: $markerDefaults['has_lock']['borderColor'];
        $markerStyles['has_lock']['glyphColor'] = Setting::getValue(
            Setting::KEY_GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR,
            $markerDefaults['has_lock']['glyphColor']
        ) ?: $markerDefaults['has_lock']['glyphColor'];

        $markerStyles['no_lock']['background'] = Setting::getValue(
            Setting::KEY_GOOGLE_MARKER_NO_LOCK_BACKGROUND,
            $markerDefaults['no_lock']['background']
        ) ?: $markerDefaults['no_lock']['background'];
        $markerStyles['no_lock']['borderColor'] = Setting::getValue(
            Setting::KEY_GOOGLE_MARKER_NO_LOCK_BORDER_COLOR,
            $markerDefaults['no_lock']['borderColor']
        ) ?: $markerDefaults['no_lock']['borderColor'];
        $markerStyles['no_lock']['glyphColor'] = Setting::getValue(
            Setting::KEY_GOOGLE_MARKER_NO_LOCK_GLYPH_COLOR,
            $markerDefaults['no_lock']['glyphColor']
        ) ?: $markerDefaults['no_lock']['glyphColor'];

        return response()->json([
            'default_position' => [
                'lat' => (float) ($defaultPosition['lat'] ?? 0),
                'lng' => (float) ($defaultPosition['lng'] ?? 0),
            ],
            'marker_styles' => $markerStyles,
            'maps_api_key' => config('services.google.maps_api_key'),
            'map_radius_km' => max((float) $mapRadiusKm, 0.1),
            'assigned_boundary_kml' => Setting::getValue(
                Setting::KEY_GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY,
                (string) ($defaults[Setting::KEY_GOOGLE_MAPS_ASSIGNED_TERRITORY_BOUNDARY] ?? '')
            ),
        ]);
    }
}
