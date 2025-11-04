<?php

namespace App\Services\Building;

use App\Models\Building;

class StoreBuildingService
{
    public function execute(array $data): Building
    {
        return Building::create($data);
    }
}
