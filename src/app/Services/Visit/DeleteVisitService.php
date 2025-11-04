<?php

namespace App\Services\Visit;

use App\Models\Visit;

class DeleteVisitService
{
    /**
     * Delete the specified visit and detach related resources if necessary.
     */
    public function execute(Visit $visit): bool
    {
        return $visit->delete();
    }
}
