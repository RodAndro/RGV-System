<x-mail::message>
# MFA Verification Code

Your verification code is:

<div style="font-size: 32px; font-weight: bold; letter-spacing: 8px; text-align: center; padding: 20px; background: #f0f9ef; border-radius: 8px; margin: 20px 0;">
{{ $code }}
</div>

This code will expire in 10 minutes.

If you did not attempt to log in, please change your password immediately.

<x-mail::button :url="route('home')">
Go to Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
