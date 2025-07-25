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

    // Using single-database multi-tenancy with scopes, so no separate database needed

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
