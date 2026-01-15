<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="/logo.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ mix('css/App.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/firasans/firasans.css') }}">

    <script src="src/theme-solarized_dark.js" type="text/javascript" charset="utf-8"></script>

    <title>{{ "Unique English" }}</title>
    <link rel="icon" href="{{ asset('images/logo.jpg') }}" sizes="256x256" type="image/jpg" />

    <style>
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .spinner {
            position: relative;
            width: 4rem;
            height: 4rem;
        }

        .spinner-inner-fast {
            position: absolute;
            inset: 0;
            border: 4px solid #f97316;
            /* lime-500 */
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .spinner-inner-slow {
            position: absolute;
            inset: 0.25rem;
            border: 4px solid #e87725;
            /* lime-300 */
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin-slow 2s linear infinite;
        }

        .loading-text {
            font-size: 1.125rem;
            font-weight: 500;
            color: #e5833d;
            /* gray-700 */
            margin-top: 1rem;
            animation: pulse 1.5s infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spin-slow {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="font-Kanit overflow-y-auto scrollbar">
    <div id="app"></div>
    <div id="modals"></div>

    <!-- Loading screen that will be removed when app mounts -->
    <div id="loading-screen">
        <div class="loading-container">
            <div class="spinner">
                <div class="spinner-inner-fast"></div>
                <div class="spinner-inner-slow"></div>
            </div>
            <p class="loading-text">Loading...</p>
        </div>
    </div>

    <script>
        // Show loading screen immediately
        document.getElementById('loading-screen').style.display = 'flex';

        // Add event listener for when the app is mounted
        document.addEventListener('DOMContentLoaded', function() {
            // This assumes your Vue app will emit a custom event when mounted
            document.addEventListener('app-mounted', function() {
                document.getElementById('loading-screen').style.display = 'none';
            });

            // Fallback in case the event isn't fired
            setTimeout(function() {
                document.getElementById('loading-screen').style.display = 'none';
            }, 3000);
        });
    </script>

    <script type="module" src="{{ mix('js/App.js') }}"></script>
</body>

</html>