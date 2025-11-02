<?php

namespace App\Models;

use App\Enums\SelfLockType;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
