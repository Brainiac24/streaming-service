<?php

namespace Database\Seeders;

use App\Constants\EventSessionStatuses;
use App\Models\EventSessionStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventSessionStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => EventSessionStatuses::ACTIVE, 'name' => 'active'],
            ['id' => EventSessionStatuses::INACTIVE, 'name' => 'inactive'],
        ];

        foreach ($items as $item) {
            try {
                EventSessionStatus::updateOrCreate(['id' => $item['id']], $item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
