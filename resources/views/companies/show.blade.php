@extends('layouts.app')

@section('title', $company->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('companies.index') }}" class="link-secondary text-decoration-none">Companies</a>
        <h1 class="h3 mt-2 mb-1">{{ $company->name }}</h1>
        <p class="text-secondary mb-0">Company key: <code>{{ $company->slug }}</code></p>
    </div>

    <section aria-labelledby="users-heading">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 id="users-heading" class="h5 mb-0">Users</h2>
            <span class="text-secondary small">{{ $users->total() }} total</span>
        </div>
        <div class="bg-white border rounded-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary py-4">No users are assigned to this company yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $users->links() }}</div>
        </div>
    </section>

    <section aria-labelledby="invitations-heading" class="mt-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 id="invitations-heading" class="h5 mb-0">Recent invitations</h2>
            @can('inviteUsers', $company)
                <a href="{{ route('companies.invitations.create', $company) }}" class="btn btn-sm btn-outline-success">Invite user</a>
            @endcan
        </div>
        <div class="bg-white border rounded-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Expires</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invitations as $invitation)
                            <tr>
                                <td>{{ $invitation->email }}</td>
                                <td>{{ $invitation->role->name }}</td>
                                <td>{{ $invitation->accepted_at ? 'Accepted' : ($invitation->expires_at->isPast() ? 'Expired' : 'Pending') }}</td>
                                <td>{{ $invitation->expires_at->toDateString() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">No invitations yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection