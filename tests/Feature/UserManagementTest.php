<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_superadmin_can_view_users_and_invitations_across_companies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $firstCompany = Company::factory()->create(['name' => 'First User Company']);
        $secondCompany = Company::factory()->create(['name' => 'Second User Company']);
        $firstAdmin = User::factory()->role('admin')->create([
            'name' => 'First Company Admin',
            'company_id' => $firstCompany->id,
        ]);
        $secondMember = User::factory()->role('member')->create([
            'name' => 'Second Company Member',
            'company_id' => $secondCompany->id,
        ]);
        $this->createInvitation($firstCompany, $superAdmin, 'first-invite@example.test', 'admin');
        $this->createInvitation($secondCompany, $firstAdmin, 'second-invite@example.test', 'member');

        $this->actingAs($superAdmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee($firstAdmin->name)
            ->assertSee($secondMember->name)
            ->assertSee('First User Company')
            ->assertSee('Second User Company')
            ->assertSee('first-invite@example.test')
            ->assertSee('second-invite@example.test');
    }

    public function test_admin_only_sees_users_and_invitations_from_own_company(): void
    {
        $company = Company::factory()->create(['name' => 'Admin User Company']);
        $otherCompany = Company::factory()->create(['name' => 'Hidden User Company']);
        $admin = User::factory()->role('admin')->create([
            'name' => 'Current Admin',
            'company_id' => $company->id,
        ]);
        $ownMember = User::factory()->role('member')->create([
            'name' => 'Visible Company Member',
            'company_id' => $company->id,
        ]);
        User::factory()->role('member')->create([
            'name' => 'Hidden Company Member',
            'company_id' => $otherCompany->id,
        ]);
        $this->createInvitation($company, $admin, 'visible-invite@example.test', 'member');
        $this->createInvitation($otherCompany, $admin, 'hidden-invite@example.test', 'member');

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee($ownMember->name)
            ->assertSee('visible-invite@example.test')
            ->assertDontSee('Hidden Company')
            ->assertDontSee('hidden-invite@example.test');
    }

    public function test_member_sales_and_manager_cannot_open_user_management(): void
    {
        $company = Company::factory()->create();

        foreach (['member', 'sales', 'manager'] as $role) {
            $user = User::factory()->role($role)->create(['company_id' => $company->id]);

            $this->actingAs($user)->get(route('users.index'))->assertForbidden();
        }
    }

    public function test_guests_are_redirected_to_login_from_user_management(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    private function createInvitation(Company $company, User $inviter, string $email, string $role): Invitation
    {
        return Invitation::create([
            'company_id' => $company->id,
            'email' => $email,
            'role_id' => Role::where('slug', $role)->value('id'),
            'invited_by' => $inviter->id,
            'token_hash' => hash('sha256', $email),
            'expires_at' => now()->addDays(3),
        ]);
    }
}
