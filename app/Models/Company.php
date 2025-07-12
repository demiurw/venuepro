<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant;

class Company extends Tenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subscription_level',
        'trial_ends_at',
        'max_users',
        'max_rooms',
        'is_active',
    ];

    /**
     * Dynamically generates the tenant's database name.
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        // Example: returns "venuepro_tenant_innovate"
        $prefix = config('multitenancy.database.prefix');

        return "{$prefix}{$this->slug}";
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
