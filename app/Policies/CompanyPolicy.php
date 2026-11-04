<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->role?->hasPermission('companies.view:any')) {
            return true;
        }

        return $user->role?->slug === 'admin' && $user->company_id !== null;
    }

    public function view(User $user, Company $company): bool
    {
        if ($user->role?->hasPermission('companies.view:any')) {
            return true;
        }

        return $user->role?->slug === 'admin' && $user->company_id === $company->id;
    }

    public function create(User $user): bool
    {
        return $user->role?->hasPermission('companies.create') ?? false;
    }

    public function inviteUsers(User $user, Company $company): bool
    {
        return $this->inviteAdmin($user, $company) || $this->inviteMember($user, $company);
    }

    public function inviteAdmin(User $user, Company $company): bool
    {
        if (! $user->role?->hasPermission('users.invite_admin')) {
            return false;
        }

        return $user->role->hasPermission('companies.view:any') || $user->company_id === $company->id;
    }

    public function inviteMember(User $user, Company $company): bool
    {
        return ($user->role?->hasPermission('users.invite_member') ?? false)
            && $user->company_id === $company->id;
    }
}
