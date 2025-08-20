<?php

namespace App\Http\Middleware;

use App\Models\Building;
use App\Models\Group;
use App\Models\Room;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OnboardingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = Auth::user();

        // Only apply onboarding logic to System Admins
        if ($user && $user->user_type === 'system_admin') {
            // Allow access to onboarding routes
            if ($request->routeIs('onboarding.*')) {
                return $next($request);
            }

            // Check if user has completed all onboarding steps (step 5 is the last step)
            if ($user->onboarding_step_completed < 5) {
                // Determine which step to redirect to based on progress
                $step = $user->onboarding_step_completed + 1;
                $stepRoutes = [
                    1 => 'onboarding.buildings',
                    2 => 'onboarding.rooms',
                    3 => 'onboarding.groups',
                    4 => 'onboarding.users',
                    5 => 'onboarding.labels',
                ];

                $routeName = $stepRoutes[$step] ?? 'onboarding.buildings';
                return redirect()->route($routeName);
            }
        }

        return $next($request);
    }
}
