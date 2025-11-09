<?php

namespace Database\Factories;

use App\Models\Area;
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
        $coordinates = [];

        for ($i = 0; $i < 5; $i++) {
            $coordinates[] = fake()->longitude() . ',' . fake()->latitude() . ',0';
        }

        $coordinateString = implode(' ', $coordinates);

        $boundary = <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Placemark>
    <name>Area {$this->sequence}</name>
    <Polygon>
      <outerBoundaryIs>
        <LinearRing>
          <coordinates>
            {$coordinateString}
          </coordinates>
        </LinearRing>
      </outerBoundaryIs>
    </Polygon>
  </Placemark>
</kml>
KML;

        return [
            'number' => $this->sequence++,
            'name' => fake()->city(),
            'boundary_kml' => $boundary,
            'center_lat' => fake()->latitude(),
            'center_lng' => fake()->longitude(),
        ];
    }
}
