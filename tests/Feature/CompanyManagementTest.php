<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_superadmin_can_create_a_company(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post(route('companies.store'), [
            'name' => 'Northwind Trading',
        ]);

        $company = Company::where('slug', 'northwind-trading')->firstOrFail();

        $response->assertRedirect(route('companies.show', $company));
        $response->assertSessionHas('status', 'Company created.');
    }

    public function test_superadmin_can_list_all_companies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Company::factory()->create(['name' => 'Northwind', 'slug' => 'northwind']);
        Company::factory()->create(['name' => 'Contoso', 'slug' => 'contoso']);

        $this->actingAs($superAdmin)
            ->get(route('companies.index'))
            ->assertOk()
            ->assertSee('Northwind')
            ->assertSee('Contoso');
    }

    public function test_admin_can_only_list_and_view_its_own_company(): void
    {
        $company = Company::factory()->create(['name' => 'Own Company', 'slug' => 'own-company']);
        $otherCompany = Company::factory()->create(['name' => 'Other Company', 'slug' => 'other-company']);
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);

        $this->actingAs($admin)
            ->get(route('companies.index'))
            ->assertOk()
            ->assertSee('Own Company')
            ->assertDontSee('Other Company');

        $this->get(route('companies.show', $otherCompany))->assertForbidden();
    }

    public function test_company_details_only_show_users_from_that_company(): void
    {
        $company = Company::factory()->create(['name' => 'Own Company', 'slug' => 'own-company']);
        $otherCompany = Company::factory()->create(['name' => 'Other Company', 'slug' => 'other-company']);
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);
        User::factory()->role('member')->create([
            'name' => 'Own Employee',
            'company_id' => $company->id,
        ]);
        User::factory()->role('member')->create([
            'name' => 'Other Employee',
            'company_id' => $otherCompany->id,
        ]);

        $this->actingAs($admin)
            ->get(route('companies.show', $company))
            ->assertOk()
            ->assertSee('Own Employee')
            ->assertDontSee('Other Employee');
    }

    public function test_member_cannot_view_company_records(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('companies.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_create_companies(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);

        $this->actingAs($admin)
            ->post(route('companies.store'), ['name' => 'Unauthorized Company'])
            ->assertForbidden();
    }
}
