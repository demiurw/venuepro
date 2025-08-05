<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromUser
{

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only proceed if user is authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Only proceed if user has a company
        if (!$user->company_id) {
            // For users without company (like VenuePro admins), we still allow access
            // but log this for monitoring
            Log::info('Authenticated user has no company assigned - allowing access', [
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'email' => $user->email
            ]);
            return $next($request);
        }

        // Check if a tenant is already set (to avoid redundant operations)
        try {
            $currentTenant = app()->bound('currentTenant') ? app('currentTenant') : null;
            if ($currentTenant && $currentTenant->id === $user->company_id) {
                return $next($request);
            }
        } catch (\Exception $e) {
            // If checking current tenant fails, continue to set it
        }

        // Get the tenant model (Company)
        $tenant = Company::find($user->company_id);

        if (!$tenant) {
            Log::error('User company not found', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'email' => $user->email
            ]);

            // Don't logout immediately - redirect to account pending page
            return redirect()->route('account.pending')->withErrors([
                'message' => 'Your company account could not be found. Please contact support.'
            ]);
        }

        // Set the tenant as current
        try {
            $tenant->makeCurrent();

            Log::debug('Tenant set for authenticated user', [
                'user_id' => $user->id,
                'company_id' => $tenant->id,
                'company_name' => $tenant->name ?? 'Unknown',
                'user_type' => $user->user_type
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to set tenant as current', [
                'user_id' => $user->id,
                'company_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Continue with the request even if tenant setting fails
            // This prevents the entire request from breaking
        }

        return $next($request);
    }
}
