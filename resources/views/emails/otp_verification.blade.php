<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .email-header {
            background-color: #4CAF50;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .email-header img {
            max-width: 150px;
        }

        .email-content {
            padding: 20px;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            letter-spacing: 5px;
            text-align: center;
            margin: 20px 0;
        }

        .cta-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }

        .footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #777;
        }

        .footer a {
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <img src="https://unique.degantechnologies.com/images/logo.jpg" alt="Company Logo">
            <h1>OTP Verification</h1>
        </div>

        <!-- Body Content Section -->
        <div class="email-content">
            <p>Hello {{ $name }},</p>
            <p>Your OTP is:</p>
            <div class="otp-code">{{ $otp }}</div> >
            <p>If you didn't request this, please ignore this email.</p>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} Unique-English. All rights reserved.</p>
            <p><a href="https://unique-english.com">Go to Our Website</a></p>
        </div>
    </div>
</body>

</html>