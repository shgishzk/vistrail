<?php

namespace App\States\VisitStatus;

use App\Enums\VisitStatus;

class VisitStatusContext
{
    private VisitStatusState $state;

    private function __construct()
    {
    }

    public static function from(VisitStatus|string|null $status): self
    {
        $context = new self();

        $normalized = self::normalize($status);

        $context->setState($context->makeState($normalized));

        return $context;
    }

    public function current(): VisitStatus
    {
        return $this->state->value();
    }

    public function canTransitionTo(VisitStatus|string $status): bool
    {
        return $this->state->canTransitionTo(self::normalize($status));
    }

    public function transitionTo(VisitStatus|string $status): VisitStatus
    {
        $state = $this->state->transitionTo(self::normalize($status));

        return $state->value();
    }

    /**
     * @return VisitStatus[]
     */
    public function availableTransitions(): array
    {
        return $this->state->availableTransitions();
    }

    public function setState(VisitStatusState $state): void
    {
        $this->state = $state;
    }

    public function makeState(VisitStatus $status): VisitStatusState
    {
        return match ($status) {
            VisitStatus::IN_PROGRESS => new InProgressState($this),
            VisitStatus::PENDING_REASSIGNMENT => new PendingReassignmentState($this),
            VisitStatus::REASSIGNED => new ReassignedState($this),
            VisitStatus::COMPLETED => new CompletedState($this),
            VisitStatus::CANCELED => new CanceledState($this),
        };
    }

    private static function normalize(VisitStatus|string|null $status): VisitStatus
    {
        if ($status instanceof VisitStatus) {
            return $status;
        }

        if ($status === null || $status === '') {
            return VisitStatus::IN_PROGRESS;
        }

        return VisitStatus::from((string) $status);
    }
}
