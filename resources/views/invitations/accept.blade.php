@extends('layouts.guest')

@section('title', 'Accept invitation')

@section('content')
    <div class="mb-4">
        <p class="text-uppercase text-success fw-semibold small mb-2">Company URL Shortener</p>
        <h1 class="h3 mb-1">Accept your invitation</h1>
        <p class="text-secondary mb-0">Join {{ $invitation->company->name }} as {{ $invitation->role->name }}.</p>
    </div>

    <form method="POST" action="{{ route('invitations.complete', $invitationToken = request()->route('token')) }}" class="bg-white border rounded-2 p-4">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Your name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autofocus class="form-control @error('name') is-invalid @enderror">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Invited email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $invitation->email) }}" autocomplete="email" required maxlength="255" class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Create password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required minlength="12" class="form-control @error('password') is-invalid @enderror">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="12" class="form-control">
        </div>

        <button type="submit" class="btn btn-success w-100">Create account</button>
    </form>
@endsection