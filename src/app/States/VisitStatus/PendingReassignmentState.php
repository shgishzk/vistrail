<?php

namespace App\States\VisitStatus;

use App\Enums\VisitStatus;

class PendingReassignmentState extends VisitStatusState
{
    public function value(): VisitStatus
    {
        return VisitStatus::PENDING_REASSIGNMENT;
    }

    protected function transitions(): array
    {
        return [
            VisitStatus::IN_PROGRESS,
            VisitStatus::REASSIGNED,
        ];
    }
}
