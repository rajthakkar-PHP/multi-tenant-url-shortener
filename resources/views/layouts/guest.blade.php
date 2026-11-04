<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') | {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: #f3f6f4; }
        .auth-shell { width: min(100% - 2rem, 420px); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    <main class="auth-shell">
        @yield('content')
    </main>
</body>
</html>