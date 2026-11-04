@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase text-success fw-semibold small mb-2">Company URL Shortener</p>
        <h1 class="h3 mb-1">Sign in</h1>
        <p class="text-secondary mb-0">Use your company account to continue.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="bg-white border rounded-2 p-4">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-4">
            <input id="remember" name="remember" type="checkbox" value="1" class="form-check-input">
            <label for="remember" class="form-check-label">Remember me</label>
        </div>

        <button type="submit" class="btn btn-success w-100">Sign in</button>
    </form>
@endsection