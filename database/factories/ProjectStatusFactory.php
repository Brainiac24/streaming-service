<?php

namespace Database\Factories;

use App\Models\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectStatus>
 */
class ProjectStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(array $data = []): array
    {
        return [
            'name' => $data['name'] ?? $this->faker->name(),
            'is_active' => $data['is_active'] ?? true
        ];
    }
}
