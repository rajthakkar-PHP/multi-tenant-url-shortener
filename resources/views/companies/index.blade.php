@extends('layouts.app')

@section('title', 'Companies')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">Companies</h1>
            <p class="text-secondary mb-0">Company accounts and membership.</p>
        </div>
        @can('create', \App\Models\Company::class)
            <a href="{{ route('companies.create') }}" class="btn btn-success">Create company</a>
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
                        <th scope="col">Company</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Users</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>{{ $company->name }}</td>
                            <td><code>{{ $company->slug }}</code></td>
                            <td>{{ $company->users_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">No companies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $companies->links() }}</div>
    </div>
@endsection