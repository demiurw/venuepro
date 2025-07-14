<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the login page.
     * For OTP-only authentication, this shows a simple login form that redirects to OTP.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'message' => $request->session()->get('message'),
            'authMethod' => 'otp', // Indicate this is OTP-only
            'otpStep' => $request->session()->get('otp_step', 'request'),
            'otpEmail' => $request->session()->get('otp_email', ''),
        ]);
    }

    /**
     * Handle an incoming OTP login request.
     * This method processes the initial email submission for OTP generation.
     */
    public function requestOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        try {
            // Find the user
            $user = User::where('email', strtolower(trim($request->email)))->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'No account found with this email address.'
                ]);
            }

            // Check if user account is active
            if (!$user->isActive()) {
                $status = $user->status;

                if ($status === 'pending') {
                    return back()->withErrors([
                        'email' => 'Your account is pending verification. Please check your email for activation instructions.'
                    ]);
                } elseif ($status === 'inactive') {
                    return back()->withErrors([
                        'email' => 'Your account has been deactivated. Please contact support for assistance.'
                    ]);
                }

                return back()->withErrors([
                    'email' => 'Your account is not active. Please contact support.'
                ]);
            }

            // Verify user has OTP authentication method
            if (!$user->usesOtpAuth()) {
                Log::warning('Login attempt on non-OTP user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'auth_method' => $user->auth_method
                ]);

                return back()->withErrors([
                    'email' => 'This account uses a different authentication method. Please contact support.'
                ]);
            }

            // Generate and send OTP
            $result = $this->otpService->generateOtp($user, 'login');

            if ($result['success']) {
                // Store email and step in session for the verification flow
                $request->session()->put([
                    'otp_email' => $user->email,
                    'otp_step' => 'verify',
                    'otp_user_id' => $user->id
                ]);

                Log::info('Login OTP generated successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'expires_at' => $result['expires_at']
                ]);

                return redirect()->route('otp.create')->with([
                    'status' => 'success',
                    'message' => 'Login code sent to your email.',
                    'step' => 'verify',
                    'email' => $user->email,
                    'expires_at' => $result['expires_at'],
                ]);
            } else {
                return back()->withErrors([
                    'email' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Login OTP request failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'email' => 'Failed to send login code. Please try again.'
            ]);
        }
    }

    /**
     * Handle an incoming authentication request with OTP verification.
     * This processes the OTP code submitted by the user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp_code' => 'required|string|size:6',
            'remember' => 'boolean',
        ]);

        try {
            // Find the user
            $user = User::where('email', strtolower(trim($request->email)))->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'otp_code' => 'Invalid login attempt.',
                ]);
            }

            // Verify the OTP
            $result = $this->otpService->verifyOtp($user, $request->otp_code);

            if (!$result['success']) {
                // Handle specific error types
                if (isset($result['max_attempts_exceeded'])) {
                    return back()->withErrors([
                        'otp_code' => $result['message']
                    ])->with([
                        'step' => 'request', // Force back to email step
                        'email' => $user->email
                    ]);
                }

                if (isset($result['expired'])) {
                    return back()->withErrors([
                        'otp_code' => $result['message']
                    ])->with([
                        'step' => 'verify',
                        'email' => $user->email,
                        'otp_expired' => true
                    ]);
                }

                // Generic OTP verification failure
                return back()->withErrors([
                    'otp_code' => $result['message']
                ])->with([
                    'step' => 'verify',
                    'email' => $user->email,
                    'attempts_remaining' => $result['attempts_remaining'] ?? 0
                ]);
            }

            // OTP verification successful - log the user in
            Auth::login($user, $request->boolean('remember'));

            // Record login timestamp
            $user->recordLogin();

            // Set the tenant for this user
            if ($user->company_id && $user->company) {
                $user->company->makeCurrent();

                Log::info('Tenant set after successful login', [
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'company_name' => $user->company->name
                ]);
            } else {
                Log::warning('User logged in but has no company assigned', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            // Regenerate session for security
            $request->session()->regenerate();

            // Clear OTP-related session data
            $request->session()->forget(['otp_email', 'otp_step', 'otp_user_id']);

            Log::info('User logged in successfully via OTP', [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember' => $request->boolean('remember'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Redirect to intended destination or dashboard
            return redirect()->intended(route('dashboard', absolute: false))->with([
                'status' => 'success',
                'message' => 'Welcome back! You have been logged in successfully.'
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login verification failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'otp_code' => 'Login verification failed. Please try again.'
            ])->with([
                'step' => 'verify',
                'email' => $request->email
            ]);
        }
    }

    /**
     * Resend OTP for login.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        try {
            $user = User::where('email', strtolower(trim($request->email)))->first();

            if (!$user || !$user->isActive() || !$user->usesOtpAuth()) {
                return back()->withErrors([
                    'email' => 'Unable to resend code to this email address.'
                ]);
            }

            $result = $this->otpService->generateOtp($user, 'login');

            if ($result['success']) {
                Log::info('Login OTP resent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

                return back()->with([
                    'status' => 'success',
                    'message' => 'New login code sent to your email.',
                    'step' => 'verify',
                    'email' => $user->email,
                    'expires_at' => $result['expires_at'],
                ]);
            } else {
                return back()->withErrors([
                    'email' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to resend login OTP', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'email' => 'Failed to resend login code. Please try again.'
            ]);
        }
    }

    /**
     * Destroy an authenticated session (logout).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear any OTP-related session data
        $request->session()->forget(['otp_email', 'otp_step', 'otp_user_id']);

        return redirect('/')->with([
            'status' => 'success',
            'message' => 'You have been logged out successfully.'
        ]);
    }

    /**
     * Show login instructions or redirect based on authentication method.
     * This is a helper method for displaying OTP-specific login guidance.
     */
    public function showLoginHelp(): Response
    {
        return Inertia::render('Auth/LoginHelp', [
            'authentication_method' => 'otp',
            'features' => [
                'No passwords to remember',
                'Secure email-based verification',
                'Time-limited access codes',
                'Enhanced account security'
            ]
        ]);
    }
}
