<?php

namespace Database\Seeders;

use App\Constants\StreamStatuses;
use App\Models\StreamStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class StreamStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => StreamStatuses::NEW, 'name' => 'new']
        ];

        foreach ($items as $item) {
            try {
                StreamStatus::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
