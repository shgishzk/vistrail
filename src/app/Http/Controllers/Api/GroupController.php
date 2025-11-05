<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::query()
            ->where('is_public', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'groups' => $groups,
        ]);
    }

    public function buildings(Request $request)
    {
        $groupId = $request->input('group_id');
        $selectedGroup = null;

        if ($groupId) {
            $selectedGroup = Group::query()
                ->where('is_public', true)
                ->findOrFail($groupId);
        }

        $sixMonthsAgo = Carbon::now()->subMonths(6);

        $buildingsQuery = Building::query()
            ->select(['id', 'name', 'lat', 'lng', 'self_lock_type', 'url', 'memo'])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('is_public', true)
            ->withCount([
                'rooms',
                'rooms as recent_rooms_count' => function ($query) use ($sixMonthsAgo) {
                    $query
                        ->where('status', '!=', RoomStatus::UNVISITED->value)
                        ->where('updated_at', '>=', $sixMonthsAgo);
                },
            ])
            ->withMax('rooms', 'updated_at')
            ->with(['groups' => function ($query) {
                $query->where('is_public', true)->select('groups.id', 'groups.name');
            }])
            ->whereHas('groups', function ($query) {
                $query->where('is_public', true);
            })
            ->orderBy('name');

        if ($selectedGroup) {
            $buildingsQuery->whereHas('groups', function ($query) use ($selectedGroup) {
                $query->where('groups.id', $selectedGroup->id);
            });
        }

        $buildings = $buildingsQuery
            ->get()
            ->map(function (Building $building) use ($selectedGroup) {
                return $this->formatBuildingSummary($building, $selectedGroup);
            })
            ->values();

        return response()->json([
            'buildings' => $buildings,
        ]);
    }

    protected function formatBuildingSummary(Building $building, ?Group $selectedGroup = null): array
    {
        $totalRooms = (int) ($building->rooms_count ?? 0);
        $recentRooms = (int) ($building->recent_rooms_count ?? 0);
        $visitRate = $totalRooms > 0 ? round(($recentRooms / $totalRooms) * 100, 1) : 0.0;

        $lastVisitDate = null;
        if ($building->rooms_max_updated_at) {
            $lastVisitDate = Carbon::parse($building->rooms_max_updated_at)->toDateString();
        }

        $groupName = $selectedGroup?->name;
        if (!$groupName) {
            $groupName = optional($building->groups->first())->name;
        }

        $groupInitial = $groupName ? mb_substr($groupName, 0, 1, 'UTF-8') : null;

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
            'group_name' => $groupName,
            'group_initial' => $groupInitial,
        ];
    }
}
