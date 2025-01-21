<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="/logo.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ mix('css/App.css') }}">
<link
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
  rel="stylesheet"
/>

    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/firasans/firasans.css') }}">
    <script src="src/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="src/mode-vue.js" type="text/json" charset="utf-8"></script>
    <script src="src/mode-xml.js" type="text/json" charset="utf-8"></script>
    <script src="src/mode-css.js" type="text/json" charset="utf-8"></script>
    <script src="src/mode-html.js" type="text/json" charset="utf-8"></script>
    <script src="src/mode-json.js" type="text/json" charset="utf-8"></script>
    <script src="src/mode-text.js" type="text/json" charset="utf-8"></script>
    <script src="https://meet.jit.si/external_api.js"></script>

    <script src="src/mode-javascript.js" type="text/javascript" charset="utf-8"></script>

    <script src="src/theme-solarized_dark.js" type="text/javascript" charset="utf-8"></script>

    <title>{{ "Unique English" }}</title>
</head>

<body  class="bg-gradient-to-b from-front-primary to-front-primary">
    <div id="app"></div>
    <script type="module" src="{{ mix('js/App.js') }}"></script>
</body>

</html>