<?php

namespace App\Services\Area;

use App\Models\Area;
use App\Support\GeoJsonCenterCalculator;

class StoreAreaService
{
    /**
     * Create a new area with the given data.
     */
    public function execute(array $data): Area
    {
        if (!empty($data['boundary_geojson'])) {
            $center = GeoJsonCenterCalculator::calculate($data['boundary_geojson']);
            $data['center_lat'] = $center['lat'] ?? null;
            $data['center_lng'] = $center['lng'] ?? null;
        }

        return Area::create($data);
    }
}
