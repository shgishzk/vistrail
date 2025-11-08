<?php

namespace App\States\VisitStatus;

use App\Enums\VisitStatus;

class CanceledState extends VisitStatusState
{
    public function value(): VisitStatus
    {
        return VisitStatus::CANCELED;
    }

    protected function transitions(): array
    {
        return [];
    }
}
