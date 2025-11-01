<?php

namespace App\Services\Area;

use App\Models\Area;
use App\Support\KmlCenterCalculator;

class UpdateAreaService
{
    /**
     * Update an area with the given data.
     */
    public function execute(Area $area, array $data): Area
    {
        if (!empty($data['boundary_kml'])) {
            $center = KmlCenterCalculator::calculate($data['boundary_kml']);
            $data['center_lat'] = $center['lat'] ?? null;
            $data['center_lng'] = $center['lng'] ?? null;
        } elseif (!array_key_exists('center_lat', $data) && !array_key_exists('center_lng', $data)) {
            $data['center_lat'] = $area->center_lat;
            $data['center_lng'] = $area->center_lng;
        }

        $area->update($data);
        
        return $area;
    }
}
