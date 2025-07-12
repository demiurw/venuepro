<?php

namespace Database\Factories;

use App\Models\UsageLimit;
use App\Models\Company;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsageLimitFactory extends Factory
{
    protected $model = UsageLimit::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'group_id' => Group::factory(),
            'resource' => $this->faker->randomElement(['bookings', 'data_storage_mb']),
            'limit_value' => $this->faker->numberBetween(100, 1000),
        ];
    }
}
