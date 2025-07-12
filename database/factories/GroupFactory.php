<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->jobTitle . ' Team',
            'description' => $this->faker->sentence,
            'created_by' => User::factory(),
            'is_active' => true,
        ];
    }
}
