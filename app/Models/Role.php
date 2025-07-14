<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'permissions',
        'is_system_role',
        'guard_name',
        'team_id',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system_role' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * This is typically used to register global scopes or other model event listeners.
     */
    protected static function booted()
    {
        // Add creating event to set the team_id if not already set,
        // and only if teams are enabled in Spatie's config.
        static::creating(function ($role) {
            if (config('permission.teams') && is_null($role->team_id)) {
                // Attempt to set the team_id based on the current tenant, if available
                if (session()->has('company_id')) {
                    $role->team_id = session('company_id');
                } else {
                    // For central roles, team_id can be null or a default for system roles
                    // For now, we'll leave it null if no company_id is in session,
                    // assuming system roles might not be tied to a specific company initially.
                    // This can be further refined based on business logic.
                }
            }
        });
    }

    /**
     * Define the relationship to the Company model (if applicable, for tenant-specific roles).
     * This might not be a direct `belongsTo` for roles as they are often globally defined
     * and linked to companies via `team_id`.
     */
    public function company()
    {
        // Assuming team_id maps to company_id for tenant-specific roles
        return $this->belongsTo(Company::class, 'team_id');
    }

    // Add any other specific methods or relationships for your Role model here
}
