<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'entity_type' => 'App\\Models\\Booking',
            'entity_id' => 1,
            'action' => $this->faker->randomElement(['create', 'update', 'delete']),
            'details' => ['old_status' => 'pending', 'new_status' => 'confirmed'],
            'ip_address' => $this->faker->ipv4,
        ];
    }
}
