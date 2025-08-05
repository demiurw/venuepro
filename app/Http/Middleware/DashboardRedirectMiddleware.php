<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserStatus;
use Symfony\Component\HttpFoundation\Response;

class DashboardRedirectMiddleware
{
    /**
     * Dashboard routes mapping for different user types
     */
    private const DASHBOARD_ROUTES = [
        'venuepro_admin' => '/venuepro-admin/dashboard',
        'system_admin' => '/admin/dashboard',
        'hod' => '/hod/dashboard',
        'booking_agent' => '/agent/dashboard',
        'invitee' => '/user/dashboard',
        'external' => '/external/dashboard',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only redirect authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if user is accessing the generic dashboard route
        if ($request->is('dashboard') || $request->is('dashboard/*')) {
            $redirectRoute = $this->getDashboardRoute($user);

            if ($redirectRoute && $request->path() !== ltrim($redirectRoute, '/')) {
                return redirect($redirectRoute);
            }
        }

        return $next($request);
    }

    /**
     * Get the appropriate dashboard route for the user
     */
    private function getDashboardRoute($user): ?string
    {
        // Check user status first
        if (!$user->isActive()) {
            if ($user->isPending()) {
                return '/verification/notice';
            }
            return '/account/inactive';
        }

        // Get user type and return corresponding dashboard
        $userType = $user->user_type;

        return self::DASHBOARD_ROUTES[$userType] ?? '/user/dashboard';
    }

    /**
     * Check if user has access to the requested dashboard
     */
    public static function hasAccessToDashboard($user, string $dashboardType): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        $allowedRoute = self::DASHBOARD_ROUTES[$user->user_type] ?? null;
        $requestedRoute = self::DASHBOARD_ROUTES[$dashboardType] ?? null;

        return $allowedRoute === $requestedRoute;
    }

    /**
     * Get user's default dashboard route
     */
    public static function getUserDashboardRoute($user): string
    {
        if (!$user->isActive()) {
            if ($user->isPending()) {
                return '/verification/notice';
            }
            return '/account/inactive';
        }

        return self::DASHBOARD_ROUTES[$user->user_type] ?? '/user/dashboard';
    }
}
