<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'boundary_geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [fake()->longitude(), fake()->latitude()],
                        [fake()->longitude(), fake()->latitude()],
                        [fake()->longitude(), fake()->latitude()],
                        [fake()->longitude(), fake()->latitude()],
                        [fake()->longitude(), fake()->latitude()],
                    ]
                ]
            ]),
        ];
    }
}
