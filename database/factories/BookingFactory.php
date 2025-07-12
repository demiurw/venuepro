<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Company;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('+1 day', '+1 month');
        return [
            'company_id' => Company::factory(),
            'room_id' => Room::factory(),
            'created_by' => User::factory(),
            'title' => $this->faker->sentence(3),
            'date' => $date->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'completed']),
        ];
    }
}
