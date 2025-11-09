<?php

namespace App\Services\Visit;

use App\Enums\VisitStatus;
use App\Models\Visit;

class StoreVisitService
{
    /**
     * Create a new visit with the given data.
     */
    public function execute(array $data): Visit
    {
        $data['status'] = VisitStatus::default()->value;

        return Visit::create($data);
    }
}
