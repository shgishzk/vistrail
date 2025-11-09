<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAreaRequest;
use App\Http\Requests\Admin\UpdateAreaRequest;
use App\Models\Area;
use App\Models\Setting;
use App\Services\Area\FilterAreaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreasController extends Controller
{
    /**
     * Display a listing of the areas.
     */
    public function index(Request $request): View
    {
        $filterService = new FilterAreaService();
        [$areas, $filters, $suggestUsers, $suggestSelectedDisplay] = $filterService->execute($request);

        return view('admin.areas.index', compact('areas', 'filters', 'suggestUsers', 'suggestSelectedDisplay'));
    }

    /**
     * Show the form for creating a new area.
     */
    public function create(): View
    {
        $googleMapsApiKey = config('services.google.maps_api_key');
        $defaultPosition = $this->defaultMapPosition();

        return view('admin.areas.create', compact('googleMapsApiKey', 'defaultPosition'));
    }

    /**
     * Store a newly created area in storage.
     */
    public function store(StoreAreaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $storeAreaService = new \App\Services\Area\StoreAreaService();
        $storeAreaService->execute($validated);
        
        return redirect()->route('admin.areas')
            ->with('success', __('Area created successfully.'));
    }
    
    /**
     * Show the form for editing the specified area.
     */
    public function edit(Area $area): View
    {
        $googleMapsApiKey = config('services.google.maps_api_key');
        $defaultPosition = $this->defaultMapPosition();

        return view('admin.areas.edit', compact('area', 'googleMapsApiKey', 'defaultPosition'));
    }

    /**
     * Update the specified area in storage.
     */
    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $validated = $request->validated();
        
        $updateAreaService = new \App\Services\Area\UpdateAreaService();
        $updateAreaService->execute($area, $validated);
        
        return redirect()->route('admin.areas.edit', $area)
            ->with('success', __('Area updated successfully.'));
    }

    /**
     * Remove the specified area from storage.
     */
    public function destroy(Area $area): RedirectResponse
    {
        $deleteAreaService = new \App\Services\Area\DeleteAreaService();
        $deleteAreaService->execute($area);
        
        return redirect()->route('admin.areas')
            ->with('success', __('Area deleted successfully.'));
    }

    public function print(Area $area): View
    {
        $googleMapsApiKey = config('services.google.maps_api_key');
        $defaultPosition = $this->defaultMapPosition();

        return view('admin.areas.print', [
            'area' => $area,
            'googleMapsApiKey' => $googleMapsApiKey,
            'defaultPosition' => $defaultPosition,
        ]);
    }

    private function defaultMapPosition(): array
    {
        $defaults = Setting::defaults();

        return [
            'lat' => Setting::getFloat(
                Setting::KEY_GOOGLE_MAPS_DEFAULT_LAT,
                (float) ($defaults[Setting::KEY_GOOGLE_MAPS_DEFAULT_LAT] ?? 0)
            ),
            'lng' => Setting::getFloat(
                Setting::KEY_GOOGLE_MAPS_DEFAULT_LNG,
                (float) ($defaults[Setting::KEY_GOOGLE_MAPS_DEFAULT_LNG] ?? 0)
            ),
        ];
    }

    private function getLatestVisitorSuggestions(): array
    {
        return \App\Models\User::orderBy('name')
            ->get(['id', 'name', 'name_kana', 'email'])
            ->map(function ($user) {
                $parts = [$user->name];
                if (!empty($user->name_kana)) {
                    $parts[] = $user->name_kana;
                }

                return [
                    'id' => $user->id,
                    'display' => implode(' / ', $parts) . ' (' . $user->email . ')',
                ];
            })
            ->toArray();
    }
}
