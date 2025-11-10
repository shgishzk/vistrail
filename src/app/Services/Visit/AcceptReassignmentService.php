<?php

namespace App\Services\Visit;

use App\Enums\VisitStatus;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptReassignmentService
{
    public function __construct(private UpdateVisitService $updateVisitService)
    {
    }

    public function execute(Visit $visit, int $userId): Visit
    {
        return DB::transaction(function () use ($visit, $userId) {
            $lockedVisit = Visit::query()
                ->whereKey($visit->getKey())
                ->lockForUpdate()
                ->with(['area', 'pins'])
                ->firstOrFail();

            if ($lockedVisit->status !== VisitStatus::PENDING_REASSIGNMENT) {
                throw ValidationException::withMessages([
                    'status' => __('この区域は再割当待機ではありません。'),
                ]);
            }

            $this->updateVisitService->execute($lockedVisit, [
                'status' => VisitStatus::REASSIGNED->value,
                'end_date' => Carbon::today(),
            ]);

            $newVisit = Visit::create([
                'user_id' => $userId,
                'area_id' => $lockedVisit->area_id,
                'start_date' => Carbon::today(),
                'status' => VisitStatus::IN_PROGRESS->value,
            ]);

            if ($lockedVisit->relationLoaded('pins')) {
                $lockedVisit->pins->each(function ($pin) use ($newVisit, $userId) {
                    $pin->visit_id = $newVisit->id;
                    $pin->user_id = $userId;
                    $pin->save();
                });
            } else {
                $lockedVisit->pins()->update([
                    'visit_id' => $newVisit->id,
                    'user_id' => $userId,
                ]);
            }

            return $newVisit->load(['area', 'pins']);
        });
    }
}
