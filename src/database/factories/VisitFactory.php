<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visit>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');
        $endDate = fake()->optional(0.7)->dateTimeBetween($startDate, '+1 month');

        return [
            'user_id' => User::factory(),
            'area_id' => Area::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'memo' => fake()->optional(0.8)->paragraph(),
        ];
    }
}
