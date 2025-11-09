<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Visit;
use App\Services\Visit\VisitFilterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaVisitsController extends Controller
{
    public function __construct(private VisitFilterService $visitFilterService)
    {
    }

    /**
     * Display the visits that belong to the given area.
     */
    public function index(Request $request, Area $area): View|RedirectResponse
    {
        $requestedAreaId = (int) $request->input('area_id', $area->id);

        if ($requestedAreaId !== $area->id) {
            $targetArea = Area::find($requestedAreaId);

            if ($targetArea !== null) {
                $queryParams = array_filter([
                    'user_id' => $request->input('user_id'),
                    'status' => $request->input('status'),
                    'start_from' => $request->input('start_from'),
                    'start_to' => $request->input('start_to'),
                ], static fn ($value) => $value !== null && $value !== '');

                return redirect()->route(
                    'admin.areas.visits',
                    array_merge(['area' => $targetArea->id], $queryParams)
                );
            }
        }

        $filters = $this->visitFilterService->extractFilters($request, $area);

        $visitsQuery = Visit::with(['user', 'area']);

        $visits = $this->visitFilterService->apply($visitsQuery, $filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $options = $this->visitFilterService->options();

        return view('admin.areas.visits.index', [
            'area' => $area,
            'visits' => $visits,
            'filters' => $filters,
            'filterUsers' => $options['users'],
            'filterAreas' => $options['areas'],
            'statusOptions' => $options['statuses'],
        ]);
    }
}
