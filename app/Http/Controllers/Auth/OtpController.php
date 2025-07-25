<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\Auth\OtpService;
use App\Models\User;
use App\Http\Middleware\DashboardRedirectMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the OTP request form
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Otp', [
            'email' => $request->old('email'),
            'step' => 'request', // 'request' or 'verify'
            'status' => session('status'),
        ]);
    }

    /**
     * Generate and send OTP to user's email
     */
    public function generate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $result = $this->otpService->verifyUserAndGenerateOtp(
            $request->email,
            'login'
        );

        if ($result['success']) {
            return back()->with([
                'status' => 'success',
                'message' => $result['message'],
                'step' => 'verify',
                'email' => $request->email,
                'expires_at' => $result['expires_at'],
            ]);
        }

        return back()->withErrors([
            'email' => $result['message']
        ])->with([
            'step' => 'request'
        ]);
    }

    /**
     * Verify OTP and authenticate user
     */
    public function verify(OtpRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'otp_code' => 'Invalid user.'
            ]);
        }

        $result = $this->otpService->verifyOtp($user, $request->otp_code);

        if ($result['success']) {
            // Log the user in
            Auth::login($user, $request->boolean('remember'));

            // Regenerate session for security
            $request->session()->regenerate();

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            Log::info('User logged in via OTP', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_type' => $user->user_type
            ]);

            // Redirect based on user status and role
            if ($user->isPending()) {
                return redirect()->route('verification.notice');
            }

            // Use the role-based dashboard redirection
            $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);

            Log::info('Redirecting user to role-based dashboard', [
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'dashboard_route' => $dashboardRoute
            ]);

            return redirect()->intended($dashboardRoute);
        }

        // Handle different error cases
        $errorKey = 'otp_code';
        $errorMessage = $result['message'];

        if (isset($result['max_attempts_exceeded'])) {
            return back()->with([
                'step' => 'request',
                'status' => 'error',
                'message' => $errorMessage
            ]);
        }

        return back()->withErrors([
            $errorKey => $errorMessage
        ])->with([
            'step' => 'verify',
            'email' => $request->email,
            'attempts_remaining' => $result['attempts_remaining'] ?? null
        ]);
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $result = $this->otpService->verifyUserAndGenerateOtp(
            $request->email,
            'login'
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'expires_at' => $result['expires_at'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Generate OTP for user registration/verification
     */
    public function generateForVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->isPending()) {
            return back()->withErrors([
                'email' => 'Invalid verification request.'
            ]);
        }

        $result = $this->otpService->generateOtp($user, 'verification');

        if ($result['success']) {
            return back()->with([
                'status' => 'success',
                'message' => 'Verification code sent to your email.',
                'step' => 'verify',
                'email' => $request->email,
            ]);
        }

        return back()->withErrors([
            'email' => $result['message']
        ]);
    }

    /**
     * Verify OTP for user account activation
     */
    public function verifyForActivation(OtpRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->isPending()) {
            return back()->withErrors([
                'otp_code' => 'Invalid verification request.'
            ]);
        }

        $result = $this->otpService->verifyOtp($user, $request->otp_code);

        if ($result['success']) {
            // Activate the user account
            $user->activate();

            // Log the user in
            Auth::login($user);
            $request->session()->regenerate();

            Log::info('User account activated via OTP', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Use role-based dashboard redirection for newly activated users too
            $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);

            return redirect($dashboardRoute)->with([
                'status' => 'success',
                'message' => 'Your account has been activated successfully!'
            ]);
        }

        return back()->withErrors([
            'otp_code' => $result['message']
        ])->with([
            'step' => 'verify',
            'email' => $request->email,
        ]);
    }
}
