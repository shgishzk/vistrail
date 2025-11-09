<?php

namespace App\States\VisitStatus;

use App\Enums\VisitStatus;
use App\Exceptions\InvalidVisitStatusTransitionException;

abstract class VisitStatusState
{
    public function __construct(protected VisitStatusContext $context)
    {
    }

    abstract public function value(): VisitStatus;

    /**
     * @return VisitStatus[]
     */
    abstract protected function transitions(): array;

    public function canTransitionTo(VisitStatus $status): bool
    {
        return in_array($status, $this->transitions(), true);
    }

    public function transitionTo(VisitStatus $status): VisitStatusState
    {
        if (!$this->canTransitionTo($status)) {
            throw InvalidVisitStatusTransitionException::make($this->value(), $status);
        }

        $next = $this->context->makeState($status);
        $this->context->setState($next);

        return $next;
    }

    /**
     * @return VisitStatus[]
     */
    public function availableTransitions(): array
    {
        return $this->transitions();
    }
}
