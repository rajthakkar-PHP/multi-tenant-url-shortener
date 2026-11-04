<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'SuperAdmin', 'slug' => 'superadmin', 'permissions' => ['companies.create', 'companies.view:any', 'short_urls.view:any', 'users.invite_admin']],
            ['name' => 'Admin', 'slug' => 'admin', 'permissions' => ['short_urls.create', 'short_urls.view:company', 'users.invite_admin', 'users.invite_member']],
            ['name' => 'Member', 'slug' => 'member', 'permissions' => ['short_urls.create', 'short_urls.view:own']],
            ['name' => 'Sales', 'slug' => 'sales', 'permissions' => []],
            ['name' => 'Manager', 'slug' => 'manager', 'permissions' => []],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
