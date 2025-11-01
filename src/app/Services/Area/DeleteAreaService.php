<?php

namespace App\Services\Area;

use App\Models\Area;

class DeleteAreaService
{
    /**
     * Delete the specified area.
     */
    public function execute(Area $area): bool
    {
        return $area->delete();
    }
}
