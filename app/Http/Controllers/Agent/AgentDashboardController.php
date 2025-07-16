<?php

// Booking Agent Dashboard Controller
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgentDashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Agent/Dashboard', [
            'user' => $user,
            'stats' => [
                'todays_bookings' => \App\Models\Booking::where('created_by', $user->id)
                    ->whereDate('start_time', today())
                    ->count(),
                'total_bookings' => \App\Models\Booking::where('created_by', $user->id)->count(),
                'active_clients' => \App\Models\User::where('user_type', 'external')
                    ->where('company_id', $user->company_id)
                    ->where('status', 'active')
                    ->count(),
                'revenue_generated' => 0, // Implement revenue tracking
            ],
            'upcoming_bookings' => [], // Today's upcoming bookings
            'recent_clients' => [], // Recently added clients
        ]);
    }

    public function bookings(): Response
    {
        return Inertia::render('Agent/Bookings');
    }

    public function calendar(): Response
    {
        return Inertia::render('Agent/Calendar');
    }

    public function clients(): Response
    {
        return Inertia::render('Agent/Clients');
    }
}
