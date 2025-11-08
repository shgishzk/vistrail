<?php

namespace App\Exceptions;

use App\Enums\VisitStatus;
use DomainException;

class InvalidVisitStatusTransitionException extends DomainException
{
    public static function make(VisitStatus $from, VisitStatus $to): self
    {
        return new self(sprintf(
            'Cannot transition visit status from "%s" to "%s".',
            $from->value,
            $to->value
        ));
    }
}
