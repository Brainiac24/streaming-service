<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            EventSessionStatusesTableSeeder::class,
            EventStatusesTableSeeder::class,
            EventTicketStatusesTableSeeder::class,
            EventTicketTypesTableSeeder::class,
            ProjectStatusesTableSeeder::class,
            TinkoffPaymentStatusesTableSeeder::class,
            TransactionTypesTableSeeder::class,
            FareTypesTableSeeder::class,
            FaresTableSeeder::class,
            StreamStatusesTableSeeder::class,
            RolesTableSeeder::class,
            EventDataCollectionDictionarySeeder::class,
            ChatMessageTypesTableSeeder::class,
            PollStatusesTableSeeder::class,
            PollTypesTableSeeder::class,
            SaleStatusesTableSeeder::class,
            TransactionCodesTableSeeder::class,
            MailingStatusesTableSeeder::class,
            MessageTemplatesTableSeeder::class,
            UserSeeder::class
        ]);
    }
}
