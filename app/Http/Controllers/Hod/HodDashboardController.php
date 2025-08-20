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
        $user = auth()->user()->load(['role', 'group']);

        // Get team members (users in the same group)
        $teamMembers = \App\Models\User::where('group_id', $user->group_id)
            ->where('id', '!=', $user->id) // Exclude HOD themselves
            ->get();

        // Get team bookings (bookings by or for team members)
        $teamBookings = \App\Models\Booking::whereHas('creator', function($q) use ($user) {
                $q->where('group_id', $user->group_id);
            })
            ->orWhereHas('bookedForUser', function($q) use ($user) {
                $q->where('group_id', $user->group_id);
            })
            ->where('start_time', '>=', now())
            ->count();

        // Get weekly team bookings
        $weeklyTeamBookings = \App\Models\Booking::whereHas('creator', function($q) use ($user) {
                $q->where('group_id', $user->group_id);
            })
            ->orWhereHas('bookedForUser', function($q) use ($user) {
                $q->where('group_id', $user->group_id);
            })
            ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Get recent team activities
        $teamActivity = \App\Models\Booking::with(['creator', 'bookedForUser', 'room'])
            ->where(function($query) use ($user) {
                $query->whereHas('creator', function($q) use ($user) {
                    $q->where('group_id', $user->group_id);
                })
                ->orWhereHas('bookedForUser', function($q) use ($user) {
                    $q->where('group_id', $user->group_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($booking) {
                $user = $booking->creator ?? $booking->bookedForUser;
                return [
                    'id' => $booking->id,
                    'user_name' => $user?->full_name ?? 'Unknown User',
                    'action' => "Booked " . ($booking->room->name ?? 'Room') . " for " . $booking->start_time->format('M j, Y'),
                    'formatted_time' => $booking->created_at->diffForHumans(),
                    'status' => ucfirst($booking->status),
                    'status_class' => $this->getStatusClass($booking->status),
                    'initials' => $this->getInitials($user->full_name ?? 'U U'),
                    'bgColor' => $this->getRandomBgColor(),
                    'textColor' => 'text-white',
                ];
            });

        // Get approval requests (bookings pending HOD approval)
        $approvalRequests = \App\Models\Booking::with(['creator', 'room'])
            ->where('status', 'pending_approval')
            ->whereHas('creator', function($q) use ($user) {
                $q->where('group_id', $user->group_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'room_name' => $booking->room->name ?? 'Unknown Room',
                    'user_name' => ($booking->creator ?? $booking->bookedForUser)->full_name ?? 'Unknown User',
                    'formatted_date' => $booking->start_time->format('M j, Y'),
                    'formatted_time' => $booking->start_time->format('g:i A') . ' - ' . $booking->end_time->format('g:i A'),
                    'bg_color' => 'bg-orange-100',
                    'text_color' => 'text-orange-600',
                ];
            });

        // Team performance metrics
        $performance = [
            'booking_efficiency' => $this->calculateBookingEfficiency($user->group_id),
            'team_utilization' => $this->calculateTeamUtilization($user->group_id),
            'avg_response_time' => $this->calculateAvgResponseTime($user->group_id),
            'team_satisfaction' => $this->calculateTeamSatisfaction($user->group_id),
        ];

        return Inertia::render('Hod/Dashboard', [
            'user' => $user,
            'stats' => [
                'team_members' => $teamMembers->count(),
                'team_members_change' => 0, // TODO: Calculate month-over-month change
                'team_bookings' => $teamBookings,
                'team_bookings_weekly' => $weeklyTeamBookings,
                'pending_approvals' => $approvalRequests->count(),
                'department_budget' => 50000, // TODO: Implement budget tracking
                'budget_utilization' => 65, // TODO: Calculate actual utilization
            ],
            'teamActivity' => $teamActivity,
            'approvalRequests' => $approvalRequests,
            'performance' => $performance,
        ]);
    }

    private function getStatusClass($status)
    {
        return match($status) {
            'confirmed' => 'bg-green-100 text-green-700',
            'pending' => 'bg-yellow-100 text-yellow-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    private function getInitials($name)
    {
        $words = explode(' ', $name);
        return strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    }

    private function getRandomBgColor()
    {
        $colors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-orange-500', 'bg-pink-500'];
        return $colors[array_rand($colors)];
    }

    private function calculateBookingEfficiency($groupId)
    {
        // TODO: Implement actual calculation
        return 85;
    }

    private function calculateTeamUtilization($groupId)
    {
        // TODO: Implement actual calculation
        return 78;
    }

    private function calculateAvgResponseTime($groupId)
    {
        // TODO: Implement actual calculation
        return '15m';
    }

    private function calculateTeamSatisfaction($groupId)
    {
        // TODO: Implement actual calculation
        return 4.2;
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

    public function approveRequest(Request $request, $bookingId)
    {
        $user = auth()->user();
        $booking = \App\Models\Booking::findOrFail($bookingId);
        
        // Verify this booking belongs to the HOD's group
        if (!$booking->creator || $booking->creator->group_id !== $user->group_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $booking->update(['status' => 'confirmed']);
        
        return response()->json(['message' => 'Booking approved successfully']);
    }

    public function rejectRequest(Request $request, $bookingId)
    {
        $user = auth()->user();
        $booking = \App\Models\Booking::findOrFail($bookingId);
        
        // Verify this booking belongs to the HOD's group
        if (!$booking->creator || $booking->creator->group_id !== $user->group_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $booking->update([
            'status' => 'cancelled',
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Rejected by Head of Department'
        ]);
        
        return response()->json(['message' => 'Booking rejected successfully']);
    }
}
