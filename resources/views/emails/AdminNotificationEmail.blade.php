<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Course Enrollment</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
        }

        .email-content {
            padding: 30px;
        }

        .intro-text {
            font-size: 16px;
            margin-bottom: 25px;
            color: #555555;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 25px 0;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .data-table tr:not(:last-child) td {
            border-bottom: 1px solid #f0f0f0;
        }

        .data-table td {
            padding: 16px 20px;
            vertical-align: top;
        }

        .data-table td:first-child {
            font-weight: 600;
            color: #555555;
            width: 30%;
        }

        .data-table td:last-child {
            color: #222222;
        }

        .button-container {
            text-align: center;
            margin: 30px 0 20px;
        }

        .cta-button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(33, 150, 243, 0.25);
        }

        .footer {
            background: #f5f7fa;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #777777;
            border-top: 1px solid #eeeeee;
        }

        .highlight {
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }

        .sync-info {
            font-size: 12px;
            color: #999999;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <img src="https://unique-english.com/images/logo.jpg" alt="Company Logo">
            <h1>New Course Enrollment</h1>
        </div>

        <!-- Content Section -->
        <div class="email-content">
            <p class="intro-text">Hello Administrator,</p>
            <p class="intro-text">A new student has enrolled in your platform. Here are the details:</p>

            <table class="data-table">
                <tr>
                    <td><strong>Student:</strong></td>
                    <td>{{ $studentName }}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><a href="mailto:$studentEmail">{{ $studentEmail }}</a></td>
                </tr>
                <tr>
                    <td><strong>Course:</strong></td>
                    <td>{{ $courseName }}<span class="highlight">(ID: $courseId)</span></td>
                </tr>
                <tr>
                    <td><strong>Amount:</strong></td>
                    <td><strong style="color: #4CAF50;">{{ $amount }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>{{ $enrollmentDate }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer Section -->
        <div class="footer">
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p class="sync-info">Last system sync: $syncTime</p>
        </div>
    </div>
</body>

</html>