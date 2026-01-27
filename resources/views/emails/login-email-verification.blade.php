<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Email Verification - Unique English</title>
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
            background-color: #3b82f6;
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
            background-color: #eff6ff;
            border: 2px dashed #3b82f6;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #3b82f6;
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
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .login-info {
            background-color: #f0f9ff;
            border: 1px solid #0ea5e9;
            color: #0c4a6e;
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
            <p>Email Verification Required</p>
        </div>

        <div class="content">
            <h2>Hello {{ $userName }},</h2>

            <p>You attempted to log in to your <strong>Unique English</strong> account using your email address. To ensure account security, we need to verify your email address before you can proceed.</p>

            <div class="login-info">
                <strong>Login Attempt Details:</strong>
                <ul style="margin: 10px 0;">
                    <li>Email: {{ $userEmail }}</li>
                    <li>Time: {{ date('Y-m-d H:i:s') }}</li>
                    <li>Action: Email Login Verification</li>
                </ul>
            </div>

            <div class="otp-box">
                <p style="margin: 0; font-size: 16px; color: #666;">Your login verification code is:</p>
                <div class="otp-code">{{ $otp }}</div>
                <p style="margin: 0; font-size: 14px; color: #666;">This code will expire in 10 minutes</p>
            </div>

            <p>Please enter this code in the verification form to complete your login process. Once verified, you'll be automatically logged in to your account.</p>

            <div class="warning">
                <strong>Security Notice:</strong> If you didn't attempt to log in, please ignore this email and consider changing your password for security.
            </div>

            <div class="security-notice">
                <strong>Important Guidelines:</strong>
                <ul style="margin: 10px 0;">
                    <li>This verification code is valid for 10 minutes only</li>
                    <li>Never share this code with anyone</li>
                    <li>Only use this code if you just attempted to log in</li>
                    <li>Contact support if you suspect unauthorized access</li>
                </ul>
            </div>

            <p>After successful verification, your email will be marked as verified and future logins will be seamless.</p>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

            <p>Best regards,<br>
                <strong>Unique English</strong>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Unique English. All rights reserved.</p>
            <p>This is an automated security email, please do not reply to this message.</p>
            @if(isset($supportUrl) && is_string($supportUrl) && !empty($supportUrl))
            <p><a href="{{ $supportUrl }}" style="color: #3b82f6;">Contact Support</a></p>
            @endif
        </div>
    </div>
</body>

</html>