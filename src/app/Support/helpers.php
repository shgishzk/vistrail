<?php

use App\Models\Setting;

if (! function_exists('google_default_position')) {
    function google_default_position(): array
    {
        $defaults = Setting::defaults();

        return [
            'lat' => Setting::getFloat(
                Setting::KEY_GOOGLE_MAPS_DEFAULT_LAT,
                (float) ($defaults[Setting::KEY_GOOGLE_MAPS_DEFAULT_LAT] ?? 0)
            ),
            'lng' => Setting::getFloat(
                Setting::KEY_GOOGLE_MAPS_DEFAULT_LNG,
                (float) ($defaults[Setting::KEY_GOOGLE_MAPS_DEFAULT_LNG] ?? 0)
            ),
        ];
    }
}

if (! function_exists('google_marker_styles')) {
    function google_marker_styles(): array
    {
        $defaults = Setting::defaults();
        $markerStyles = config('services.google.marker_styles', []);

        $markerDefaults = [
            'has_lock' => [
                'background' => $defaults[Setting::KEY_GOOGLE_MARKER_HAS_LOCK_BACKGROUND] ?? '#EA4335',
                'borderColor' => $defaults[Setting::KEY_GOOGLE_MARKER_HAS_LOCK_BORDER_COLOR] ?? '#C5221F',
                'glyphColor' => $defaults[Setting::KEY_GOOGLE_MARKER_HAS_LOCK_GLYPH_COLOR] ?? '#FFFFFF',
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

        return $markerStyles;
    }
}
