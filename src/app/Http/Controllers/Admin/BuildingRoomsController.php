<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkStoreRoomsRequest;
use App\Http\Requests\Admin\BulkUpdateRoomsRequest;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BuildingRoomsController extends Controller
{
    private const MAX_BULK_ROWS = 50;

    public function create(Building $building): View
    {
        $statusOptions = RoomStatus::cases();

        return view('admin.buildings.rooms.create', [
            'building' => $building,
            'statusOptions' => $statusOptions,
            'maxRows' => self::MAX_BULK_ROWS,
        ]);
    }

    public function store(BulkStoreRoomsRequest $request, Building $building): RedirectResponse
    {
        $validated = $request->validated();
        $roomsInput = $validated['rooms'] ?? [];

        $roomsToCreate = collect($roomsInput)
            ->map(fn (array $data) => [
                'number' => trim($data['number'] ?? ''),
                'status' => $data['status'] ?? RoomStatus::UNVISITED->value,
            ])
            ->filter(fn (array $data) => $data['number'] !== '')
            ->values();

        if ($roomsToCreate->isEmpty()) {
            return back()
                ->withErrors(['rooms' => __('Please enter at least one room.')])
                ->withInput();
        }

        foreach ($roomsToCreate as $roomData) {
            $building->rooms()->create($roomData);
        }

        return redirect()
            ->route('admin.buildings.rooms', $building)
            ->with('success', __('Rooms created successfully.'));
    }

    public function update(BulkUpdateRoomsRequest $request, Building $building): RedirectResponse
    {
        $payload = $request->validated();
        $roomData = $payload['rooms'] ?? [];
        $roomIds = array_keys($roomData);

        $rooms = $building->rooms()->whereIn('id', $roomIds)->get()->keyBy('id');

        foreach ($roomData as $roomId => $data) {
            if (! isset($rooms[$roomId])) {
                abort(404);
            }

            /** @var Room $room */
            $room = $rooms[$roomId];
            $room->update([
                'number' => $data['number'],
                'status' => $data['status'],
            ]);
        }

        return redirect()
            ->route('admin.buildings.rooms', $building)
            ->with('success', __('Rooms updated successfully.'));
    }
}
