<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SelfLockType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBuildingRequest;
use App\Http\Requests\Admin\UpdateBuildingRequest;
use App\Models\Building;
use App\Services\Building\StoreBuildingService;
use App\Services\Building\UpdateBuildingService;
use App\Services\Building\DeleteBuildingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuildingsController extends Controller
{
    public function index(): View
    {
        $buildings = Building::orderBy('name')->paginate(15);

        return view('admin.buildings.index', compact('buildings'));
    }

    public function create(): View
    {
        $selfLockOptions = SelfLockType::cases();
        $selfLockLabels = SelfLockType::labels();

        return view('admin.buildings.create', compact('selfLockOptions', 'selfLockLabels'));
    }

    public function store(StoreBuildingRequest $request): RedirectResponse
    {
        $service = new StoreBuildingService();
        $service->execute($request->validated());

        return redirect()->route('admin.buildings')
            ->with('success', __('Building created successfully.'));
    }

    public function edit(Building $building): View
    {
        $selfLockOptions = SelfLockType::cases();
        $selfLockLabels = SelfLockType::labels();

        return view('admin.buildings.edit', compact('building', 'selfLockOptions', 'selfLockLabels'));
    }

    public function update(UpdateBuildingRequest $request, Building $building): RedirectResponse
    {
        $service = new UpdateBuildingService();
        $service->execute($building, $request->validated());

        return redirect()->route('admin.buildings')
            ->with('success', __('Building updated successfully.'));
    }

    public function destroy(Building $building): RedirectResponse
    {
        $service = new DeleteBuildingService();
        $service->execute($building);

        return redirect()->route('admin.buildings')
            ->with('success', __('Building deleted successfully.'));
    }

    public function rooms(Building $building): View
    {
        $rooms = $building->rooms()->paginate(100);
        $statusOptions = \App\Enums\RoomStatus::cases();

        return view('admin.buildings.rooms', compact('building', 'rooms', 'statusOptions'));
    }
}
