<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use App\Policies\CompanyPolicy;
use App\Policies\ShortUrlPolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(ShortUrl::class, ShortUrlPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Paginator::useBootstrapFive();

        Gate::define('permission', function (User $user, string $permission): bool {
            return $user->role?->hasPermission($permission) ?? false;
        });
    }
}
