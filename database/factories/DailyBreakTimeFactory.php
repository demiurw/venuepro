<?php

namespace Database\Factories;

use App\Models\DailyBreakTime;
use App\Models\Company;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyBreakTimeFactory extends Factory
{
    protected $model = DailyBreakTime::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'room_id' => Room::factory(),
            'day_of_week' => 'all',
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'title' => 'Lunch Break',
            'is_active' => true,
        ];
    }
}
