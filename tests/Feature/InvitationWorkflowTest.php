<?php

namespace Tests\Feature;

use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use LogicException;
use Tests\TestCase;

class InvitationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_superadmin_can_invite_an_admin_and_only_a_hash_is_stored(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post(route('companies.invitations.store', $company), [
            'email' => 'new-admin@example.test',
            'role' => 'admin',
        ]);

        $invitation = Invitation::firstOrFail();
        $mail = $this->sentInvitationMail();

        $response->assertRedirect(route('companies.show', $company));
        $this->assertSame($company->id, $invitation->company_id);
        $this->assertSame($superAdmin->id, $invitation->invited_by);
        $this->assertSame('admin', $invitation->role->slug);
        $this->assertSame(hash('sha256', $mail->acceptanceToken), $invitation->token_hash);
        $this->assertNotSame($mail->acceptanceToken, $invitation->token_hash);
        $this->assertStringContainsString(
            route('invitations.accept', $mail->acceptanceToken),
            $mail->render(),
        );
        Mail::assertSent(InvitationMail::class, fn (InvitationMail $sent): bool => $sent->hasTo('new-admin@example.test'));
    }

    public function test_superadmin_invite_form_only_offers_admin(): void
    {
        $company = Company::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('companies.invitations.create', $company))
            ->assertOk()
            ->assertSee('value="admin"', false)
            ->assertDontSee('value="member"', false);
    }

    public function test_admin_invite_form_offers_admin_and_member(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);

        $this->actingAs($admin)
            ->get(route('companies.invitations.create', $company))
            ->assertOk()
            ->assertSee('value="admin"', false)
            ->assertSee('value="member"', false);
    }

    public function test_admin_can_invite_admin_and_member_only_in_its_company(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);

        foreach (['admin', 'member'] as $role) {
            $this->actingAs($admin)
                ->post(route('companies.invitations.store', $company), [
                    'email' => "new-{$role}@example.test",
                    'role' => $role,
                ])
                ->assertRedirect(route('companies.show', $company));
        }

        $this->assertDatabaseCount('invitations', 2);
        Mail::assertSent(InvitationMail::class, 2);
    }

    public function test_admin_cannot_invite_into_another_company(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $admin = User::factory()->role('admin')->create(['company_id' => $ownCompany->id]);

        $this->actingAs($admin)
            ->post(route('companies.invitations.store', $otherCompany), [
                'email' => 'outside@example.test',
                'role' => 'member',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_member_sales_and_manager_cannot_invite_by_default(): void
    {
        $company = Company::factory()->create();

        foreach (['member', 'sales', 'manager'] as $role) {
            $user = User::factory()->role($role)->create(['company_id' => $company->id]);

            $this->actingAs($user)
                ->post(route('companies.invitations.store', $company), [
                    'email' => "{$role}@example.test",
                    'role' => 'member',
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_invitee_can_accept_and_receive_the_invited_role_and_company(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('companies.invitations.store', $company), [
            'email' => 'accepted@example.test',
            'role' => 'admin',
        ]);

        $mail = $this->sentInvitationMail();
        $invitation = Invitation::firstOrFail();
        Auth::logout();

        $this->get(route('invitations.accept', $mail->acceptanceToken))
            ->assertOk()
            ->assertSee('accepted@example.test');

        $response = $this->post(route('invitations.complete', $mail->acceptanceToken), [
            'name' => 'Accepted Admin',
            'email' => 'accepted@example.test',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
            'role' => 'superadmin',
        ]);

        $response->assertRedirect(route('login'))->assertSessionHas('status');
        $this->get(route('login'))->assertOk()->assertSee('Your account is ready. Sign in to continue.');
        $this->assertDatabaseHas('users', [
            'email' => 'accepted@example.test',
            'role_id' => Role::where('slug', 'admin')->value('id'),
            'company_id' => $company->id,
        ]);
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->get(route('invitations.accept', $mail->acceptanceToken))->assertStatus(410);
        $this->post(route('invitations.complete', $mail->acceptanceToken), [
            'name' => 'Duplicate Admin',
            'email' => 'accepted@example.test',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
        ])->assertStatus(410);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_invitation_cannot_be_accepted_with_a_different_email(): void
    {
        Mail::fake();
        $company = Company::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('companies.invitations.store', $company), [
            'email' => 'invited@example.test',
            'role' => 'admin',
        ]);

        $mail = $this->sentInvitationMail();
        Auth::logout();

        $this->post(route('invitations.complete', $mail->acceptanceToken), [
            'name' => 'Wrong Email',
            'email' => 'different@example.test',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'invited@example.test']);
        $this->assertNull(Invitation::firstOrFail()->accepted_at);
    }

    public function test_expired_invitation_cannot_be_opened_or_reused(): void
    {
        $company = Company::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $token = bin2hex(random_bytes(32));

        Invitation::create([
            'company_id' => $company->id,
            'email' => 'expired@example.test',
            'role_id' => Role::where('slug', 'admin')->value('id'),
            'invited_by' => $superAdmin->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('invitations.accept', $token))->assertStatus(410);
        $this->post(route('invitations.complete', $token), [
            'name' => 'Expired User',
            'email' => 'expired@example.test',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
        ])->assertStatus(410);

        $this->assertDatabaseMissing('users', ['email' => 'expired@example.test']);
    }

    private function sentInvitationMail(): InvitationMail
    {
        /** @var InvitationMail|null $sentMail */
        $sentMail = null;

        Mail::assertSent(InvitationMail::class, function (InvitationMail $mail) use (&$sentMail): bool {
            $sentMail = $mail;

            return true;
        });

        return $sentMail ?? throw new LogicException('No invitation email was sent.');
    }
}
