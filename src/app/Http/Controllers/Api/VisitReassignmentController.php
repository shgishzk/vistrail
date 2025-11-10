<?php

namespace App\Http\Controllers\Api;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Services\Visit\AcceptReassignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VisitReassignmentController extends Controller
{
    public function index(): JsonResponse
    {
        $visits = Visit::query()
            ->with(['area', 'user'])
            ->where('status', VisitStatus::PENDING_REASSIGNMENT->value)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Visit $visit) => $visit->area !== null)
            ->map(function (Visit $visit) {
                $area = $visit->area;
                $user = $visit->user;

                return [
                    'id' => $visit->id,
                    'start_date' => optional($visit->start_date)->toDateString(),
                    'updated_at' => optional($visit->updated_at)->toDateTimeString(),
                    'status' => $visit->status instanceof VisitStatus ? $visit->status->value : (string) $visit->status,
                    'memo' => $visit->memo,
                    'previous_publisher' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                    ] : null,
                    'area' => [
                        'id' => $area->id,
                        'number' => $area->number,
                        'name' => $area->name,
                        'boundary_kml' => $area->boundary_kml,
                        'center_lat' => $area->center_lat,
                        'center_lng' => $area->center_lng,
                    ],
                ];
            })
            ->values();

        return response()->json([
            'visits' => $visits,
        ]);
    }

    public function accept(
        Request $request,
        Visit $visit,
        AcceptReassignmentService $service
    ): JsonResponse {
        if ($visit->area === null) {
            throw ValidationException::withMessages([
                'area' => __('この区域は利用できません。'),
            ]);
        }

        $newVisit = $service->execute($visit, (int) $request->user()->id);

        return response()->json([
            'message' => __('区域を受け取りました。'),
            'visit' => $this->transformVisit($newVisit),
        ]);
    }

    private function transformVisit(Visit $visit): array
    {
        $area = $visit->area;

        return [
            'id' => $visit->id,
            'start_date' => optional($visit->start_date)->toDateString(),
            'status' => $visit->status instanceof VisitStatus ? $visit->status->value : (string) $visit->status,
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
