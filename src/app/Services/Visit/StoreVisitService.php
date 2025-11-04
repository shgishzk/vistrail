<?php

namespace App\Services\Visit;

use App\Models\Visit;

class StoreVisitService
{
    /**
     * Create a new visit with the given data.
     */
    public function execute(array $data): Visit
    {
        return Visit::create($data);
    }
}
