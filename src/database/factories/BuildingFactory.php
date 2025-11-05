<?php

namespace Database\Factories;

use App\Enums\SelfLockType;
use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        $selfLock = fake()->randomElement(SelfLockType::cases());

        return [
            'url' => fake()->unique()->url(),
            'name' => fake()->company(),
            'self_lock_type' => $selfLock->value,
            // lat and lng around Kyoto, Japan.
            'lat' => fake()->randomFloat(6, 34.950000, 35.100000),
            'lng' => fake()->randomFloat(6, 135.650000, 135.850000),
            'is_public' => 1,
            'memo' => fake()->optional()->sentence(),
        ];
    }
}
