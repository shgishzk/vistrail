<?php

namespace App\Models;

use App\Enums\RoomStatus;
use App\Enums\SelfLockType;
use App\Models\Group;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'name',
        'self_lock_type',
        'lat',
        'lng',
        'is_public',
        'memo',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'is_public' => 'boolean',
        'self_lock_type' => SelfLockType::class,
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)->withTimestamps();
    }

    public function badgeClass(): string
    {
        return $this->self_lock_type === \App\Enums\SelfLockType::HAS_LOCK ? 'bg-warning' : 'bg-success';
    }

    public function selfLockLabel(): string
    {
        return \App\Enums\SelfLockType::labels()[$this->self_lock_type->value] ?? $this->self_lock_type->value;
    }

    public function publicClass(): string
    {
        return $this->is_public ? 'bg-info' : 'bg-secondary';
    }

    public function publicLabel(): string
    {
        return $this->is_public ? __('Public') : __('Private');
    }

    /**
     * Count rooms that were visited (status !== UNVISITED) within the provided period.
     */
    public function countVisitedRoomsSince(Carbon $since, ?Collection $rooms = null): int
    {
        $roomsCollection = $this->resolveRoomsCollection($rooms);

        return $roomsCollection->filter(function ($room) use ($since) {
            $status = $room->status instanceof RoomStatus ? $room->status->value : (string) $room->status;

            return $status !== RoomStatus::UNVISITED->value
                && $room->updated_at
                && Carbon::parse($room->updated_at)->greaterThanOrEqualTo($since);
        })->count();
    }

    /**
     * Calculate visit rate within the period (visited rooms / total rooms * 100).
     */
    public function visitRateSince(Carbon $since, ?Collection $rooms = null): float
    {
        $roomsCollection = $this->resolveRoomsCollection($rooms);
        $totalRooms = $roomsCollection->count();

        if ($totalRooms === 0) {
            return 0.0;
        }

        $visitedRooms = $this->countVisitedRoomsSince($since, $roomsCollection);

        return round(($visitedRooms / $totalRooms) * 100, 1);
    }

    /**
     * Resolve the rooms collection, using a provided collection, the already-loaded relation,
     * or fetching from the database as needed.
     */
    protected function resolveRoomsCollection(?Collection $rooms = null): Collection
    {
        if ($rooms instanceof Collection) {
            return $rooms;
        }

        if ($this->relationLoaded('rooms')) {
            return $this->getRelation('rooms');
        }

        return $this->rooms()->get();
    }

    public static function selectableStatuses(): array
    {
        $labels = RoomStatus::labels();

        unset($labels[RoomStatus::DO_NOT_CALL->value], $labels[RoomStatus::WITNESS->value]);

        return $labels;
    }

    public function roomAlert(Room $room, ?Carbon $reference = null): ?array
    {
        $reference = $reference ?? Carbon::now();
        $updatedAt = $room->updated_at ? Carbon::parse($room->updated_at) : null;
        if (!$updatedAt) {
            return null;
        }

        $statusValue = $room->status instanceof RoomStatus
            ? $room->status->value
            : (string) $room->status;

        $visitedWithinDays = (int) config('buildings.alerts.visited_within_days', 90);
        if ($visitedWithinDays > 0
            && in_array($statusValue, [RoomStatus::AT_HOME->value, RoomStatus::POSTED->value], true)
            && $updatedAt->greaterThanOrEqualTo($reference->copy()->subDays($visitedWithinDays))
        ) {
            return [
                'type' => 'danger',
                'icon' => 'alert-circle',
                'message' => __('Visited within :days days. Please follow up accordingly.', ['days' => $visitedWithinDays]),
            ];
        }

        $notAtHomeWithinDays = (int) config('buildings.alerts.not_at_home_within_days', 7);
        if ($notAtHomeWithinDays > 0
            && $statusValue === RoomStatus::NOT_AT_HOME->value
            && $updatedAt->greaterThanOrEqualTo($reference->copy()->subDays($notAtHomeWithinDays))
        ) {
            return [
                'type' => 'warning',
                'icon' => 'alert-triangle',
                'message' => __('Marked as not at home within :days days.', ['days' => $notAtHomeWithinDays]),
            ];
        }

        return null;
    }
}
