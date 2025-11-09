<?php

namespace App\Http\Controllers\Api;

use App\Enums\VisitStatus;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Visit;
use App\Services\Area\AreaAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct(
        private readonly AreaAvailabilityService $availabilityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 40);
        $perPage = max(10, min($perPage, 100));
        $statusFilter = (string) $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = Area::query()
            ->with([
                'currentVisit.user:id,name',
                'latestVisit.user:id,name',
            ]);

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'active') {
            $query->whereHas('currentVisit');
        } elseif ($statusFilter === 'available') {
            $availabilityService = $this->availabilityService;
            $query->where(function ($availabilityQuery) use ($availabilityService) {
                $availabilityQuery
                    ->whereDoesntHave('latestVisit')
                    ->orWhereHas('latestVisit', function ($latestVisitQuery) use ($availabilityService) {
                        $availabilityService->applyLatestVisitAvailabilityScope($latestVisitQuery);
                    });
            });
        }

        $query->orderBy('number');

        $areas = $query->paginate($perPage);
        $items = $areas->getCollection()
            ->map(function (Area $area) {
                return $this->transformAreaSummary($area);
            })
            ->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $areas->currentPage(),
                'last_page' => $areas->lastPage(),
                'per_page' => $areas->perPage(),
                'total' => $areas->total(),
                'has_more' => $areas->hasMorePages(),
            ],
        ]);
    }

    public function show(Area $area): JsonResponse
    {
        $area->load([
            'currentVisit.user:id,name',
            'latestVisit.user:id,name',
        ]);

        return response()->json([
            'data' => $this->transformAreaDetail($area),
        ]);
    }

    protected function transformAreaSummary(Area $area): array
    {
        $currentVisit = $area->getRelationValue('currentVisit');
        $latestVisit = $area->getRelationValue('latestVisit');

        return [
            'id' => $area->id,
            'number' => $area->number,
            'name' => $area->name,
            'center_lat' => $area->center_lat,
            'center_lng' => $area->center_lng,
            'memo' => $area->memo,
            'has_boundary' => !empty($area->boundary_kml),
            'current_visit' => $currentVisit ? $this->transformVisit($currentVisit) : null,
            'latest_visit' => $latestVisit ? $this->transformVisit($latestVisit) : null,
            'is_available' => $this->availabilityService->isAvailable($latestVisit),
        ];
    }

    protected function transformAreaDetail(Area $area): array
    {
        return array_merge(
            $this->transformAreaSummary($area),
            [
                'boundary_kml' => $area->boundary_kml,
            ]
        );
    }

    protected function transformVisit(?Visit $visit): ?array
    {
        if (!$visit) {
            return null;
        }

        $status = $visit->status instanceof VisitStatus
            ? $visit->status->value
            : (string) $visit->status;

        return [
            'id' => $visit->id,
            'status' => $status,
            'start_date' => optional($visit->start_date)->toDateString(),
            'end_date' => optional($visit->end_date)->toDateString(),
            'user' => $visit->relationLoaded('user') && $visit->user
                ? [
                    'id' => $visit->user->id,
                    'name' => $visit->user->name,
                ]
                : null,
        ];
    }
}
