<?php

namespace App\Services\Visit;

use App\Enums\VisitStatus;
use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VisitFilterService
{
    public function extractFilters(Request $request, ?Area $lockedArea = null): array
    {
        $filters = [
            'user_id' => $request->input('user_id'),
            'area_id' => $request->input('area_id'),
            'status' => $request->input('status'),
            'start_from' => $request->input('start_from'),
            'start_to' => $request->input('start_to'),
        ];

        if ($lockedArea !== null) {
            $filters['area_id'] = $lockedArea->id;
        }

        return $filters;
    }

    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['user_id'] ?? null, fn (Builder $builder, $userId) => $builder->where('user_id', $userId))
            ->when($filters['area_id'] ?? null, fn (Builder $builder, $areaId) => $builder->where('area_id', $areaId))
            ->when($filters['status'] ?? null, fn (Builder $builder, $status) => $builder->where('status', $status))
            ->when($filters['start_from'] ?? null, fn (Builder $builder, $startFrom) => $builder->whereDate('start_date', '>=', $startFrom))
            ->when($filters['start_to'] ?? null, fn (Builder $builder, $startTo) => $builder->whereDate('start_date', '<=', $startTo));
    }

    public function options(): array
    {
        return [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'areas' => Area::orderBy('number')->get(['id', 'number', 'name']),
            'statuses' => VisitStatus::labels(),
        ];
    }
}
