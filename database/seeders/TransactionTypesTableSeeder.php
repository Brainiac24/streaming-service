<?php

namespace Database\Seeders;

use App\Constants\TransactionTypes;
use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TransactionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => TransactionTypes::PAY, 'name' => 'pay'],
            ['id' => TransactionTypes::FILL, 'name' => 'fill'],
            ['id' => TransactionTypes::RETURN, 'name' => 'return'],
        ];

        foreach ($items as $item) {
            try {
                TransactionType::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
