<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\Auth\OtpService;
use App\Models\User;
use App\Enums\UserStatus;
use App\Http\Middleware\DashboardRedirectMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OtpVerificationController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Show the account verification form
     */
    public function show(Request $request): Response
    {
        return Inertia::render('auth/VerifyAccount', [
            'email' => $request->old('email') ?? $request->get('email'),
            'step' => $request->get('step', 'request'), // 'request' or 'verify'
            'status' => session('status'),
            'message' => session('message'),
        ]);
    }

    /**
     * Generate OTP for account verification
     */
    public function generate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'No account found with this email address.'
            ]);
        }

        // Check if user is pending verification
        if (!$user->isPending()) {
            throw ValidationException::withMessages([
                'email' => 'This account is not pending verification.'
            ]);
        }

        $result = $this->otpService->generateOtp($user, 'account_verification');

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
     * Verify OTP and activate account
     */
    public function verify(OtpRequest $request): RedirectResponse
    {
        $result = $this->otpService->verifyOtpAndActivateUser(
            $request->email,
            $request->otp_code
        );

        if ($result['success']) {
            $user = $result['user'];

            // Log the user in
            Auth::login($user, $request->boolean('remember'));

            // Regenerate session for security
            $request->session()->regenerate();

            Log::info('User account activated and logged in via OTP verification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_type' => $user->user_type
            ]);

            // Get role-based dashboard route
            $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);

            return redirect($dashboardRoute)->with([
                'status' => 'success',
                'message' => 'Your account has been verified and activated successfully!'
            ]);
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
     * Resend verification OTP
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification request.',
            ], 400);
        }

        $result = $this->otpService->generateOtp($user, 'account_verification');

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
     * Show user activation notice
     */
    public function notice(Request $request): Response
    {
        $user = Auth::user();

        // Redirect active users to their dashboard
        if ($user && $user->isActive()) {
            $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);
            return redirect($dashboardRoute);
        }

        // If user is not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login')->with('message', 'Please log in to continue.');
        }

        return Inertia::render('auth/AccountPending', [
            'user' => [
                'email' => $user->email,
                'status' => $user->status->value,
                'status_label' => $user->status->label(),
                'requires_verification' => $user->requiresVerification(),
            ],
            'message' => session('message'),
        ]);
    }

    /**
     * Check verification status (API endpoint)
     */
    public function status(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $user->status->value,
                'status_label' => $user->status->label(),
                'is_active' => $user->isActive(),
                'is_pending' => $user->isPending(),
                'requires_verification' => $user->requiresVerification(),
                'last_login_at' => $user->last_login_at?->toISOString(),
                'email_verified_at' => $user->email_verified_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Activate user account manually (for admin use)
     */
    public function activate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already active.',
            ], 400);
        }

        $activated = $user->activate();

        if ($activated) {
            Log::info('User account activated manually', [
                'user_id' => $user->id,
                'email' => $user->email,
                'admin_user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User account activated successfully.',
                'data' => [
                    'status' => $user->fresh()->status->value,
                    'is_active' => true,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to activate user account.',
        ], 500);
    }

    /**
     * Deactivate user account (for admin use)
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->isInactive()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already inactive.',
            ], 400);
        }

        $deactivated = $user->deactivate();

        if ($deactivated) {
            Log::info('User account deactivated manually', [
                'user_id' => $user->id,
                'email' => $user->email,
                'admin_user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User account deactivated successfully.',
                'data' => [
                    'status' => $user->fresh()->status->value,
                    'is_active' => false,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to deactivate user account.',
        ], 500);
    }
}