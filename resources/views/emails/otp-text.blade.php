{{ $companyName }} - {{ ucfirst($purpose) }} Verification Code

Hello {{ $user->first_name }},

@if($purpose === 'login')
    You requested to log in to your {{ $companyName }} account. Please use the verification code below:
@elseif($purpose === 'verification')
    Welcome to {{ $companyName }}! Please use the verification code below to activate your account:
@else
    Please use the verification code below to complete your request:
@endif

Your verification code is: {{ $otpCode }}

IMPORTANT: This code will expire in {{ $expiresIn }} minutes for your security.

SECURITY NOTICE:
- Never share this code with anyone
- {{ $companyName }} will never ask for this code via phone or email
- If you didn't request this code, please ignore this email

@if($purpose === 'verification')
    After verification, you'll have full access to your {{ $companyName }} account and can start booking rooms and managing your workspace.
@endif

If you're having trouble with the verification process, please contact our support team.

---
This email was sent from {{ $companyName }}
If you didn't request this verification code, you can safely ignore this email.
