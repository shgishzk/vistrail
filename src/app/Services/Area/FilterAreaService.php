<?php

namespace App\Services\Area;

use App\Models\Area;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class FilterAreaService
{
    /**
     * @return array{0: LengthAwarePaginator, 1: array<string, mixed>, 2: array<int, array{id:int,display:string}>, 3: string|null}
     */
    public function execute(Request $request): array
    {
        $query = Area::query()->with([
            'latestVisit' => function ($relation) {
                $relation->with('user');
            },
        ]);

        if ($number = $request->input('number')) {
            $query->where('number', 'like', '%' . $number . '%');
        }

        if ($name = $request->input('name')) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if ($memo = $request->input('memo')) {
            $query->where('memo', 'like', '%' . $memo . '%');
        }

        if ($latestVisitUserId = $request->input('latest_visit_user')) {
            $query->whereHas('latestVisit', function ($relation) use ($latestVisitUserId) {
                $relation->where('user_id', $latestVisitUserId);
            });
        }

        $fromDate = $this->resolveDate($request->input('visit_from'), true);
        $toDate = $this->resolveDate($request->input('visit_to'), false);

        if ($fromDate || $toDate) {
            $query->whereHas('visits', function ($relation) use ($fromDate, $toDate) {
                $relation->where(function ($visitQuery) use ($fromDate, $toDate) {
                    if ($fromDate) {
                        $visitQuery->where('start_date', '>=', $fromDate->toDateString());
                    }

                    if ($toDate) {
                        $visitQuery->where('start_date', '<=', $toDate->toDateString());
                    }
                })->orWhere(function ($visitQuery) use ($fromDate, $toDate) {
                    $visitQuery->whereNotNull('end_date');

                    if ($fromDate) {
                        $visitQuery->where('end_date', '>=', $fromDate->toDateString());
                    }

                    if ($toDate) {
                        $visitQuery->where('end_date', '<=', $toDate->toDateString());
                    }
                });
            });
        }

        $areas = $query->paginate(15)->appends($request->query());

        $filters = $request->only([
            'number',
            'name',
            'memo',
            'visit_from',
            'visit_to',
            'latest_visit_user',
        ]);

        $suggestUsers = User::orderBy('name')
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

        $suggestSelectedDisplay = null;
        if (!empty($filters['latest_visit_user'])) {
            $selected = collect($suggestUsers)->firstWhere('id', (int) $filters['latest_visit_user']);
            $suggestSelectedDisplay = $selected['display'] ?? null;
        }

        return [$areas, $filters, $suggestUsers, $suggestSelectedDisplay];
    }

    private function resolveDate(?string $value, bool $startOfDay): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
            return $startOfDay ? $date->startOfDay() : $date->endOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
}
