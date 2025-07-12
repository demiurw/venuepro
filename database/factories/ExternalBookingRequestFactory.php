<?php

namespace Database\Factories;

use App\Models\ExternalBookingRequest;
use App\Models\Company;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ExternalBookingRequestFactory extends Factory
{
    protected $model = ExternalBookingRequest::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'room_id' => Room::factory(),
            'requester_name' => $this->faker->name,
            'requester_email' => $this->faker->safeEmail,
            'date' => $this->faker->dateTimeBetween('+2 days', '+1 month')->format('Y-m-d'),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'title' => 'External Meeting Request',
            'status' => 'pending',
            'token' => Str::random(40),
        ];
    }
}
