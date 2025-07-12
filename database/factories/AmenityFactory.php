<?php

namespace Database\Factories;

use App\Models\Amenity;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class AmenityFactory extends Factory
{
    protected $model = Amenity::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->randomElement(['Whiteboard', 'Projector', 'Coffee Machine', 'Video Conferencing']),
            'is_active' => true,
        ];
    }
}
