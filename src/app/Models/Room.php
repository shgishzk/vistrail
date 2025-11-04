<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'number',
        'status',
    ];

    protected $casts = [
        'status' => RoomStatus::class,
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
