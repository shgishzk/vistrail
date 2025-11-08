<?php

namespace App\Http\Controllers\Api;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyAreasController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $visits = Visit::with(['area'])
            ->where('user_id', $user->id)
            ->where('status', VisitStatus::IN_PROGRESS->value)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (Visit $visit) {
                $area = $visit->area;

                return [
                    'id' => $visit->id,
                    'start_date' => optional($visit->start_date)->toDateString(),
                    'end_date' => optional($visit->end_date)->toDateString(),
                    'status' => $visit->status instanceof VisitStatus
                        ? $visit->status->value
                        : (string) $visit->status,
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
            })
            ->filter(fn (array $visit) => $visit['area'] !== null)
            ->values();

        return response()->json([
            'visits' => $visits,
        ]);
    }
}
