<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePinRequest;
use App\Http\Requests\Api\UpdatePinRequest;
use App\Models\Area;
use App\Models\Pin;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'visit_id' => ['required', 'integer', 'exists:visits,id'],
        ]);

        $visit = Visit::where('id', $validated['visit_id'])->firstOrFail();

        if ($visit->user_id !== $user->id) {
            abort(403, __('You are not allowed to view pins for this visit.'));
        }

        if ($visit->area_id !== (int) $validated['area_id']) {
            throw ValidationException::withMessages([
                'visit_id' => __('The visit does not belong to the specified area.'),
            ]);
        }

        $pins = Pin::query()
            ->where('user_id', $user->id)
            ->where('area_id', $validated['area_id'])
            ->where('visit_id', $visit->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(static function (Pin $pin) {
                return [
                    'id' => $pin->id,
                    'area_id' => $pin->area_id,
                    'visit_id' => $pin->visit_id,
                    'lat' => (float) $pin->lat,
                    'lng' => (float) $pin->lng,
                    'status' => $pin->status,
                    'memo' => $pin->memo,
                    'created_at' => $pin->created_at,
                ];
            });

        return response()->json(['pins' => $pins]);
    }

    public function store(StorePinRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $area = Area::findOrFail($validated['area_id']);

        $visit = Visit::where('id', $validated['visit_id'])->first();

        if (!$visit || $visit->user_id !== $user->id) {
            abort(403, __('You are not allowed to add pins to this visit.'));
        }

        if ($visit->area_id !== $area->id) {
            throw ValidationException::withMessages([
                'visit_id' => __('The visit does not belong to the specified area.'),
            ]);
        }

        $pin = Pin::create([
            'user_id' => $user->id,
            'area_id' => $area->id,
            'visit_id' => $visit->id,
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'status' => 'visited',
            'memo' => $validated['memo'] ?? null,
        ]);

        return response()->json([
            'pin' => [
                'id' => $pin->id,
                'area_id' => $pin->area_id,
                'visit_id' => $pin->visit_id,
                'lat' => (float) $pin->lat,
                'lng' => (float) $pin->lng,
                'status' => $pin->status,
                'memo' => $pin->memo,
            ],
        ], 201);
    }

    public function update(UpdatePinRequest $request, Pin $pin): JsonResponse
    {
        $user = $request->user();

        if ($pin->user_id !== $user->id) {
            abort(403, __('You are not allowed to update this pin.'));
        }

        $pin->update([
            'memo' => $request->input('memo'),
        ]);

        return response()->json([
            'pin' => [
                'id' => $pin->id,
                'area_id' => $pin->area_id,
                'visit_id' => $pin->visit_id,
                'lat' => (float) $pin->lat,
                'lng' => (float) $pin->lng,
                'status' => $pin->status,
                'memo' => $pin->memo,
            ],
        ]);
    }

    public function destroy(Request $request, Pin $pin): JsonResponse
    {
        $user = $request->user();

        if ($pin->user_id !== $user->id) {
            abort(403, __('You are not allowed to delete this pin.'));
        }

        $pin->delete();

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
