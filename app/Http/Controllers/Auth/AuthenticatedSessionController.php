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

            // Generate OTP using your existing service
            $result = $this->otpService->generateOtp($user, 'login');

            if (!$result['success']) {
                throw ValidationException::withMessages([
                    'email' => [$result['message'] ?? 'Failed to send OTP. Please try again.'],
                ]);
            }

            return redirect()->route('login')
                ->with('otp_sent', true)
                ->with('email', $email)
                ->with('success', 'OTP sent to your email address.')
                ->with('expires_at', $result['expires_at'] ?? null);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP request failed in AuthenticatedSessionController', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
            'otp_code' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $otp = $request->otp_code;

        // Rate limiting for OTP verification
        $key = 'otp-verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'otp_code' => ["Too many verification attempts. Please try again in {$seconds} seconds."],
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

            // Verify the OTP using your existing OtpService
            $result = $this->otpService->verifyOtp($user, $otp);

            if (!$result['success']) {
                RateLimiter::hit($key, 60);

                // Handle different error cases from your OtpService
                $errorMessage = $result['message'] ?? 'The provided OTP is invalid or has expired.';

                throw ValidationException::withMessages([
                    'otp_code' => [$errorMessage],
                ]);
            }

            // Clear rate limiter on successful verification
            RateLimiter::clear($key);

            // Log the user in
            Auth::login($user, $request->boolean('remember'));

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Set tenant context if user has company
            if ($user->company_id) {
                $request->session()->put('company_id', $user->company_id);
                
                // Set the tenant immediately for this request
                try {
                    $company = \App\Models\Company::find($user->company_id);
                    if ($company) {
                        $company->makeCurrent();
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Could not set tenant during login', [
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Regenerate session
            $request->session()->regenerate();

            // Log successful login
            \Illuminate\Support\Facades\Log::info('User logged in via OTP through AuthenticatedSessionController', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            // Redirect to appropriate dashboard based on user role
            $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);

            return redirect()->intended($dashboardRoute);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            RateLimiter::hit($key, 60);
            \Illuminate\Support\Facades\Log::error('Authentication error in AuthenticatedSessionController', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['otp_code' => 'An error occurred during authentication. Please try again.']);
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

            // Generate new OTP using your existing service
            $result = $this->otpService->generateOtp($user, 'login');

            if (!$result['success']) {
                throw ValidationException::withMessages([
                    'email' => [$result['message'] ?? 'Failed to resend OTP. Please try again.'],
                ]);
            }

            return redirect()->back()
                ->with('success', 'New OTP sent to your email address.')
                ->with('expires_at', $result['expires_at'] ?? null);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP resend failed in AuthenticatedSessionController', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['email' => 'Failed to resend OTP. Please try again.']);
        }
    }

    /**
     * Show login help page
     */
    public function showLoginHelp(): Response
    {
        return Inertia::render('auth/LoginHelp');
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
