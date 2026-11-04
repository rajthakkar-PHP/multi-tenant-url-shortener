<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\ShortUrl;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TenantSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_seeder_leaves_unspecified_roles_without_permissions(): void
    {
        $this->seed(RoleSeeder::class);

        $this->assertSame([], Role::where('slug', 'sales')->firstOrFail()->permissions);
        $this->assertSame([], Role::where('slug', 'manager')->firstOrFail()->permissions);
        $this->assertSame(5, Role::count());
    }

    public function test_short_url_creator_must_belong_to_its_recorded_company(): void
    {
        $role = Role::create([
            'name' => 'Member',
            'slug' => 'member',
            'permissions' => [],
        ]);
        $creatorCompany = Company::create(['name' => 'Creator Company', 'slug' => 'creator-company']);
        $otherCompany = Company::create(['name' => 'Other Company', 'slug' => 'other-company']);
        $user = User::create([
            'name' => 'Member User',
            'email' => 'member@example.test',
            'password' => 'test-password',
            'company_id' => $creatorCompany->id,
            'role_id' => $role->id,
        ]);

        $this->expectException(QueryException::class);

        ShortUrl::create([
            'company_id' => $otherCompany->id,
            'user_id' => $user->id,
            'original_url' => 'https://example.com',
            'short_code' => 'abc12345',
        ]);
    }

    public function test_admin_user_requires_a_company(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'permissions' => []]);

        $this->expectException(ValidationException::class);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => 'test-password',
            'role_id' => $role->id,
        ]);
    }

    public function test_superadmin_user_cannot_belong_to_a_company(): void
    {
        $role = Role::create(['name' => 'SuperAdmin', 'slug' => 'superadmin', 'permissions' => []]);
        $company = Company::create(['name' => 'Company', 'slug' => 'company']);

        $this->expectException(ValidationException::class);

        User::create([
            'name' => 'SuperAdmin User',
            'email' => 'superadmin@example.test',
            'password' => 'test-password',
            'company_id' => $company->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_user_factories_respect_member_and_superadmin_company_rules(): void
    {
        $member = User::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertSame('member', $member->role->slug);
        $this->assertNotNull($member->company_id);
        $this->assertSame('superadmin', $superAdmin->role->slug);
        $this->assertNull($superAdmin->company_id);
    }
}
