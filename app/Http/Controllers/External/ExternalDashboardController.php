<?php

// External User Dashboard Controller
namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExternalDashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('External/Dashboard', [
            'user' => $user,
            'stats' => [
                'active_bookings' => \App\Models\Booking::where('user_id', $user->id)
                    ->where('start_time', '>=', now())
                    ->count(),
                'pending_requests' => \App\Models\ExternalBookingRequest::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'approved_requests' => \App\Models\ExternalBookingRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->count(),
            ],
            'recent_bookings' => [], // Recent bookings
            'booking_requests' => [], // Pending requests
        ]);
    }

    public function bookings(): Response
    {
        return Inertia::render('External/Bookings');
    }

    public function requests(): Response
    {
        return Inertia::render('External/Requests');
    }

    public function createRequest(): Response
    {
        return Inertia::render('External/CreateRequest');
    }
}
