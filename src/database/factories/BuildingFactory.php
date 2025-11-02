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
            'lat' => fake()->latitude(),
            'lng' => fake()->longitude(),
            'is_public' => fake()->boolean() ? 1 : 0,
            'memo' => fake()->optional()->sentence(),
        ];
    }
}
