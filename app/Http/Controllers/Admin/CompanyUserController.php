<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\Auth\OtpService;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompanyUserController extends Controller
{
    protected $otpService;

    /**
     * Allowed roles that System Admin can assign to users
     */
    protected $allowedRoles = ['booking_agent', 'invitee', 'hod'];

    /**
     * Mapping of user types to role IDs
     */
    protected $userTypeToRoleMapping = [
        'hod' => 1,
        'booking_agent' => 4,
        'invitee' => 3,
    ];

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
        
        // Ensure only system_admin users can access this controller
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_type !== 'system_admin') {
                abort(403, 'Unauthorized access. Only System Administrators can manage company users.');
            }
            return $next($request);
        });
    }

    /**
     * Display a paginated list of company users
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Build query for users in the same company, excluding system_admin and venuepro_admin
        $query = User::where('company_id', $user->company_id)
            ->whereIn('user_type', $this->allowedRoles)
            ->with(['role']);

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter if provided
        if ($request->filled('role')) {
            $role = $request->get('role');
            if (in_array($role, $this->allowedRoles)) {
                $query->where('user_type', $role);
            }
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $status = $request->get('status');
            $validStatuses = UserStatus::values();
            if (in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
        }

        // Order by created date (newest first)
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $users = $query->paginate(15)->withQueryString();

        // Get role statistics for dashboard
        $roleStats = User::where('company_id', $user->company_id)
            ->whereIn('user_type', $this->allowedRoles)
            ->selectRaw('user_type, COUNT(*) as count')
            ->groupBy('user_type')
            ->pluck('count', 'user_type');

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
            'allowedRoles' => $this->allowedRoles,
            'roleStats' => $roleStats,
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'allowedRoles' => $this->allowedRoles,
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('users')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ],
            'user_type' => ['required', 'string', Rule::in($this->allowedRoles)],
        ]);

        try {
            DB::beginTransaction();

            // Get the role ID for the user type
            $roleId = $this->userTypeToRoleMapping[$validated['user_type']];

            // Create the new user
            $newUser = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'user_type' => $validated['user_type'],
                'role_id' => $roleId,
                'company_id' => $user->company_id,
                'status' => UserStatus::PENDING, // User starts as pending until they complete OTP verification
                'auth_method' => 'otp',
            ]);

            // Generate initial OTP for the new user
            $otpResult = $this->otpService->generateOtpForEmail(
                $validated['email'], 
                'account_setup'
            );

            if (!$otpResult['success']) {
                DB::rollBack();
                Log::error('Failed to generate OTP for new user', [
                    'user_id' => $newUser->id,
                    'email' => $validated['email'],
                    'error' => $otpResult['message']
                ]);
                
                return back()->withErrors([
                    'email' => 'Failed to send setup email to the user. Please try again.'
                ])->withInput();
            }

            DB::commit();

            Log::info('New user created by system admin', [
                'created_user_id' => $newUser->id,
                'created_by' => $user->id,
                'company_id' => $user->company_id,
                'user_type' => $validated['user_type']
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "User {$newUser->full_name} has been created successfully. Setup instructions have been sent to their email.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create new user', [
                'error' => $e->getMessage(),
                'created_by' => $user->id,
                'company_id' => $user->company_id,
                'data' => $validated
            ]);

            return back()->withErrors([
                'email' => 'Failed to create user. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $companyUser): Response
    {
        $currentUser = auth()->user();
        
        // Ensure the user belongs to the same company and has allowed role
        if ($companyUser->company_id !== $currentUser->company_id || 
            !in_array($companyUser->user_type, $this->allowedRoles)) {
            abort(403, 'User not found or access denied.');
        }

        // Load relationships
        $companyUser->load(['role', 'groups', 'bookings' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        // Get user statistics
        $userStats = [
            'total_bookings' => $companyUser->bookings()->count(),
            'active_bookings' => $companyUser->bookings()
                ->where(function($query) {
                    $query->where('date', '>', now()->toDateString())
                          ->orWhere(function($q) {
                              $q->where('date', '=', now()->toDateString())
                                ->whereTime('start_time', '>=', now()->toTimeString());
                          });
                })
                ->where('status', '!=', 'cancelled')
                ->count(),
            'groups_count' => $companyUser->groups()->count(),
            'last_activity' => $companyUser->last_login_at,
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $companyUser,
            'userStats' => $userStats,
            'allowedRoles' => $this->allowedRoles,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $companyUser): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Ensure the user belongs to the same company and has allowed role
        if ($companyUser->company_id !== $currentUser->company_id || 
            !in_array($companyUser->user_type, $this->allowedRoles)) {
            abort(403, 'User not found or access denied.');
        }

        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('users')->where(function ($query) use ($currentUser) {
                    return $query->where('company_id', $currentUser->company_id);
                })->ignore($companyUser->id)
            ],
            'user_type' => ['required', 'string', Rule::in($this->allowedRoles)],
            'status' => ['sometimes', 'string', Rule::in(UserStatus::values())],
        ]);

        try {
            $emailChanged = $companyUser->email !== $validated['email'];
            
            // Get the role ID for the user type
            $roleId = $this->userTypeToRoleMapping[$validated['user_type']];

            // Update user details
            $companyUser->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'user_type' => $validated['user_type'],
                'role_id' => $roleId,
                'status' => $validated['status'] ?? $companyUser->status,
            ]);

            // If email changed and user is active, send notification about the change
            if ($emailChanged && $companyUser->isActive()) {
                // Generate OTP for email verification of the new address
                $otpResult = $this->otpService->generateOtp($companyUser, 'email_change_verification');
                
                if ($otpResult['success']) {
                    Log::info('Email change verification OTP sent', [
                        'user_id' => $companyUser->id,
                        'old_email' => $companyUser->getOriginal('email'),
                        'new_email' => $validated['email'],
                        'updated_by' => $currentUser->id
                    ]);
                }
            }

            Log::info('User updated by system admin', [
                'updated_user_id' => $companyUser->id,
                'updated_by' => $currentUser->id,
                'changes' => $companyUser->getChanges(),
                'email_changed' => $emailChanged
            ]);

            $message = "User {$companyUser->full_name} has been updated successfully.";
            if ($emailChanged) {
                $message .= " A verification email has been sent to the new email address.";
            }

            return redirect()->route('admin.users.show', $companyUser)
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'error' => $e->getMessage(),
                'user_id' => $companyUser->id,
                'updated_by' => $currentUser->id,
                'data' => $validated
            ]);

            return back()->withErrors([
                'email' => 'Failed to update user. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Deactivate the specified user (soft deletion via status change)
     */
    public function destroy(User $companyUser): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Ensure the user belongs to the same company and has allowed role
        if ($companyUser->company_id !== $currentUser->company_id || 
            !in_array($companyUser->user_type, $this->allowedRoles)) {
            abort(403, 'User not found or access denied.');
        }

        // Prevent self-deletion
        if ($companyUser->id === $currentUser->id) {
            return back()->withErrors([
                'user' => 'You cannot deactivate your own account.'
            ]);
        }

        // Check if user is already inactive
        if ($companyUser->isInactive()) {
            return back()->withErrors([
                'user' => 'User is already inactive.'
            ]);
        }

        try {
            DB::beginTransaction();

            $userName = $companyUser->full_name;
            $userId = $companyUser->id;

            // Check if user has active bookings
            $activeBookings = $companyUser->bookings()
                ->where(function($query) {
                    $query->where('date', '>', now()->toDateString())
                          ->orWhere(function($q) {
                              $q->where('date', '=', now()->toDateString())
                                ->whereTime('start_time', '>=', now()->toTimeString());
                          });
                })
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($activeBookings > 0) {
                return back()->withErrors([
                    'user' => "Cannot deactivate user {$userName}. They have {$activeBookings} active booking(s). Please cancel or reassign these bookings first."
                ]);
            }

            // Deactivate the user instead of deleting
            $companyUser->deactivate();

            DB::commit();

            Log::info('User deactivated by system admin', [
                'deactivated_user_id' => $userId,
                'deactivated_user_name' => $userName,
                'deactivated_by' => $currentUser->id,
                'company_id' => $currentUser->company_id
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "User {$userName} has been deactivated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to deactivate user', [
                'error' => $e->getMessage(),
                'user_id' => $companyUser->id,
                'deactivated_by' => $currentUser->id
            ]);

            return back()->withErrors([
                'user' => 'Failed to deactivate user. Please try again.'
            ]);
        }
    }

    /**
     * Reactivate a deactivated user
     */
    public function reactivate(User $companyUser): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Ensure the user belongs to the same company and has allowed role
        if ($companyUser->company_id !== $currentUser->company_id || 
            !in_array($companyUser->user_type, $this->allowedRoles)) {
            abort(403, 'User not found or access denied.');
        }

        // Check if user is already active
        if ($companyUser->isActive()) {
            return back()->withErrors([
                'user' => 'User is already active.'
            ]);
        }

        try {
            $userName = $companyUser->full_name;
            $userId = $companyUser->id;

            // Reactivate the user
            $companyUser->activate();

            Log::info('User reactivated by system admin', [
                'reactivated_user_id' => $userId,
                'reactivated_user_name' => $userName,
                'reactivated_by' => $currentUser->id,
                'company_id' => $currentUser->company_id
            ]);

            return back()->with('success', "User {$userName} has been reactivated successfully.");

        } catch (\Exception $e) {
            Log::error('Failed to reactivate user', [
                'error' => $e->getMessage(),
                'user_id' => $companyUser->id,
                'reactivated_by' => $currentUser->id
            ]);

            return back()->withErrors([
                'user' => 'Failed to reactivate user. Please try again.'
            ]);
        }
    }

    /**
     * Resend setup/verification OTP to a user
     */
    public function resendOtp(User $companyUser): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Ensure the user belongs to the same company and has allowed role
        if ($companyUser->company_id !== $currentUser->company_id || 
            !in_array($companyUser->user_type, $this->allowedRoles)) {
            abort(403, 'User not found or access denied.');
        }

        // Determine OTP purpose based on user status
        $purpose = $companyUser->isPending() ? 'account_setup' : 'login';

        try {
            $otpResult = $this->otpService->generateOtp($companyUser, $purpose);

            if (!$otpResult['success']) {
                Log::warning('Failed to resend OTP', [
                    'user_id' => $companyUser->id,
                    'requested_by' => $currentUser->id,
                    'error' => $otpResult['message']
                ]);

                return back()->withErrors([
                    'otp' => $otpResult['message']
                ]);
            }

            Log::info('OTP resent by system admin', [
                'user_id' => $companyUser->id,
                'requested_by' => $currentUser->id,
                'purpose' => $purpose
            ]);

            return back()->with('success', "Setup instructions have been resent to {$companyUser->full_name}'s email address.");

        } catch (\Exception $e) {
            Log::error('Failed to resend OTP', [
                'error' => $e->getMessage(),
                'user_id' => $companyUser->id,
                'requested_by' => $currentUser->id
            ]);

            return back()->withErrors([
                'otp' => 'Failed to resend setup instructions. Please try again.'
            ]);
        }
    }
}