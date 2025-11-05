<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoomStatus;
use App\Enums\SelfLockType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBuildingRequest;
use App\Http\Requests\Admin\UpdateBuildingRequest;
use App\Models\Building;
use App\Services\Building\StoreBuildingService;
use App\Services\Building\UpdateBuildingService;
use App\Services\Building\DeleteBuildingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class BuildingsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['name', 'self_lock_type', 'is_public']);

        $query = Building::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (array_key_exists('self_lock_type', $filters) && $filters['self_lock_type'] !== null && $filters['self_lock_type'] !== '') {
            $validSelfLockTypes = array_map(fn (SelfLockType $type) => $type->value, SelfLockType::cases());
            if (in_array($filters['self_lock_type'], $validSelfLockTypes, true)) {
                $query->where('self_lock_type', $filters['self_lock_type']);
            }
        }

        if (array_key_exists('is_public', $filters) && $filters['is_public'] !== null && $filters['is_public'] !== '') {
            if (in_array((string) $filters['is_public'], ['0', '1'], true)) {
                $query->where('is_public', $filters['is_public'] === '1');
            }
        }

        $buildings = $query->orderBy('name')->paginate(15)->appends($request->query());
        $selfLockOptions = SelfLockType::cases();
        $selfLockLabels = SelfLockType::labels();

        return view('admin.buildings.index', compact('buildings', 'filters', 'selfLockOptions', 'selfLockLabels'));
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
        $roomsQuery = $building->rooms()->orderBy('number');
        $totalRooms = (clone $roomsQuery)->count();
        $rooms = $roomsQuery->paginate(100);
        $statusOptions = RoomStatus::cases();

        $oneYearAgo = Carbon::now()->subYear();
        $roomsCollection = (clone $roomsQuery)->get();
        $recentRoomsCount = $building->countVisitedRoomsSince($oneYearAgo, $roomsCollection);
        $lastVisitDateRaw = $roomsCollection->max('updated_at');

        $summary = [
            'last_visit_date' => $lastVisitDateRaw ? Carbon::parse($lastVisitDateRaw)->format('Y-m-d') : null,
            'visit_rate_year' => $building->visitRateSince($oneYearAgo, $roomsCollection),
            'total_rooms' => $totalRooms,
            'recent_rooms_count' => $recentRoomsCount,
        ];

        return view('admin.buildings.rooms', compact('building', 'rooms', 'statusOptions', 'totalRooms', 'summary'));
    }
}
