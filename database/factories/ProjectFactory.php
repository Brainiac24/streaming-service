<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(array $data = []): array
    {
        return [
            'name'              => $data['name'] ?? $this->faker->name,
            'user_id'           => $data['user_id'] ?? User::factory(),
            'cover_img_path'    => $this->faker->imageUrl(360, 360, 'animals', true, 'cats'),
            'project_status_id' => ProjectStatus::factory(),
            'support_name'      => $this->faker->name,
            'support_link'      => 'https://example.com/',
            'support_phone'     => $this->faker->phoneNumber,
            'support_email'     => $this->faker->email,
            'support_site'      => 'https://example.com/',
        ];
    }
}
