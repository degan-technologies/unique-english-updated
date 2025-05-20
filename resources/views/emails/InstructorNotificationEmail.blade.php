<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Student Alert</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f7fa;
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
            padding: 30px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 180px;
            height: auto;
            margin-bottom: 15px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .email-content {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 5px;
            color: #555;
        }
        .announcement {
            font-size: 16px;
            margin-bottom: 25px;
            color: #555;
        }
        .student-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        .student-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #2196F3, #4CAF50);
        }
        .student-name {
            font-size: 20px;
            margin: 0 0 5px 0;
            color: #2d3748;
        }
        .student-email {
            color: #4a5568;
            margin: 0 0 15px 0;
            word-break: break-all;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            background: linear-gradient(to right, #4CAF50, #8BC34A);
            color: white;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-right: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .enrollment-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        .detail-item {
            flex: 1;
            min-width: 120px;
        }
        .detail-label {
            font-size: 12px;
            color: #718096;
            margin-bottom: 3px;
        }
        .detail-value {
            font-weight: 600;
            color: #2d3748;
        }
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 200px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
        }
        .secondary-button {
            display: inline-block;
            padding: 14px 28px;
            background: white;
            color: #2d3748 !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 200px;
            text-align: center;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(33, 150, 243, 0.25);
        }
        .secondary-button:hover {
            background: #f8fafc;
            border-color: #cbd5e0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
        .footer-info {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        .footer-item {
            padding: 0 10px;
            border-right: 1px solid #e2e8f0;
        }
        .footer-item:last-child {
            border-right: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <img src="/images/logo.jpg" alt="Your Academy Logo">
            <h1>New Student Enrolled!</h1>
        </div>

        <!-- Content Section -->
        <div class="email-content">
            <p class="greeting">Hi {{ $instructorName }},</p>
            <p class="announcement">Congratulations! You have a new student in your course <strong>{{ $courseName }}</strong>:</p>
            
            <div class="student-card">
                <h3 class="student-name">{{ $studentName }}</h3>
                <p class="student-email">{{ $studentEmail }}</p>
                
                <div>
                    <span class="badge">New Student</span>
                    <span style="color: #718096; font-size: 14px;">Joined on {{ $enrollmentDate }}</span>
                </div>
                 
            </div> 

            <p style="font-size: 14px; color: #718096; text-align: center;">
                This student has been automatically added to your <a href="$studentListUrl" style="color: #2196F3;">student roster</a>.
            </p>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <p>Need help? Contact our <a href="mailto:{{ $systemAdminEmail }}" style="color: #2196F3;">support team</a></p> 
        </div>
    </div>
</body>
</html>