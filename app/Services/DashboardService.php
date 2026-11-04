<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    /** @return array{heading: string, introduction: string, companyName: ?string, metrics: array<int, array{label: string, value: int}>, recentUrls: Collection<int, ShortUrl>, emptyMessage: ?string} */
    public function forUser(User $user): array
    {
        $role = $user->role;
        $empty = new Collection;

        if ($role?->hasPermission('short_urls.view:any')) {
            return [
                'heading' => 'Platform overview',
                'introduction' => 'A cross-company view of the URL shortener.',
                'companyName' => null,
                'metrics' => [
                    ['label' => 'Companies', 'value' => Company::count()],
                    ['label' => 'Company users', 'value' => User::whereNotNull('company_id')->count()],
                    ['label' => 'Short URLs', 'value' => ShortUrl::count()],
                ],
                'recentUrls' => ShortUrl::with(['company', 'user'])->latest()->limit(8)->get(),
                'emptyMessage' => null,
            ];
        }

        if ($role?->hasPermission('short_urls.view:company') && $user->company_id !== null) {
            return [
                'heading' => 'Company overview',
                'introduction' => 'Activity and membership for your company.',
                'companyName' => $user->company->name,
                'metrics' => [
                    ['label' => 'Company users', 'value' => $user->company->users()->count()],
                    ['label' => 'Short URLs', 'value' => $user->company->shortUrls()->count()],
                    ['label' => 'Pending invitations', 'value' => $user->company->invitations()
                        ->whereNull('accepted_at')->where('expires_at', '>', now())->count()],
                ],
                'recentUrls' => $user->company->shortUrls()->with(['company', 'user'])->latest()->limit(8)->get(),
                'emptyMessage' => null,
            ];
        }

        if ($role?->hasPermission('short_urls.view:own') && $user->company_id !== null) {
            return [
                'heading' => 'My URL activity',
                'introduction' => 'Your recently created short URLs.',
                'companyName' => null,
                'metrics' => [
                    ['label' => 'My short URLs', 'value' => $user->shortUrls()->count()],
                ],
                'recentUrls' => $user->shortUrls()->with(['company', 'user'])->latest()->limit(8)->get(),
                'emptyMessage' => null,
            ];
        }

        return [
            'heading' => 'Dashboard',
            'introduction' => 'Signed in as '.$role?->name.'.',
            'companyName' => null,
            'metrics' => [],
            'recentUrls' => $empty,
            'emptyMessage' => 'No dashboard access is configured for this role.',
        ];
    }
}
