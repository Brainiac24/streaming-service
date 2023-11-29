<?php

namespace Database\Seeders;

use App\Constants\FareTypes;
use App\Models\FareType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FareTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => FareTypes::FREE, 'name' => 'free'],
            ['id' => FareTypes::BASIC, 'name' => 'basic'],
            ['id' => FareTypes::STANDART, 'name' => 'standart'],
            ['id' => FareTypes::ADVANCE, 'name' => 'advance'],
            ['id' => FareTypes::PREMIUM, 'name' => 'premium'],
            ['id' => FareTypes::EXTRA, 'name' => 'extra'],
        ];

        foreach ($items as $item) {
            try {
                FareType::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
