<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta charset="UTF-8" />
    <link rel="icon" href="/logo.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ mix('css/App.css') }}">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<link
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
  rel="stylesheet"
/>


    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/firasans/firasans.css') }}">
   
    <script src="src/theme-solarized_dark.js" type="text/javascript" charset="utf-8"></script>

    <title>{{ "Unique English" }}</title>
</head>

<body class="bg-gray-100 overflow-hidden dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div id="app">
        <theme-switcher></theme-switcher>
        @yield('content')
    </div>
</body>

<body class="font-Kanit">
    <div id="app"></div>
    <script type="module" src="{{ mix('js/App.js') }}"></script>
</body>

</html>