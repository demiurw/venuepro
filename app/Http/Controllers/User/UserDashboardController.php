<?php

// User Dashboard Controller
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('User/Dashboard', [
            'user' => $user,
            'stats' => [
                'upcoming_bookings' => \App\Models\Booking::where('user_id', $user->id)
                    ->where('start_time', '>=', now())
                    ->count(),
                'past_bookings' => \App\Models\Booking::where('user_id', $user->id)
                    ->where('start_time', '<', now())
                    ->count(),
                'favorite_rooms' => 0, // Implement favorite rooms
                'meeting_hours' => 0, // Total meeting hours this month
            ],
            'upcoming_bookings' => [], // Next few bookings
            'available_rooms' => [], // Currently available rooms
        ]);
    }

    public function bookings(): Response
    {
        return Inertia::render('User/Bookings');
    }

    public function calendar(): Response
    {
        return Inertia::render('User/Calendar');
    }

    public function profile(): Response
    {
        return Inertia::render('User/Profile');
    }
}
