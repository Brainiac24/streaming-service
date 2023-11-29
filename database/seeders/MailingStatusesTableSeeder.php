<?php

namespace Database\Seeders;

use App\Constants\MailingStatuses;
use App\Models\MailingStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MailingStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => MailingStatuses::ACTIVE, 'name' => 'Active'],
            ['id' => MailingStatuses::INACTIVE, 'name' => 'Inactive'],
            ['id' => MailingStatuses::IN_PROCESS, 'name' => 'In process'],
            ['id' => MailingStatuses::NOT_AVAILABLE, 'name' => 'Not available'],
            ['id' => MailingStatuses::FAILED, 'name' => 'Failed'],
            ['id' => MailingStatuses::COMPLETED, 'name' => 'Completed'],
        ];

        foreach ($items as $item) {
            try {
                MailingStatus::updateOrCreate(['id' => $item['id']], $item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
