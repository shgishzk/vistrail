<?php

return [
    'alerts' => [
        'visited_within_days' => env('ROOM_VISITED_ALERT_DAYS', 90),
        'not_at_home_within_days' => env('ROOM_NOT_AT_HOME_ALERT_DAYS', 7),
    ],
    'map' => [
        'half_side_km' => env('BUILDING_MAP_HALF_SIDE_KM', 1.0),
    ],
];
