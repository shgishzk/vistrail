<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVisitRequest;
use App\Http\Requests\Admin\UpdateVisitRequest;
use App\Models\Area;
use App\Models\User;
use App\Models\Visit;
use App\States\VisitStatus\VisitStatusContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitsController extends Controller
{
    /**
     * Display a listing of the visits.
     */
    public function index(): View
    {
        $visits = Visit::with(['user', 'area'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.visits.index', compact('visits'));
    }

    /**
     * Show the form for creating a new visit.
     */
    public function create(Request $request): View
    {
        [$users, $areas] = $this->getSuggestionLists();

        $preselectedAreaId = null;
        $requestedAreaId = $request->query('area_id');

        if (!empty($requestedAreaId)) {
            $requestedAreaId = (int) $requestedAreaId;

            if ($areas->contains('id', $requestedAreaId)) {
                $preselectedAreaId = $requestedAreaId;
            }
        }

        return view('admin.visits.create', [
            'users' => $users,
            'areas' => $areas,
            'preselectedAreaId' => $preselectedAreaId,
        ]);
    }

    /**
     * Store a newly created visit in storage.
     */
    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $storeVisitService = new \App\Services\Visit\StoreVisitService();
        $storeVisitService->execute($validated);

        return redirect()->route('admin.visits')
            ->with('success', __('Visit created successfully.'));
    }

    /**
     * Show the form for editing the specified visit.
     */
    public function edit(Visit $visit): View
    {
        [$users, $areas] = $this->getSuggestionLists();
        $statusContext = VisitStatusContext::from($visit->status);

        return view('admin.visits.edit', [
            'visit' => $visit,
            'users' => $users,
            'areas' => $areas,
            'preselectedAreaId' => null,
            'currentStatus' => $statusContext->current(),
            'statusTransitions' => $statusContext->availableTransitions(),
        ]);
    }

    /**
     * Update the specified visit in storage.
     */
    public function update(UpdateVisitRequest $request, Visit $visit): RedirectResponse
    {
        $validated = $request->validated();

        $updateVisitService = new \App\Services\Visit\UpdateVisitService();
        $updateVisitService->execute($visit, $validated);

        return redirect()->route('admin.visits')
            ->with('success', __('Visit updated successfully.'));
    }

    /**
     * Remove the specified visit from storage.
     */
    public function destroy(Visit $visit): RedirectResponse
    {
        $deleteVisitService = new \App\Services\Visit\DeleteVisitService();
        $deleteVisitService->execute($visit);

        return redirect()->route('admin.visits')
            ->with('success', __('Visit deleted successfully.'));
    }

    /**
     * Retrieve suggestion lists for users and areas.
     */
    private function getSuggestionLists(): array
    {
        $users = User::orderBy('name')
            ->get(['id', 'name', 'name_kana', 'email']);

        $areas = Area::orderBy('number')
            ->get(['id', 'number', 'name']);

        return [$users, $areas];
    }
}
