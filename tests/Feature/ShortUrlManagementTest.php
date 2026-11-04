<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\ShortUrl;
use App\Models\User;
use App\Services\ShortCodeGenerator;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortUrlManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_create_a_short_url_for_its_company(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);

        $this->actingAs($admin)
            ->post(route('short-urls.store'), ['original_url' => 'https://example.com/path'])
            ->assertRedirect();

        $shortUrl = ShortUrl::firstOrFail();
        $this->assertSame($company->id, $shortUrl->company_id);
        $this->assertSame($admin->id, $shortUrl->user_id);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{8}$/', $shortUrl->short_code);
    }

    public function test_member_can_create_a_short_url(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->post(route('short-urls.store'), ['original_url' => 'http://example.com'])
            ->assertRedirect();

        $this->assertDatabaseHas('short_urls', [
            'company_id' => $member->company_id,
            'user_id' => $member->id,
            'original_url' => 'http://example.com',
        ]);
    }

    public function test_superadmin_cannot_create_short_urls(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('short-urls.store'), ['original_url' => 'https://example.com'])
            ->assertForbidden();

        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_superadmin_can_view_short_urls_across_companies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $firstCompany = Company::factory()->create(['name' => 'First Company']);
        $secondCompany = Company::factory()->create(['name' => 'Second Company']);
        $firstCreator = User::factory()->role('member')->create(['company_id' => $firstCompany->id]);
        $secondCreator = User::factory()->role('member')->create(['company_id' => $secondCompany->id]);
        $this->createShortUrl($firstCreator, 'first001');
        $this->createShortUrl($secondCreator, 'second01');

        $this->actingAs($superAdmin)
            ->get(route('short-urls.index'))
            ->assertOk()
            ->assertSee('first001')
            ->assertSee('second01')
            ->assertSee('First Company')
            ->assertSee('Second Company');
    }

    public function test_admin_can_only_list_its_company_urls_and_cannot_open_another_company_url(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $admin = User::factory()->role('admin')->create(['company_id' => $company->id]);
        $ownUrl = $this->createShortUrl($admin, 'ownurl01');
        $otherCreator = User::factory()->role('member')->create(['company_id' => $otherCompany->id]);
        $otherUrl = $this->createShortUrl($otherCreator, 'otherurl');

        $this->actingAs($admin)
            ->get(route('short-urls.index'))
            ->assertOk()
            ->assertSee('ownurl01')
            ->assertDontSee('otherurl');

        $this->get(route('short-urls.show', $otherUrl))->assertForbidden();
        $this->get(route('short-urls.show', $ownUrl))->assertOk();
    }

    public function test_member_can_only_list_and_open_own_urls(): void
    {
        $member = User::factory()->create();
        $otherMember = User::factory()->role('member')->create(['company_id' => $member->company_id]);
        $ownUrl = $this->createShortUrl($member, 'member01');
        $otherUrl = $this->createShortUrl($otherMember, 'member02');

        $this->actingAs($member)
            ->get(route('short-urls.index'))
            ->assertOk()
            ->assertSee('member01')
            ->assertDontSee('member02');

        $this->get(route('short-urls.show', $otherUrl))->assertForbidden();
        $this->get(route('short-urls.show', $ownUrl))->assertOk();
    }

    public function test_short_url_redirects_with_http_302(): void
    {
        $creator = User::factory()->create();
        $shortUrl = $this->createShortUrl($creator, 'redirect1', 'https://example.com/destination');

        $this->get(route('short-urls.redirect', $shortUrl->short_code))
            ->assertStatus(302)
            ->assertRedirect('https://example.com/destination');
    }

    public function test_unknown_short_code_returns_404(): void
    {
        $this->get(route('short-urls.redirect', 'missing01'))->assertNotFound();
    }

    public function test_redirect_rejects_an_unsafe_scheme_even_if_it_is_already_stored(): void
    {
        $creator = User::factory()->create();
        $shortUrl = $this->createShortUrl($creator, 'unsafe01', 'https://safe.example.com');
        $shortUrl->forceFill(['original_url' => 'javascript:alert(1)'])->save();

        $this->get(route('short-urls.redirect', $shortUrl->short_code))->assertNotFound();
    }

    public function test_short_url_listing_is_paginated(): void
    {
        $member = User::factory()->create();

        foreach (range(0, 15) as $index) {
            $code = 'page'.str_pad((string) $index, 4, '0', STR_PAD_LEFT);
            $shortUrl = $this->createShortUrl($member, $code);
            $shortUrl->forceFill(['created_at' => now()->addSeconds($index)])->save();
        }

        $this->actingAs($member)
            ->get(route('short-urls.index'))
            ->assertOk()
            ->assertSee('page0015')
            ->assertDontSee('page0000')
            ->assertSee('Next');

        $this->get(route('short-urls.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('page0000')
            ->assertDontSee('page0015');
    }

    public function test_unsafe_redirect_schemes_are_rejected_on_creation(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->from(route('short-urls.create'))
            ->post(route('short-urls.store'), ['original_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('original_url');

        $this->assertDatabaseCount('short_urls', 0);
    }

    public function test_code_collision_retries_with_a_new_code(): void
    {
        $member = User::factory()->create();
        $existingCreator = User::factory()->create();
        $this->createShortUrl($existingCreator, 'collide1');
        $generator = new class extends ShortCodeGenerator
        {
            private array $codes = ['collide1', 'unique01'];

            public function generate(int $length = 8): string
            {
                return array_shift($this->codes);
            }
        };
        $this->app->instance(ShortCodeGenerator::class, $generator);

        $this->actingAs($member)
            ->post(route('short-urls.store'), ['original_url' => 'https://example.com/new'])
            ->assertRedirect();

        $this->assertDatabaseHas('short_urls', [
            'user_id' => $member->id,
            'short_code' => 'unique01',
        ]);
    }

    private function createShortUrl(User $creator, string $code, string $originalUrl = 'https://example.com'): ShortUrl
    {
        return ShortUrl::create([
            'company_id' => $creator->company_id,
            'user_id' => $creator->id,
            'original_url' => $originalUrl,
            'short_code' => $code,
        ]);
    }

    public function test_roles_without_configured_permissions_cannot_create_or_list_urls(): void
    {
        $company = Company::factory()->create();
        $sales = User::factory()->role('sales')->create(['company_id' => $company->id]);
        Role::where('slug', 'sales')->update(['permissions' => json_encode([])]);

        $this->actingAs($sales)
            ->get(route('short-urls.index'))
            ->assertForbidden();
        $this->post(route('short-urls.store'), ['original_url' => 'https://example.com'])
            ->assertForbidden();
    }
}
