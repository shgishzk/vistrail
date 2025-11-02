<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            // Assign names like "Group A", "Group B"
            'name' => __('Group') . ' ' . chr(65 + $this->faker->unique()->numberBetween(0, 25)),
            'is_public' => $this->faker->boolean(80),
        ];
    }
}
