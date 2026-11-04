<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPERADMIN_EMAIL');
        $password = env('SUPERADMIN_PASSWORD');

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! is_string($password) || strlen($password) < 12) {
            $this->command?->warn('SuperAdmin was not seeded. Set SUPERADMIN_EMAIL and a 12+ character SUPERADMIN_PASSWORD first.');

            return;
        }

        $roleId = DB::table('roles')->where('slug', 'superadmin')->value('id');

        if ($roleId === null) {
            throw new RuntimeException('The SuperAdmin role is missing. Run RoleSeeder first.');
        }

        $existingUser = DB::table('users')->where('email', $email)->first(['role_id', 'company_id']);

        if ($existingUser !== null) {
            if ((int) $existingUser->role_id !== (int) $roleId || $existingUser->company_id !== null) {
                throw new RuntimeException('The configured SuperAdmin email is already assigned to a different account.');
            }

            return;
        }

        $now = now();

        DB::insert(
            'INSERT INTO users (name, email, role_id, company_id, password, created_at, updated_at) VALUES (?, ?, ?, NULL, ?, ?, ?)',
            ['SuperAdmin', $email, $roleId, Hash::make($password), $now, $now],
        );
    }
}
