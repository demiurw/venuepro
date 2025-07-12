<?php

namespace Database\Factories;

use App\Models\BlockedDate;
use App\Models\Company;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlockedDateFactory extends Factory
{
    protected $model = BlockedDate::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'room_id' => Room::factory(),
            'date' => $this->faker->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'title' => 'Maintenance',
            'reason' => $this->faker->sentence,
        ];
    }
}
