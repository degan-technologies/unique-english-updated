<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - Unique English</title>
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
            background-color: #65a30d;
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
            background-color: #f8f9fa;
            border: 2px dashed #65a30d;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #65a30d;
            letter-spacing: 8px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
        }

        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
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

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #65a30d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Unique English</h1>
            <p>Email Verification</p>
        </div>

        <div class="content">
            <h2>Hello {{ $userName }},</h2>

            <p>Thank you for registering with <strong>Unique English</strong>! To complete your registration, please verify your email address using the OTP code below.</p>

            <div class="otp-box">
                <p style="margin: 0; font-size: 16px; color: #666;">Your verification code is:</p>
                <div class="otp-code">{{ $otp }}</div>
                <p style="margin: 0; font-size: 14px; color: #666;">This code will expire in 10 minutes</p>
            </div>

            <p>Please enter this code in the verification form to complete your registration.</p>

            <div class="warning">
                <strong>Security Notice:</strong>
                <ul style="margin: 5px 0;">
                    <li>This OTP is valid for 10 minutes only</li>
                    <li>Do not share this code with anyone</li>
                    <li>If you didn't request this verification, please ignore this email</li>
                </ul>
            </div>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

            <p>Best regards,<br>
                <strong>Unique English Team</strong>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Unique English. All rights reserved.</p>
            <p>This is an automated email, please do not reply to this message.</p>
            @if(isset($verificationUrl) && is_string($verificationUrl) && !empty($verificationUrl))
            <p><a href="{{ $verificationUrl }}" style="color: #65a30d;">Complete verification online</a></p>
            @endif
        </div>
    </div>
</body>

</html>