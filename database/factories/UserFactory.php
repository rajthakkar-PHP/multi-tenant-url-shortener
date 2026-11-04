<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'company_id' => Company::factory(),
            'role_id' => fn () => $this->roleId('member'),
            'remember_token' => Str::random(10),
        ];
    }

    public function role(string $slug): static
    {
        return $this->state(fn () => [
            'role_id' => $this->roleId($slug),
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'company_id' => null,
            'role_id' => $this->roleId('superadmin'),
        ]);
    }

    private function roleId(string $slug): int
    {
        $role = Role::firstOrCreate(
            ['slug' => $slug],
            ['name' => Str::headline($slug), 'permissions' => []],
        );

        return $role->id;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
