@extends('layouts.app')

@section('title', 'Create company')

@section('content')
    <div class="mb-4">
        <a href="{{ route('companies.index') }}" class="link-secondary text-decoration-none">Companies</a>
        <h1 class="h3 mt-2 mb-1">Create company</h1>
        <p class="text-secondary mb-0">A unique company URL key will be generated from its name.</p>
    </div>

    <form method="POST" action="{{ route('companies.store') }}" class="bg-white border rounded-2 p-4" style="max-width: 640px">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Company name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255" autofocus class="form-control @error('name') is-invalid @enderror">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @error('slug')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Create company</button>
            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection