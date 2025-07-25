<?php

namespace App\Http\Controllers;

use App\Http\Middleware\DashboardRedirectMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the generic dashboard route.
     * This should redirect users to their role-specific dashboard.
     */
    public function index(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get the user's specific dashboard route
        $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);
        
        return redirect($dashboardRoute);
    }
}