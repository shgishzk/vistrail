<?php

namespace Database\Factories;

use App\Models\Area;
use App\Support\GeoJsonCenterCalculator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    protected $sequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $polygon = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [fake()->longitude(), fake()->latitude()],
                    [fake()->longitude(), fake()->latitude()],
                    [fake()->longitude(), fake()->latitude()],
                    [fake()->longitude(), fake()->latitude()],
                    [fake()->longitude(), fake()->latitude()],
                ],
            ],
        ];

        $boundary = json_encode($polygon);
        $center = GeoJsonCenterCalculator::calculate($boundary);

        return [
            'number' => $this->sequence++,
            'name' => fake()->city(),
            'boundary_geojson' => $boundary,
            'center_lat' => $center['lat'] ?? null,
            'center_lng' => $center['lng'] ?? null,
        ];
    }
}
