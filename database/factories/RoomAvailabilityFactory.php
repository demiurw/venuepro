<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Room;
use App\Models\RoomAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RoomAvailabilityFactory extends Factory
{
    protected $model = RoomAvailability::class;

    public function definition(): array
    {
        return [
            'day_of_week' => $this->faker->word(),
            'opens_at' => Carbon::now(),
            'closes_at' => Carbon::now(),
            'is_closed' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'deleted_at' => Carbon::now(),

            'company_id' => Company::factory(),
            'room_id' => Room::factory(),
        ];
    }
}
