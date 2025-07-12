<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->company . ' Headquarters',
            'address_line1' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'timezone' => $this->faker->timezone,
            'is_active' => true,
        ];
    }
}
