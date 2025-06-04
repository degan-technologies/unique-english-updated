<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Restricted</title>
    <style>
        body {
            font-family: 'Fira Sans', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 600px;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        p {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .logo {
            width: 100px;
            margin-bottom: 1rem;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/firasans/firasans.css') }}">
</head>
<body>
    <div class="container">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="logo">
        <h1>Access Restricted</h1>
        <p>We've detected unauthorized access to developer tools. For security reasons, this action is not permitted on our platform.</p>
        <p>If you believe this is an error, please contact our support team.</p> 
    </div>

    
</body>
</html>