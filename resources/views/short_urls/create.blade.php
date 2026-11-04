@extends('layouts.app')

@section('title', 'Create short URL')

@section('content')
    <div class="mb-4">
        <a href="{{ route('short-urls.index') }}" class="link-secondary text-decoration-none">Short URLs</a>
        <h1 class="h3 mt-2 mb-1">Create short URL</h1>
    </div>

    <form method="POST" action="{{ route('short-urls.store') }}" class="bg-white border rounded-2 p-4" style="max-width: 680px">
        @csrf
        <div class="mb-4">
            <label for="original_url" class="form-label">Destination URL</label>
            <input id="original_url" name="original_url" type="url" inputmode="url" value="{{ old('original_url') }}" placeholder="https://example.com/page" required maxlength="2048" autofocus class="form-control @error('original_url') is-invalid @enderror">
            @error('original_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Create short URL</button>
            <a href="{{ route('short-urls.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection