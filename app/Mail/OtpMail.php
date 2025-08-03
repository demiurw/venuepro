<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $otpCode;
    public $purpose;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otpCode, string $purpose = 'login')
    {
        $this->user = $user;
        $this->otpCode = $otpCode;
        $this->purpose = $purpose;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubject();

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'noreply@venuepro.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.otp',
            text: 'emails.otp-text',
            with: [
                'user' => $this->user,
                'otpCode' => $this->otpCode,
                'purpose' => $this->purpose,
                'expiresIn' => 10, // minutes
                'companyName' => $this->user->company->name ?? 'VenuePro',
            ],
        );
    }

    /**
     * Get the subject line based on purpose.
     */
    protected function getSubject(): string
    {
        return match ($this->purpose) {
            'login' => 'Your Login Code - VenuePro',
            'verification' => 'Verify Your Account - VenuePro',
            'account_verification' => 'Activate Your Account - VenuePro',
            'password_reset' => 'Reset Your Password - VenuePro',
            default => 'Your Verification Code - VenuePro',
        };
    }
}
