<?php

namespace App\Models;

use App\Support\GeoJsonCenterCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'boundary_geojson',
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

    /**
     * @deprecated Use App\Support\GeoJsonCenterCalculator::calculate instead.
     */
    public static function calculateCenterFromGeoJson(?string $geoJson): ?array
    {
        return GeoJsonCenterCalculator::calculate($geoJson);
    }
}
