<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    protected GroupRepositoryInterface $groupRepository;

    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function index(): Response
    {
        $user = auth()->user();
        $companyId = $user->company_id;

        // dd($companyId);

        return Inertia::render('Admin/Dashboard', [
            'user' => $user,
            'stats' => [
                'total_users' => \App\Models\User::where('company_id', $companyId)->count(),
                'total_groups' => \App\Models\Group::where('company_id', $companyId)->count(),
                'active_groups' => \App\Models\Group::where('company_id', $companyId)->where('is_active', true)->count(),
                'inactive_groups' => \App\Models\Group::where('company_id', $companyId)->where('is_active', false)->count(),
                'total_rooms' => \App\Models\Room::whereHas('building', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'active_bookings' => \App\Models\Booking::where('company_id', $companyId)
                    ->where(function($query) {
                        $query->where('date', '>', now()->toDateString())
                              ->orWhere(function($q) {
                                  $q->where('date', '=', now()->toDateString())
                                    ->whereTime('start_time', '>=', now()->toTimeString());
                              });
                    })
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'pending_approvals' => 0, // Implement based on your approval system
            ],
            'recent_activities' => [], // Implement based on your audit log
        ]);
    }

    public function buildings(): Response
    {
        return Inertia::render('Admin/Buildings');
    }

    public function rooms(): Response
    {
        return Inertia::render('Admin/Rooms');
    }

    public function createRoom(): Response
    {
        return Inertia::render('Admin/CreateRoom');
    }

    public function users(): Response
    {
        return Inertia::render('Admin/Users');
    }

    public function groups(): Response
    {
        return Inertia::render('Admin/Groups');
    }

    public function reports(): Response
    {
        return Inertia::render('Admin/Reports');
    }

    public function settings(): Response
    {
        return Inertia::render('Admin/Settings');
    }
}
