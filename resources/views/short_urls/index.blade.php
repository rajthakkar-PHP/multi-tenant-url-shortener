@extends('layouts.app')

@section('title', 'Short URLs')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">Short URLs</h1>
            <p class="text-secondary mb-0">{{ $shortUrls->total() }} total</p>
        </div>
        @can('create', \App\Models\ShortUrl::class)
            <a href="{{ route('short-urls.create') }}" class="btn btn-success">Create short URL</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    <div class="bg-white border rounded-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Short URL</th>
                        <th scope="col">Destination</th>
                        @if (auth()->user()->role->hasPermission('short_urls.view:any'))
                            <th scope="col">Company</th>
                        @endif
                        @if (auth()->user()->role->hasPermission('short_urls.view:company') || auth()->user()->role->hasPermission('short_urls.view:any'))
                            <th scope="col">Created by</th>
                        @endif
                        <th scope="col">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shortUrls as $shortUrl)
                        <tr>
                            <td><a href="{{ route('short-urls.show', $shortUrl) }}">{{ route('short-urls.redirect', $shortUrl->short_code) }}</a></td>
                            <td class="text-break" style="max-width: 24rem">{{ $shortUrl->original_url }}</td>
                            @if (auth()->user()->role->hasPermission('short_urls.view:any'))
                                <td>{{ $shortUrl->company->name }}</td>
                            @endif
                            @if (auth()->user()->role->hasPermission('short_urls.view:company') || auth()->user()->role->hasPermission('short_urls.view:any'))
                                <td>{{ $shortUrl->user->name }}</td>
                            @endif
                            <td>{{ $shortUrl->created_at->toDateString() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No short URLs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $shortUrls->links() }}</div>
    </div>
@endsection