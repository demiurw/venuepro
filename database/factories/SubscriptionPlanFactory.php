<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Basic', 'Pro', 'Enterprise']),
            'price_monthly' => $this->faker->randomFloat(2, 29, 299),
            'price_yearly' => $this->faker->randomFloat(2, 290, 2990),
            'room_quota' => $this->faker->numberBetween(10, 100),
            'user_quota' => $this->faker->numberBetween(5, 50),
            'features_json' => json_encode(['Feature A', 'Feature B']),
            'is_active' => true,
        ];
    }
}
