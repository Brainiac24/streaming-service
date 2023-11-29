<?php

namespace Database\Seeders;

use App\Constants\SaleStatuses;
use App\Models\SaleStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SaleStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => SaleStatuses::NEW, 'name' => 'new'],
            ['id' => SaleStatuses::SHARED, 'name' => 'shared'],
            ['id' => SaleStatuses::DONE, 'name' => 'done'],
        ];

        foreach ($items as $item) {
            try {
                SaleStatus::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
