<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Import the User model

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // Prevent infinite loop by not applying the scope to the User model itself
        // The User model is typically retrieved to establish the tenant context,
        // so it should not be constrained by that same context during its own retrieval.
        if ($model instanceof User) {
            return;
        }

        $companyId = null;

        // Attempt to get company_id from session first
        if (session()->has('company_id')) {
            $companyId = session('company_id');
        } elseif (Auth::check()) {
            // If authenticated, use the user's company_id.
            // This is safe because the User model is now excluded from this scope above.
            $companyId = Auth::user()->company_id;
        }

        // Apply the company_id constraint if a valid companyId is found
        if ($companyId) {
            $builder->where($model->getTable() . '.company_id', $companyId);
        }
    }
}
