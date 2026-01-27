<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Unique English</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #dc2626;
            color: white;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .content {
            padding: 20px 0;
        }

        .otp-box {
            background-color: #fef2f2;
            border: 2px dashed #dc2626;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #dc2626;
            letter-spacing: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }

        .security-notice {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Unique English</h1>
            <p>Password Reset Request</p>
        </div>

        <div class="content">
            <h2>Hello {{ $userName }},</h2>

            <p>We received a request to reset your password for your <strong>Unique English</strong> account. If you made this request, please use the OTP code below to reset your password.</p>

            <div class="otp-box">
                <p style="margin: 0; font-size: 16px; color: #666;">Your password reset code is:</p>
                <div class="otp-code">{{ $otp }}</div>
                <p style="margin: 0; font-size: 14px; color: #666;">This code will expire in 10 minutes</p>
            </div>

            <p>Please enter this code in the password reset form to continue with resetting your password.</p>

            <div class="warning">
                <strong>Important:</strong> If you didn't request a password reset, please ignore this email and your password will remain unchanged.
            </div>

            <div class="security-notice">
                <strong>Security Guidelines:</strong>
                <ul style="margin: 10px 0;">
                    <li>This OTP is valid for 10 minutes only</li>
                    <li>Never share this code with anyone</li>
                    <li>Use a strong password when resetting</li>
                    <li>Contact support if you suspect unauthorized access</li>
                </ul>
            </div>

            <p>If you continue to have issues or didn't request this password reset, please contact our support team immediately.</p>

            <p>Best regards,<br>
                <strong>Unique English Support Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Unique English. All rights reserved.</p>
            <p>This is an automated email, please do not reply to this message.</p>
            @if(isset($supportUrl) && is_string($supportUrl) && !empty($supportUrl))
            <p><a href="{{ $supportUrl }}" style="color: #dc2626;">Contact Support</a></p>
            @endif
        </div>
    </div>
</body>

</html>