<?php

namespace App\Http\Resources\Fare;

use App\Constants\FareTypes;
use App\Http\Resources\BaseJsonResource;
use Illuminate\Support\Facades\App;

class UpgradeFareListResource extends BaseJsonResource
{
    public $fares;
    public function __construct($fares, $currentFare = null)
    {
        $defaultFares = [];

        foreach ($fares as $fare) {

            $viewers_count = __('up to :viewers_count viewers', [
                'viewers_count' => (int)($fare->config_json['viewers_count'] ?? 0)
            ]);

            $storage_duration_unit = isset($fare->config_json['storage_duration_unit']) ? __($fare->config_json['storage_duration_unit']) : '';

            if (isset($fare->config_json['moderators_count']) && $fare->config_json['moderators_count'] > 0) {
                $moderators_count = __('up to :moderators_count moderators', [
                    'moderators_count' => (int)($fare->config_json['moderators_count'] ?? 0)
                ]);
            } else {
                $moderators_count = __('without moderators');
            }

            $upgradePrice = 0;

            if ($currentFare != null) {
                $upgradePrice = $fare->price - $currentFare->price;
            }

            if ($upgradePrice > 0 && $fare->fare_type_id != FareTypes::EXTRA) {
                $fareItem = [
                    'id' => $fare->id,
                    'type' => $fare->fareType->name,
                    'name' => __($fare->name),
                    'description' => __($fare->description),
                    'price' => $fare->price,
                    'old_price' => $fare->old_price,
                    'upgrade_price' => $upgradePrice,
                    'is_selected' => $fare->config_json['is_selected'] ?? false,
                    'config' => [
                        'viewers_count_text' => $viewers_count,
                        'viewers_count' => ($fare->config_json['viewers_count'] ?? 0),
                        'quality' => $fare->config_json['quality'] ?? '',
                        'storage_duration' => trim(($fare->config_json['storage_duration_amount'] ?? 0) . ' ' . __($storage_duration_unit)),
                        'moderators_count' => __($moderators_count),
                        'is_unique_tickets_enabled' => $fare->config_json['is_unique_tickets_enabled'] ?? false,
                        'is_sell_buttons_enabled' => $fare->config_json['is_sell_buttons_enabled'] ?? false,
                        'is_fullhd_enabled' => $fare->config_json['is_fullhd_enabled'] ?? false,
                        'is_update_logo_enabled' => $fare->config_json['is_update_logo_enabled'] ?? false,
                        'is_unique_url_enabled' => $fare->config_json['is_unique_url_enabled'] ?? false,
                    ],
                ];

                array_push($defaultFares, $fareItem);
            }
        }

        $this->data = $defaultFares;
    }
}
