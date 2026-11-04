@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">User Management</h1>
        <p class="text-secondary mb-0">
            {{ $canViewAllCompanies ? 'Users and invitations across all companies.' : 'Users and invitations for your company.' }}
        </p>
    </div>

    <section aria-labelledby="users-heading" class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 id="users-heading" class="h5 mb-0">Users</h2>
            <span class="text-secondary small">{{ number_format($users->total()) }} total</span>
        </div>
        <div class="bg-white border rounded-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            @if ($canViewAllCompanies)
                                <th scope="col">Company</th>
                            @endif
                            <th scope="col">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->name }}</td>
                                @if ($canViewAllCompanies)
                                    <td>{{ $user->company?->name ?? 'Platform' }}</td>
                                @endif
                                <td>{{ $user->created_at->toDateString() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canViewAllCompanies ? 5 : 4 }}" class="text-center text-secondary py-4">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $users->links() }}</div>
        </div>
    </section>

    <section aria-labelledby="invitations-heading">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h2 id="invitations-heading" class="h5 mb-0">Invitations</h2>
                <p class="text-secondary small mb-0">Pending and previous invitations</p>
            </div>
        </div>
        <div class="bg-white border rounded-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            @if ($canViewAllCompanies)
                                <th scope="col">Company</th>
                            @endif
                            <th scope="col">Status</th>
                            <th scope="col">Expires</th>
                            <th scope="col">Invite</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invitations as $invitation)
                            @php
                                $canInviteRole = auth()->user()->can(
                                    $invitation->role->slug === 'admin' ? 'inviteAdmin' : 'inviteMember',
                                    $invitation->company,
                                );
                            @endphp
                            <tr>
                                <td>{{ $invitation->email }}</td>
                                <td>{{ $invitation->role->name }}</td>
                                @if ($canViewAllCompanies)
                                    <td>{{ $invitation->company->name }}</td>
                                @endif
                                <td>{{ $invitation->accepted_at ? 'Accepted' : ($invitation->expires_at->isPast() ? 'Expired' : 'Pending') }}</td>
                                <td>{{ $invitation->expires_at->toDateString() }}</td>
                                <td>
                                    @if ($canInviteRole)
                                        <a href="{{ route('companies.invitations.create', $invitation->company) }}" class="btn btn-sm btn-outline-success">Invite</a>
                                    @else
                                        <span class="text-secondary">View only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canViewAllCompanies ? 6 : 5 }}" class="text-center text-secondary py-4">No invitations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $invitations->links() }}</div>
        </div>
    </section>
@endsection