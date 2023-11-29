<?php

namespace Database\Seeders;

use App\Constants\PollTypes;
use App\Models\PollType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PollTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => PollTypes::ROWS, 'name' => 'rows'],
            ['id' => PollTypes::PIE, 'name' => 'pie'],
            ['id' => PollTypes::COLUMNS, 'name' => 'columns'],
        ];

        foreach ($items as $item) {
            try {
                PollType::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
