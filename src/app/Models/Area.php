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
        'boundary_geojson',
        'memo',
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
}
