<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAreaRequest;
use App\Http\Requests\Admin\UpdateAreaRequest;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AreasController extends Controller
{
    /**
     * Display a listing of the areas.
     */
    public function index(): View
    {
        $areas = Area::paginate(15);
        
        return view('admin.areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new area.
     */
    public function create(): View
    {
        return view('admin.areas.create');
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

        return view('admin.areas.edit', compact('area', 'googleMapsApiKey'));
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
}
