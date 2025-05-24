<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pin>
 */
class PinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'area_id' => Area::factory(),
            'visit_id' => fake()->optional(0.7)->randomElement([Visit::factory(), null]),
            'lat' => fake()->latitude(),
            'lng' => fake()->longitude(),
            'status' => fake()->randomElement(['visited', 'not_visited']),
            'memo' => fake()->optional(0.6)->sentence(),
        ];
    }

    /**
     * Indicate that the pin has been visited.
     */
    public function visited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'visited',
        ]);
    }

    /**
     * Indicate that the pin has not been visited.
     */
    public function notVisited(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_visited',
        ]);
    }
}
