<?php

namespace App\Http\Controllers\Api;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Visit;
use App\Services\Area\AreaAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AreaPickupController extends Controller
{
    public function __invoke(Request $request, AreaAvailabilityService $availabilityService): JsonResponse
    {
        $validated = $request->validate([
            'area_id' => ['required', 'integer', 'exists:areas,id'],
        ]);

        $user = $request->user();
        $area = Area::query()->findOrFail($validated['area_id']);

        $visit = null;

        DB::transaction(function () use ($area, $user, $availabilityService, &$visit) {
            $latestVisit = Visit::query()
                ->where('area_id', $area->id)
                ->latest('start_date')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$availabilityService->isAvailable($latestVisit)) {
                throw ValidationException::withMessages([
                    'area_id' => __('この区域は既に割り当てられました。'),
                ]);
            }

            $visit = Visit::create([
                'user_id' => $user->id,
                'area_id' => $area->id,
                'start_date' => Carbon::today(),
                'status' => VisitStatus::IN_PROGRESS->value,
            ]);
        });

        return response()->json([
            'message' => __('区域を割り当てました。'),
            'visit' => [
                'id' => $visit->id,
                'area_id' => $visit->area_id,
                'user_id' => $visit->user_id,
                'start_date' => optional($visit->start_date)->toDateString(),
                'status' => $visit->status instanceof VisitStatus ? $visit->status->value : (string) $visit->status,
            ],
        ]);
    }
}
