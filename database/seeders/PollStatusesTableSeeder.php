<?php

namespace Database\Seeders;

use App\Constants\PollStatuses;
use App\Models\PollStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PollStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => PollStatuses::NEW, 'name' => 'new'],
            ['id' => PollStatuses::STARTED, 'name' => 'started'],
            ['id' => PollStatuses::FINISHED, 'name' => 'finished'],
        ];

        foreach ($items as $item) {
            try {
                PollStatus::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
