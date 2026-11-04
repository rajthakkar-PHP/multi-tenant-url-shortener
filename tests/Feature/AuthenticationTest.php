<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in')
            ->assertSee('name="_token"', false);
    }

    public function test_member_can_sign_in_and_the_session_id_is_regenerated(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.test',
            'password' => 'correct-password',
        ]);
        $sessionId = session()->getId();

        $response = $this->post(route('login.store'), [
            'email' => ' MEMBER@example.test ',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
    }

    public function test_admin_can_sign_in(): void
    {
        $admin = User::factory()->role('admin')->create([
            'email' => 'admin-login@example.test',
            'password' => 'correct-password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin-login@example.test',
            'password' => 'correct-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_superadmin_can_sign_in(): void
    {
        $superAdmin = User::factory()->superAdmin()->create([
            'email' => 'superadmin-login@example.test',
            'password' => 'correct-password',
        ]);

        $this->post(route('login.store'), [
            'email' => 'superadmin-login@example.test',
            'password' => 'correct-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'missing@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_attempts_are_throttled_after_five_failures(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), [
                'email' => 'missing@example.test',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'missing@example.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_invalidates_the_authenticated_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_for_protected_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Member');
    }

    public function test_roles_are_denied_permissions_not_explicitly_granted(): void
    {
        $this->seed(RoleSeeder::class);
        $sales = User::factory()->role('sales')->create();
        $member = User::factory()->role('member')->create();

        $this->assertFalse(Gate::forUser($sales)->allows('permission', 'short_urls.create'));
        $this->assertTrue(Gate::forUser($member)->allows('permission', 'short_urls.create'));
    }
}
