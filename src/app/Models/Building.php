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
}
