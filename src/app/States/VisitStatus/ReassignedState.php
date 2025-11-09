<?php

namespace App\States\VisitStatus;

use App\Enums\VisitStatus;

class ReassignedState extends VisitStatusState
{
    public function value(): VisitStatus
    {
        return VisitStatus::REASSIGNED;
    }

    protected function transitions(): array
    {
        return [];
    }
}
