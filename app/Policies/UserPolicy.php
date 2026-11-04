<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->role?->hasPermission('companies.view:any')) {
            return true;
        }

        return $user->role?->slug === 'admin' && $user->company_id !== null;
    }
}
