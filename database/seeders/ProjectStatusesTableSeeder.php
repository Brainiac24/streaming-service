<?php

namespace Database\Seeders;

use App\Constants\ProjectStatuses;
use App\Models\ProjectStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProjectStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => ProjectStatuses::ACTIVE, 'name' => 'active'],
            ['id' => ProjectStatuses::ARCHIVED, 'name' => 'archived'],
        ];

        foreach ($items as $item) {
            try {
                ProjectStatus::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
