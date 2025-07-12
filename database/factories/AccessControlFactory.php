<?php

namespace Database\Factories;

use App\Models\AccessControl;
use App\Models\Company;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccessControlFactory extends Factory
{
    protected $model = AccessControl::class;

    public function definition(): array
    {
        return [
            'entity_type' => $this->faker->word(),
            'entity_id' => $this->faker->randomNumber(),
            'access_level' => $this->faker->word(),
            'created_at' => Carbon::now(),

            'company_id' => Company::factory(),
            'group_id' => Group::factory(),
        ];
    }
}
