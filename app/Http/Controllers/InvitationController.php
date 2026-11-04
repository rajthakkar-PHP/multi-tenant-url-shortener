<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\StoreInvitationRequest;
use App\Models\Company;
use App\Models\Role;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function create(Request $request, Company $company): View
    {
        $this->authorize('inviteUsers', $company);

        $roles = Role::query()
            ->whereIn('slug', ['admin', 'member'])
            ->get()
            ->filter(fn (Role $role): bool => $request->user()->can(
                $role->slug === 'admin' ? 'inviteAdmin' : 'inviteMember',
                $company,
            ))
            ->values();

        return view('invitations.create', compact('company', 'roles'));
    }

    public function store(
        StoreInvitationRequest $request,
        Company $company,
        InvitationService $invitations,
    ): RedirectResponse {
        $data = $request->validated();
        $role = Role::where('slug', $data['role'])->firstOrFail();
        $ability = $role->slug === 'admin' ? 'inviteAdmin' : 'inviteMember';
        $this->authorize($ability, $company);

        $invitation = $invitations->send($request->user(), $company, $role, $data['email']);

        return redirect()
            ->route('companies.show', $company)
            ->with('status', "Invitation sent to {$invitation->email}.");
    }

    public function accept(string $token, InvitationService $invitations): View
    {
        return view('invitations.accept', [
            'invitation' => $invitations->findValid($token),
        ]);
    }

    public function complete(
        AcceptInvitationRequest $request,
        string $token,
        InvitationService $invitations,
    ): RedirectResponse {
        $invitations->accept($token, $request->validated());

        return redirect()
            ->route('login')
            ->with('status', 'Your account is ready. Sign in to continue.');
    }
}
