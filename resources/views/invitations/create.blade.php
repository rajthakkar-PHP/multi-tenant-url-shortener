@extends('layouts.app')

@section('title', 'Invite user')

@section('content')
    <div class="mb-4">
        <a href="{{ route('companies.show', $company) }}" class="link-secondary text-decoration-none">{{ $company->name }}</a>
        <h1 class="h3 mt-2 mb-1">Invite a user</h1>
    </div>

    <form method="POST" action="{{ route('companies.invitations.store', $company) }}" class="bg-white border rounded-2 p-4" style="max-width: 640px">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autofocus class="form-control @error('email') is-invalid @enderror">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="role" class="form-label">Role</label>
            <select id="role" name="role" required class="form-select @error('role') is-invalid @enderror">
                <option value="">Select a role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->slug }}" @selected(old('role') === $role->slug)>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Send invitation</button>
            <a href="{{ route('companies.show', $company) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection