<?php

namespace Database\Seeders;

use App\Constants\FareTypes;
use App\Models\Fare;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FaresTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'id' => 1,
                'fare_type_id' => FareTypes::FREE,
                'name' => 'Start',
                'description' => 'up to 100 viewers',
                'price' => 0,
                'old_price' => 0,
                'config_json' => [
                    'viewers_count' => 100,
                    'quality' => '720p',
                    'storage_duration_amount' => 1,
                    'storage_duration_unit' => 'week',
                    'moderators_count' => 0,
                    'is_selected' => false,
                    'is_unique_tickets_enabled' => false,
                    'is_sell_buttons_enabled' => false,
                    'is_fullhd_enabled' => false,
                    'is_update_logo_enabled' => false,
                    'is_unique_url_enabled' => false,
                ],
            ],
            [
                'id' => 2,
                'fare_type_id' => FareTypes::BASIC,
                'name' => 'Basic',
                'description' => 'up to 500 viewers',
                'price' => 1490,
                'old_price' => 2490,
                'config_json' => [
                    'viewers_count' => 500,
                    'quality' => '720p',
                    'storage_duration_amount' => 1,
                    'storage_duration_unit' => 'month',
                    'moderators_count' => 0,
                    'is_selected' => false,
                    'is_unique_tickets_enabled' => true,
                    'is_sell_buttons_enabled' => false,
                    'is_fullhd_enabled' => false,
                    'is_update_logo_enabled' => false,
                    'is_unique_url_enabled' => true,
                ],
            ],
            [
                'id' => 3,
                'fare_type_id' => FareTypes::STANDART,
                'name' => 'Standart',
                'description' => 'up to 2000 viewers',
                'price' => 2990,
                'old_price' => 4990,
                'config_json' => [
                    'viewers_count' => 2000,
                    'quality' => '720p',
                    'storage_duration_amount' => 3,
                    'storage_duration_unit' => 'months',
                    'moderators_count' => 2,
                    'is_selected' => true,
                    'is_unique_tickets_enabled' => true,
                    'is_sell_buttons_enabled' => true,
                    'is_fullhd_enabled' => false,
                    'is_update_logo_enabled' => false,
                    'is_unique_url_enabled' => true,
                ],
            ],
            [
                'id' => 4,
                'fare_type_id' => FareTypes::ADVANCE,
                'name' => 'Advance',
                'description' => 'up to 5000 viewers',
                'price' => 4990,
                'old_price' => 7990,
                'config_json' => [
                    'viewers_count' => 5000,
                    'quality' => '720p',
                    'storage_duration_amount' => 6,
                    'storage_duration_unit' => 'months',
                    'moderators_count' => 5,
                    'is_selected' => false,
                    'is_unique_tickets_enabled' => true,
                    'is_sell_buttons_enabled' => true,
                    'is_fullhd_enabled' => false,
                    'is_update_logo_enabled' => false,
                    'is_unique_url_enabled' => true,
                ],
            ],
            [
                'id' => 5,
                'fare_type_id' => FareTypes::PREMIUM,
                'name' => 'Premium',
                'description' => 'up to 10000 viewers',
                'price' => 6990,
                'old_price' => 9990,
                'config_json' => [
                    'viewers_count' => 10000,
                    'quality' => '1080p',
                    'storage_duration_amount' => 12,
                    'storage_duration_unit' => 'months',
                    'moderators_count' => 10,
                    'is_selected' => false,
                    'is_unique_tickets_enabled' => true,
                    'is_sell_buttons_enabled' => true,
                    'is_fullhd_enabled' => true,
                    'is_update_logo_enabled' => true,
                    'is_unique_url_enabled' => true,
                ],
            ],
            [
                'id' => 6,
                'fare_type_id' => FareTypes::EXTRA,
                'name' => 'Extra halls',
                'description' => 'Parallel session on the same days (the total number of views of all halls is not higher than the selected tariff)',
                'price' => 990,
                'old_price' => 1490,
                'config_json' => [],
            ],
        ];

        foreach ($items as $item) {
            try {
                Fare::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
