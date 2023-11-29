<?php

namespace Database\Seeders;

use App\Constants\TransactionCodes;
use App\Models\TransactionCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TransactionCodesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => TransactionCodes::CREATE_SESSION, 'name' => 'create_session'],
            ['id' => TransactionCodes::CREATE_EXTRA_SESSION, 'name' => 'create_extra_session'],
            ['id' => TransactionCodes::FILL_BALANCE, 'name' => 'fill_balance'],
            ['id' => TransactionCodes::UPGRADE_FARE, 'name' => 'upgrade_fare'],
        ];

        foreach ($items as $item) {
            try {
                TransactionCode::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
