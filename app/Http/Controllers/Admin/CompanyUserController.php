<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Group;
use App\Services\Auth\OtpService;
use App\Services\UserService;
use App\Http\Requests\Admin\UpdateUserRequest;
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
    protected $userService;

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

    public function __construct(OtpService $otpService, UserService $userService)
    {
        $this->otpService = $otpService;
        $this->userService = $userService;
        
        // Ensure only system_admin and hod users can access this controller
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !in_array($user->user_type, ['system_admin', 'hod'])) {
                abort(403, 'Unauthorized access. Only System Administrators and HODs can manage users.');
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
        $filters = $request->only(['search', 'role', 'status']);
        
        // Get paginated users using the service
        $users = $this->userService->getPaginatedUsers($user, $filters, 15);
        
        // Get role statistics
        $roleStats = $this->userService->getUserStatistics($user);
        
        // Get allowed roles for current user
        $allowedRoles = $this->userService->getAllowedRoles($user);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'allowedRoles' => $allowedRoles,
            'roleStats' => $roleStats,
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        // Get available groups and roles using the service
        $groups = $this->userService->getAvailableGroups($user);
        $allowedRoles = $this->userService->getAllowedRoles($user);
        $roleRequirements = $this->userService->getRoleRequirements();

        return Inertia::render('Admin/Users/Create', [
            'allowedRoles' => $allowedRoles,
            'groups' => $groups,
            'roleRequirements' => $roleRequirements,
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        // Define validation rules
        $rules = [
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
        ];

        // Add group_id validation - required only for booking_agent and hod roles
        if (in_array($request->input('user_type'), ['booking_agent', 'hod'])) {
            $rules['group_id'] = [
                'required',
                'integer',
                Rule::exists('groups', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id)
                                 ->where('is_active', true);
                })
            ];
        } else {
            // Optional for other roles (like invitee)
            $rules['group_id'] = [
                'nullable',
                'integer',
                Rule::exists('groups', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id)
                                 ->where('is_active', true);
                })
            ];
        }
        
        // Validate the request
        $validated = $request->validate($rules);

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
                'group_id' => $validated['group_id'] ?? null,
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
     * Show the form for editing a user
     */
    public function edit(User $companyUser): Response
    {
        $currentUser = auth()->user();
        
        // Check if user can be managed by current user using service
        $user = $this->userService->findManageableUser($currentUser, $companyUser->id);
        
        if (!$user) {
            abort(403, 'User not found or access denied.');
        }

        // Get available groups and roles using the service
        $groups = $this->userService->getAvailableGroups($currentUser);
        $allowedRoles = $this->userService->getAllowedRoles($currentUser);
        $roleRequirements = $this->userService->getRoleRequirements();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'allowedRoles' => $allowedRoles,
            'groups' => $groups,
            'roleRequirements' => $roleRequirements,
        ]);
    }

    /**
     * Display the specified user
     */
    public function show(User $companyUser): Response
    {
        $currentUser = auth()->user();
        
        // Check if user can be managed by current user using service
        $user = $this->userService->findManageableUser($currentUser, $companyUser->id);
        
        if (!$user) {
            abort(403, 'User not found or access denied.');
        }

        // Get user statistics using repository
        $userStats = $this->userService->getUserBookingStatistics($companyUser->id);

        // Get available groups and roles using the service
        $groups = $this->userService->getAvailableGroups($currentUser);
        $allowedRoles = $this->userService->getAllowedRoles($currentUser);
        $roleRequirements = $this->userService->getRoleRequirements();

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'userStats' => $userStats,
            'allowedRoles' => $allowedRoles,
            'groups' => $groups,
            'roleRequirements' => $roleRequirements,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $companyUser): RedirectResponse
    {
        $currentUser = auth()->user();
        $validated = $request->validated();

        // Update user using service
        $result = $this->userService->updateUser($currentUser, $companyUser->id, $validated);

        if (!$result['success']) {
            return back()->withErrors(['email' => $result['message']])->withInput();
        }

        return redirect()->route('admin.users.show', $companyUser)
            ->with('success', $result['message']);
    }

    /**
     * Deactivate the specified user (soft deletion via status change)
     */
    public function destroy(Request $request, User $companyUser): RedirectResponse
    {
        $currentUser = auth()->user();

        // Validate OTP
        $validated = $request->validate(['otp' => 'required|string|digits:6']);
        $otpResult = $this->otpService->verifyOtp($currentUser, $validated['otp']);

        if (!$otpResult['success']) {
            return back()->withErrors(['otp' => $otpResult['message']]);
        }
        
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
            $activeBookings = $companyUser->allBookings()
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
    public function reactivate(Request $request, User $companyUser): RedirectResponse
    {
        $currentUser = auth()->user();

        // Validate OTP
        $validated = $request->validate(['otp' => 'required|string|digits:6']);
        $otpResult = $this->otpService->verifyOtp($currentUser, $validated['otp']);

        if (!$otpResult['success']) {
            return back()->withErrors(['otp' => $otpResult['message']]);
        }
        
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
        
        $result = $this->userService->resendOtp($currentUser, $companyUser->id);

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Send an OTP to the admin for a specific action
     */
    public function sendActionOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', Rule::in(['user_status_change'])],
        ]);

        $admin = auth()->user();

        $otpResult = $this->otpService->generateOtp($admin, $validated['action']);

        if (!$otpResult['success']) {
            return back()->withErrors(['otp' => $otpResult['message']]);
        }

        return back()->with('success', 'An OTP has been sent to your email.');
    }
}