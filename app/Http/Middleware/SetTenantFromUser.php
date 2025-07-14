<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromUser
{
    use UsesTenantModel;

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
            // Log this issue but don't block the request
            \Log::warning('Authenticated user has no company assigned', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return $next($request);
        }

        // Get the tenant model (Company)
        $tenant = $this->getTenantModel()::find($user->company_id);

        if (!$tenant) {
            \Log::error('User company not found', [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'email' => $user->email
            ]);

            // Redirect to an error page or logout
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your company account could not be found. Please contact support.'
            ]);
        }

        // Set the tenant as current
        $tenant->makeCurrent();

        \Log::info('Tenant set for authenticated user', [
            'user_id' => $user->id,
            'company_id' => $tenant->id,
            'company_name' => $tenant->name
        ]);

        return $next($request);
    }
}
