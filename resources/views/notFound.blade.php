<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Page Not Found</title>
    <link rel="stylesheet" href="{{ asset('assets/firasans/firasans.css') }}">
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

        .logo {
            width: 100px;
            margin-bottom: 1rem;
        }

        h1 {
            color: #dc3545;
            margin-bottom: 1rem;
        }

        p {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease-in-out;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="logo">
        <h1>404 - Page Not Found</h1>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        <a href="/" class="btn">Back to Home</a>
    </div>
</body>

</html>