<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyName = $this->faker->company;
        return [
            'name' => $companyName,
            'slug' => Str::slug($companyName),
            'subscription_level' => $this->faker->randomElement(['trial', 'basic', 'professional', 'enterprise']),
            'trial_ends_at' => now()->addDays(14),
            'max_users' => $this->faker->randomElement([5, 10, 50]),
            'max_rooms' => $this->faker->randomElement([10, 20, 100]),
            'is_active' => true,
        ];
    }
}
