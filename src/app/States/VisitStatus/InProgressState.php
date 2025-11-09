<?php

namespace App\States\VisitStatus;

use App\Enums\VisitStatus;

class InProgressState extends VisitStatusState
{
    public function value(): VisitStatus
    {
        return VisitStatus::IN_PROGRESS;
    }

    protected function transitions(): array
    {
        return [
            VisitStatus::COMPLETED,
            VisitStatus::PENDING_REASSIGNMENT,
            VisitStatus::CANCELED,
        ];
    }
}
