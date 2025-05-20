<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Enrollment Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f7fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 180px;
            height: auto;
            margin-bottom: 15px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .email-content {
            padding: 35px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 5px;
            color: #555555;
        }
        .intro-text {
            font-size: 16px;
            margin-bottom: 25px;
            color: #555555;
        }
        .highlight-box {
            background: #f0f7ff;
            border-left: 4px solid #4CAF50;
            border-radius: 0 8px 8px 0;
            padding: 25px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }
        .highlight-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #4CAF50, #2196F3);
        }
        .access-title {
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 20px;
            color: #2d3748;
        }
        .access-detail {
            margin: 15px 0;
            display: flex;
            align-items: center;
        }
        .detail-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 100px;
        }
        .detail-value {
            color: #2d3748;
        }
        .dashboard-link {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.2);
        }
        .dashboard-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(76, 175, 80, 0.25);
        }
        .support-text {
            font-size: 15px;
            color: #555555;
            margin: 30px 0 15px;
        }
        .support-link {
            color: #2196F3;
            text-decoration: none;
            font-weight: 600;
        }
        .footer {
            background: #f8f9fa;
            padding: 25px;
            text-align: center;
            font-size: 14px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        .copyright {
            margin-bottom: 10px;
        }
        .website-link {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
           <img src="/images/logo.jpg" alt="Your Academy Logo">
            <h1>Welcome to Your Course!</h1>
        </div>

        <!-- Content Section -->
        <div class="email-content">
            <p class="greeting">Hello {{ $studentName }},</p>
            <p class="intro-text">Thank you for enrolling in <strong>{{ $courseName }}</strong>! We're excited to have you join our learning community. Here's what happens next:</p>
            
            <div class="highlight-box">
                <h3 class="access-title">Your Course Access</h3>
                
                <div class="access-detail">
                    <span class="detail-label">Start Date:</span>
                    <span class="detail-value">Immediate access</span>
                </div>
                
                <div class="access-detail">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">Active enrollment</span>
                </div>
                
                <div class="access-detail">
                    <span class="detail-label">Dashboard:</span>
                    <span class="detail-value">
                        <a href="https://unique.degantechnologies.com/" class="dashboard-link">Launch Learning Portal</a>
                    </span>
                </div>
            </div>

            <p class="support-text">Need help or have questions? Visit our <a href="https://unique.degantechnologies.com/" class="support-link">support center</a> or simply reply to this email.</p>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <p class="copyright">&copy; <?php echo date("Y"); ?> Unique-English. All rights reserved.</p>
            <p><a href="$websiteUrl" class="https://degantechnologies.com/">Visit our website</a></p>
        </div>
    </div>
</body>
</html>