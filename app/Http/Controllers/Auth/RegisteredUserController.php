<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Services\Auth\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Creates user with OTP-only authentication and sends verification OTP.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate registration data (removed password validation)
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:191|unique:users',
            'company_name' => 'required|string|max:255',
            'terms' => 'accepted',
        ]);

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

            // Create user with OTP-only authentication
            $user = $this->createUser($request, $company, $defaultRole);

            DB::commit();

            // Fire the registered event
            event(new Registered($user));

            // Generate and send OTP for account verification
            $otpResult = $this->otpService->generateOtp($user, 'account_verification');

            if ($otpResult['success']) {
                Log::info('User registered successfully with OTP verification', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'company_id' => $company->id,
                    'expires_at' => $otpResult['expires_at']
                ]);

                return redirect()->route('otp.create')->with([
                    'status' => 'registration_success',
                    'message' => 'Registration successful! Please check your email for the verification code to activate your account.',
                    'step' => 'verify',
                    'email' => $user->email,
                    'verification_type' => 'account_activation',
                    'expires_at' => $otpResult['expires_at'],
                    'user_id' => $user->id,
                ]);
            } else {
                // If OTP generation fails, provide fallback instructions
                Log::warning('OTP generation failed during registration', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $otpResult['message']
                ]);

                return redirect()->route('otp.create')->with([
                    'status' => 'registration_partial',
                    'message' => 'Registration completed, but there was an issue sending the verification code. Please try requesting a new verification code.',
                    'step' => 'request',
                    'email' => $user->email,
                    'verification_type' => 'account_activation',
                    'user_id' => $user->id,
                ]);
            }

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
