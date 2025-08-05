<?php

// HOD Dashboard Controller
namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HodDashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Hod/Dashboard', [
            'user' => $user,
            'stats' => [
                'team_members' => \App\Models\User::where('group_id', $user->group_id)->count(),
                'department_bookings' => \App\Models\Booking::where('created_by', $user->id)
                    ->orWhereHas('creator', function($q) use ($user) {
                        $q->where('group_id', $user->group_id);
                    })
                    ->orWhereHas('bookedForUser', function($q) use ($user) {
                        $q->where('group_id', $user->group_id);
                    })
                    ->where('start_time', '>=', now())
                    ->count(),
                'pending_approvals' => 0, // Implement approval system
                'budget_utilization' => 0, // Implement budget tracking
            ],
            'team_activities' => [], // Recent team activities
            'upcoming_bookings' => [], // Team's upcoming bookings
        ]);
    }

    public function team(): Response
    {
        return Inertia::render('Hod/Team');
    }

    public function bookings(): Response
    {
        return Inertia::render('Hod/Bookings');
    }

    public function approvals(): Response
    {
        return Inertia::render('Hod/Approvals');
    }

    public function reports(): Response
    {
        return Inertia::render('Hod/Reports');
    }
}
