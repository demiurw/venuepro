<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\OtpAttempt;
use App\Services\Auth\OtpService;
use App\Http\Middleware\DashboardRedirectMiddleware;
use App\Enums\UserStatus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register', [
            'status' => session('status'),
            'message' => session('message'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     * Validates OTP and creates user with active status for streamlined registration.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate registration data including OTP
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:191|unique:users',
            'company_name' => 'required|string|max:255',
            'otp' => 'required|string|size:6', // Add OTP validation
            'terms' => 'accepted',
        ]);

        // Verify OTP before creating user account
        $otpResult = $this->otpService->verifyOtpForEmail($request->email, $request->otp);
        
        if (!$otpResult['success']) {
            return back()->withErrors([
                'otp' => $otpResult['message']
            ])->withInput($request->except(['otp']));
        }

        try {
            DB::beginTransaction();

            // Create company first
            $company = $this->createCompany($request->company_name);

            // Get default admin role for first user
            $defaultRole = $this->getDefaultAdminRole();
            if (!$defaultRole) {
                DB::rollBack();
                return back()->withErrors([
                    'email' => 'System configuration error. Please contact support.'
                ]);
            }

            // Create user with OTP-only authentication (now active since OTP is already verified)
            $user = $this->createActiveUser($request, $company, $defaultRole);

            // Update the OTP attempt with user_id and company_id for future reference
            $this->updateOtpAttemptWithUserInfo($otpResult['otp_attempt_id'], $user->id, $company->id);

            DB::commit();

            // Fire the registered event
            event(new Registered($user));

            // Log the user in immediately
            Auth::login($user);
            $request->session()->regenerate();

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            Log::info('User registered and logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'company_id' => $company->id,
                'user_type' => $user->user_type
            ]);

            // Redirect to role-appropriate dashboard
            $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);

            return redirect($dashboardRoute)->with([
                'status' => 'registration_success',
                'message' => 'Registration successful! Welcome to VenuePro.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Registration failed', [
                'email' => $request->email,
                'company_name' => $request->company_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'email' => 'Registration failed. Please try again or contact support if the problem persists.'
            ])->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    /**
     * Create a new company for the registering user.
     *
     * @param string $companyName
     * @return Company
     */
    protected function createCompany(string $companyName): Company
    {
        return Company::create([
            'name' => $companyName,
            'slug' => Str::slug($companyName . '-' . Str::random(6)), // Add random suffix to ensure uniqueness
            'subscription_level' => 'trial', // Start with trial
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get the default admin role for new company registration.
     *
     * @return Role|null
     */
    protected function getDefaultAdminRole(): ?Role
    {
        // For multi-tenant setup, you might need to create role per company
        // For now, get the global admin role
        return Role::where('name', 'admin')
            ->orWhere('name', 'system_admin')
            ->first();
    }

    /**
     * Create a new user with OTP-only authentication.
     *
     * @param Request $request
     * @param Company $company
     * @param Role $role
     * @return User
     */
    protected function createUser(Request $request, Company $company, Role $role): User
    {
        return User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => strtolower(trim($request->email)),
            'company_id' => $company->id,
            'role_id' => $role->id,
            'user_type' => 'system_admin', // First user becomes system admin
            'auth_method' => 'otp', // Force OTP-only authentication
            'status' => 'pending', // User needs to verify email first
            'email_verification_token' => Str::random(100),
        ]);
    }

    /**
     * Create a new active user with OTP-only authentication.
     * Used for streamlined registration where OTP is already verified.
     *
     * @param Request $request
     * @param Company $company
     * @param Role $role
     * @return User
     */
    protected function createActiveUser(Request $request, Company $company, Role $role): User
    {
        return User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => strtolower(trim($request->email)),
            'company_id' => $company->id,
            'role_id' => $role->id,
            'user_type' => 'system_admin', // First user becomes system admin
            'auth_method' => 'otp', // Force OTP-only authentication
            'status' => UserStatus::ACTIVE, // User is active since OTP is already verified
            'email_verified_at' => now(), // Mark email as verified
        ]);
    }

    /**
     * Update OTP attempt record with user and company information.
     *
     * @param int $otpAttemptId
     * @param int $userId
     * @param int $companyId
     * @return void
     */
    protected function updateOtpAttemptWithUserInfo(int $otpAttemptId, int $userId, int $companyId): void
    {
        try {
            OtpAttempt::where('id', $otpAttemptId)->update([
                'user_id' => $userId,
                'company_id' => $companyId,
            ]);

            Log::info('Updated OTP attempt with user info', [
                'otp_attempt_id' => $otpAttemptId,
                'user_id' => $userId,
                'company_id' => $companyId
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update OTP attempt with user info', [
                'otp_attempt_id' => $otpAttemptId,
                'user_id' => $userId,
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send OTP for registration without creating user account yet.
     * This is for the streamlined single-page registration flow.
     */
    public function sendRegistrationOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:191|unique:users',
        ]);

        try {
            // Create a temporary user object to use with OtpService
            // This user won't be saved to database yet
            $tempUser = new User([
                'email' => strtolower(trim($request->email)),
                'first_name' => 'Temp', // Will be replaced during actual registration
                'last_name' => 'User',
                'status' => 'pending',
                'auth_method' => 'otp',
            ]);

            // Generate OTP using email as identifier
            $result = $this->otpService->generateOtpForEmail($request->email, 'registration');

            if ($result['success']) {
                Log::info('Registration OTP sent successfully', [
                    'email' => $request->email,
                    'expires_at' => $result['expires_at']
                ]);

                return back()->with([
                    'status' => 'success',
                    'message' => 'Verification code sent to your email.',
                    'expires_at' => $result['expires_at'],
                ]);
            } else {
                return back()->withErrors([
                    'email' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send registration OTP', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'email' => 'Failed to send verification code. Please try again.',
            ]);
        }
    }

    /**
     * Resend verification OTP for pending users.
     * This can be called if the initial OTP fails or expires.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->email)
                ->where('status', 'pending')
                ->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'User not found or already verified.'
                ]);
            }

            $otpResult = $this->otpService->generateOtp($user, 'account_verification');

            if ($otpResult['success']) {
                return back()->with([
                    'status' => 'otp_sent',
                    'message' => 'Verification code sent successfully.',
                    'expires_at' => $otpResult['expires_at'],
                ]);
            } else {
                return back()->withErrors([
                    'email' => $otpResult['message']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to resend verification OTP', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'email' => 'Failed to send verification code. Please try again.'
            ]);
        }
    }
}
