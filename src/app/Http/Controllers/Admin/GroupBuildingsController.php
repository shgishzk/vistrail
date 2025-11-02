<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGroupBuildingsRequest;
use App\Models\Building;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupBuildingsController extends Controller
{
    public function edit(Group $group): View
    {
        $allBuildings = Building::orderBy('name')->get();
        $labelFormatter = fn (Building $building): string => sprintf('%s (#%d)', $building->name, $building->id);

        $buildingOptions = $allBuildings->map(function (Building $building) use ($labelFormatter) {
            return [
                'id' => $building->id,
                'label' => $labelFormatter($building),
                'lat' => $building->lat,
                'lng' => $building->lng,
                'url' => $building->url,
                'self_lock_type' => $building->self_lock_type?->value,
            ];
        })->values()->toArray();

        $labelsById = collect($buildingOptions)->pluck('label', 'id')->toArray();

        $assignedRows = $group->buildings()
            ->orderBy('name')
            ->get()
            ->map(function (Building $building) use ($labelsById, $labelFormatter) {
                return [
                    'id' => (string) $building->id,
                    'name' => $labelsById[$building->id] ?? $labelFormatter($building),
                ];
            })
            ->values()
            ->toArray();

        $googleMapsApiKey = config('services.google.maps_api_key');

        return view('admin.groups.buildings', [
            'group' => $group,
            'assignedRows' => $assignedRows,
            'buildingOptions' => $buildingOptions,
            'labelsById' => $labelsById,
            'googleMapsApiKey' => $googleMapsApiKey,
        ]);
    }

    public function update(UpdateGroupBuildingsRequest $request, Group $group): RedirectResponse
    {
        $validated = $request->validated();

        $buildingIds = collect($validated['buildings'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $validIds = Building::query()
            ->whereIn('id', $buildingIds)
            ->pluck('id')
            ->all();

        $group->buildings()->sync($validIds);

        return redirect()
            ->route('admin.groups.buildings.edit', $group)
            ->with('success', __('Group buildings updated successfully.'));
    }
}
