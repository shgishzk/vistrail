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
        return config('services.google.marker_styles');
    }
}
