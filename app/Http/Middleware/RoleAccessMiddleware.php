<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleAccessMiddleware
{
    /**
     * Route patterns and their required user types
     */
    private const ROUTE_PERMISSIONS = [
        'venuepro-admin/*' => ['venuepro_admin'],
        'admin/*' => ['system_admin'],
        'hod/*' => ['hod'],
        'agent/*' => ['booking_agent'],
        'user/*' => ['invitee', 'external'], // Multiple types can access user routes
        'external/*' => ['external'],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user account is active
        if (!$user->isActive()) {
            if ($user->isPending()) {
                return redirect()->route('verification.notice')
                    ->with('error', 'Your account requires verification.');
            }
            return redirect()->route('account.pending')
                ->with('error', 'Your account is not yet active. Please contact your administrator.');
        }

        // If specific roles are passed as parameters, check against those
        if (!empty($allowedRoles)) {
            if (!in_array($user->user_type, $allowedRoles)) {
                return $this->handleUnauthorizedAccess($user);
            }
            return $next($request);
        }

        // Otherwise, check against route patterns
        $currentPath = $request->path();
        $userType = $user->user_type;

        foreach (self::ROUTE_PERMISSIONS as $pattern => $allowedTypes) {
            if ($request->is($pattern)) {
                if (!in_array($userType, $allowedTypes)) {
                    return $this->handleUnauthorizedAccess($user);
                }
                break;
            }
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access attempts
     */
    private function handleUnauthorizedAccess($user): Response
    {
        // Redirect to user's appropriate dashboard based on their user type
        $dashboardRoutes = [
            'venuepro_admin' => '/venuepro-admin/dashboard',
            'system_admin' => '/admin/dashboard',
            'hod' => '/hod/dashboard',
            'booking_agent' => '/agent/dashboard',
            'invitee' => '/user/dashboard',
            'external' => '/external/dashboard',
        ];

        $dashboardRoute = $dashboardRoutes[$user->user_type] ?? '/user/dashboard';

        return redirect($dashboardRoute)
            ->with('error', 'You do not have permission to access that area.');
    }

    /**
     * Check if user has access to specific route pattern
     */
    public static function userCanAccess($user, string $routePattern): bool
    {
        if (!$user->isActive()) {
            return false;
        }

        $allowedTypes = self::ROUTE_PERMISSIONS[$routePattern] ?? [];
        return in_array($user->user_type, $allowedTypes);
    }

    /**
     * Get all accessible route patterns for a user
     */
    public static function getAccessibleRoutes($user): array
    {
        if (!$user->isActive()) {
            return [];
        }

        $accessibleRoutes = [];
        foreach (self::ROUTE_PERMISSIONS as $pattern => $allowedTypes) {
            if (in_array($user->user_type, $allowedTypes)) {
                $accessibleRoutes[] = $pattern;
            }
        }

        return $accessibleRoutes;
    }
}
