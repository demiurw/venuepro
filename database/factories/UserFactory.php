<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role; // <-- Import the Role model

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        // Find or create a default role for the tenant.
        // The 'team_id' is crucial for Spatie's multi-tenancy permission setup.
        $role = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['team_id' => $this->faker->numberBetween(1, 100)] // Using a placeholder team_id
        );

        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'company_id' => Company::factory(),
            'role_id' => $role->id, // <-- Assign the role_id here
            'status' => 'active',
            'user_type' => $this->faker->randomElement(['system_admin', 'hod', 'booking_agent']),
            'auth_method' => 'otp',
            'email_verified_at' => now(),
        ];
    }
}
