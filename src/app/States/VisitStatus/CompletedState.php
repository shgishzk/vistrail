<?php

namespace App\States\VisitStatus;

use App\Enums\VisitStatus;

class CompletedState extends VisitStatusState
{
    public function value(): VisitStatus
    {
        return VisitStatus::COMPLETED;
    }

    protected function transitions(): array
    {
        return [
            VisitStatus::CANCELED,
        ];
    }
}
