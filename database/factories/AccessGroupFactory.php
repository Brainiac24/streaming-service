<?php

namespace Database\Factories;

use App\Models\AccessGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessGroup>
 */
class AccessGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(array $data = []): array
    {
        return [
            'name'         => $data['name'] ?? $this->faker->name,
            'display_name' => $data['display_name'] ?? $this->faker->name,
            'description'  => $data['description'] ?? $this->faker->text,
            'is_active'    => $data['is_active'] ?? true,
        ];
    }
}
