<?php

namespace App\Http\Resources\EventSessionVisit;

use App\Http\Resources\BaseJsonResource;

class ExportEventSessionVisitUsersListResource extends BaseJsonResource
{
    public function __construct($eventSessionVisits, $eventDataCollectionTemplate)
    {
        $headers = [
            'id' => __('ID'),
            'email' => __('Email'),
            'name' => __('Name'),
            'lastname' => __('Lastname'),
            'phone' => __('Phone'),
            'url' => __('URL'),
            'ip' => __('IP'),
        ];

        $eventIsDataCollectionEnabled = false;
        if (
            $eventDataCollectionTemplate &&
            isset($eventDataCollectionTemplate[0]) &&
            isset($eventDataCollectionTemplate[0]['event_is_data_collection_enabled'])
        ) {
            $eventIsDataCollectionEnabled = $eventDataCollectionTemplate[0]['event_is_data_collection_enabled'];

            foreach ($eventDataCollectionTemplate as $item) {
                if ($item['is_editable']) {
                    $headers[$item['label']] = $item['label'];
                }
            }
        }

        $data = [];
        $eventDataCollectionTemplateItem = [];
        foreach ($eventDataCollectionTemplate as $item) {
            $eventDataCollectionTemplateItem[$item['label']] = '';
        }

        foreach ($eventSessionVisits as $eventSessionVisit) {

            if (!isset($data[$eventSessionVisit['id']])) {
                $data[$eventSessionVisit['id']] = [
                    'id' => $eventSessionVisit['id'],
                    'email' => (!is_null($eventSessionVisit['email']) ? $eventSessionVisit['email'] : "Guest"),
                    'name' => $eventSessionVisit['name'],
                    'lastname' => $eventSessionVisit['lastname'],
                    'phone' => $eventSessionVisit['phone'],
                    'url' => (!is_null($eventSessionVisit['email']) ? $eventSessionVisit['source'] : "Iframe") ,
                    'ip' => $eventSessionVisit['ip'],
                ];
                $data[$eventSessionVisit['id']] += $eventDataCollectionTemplateItem;
            }

            if ($eventIsDataCollectionEnabled) {
                if ($item['is_editable']) {
                    $data[$eventSessionVisit['id']][$eventSessionVisit['event_data_collection_template_label']] = ($eventSessionVisit['event_data_collection_value'] ?? '');
                }
            }
        }

        $this->data = [$headers] + $data;
    }
}
