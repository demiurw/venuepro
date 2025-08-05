<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Enums\UserStatus;
use App\Http\Middleware\DashboardRedirectMiddleware;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserStatusMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that users have the appropriate status
     * to access certain parts of the application.
     */
    public function handle(Request $request, Closure $next, string $requiredStatus = 'active'): Response
    {
        // Skip check for guests
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $userStatus = $user->status;

        // Convert string to enum for comparison
        $requiredStatusEnum = UserStatus::from($requiredStatus);

        Log::debug('User status middleware check', [
            'user_id' => $user->id,
            'current_status' => $userStatus->value,
            'required_status' => $requiredStatusEnum->value,
            'route' => $request->route()?->getName(),
            'url' => $request->url()
        ]);

        // Handle different status requirements
        switch ($requiredStatusEnum) {
            case UserStatus::ACTIVE:
                return $this->handleActiveRequirement($request, $next, $user);
                
            case UserStatus::PENDING:
                return $this->handlePendingRequirement($request, $next, $user);
                
            case UserStatus::INACTIVE:
                return $this->handleInactiveRequirement($request, $next, $user);
                
            default:
                return $next($request);
        }
    }

    /**
     * Handle routes that require active status
     */
    protected function handleActiveRequirement(Request $request, Closure $next, $user): Response
    {
        if (!$user->isActive()) {
            Log::info('Access denied - user not active', [
                'user_id' => $user->id,
                'status' => $user->status->value,
                'attempted_url' => $request->url()
            ]);

            if ($user->isPending()) {
                // Redirect pending users to verification notice
                return redirect()->route('verification.notice')->with([
                    'message' => 'Please verify your account to continue.',
                    'status' => 'warning'
                ]);
            }

            if ($user->isInactive()) {
                // Log out inactive users and redirect to login
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been deactivated. Please contact support.'
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Handle routes that require pending status (e.g., verification flows)
     */
    protected function handlePendingRequirement(Request $request, Closure $next, $user): Response
    {
        if (!$user->isPending()) {
            Log::info('Access denied - user not pending', [
                'user_id' => $user->id,
                'status' => $user->status->value,
                'attempted_url' => $request->url()
            ]);

            if ($user->isActive()) {
                // Redirect active users to their dashboard
                $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);
                return redirect($dashboardRoute);
            }

            if ($user->isInactive()) {
                // Log out inactive users
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been deactivated.'
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Handle routes that require inactive status (admin tools, etc.)
     */
    protected function handleInactiveRequirement(Request $request, Closure $next, $user): Response
    {
        if (!$user->isInactive()) {
            Log::info('Access denied - user not inactive', [
                'user_id' => $user->id,
                'status' => $user->status->value,
                'attempted_url' => $request->url()
            ]);

            // Redirect to appropriate dashboard based on status
            if ($user->isActive()) {
                $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);
                return redirect($dashboardRoute);
            }

            if ($user->isPending()) {
                return redirect()->route('verification.notice');
            }
        }

        return $next($request);
    }
}