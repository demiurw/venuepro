<?php

namespace Database\Factories;

use App\Models\UsageLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsageLogFactory extends Factory
{
    protected $model = UsageLog::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'resource' => 'bookings',
            'usage_value' => $this->faker->numberBetween(1, 10),
            'logged_at' => now(),
        ];
    }
}
