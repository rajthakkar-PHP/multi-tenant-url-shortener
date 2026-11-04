<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShortUrl>
 */
class ShortUrlFactory extends Factory
{
    protected $model = ShortUrl::class;

    public function definition(): array
    {
        $company = Company::factory()->create();
        $user = User::factory()->role('member')->create(['company_id' => $company->id]);

        return [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'original_url' => fake()->url(),
            'short_code' => Str::random(8),
        ];
    }
}
