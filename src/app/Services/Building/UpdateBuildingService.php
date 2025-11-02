<?php

namespace App\Services\Building;

use App\Models\Building;

class UpdateBuildingService
{
    public function execute(Building $building, array $data): Building
    {
        $building->update($data);

        return $building;
    }
}
