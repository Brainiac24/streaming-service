<?php

namespace Database\Factories;

use App\Models\AccessGroup;
use App\Models\Event;
use App\Models\EventStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(array $data = []): array
    {
        return [
            'name'                     => $data['name'] ?? $this->faker->name,
            'project_id'               => $data['project_id'] ?? Project::factory(),
            'access_group_id'          => $data['access_group_id'] ?? AccessGroup::factory(),
            'cover_img_path'           => $this->faker->imageUrl(360, 360, 'animals', true, 'cats'),
            'logo_img_path'            => $this->faker->imageUrl(360, 360, 'animals', true, 'cats'),
            'description'              => $data['description'] ?? $this->faker->text,
            'start_at'                 => $data['start_at'] ?? time(),
            'end_at'                   => $data['end_at'] ?? time(),
            'config_json'              => $data['config_json'] ?? null,
            'event_status_id'          => $data['event_status_id'] ?? EventStatus::factory(),
            'is_unique_ticket_enabled' => $data['is_unique_ticket_enabled'] ?? false,
            'is_multi_ticket_enabled' => $data['is_multi_ticket_enabled'] ?? false,
            'is_data_collection_enabled' => $data['is_data_collection_enabled'] ?? false
        ];
    }
}
