<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Middleware\DashboardRedirectMiddleware;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {}

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Login');
    }

    /**
     * Handle OTP request (first step of login)
     */
    public function requestOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;

        // Rate limiting
        $key = 'otp-request:' . $email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many OTP requests. Please try again in {$seconds} seconds."],
            ]);
        }

        RateLimiter::hit($key, 60); // 1 minute window

        try {
            // Find the user by email
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['User not found.'],
                ]);
            }

            // Check if user is active
            if ($user->status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => ['Your account is not active. Please contact your administrator.'],
                ]);
            }

            $this->otpService->generateOtp($user);

            return redirect()->route('login')
                ->with('otp_sent', true)
                ->with('email', $email)
                ->with('success', 'OTP sent to your email address.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['email' => 'Failed to send OTP. Please try again.']);
        }
    }

    /**
     * Handle OTP verification and authentication (second step of login)
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $otp = $request->otp;

        // Rate limiting for OTP verification
        $key = 'otp-verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'otp' => ["Too many verification attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        try {
            // Find the user by email
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                RateLimiter::hit($key, 60);
                throw ValidationException::withMessages([
                    'email' => ['User not found.'],
                ]);
            }

            // Verify the OTP
            $isValidOtp = $this->otpService->verifyOtp($user, $otp);

            if (!$isValidOtp) {
                RateLimiter::hit($key, 60);
                throw ValidationException::withMessages([
                    'otp' => ['The provided OTP is invalid or has expired.'],
                ]);
            }

            // Clear rate limiter on successful verification
            RateLimiter::clear($key);

            // Log the user in
            Auth::login($user, $request->boolean('remember'));

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Regenerate session
            $request->session()->regenerate();

            // Redirect to appropriate dashboard based on user role
            $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);

            return redirect()->intended($dashboardRoute);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            RateLimiter::hit($key, 60);
            return redirect()->back()
                ->withErrors(['otp' => 'An error occurred during authentication. Please try again.']);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;

        // Rate limiting for resend
        $key = 'otp-resend:' . $email;
        if (RateLimiter::tooManyAttempts($key, 2)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many resend requests. Please try again in {$seconds} seconds."],
            ]);
        }

        RateLimiter::hit($key, 60);

        try {
            // Find the user by email
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['User not found.'],
                ]);
            }

            // Check if user is active
            if ($user->status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => ['Your account is not active. Please contact your administrator.'],
                ]);
            }

            $this->otpService->generateOtp($user);

            return redirect()->back()
                ->with('success', 'New OTP sent to your email address.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['email' => 'Failed to resend OTP. Please try again.']);
        }
    }

    /**
     * Show login help page
     */
    public function showLoginHelp(): Response
    {
        return Inertia::render('Auth/LoginHelp');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
