<?php

if (! function_exists('google_default_position')) {
    function google_default_position(): array
    {
        return [
            'lat' => (float) config('services.google.default_position.lat'),
            'lng' => (float) config('services.google.default_position.lng'),
        ];
    }
}
