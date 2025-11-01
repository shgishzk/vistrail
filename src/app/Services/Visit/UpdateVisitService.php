<?php

namespace App\Services\Visit;

use App\Models\Visit;

class UpdateVisitService
{
    /**
     * Update the specified visit with the given data.
     */
    public function execute(Visit $visit, array $data): Visit
    {
        $visit->update($data);

        return $visit;
    }
}
