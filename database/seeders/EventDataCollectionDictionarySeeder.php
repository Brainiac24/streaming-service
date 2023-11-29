<?php

namespace Database\Seeders;

use App\Constants\EventDataCollectionDictionaries;
use App\Models\EventDataCollectionDictionary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class EventDataCollectionDictionarySeeder extends Seeder
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
                'id' => EventDataCollectionDictionaries::NAME,
                'name' => EventDataCollectionDictionaries::NAME_NAME,
                'label' => 'Name',
                'is_required' => false,
                'is_editable' => false,
            ],
            [
                'id' => EventDataCollectionDictionaries::LASTNAME,
                'name' => EventDataCollectionDictionaries::LASTNAME_NAME,
                'label' => 'Lastname',
                'is_required' => false,
                'is_editable' => false,
            ],
            [
                'id' => EventDataCollectionDictionaries::EMAIL,
                'name' => EventDataCollectionDictionaries::EMAIL_NAME,
                'label' => 'Email',
                'is_required' => false,
                'is_editable' => false,
            ],
            [
                'id' => EventDataCollectionDictionaries::COUNTRY,
                'name' => EventDataCollectionDictionaries::COUNTRY_NAME,
                'label' => 'Country',
                'is_required' => false,
                'is_editable' => true,
            ],
            [
                'id' => EventDataCollectionDictionaries::REGION,
                'name' => EventDataCollectionDictionaries::REGION_NAME,
                'label' => 'Region',
                'is_required' => false,
                'is_editable' => true,
            ],
            [
                'id' => EventDataCollectionDictionaries::CITY,
                'name' => EventDataCollectionDictionaries::CITY_NAME,
                'label' => 'City',
                'is_required' => false,
                'is_editable' => true,
            ],
            [
                'id' => EventDataCollectionDictionaries::WORK_SCOPE,
                'name' => EventDataCollectionDictionaries::WORK_SCOPE_NAME,
                'label' => 'Work scope',
                'is_required' => false,
                'is_editable' => true,
            ],
            [
                'id' => EventDataCollectionDictionaries::WORK_COMPANY,
                'name' => EventDataCollectionDictionaries::WORK_COMPANY_NAME,
                'label' => 'Work company',
                'is_required' => false,
                'is_editable' => true,
            ],
            [
                'id' => EventDataCollectionDictionaries::WORK_DIVISION,
                'name' => EventDataCollectionDictionaries::WORK_DIVISION_NAME,
                'label' => 'Work division',
                'is_required' => false,
                'is_editable' => true,
            ],
            [
                'id' => EventDataCollectionDictionaries::WORK_POSITION,
                'name' => EventDataCollectionDictionaries::WORK_POSITION_NAME,
                'label' => 'Work position',
                'is_required' => false,
                'is_editable' => true,
            ],
            [
                'id' => EventDataCollectionDictionaries::EDUCATION,
                'name' => EventDataCollectionDictionaries::EDUCATION_NAME,
                'label' => 'Education',
                'is_required' => false,
                'is_editable' => true,
            ],
        ];

        foreach ($items as $item) {
            try {
                EventDataCollectionDictionary::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
