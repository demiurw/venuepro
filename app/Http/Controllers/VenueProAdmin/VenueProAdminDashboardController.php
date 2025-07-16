<?php


// VenuePro Admin Dashboard Controller
namespace App\Http\Controllers\VenueProAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VenueProAdminDashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('VenueProAdmin/Dashboard', [
            'user' => auth()->user(),
            'stats' => [
                'total_companies' => \App\Models\Company::count(),
                'active_users' => \App\Models\User::where('status', 'active')->count(),
                'total_bookings' => \App\Models\Booking::count(),
                'system_revenue' => 0, // Implement system-wide revenue tracking
            ],
            'company_analytics' => [], // System-wide analytics
            'recent_signups' => [], // Recent company signups
        ]);
    }

    public function companies(): Response
    {
        return Inertia::render('VenueProAdmin/Companies');
    }

    public function systemSettings(): Response
    {
        return Inertia::render('VenueProAdmin/SystemSettings');
    }

    public function analytics(): Response
    {
        return Inertia::render('VenueProAdmin/Analytics');
    }
}
