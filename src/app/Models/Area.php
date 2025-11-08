<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'boundary_kml',
        'center_lat',
        'center_lng',
        'memo',
    ];

    protected $casts = [
        'center_lat' => 'float',
        'center_lng' => 'float',
    ];

    /**
     * Get the pins for this area.
     */
    public function pins()
    {
        return $this->hasMany(Pin::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function latestVisit()
    {
        return $this->hasOne(Visit::class)->latestOfMany('start_date');
    }
}
