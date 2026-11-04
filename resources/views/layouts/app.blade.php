<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <header class="bg-white border-bottom">
        <nav class="container d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
            <a href="{{ route('dashboard') }}" class="text-decoration-none text-dark fw-semibold">Company URL Shortener</a>
            <div class="d-flex flex-wrap align-items-center gap-3">
                @can('viewAny', \App\Models\Company::class)
                    <a href="{{ route('companies.index') }}" class="text-decoration-none">Companies</a>
                @endcan
                @can('viewAny', \App\Models\User::class)
                    <a href="{{ route('users.index') }}" class="text-decoration-none">Users</a>
                @endcan
                @can('viewAny', \App\Models\ShortUrl::class)
                    <a href="{{ route('short-urls.index') }}" class="text-decoration-none">Short URLs</a>
                @endcan
                @can('create', \App\Models\ShortUrl::class)
                    <a href="{{ route('short-urls.create') }}" class="text-decoration-none">Create short URL</a>
                @endcan
                <span class="small text-secondary">{{ auth()->user()->name }} · {{ auth()->user()->role->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm" type="submit">Sign out</button>
                </form>
            </div>
        </nav>
    </header>
    <main class="container py-5">
        @yield('content')
    </main>
</body>
</html>