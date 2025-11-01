<?php

namespace App\Services\Area;

use App\Models\Area;

class UpdateAreaService
{
    /**
     * Update an area with the given data.
     */
    public function execute(Area $area, array $data): Area
    {
        $area->update($data);

        return $area;
    }
}
