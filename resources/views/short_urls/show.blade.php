@extends('layouts.app')

@section('title', 'Short URL')

@section('content')
    <div class="mb-4">
        <a href="{{ route('short-urls.index') }}" class="link-secondary text-decoration-none">Short URLs</a>
        <h1 class="h3 mt-2 mb-1">Short URL details</h1>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <dl class="row bg-white border rounded-2 p-4" style="max-width: 760px">
        <dt class="col-sm-3">Short URL</dt>
        <dd class="col-sm-9"><a href="{{ route('short-urls.redirect', $shortUrl->short_code) }}">{{ route('short-urls.redirect', $shortUrl->short_code) }}</a></dd>
        <dt class="col-sm-3">Destination</dt>
        <dd class="col-sm-9 text-break"><a href="{{ $shortUrl->original_url }}" target="_blank" rel="noopener noreferrer">{{ $shortUrl->original_url }}</a></dd>
        <dt class="col-sm-3">Created</dt>
        <dd class="col-sm-9">{{ $shortUrl->created_at->toDayDateTimeString() }}</dd>
    </dl>
@endsection