<?php

namespace App\Services\Area;

use App\Models\Area;

class StoreAreaService
{
    /**
     * Create a new area with the given data.
     */
    public function execute(array $data): Area
    {
        return Area::create($data);
    }
}
