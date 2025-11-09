<?php

namespace App\Services\Area;

use App\Enums\VisitStatus;
use App\Models\Setting;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AreaAvailabilityService
{
    private bool $requireStartElapsed;
    private int $startElapsedDays;
    private bool $requireEndElapsed;
    private int $endElapsedDays;

    /**
     * @var string[]
     */
    private array $blockedStatuses;

    public function __construct()
    {
        $this->requireStartElapsed = (bool) Setting::getInt(
            Setting::KEY_AREA_VACANCY_REQUIRE_START_ELAPSED_ENABLED,
            0
        );
        $this->startElapsedDays = max(0, Setting::getInt(
            Setting::KEY_AREA_VACANCY_REQUIRE_START_ELAPSED_DAYS,
            30
        ));

        $this->requireEndElapsed = (bool) Setting::getInt(
            Setting::KEY_AREA_VACANCY_REQUIRE_END_ELAPSED_ENABLED,
            0
        );
        $this->endElapsedDays = max(0, Setting::getInt(
            Setting::KEY_AREA_VACANCY_REQUIRE_END_ELAPSED_DAYS,
            14
        ));

        $this->blockedStatuses = [
            VisitStatus::IN_PROGRESS->value,
            VisitStatus::PENDING_REASSIGNMENT->value,
            VisitStatus::REASSIGNED->value,
        ];
    }

    public function applyLatestVisitAvailabilityScope(Builder $query): Builder
    {
        $query->whereNotIn('status', $this->blockedStatuses);

        if ($this->requireStartElapsed) {
            $query->whereNotNull('start_date')
                ->whereDate('start_date', '<=', $this->threshold($this->startElapsedDays));
        }

        if ($this->requireEndElapsed) {
            $query->whereNotNull('end_date')
                ->whereDate('end_date', '<=', $this->threshold($this->endElapsedDays));
        }

        return $query;
    }

    public function isAvailable(?Visit $latestVisit): bool
    {
        if (!$latestVisit) {
            return true;
        }

        $status = $latestVisit->status instanceof VisitStatus
            ? $latestVisit->status->value
            : (string) $latestVisit->status;

        if (in_array($status, $this->blockedStatuses, true)) {
            return false;
        }

        if ($this->requireStartElapsed) {
            $startDate = $latestVisit->start_date;
            if (!$startDate) {
                return false;
            }

            if (!$startDate instanceof Carbon) {
                $startDate = Carbon::parse($startDate);
            }

            if ($startDate->gt($this->threshold($this->startElapsedDays))) {
                return false;
            }
        }

        if ($this->requireEndElapsed) {
            $endDate = $latestVisit->end_date;
            if (!$endDate) {
                return false;
            }

            if (!$endDate instanceof Carbon) {
                $endDate = Carbon::parse($endDate);
            }

            if ($endDate->gt($this->threshold($this->endElapsedDays))) {
                return false;
            }
        }

        return true;
    }

    private function threshold(int $days): Carbon
    {
        return now()->copy()->subDays($days);
    }
}
