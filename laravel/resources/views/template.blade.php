<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FinAI - @yield('title')</title>
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
<button id="theme-toggle" title="Alternar tema">
    <span id="theme-icon">🌙</span>
</button>

<div class="container">
    @yield('content')
</div>
</body>
<script src="{{ asset('js/script.js') }}"></script>
</html>
