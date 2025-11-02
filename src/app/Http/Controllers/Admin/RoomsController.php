<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomsController extends Controller
{
    public function index(): View
    {
        $rooms = Room::with('building')->orderByDesc('updated_at')->paginate(15);

        return view('admin.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        $buildings = Building::orderBy('name')->get();
        $statuses = RoomStatus::cases();

        return view('admin.rooms.create', compact('buildings', 'statuses'));
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Room::create($request->validated());

        return redirect()->route('admin.rooms')
            ->with('success', __('Room created successfully.'));
    }

    public function edit(Room $room): View
    {
        $buildings = Building::orderBy('name')->get();
        $statuses = RoomStatus::cases();

        return view('admin.rooms.edit', compact('room', 'buildings', 'statuses'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $room->update($request->validated());

        return redirect()->route('admin.rooms')
            ->with('success', __('Room updated successfully.'));
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('admin.rooms')
            ->with('success', __('Room deleted successfully.'));
    }
}
