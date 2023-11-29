<?php

namespace Database\Seeders;

use App\Constants\EventTicketTypes;
use App\Models\EventTicketType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventTicketTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => EventTicketTypes::MULTI, 'name' => 'multi'],
            ['id' => EventTicketTypes::UNIQUE, 'name' => 'unique'],
        ];

        foreach ($items as $item) {
            try {
                EventTicketType::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
