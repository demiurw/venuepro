<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:191|unique:users',
            'company_name' => 'required|string|max:255',
            'auth_method' => 'in:password,otp',
            'password' => ['required_if:auth_method,password', 'confirmed', Rules\Password::defaults()],
            'terms' => 'accepted',
        ]);

        // Create company first (this is a simplified version - you might want a separate service)
        $company = \App\Models\Company::create([
            'name' => $request->company_name,
            'slug' => \Str::slug($request->company_name),
            // Add other company fields as needed
        ]);

        // Get default role (you'll need to ensure this exists in your seeder)
        $defaultRole = \App\Models\Role::where('name', 'admin')->first();
        if (!$defaultRole) {
            return back()->withErrors(['email' => 'System configuration error. Please contact support.']);
        }

        // Determine auth method - default to OTP for new registrations
        $authMethod = $request->auth_method ?? 'otp';

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'company_id' => $company->id,
            'role_id' => $defaultRole->id,
            'user_type' => 'system_admin', // First user becomes system admin
            'auth_method' => $authMethod,
            'status' => 'pending', // User needs to verify email
            'password' => $authMethod === 'password' ? Hash::make($request->password) : null,
            'email_verification_token' => \Str::random(100),
        ]);

        event(new Registered($user));

        // Handle verification based on auth method
        if ($authMethod === 'otp') {
            // Generate OTP for account verification
            $result = $this->otpService->generateOtp($user, 'verification');

            if ($result['success']) {
                return redirect()->route('otp.create')->with([
                    'status' => 'registration_success',
                    'message' => 'Registration successful! Please check your email for the verification code.',
                    'step' => 'verify',
                    'email' => $user->email,
                    'verification_type' => 'account_activation'
                ]);
            } else {
                // If OTP generation fails, fall back to email verification
                return redirect()->route('verification.notice')->with([
                    'status' => 'registration_success',
                    'message' => 'Registration successful! Please check your email for verification instructions.',
                ]);
            }
        } else {
            // Password-based registration - send email verification
            return redirect()->route('verification.notice')->with([
                'status' => 'registration_success',
                'message' => 'Registration successful! Please verify your email address to continue.',
            ]);
        }
    }
}
