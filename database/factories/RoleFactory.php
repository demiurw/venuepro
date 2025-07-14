<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'permissions' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'guard_name' => $this->faker->name(),
            'team_id' => $this->faker->randomNumber(),
            'updated_at' => Carbon::now(),
            'name' => $this->faker->name(),
        ];
    }
}
