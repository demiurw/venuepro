<?php

namespace App\Traits;

use App\Models\Company;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     *
     * This method is automatically called by Eloquent when the trait is used.
     * It applies a global scope to filter queries by the tenant ID
     * and sets up a creating event to add the tenant ID to new models.
     */
    protected static function booted()
    {
        // Apply the global scope for multi-tenancy
        static::addGlobalScope(new TenantScope);

        // Set the company_id automatically when creating a new model
        static::creating(function (Model $model) {
            if (session()->has('company_id')) {
                $model->company_id = session('company_id');
            } elseif (Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    /**
     * Define the relationship to the Company model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
