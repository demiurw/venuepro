<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // For OTP-only authentication, we need email and OTP code
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'otp_code' => ['required', 'string', 'size:6'], // 6-digit OTP
            'remember' => ['boolean'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'No account found with this email address.',
            'otp_code.required' => 'Please enter your verification code.',
            'otp_code.size' => 'Verification code must be exactly 6 digits.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials using OTP.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        try {
            // Find the user
            $user = User::where('email', strtolower(trim($this->email)))->first();

            if (!$user) {
                RateLimiter::hit($this->throttleKey());
                $this->throwFailedAuthenticationException();
            }

            // Check if user is active
            if (!$user->isActive()) {
                RateLimiter::hit($this->throttleKey());
                $this->throwUserStatusException($user);
            }

            // Verify user uses OTP authentication
            if (!$user->usesOtpAuth()) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'This account uses a different authentication method.',
                ]);
            }

            // Verify OTP using the OTP service
            $otpService = app(OtpService::class);
            $result = $otpService->verifyOtp($user, $this->otp_code);

            if (!$result['success']) {
                RateLimiter::hit($this->throttleKey());
                $this->throwOtpVerificationException($result);
            }

            // Authentication successful - clear rate limiting
            RateLimiter::clear($this->throttleKey());

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            RateLimiter::hit($this->throttleKey());

            \Log::error('Authentication error in LoginRequest', [
                'email' => $this->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw ValidationException::withMessages([
                'otp_code' => 'Authentication failed. Please try again.',
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }

    /**
     * Throw a failed authentication exception.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwFailedAuthenticationException(): void
    {
        throw ValidationException::withMessages([
            'email' => 'No account found with this email address.',
        ]);
    }

    /**
     * Throw an exception based on user status.
     *
     * @param User $user
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwUserStatusException(User $user): void
    {
        $message = match ($user->status) {
            'pending' => 'Your account is pending verification. Please check your email for activation instructions.',
            'inactive' => 'Your account has been deactivated. Please contact support for assistance.',
            default => 'Your account is not active. Please contact support.',
        };

        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }

    /**
     * Throw an OTP verification exception with appropriate message.
     *
     * @param array $result
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function throwOtpVerificationException(array $result): void
    {
        $message = $result['message'] ?? 'Invalid verification code.';

        $errors = ['otp_code' => $message];

        // Add additional context if available
        if (isset($result['attempts_remaining'])) {
            $attempts = $result['attempts_remaining'];
            if ($attempts > 0) {
                $errors['otp_code'] = $message . " ({$attempts} attempt" . ($attempts !== 1 ? 's' : '') . " remaining)";
            }
        }

        throw ValidationException::withMessages($errors);
    }

    /**
     * Get the email being used for authentication.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return strtolower(trim($this->email));
    }

    /**
     * Get the OTP code being used for authentication.
     *
     * @return string
     */
    public function getOtpCode(): string
    {
        return $this->otp_code;
    }

    /**
     * Check if the user wants to be remembered.
     *
     * @return bool
     */
    public function shouldRemember(): bool
    {
        return $this->boolean('remember');
    }
}
