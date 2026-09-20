<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'phone' => '08' . fake()->numerify('##########'),
        'password' => static::$password ??= Hash::make('password'),
        'role' => 'user',
        'status' => 'active',
    ];
}

    public function inactive(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'deactivation_reason' => $reason ?? 'Melanggar ketentuan penggunaan aplikasi.',
        ]);
    }
}