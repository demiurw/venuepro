<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Registration Routes
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::post('register/send-otp', [RegisteredUserController::class, 'sendRegistrationOtp'])
        ->name('register.send-otp')
        ->middleware('throttle:3,1');

    Route::post('register/resend-verification', [RegisteredUserController::class, 'resendVerification'])
        ->name('register.resend-verification')
        ->middleware('throttle:2,1');

    // Login Routes - OTP-based authentication
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // For OTP-only system, POST /login now handles the email submission step
    Route::post('login', [AuthenticatedSessionController::class, 'requestOtp'])
        ->name('login.submit')
        ->middleware('throttle:10,1');

    // OTP verification endpoint
    Route::post('login/verify', [AuthenticatedSessionController::class, 'store'])
        ->name('login.verify')
        ->middleware('throttle:5,1');

    Route::post('login/resend-otp', [AuthenticatedSessionController::class, 'resendOtp'])
        ->name('login.resend-otp')
        ->middleware('throttle:2,1');

    Route::get('login/help', [AuthenticatedSessionController::class, 'showLoginHelp'])
        ->name('login.help');

    // OTP Authentication Routes (Separate interface if needed)
    Route::get('otp', [OtpController::class, 'create'])
        ->name('otp.create');

    Route::post('otp/generate', [OtpController::class, 'generate'])
        ->name('otp.generate')
        ->middleware('throttle:3,1');

    Route::post('otp/verify', [OtpController::class, 'verify'])
        ->name('otp.verify')
        ->middleware('throttle:5,1');

    Route::post('otp/resend', [OtpController::class, 'resend'])
        ->name('otp.resend')
        ->middleware('throttle:2,1');

    // Account Verification Routes (for new user registration)
    Route::get('verification', [OtpVerificationController::class, 'show'])
        ->name('verification.show');

    Route::post('verification/generate', [OtpVerificationController::class, 'generate'])
        ->name('verification.generate')
        ->middleware('throttle:3,1');

    Route::post('verification/verify', [OtpVerificationController::class, 'verify'])
        ->name('verification.verify')
        ->middleware('throttle:5,1');

    Route::post('verification/resend', [OtpVerificationController::class, 'resend'])
        ->name('verification.resend')
        ->middleware('throttle:2,1');

    // Legacy routes for backward compatibility
    Route::post('verify-account/generate', [OtpController::class, 'generateForVerification'])
        ->name('verification.generate.legacy')
        ->middleware('throttle:3,1');

    Route::post('verify-account/verify', [OtpController::class, 'verifyForActivation'])
        ->name('verification.verify.otp')
        ->middleware('throttle:5,1');

    // Legacy password reset routes (might be removed in fully OTP-only system)
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // OAuth Social Login Routes
    Route::get('auth/{provider}', [SocialiteController::class, 'redirect'])
        ->where('provider', 'google|microsoft')
        ->name('auth.social.redirect');

    Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->where('provider', 'google|microsoft')
        ->name('auth.social.callback');
});

Route::middleware('auth')->group(function () {
    // Account verification notice
    Route::get('verification/notice', [OtpVerificationController::class, 'notice'])
        ->name('verification.notice');

    // User status management (admin-only)
    Route::post('verification/activate', [OtpVerificationController::class, 'activate'])
        ->name('verification.activate')
        ->middleware('can:manage-users');

    Route::post('verification/deactivate', [OtpVerificationController::class, 'deactivate'])
        ->name('verification.deactivate')
        ->middleware('can:manage-users');

    Route::get('verification/status', [OtpVerificationController::class, 'status'])
        ->name('verification.status');

    // Legacy email verification routes (still needed for new accounts)
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice.legacy');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify.email');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Password confirmation (might not be needed in OTP-only system)
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Logout route
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Optional: OTP re-verification for sensitive operations
    Route::middleware('verified')->group(function () {
        // Routes that require verified email status

        // OTP re-verification for sensitive operations (optional feature)
        Route::post('otp/re-verify', [OtpController::class, 'reVerifyForSensitiveAction'])
            ->name('otp.re-verify')
            ->middleware('throttle:3,1');
    });
});

/*
|--------------------------------------------------------------------------
| Additional Route Comments for OTP-only Authentication
|--------------------------------------------------------------------------
|
| Authentication Flow Overview:
| 1. User goes to /login -> redirects to /otp (request step)
| 2. User enters email -> POST /otp/generate -> sends OTP
| 3. User enters OTP -> POST /otp/verify -> authenticates and logs in
|
| Alternative Flow (direct login form):
| 1. User goes to /login -> shows OTP login form
| 2. User enters email -> POST /login/request-otp -> sends OTP
| 3. User enters OTP -> POST /login -> authenticates and logs in
|
| Registration Flow:
| 1. User registers -> account created with pending status
| 2. OTP sent for account verification
| 3. User verifies OTP -> account activated and logged in
|
| Rate Limiting Strategy:
| - OTP generation: 3 per minute (prevent spam)
| - OTP verification: 5 per minute (allow for typos)
| - OTP resend: 2 per minute (prevent abuse)
| - Password reset: 6 per minute (legacy, might remove)
|
| Security Considerations:
| - All OTP routes are rate-limited
| - OTP codes expire after 10 minutes
| - Failed attempts are logged
| - Session regeneration on successful auth
| - CSRF protection on all POST routes
*/
