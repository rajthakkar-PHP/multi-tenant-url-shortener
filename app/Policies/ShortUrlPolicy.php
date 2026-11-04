<?php

namespace App\Policies;

use App\Models\ShortUrl;
use App\Models\User;

class ShortUrlPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->role?->hasPermission('short_urls.view:any')) {
            return true;
        }

        return $user->company_id !== null
            && ($user->role?->hasPermission('short_urls.view:company')
                || $user->role?->hasPermission('short_urls.view:own'));
    }

    public function view(User $user, ShortUrl $shortUrl): bool
    {
        if ($user->role?->hasPermission('short_urls.view:any')) {
            return true;
        }

        if ($user->company_id !== $shortUrl->company_id) {
            return false;
        }

        if ($user->role?->hasPermission('short_urls.view:company')) {
            return true;
        }

        return $user->role?->hasPermission('short_urls.view:own')
            && $user->id === $shortUrl->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role?->hasPermission('short_urls.create') ?? false;
    }
}
