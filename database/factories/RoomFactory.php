<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Building;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'building_id' => Building::factory(),
            'name' => 'Room ' . $this->faker->buildingNumber,
            'room_type' => $this->faker->randomElement(['meeting_room', 'conference_room', 'board_room']),
            'capacity' => $this->faker->numberBetween(4, 20),
            'floor' => $this->faker->numberBetween(1, 10),
            'hourly_rate' => $this->faker->randomElement([25, 50, 75, 100]),
            'is_active' => true,
        ];
    }
}
