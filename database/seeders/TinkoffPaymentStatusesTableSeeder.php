<?php

namespace Database\Seeders;

use App\Constants\TinkoffPaymentStatuses;
use App\Models\TinkoffPaymentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TinkoffPaymentStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['id' => TinkoffPaymentStatuses::INIT, 'name' => TinkoffPaymentStatuses::INIT_NAME],
            ['id' => TinkoffPaymentStatuses::NEW, 'name' => TinkoffPaymentStatuses::NEW_NAME],
            ['id' => TinkoffPaymentStatuses::FORM_SHOWED, 'name' => TinkoffPaymentStatuses::FORM_SHOWED_NAME],
            ['id' => TinkoffPaymentStatuses::DEADLINE_EXPIRED, 'name' => TinkoffPaymentStatuses::DEADLINE_EXPIRED_NAME],
            ['id' => TinkoffPaymentStatuses::CANCELED, 'name' => TinkoffPaymentStatuses::CANCELED_NAME],
            ['id' => TinkoffPaymentStatuses::PREAUTHORIZING, 'name' => TinkoffPaymentStatuses::PREAUTHORIZING_NAME],
            ['id' => TinkoffPaymentStatuses::AUTHORIZING, 'name' => TinkoffPaymentStatuses::AUTHORIZING_NAME],
            ['id' => TinkoffPaymentStatuses::AUTH_FAIL, 'name' => TinkoffPaymentStatuses::AUTH_FAIL_NAME],
            ['id' => TinkoffPaymentStatuses::REJECTED, 'name' => TinkoffPaymentStatuses::REJECTED_NAME],
            ['id' => TinkoffPaymentStatuses::THREE_DS_CHECKING, 'name' => TinkoffPaymentStatuses::THREE_DS_CHECKING_NAME],
            ['id' => TinkoffPaymentStatuses::THREE_DS_CHECKED, 'name' => TinkoffPaymentStatuses::THREE_DS_CHECKED_NAME],
            ['id' => TinkoffPaymentStatuses::PAY_CHECKING, 'name' => TinkoffPaymentStatuses::PAY_CHECKING_NAME],
            ['id' => TinkoffPaymentStatuses::AUTHORIZED, 'name' => TinkoffPaymentStatuses::AUTHORIZED_NAME],
            ['id' => TinkoffPaymentStatuses::REVERSING, 'name' => TinkoffPaymentStatuses::REVERSING_NAME],
            ['id' => TinkoffPaymentStatuses::PARTIAL_REVERSED, 'name' => TinkoffPaymentStatuses::PARTIAL_REVERSED_NAME],
            ['id' => TinkoffPaymentStatuses::REVERSED, 'name' => TinkoffPaymentStatuses::REVERSED_NAME],
            ['id' => TinkoffPaymentStatuses::CONFIRMING, 'name' => TinkoffPaymentStatuses::CONFIRMING_NAME],
            ['id' => TinkoffPaymentStatuses::CONFIRM_CHECKING, 'name' => TinkoffPaymentStatuses::CONFIRM_CHECKING_NAME],
            ['id' => TinkoffPaymentStatuses::CONFIRMED, 'name' => TinkoffPaymentStatuses::CONFIRMED_NAME],
            ['id' => TinkoffPaymentStatuses::REFUNDING, 'name' => TinkoffPaymentStatuses::REFUNDING_NAME],
            ['id' => TinkoffPaymentStatuses::PARTIAL_REFUNDED, 'name' => TinkoffPaymentStatuses::PARTIAL_REFUNDED_NAME],
            ['id' => TinkoffPaymentStatuses::REFUNDED, 'name' => TinkoffPaymentStatuses::REFUNDED_NAME],
        ];

        foreach ($items as $item) {
            try {
                TinkoffPaymentStatus::create($item);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
