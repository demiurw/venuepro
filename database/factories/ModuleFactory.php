<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'price_monthly' => $this->faker->randomFloat(2, 10, 50),
            'price_yearly' => $this->faker->randomFloat(2, 100, 500),
            'is_active' => true,
        ];
    }
}
