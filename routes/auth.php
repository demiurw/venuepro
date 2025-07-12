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
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // OTP Authentication Routes
    Route::get('otp', [OtpController::class, 'create'])
        ->name('otp.create');

    Route::post('otp/generate', [OtpController::class, 'generate'])
        ->name('otp.generate')
        ->middleware('throttle:3,1'); // Max 3 attempts per minute

    Route::post('otp/verify', [OtpController::class, 'verify'])
        ->name('otp.verify')
        ->middleware('throttle:5,1'); // Max 5 verification attempts per minute

    Route::post('otp/resend', [OtpController::class, 'resend'])
        ->name('otp.resend')
        ->middleware('throttle:2,1'); // Max 2 resend attempts per minute

    // Account Verification Routes (for new users)
    Route::post('verify-account/generate', [OtpController::class, 'generateForVerification'])
        ->name('verification.generate')
        ->middleware('throttle:3,1');

    Route::post('verify-account/verify', [OtpController::class, 'verifyForActivation'])
        ->name('verification.verify.otp')
        ->middleware('throttle:5,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
