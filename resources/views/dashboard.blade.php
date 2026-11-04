@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            @if ($companyName)
                <p class="text-uppercase text-success fw-semibold small mb-2">{{ $companyName }}</p>
            @endif
            <h1 class="h3 mb-1">{{ $heading }}</h1>
            <p class="text-secondary mb-0">{{ $introduction }}</p>
        </div>
        @can('create', \App\Models\ShortUrl::class)
            <a href="{{ route('short-urls.create') }}" class="btn btn-success">Create short URL</a>
        @endcan
    </div>

    @if (count($metrics))
        <section aria-label="Summary" class="mb-5">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
                @foreach ($metrics as $metric)
                    <div class="col">
                        <article class="h-100 bg-white border rounded-2 p-4">
                            <p class="text-secondary mb-2">{{ $metric['label'] }}</p>
                            <p class="display-6 fw-semibold mb-0">{{ number_format($metric['value']) }}</p>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($emptyMessage)
        <p class="border-start border-3 border-secondary ps-3 py-2 text-secondary">{{ $emptyMessage }}</p>
    @elseif ($recentUrls->isNotEmpty())
        <section aria-labelledby="recent-urls-heading">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 id="recent-urls-heading" class="h5 mb-0">Recent short URLs</h2>
                @can('viewAny', \App\Models\ShortUrl::class)
                    <a href="{{ route('short-urls.index') }}" class="text-decoration-none">View all</a>
                @endcan
            </div>
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
                                @if (auth()->user()->role->hasPermission('short_urls.view:any') || auth()->user()->role->hasPermission('short_urls.view:company'))
                                    <th scope="col">Created by</th>
                                @endif
                                <th scope="col">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentUrls as $shortUrl)
                                <tr>
                                    <td><a href="{{ route('short-urls.show', $shortUrl) }}">{{ route('short-urls.redirect', $shortUrl->short_code) }}</a></td>
                                    <td class="text-break" style="max-width: 24rem">{{ $shortUrl->original_url }}</td>
                                    @if (auth()->user()->role->hasPermission('short_urls.view:any'))
                                        <td>{{ $shortUrl->company->name }}</td>
                                    @endif
                                    @if (auth()->user()->role->hasPermission('short_urls.view:any') || auth()->user()->role->hasPermission('short_urls.view:company'))
                                        <td>{{ $shortUrl->user->name }}</td>
                                    @endif
                                    <td>{{ $shortUrl->created_at->toDateString() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @else
        <p class="text-secondary">No short URLs yet.</p>
    @endif
@endsection