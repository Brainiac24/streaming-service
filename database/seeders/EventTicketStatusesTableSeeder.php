<?php

namespace Database\Seeders;

use App\Constants\EventTicketStatuses;
use App\Models\EventTicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventTicketStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => EventTicketStatuses::ACTIVE, 'name' => 'active'],
            ['id' => EventTicketStatuses::USED, 'name' => 'used'],
            ['id' => EventTicketStatuses::BANNED, 'name' => 'banned'],
            ['id' => EventTicketStatuses::INACTIVE, 'name' => 'inactive'],
            ['id' => EventTicketStatuses::RESERVED, 'name' => 'reserved'],
        ];

        foreach ($items as $item) {
            try {
                EventTicketStatus::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
