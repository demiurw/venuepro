<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\OtpAttempt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OtpService
{
    protected $otpLength = 6;
    protected $otpExpireMinutes = 10;
    protected $maxAttempts = 3;
    protected $rateLimitMinutes = 2; // Wait time between OTP requests

    public function generateOtp(User $user, string $purpose = 'login'): array
    {
        try {
            // Check rate limiting
            if ($this->isRateLimited($user)) {
                return [
                    'success' => false,
                    'message' => 'Please wait before requesting another OTP.',
                    'wait_time' => $this->getRemainingWaitTime($user)
                ];
            }

            // Invalidate existing active OTPs for this user
            $this->invalidateExistingOtps($user);

            // Generate new OTP code
            $otpCode = $this->generateOtpCode();

            // Create OTP attempt record
            $otpAttempt = OtpAttempt::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'expires_at' => now()->addMinutes($this->otpExpireMinutes),
                'is_used' => false,
                'attempt_count' => 0,
            ]);

            // Send OTP via email
            $this->sendOtpEmail($user, $otpCode, $purpose);

            Log::info('OTP generated for user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'purpose' => $purpose,
                'expires_at' => $otpAttempt->expires_at
            ]);

            return [
                'success' => true,
                'message' => 'OTP sent to your email address.',
                'expires_at' => $otpAttempt->expires_at,
                'otp_id' => $otpAttempt->id
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate OTP', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }
    }

    public function verifyOtp(User $user, string $otpCode): array
    {
        try {
            // Find the most recent active OTP attempt
            $otpAttempt = $user->activeOtpAttempts()
                ->where('otp_code', $otpCode)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpAttempt) {
                // Check if there's an active OTP to increment attempts
                $activeOtp = $user->activeOtpAttempts()
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($activeOtp) {
                    $activeOtp->incrementAttempt();

                    if ($activeOtp->hasExceededMaxAttempts($this->maxAttempts)) {
                        $activeOtp->markAsUsed(); // Invalidate after max attempts

                        return [
                            'success' => false,
                            'message' => 'Maximum attempts exceeded. Please request a new OTP.',
                            'max_attempts_exceeded' => true
                        ];
                    }
                }

                return [
                    'success' => false,
                    'message' => 'Invalid OTP code.',
                    'attempts_remaining' => $this->maxAttempts - ($activeOtp->attempt_count ?? 0)
                ];
            }

            // Check if OTP is expired
            if ($otpAttempt->isExpired()) {
                return [
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.',
                    'expired' => true
                ];
            }

            // Check if max attempts exceeded
            if ($otpAttempt->hasExceededMaxAttempts($this->maxAttempts)) {
                $otpAttempt->markAsUsed();

                return [
                    'success' => false,
                    'message' => 'Maximum attempts exceeded. Please request a new OTP.',
                    'max_attempts_exceeded' => true
                ];
            }

            // Mark OTP as used
            $otpAttempt->markAsUsed();

            // Update user's last login time
            $user->update(['last_login_at' => now()]);

            Log::info('OTP verified successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_attempt_id' => $otpAttempt->id
            ]);

            return [
                'success' => true,
                'message' => 'OTP verified successfully.',
                'user' => $user
            ];

        } catch (\Exception $e) {
            Log::error('Failed to verify OTP', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again.'
            ];
        }
    }

    public function verifyUserAndGenerateOtp(string $email, string $purpose = 'login'): array
    {
        try {
            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'No account found with this email address.',
                    'user_not_found' => true
                ];
            }

            // Check if user can receive OTP
            if (!$user->isOtpUser()) {
                return [
                    'success' => false,
                    'message' => 'This account is not configured for OTP authentication.',
                    'invalid_auth_method' => true
                ];
            }

            if ($user->status === 'inactive') {
                return [
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact support.',
                    'account_inactive' => true
                ];
            }

            // Generate and send OTP
            return $this->generateOtp($user, $purpose);

        } catch (\Exception $e) {
            Log::error('Failed to process OTP request', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process your request. Please try again.'
            ];
        }
    }

    protected function generateOtpCode(): string
    {
        return str_pad(random_int(0, pow(10, $this->otpLength) - 1), $this->otpLength, '0', STR_PAD_LEFT);
    }

    protected function invalidateExistingOtps(User $user): void
    {
        $user->otpAttempts()
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true]);
    }

    protected function isRateLimited(User $user): bool
    {
        $recentOtp = $user->otpAttempts()
            ->where('created_at', '>', now()->subMinutes($this->rateLimitMinutes))
            ->first();

        return $recentOtp !== null;
    }

    protected function getRemainingWaitTime(User $user): int
    {
        $recentOtp = $user->otpAttempts()
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$recentOtp) {
            return 0;
        }

        $waitUntil = $recentOtp->created_at->addMinutes($this->rateLimitMinutes);

        return max(0, $waitUntil->diffInSeconds(now()));
    }

    protected function sendOtpEmail(User $user, string $otpCode, string $purpose): void
    {
        // You'll need to create this Mailable class
        Mail::to($user->email)->send(new \App\Mail\OtpMail($user, $otpCode, $purpose));
    }

    public function cleanupExpiredOtps(): int
    {
        return OtpAttempt::where('expires_at', '<', now())
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }
}
