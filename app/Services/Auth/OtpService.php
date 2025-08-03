<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\OtpAttempt;
use App\Mail\OtpMail;
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
            Log::info('OTP generation started', [
                'user_id' => $user->id,
                'email' => $user->email,
                'purpose' => $purpose
            ]);

            // Check rate limiting
            if ($this->isRateLimited($user)) {
                Log::warning('OTP generation rate limited', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);

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

            Log::info('OTP code generated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_code' => $otpCode, // REMOVE THIS IN PRODUCTION!
                'purpose' => $purpose
            ]);

            // Create OTP attempt record
            $otpAttempt = OtpAttempt::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'email' => $user->email, // Store email for consistency
                'purpose' => $purpose, // Add purpose field
                'otp_code' => $otpCode,
                'expires_at' => now()->addMinutes($this->otpExpireMinutes),
                'is_used' => false,
                'attempt_count' => 0,
            ]);

            Log::info('OTP attempt record created', [
                'user_id' => $user->id,
                'otp_attempt_id' => $otpAttempt->id,
                'expires_at' => $otpAttempt->expires_at
            ]);

            // Send OTP via email
            $this->sendOtpEmail($user, $otpCode, $purpose);

            Log::info('OTP generation completed successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'purpose' => $purpose,
                'expires_at' => $otpAttempt->expires_at
            ]);

            return [
                'success' => true,
                'message' => 'OTP sent to your email address.',
                'expires_at' => $otpAttempt->expires_at,
                'otp_id' => $otpAttempt->id,
                'debug_otp' => config('app.debug') ? $otpCode : null // Only in debug mode
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate OTP', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            Log::info('OTP verification started', [
                'user_id' => $user->id,
                'email' => $user->email,
                'provided_otp' => $otpCode
            ]);

            // Find the most recent active OTP attempt
            $otpAttempt = $user->otpAttempts()
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpAttempt) {
                Log::warning('No active OTP found for verification', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'provided_otp' => $otpCode
                ]);

                // Check if there's an expired OTP to give better error message
                $expiredOtp = $user->otpAttempts()
                    ->where('expires_at', '<=', now())
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($expiredOtp) {
                    return [
                        'success' => false,
                        'message' => 'OTP has expired. Please request a new one.',
                        'expired' => true
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'No valid OTP found. Please request a new one.',
                ];
            }

            // Check if OTP code matches
            if ($otpAttempt->otp_code !== $otpCode) {
                Log::warning('OTP code mismatch', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'expected_otp' => $otpAttempt->otp_code,
                    'provided_otp' => $otpCode,
                    'attempt_count' => $otpAttempt->attempt_count
                ]);

                $otpAttempt->incrementAttempt();

                if ($otpAttempt->hasExceededMaxAttempts($this->maxAttempts)) {
                    $otpAttempt->markAsUsed(); // Invalidate after max attempts

                    return [
                        'success' => false,
                        'message' => 'Maximum attempts exceeded. Please request a new OTP.',
                        'max_attempts_exceeded' => true
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Invalid OTP code.',
                    'attempts_remaining' => $this->maxAttempts - $otpAttempt->attempt_count
                ];
            }

            // Check if OTP is expired
            if ($otpAttempt->isExpired()) {
                Log::warning('OTP verification attempted with expired code', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'expired_at' => $otpAttempt->expires_at
                ]);

                return [
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.',
                    'expired' => true
                ];
            }

            // OTP verification successful
            $otpAttempt->markAsUsed();

            Log::info('OTP verification successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_attempt_id' => $otpAttempt->id
            ]);

            return [
                'success' => true,
                'message' => 'OTP verified successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('OTP verification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'provided_otp' => $otpCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'OTP verification failed. Please try again.'
            ];
        }
    }

    public function generateOtpForEmail(string $email, string $purpose = 'registration'): array
    {
        try {
            Log::info('Email-based OTP generation started', [
                'email' => $email,
                'purpose' => $purpose
            ]);

            // Check rate limiting by email
            if ($this->isEmailRateLimited($email)) {
                Log::warning('Email-based OTP generation rate limited', [
                    'email' => $email
                ]);

                return [
                    'success' => false,
                    'message' => 'Please wait before requesting another OTP.',
                    'wait_time' => $this->getEmailRemainingWaitTime($email)
                ];
            }

            // For registration, we don't validate against existing users
            // Just check if email is already registered
            if ($purpose === 'registration') {
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    return [
                        'success' => false,
                        'message' => 'Email address is already registered.'
                    ];
                }
            }

            // Invalidate existing OTPs for this email
            $this->invalidateExistingOtpsByEmail($email);

            // Generate new OTP code
            $otpCode = $this->generateOtpCode();

            Log::info('OTP code generated for email', [
                'email' => $email,
                'otp_code' => $otpCode, // REMOVE THIS IN PRODUCTION!
                'purpose' => $purpose
            ]);

            // Create OTP attempt record without user_id for registration
            $otpAttempt = OtpAttempt::create([
                'company_id' => null, // Will be set when user is created
                'user_id' => null, // Will be set when user is created
                'email' => $email, // Store email for registration OTPs
                'otp_code' => $otpCode,
                'expires_at' => now()->addMinutes($this->otpExpireMinutes),
                'is_used' => false,
                'attempt_count' => 0,
                'purpose' => $purpose,
            ]);

            Log::info('OTP attempt record created for email', [
                'email' => $email,
                'otp_attempt_id' => $otpAttempt->id,
                'expires_at' => $otpAttempt->expires_at
            ]);

            // Send OTP via email (create temporary user object for email sending)
            $tempUser = new User([
                'email' => $email,
                'first_name' => 'User', // Generic name for registration emails
                'last_name' => '',
            ]);
            $this->sendOtpEmail($tempUser, $otpCode, $purpose);

            Log::info('Email-based OTP generation completed successfully', [
                'email' => $email,
                'purpose' => $purpose,
                'expires_at' => $otpAttempt->expires_at
            ]);

            return [
                'success' => true,
                'message' => 'OTP sent to your email address.',
                'expires_at' => $otpAttempt->expires_at,
                'otp_id' => $otpAttempt->id,
                'debug_otp' => config('app.debug') ? $otpCode : null // Only in debug mode
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate email-based OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }
    }

    public function verifyOtpForEmail(string $email, string $otpCode): array
    {
        try {
            Log::info('Email-based OTP verification started', [
                'email' => $email,
                'provided_otp' => $otpCode
            ]);

            // Find the most recent active OTP attempt for this email
            $otpAttempt = OtpAttempt::where('email', $email)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpAttempt) {
                Log::warning('No active email-based OTP found for verification', [
                    'email' => $email,
                    'provided_otp' => $otpCode
                ]);

                // Check if there's an expired OTP to give better error message
                $expiredOtp = OtpAttempt::where('email', $email)
                    ->where('expires_at', '<=', now())
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($expiredOtp) {
                    return [
                        'success' => false,
                        'message' => 'OTP has expired. Please request a new one.',
                        'expired' => true
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'No valid OTP found. Please request a new one.',
                ];
            }

            // Check if OTP code matches
            if ($otpAttempt->otp_code !== $otpCode) {
                Log::warning('Email-based OTP code mismatch', [
                    'email' => $email,
                    'expected_otp' => $otpAttempt->otp_code,
                    'provided_otp' => $otpCode,
                    'attempt_count' => $otpAttempt->attempt_count
                ]);

                $otpAttempt->incrementAttempt();

                if ($otpAttempt->hasExceededMaxAttempts($this->maxAttempts)) {
                    $otpAttempt->markAsUsed(); // Invalidate after max attempts

                    return [
                        'success' => false,
                        'message' => 'Maximum attempts exceeded. Please request a new OTP.',
                        'max_attempts_exceeded' => true
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Invalid OTP code.',
                    'attempts_remaining' => $this->maxAttempts - $otpAttempt->attempt_count
                ];
            }

            // Check if OTP is expired
            if ($otpAttempt->isExpired()) {
                Log::warning('Email-based OTP verification attempted with expired code', [
                    'email' => $email,
                    'expired_at' => $otpAttempt->expires_at
                ]);

                return [
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.',
                    'expired' => true
                ];
            }

            // OTP verification successful - mark as used
            $otpAttempt->markAsUsed();

            Log::info('Email-based OTP verification successful', [
                'email' => $email,
                'otp_attempt_id' => $otpAttempt->id
            ]);

            return [
                'success' => true,
                'message' => 'OTP verified successfully.',
                'otp_attempt_id' => $otpAttempt->id,
            ];

        } catch (\Exception $e) {
            Log::error('Email-based OTP verification failed', [
                'email' => $email,
                'provided_otp' => $otpCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'OTP verification failed. Please try again.'
            ];
        }
    }

    public function verifyUserAndGenerateOtp(string $email, string $purpose = 'login'): array
    {
        try {
            Log::info('User verification and OTP generation started', [
                'email' => $email,
                'purpose' => $purpose
            ]);

            $user = User::where('email', $email)->first();

            if (!$user) {
                Log::warning('OTP generation attempted for non-existent user', [
                    'email' => $email
                ]);

                return [
                    'success' => false,
                    'message' => 'No account found with this email address.'
                ];
            }

            // Check if user account is active (allow pending users for account verification)
            if ($user->status !== 'active' && !($user->status === 'pending' && $purpose === 'account_verification')) {
                Log::warning('OTP generation attempted for inactive user', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'status' => $user->status,
                    'purpose' => $purpose
                ]);

                return [
                    'success' => false,
                    'message' => 'Your account is not active. Please contact support.',
                    'account_inactive' => true
                ];
            }

            // Generate and send OTP
            return $this->generateOtp($user, $purpose);

        } catch (\Exception $e) {
            Log::error('Failed to process OTP request', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
        $updated = $user->otpAttempts()
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true]);

        Log::info('Invalidated existing OTPs', [
            'user_id' => $user->id,
            'count' => $updated
        ]);
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

    protected function isEmailRateLimited(string $email): bool
    {
        $recentOtp = OtpAttempt::where('email', $email)
            ->where('created_at', '>', now()->subMinutes($this->rateLimitMinutes))
            ->first();

        return $recentOtp !== null;
    }

    protected function getEmailRemainingWaitTime(string $email): int
    {
        $recentOtp = OtpAttempt::where('email', $email)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$recentOtp) {
            return 0;
        }

        $waitUntil = $recentOtp->created_at->addMinutes($this->rateLimitMinutes);

        return max(0, $waitUntil->diffInSeconds(now()));
    }

    protected function invalidateExistingOtpsByEmail(string $email): void
    {
        $updated = OtpAttempt::where('email', $email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true]);

        Log::info('Invalidated existing OTPs for email', [
            'email' => $email,
            'count' => $updated
        ]);
    }

    protected function sendOtpEmail(User $user, string $otpCode, string $purpose): void
    {
        try {
            Log::info('Attempting to send OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'purpose' => $purpose,
                'mail_driver' => config('mail.default'),
                'otp_code' => config('app.debug') ? $otpCode : '***hidden***'
            ]);

            // Check if we're using log driver and log the OTP directly
            if (config('mail.default') === 'log') {
                Log::channel('mail')->info('OTP EMAIL WOULD BE SENT', [
                    'to' => $user->email,
                    'subject' => "Your {$purpose} code - VenuePro",
                    'otp_code' => $otpCode,
                    'user_name' => $user->first_name,
                    'purpose' => $purpose,
                    'expires_in' => $this->otpExpireMinutes . ' minutes'
                ]);
            }

            // Send the actual email
            Mail::to($user->email)->send(new OtpMail($user, $otpCode, $purpose));

            Log::info('OTP email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'purpose' => $purpose
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception so the calling method knows it failed
            throw $e;
        }
    }

    public function cleanupExpiredOtps(): int
    {
        $count = OtpAttempt::where('expires_at', '<', now())
            ->where('is_used', false)
            ->update(['is_used' => true]);

        Log::info('Cleaned up expired OTPs', ['count' => $count]);

        return $count;
    }

    /**
     * Debug method to get the latest OTP for a user (development only)
     */
    public function getLatestOtpForUser(User $user): ?string
    {
        if (!config('app.debug')) {
            return null;
        }

        $otpAttempt = $user->otpAttempts()
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        return $otpAttempt ? $otpAttempt->otp_code : null;
    }
}
