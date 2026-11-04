<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_superadmin_dashboard_aggregates_all_company_data(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $firstCompany = Company::factory()->create(['name' => 'First Dashboard Company']);
        $secondCompany = Company::factory()->create(['name' => 'Second Dashboard Company']);
        $firstUser = User::factory()->role('member')->create(['company_id' => $firstCompany->id]);
        $secondUser = User::factory()->role('member')->create(['company_id' => $secondCompany->id]);
        $this->createShortUrl($firstUser, 'global001', 'https://first.example.test');
        $this->createShortUrl($secondUser, 'global002', 'https://second.example.test');

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Platform overview')
            ->assertSee('First Dashboard Company')
            ->assertSee('Second Dashboard Company')
            ->assertSee('global001')
            ->assertSee('global002');
    }

    public function test_admin_dashboard_only_includes_its_company_data(): void
    {
        $company = Company::factory()->create(['name' => 'Admin Dashboard Company']);
        $otherCompany = Company::factory()->create(['name' => 'Hidden Dashboard Company']);
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);
        $ownMember = User::factory()->role('member')->create(['company_id' => $company->id]);
        $otherMember = User::factory()->role('member')->create(['company_id' => $otherCompany->id]);
        $this->createShortUrl($ownMember, 'adminown1', 'https://own.example.test');
        $this->createShortUrl($otherMember, 'adminoth1', 'https://other.example.test');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Company overview')
            ->assertSee('Admin Dashboard Company')
            ->assertSee('adminown1')
            ->assertDontSee('Hidden Dashboard Company')
            ->assertDontSee('adminoth1');
    }

    public function test_member_dashboard_only_includes_urls_created_by_that_member(): void
    {
        $member = User::factory()->create();
        $otherMember = User::factory()->role('member')->create(['company_id' => $member->company_id]);
        $this->createShortUrl($member, 'memberown');
        $this->createShortUrl($otherMember, 'memberoth');

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My URL activity')
            ->assertSee('memberown')
            ->assertDontSee('memberoth');
    }

    public function test_sales_and_manager_dashboard_shows_no_unconfigured_business_data(): void
    {
        $company = Company::factory()->create();

        foreach (['sales', 'manager'] as $role) {
            $user = User::factory()->role($role)->create(['company_id' => $company->id]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('No dashboard access is configured for this role.')
                ->assertDontSee($company->name);
        }
    }

    public function test_error_views_render_safe_status_messages(): void
    {
        $this->assertStringContainsString('Access denied', view('errors.403')->render());
        $this->assertStringContainsString('Page not found', view('errors.404')->render());
        $this->assertStringContainsString('Link unavailable', view('errors.410')->render());
        $this->assertStringContainsString('Something went wrong', view('errors.500')->render());
    }

    private function createShortUrl(User $creator, string $code, string $url = 'https://example.test'): ShortUrl
    {
        return ShortUrl::create([
            'company_id' => $creator->company_id,
            'user_id' => $creator->id,
            'original_url' => $url,
            'short_code' => $code,
        ]);
    }
}
