<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Visit;
use Illuminate\View\View;

class AreaVisitsController extends Controller
{
    /**
     * Display the visits that belong to the given area.
     */
    public function index(Area $area): View
    {
        $visits = Visit::with(['user', 'area'])
            ->where('area_id', $area->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.areas.visits.index', [
            'area' => $area,
            'visits' => $visits,
        ]);
    }
}
