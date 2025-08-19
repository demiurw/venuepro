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

            $companyId = $user->company_id;

            // Check if company has completed onboarding requirements
            $hasBuilding = Building::where('company_id', $companyId)->exists();
            $hasRoom = Room::where('company_id', $companyId)->exists();
            $hasGroup = Group::where('company_id', $companyId)
                ->where('is_active', true)
                ->exists();
            $hasOtherUser = User::where('company_id', $companyId)
                ->where('id', '!=', $user->id)
                ->whereIn('user_type', ['booking_agent', 'hod', 'invitee'])
                ->exists();

            // Redirect to onboarding if any requirement is missing
            if (!$hasBuilding || !$hasRoom || !$hasGroup || !$hasOtherUser) {
                return redirect()->route('onboarding.buildings');
            }
        }

        return $next($request);
    }
}
