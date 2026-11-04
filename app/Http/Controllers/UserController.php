<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $viewer = $request->user();
        $canViewAllCompanies = $viewer->role->hasPermission('companies.view:any');

        $usersQuery = User::query()
            ->with(['company', 'role'])
            ->orderBy('name');

        $invitationsQuery = Invitation::query()
            ->with(['company', 'role'])
            ->latest();

        if (! $canViewAllCompanies) {
            $usersQuery->where('company_id', $viewer->company_id);
            $invitationsQuery->where('company_id', $viewer->company_id);
        }

        return view('users.index', [
            'users' => $usersQuery->paginate(15, ['*'], 'users_page'),
            'invitations' => $invitationsQuery->paginate(15, ['*'], 'invitations_page'),
            'canViewAllCompanies' => $canViewAllCompanies,
        ]);
    }
}
