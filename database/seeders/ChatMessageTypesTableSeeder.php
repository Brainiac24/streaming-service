<?php

namespace Database\Seeders;

use App\Constants\ChatMessageTypes;
use App\Models\ChatMessageType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ChatMessageTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => ChatMessageTypes::MESSAGE, 'name' => 'message'],
            ['id' => ChatMessageTypes::QUESTION, 'name' => 'question'],
        ];

        foreach ($items as $item) {
            try {
                ChatMessageType::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}