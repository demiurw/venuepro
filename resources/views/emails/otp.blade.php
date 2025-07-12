<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .otp-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-block;
        }
        .expires {
            background: #f8f9fa;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">{{ $companyName }}</div>
    <h1>{{ ucfirst($purpose) }} Verification Code</h1>
</div>

<p>Hello {{ $user->first_name }},</p>

@if($purpose === 'login')
    <p>You requested to log in to your {{ $companyName }} account. Please use the verification code below:</p>
@elseif($purpose === 'verification')
    <p>Welcome to {{ $companyName }}! Please use the verification code below to activate your account:</p>
@else
    <p>Please use the verification code below to complete your request:</p>
@endif

<div class="otp-container">
    <p style="margin: 0; font-size: 18px;">Your verification code is:</p>
    <div class="otp-code">{{ $otpCode }}</div>
    <p style="margin: 0; opacity: 0.9;">Enter this code to continue</p>
</div>

<div class="expires">
    <strong>⏰ Important:</strong> This code will expire in {{ $expiresIn }} minutes for your security.
</div>

<div class="security-notice">
    <strong>🔒 Security Notice:</strong>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li>Never share this code with anyone</li>
        <li>{{ $companyName }} will never ask for this code via phone or email</li>
        <li>If you didn't request this code, please ignore this email</li>
    </ul>
</div>

@if($purpose === 'verification')
    <p>After verification, you'll have full access to your {{ $companyName }} account and can start booking rooms and managing your workspace.</p>
@endif

<p>If you're having trouble with the verification process, please contact our support team.</p>

<div class="footer">
    <p>This email was sent from {{ $companyName }}</p>
    <p>If you didn't request this verification code, you can safely ignore this email.</p>
</div>
</body>
</html>
