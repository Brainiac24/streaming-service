<?php

namespace Database\Seeders;

use App\Constants\EventStatuses;
use App\Models\EventStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => EventStatuses::NEW, 'name' => 'new'],
        ];

        foreach ($items as $item) {
            try {
                EventStatus::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
