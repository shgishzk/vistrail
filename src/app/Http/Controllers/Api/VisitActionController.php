<?php

namespace App\Http\Controllers\Api;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Services\Visit\UpdateVisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VisitActionController extends Controller
{
    public function requestReassignment(Request $request, Visit $visit, UpdateVisitService $service): JsonResponse
    {
        $this->authorizeVisit($request, $visit);

        $this->ensureTransitionIsAllowed($visit, VisitStatus::PENDING_REASSIGNMENT);

        $updated = $service->execute($visit, [
            'status' => VisitStatus::PENDING_REASSIGNMENT->value,
        ])->loadMissing('area');

        return response()->json([
            'message' => __('区域を再割当待機に変更しました。'),
            'visit' => $this->transformVisit($updated),
        ]);
    }

    public function returnAsUnstarted(Request $request, Visit $visit, UpdateVisitService $service): JsonResponse
    {
        $this->authorizeVisit($request, $visit);

        $this->ensureTransitionIsAllowed($visit, VisitStatus::CANCELED);

        $updated = $service->execute($visit, [
            'status' => VisitStatus::CANCELED->value,
            'end_date' => Carbon::today(),
        ])->loadMissing('area');

        return response()->json([
            'message' => __('訪問を未着手として返却しました。'),
            'visit' => $this->transformVisit($updated),
        ]);
    }

    private function authorizeVisit(Request $request, Visit $visit): void
    {
        if ((int) $visit->user_id !== (int) $request->user()->id) {
            abort(403, __('指定された訪問にはアクセスできません。'));
        }
    }

    private function ensureTransitionIsAllowed(Visit $visit, VisitStatus $target): void
    {
        $context = $visit->statusContext();

        if (!$context->canTransitionTo($target)) {
            throw ValidationException::withMessages([
                'status' => __('この訪問は更新できません。'),
            ]);
        }
    }

    private function transformVisit(Visit $visit): array
    {
        $area = $visit->area;

        return [
            'id' => $visit->id,
            'start_date' => optional($visit->start_date)->toDateString(),
            'end_date' => optional($visit->end_date)->toDateString(),
            'status' => $visit->status instanceof VisitStatus ? $visit->status->value : (string) $visit->status,
            'memo' => $visit->memo,
            'area' => $area ? [
                'id' => $area->id,
                'number' => $area->number,
                'name' => $area->name,
                'boundary_kml' => $area->boundary_kml,
                'center_lat' => $area->center_lat,
                'center_lng' => $area->center_lng,
            ] : null,
        ];
    }
}
