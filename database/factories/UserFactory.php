<?php

namespace Database\Factories;

use App\Models\User;
use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(array $data = null): array
    {
        return [
            'login' => $data['login'] ?? fake()->name(),
            'name' => $data['name'] ?? fake()->name(),
            'lastname' => $data['lastname'] ?? fake()->lastName(),
            'password' => Hash::make('password'),
            'email' => $data['email'] ?? fake()->unique()->safeEmail(),
            'is_active' => $data['is_active'] ?? true,
            'email_verified_at' => now()
        ];
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
