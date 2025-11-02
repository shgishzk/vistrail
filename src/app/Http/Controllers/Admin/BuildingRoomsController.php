<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkUpdateRoomsRequest;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;

class BuildingRoomsController extends Controller
{
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
