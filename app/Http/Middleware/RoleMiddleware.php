<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  - Pipe-separated list of roles
     * @param  string|null  $guard
     */
    public function handle(Request $request, Closure $next, string $roles, string $guard = null): Response
    {
        $authGuard = Auth::guard($guard);
        
        if ($authGuard->guest()) {
            return redirect('/login');
        }

        $user = $authGuard->user();
        $allowedRoles = explode('|', $roles);

        if (!$user->hasAnyRole($allowedRoles)) {
            abort(403, 'You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}