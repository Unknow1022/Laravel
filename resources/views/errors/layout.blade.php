<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Error') - Almacén Inteligente</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
</head>
<body>
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-color);">
    @yield('content')
</div>
</body>
</html>
