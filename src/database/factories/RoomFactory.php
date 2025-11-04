<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        $status = fake()->randomElement(RoomStatus::cases());

        return [
            'building_id' => Building::factory(),
            'number' => strtoupper(fake()->bothify('##A')),
            'status' => $status->value,
        ];
    }
}
