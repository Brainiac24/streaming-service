<?php

namespace Database\Seeders;

use App\Constants\MessageTemplates;
use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MessageTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => MessageTemplates::DEFAULT_TEMPLATE, 'blade_path' => '/notifications/email_notification'],
        ];

        foreach ($items as $item) {
            try {
                MessageTemplate::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
