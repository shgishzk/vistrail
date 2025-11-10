<?php

namespace App\Services\Visit;

use App\Enums\VisitStatus;
use App\Models\Visit;
use App\States\VisitStatus\VisitStatusContext;

class UpdateVisitService
{
    /**
     * Update the specified visit with the given data.
     */
    public function execute(Visit $visit, array $data): Visit
    {
        if (array_key_exists('status', $data)) {
            $requested = $data['status'];

            $context = VisitStatusContext::from($visit->status);

            $target = $requested instanceof VisitStatus
                ? $requested
                : VisitStatus::from((string) $requested);

            if ($target !== $context->current()) {
                $context->transitionTo($target);
            }

            $data['status'] = $context->current()->value;
        }

        $visit->update($data);

        if (isset($data['status']) && in_array($data['status'], [
            VisitStatus::CANCELED->value,
            VisitStatus::COMPLETED->value,
        ], true)) {
            $visit->pins()->delete();
        }

        return $visit->refresh();
    }
}
