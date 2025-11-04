<?php

namespace App\Services\Building;

use App\Models\Building;

class DeleteBuildingService
{
    public function execute(Building $building): bool
    {
        return $building->delete();
    }
}
