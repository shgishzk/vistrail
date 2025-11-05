<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BuildingController extends Controller
{
    /**
     * Return buildings within a square window around provided lat/lng along with visit stats.
     */
    public function index(Request $request)
    {
        $defaultPosition = config('services.google.default_position');

        $lat = (float) $request->input('lat', $defaultPosition['lat'] ?? 0);
        $lng = (float) $request->input('lng', $defaultPosition['lng'] ?? 0);

        $halfSideKm = 1.5;
        $latRad = deg2rad($lat);
        $kmPerDegreeLat = 110.574;
        $degLat = $halfSideKm / $kmPerDegreeLat;
        $cosLat = cos($latRad);
        $kmPerDegreeLng = 111.320 * max(abs($cosLat), 1e-6);
        $degLng = $halfSideKm / $kmPerDegreeLng;

        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $buildings = Building::query()
            ->select(['id', 'name', 'lat', 'lng', 'self_lock_type', 'url', 'memo'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('is_public', true)
            ->whereBetween('lat', [$lat - $degLat, $lat + $degLat])
            ->whereBetween('lng', [$lng - $degLng, $lng + $degLng])
            ->withCount([
                'rooms',
            'rooms as recent_rooms_count' => function ($query) use ($sixMonthsAgo) {
                $query
                    ->where('status', '!=', RoomStatus::UNVISITED->value)
                    ->where('updated_at', '>=', $sixMonthsAgo);
            },
        ])
            ->withMax('rooms', 'updated_at')
            ->orderBy('name')
            ->get()
            ->map(function (Building $building) {
                $totalRooms = (int) $building->rooms_count;
                $recentRooms = (int) ($building->recent_rooms_count ?? 0);
                $visitRate = $totalRooms > 0 ? round(($recentRooms / $totalRooms) * 100, 1) : 0.0;

                $lastVisitDate = null;
                if ($building->rooms_max_updated_at) {
                    $lastVisitDate = Carbon::parse($building->rooms_max_updated_at)->toDateString();
                }

                return [
                    'id' => $building->id,
                    'name' => $building->name,
                    'lat' => (float) $building->lat,
                    'lng' => (float) $building->lng,
                    'self_lock_type' => $building->self_lock_type?->value,
                    'url' => $building->url,
                    'memo' => $building->memo,
                    'last_visit_date' => $lastVisitDate,
                    'visit_rate' => $visitRate,
                    'detail_url' => url("/buildings/{$building->id}"),
                ];
            })
            ->values();

        return response()->json([
            'buildings' => $buildings,
        ]);
    }

    /**
     * Return detail information for a given building.
     */
    public function show(Building $building)
    {
        abort_unless($building->is_public, 404);

        $oneYearAgo = Carbon::now()->subYear();

        $building->load(['rooms' => function ($query) {
            $query->select('id', 'building_id', 'number', 'status', 'updated_at')
                ->orderBy('number');
        }]);

        $roomsCollection = $building->rooms;
        $totalRooms = $roomsCollection->count();
        $recentRooms = $building->countVisitedRoomsSince($oneYearAgo, $roomsCollection);

        $lastVisitDate = $roomsCollection
            ->filter(fn ($room) => $room->updated_at)
            ->max('updated_at');
        $lastVisitDate = $lastVisitDate ? Carbon::parse($lastVisitDate)->toDateString() : null;

        $visitRate = $building->visitRateSince($oneYearAgo, $roomsCollection);

        $statusLabels = RoomStatus::labels();
        $selectableStatusLabels = Building::selectableStatuses();

        $now = Carbon::now();

        return response()->json([
            'building' => [
                'id' => $building->id,
                'name' => $building->name,
                'memo' => $building->memo,
                'url' => $building->url,
                'lat' => (float) $building->lat,
                'lng' => (float) $building->lng,
                'last_visit_date' => $lastVisitDate,
                'visit_rate_year' => $visitRate,
                'total_rooms' => $totalRooms,
                'recent_rooms_count' => $recentRooms,
                'rooms' => $building->rooms->map(function ($room) use ($statusLabels, $selectableStatusLabels, $building, $now) {
                    $statusValue = $room->status instanceof RoomStatus
                        ? $room->status->value
                        : (string) $room->status;
                    $alert = $building->roomAlert($room, $now);

                    return [
                        'id' => $room->id,
                        'number' => $room->number,
                        'status' => $statusValue,
                        'status_label' => $statusLabels[$statusValue] ?? $statusValue,
                        'selectable' => isset($selectableStatusLabels[$statusValue]),
                        'updated_at' => optional($room->updated_at)->toDateTimeString(),
                        'alert' => $alert,
                    ];
                }),
            ],
            'statuses' => $statusLabels,
            'selectable_statuses' => $selectableStatusLabels,
        ]);
    }

    public function updateRoomStatus(Request $request, Building $building, Room $room)
    {
        abort_unless($building->is_public && $room->building_id === $building->id, 404);

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Building::selectableStatuses())),
        ]);

        $room->update([
            'status' => $validated['status'],
        ]);

        $room->refresh();

        $statusValue = $room->status instanceof RoomStatus
            ? $room->status->value
            : (string) $room->status;

        $alert = $building->roomAlert($room);

        return response()->json([
            'room' => [
                'id' => $room->id,
                'status' => $statusValue,
                'status_label' => RoomStatus::labels()[$statusValue] ?? $statusValue,
                'updated_at' => optional($room->updated_at)->toDateTimeString(),
                'alert' => $alert,
            ],
        ]);
    }
}
