<!DOCTYPE html>
<html lang="en">

<head> 
    <meta charset="UTF-8" />
    <link rel="icon" href="/logo.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ mix('css/App.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

<body class="font-Kanit">
    <div id="app"></div>
    <script type="module" src="{{ mix('js/App.js') }}"></script>
</body>

</html>